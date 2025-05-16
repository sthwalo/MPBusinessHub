import { memo } from 'react';
import { packageFeatures } from '../../../utils/businessUtils';

/**
 * ProductsGrid component displays business products in a grid layout
 * @param {Object} props
 * @param {Object} props.business - Business data object
 * @returns {JSX.Element}
 */
const ProductsGrid = ({ business }) => {
  if (!business) return null;
  
  const packageType = business.package_type || 'Basic';
  const canShowProducts = packageFeatures[packageType]?.products || false;
  
  if (!canShowProducts) return null;
  
  return (
    <div className="mt-6">
      <h3 className="text-lg font-bold mb-3">Products & Services</h3>
      {business.products && business.products.length > 0 ? (
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
          {business.products.map((product, index) => (
            <div key={product?.id || `product-${index}`} className="border rounded-lg p-4">
              <h4 className="font-bold">{product?.name || 'Unnamed Product'}</h4>
              <p className="text-gray-600">{product?.description || 'No description'}</p>
              {product?.price && (
                <p className="font-bold mt-2">R{parseFloat(product.price).toFixed(2)}</p>
              )}
            </div>
          ))}
        </div>
      ) : (
        <p className="text-gray-500">No products or services listed yet.</p>
      )}
    </div>
  );
};

export default memo(ProductsGrid);