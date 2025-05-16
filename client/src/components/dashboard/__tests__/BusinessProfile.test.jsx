import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import BusinessProfile from '../BusinessProfile';
import { businessMetadataApi } from '../../../utils/api';

// Mock the API module
vi.mock('../../../utils/api', () => ({
  businessMetadataApi: {
    getCategories: vi.fn(),
    getDistricts: vi.fn()
  }
}));

// Mock fetch
global.fetch = vi.fn();

// Mock components used by BusinessProfile
vi.mock('../../ui/ErrorMessage', () => ({
  default: ({ message }) => <div data-testid="error-message">{message}</div>
}));

vi.mock('../../ui/FormComponents', () => ({
  FormSection: ({ title, children }) => (
    <div data-testid="form-section">
      <h3>{title}</h3>
      {children}
    </div>
  ),
  SuccessMessage: ({ message }) => message ? <div data-testid="success-message">{message}</div> : null
}));

vi.mock('../ImageUpload', () => ({
  default: ({ businessImage, onImageUpload }) => (
    <div data-testid="image-upload">
      <button onClick={() => onImageUpload('https://example.com/new-image.jpg')}>
        Upload Image
      </button>
      {businessImage && <img src={businessImage} alt="Business" />}
    </div>
  )
}));

vi.mock('../profile/BasicInfoSection', () => ({
  default: ({ formData, handleChange, errors, categories, districts, isLoadingMetadata }) => (
    <div data-testid="basic-info-section">
      <select 
        data-testid="category-select" 
        name="category" 
        value={formData.category} 
        onChange={handleChange}
        disabled={isLoadingMetadata}
      >
        <option value="">Select a category</option>
        {isLoadingMetadata ? (
          <option>Loading categories...</option>
        ) : (
          categories.map(category => (
            <option key={category} value={category}>{category}</option>
          ))
        )}
      </select>
      <select 
        data-testid="district-select" 
        name="district" 
        value={formData.district} 
        onChange={handleChange}
        disabled={isLoadingMetadata}
      >
        <option value="">Select a district</option>
        {isLoadingMetadata ? (
          <option>Loading districts...</option>
        ) : (
          districts.map(district => (
            <option key={district} value={district}>{district}</option>
          ))
        )}
      </select>
    </div>
  )
}));

vi.mock('../profile/ContactInfoSection', () => ({
  default: ({ formData, handleChange, errors }) => (
    <div data-testid="contact-info-section">
      <input 
        data-testid="email-input" 
        name="email" 
        value={formData.email} 
        onChange={handleChange}
      />
    </div>
  )
}));

describe('BusinessProfile Component', () => {
  const mockBusinessData = {
    id: 1,
    name: 'Test Business',
    category: 'Tourism',
    district: 'Mbombela',
    description: 'Test description',
    phone: '123456789',
    email: 'test@example.com',
    website: 'https://example.com',
    address: 'Test Address',
    image_url: 'https://example.com/image.jpg',
    package_type: 'Basic'
  };
  
  const mockUpdateBusinessData = vi.fn();
  
  beforeEach(() => {
    vi.clearAllMocks();
    
    // Mock localStorage
    Object.defineProperty(window, 'localStorage', {
      value: {
        getItem: vi.fn(() => 'fake-token'),
        setItem: vi.fn(),
        removeItem: vi.fn()
      },
      writable: true
    });
    
    // Mock fetch for form submission
    global.fetch.mockResolvedValue({
      ok: true,
      json: async () => ({
        status: 'success',
        message: 'Profile updated successfully!',
        data: { ...mockBusinessData, name: 'Updated Business' }
      })
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

  const renderBusinessProfile = () => {
    return render(
      <BusinessProfile 
        businessData={mockBusinessData}
        updateBusinessData={mockUpdateBusinessData}
      />
    );
  };

  it('fetches categories and districts on mount', async () => {
    renderBusinessProfile();
    
    // Verify API calls were made
    expect(businessMetadataApi.getCategories).toHaveBeenCalledTimes(1);
    expect(businessMetadataApi.getDistricts).toHaveBeenCalledTimes(1);
    
    // Wait for loading state to resolve
    await waitFor(() => {
      expect(screen.getByText('Business Profile')).toBeInTheDocument();
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
    
    renderBusinessProfile();
    
    // Verify form is rendered with loading state
    expect(screen.getByTestId('basic-info-section')).toBeInTheDocument();
    
    // Wait for categories and districts to load
    await waitFor(() => {
      expect(businessMetadataApi.getCategories).toHaveBeenCalledTimes(1);
      expect(businessMetadataApi.getDistricts).toHaveBeenCalledTimes(1);
    });
  });
  
  it('falls back to default values if API fails', async () => {
    // Mock API failures
    businessMetadataApi.getCategories.mockRejectedValue(new Error('API error'));
    businessMetadataApi.getDistricts.mockRejectedValue(new Error('API error'));
    
    renderBusinessProfile();
    
    // Wait for fallback values to be used
    await waitFor(() => {
      const categorySelect = screen.getByTestId('category-select');
      expect(categorySelect).toBeInTheDocument();
      
      // Check that the select contains the fallback options
      expect(categorySelect).toContainHTML('Tourism');
      expect(categorySelect).toContainHTML('Agriculture');
      expect(categorySelect).toContainHTML('Construction');
      expect(categorySelect).toContainHTML('Events');
      
      const districtSelect = screen.getByTestId('district-select');
      expect(districtSelect).toContainHTML('Mbombela');
      expect(districtSelect).toContainHTML('Emalahleni');
      expect(districtSelect).toContainHTML('Bushbuckridge');
    });
  });
  
  it('uses categories and districts from API in the form', async () => {
    // Mock API with custom values
    businessMetadataApi.getCategories.mockResolvedValue({
      status: 'success',
      data: ['Technology', 'Healthcare', 'Education']
    });
    
    businessMetadataApi.getDistricts.mockResolvedValue({
      status: 'success',
      data: ['Pretoria', 'Johannesburg', 'Cape Town']
    });
    
    renderBusinessProfile();
    
    // Wait for custom categories to be available
    await waitFor(() => {
      const categorySelect = screen.getByTestId('category-select');
      expect(categorySelect).toContainHTML('Technology');
      expect(categorySelect).toContainHTML('Healthcare');
      expect(categorySelect).toContainHTML('Education');
      
      const districtSelect = screen.getByTestId('district-select');
      expect(districtSelect).toContainHTML('Pretoria');
      expect(districtSelect).toContainHTML('Johannesburg');
      expect(districtSelect).toContainHTML('Cape Town');
    });
  });
  
  it('submits form with updated data', async () => {
    renderBusinessProfile();
    
    // Wait for form to load
    await waitFor(() => {
      expect(screen.getByText('Business Profile')).toBeInTheDocument();
    });
    
    // Submit the form
    fireEvent.click(screen.getByRole('button', { name: /Save Changes/i }));
    
    // Check that fetch was called with the right data
    await waitFor(() => {
      expect(global.fetch).toHaveBeenCalledWith('/api/business/update', expect.objectContaining({
        method: 'POST',
        headers: expect.objectContaining({
          'Content-Type': 'application/json',
          'Authorization': 'Bearer fake-token'
        }),
        body: expect.any(String)
      }));
      
      // Check that the callback was called with updated data
      expect(mockUpdateBusinessData).toHaveBeenCalledWith(
        expect.objectContaining({ name: 'Updated Business' })
      );
      
      // Check that success message is displayed
      expect(screen.getByTestId('success-message')).toBeInTheDocument();
    });
  });
});
