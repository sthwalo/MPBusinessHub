import api from '../utils/api';

export const invoiceService = {
  getInvoices: async () => {
    try {
      return await api.get('/invoices');
    } catch (error) {
      console.error('Error fetching invoices:', error);
      throw error;
    }
  },
  
  downloadInvoice: async (invoiceId) => {
    try {
      return await api.get(`/invoices/${invoiceId}/download`, {
        responseType: 'blob'
      });
    } catch (error) {
      console.error('Error downloading invoice:', error);
      throw error;
    }
  },
  
  viewInvoice: async (invoiceId) => {
    try {
      return await api.get(`/invoices/${invoiceId}`);
    } catch (error) {
      console.error('Error viewing invoice:', error);
      throw error;
    }
  },
  
  sendInvoiceEmail: async (invoiceId) => {
    try {
      return await api.post(`/invoices/${invoiceId}/send`);
    } catch (error) {
      console.error('Error sending invoice email:', error);
      throw error;
    }
  },
  
  initiatePayment: async (paymentData) => {
    try {
      return await api.post('/payments/initiate', paymentData);
    } catch (error) {
      console.error('Error initiating payment:', error);
      throw error;
    }
  }
};
