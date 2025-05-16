import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import { FormSelect } from '../FormComponents';

describe('FormSelect Component', () => {
  // Basic props for testing
  const defaultProps = {
    label: 'Test Select',
    name: 'testSelect',
    value: '',
    onChange: () => {},
    error: '',
    options: ['Option 1', 'Option 2', 'Option 3'],
    isLoading: false
  };

  it('renders with label and options', () => {
    render(<FormSelect {...defaultProps} />);
    
    // Check label is rendered
    expect(screen.getByLabelText('Test Select')).toBeInTheDocument();
    
    // Check all options are rendered
    expect(screen.getByText('Select a Test Select')).toBeInTheDocument(); // Default option
    expect(screen.getByText('Option 1')).toBeInTheDocument();
    expect(screen.getByText('Option 2')).toBeInTheDocument();
    expect(screen.getByText('Option 3')).toBeInTheDocument();
  });

  it('displays loading state when isLoading is true', () => {
    render(<FormSelect {...defaultProps} isLoading={true} />);
    
    // Check loading text is displayed
    expect(screen.getByText('Loading...')).toBeInTheDocument();
    
    // Check select is disabled
    expect(screen.getByLabelText('Test Select')).toBeDisabled();
  });

  it('displays selected value', () => {
    render(<FormSelect {...defaultProps} value="Option 2" />);
    
    // Check the select has the correct value
    expect(screen.getByLabelText('Test Select').value).toBe('Option 2');
  });

  it('displays error message when error is provided', () => {
    const errorMessage = 'This field is required';
    render(<FormSelect {...defaultProps} error={errorMessage} />);
    
    // Check error message is displayed
    expect(screen.getByText(errorMessage)).toBeInTheDocument();
    
    // Check select has error class
    const selectElement = screen.getByLabelText('Test Select');
    expect(selectElement.classList.contains('border-red-500')).toBe(true);
  });

  it('handles empty options array', () => {
    render(<FormSelect {...defaultProps} options={[]} />);
    
    // Check only default option is rendered
    const selectElement = screen.getByLabelText('Test Select');
    expect(selectElement.children.length).toBe(1);
    expect(selectElement.children[0].textContent).toBe('Select a Test Select');
  });

  it('customizes default option text', () => {
    const customDefaultOption = 'Choose an option';
    render(<FormSelect {...defaultProps} defaultOption={customDefaultOption} />);
    
    // Check custom default option is rendered
    expect(screen.getByText(customDefaultOption)).toBeInTheDocument();
  });

  it('applies additional className when provided', () => {
    render(<FormSelect {...defaultProps} className="custom-class" />);
    
    // Check custom class is applied
    const selectElement = screen.getByLabelText('Test Select');
    expect(selectElement.classList.contains('custom-class')).toBe(true);
  });
});
