import { memo } from 'react';
import RatingStars from '../../ui/RatingStars';
import { iconPaths } from '../../../utils/businessUtils';

/**
 * BusinessHeader component displays the main business information header
 * @param {Object} props
 * @param {Object} props.business - Business data object
 * @returns {JSX.Element}
 */
const BusinessHeader = ({ business }) => {
  if (!business) return null;
  
  return (
    <div className="mb-8">
      <h1 className="text-3xl font-bold mb-2">{business.name}</h1>
      
      {business.district && (
        <div className="flex items-center text-gray-600 mb-1">
          <svg className="h-5 w-5 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={iconPaths.location} />
          </svg>
          <span>{business.district}</span>
        </div>
      )}
      
      {business.category && (
        <div className="flex items-center text-gray-600 mb-4">
          <svg className="h-5 w-5 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={iconPaths.category} />
          </svg>
          <span>{business.category}</span>
        </div>
      )}
      
      {business.rating !== undefined && business.rating !== null && (
        <div className="flex items-center mb-4">
          <RatingStars value={Number(business.rating) || 0} />
          <span className="ml-2 text-gray-600">
            {business.rating ? business.rating.toFixed(1) : 'No ratings'}
            <span className="ml-1">({business.review_count || business.reviews?.length || 0} {(business.review_count === 1 || business.reviews?.length === 1) ? 'review' : 'reviews'})</span>
          </span>
        </div>
      )}
    </div>
  );
};

export default memo(BusinessHeader);