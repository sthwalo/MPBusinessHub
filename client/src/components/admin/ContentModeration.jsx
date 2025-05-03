// /client/src/components/admin/ContentModeration.jsx
import React, { useState, useEffect } from 'react';
import axios from 'axios';

function ContentModeration() {
  const [reviews, setReviews] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchPendingReviews();
  }, []);

  const fetchPendingReviews = async () => {
    try {
      setLoading(true);
      const response = await axios.get('/api/admin/reviews/pending', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('mpbh_token')}`
        }
      });
      
      setReviews(response.data.data);
      setLoading(false);
    } catch (error) {
      setError('Failed to load pending reviews. Please try again.');
      setLoading(false);
      console.error('Error fetching pending reviews:', error);
    }
  };

  const handleApproveReview = async (id) => {
    try {
      await axios.put(`/api/admin/reviews/${id}/approve`, {}, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('mpbh_token')}`
        }
      });
      
      // Remove the approved review from the list
      setReviews(reviews.filter(review => review.id !== id));
      alert('Review approved successfully!');
    } catch (error) {
      alert('Failed to approve review. Please try again.');
      console.error('Error approving review:', error);
    }
  };

  const handleRejectReview = async (id) => {
    try {
      await axios.put(`/api/admin/reviews/${id}/reject`, {}, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('mpbh_token')}`
        }
      });
      
      // Remove the rejected review from the list
      setReviews(reviews.filter(review => review.id !== id));
      alert('Review rejected successfully!');
    } catch (error) {
      alert('Failed to reject review. Please try again.');
      console.error('Error rejecting review:', error);
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong className="font-bold">Error!</strong>
        <span className="block sm:inline"> {error}</span>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold mb-6">Content Moderation</h1>
      
      <div className="mb-6">
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-semibold">Pending Reviews</h2>
          <button 
            className="bg-brand-black text-white px-4 py-2 rounded"
            onClick={fetchPendingReviews}
          >
            Refresh
          </button>
        </div>
      </div>
      
      {reviews.length === 0 ? (
        <div className="text-center py-8">
          <p className="text-gray-500">No pending reviews found.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {reviews.map((review) => (
            <div key={review.id} className="bg-white p-6 rounded-lg shadow-md">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="font-bold text-lg">{review.reviewer_name}</h3>
                  <p className="text-gray-500 text-sm">{review.reviewer_email || 'No email provided'}</p>
                </div>
                <div className="flex items-center">
                  {[1, 2, 3, 4, 5].map((star) => (
                    <svg 
                      key={star}
                      className={`h-5 w-5 ${star <= review.rating ? 'text-yellow-500' : 'text-gray-300'}`}
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                  ))}
                </div>
              </div>
              
              <div className="mb-4">
                <h4 className="font-semibold text-gray-700 mb-2">Business:</h4>
                <p>{review.business_name}</p>
              </div>
              
              <div className="mb-6">
                <h4 className="font-semibold text-gray-700 mb-2">Review:</h4>
                <p className="text-gray-600">{review.comment}</p>
              </div>
              
              <div className="flex justify-end space-x-2">
                <button
                  onClick={() => handleRejectReview(review.id)}
                  className="px-4 py-2 bg-red-600 text-white rounded"
                >
                  Reject
                </button>
                <button
                  onClick={() => handleApproveReview(review.id)}
                  className="px-4 py-2 bg-green-600 text-white rounded"
                >
                  Approve
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

export default ContentModeration;