import { useState, useRef } from 'react';
import { iconPaths } from '../../utils/businessUtils';

// Import reusable components
import LoadingSkeleton from '../ui/LoadingSkeleton';
import ErrorMessage from '../ui/ErrorMessage';

// Form field components
const FormField = ({ label, id, name, type = 'text', value, onChange, error, children }) => (
  <div>
    <label htmlFor={id} className="form-label">{label}</label>
    {children || (
      <input
        type={type}
        id={id}
        name={name}
        className={`form-control ${error ? 'border-red-500' : ''}`}
        value={value}
        onChange={onChange}
      />
    )}
    {error && <p className="text-red-500 text-sm mt-1">{error}</p>}
  </div>
);

const FormSelect = ({ label, id, name, value, onChange, error, options }) => (
  <FormField label={label} id={id} name={name} value={value} onChange={onChange} error={error}>
    <select
      id={id}
      name={name}
      className={`form-control ${error ? 'border-red-500' : ''}`}
      value={value}
      onChange={onChange}
    >
      <option value="">Select a {label.toLowerCase()}</option>
      {options.map(option => (
        <option key={option} value={option}>{option}</option>
      ))}
    </select>
  </FormField>
);

const FormTextarea = ({ label, id, name, rows = 3, value, onChange, error, minLength, maxLength }) => (
  <div>
    <label htmlFor={id} className="form-label">{label}</label>
    <textarea
      id={id}
      name={name}
      rows={rows}
      className={`form-control ${error ? 'border-red-500' : ''}`}
      value={value}
      onChange={onChange}
    ></textarea>
    {minLength && (
      <div className="flex justify-between mt-1">
        <p className={`text-sm ${value.length < minLength ? 'text-red-500' : 'text-gray-500'}`}>
          Minimum {minLength} characters
        </p>
        <p className="text-sm text-gray-500">
          {value.length}/{maxLength || 1000}
        </p>
      </div>
    )}
    {error && <p className="text-red-500 text-sm">{error}</p>}
  </div>
);

const FormSection = ({ title, children }) => (
  <div className="bg-brand-white border border-brand-gray-200 rounded-lg p-6 mb-6">
    <h3 className="text-lg font-bold mb-4">{title}</h3>
    {children}
  </div>
);

const SuccessMessage = ({ message }) => (
  message ? (
    <div className="bg-green-50 border border-green-200 text-green-700 rounded-md p-4 mb-6">
      {message}
    </div>
  ) : null
);

// Image upload component
const ImageUpload = ({ businessImage, onImageUpload }) => {
  const [previewUrl, setPreviewUrl] = useState(businessImage || '');
  const [isUploading, setIsUploading] = useState(false);
  const [uploadError, setUploadError] = useState('');
  const fileInputRef = useRef(null);

  const handleFileChange = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    // Preview the image
    const reader = new FileReader();
    reader.onload = () => {
      setPreviewUrl(reader.result);
    };
    reader.readAsDataURL(file);

    // Upload the image
    uploadImage(file);
  };

  const uploadImage = async (file) => {
    setIsUploading(true);
    setUploadError('');

    try {
      const formData = new FormData();
      formData.append('image', file);

      const token = localStorage.getItem('mpbh_token');
      if (!token) {
        throw new Error('Authentication token not found');
      }

      const response = await fetch('/api/images/business', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        },
        body: formData
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to upload image');
      }

      const data = await response.json();
      if (data.status === 'success') {
        // Call the parent component's callback with the new image URL
        onImageUpload(data.image_url);
      } else {
        throw new Error(data.message || 'Failed to upload image');
      }
    } catch (error) {
      setUploadError(error.message || 'Failed to upload image');
      console.error('Error uploading image:', error);
    } finally {
      setIsUploading(false);
    }
  };

  const triggerFileInput = () => {
    fileInputRef.current.click();
  };

  return (
    <div className="mb-6">
      <label className="form-label">Business Profile Image</label>
      <div className="mt-2 flex flex-col items-center">
        <div 
          className="relative w-48 h-48 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center overflow-hidden bg-gray-50 cursor-pointer"
          onClick={triggerFileInput}
        >
          {previewUrl ? (
            <img 
              src={previewUrl} 
              alt="Business profile" 
              className="w-full h-full object-cover"
            />
          ) : (
            <div className="text-center p-4">
              <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <p className="mt-2 text-sm text-gray-500">Click to upload an image</p>
              <p className="text-xs text-gray-400 mt-1">JPG, PNG, GIF up to 2MB</p>
            </div>
          )}
          {isUploading && (
            <div className="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
              <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-white"></div>
            </div>
          )}
        </div>
        <input
          type="file"
          ref={fileInputRef}
          className="hidden"
          accept="image/jpeg,image/png,image/gif"
          onChange={handleFileChange}
        />
        {uploadError && <p className="text-red-500 text-sm mt-2">{uploadError}</p>}
        <button 
          type="button" 
          className="mt-3 text-sm text-blue-600 hover:text-blue-800"
          onClick={triggerFileInput}
        >
          {previewUrl ? 'Change image' : 'Upload image'}
        </button>
      </div>
    </div>
  );
};

