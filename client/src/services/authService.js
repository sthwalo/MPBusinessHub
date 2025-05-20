import api from '../utils/api';

export const authService = {
  login: async (credentials) => {
    try {
      return await api.post('/auth/login', credentials);
    } catch (error) {
      console.error('Error during login:', error);
      throw error;
    }
  },
  
  register: async (userData) => {
    try {
      return await api.post('/businesses/register', userData);
    } catch (error) {
      console.error('Error during registration:', error);
      throw error;
    }
  },
  
  sendVerificationEmail: async () => {
    try {
      return await api.post('/email/verification-notification');
    } catch (error) {
      console.error('Error sending verification email:', error);
      throw error;
    }
  },
  
  verifyEmail: async (id, hash, expires, signature) => {
    try {
      return await api.get(`/email/verify/${id}/${hash}?expires=${expires}&signature=${signature}`);
    } catch (error) {
      console.error('Error verifying email:', error);
      throw error;
    }
  },
  
  forgotPassword: async (email) => {
    try {
      return await api.post('/password/email', { email });
    } catch (error) {
      console.error('Error requesting password reset:', error);
      throw error;
    }
  },
  
  resetPassword: async (resetData) => {
    try {
      return await api.post('/password/reset', resetData);
    } catch (error) {
      console.error('Error resetting password:', error);
      throw error;
    }
  }
};
