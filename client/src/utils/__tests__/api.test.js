import axios from 'axios';
import { businessMetadataApi } from '../api';

// Mock axios
jest.mock('axios');

describe('Business Metadata API', () => {
  // Reset mocks before each test
  beforeEach(() => {
    jest.clearAllMocks();
  });

  describe('getCategories', () => {
    it('should fetch categories successfully', async () => {
      // Mock successful response
      const mockResponse = {
        status: 'success',
        data: ['Tourism', 'Agriculture', 'Construction', 'Events']
      };
      
      axios.get.mockResolvedValueOnce({ data: mockResponse });
      
      // Call the function
      const result = await businessMetadataApi.getCategories();
      
      // Assert axios was called with the correct URL
      expect(axios.get).toHaveBeenCalledWith('/api/categories');
      
      // Assert the result matches the mock response
      expect(result).toEqual(mockResponse);
    });
    
    it('should handle errors when fetching categories', async () => {
      // Mock error response
      const errorMessage = 'Network Error';
      axios.get.mockRejectedValueOnce(new Error(errorMessage));
      
      // Call the function and expect it to throw
      await expect(businessMetadataApi.getCategories()).rejects.toThrow(errorMessage);
      
      // Assert axios was called with the correct URL
      expect(axios.get).toHaveBeenCalledWith('/api/categories');
    });
  });
  
  describe('getDistricts', () => {
    it('should fetch districts successfully', async () => {
      // Mock successful response
      const mockResponse = {
        status: 'success',
        data: ['Mbombela', 'Emalahleni', 'Bushbuckridge']
      };
      
      axios.get.mockResolvedValueOnce({ data: mockResponse });
      
      // Call the function
      const result = await businessMetadataApi.getDistricts();
      
      // Assert axios was called with the correct URL
      expect(axios.get).toHaveBeenCalledWith('/api/districts');
      
      // Assert the result matches the mock response
      expect(result).toEqual(mockResponse);
    });
    
    it('should handle errors when fetching districts', async () => {
      // Mock error response
      const errorMessage = 'Network Error';
      axios.get.mockRejectedValueOnce(new Error(errorMessage));
      
      // Call the function and expect it to throw
      await expect(businessMetadataApi.getDistricts()).rejects.toThrow(errorMessage);
      
      // Assert axios was called with the correct URL
      expect(axios.get).toHaveBeenCalledWith('/api/districts');
    });
  });
});
