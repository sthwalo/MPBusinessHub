import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { BrowserRouter, useSearchParams } from 'react-router-dom';
import BusinessDirectory from '../BusinessDirectory';
import { businessMetadataApi } from '../../utils/api';

// Mock the API module
vi.mock('../../utils/api', () => ({
  businessMetadataApi: {
    getCategories: vi.fn(),
    getDistricts: vi.fn()
  }
}));

// Mock useSearchParams
vi.mock('react-router-dom', async () => {
  const actual = await vi.importActual('react-router-dom');
  return {
    ...actual,
    useSearchParams: vi.fn(() => [
      {
        get: vi.fn((param) => null),
      },
      vi.fn(),
    ]),
  };
});

// Mock fetch
global.fetch = vi.fn();

describe('BusinessDirectory Component', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    
    // Mock successful API responses for businesses
    global.fetch.mockResolvedValue({
      ok: true,
      json: async () => ({
        status: 'success',
        data: [
          {
            id: 1,
            name: 'Test Business 1',
            category: 'Tourism',
            district: 'Mbombela',
            description: 'Test description',
            rating: 4.5,
            review_count: 10,
            contact: {
              phone: '123456789',
              email: 'test@example.com',
              website: 'https://example.com',
              address: 'Test Address'
            },
            image_url: 'https://example.com/image.jpg'
          },
          {
            id: 2,
            name: 'Test Business 2',
            category: 'Agriculture',
            district: 'Emalahleni',
            description: 'Another test description',
            rating: 3.5,
            review_count: 5,
            contact: {
              phone: '987654321',
              email: 'test2@example.com',
              website: 'https://example2.com',
              address: 'Test Address 2'
            },
            image_url: 'https://example.com/image2.jpg'
          }
        ]
      })
    });
    
    // Mock successful API responses for adverts
    global.fetch.mockImplementation((url) => {
      if (url.includes('/api/adverts/active')) {
        return Promise.resolve({
          ok: true,
          json: async () => ({
            status: 'success',
            data: []
          })
        });
      }
      return Promise.resolve({
        ok: true,
        json: async () => ({
          status: 'success',
          data: [/* businesses data */]
        })
      });
    });
    
    // Mock successful API responses for categories and districts
    businessMetadataApi.getCategories.mockResolvedValue({
      status: 'success',
      data: ['Tourism', 'Agriculture', 'Construction', 'Events']
    });
    
    businessMetadataApi.getDistricts.mockResolvedValue({
      status: 'success',
      data: ['Mbombela', 'Emalahleni', 'Bushbuckridge']
    });
  });

  const renderBusinessDirectory = () => {
    return render(
      <BrowserRouter>
        <BusinessDirectory />
      </BrowserRouter>
    );
  };

  it('fetches categories and districts on mount', async () => {
    renderBusinessDirectory();
    
    // Verify API calls were made
    expect(businessMetadataApi.getCategories).toHaveBeenCalledTimes(1);
    expect(businessMetadataApi.getDistricts).toHaveBeenCalledTimes(1);
    
    // Wait for loading state to resolve
    await waitFor(() => {
      expect(screen.getByText('Business Directory')).toBeInTheDocument();
    });
  });
  
  it('displays loading state while fetching metadata', async () => {
    // Delay the API responses
    businessMetadataApi.getCategories.mockImplementation(
      () => new Promise(resolve => setTimeout(() => resolve({
        status: 'success',
        data: ['Tourism', 'Agriculture']
      }), 100))
    );
    
    businessMetadataApi.getDistricts.mockImplementation(
      () => new Promise(resolve => setTimeout(() => resolve({
        status: 'success',
        data: ['Mbombela', 'Emalahleni']
      }), 100))
    );
    
    renderBusinessDirectory();
    
    // Verify loading state is shown
    expect(screen.getByText('Loading categories...')).toBeInTheDocument();
    expect(screen.getByText('Loading districts...')).toBeInTheDocument();
    
    // Wait for categories and districts to load
    await waitFor(() => {
      expect(screen.queryByText('Loading categories...')).not.toBeInTheDocument();
      expect(screen.queryByText('Loading districts...')).not.toBeInTheDocument();
    });
  });
  
  it('falls back to default values if API fails', async () => {
    // Mock API failures
    businessMetadataApi.getCategories.mockRejectedValue(new Error('API error'));
    businessMetadataApi.getDistricts.mockRejectedValue(new Error('API error'));
    
    renderBusinessDirectory();
    
    // Wait for fallback values to be used
    await waitFor(() => {
      expect(screen.getByText('Tourism')).toBeInTheDocument();
      expect(screen.getByText('Agriculture')).toBeInTheDocument();
      expect(screen.getByText('Construction')).toBeInTheDocument();
      expect(screen.getByText('Events')).toBeInTheDocument();
      
      expect(screen.getByText('Mbombela')).toBeInTheDocument();
      expect(screen.getByText('Emalahleni')).toBeInTheDocument();
      expect(screen.getByText('Bushbuckridge')).toBeInTheDocument();
    });
  });
  
  it('uses categories and districts from API in the filters', async () => {
    // Mock API with custom values
    businessMetadataApi.getCategories.mockResolvedValue({
      status: 'success',
      data: ['Technology', 'Healthcare', 'Education']
    });
    
    businessMetadataApi.getDistricts.mockResolvedValue({
      status: 'success',
      data: ['Pretoria', 'Johannesburg', 'Cape Town']
    });
    
    renderBusinessDirectory();
    
    // Wait for custom categories to be available
    await waitFor(() => {
      expect(screen.getByText('Technology')).toBeInTheDocument();
      expect(screen.getByText('Healthcare')).toBeInTheDocument();
      expect(screen.getByText('Education')).toBeInTheDocument();
      
      expect(screen.getByText('Pretoria')).toBeInTheDocument();
      expect(screen.getByText('Johannesburg')).toBeInTheDocument();
      expect(screen.getByText('Cape Town')).toBeInTheDocument();
    });
  });
  
  it('filters businesses when category is selected', async () => {
    renderBusinessDirectory();
    
    // Wait for businesses to load
    await waitFor(() => {
      expect(screen.getByText('Test Business 1')).toBeInTheDocument();
      expect(screen.getByText('Test Business 2')).toBeInTheDocument();
    });
    
    // Click on a category filter
    fireEvent.click(screen.getByText('Tourism'));
    
    // Check that URL params are updated
    expect(useSearchParams()[1]).toHaveBeenCalled();
  });
});
