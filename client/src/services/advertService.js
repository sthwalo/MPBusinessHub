import api from '../utils/api';

export const advertService = {
  getActiveAdverts: async () => {
    try {
      return await api.get('/adverts/active');
    } catch (error) {
      console.error('Error fetching active adverts:', error);
      throw error;
    }
  },
  
  createAdvert: async (advertData) => {
    try {
      return await api.post('/adverts', advertData);
    } catch (error) {
      console.error('Error creating advert:', error);
      throw error;
    }
  },
  
  updateAdvert: async (advertId, advertData) => {
    try {
      return await api.put(`/adverts/${advertId}`, advertData);
    } catch (error) {
      console.error('Error updating advert:', error);
      throw error;
    }
  },
  
  deleteAdvert: async (advertId) => {
    try {
      return await api.delete(`/adverts/${advertId}`);
    } catch (error) {
      console.error('Error deleting advert:', error);
      throw error;
    }
  }
};

export const packageService = {
  getPackages: async () => {
    try {
      return await api.get('/packages');
    } catch (error) {
      console.error('Error fetching packages:', error);
      throw error;
    }
  },
  
  upgradePlan: async (planData) => {
    try {
      return await api.post('/businesses/upgrade-plan', planData);
    } catch (error) {
      console.error('Error upgrading plan:', error);
      throw error;
    }
  }
};
