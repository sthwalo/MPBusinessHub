import React from 'react';
import { FormField, FormSelect, FormTextarea } from '../../ui/FormComponents';

const BasicInfoSection = ({ formData, handleChange, errors, categories, districts, isLoadingMetadata }) => {
  return (
    <div>
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
          isLoading={isLoadingMetadata}
          loadingText="Loading categories..."
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
          isLoading={isLoadingMetadata}
          loadingText="Loading districts..."
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
    </div>
  );
};

export default BasicInfoSection;