function BusinessProfile({ businessData, updateBusinessData }) {
  const [formData, setFormData] = useState({
    businessName: businessData.name,
    category: businessData.category,
    district: businessData.district,
    description: businessData.description || '',
    phone: businessData.phone || '',
    email: businessData.email || '',
    website: businessData.website || '',
    address: businessData.address || ''
  });
  const [errors, setErrors] = useState({});
  const [isLoading, setIsLoading] = useState(false);
  const [successMessage, setSuccessMessage] = useState('');
  const [businessImage, setBusinessImage] = useState(businessData.image_url || '');

  // Available options based on the database schema
  const categories = ['Tourism', 'Agriculture', 'Construction', 'Events'];
  const districts = ['Mbombela', 'Emalahleni', 'Bushbuckridge'];

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prevData => ({
      ...prevData,
      [name]: value
    }));
    
    // Clear error and success message when user starts typing
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }));
    }
    if (successMessage) {
      setSuccessMessage('');
    }
  };

  const validateForm = () => {
    const newErrors = {};
    
    if (!formData.businessName.trim()) {
      newErrors.businessName = 'Business name is required';
    }
    
    if (!formData.category) {
      newErrors.category = 'Please select a category';
    }
    
    if (!formData.district) {
      newErrors.district = 'Please select a district';
    }
    
    if (!formData.description.trim()) {
      newErrors.description = 'Business description is required';
    } else if (formData.description.length < 50) {
      newErrors.description = 'Description should be at least 50 characters';
    }
    
    if (!formData.phone.trim()) {
      newErrors.phone = 'Phone number is required';
    }
    
    if (!formData.email.trim()) {
      newErrors.email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Please enter a valid email address';
    }
    
    if (formData.website && !/^(https?:\/\/)?[\w-]+(\.[\w-]+)+(\/[\w-./?%&=]*)?$/.test(formData.website)) {
      newErrors.website = 'Please enter a valid website URL';
    }
    
    if (!formData.address.trim()) {
      newErrors.address = 'Business address is required';
    }
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!validateForm()) {
      window.scrollTo(0, 0);
      return;
    }
    
    setIsLoading(true);
    
    try {
      // Make the real API call to update business profile
      const token = localStorage.getItem('mpbh_token');
      if (!token) {
        throw new Error('Authentication token not found');
      }
      
      const response = await fetch('/api/business/update', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(formData)
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to update business profile');
      }
      
      const data = await response.json();
      
      if (data.status === 'success') {
        setSuccessMessage(data.message || 'Business profile updated successfully!');
        // Update the parent component's business data
        if (typeof updateBusinessData === 'function') {
          updateBusinessData(data.data);
        }
      } else {
        throw new Error(data.message || 'Failed to update business profile');
      }
      
      setIsLoading(false);
      window.scrollTo(0, 0);
    } catch (error) {
      setErrors({ form: error.message || 'Failed to update profile. Please try again.' });
      setIsLoading(false);
      window.scrollTo(0, 0);
    }
  };

  const handleImageUpload = (imageUrl) => {
    setBusinessImage(imageUrl);
  };

  return (
    <div>
      <h2 className="text-2xl font-bold mb-6">Business Profile</h2>
      
      {/* Success message */}
      <SuccessMessage message={successMessage} />
      
      {/* Error message */}
      {errors.form && <ErrorMessage message={errors.form} />}
      
      <form onSubmit={handleSubmit}>
        <div className="bg-brand-gray-100 border border-brand-gray-200 rounded-md p-4 mb-6">
          <p className="text-brand-gray-700">
            <strong>Package Status:</strong> {businessData.package_type} Package
            {(businessData.package_type === 'Basic' || businessData.package_type === 'Bronze') && (
              <span className="ml-2">
                <a href="/dashboard/upgrade" className="text-brand-black underline">Upgrade</a> to unlock more features.
              </span>
            )}
          </p>
        </div>
        
        <ImageUpload businessImage={businessImage} onImageUpload={handleImageUpload} />
        
        <FormSection title="Basic Information">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormField
              label="Business Name"
              id="businessName"
              name="businessName"
              value={formData.businessName}
              onChange={handleChange}
              error={errors.businessName}
            />
            
            <FormSelect
              label="Business Category"
              id="category"
              name="category"
              value={formData.category}
              onChange={handleChange}
              error={errors.category}
              options={categories}
            />
          </div>
          
          <div className="mt-6">
            <FormSelect
              label="District"
              id="district"
              name="district"
              value={formData.district}
              onChange={handleChange}
              error={errors.district}
              options={districts}
            />
          </div>
          
          <div className="mt-6">
            <FormTextarea
              label="Business Description"
              id="description"
              name="description"
              rows={6}
              value={formData.description}
              onChange={handleChange}
              error={errors.description}
              minLength={50}
              maxLength={1000}
            />
          </div>
        </FormSection>
        
        <FormSection title="Contact Information">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <FormField
              label="Phone Number"
              id="phone"
              name="phone"
              type="tel"
              value={formData.phone}
              onChange={handleChange}
              error={errors.phone}
            />
            
            <FormField
              label="Email Address"
              id="email"
              name="email"
              type="email"
              value={formData.email}
              onChange={handleChange}
              error={errors.email}
            />
          </div>
          
          <div className="mt-6">
            <FormField
              label="Website (Optional)"
              id="website"
              name="website"
              type="url"
              value={formData.website}
              onChange={handleChange}
              error={errors.website}
            />
          </div>
          
          <div className="mt-6">
            <FormTextarea
              label="Business Address"
              id="address"
              name="address"
              rows={3}
              value={formData.address}
              onChange={handleChange}
              error={errors.address}
            />
          </div>
        </FormSection>
        
        <div className="flex justify-end">
          <button
            type="submit"
            className={`px-6 py-2 bg-brand-black text-brand-white rounded-md hover:bg-brand-gray-800 transition-colors ${isLoading ? 'opacity-70 cursor-not-allowed' : ''}`}
            disabled={isLoading}
          >
            {isLoading ? (
              <>
                <svg className="animate-spin h-5 w-5 mr-3 text-brand-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Saving Changes...
              </>
            ) : 'Save Changes'}
          </button>
        </div>
      </form>
    </div>
  );
}

export default BusinessProfile;
