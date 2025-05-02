import { memo } from 'react';
import { Link } from 'react-router-dom';
import RatingStars from '../../ui/RatingStars';

/**
 * ReviewForm component for submitting business reviews
 * @param {Object} props
 * @param {number} props.userRating - Current user rating
 * @param {string} props.reviewText - Current review text
 * @param {boolean} props.isSubmitting - Whether form is submitting
 * @param {boolean} props.reviewSuccess - Whether submission was successful
 * @param {boolean} props.isAuthenticated - Whether user is authenticated
 * @param {Function} props.setReviewText - Function to update review text
 * @param {Function} props.handleRatingChange - Function to handle rating change
 * @param {Function} props.handleReviewSubmit - Function to handle form submission
 * @returns {JSX.Element}
 */
const ReviewForm = ({
  userRating,
  reviewText,
  isSubmitting,
  reviewSuccess,
  isAuthenticated,
  setReviewText,
  handleRatingChange,
  handleReviewSubmit
}) => {
  if (!isAuthenticated) {
    return (
      <div className="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 className="text-xl font-bold mb-4">Write a Review</h2>
        <p className="text-gray-600 mb-4">Please log in to leave a review.</p>
        <Link to="/login" className="btn btn-primary">Log In</Link>
      </div>
    );
  }
  
  return (
    <div className="bg-white rounded-lg shadow-md p-6 mb-6">
      <h2 className="text-xl font-bold mb-4">Write a Review</h2>
      
      {reviewSuccess && (
        <div className="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
          <p className="text-green-700">Your review has been submitted successfully!</p>
        </div>
      )}
      
      <form onSubmit={handleReviewSubmit}>
        <div className="mb-4">
          <label className="block text-gray-700 mb-2">Rating</label>
          <RatingStars interactive={true} value={userRating} onChange={handleRatingChange} />
        </div>
        
        <div className="mb-4">
          <label htmlFor="review" className="block text-gray-700 mb-2">Your Review</label>
          <textarea
            id="review"
            value={reviewText}
            onChange={(e) => setReviewText(e.target.value)}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            rows="4"
            placeholder="Share your experience with this business..."
          ></textarea>
        </div>
        
        <button 
          type="submit" 
          className="btn btn-primary w-full"
          disabled={isSubmitting}
        >
          {isSubmitting ? 'Submitting...' : 'Submit Review'}
        </button>
      </form>
    </div>
  );
};

export default memo(ReviewForm);