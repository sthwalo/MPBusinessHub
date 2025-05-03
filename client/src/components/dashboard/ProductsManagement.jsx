import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { productsApi } from '../../utils/api'

// Form component for creating/editing products
const ProductForm = ({ onSubmit, onCancel, initialData = null }) => {
  const [formData, setFormData] = useState({
    name: initialData?.name || '',
    description: initialData?.description || '',
    price: initialData?.price || '',
    image_url: initialData?.image_url || '',
    is_featured: initialData?.is_featured || false
  })
  const [errors, setErrors] = useState({})

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target
    setFormData(prev => ({ 
      ...prev, 
      [name]: type === 'checkbox' ? checked : value 
    }))
    
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }))
    }
  }

  const validateForm = () => {
    const newErrors = {}
    
    if (!formData.name.trim()) {
      newErrors.name = 'Product name is required'
    }
    
    if (formData.price && isNaN(parseFloat(formData.price))) {
      newErrors.price = 'Price must be a valid number'
    }
    
    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = (e) => {
    e.preventDefault()
    
    if (!validateForm()) return
    
    onSubmit(formData)
  }

  return (
    <div className="bg-white border rounded-lg p-6 mb-6">
      <h3 className="text-lg font-bold mb-4">
        {initialData ? 'Edit Product' : 'Add New Product'}
      </h3>
      <form onSubmit={handleSubmit}>
        <div className="mb-4">
          <label htmlFor="name" className="form-label">Product Name*</label>
          <input
            type="text"
            id="name"
            name="name"
            className={`form-control ${errors.name ? 'border-red-500' : ''}`}
            value={formData.name}
            onChange={handleChange}
            placeholder="e.g. Premium Service Package"
          />
          {errors.name && <p className="text-red-500 text-sm mt-1">{errors.name}</p>}
        </div>
        
        <div className="mb-4">
          <label htmlFor="description" className="form-label">Description</label>
          <textarea
            id="description"
            name="description"
            rows="3"
            className="form-control"
            value={formData.description}
            onChange={handleChange}
            placeholder="Describe your product or service..."
          ></textarea>
        </div>
        
        <div className="mb-4">
          <label htmlFor="price" className="form-label">Price (R)</label>
          <input
            type="number"
            step="0.01"
            min="0"
            id="price"
            name="price"
            className={`form-control ${errors.price ? 'border-red-500' : ''}`}
            value={formData.price}
            onChange={handleChange}
            placeholder="0.00"
          />
          {errors.price && <p className="text-red-500 text-sm mt-1">{errors.price}</p>}
        </div>
        
        <div className="mb-4">
          <label htmlFor="image_url" className="form-label">Image URL</label>
          <input
            type="text"
            id="image_url"
            name="image_url"
            className="form-control"
            value={formData.image_url}
            onChange={handleChange}
            placeholder="https://example.com/image.jpg"
          />
        </div>
        
        <div className="mb-4">
          <label className="flex items-center">
            <input
              type="checkbox"
              name="is_featured"
              checked={formData.is_featured}
              onChange={handleChange}
              className="mr-2"
            />
            <span>Featured Product</span>
          </label>
        </div>
        
        <div className="flex justify-end space-x-3">
          <button 
            type="button" 
            className="btn btn-outline" 
            onClick={onCancel}
          >
            Cancel
          </button>
          <button 
            type="submit" 
            className="btn btn-primary"
          >
            {initialData ? 'Update Product' : 'Add Product'}
          </button>
        </div>
      </form>
    </div>
  )
}

// Individual product card component
const ProductCard = ({ product, onEdit, onDelete }) => {
  return (
    <div className="bg-white rounded-lg shadow-sm border p-6">
      <div className="flex justify-between items-start">
        <div>
          <div className="flex items-center mb-2">
            <h3 className="text-lg font-bold">{product.name}</h3>
            {product.is_featured && (
              <span className="ml-3 text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">
                Featured
              </span>
            )}
          </div>
          <p className="text-gray-600 mb-2">{product.description || 'No description'}</p>
          {product.price && (
            <p className="font-bold mt-2">R{parseFloat(product.price).toFixed(2)}</p>
          )}
        </div>
        <div className="flex space-x-2">
          <button 
            onClick={() => onEdit(product)}
            className="text-blue-600 hover:text-blue-800"
            title="Edit product"
          >
            <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
          </button>
          <button 
            onClick={() => onDelete(product.id)}
            className="text-red-600 hover:text-red-800"
            title="Delete product"
          >
            <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  )
}

