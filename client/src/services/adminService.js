import api from '../utils/api';

export const adminService = {
  getPendingBusinesses: async () => {
    try {
      return await api.get('/admin/businesses/pending');
    } catch (error) {
      console.error('Error fetching pending businesses:', error);
      throw error;
    }
  },
  
  getDashboardStats: async () => {
    try {
      return await api.get('/admin/dashboard');
    } catch (error) {
      console.error('Error fetching dashboard stats:', error);
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
  },
  
  getStatistics: async () => {
    try {
      return await api.get('/admin/statistics');
    } catch (error) {
      console.error('Error fetching statistics:', error);
      throw error;
    }
  },
  
  checkBusinesses: async () => {
    try {
      return await api.get('/businesses/check');
    } catch (error) {
      console.error('Error checking businesses:', error);
      throw error;
    }
  }
};