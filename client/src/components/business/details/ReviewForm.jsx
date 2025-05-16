import { memo, useState } from 'react';
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
 * @param {Function} props.handleAnonymousReviewSubmit - Function to handle anonymous review submission
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
  handleReviewSubmit,
  handleAnonymousReviewSubmit
}) => {
  const [showAnonymousForm, setShowAnonymousForm] = useState(false);
  const [anonymousName, setAnonymousName] = useState('');
  const [anonymousEmail, setAnonymousEmail] = useState('');
  const [anonymousRating, setAnonymousRating] = useState(0);
  const [anonymousReviewText, setAnonymousReviewText] = useState('');
  
  if (!isAuthenticated && !showAnonymousForm) {
    return (
      <div className="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 className="text-xl font-bold mb-4">Write a Review</h2>
        <div className="flex flex-col space-y-4">
          <button 
            onClick={() => setShowAnonymousForm(true)}
            className="btn bg-brand-black text-white hover:bg-brand-gray-800"
          >
            Continue as Guest
          </button>
          <div className="flex items-center justify-center">
            <div className="flex-grow border-t border-gray-300"></div>
            <span className="mx-4 text-gray-500 text-sm">OR</span>
            <div className="flex-grow border-t border-gray-300"></div>
          </div>
          <Link to="/login" className="btn border border-brand-black text-brand-black hover:bg-gray-100">
            Log In to Review
          </Link>
        </div>
      </div>
    );
  }
  
  if (!isAuthenticated && showAnonymousForm) {
    const handleSubmit = (e) => {
      e.preventDefault();
      handleAnonymousReviewSubmit({
        rating: anonymousRating,
        comment: anonymousReviewText,
        reviewer_name: anonymousName,
        reviewer_email: anonymousEmail
      });
    };
    
    return (
      <div className="bg-white rounded-lg shadow-md p-6 mb-6">
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-xl font-bold">Write a Review as Guest</h2>
          <button 
            onClick={() => setShowAnonymousForm(false)}
            className="text-sm text-gray-500 hover:text-gray-700"
          >
            Back
          </button>
        </div>
        
        {reviewSuccess && (
          <div className="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
            <p className="text-green-700">Your review has been submitted successfully! It will be visible after moderation.</p>
          </div>
        )}
        
        <form onSubmit={handleSubmit}>
          <div className="mb-4">
            <label className="block text-gray-700 mb-2">Your Name *</label>
            <input
              type="text"
              value={anonymousName}
              onChange={(e) => setAnonymousName(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Your name"
              required
            />
          </div>
          
          <div className="mb-4">
            <label className="block text-gray-700 mb-2">Your Email (optional)</label>
            <input
              type="email"
              value={anonymousEmail}
              onChange={(e) => setAnonymousEmail(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Your email (optional)"
            />
          </div>
          
          <div className="mb-4">
            <label className="block text-gray-700 mb-2">Rating *</label>
            <RatingStars interactive={true} value={anonymousRating} onChange={setAnonymousRating} />
          </div>
          
          <div className="mb-4">
            <label htmlFor="anonymous-review" className="block text-gray-700 mb-2">Your Review</label>
            <textarea
              id="anonymous-review"
              value={anonymousReviewText}
              onChange={(e) => setAnonymousReviewText(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows="4"
              placeholder="Share your experience with this business..."
            ></textarea>
          </div>
          
          <button 
            type="submit" 
            className="btn btn-primary w-full"
            disabled={isSubmitting || anonymousRating === 0 || !anonymousName}
          >
            {isSubmitting ? 'Submitting...' : 'Submit Review'}
          </button>
          
          <p className="text-xs text-gray-500 mt-2">* Required fields. Reviews submitted as a guest will be moderated before appearing.</p>
        </form>
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