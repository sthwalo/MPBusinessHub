import { memo } from 'react';
import RatingStars from '../../ui/RatingStars';

/**
 * ReviewsList component displays a list of business reviews
 * @param {Object} props
 * @param {Array} props.reviews - Array of review objects
 * @returns {JSX.Element}
 */
const ReviewsList = ({ reviews = [] }) => {
  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h2 className="text-2xl font-bold mb-6">Customer Reviews</h2>
      {Array.isArray(reviews) && reviews.length > 0 ? (
        <div className="space-y-6">
          {reviews.map((review, index) => (
            <div key={review?.id || `review-${index}`} className="border-b pb-6 last:border-b-0 last:pb-0">
              <div className="flex justify-between items-start mb-2">
                <div>
                  {review?.user?.name ? (
                    <div className="font-semibold">{review.user.name}</div>
                  ) : (
                    <div className="font-semibold text-gray-500">Anonymous</div>
                  )}
                  <div className="text-gray-500 text-sm">
                    {review?.created_at ? 
                      new Date(review.created_at).toLocaleDateString() : 
                      'Unknown date'}
                  </div>
                </div>
                <div className="flex items-center">
                  <div className="text-yellow-400">
                    <RatingStars value={Number(review?.rating) || 0} />
                  </div>
                </div>
              </div>
              <p className="text-gray-700">{review?.comment || 'No comment provided'}</p>
            </div>
          ))}
        </div>
      ) : (
        <p className="text-gray-500">No reviews yet. Be the first to leave a review!</p>
      )}
    </div>
  );
};

export default memo(ReviewsList);