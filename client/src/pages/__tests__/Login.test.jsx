import { describe, it, expect, beforeEach, vi } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import Login from '../Login';
import toast from 'react-hot-toast';

// Mock the react-hot-toast
vi.mock('react-hot-toast', () => ({
  default: {
    success: vi.fn(),
    error: vi.fn(),
    loading: vi.fn().mockReturnValue('mocked-toast-id'),
    dismiss: vi.fn()
  }
}));

// Mock fetch
global.fetch = vi.fn();

// Mock useNavigate
vi.mock('react-router-dom', async () => {
  const actual = await vi.importActual('react-router-dom');
  return {
    ...actual,
    useNavigate: () => vi.fn()
  };
});

describe('Login Component', () => {
  const mockSetIsAuthenticated = vi.fn();
  
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

  const renderLoginComponent = () => {
    return render(
      <BrowserRouter>
        <Login setIsAuthenticated={mockSetIsAuthenticated} />
      </BrowserRouter>
    );
  };

  it('renders login form correctly', () => {
    renderLoginComponent();
    
    expect(screen.getByText('Login to Your Account')).toBeInTheDocument();
    expect(screen.getByLabelText(/Email Address/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/Password/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/Remember me/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /Login/i })).toBeInTheDocument();
    expect(screen.getByText(/Don't have an account/i)).toBeInTheDocument();
    expect(screen.getByText(/Register Your Business/i)).toBeInTheDocument();
  });

  it('shows validation errors for empty form submission', async () => {
    renderLoginComponent();
    
    // Submit the form without entering any data
    fireEvent.click(screen.getByRole('button', { name: /Login/i }));
    
    // Check for validation errors
    expect(await screen.findByText('Email is required')).toBeInTheDocument();
    expect(screen.getByText('Password is required')).toBeInTheDocument();
  });

  it('shows validation error for invalid email', async () => {
    renderLoginComponent();
    
    // Enter invalid email
    fireEvent.change(screen.getByLabelText(/Email Address/i), {
      target: { value: 'invalid-email' }
    });
    
    // Enter password
    fireEvent.change(screen.getByLabelText(/Password/i), {
      target: { value: 'password123' }
    });
    
    // Submit the form
    fireEvent.click(screen.getByRole('button', { name: /Login/i }));
    
    // Check for validation error
    expect(await screen.findByText('Email is invalid')).toBeInTheDocument();
  });

  it('handles successful login', async () => {
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
            status: 'active'
          }
        }
      })
    });
    
    renderLoginComponent();
    
    // Fill in the form
    fireEvent.change(screen.getByLabelText(/Email Address/i), {
      target: { value: 'test@example.com' }
    });
    
    fireEvent.change(screen.getByLabelText(/Password/i), {
      target: { value: 'password123' }
    });
    
    // Submit the form
    fireEvent.click(screen.getByRole('button', { name: /Login/i }));
    
    // Wait for the API call to complete
    await waitFor(() => {
      // Check if fetch was called with correct parameters
      expect(global.fetch).toHaveBeenCalledWith('/api/auth/login', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          email: 'test@example.com',
          password: 'password123'
        })
      });
      
      // Check if localStorage was updated
      expect(window.localStorage.setItem).toHaveBeenCalledWith('mpbh_token', 'fake-token');
      expect(window.localStorage.setItem).toHaveBeenCalledWith('mpbh_user', JSON.stringify({
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        businessId: 1,
        businessName: 'Test Business',
        businessStatus: 'active'
      }));
      
      // Check if toast success was called
      expect(toast.success).toHaveBeenCalledWith('Login successful!');
      
      // Check if authentication state was updated
      expect(mockSetIsAuthenticated).toHaveBeenCalledWith(true);
    });
  });

  it('handles API error during login', async () => {
    // Mock failed API response
    global.fetch.mockResolvedValueOnce({
      ok: false,
      json: () => Promise.resolve({
        message: 'Invalid credentials'
      })
    });
    
    renderLoginComponent();
    
    // Fill in the form
    fireEvent.change(screen.getByLabelText(/Email Address/i), {
      target: { value: 'test@example.com' }
    });
    
    fireEvent.change(screen.getByLabelText(/Password/i), {
      target: { value: 'wrong-password' }
    });
    
    // Submit the form
    fireEvent.click(screen.getByRole('button', { name: /Login/i }));
    
    // Wait for the API call to complete
    await waitFor(() => {
      // Check if toast error was called
      expect(toast.error).toHaveBeenCalledWith('Login failed. Please check your credentials.');
    });
  });

  it('clears errors when user types in a field with error', async () => {
    renderLoginComponent();
    
    // Submit empty form to trigger errors
    fireEvent.click(screen.getByRole('button', { name: /Login/i }));
    
    // Check that error is displayed
    expect(await screen.findByText('Email is required')).toBeInTheDocument();
    
    // Type in the email field
    fireEvent.change(screen.getByLabelText(/Email Address/i), {
      target: { value: 'test@example.com' }
    });
    
    // Check that error is cleared
    expect(screen.queryByText('Email is required')).not.toBeInTheDocument();
  });
});
