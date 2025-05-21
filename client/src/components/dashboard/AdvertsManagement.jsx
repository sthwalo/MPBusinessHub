import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { advertsApi } from '../../utils/api'

// Form component for creating/editing adverts
const AdvertForm = ({ onSubmit, onCancel, hasAdverts, businessData }) => {
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    startDate: '',
    endDate: ''
  })
  const [errors, setErrors] = useState({})

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData(prev => ({ ...prev, [name]: value }))
    
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }))
    }
  }

  const validateForm = () => {
    const newErrors = {}
    
    if (!formData.title.trim()) {
      newErrors.title = 'Advert title is required'
    }
    
    if (!formData.description.trim()) {
      newErrors.description = 'Description is required'
    }
    
    if (!formData.startDate) {
      newErrors.startDate = 'Start date is required'
    }
    
    if (!formData.endDate) {
      newErrors.endDate = 'End date is required'
    } else if (new Date(formData.endDate) <= new Date(formData.startDate)) {
      newErrors.endDate = 'End date must be after start date'
    }
    
    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = (e) => {
    e.preventDefault()
    
    if (!validateForm()) return
    
    if (!hasAdverts) {
      setErrors({ form: 'You have no adverts remaining. Please upgrade your package.' })
      return
    }
    
    // Format dates to ensure they're in YYYY-MM-DD format
    const formattedData = {
      ...formData,
      business_id: businessData.id,
      startDate: new Date(formData.startDate).toISOString().split('T')[0],
      endDate: new Date(formData.endDate).toISOString().split('T')[0],
      status: 'scheduled'
    }
    
    onSubmit(formattedData)
  }

  return (
    <div className="bg-white border rounded-lg p-6 mb-6">
      <h3 className="text-lg font-bold mb-4">Create New Advert</h3>
      <form onSubmit={handleSubmit}>
        <div className="mb-4">
          <label htmlFor="title" className="form-label">Advert Title</label>
          <input
            type="text"
            id="title"
            name="title"
            className={`form-control ${errors.title ? 'border-red-500' : ''}`}
            value={formData.title}
            onChange={handleChange}
            placeholder="e.g. Summer Special Offer"
          />
          {errors.title && <p className="text-red-500 text-sm mt-1">{errors.title}</p>}
        </div>
        
        <div className="mb-4">
          <label htmlFor="description" className="form-label">Description</label>
          <textarea
            id="description"
            name="description"
            rows="3"
            className={`form-control ${errors.description ? 'border-red-500' : ''}`}
            value={formData.description}
            onChange={handleChange}
            placeholder="Describe your advert..."
          ></textarea>
          {errors.description && <p className="text-red-500 text-sm mt-1">{errors.description}</p>}
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label htmlFor="startDate" className="form-label">Start Date</label>
            <input
              type="date"
              id="startDate"
              name="startDate"
              className={`form-control ${errors.startDate ? 'border-red-500' : ''}`}
              value={formData.startDate}
              onChange={handleChange}
              min={new Date().toISOString().split('T')[0]}
            />
            {errors.startDate && <p className="text-red-500 text-sm mt-1">{errors.startDate}</p>}
          </div>
          
          <div>
            <label htmlFor="endDate" className="form-label">End Date</label>
            <input
              type="date"
              id="endDate"
              name="endDate"
              className={`form-control ${errors.endDate ? 'border-red-500' : ''}`}
              value={formData.endDate}
              onChange={handleChange}
              min={formData.startDate || new Date().toISOString().split('T')[0]}
            />
            {errors.endDate && <p className="text-red-500 text-sm mt-1">{errors.endDate}</p>}
          </div>
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
            disabled={!hasAdverts}
          >
            Create Advert
          </button>
        </div>
      </form>
    </div>
  )
}

// Individual advert card component
const AdvertCard = ({ advert, onDelete }) => {
  return (
    <div className="bg-white rounded-lg shadow-sm border p-6">
      <div className="flex justify-between items-start">
        <div>
          <div className="flex items-center mb-2">
            <h3 className="text-lg font-bold">{advert.title}</h3>
            <span className={`ml-3 text-xs px-2 py-1 rounded-full ${advert.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}`}>
              {advert.status === 'active' ? 'Active' : 'Scheduled'}
            </span>
          </div>
          <p className="text-gray-600 mb-2">{advert.description}</p>
          <div className="text-sm text-gray-500">
            From {new Date(advert.start_date || advert.startDate).toLocaleDateString()} to {new Date(advert.end_date || advert.endDate).toLocaleDateString()}
          </div>
        </div>
        <button 
          onClick={() => onDelete(advert.id)}
          className="text-red-600 hover:text-red-800"
          title="Delete advert"
        >
          <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>
    </div>
  )
}

