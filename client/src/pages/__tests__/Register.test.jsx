import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import Register from '../Register';
import toast from 'react-hot-toast';
import { businessMetadataApi } from '../../utils/api';

// Mock the react-hot-toast
vi.mock('react-hot-toast', () => ({
  default: {
    success: vi.fn(),
    error: vi.fn(),
    loading: vi.fn().mockReturnValue('mocked-toast-id'),
    dismiss: vi.fn(),
    warning: vi.fn()
  }
}));

// Mock businessMetadataApi
vi.mock('../../utils/api', () => ({
  businessMetadataApi: {
    getCategories: vi.fn(),
    getDistricts: vi.fn()
  }
}));

// Mock fetch
global.fetch = vi.fn();

// Mock useNavigate
const mockNavigate = vi.fn();
vi.mock('react-router-dom', async () => {
  const actual = await vi.importActual('react-router-dom');
  return {
    ...actual,
    useNavigate: () => mockNavigate
  };
});

describe('Register Component', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    
    // Reset localStorage mock
    Object.defineProperty(window, 'localStorage', {
      value: {
        getItem: vi.fn(),
        setItem: vi.fn(),
        removeItem: vi.fn()
      },
      writable: true
    });
  });

  const renderRegisterComponent = () => {
    return render(
      <BrowserRouter>
        <Register />
      </BrowserRouter>
    );
  };

  it('renders registration form step 1 correctly', () => {
    renderRegisterComponent();
    
    expect(screen.getByText('Register Your Business')).toBeInTheDocument();
    expect(screen.getByLabelText(/Business Name/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/Category/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/District/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/Description/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /Next/i })).toBeInTheDocument();
    expect(screen.getByText(/Already have an account/i)).toBeInTheDocument();
  });

  it('shows validation errors for empty step 1 submission', async () => {
    renderRegisterComponent();
    
    // Try to go to next step without entering data
    fireEvent.click(screen.getByRole('button', { name: /Next/i }));
    
    // Check for validation errors
    expect(await screen.findByText('Business name is required')).toBeInTheDocument();
    expect(screen.getByText('Please select a category')).toBeInTheDocument();
    expect(screen.getByText('Please select a district')).toBeInTheDocument();
    expect(screen.getByText('Business description is required')).toBeInTheDocument();
  });

  it('shows validation error for short description in step 1', async () => {
    renderRegisterComponent();
    
    // Fill in form with short description
    fireEvent.change(screen.getByLabelText(/Business Name/i), {
      target: { value: 'Test Business' }
    });
    
    fireEvent.change(screen.getByLabelText(/Category/i), {
      target: { value: 'Tourism' }
    });
    
    fireEvent.change(screen.getByLabelText(/District/i), {
      target: { value: 'Mbombela' }
    });
    
    fireEvent.change(screen.getByLabelText(/Description/i), {
      target: { value: 'Too short' }
    });
    
    // Try to go to next step
    fireEvent.click(screen.getByRole('button', { name: /Next/i }));
    
    // Check for validation error
    expect(await screen.findByText('Description should be at least 50 characters')).toBeInTheDocument();
  });

  it('navigates to step 2 when step 1 is valid', async () => {
    renderRegisterComponent();
    
    // Fill in form with valid data
    fireEvent.change(screen.getByLabelText(/Business Name/i), {
      target: { value: 'Test Business' }
    });
    
    fireEvent.change(screen.getByLabelText(/Category/i), {
      target: { value: 'Tourism' }
    });
    
    fireEvent.change(screen.getByLabelText(/District/i), {
      target: { value: 'Mbombela' }
    });
    
    fireEvent.change(screen.getByLabelText(/Description/i), {
      target: { value: 'This is a detailed description of my business that is definitely more than 50 characters long to pass validation.' }
    });
    
    // Go to next step
    fireEvent.click(screen.getByRole('button', { name: /Next/i }));
    
    // Check that we're on step 2
    await waitFor(() => {
      expect(screen.getByLabelText(/Phone/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/Email/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/Website/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/Address/i)).toBeInTheDocument();
    });
  });

  it('shows validation errors for empty step 2 submission', async () => {
    renderRegisterComponent();
    
    // Fill in step 1 with valid data
    fireEvent.change(screen.getByLabelText(/Business Name/i), {
      target: { value: 'Test Business' }
    });
    
    fireEvent.change(screen.getByLabelText(/Category/i), {
      target: { value: 'Tourism' }
    });
    
    fireEvent.change(screen.getByLabelText(/District/i), {
      target: { value: 'Mbombela' }
    });
    
    fireEvent.change(screen.getByLabelText(/Description/i), {
      target: { value: 'This is a detailed description of my business that is definitely more than 50 characters long to pass validation.' }
    });
    
    // Go to step 2
    fireEvent.click(screen.getByRole('button', { name: /Next/i }));
    
    // Wait for step 2 to load
    await waitFor(() => {
      expect(screen.getByLabelText(/Phone/i)).toBeInTheDocument();
    });
    
    // Try to go to next step without entering data
    fireEvent.click(screen.getByRole('button', { name: /Next/i }));
    
    // Check for validation errors
    expect(await screen.findByText('Phone number is required')).toBeInTheDocument();
    expect(screen.getByText('Email is required')).toBeInTheDocument();
    expect(screen.getByText('Business address is required')).toBeInTheDocument();
  });

  it('navigates to step 3 when step 2 is valid', async () => {
    renderRegisterComponent();
    
    // Fill in step 1 with valid data
    fireEvent.change(screen.getByLabelText(/Business Name/i), {
      target: { value: 'Test Business' }
    });
    
    fireEvent.change(screen.getByLabelText(/Category/i), {
      target: { value: 'Tourism' }
    });
    
    fireEvent.change(screen.getByLabelText(/District/i), {
      target: { value: 'Mbombela' }
    });
    
    fireEvent.change(screen.getByLabelText(/Description/i), {
      target: { value: 'This is a detailed description of my business that is definitely more than 50 characters long to pass validation.' }
    });
    
    // Go to step 2
    fireEvent.click(screen.getByRole('button', { name: /Next/i }));
    
    // Wait for step 2 to load
    await waitFor(() => {
      expect(screen.getByLabelText(/Phone/i)).toBeInTheDocument();
    });
    
    // Fill in step 2 with valid data
    fireEvent.change(screen.getByLabelText(/Phone/i), {
      target: { value: '+27123456789' }
    });
    
    fireEvent.change(screen.getByLabelText(/Email/i), {
      target: { value: 'test@example.com' }
    });
    
    fireEvent.change(screen.getByLabelText(/Website/i), {
      target: { value: 'https://example.com' }
    });
    
    fireEvent.change(screen.getByLabelText(/Address/i), {
      target: { value: '123 Test Street, Mbombela' }
    });
    
    // Go to step 3
    fireEvent.click(screen.getByRole('button', { name: /Next/i }));
    
    // Check that we're on step 3
    await waitFor(() => {
      expect(screen.getByLabelText(/Password/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/Confirm Password/i)).toBeInTheDocument();
      expect(screen.getByLabelText(/I agree to the Terms and Conditions/i)).toBeInTheDocument();
    });
  });

  it('shows validation errors for empty step 3 submission', async () => {
    // Navigate to step 3 (using the same steps as previous test)
    renderRegisterComponent();
    
    // Fill in step 1
    fireEvent.change(screen.getByLabelText(/Business Name/i), {
      target: { value: 'Test Business' }
    });
    fireEvent.change(screen.getByLabelText(/Category/i), {
      target: { value: 'Tourism' }
    });
    fireEvent.change(screen.getByLabelText(/District/i), {
      target: { value: 'Mbombela' }
    });
    fireEvent.change(screen.getByLabelText(/Description/i), {
      target: { value: 'This is a detailed description of my business that is definitely more than 50 characters long to pass validation.' }
    });
    fireEvent.click(screen.getByRole('button', { name: /Next/i }));
    
    // Wait for step 2 to load and fill it
    await waitFor(() => expect(screen.getByLabelText(/Phone/i)).toBeInTheDocument());
    fireEvent.change(screen.getByLabelText(/Phone/i), {
      target: { value: '+27123456789' }
    });
    fireEvent.change(screen.getByLabelText(/Email/i), {
      target: { value: 'test@example.com' }
    });
    fireEvent.change(screen.getByLabelText(/Address/i), {
      target: { value: '123 Test Street, Mbombela' }
    });
    fireEvent.click(screen.getByRole('button', { name: /Next/i }));
    
    // Wait for step 3 to load
    await waitFor(() => expect(screen.getByLabelText(/Password/i)).toBeInTheDocument());
    
    // Try to submit without entering data
    fireEvent.click(screen.getByRole('button', { name: /Register Business/i }));
    
    // Check for validation errors
    expect(await screen.findByText('Password is required')).toBeInTheDocument();
    expect(screen.getByText('Please confirm your password')).toBeInTheDocument();
    expect(screen.getByText('You must agree to the terms and conditions')).toBeInTheDocument();
  });

  it('handles successful registration', async () => {
    // Mock successful API response
    global.fetch.mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({
        data: {
          token: 'fake-token',
          user: {
            id: 1,
            name: 'Test User',
            email: 'test@example.com'
          },
          business: {
            id: 1,
            name: 'Test Business',
            status: 'pending'
          }
        }
      })
    });
    
    // Navigate to step 3 and fill all forms
    renderRegisterComponent();
    
    // Fill in step 1
    fireEvent.change(screen.getByLabelText(/Business Name/i), {
      target: { value: 'Test Business' }
    });
    fireEvent.change(screen.getByLabelText(/Category/i), {
      target: { value: 'Tourism' }
    });
    fireEvent.change(screen.getByLabelText(/District/i), {
      target: { value: 'Mbombela' }
    });
    fireEvent.change(screen.getByLabelText(/Description/i), {
      target: { value: 'This is a detailed description of my business that is definitely more than 50 characters long to pass validation.' }
    });
    fireEvent.click(screen.getByRole('button', { name: /Next/i }));
    
    // Wait for step 2 to load and fill it
    await waitFor(() => expect(screen.getByLabelText(/Phone/i)).toBeInTheDocument());
    fireEvent.change(screen.getByLabelText(/Phone/i), {
      target: { value: '+27123456789' }
    });
    fireEvent.change(screen.getByLabelText(/Email/i), {
      target: { value: 'test@example.com' }
    });
    fireEvent.change(screen.getByLabelText(/Address/i), {
      target: { value: '123 Test Street, Mbombela' }
    });
    fireEvent.click(screen.getByRole('button', { name: /Next/i }));
    
    // Wait for step 3 to load and fill it
    await waitFor(() => expect(screen.getByLabelText(/Password/i)).toBeInTheDocument());
    fireEvent.change(screen.getByLabelText(/Password/i), {
      target: { value: 'password123' }
    });
    fireEvent.change(screen.getByLabelText(/Confirm Password/i), {
      target: { value: 'password123' }
    });
    fireEvent.click(screen.getByLabelText(/I agree to the Terms and Conditions/i));
    
    // Submit the form
    fireEvent.click(screen.getByRole('button', { name: /Register Business/i }));
    
    // Wait for the API call to complete
    await waitFor(() => {
      // Check if fetch was called with correct parameters
      expect(global.fetch).toHaveBeenCalledWith('/api/auth/register', expect.any(Object));
      
      // Check if toast success was called
      expect(toast.success).toHaveBeenCalledWith(expect.stringContaining('Registration successful'));
      
      // Check if navigation occurred
      expect(vi.mocked(useNavigate)).toHaveBeenCalledTimes(1);
    });
  });

  it('handles API error during registration', async () => {
    // Mock failed API response
    global.fetch.mockResolvedValueOnce({
      ok: false,
      json: () => Promise.resolve({
        message: 'Email already exists'
      })
    });
    
    // Navigate to step 3 and fill all forms (similar to previous test)
    renderRegisterComponent();
    
    // Fill in step 1
    fireEvent.change(screen.getByLabelText(/Business Name/i), {
      target: { value: 'Test Business' }
    });
    fireEvent.change(screen.getByLabelText(/Category/i), {
      target: { value: 'Tourism' }
    });
    fireEvent.change(screen.getByLabelText(/District/i), {
      target: { value: 'Mbombela' }
    });
    fireEvent.change(screen.getByLabelText(/Description/i), {
      target: { value: 'This is a detailed description of my business that is definitely more than 50 characters long to pass validation.' }
    });
    fireEvent.click(screen.getByRole('button', { name: /Next/i }));
    
    // Wait for step 2 to load and fill it
    await waitFor(() => expect(screen.getByLabelText(/Phone/i)).toBeInTheDocument());
    fireEvent.change(screen.getByLabelText(/Phone/i), {
      target: { value: '+27123456789' }
    });
    fireEvent.change(screen.getByLabelText(/Email/i), {
      target: { value: 'existing@example.com' }
    });
    fireEvent.change(screen.getByLabelText(/Address/i), {
      target: { value: '123 Test Street, Mbombela' }
    });
    fireEvent.click(screen.getByRole('button', { name: /Next/i }));
    
    // Wait for step 3 to load and fill it
    await waitFor(() => expect(screen.getByLabelText(/Password/i)).toBeInTheDocument());
    fireEvent.change(screen.getByLabelText(/Password/i), {
      target: { value: 'password123' }
    });
    fireEvent.change(screen.getByLabelText(/Confirm Password/i), {
      target: { value: 'password123' }
    });
    fireEvent.click(screen.getByLabelText(/I agree to the Terms and Conditions/i));
    
    // Submit the form
    fireEvent.click(screen.getByRole('button', { name: /Register Business/i }));
    
    // Wait for the API call to complete
    await waitFor(() => {
      // Check if toast error was called
      expect(toast.error).toHaveBeenCalledWith(expect.stringContaining('Registration failed'));
    });
  });

  it('fetches categories and districts on mount', async () => {
    // Setup mock responses
    businessMetadataApi.getCategories.mockResolvedValue({
      status: 'success',
      data: ['Tourism', 'Agriculture', 'Construction', 'Events']
    });
    
    businessMetadataApi.getDistricts.mockResolvedValue({
      status: 'success',
      data: ['Mbombela', 'Emalahleni', 'Bushbuckridge']
    });
    
    renderRegisterComponent();
    
    // Verify API calls were made
    expect(businessMetadataApi.getCategories).toHaveBeenCalledTimes(1);
    expect(businessMetadataApi.getDistricts).toHaveBeenCalledTimes(1);
    
    // Wait for loading state to resolve
    await waitFor(() => {
      expect(screen.getByText('Business Information')).toBeInTheDocument();
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
    
    renderRegisterComponent();
    
    // Verify form is rendered while metadata is loading
    expect(screen.getByText('Business Information')).toBeInTheDocument();
    
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
    
    renderRegisterComponent();
    
    // Wait for fallback values to be used
    await waitFor(() => {
      const categorySelect = screen.getByLabelText(/Category/i);
      fireEvent.click(categorySelect);
      expect(screen.getByText('Tourism')).toBeInTheDocument();
      expect(screen.getByText('Agriculture')).toBeInTheDocument();
      expect(screen.getByText('Construction')).toBeInTheDocument();
      expect(screen.getByText('Events')).toBeInTheDocument();
    });
    
    // Check for district fallback values
    await waitFor(() => {
      const districtSelect = screen.getByLabelText(/District/i);
      fireEvent.click(districtSelect);
      expect(screen.getByText('Mbombela')).toBeInTheDocument();
      expect(screen.getByText('Emalahleni')).toBeInTheDocument();
      expect(screen.getByText('Bushbuckridge')).toBeInTheDocument();
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
    
    renderRegisterComponent();
    
    // Wait for custom categories to be available
    await waitFor(() => {
      const categorySelect = screen.getByLabelText(/Category/i);
      fireEvent.click(categorySelect);
      expect(screen.getByText('Technology')).toBeInTheDocument();
      expect(screen.getByText('Healthcare')).toBeInTheDocument();
      expect(screen.getByText('Education')).toBeInTheDocument();
    });
    
    // Check for custom district values
    await waitFor(() => {
      const districtSelect = screen.getByLabelText(/District/i);
      fireEvent.click(districtSelect);
      expect(screen.getByText('Pretoria')).toBeInTheDocument();
      expect(screen.getByText('Johannesburg')).toBeInTheDocument();
      expect(screen.getByText('Cape Town')).toBeInTheDocument();
    });
  });
});
