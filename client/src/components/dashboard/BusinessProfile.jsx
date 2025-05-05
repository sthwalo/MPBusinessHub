import { useState } from 'react';
import ErrorMessage from '../ui/ErrorMessage';
import { FormSection, SuccessMessage } from '../ui/FormComponents';
import ImageUpload from './ImageUpload';
import BasicInfoSection from './profile/BasicInfoSection';
import ContactInfoSection from './profile/ContactInfoSection';

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
      const response = await fetch('/api/business/update', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('mpbh_token')}`
        },
        body: JSON.stringify({
          businessName: formData.businessName,
          category: formData.category,
          district: formData.district,
          description: formData.description,
          phone: formData.phone,
          email: formData.email,
          website: formData.website,
          address: formData.address,
          image_url: businessImage
        })
      });
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to update profile');
      }
      
      const data = await response.json();
      
      setSuccessMessage(data.message || 'Profile updated successfully!');
      
      // Update the parent component's business data
      if (typeof updateBusinessData === 'function') {
        updateBusinessData(data.data);
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
          <BasicInfoSection 
            formData={formData} 
            handleChange={handleChange} 
            errors={errors}
            categories={categories}
            districts={districts}
          />
        </FormSection>
        
        <FormSection title="Contact Information">
          <ContactInfoSection 
            formData={formData} 
            handleChange={handleChange} 
            errors={errors}
          />
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