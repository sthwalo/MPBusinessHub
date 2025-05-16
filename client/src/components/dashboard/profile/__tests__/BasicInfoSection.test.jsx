import { describe, it, expect, beforeEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import BasicInfoSection from '../BasicInfoSection';

describe('BasicInfoSection Component', () => {
  const mockFormData = {
    name: 'Test Business',
    category: 'Tourism',
    district: 'Mbombela',
    description: 'Test description'
  };
  
  const mockErrors = {};
  const mockHandleChange = jest.fn();
  
  // Default props
  const defaultProps = {
    formData: mockFormData,
    handleChange: mockHandleChange,
    errors: mockErrors,
    categories: ['Tourism', 'Agriculture', 'Construction', 'Events'],
    districts: ['Mbombela', 'Emalahleni', 'Bushbuckridge'],
    isLoadingMetadata: false
  };
  
  beforeEach(() => {
    mockHandleChange.mockClear();
  });
  
  it('renders with all form fields', () => {
    render(<BasicInfoSection {...defaultProps} />);
    
    // Check that all form fields are rendered
    expect(screen.getByLabelText(/Business Name/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/Category/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/District/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/Description/i)).toBeInTheDocument();
    
    // Check that the form fields have the correct values
    expect(screen.getByLabelText(/Business Name/i).value).toBe('Test Business');
    expect(screen.getByLabelText(/Category/i).value).toBe('Tourism');
    expect(screen.getByLabelText(/District/i).value).toBe('Mbombela');
    expect(screen.getByLabelText(/Description/i).value).toBe('Test description');
  });
  
  it('displays loading state for categories and districts', () => {
    render(<BasicInfoSection {...defaultProps} isLoadingMetadata={true} />);
    
    // Check that loading state is displayed
    expect(screen.getByText('Loading categories...')).toBeInTheDocument();
    expect(screen.getByText('Loading districts...')).toBeInTheDocument();
    
    // Check that the select elements are disabled
    expect(screen.getByLabelText(/Category/i)).toBeDisabled();
    expect(screen.getByLabelText(/District/i)).toBeDisabled();
  });
  
  it('renders categories and districts from props', () => {
    const customCategories = ['Technology', 'Healthcare', 'Education'];
    const customDistricts = ['Pretoria', 'Johannesburg', 'Cape Town'];
    
    render(
      <BasicInfoSection 
        {...defaultProps} 
        categories={customCategories}
        districts={customDistricts}
      />
    );
    
    // Check that all categories are rendered
    const categorySelect = screen.getByLabelText(/Category/i);
    customCategories.forEach(category => {
      expect(categorySelect).toContainElement(screen.getByText(category));
    });
    
    // Check that all districts are rendered
    const districtSelect = screen.getByLabelText(/District/i);
    customDistricts.forEach(district => {
      expect(districtSelect).toContainElement(screen.getByText(district));
    });
  });
  
  it('calls handleChange when form fields change', () => {
    render(<BasicInfoSection {...defaultProps} />);
    
    // Change business name
    fireEvent.change(screen.getByLabelText(/Business Name/i), {
      target: { name: 'name', value: 'Updated Business Name' }
    });
    expect(mockHandleChange).toHaveBeenCalledWith(expect.objectContaining({
      target: { name: 'name', value: 'Updated Business Name' }
    }));
    
    // Change category
    fireEvent.change(screen.getByLabelText(/Category/i), {
      target: { name: 'category', value: 'Agriculture' }
    });
    expect(mockHandleChange).toHaveBeenCalledWith(expect.objectContaining({
      target: { name: 'category', value: 'Agriculture' }
    }));
    
    // Change district
    fireEvent.change(screen.getByLabelText(/District/i), {
      target: { name: 'district', value: 'Emalahleni' }
    });
    expect(mockHandleChange).toHaveBeenCalledWith(expect.objectContaining({
      target: { name: 'district', value: 'Emalahleni' }
    }));
    
    // Change description
    fireEvent.change(screen.getByLabelText(/Description/i), {
      target: { name: 'description', value: 'Updated description' }
    });
    expect(mockHandleChange).toHaveBeenCalledWith(expect.objectContaining({
      target: { name: 'description', value: 'Updated description' }
    }));
  });
  
  it('displays error messages when errors are provided', () => {
    const errorsWithMessages = {
      name: 'Business name is required',
      category: 'Please select a category',
      district: 'Please select a district',
      description: 'Description is too short'
    };
    
    render(
      <BasicInfoSection 
        {...defaultProps} 
        errors={errorsWithMessages}
      />
    );
    
    // Check that error messages are displayed
    expect(screen.getByText('Business name is required')).toBeInTheDocument();
    expect(screen.getByText('Please select a category')).toBeInTheDocument();
    expect(screen.getByText('Please select a district')).toBeInTheDocument();
    expect(screen.getByText('Description is too short')).toBeInTheDocument();
  });
  
  it('handles empty categories and districts arrays', () => {
    render(
      <BasicInfoSection 
        {...defaultProps} 
        categories={[]}
        districts={[]}
      />
    );
    
    // Check that the select elements only contain the default option
    const categorySelect = screen.getByLabelText(/Category/i);
    expect(categorySelect.children.length).toBe(1);
    expect(categorySelect.children[0].textContent).toBe('Select a category');
    
    const districtSelect = screen.getByLabelText(/District/i);
    expect(districtSelect.children.length).toBe(1);
    expect(districtSelect.children[0].textContent).toBe('Select a district');
  });
});