// Main component
function AdvertsManagement({ businessData, onAdvertCreated }) {
  const [adverts, setAdverts] = useState([])
  const [loading, setLoading] = useState(true)
  const [formVisible, setFormVisible] = useState(false)
  const [errors, setErrors] = useState({})
  const [successMessage, setSuccessMessage] = useState('')

  // Check if business has available adverts
  const hasAdverts = businessData.adverts_remaining > 0
  const canCreateAdverts = businessData.package_type === 'Silver' || businessData.package_type === 'Gold'

  // Fetch adverts on component mount
  useEffect(() => {
    fetchAdverts()
  }, [])

  const fetchAdverts = async () => {
    try {
      setLoading(true)
      const response = await advertsApi.getAll()
      setAdverts(response.data || [])
    } catch (error) {
      console.error('Error fetching adverts:', error)
      setErrors({ form: error.message || 'Failed to load adverts' })
    } finally {
      setLoading(false)
    }
  }

  const handleCreateAdvert = async (formData) => {
    try {
      setLoading(true);
      const response = await advertsApi.create(formData);
      
      // Add the new advert to the list
      setAdverts(prev => [response.data, ...prev]);
      
      // Update the adverts remaining count in the parent component
      if (typeof onAdvertCreated === 'function') {
        onAdvertCreated();
      }
      
      setSuccessMessage('Advert created successfully!');
      setFormVisible(false);
      setErrors({});
    } catch (error) {
      setErrors({ form: error.message || 'Failed to create advert' });
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteAdvert = async (advertId) => {
    if (!window.confirm('Are you sure you want to delete this advert?')) return
    
    try {
      await advertsApi.delete(advertId)
      
      // Remove the deleted advert from the list
      setAdverts(adverts.filter(a => a.id !== advertId))
      setSuccessMessage('Advert deleted successfully!')
    } catch (error) {
      console.error('Error deleting advert:', error)
      setErrors({ form: error.message || 'Failed to delete advert' })
    }
  }

  // Render upgrade message if package doesn't support adverts
  if (!canCreateAdverts) {
    return (
      <div className="text-center py-10">
        <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
        </svg>
        <h3 className="mt-2 text-lg font-medium text-gray-900">Feature not available</h3>
        <p className="mt-1 text-gray-500">Adverts are available for Silver and Gold packages only.</p>
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
        <h2 className="text-2xl font-bold">Adverts Management</h2>
        {!formVisible && hasAdverts && (
          <button 
            onClick={() => setFormVisible(true)}
            className="btn btn-primary flex items-center"
          >
            <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Create New Advert
          </button>
        )}
      </div>

      {/* Adverts remaining counter */}
      <div className="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-6">
        <div className="flex items-center">
          <svg className="h-6 w-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
          </svg>
          <div>
            <p className="font-medium">
              {businessData.adverts_remaining} {businessData.adverts_remaining === 2 ? 'advert' : 'adverts'} remaining this month
            </p>
            <p className="text-sm text-blue-600">
              {businessData.package_type === 'Silver' ? '2 adverts per month with Silver package' : '4 adverts per month with Gold package'}
            </p>
          </div>
        </div>
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

      {/* Create Advert Form */}
      {formVisible && (
        <AdvertForm 
          onSubmit={handleCreateAdvert} 
          onCancel={() => setFormVisible(false)} 
          hasAdverts={hasAdverts} 
          businessData={businessData}
        />
      )}

      {/* Adverts List */}
      {loading ? (
        <div className="text-center py-10">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-3 text-gray-500">Loading adverts...</p>
        </div>
      ) : adverts.length === 0 ? (
        <div className="text-center py-10 bg-white border rounded-lg">
          <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
          </svg>
          <h3 className="mt-2 text-lg font-medium text-gray-900">No adverts yet</h3>
          <p className="mt-1 text-gray-500">Get started by creating your first advert to promote your business.</p>
          {hasAdverts ? (
            <div className="mt-6">
              <button onClick={() => setFormVisible(true)} className="btn btn-primary">
                Create Advert
              </button>
            </div>
          ) : (
            <div className="mt-6">
              <Link to="/dashboard/upgrade" className="btn btn-primary">
                Upgrade For More Adverts
              </Link>
            </div>
          )}
        </div>
      ) : (
        <div className="space-y-4">
          {adverts.map(advert => (
            <AdvertCard 
              key={advert.id} 
              advert={advert} 
              onDelete={handleDeleteAdvert} 
            />
          ))}
        </div>
      )}
    </div>
  )
}

export default AdvertsManagement