// Main component
function ProductsManagement({ businessData }) {
  const [products, setProducts] = useState([])
  const [loading, setLoading] = useState(true)
  const [formVisible, setFormVisible] = useState(false)
  const [editingProduct, setEditingProduct] = useState(null)
  const [errors, setErrors] = useState({})
  const [successMessage, setSuccessMessage] = useState('')

  // Check if business package allows products
  const canManageProducts = businessData.package_type === 'Silver' || businessData.package_type === 'Gold'

  // Fetch products on component mount
  useEffect(() => {
    fetchProducts()
  }, [])

  const fetchProducts = async () => {
    try {
      setLoading(true)
      const response = await productsApi.getAll()
      setProducts(response.data || [])
    } catch (error) {
      console.error('Error fetching products:', error)
      setErrors({ form: error.message || 'Failed to load products' })
    } finally {
      setLoading(false)
    }
  }

  const handleCreateProduct = async (formData) => {
    try {
      const response = await productsApi.create(formData)
      
      // Add the new product to the list
      setProducts([...products, response.data])
      
      setSuccessMessage('Product created successfully!')
      setFormVisible(false)
    } catch (error) {
      console.error('Error creating product:', error)
      setErrors({ form: error.message || 'Failed to create product' })
    }
  }

  const handleUpdateProduct = async (formData) => {
    try {
      const response = await productsApi.update(editingProduct.id, formData)
      
      // Update the product in the list
      setProducts(products.map(p => 
        p.id === editingProduct.id ? response.data : p
      ))
      
      setSuccessMessage('Product updated successfully!')
      setFormVisible(false)
      setEditingProduct(null)
    } catch (error) {
      console.error('Error updating product:', error)
      setErrors({ form: error.message || 'Failed to update product' })
    }
  }

  const handleDeleteProduct = async (productId) => {
    if (!window.confirm('Are you sure you want to delete this product?')) return
    
    try {
      await productsApi.delete(productId)
      
      // Remove the deleted product from the list
      setProducts(products.filter(p => p.id !== productId))
      setSuccessMessage('Product deleted successfully!')
    } catch (error) {
      console.error('Error deleting product:', error)
      setErrors({ form: error.message || 'Failed to delete product' })
    }
  }

  const handleEditClick = (product) => {
    setEditingProduct(product)
    setFormVisible(true)
  }

  const handleCancelForm = () => {
    setFormVisible(false)
    setEditingProduct(null)
  }

  // Render upgrade message if package doesn't support products
  if (!canManageProducts) {
    return (
      <div className="text-center py-10">
        <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <h3 className="mt-2 text-lg font-medium text-gray-900">Feature not available</h3>
        <p className="mt-1 text-gray-500">Products management is available for Silver and Gold packages only.</p>
        <div className="mt-6">
          <Link to="/dashboard/upgrade" className="btn btn-primary">
            Upgrade Your Package
          </Link>
        </div>
      </div>
    )
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h2 className="text-2xl font-bold">Products & Services</h2>
        {!formVisible && (
          <button 
            onClick={() => setFormVisible(true)}
            className="btn btn-primary flex items-center"
          >
            <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Product
          </button>
        )}
      </div>

      {/* Success message */}
      {successMessage && (
        <div className="bg-green-50 border border-green-200 text-green-800 rounded-md p-4 mb-6">
          {successMessage}
        </div>
      )}
      
      {/* Error message */}
      {errors.form && (
        <div className="bg-red-50 border border-red-200 text-red-800 rounded-md p-4 mb-6">
          {errors.form}
        </div>
      )}

      {/* Create/Edit Product Form */}
      {formVisible && (
        <ProductForm 
          onSubmit={editingProduct ? handleUpdateProduct : handleCreateProduct} 
          onCancel={handleCancelForm} 
          initialData={editingProduct}
        />
      )}

      {/* Products List */}
      {loading ? (
        <div className="text-center py-10">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-3 text-gray-500">Loading products...</p>
        </div>
      ) : products.length === 0 ? (
        <div className="text-center py-10 bg-white border rounded-lg">
          <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
          </svg>
          <h3 className="mt-2 text-lg font-medium text-gray-900">No products yet</h3>
          <p className="mt-1 text-gray-500">Get started by adding your first product or service.</p>
          <div className="mt-6">
            <button onClick={() => setFormVisible(true)} className="btn btn-primary">
              Add Product
            </button>
          </div>
        </div>
      ) : (
        <div className="space-y-4">
          {products.map(product => (
            <ProductCard 
              key={product.id} 
              product={product} 
              onEdit={handleEditClick}
              onDelete={handleDeleteProduct} 
            />
          ))}
        </div>
      )}
    </div>
  )
}

export default ProductsManagement