import api from '../utils/api';

export const reviewService = {
  submitReview: async (reviewData) => {
    try {
      return await api.post('/reviews', reviewData);
    } catch (error) {
      console.error('Error submitting review:', error);
      throw error;
    }
  },
  
  submitAnonymousReview: async (reviewData) => {
    try {
      return await api.post('/reviews/anonymous', reviewData);
    } catch (error) {
      console.error('Error submitting anonymous review:', error);
      throw error;
    }
  },
  
  getPendingReviews: async () => {
    try {
      return await api.get('/admin/reviews/pending');
    } catch (error) {
      console.error('Error fetching pending reviews:', error);
      throw error;
    }
  }
};
