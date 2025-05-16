import React from 'react';
import { FormField, FormTextarea } from '../../ui/FormComponents';

const ContactInfoSection = ({ formData, handleChange, errors }) => {
  return (
    <div>
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
    </div>
  );
};

export default ContactInfoSection;