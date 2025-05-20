import api from '../utils/api';

export const businessService = {
  getBusinessDetails: async () => {
    try {
      return await api.get('/business/details');
    } catch (error) {
      console.error('Error fetching business details:', error);
      throw error;
    }
  },
  
  getBusinessById: async (id) => {
    try {
      return await api.get(`/businesses/${id}`);
    } catch (error) {
      console.error(`Error fetching business ${id}:`, error);
      throw error;
    }
  },
  
  updateBusiness: async (businessData) => {
    try {
      return await api.put('/business/update', businessData);
    } catch (error) {
      console.error('Error updating business:', error);
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
  },
  

  getFilteredBusinesses: async (filters = {}, page = 1, perPage = 10) => {
    try {
      const queryParams = new URLSearchParams();
      
      if (filters.search) queryParams.append('search', filters.search);
      if (filters.category) queryParams.append('category', filters.category);
      if (filters.district) queryParams.append('district', filters.district);
      
      // Add pagination params
      queryParams.append('page', page);
      queryParams.append('per_page', perPage);
      
      const url = `/businesses/filter?${queryParams.toString()}`;
      
      // Use cache key for this specific request
      return await api.get(url);
    } catch (error) {
      console.error('Error fetching filtered businesses:', error);
      throw error;
    }
  },
  
  // Social media related endpoints
  updateSocialMedia: async (socialMediaData) => {
    try {
      return await api.post('/business/social-media', socialMediaData);
    } catch (error) {
      console.error('Error updating social media:', error);
      throw error;
    }
  },
  
  updateSocialMediaFeature: async (featureData) => {
    try {
      return await api.post('/business/social-media/feature', featureData);
    } catch (error) {
      console.error('Error updating social media feature:', error);
      throw error;
    }
  },
  
  // Image upload
  uploadBusinessImage: async (formData) => {
    try {
      return await api.post('/images/business', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
    } catch (error) {
      console.error('Error uploading business image:', error);
      throw error;
    }
  }
};
