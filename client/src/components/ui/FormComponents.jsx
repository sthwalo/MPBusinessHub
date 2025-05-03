import React from 'react';

// Form field components
export const FormField = ({ label, id, name, type = 'text', value, onChange, error, children }) => (
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

export const FormSelect = ({ label, id, name, value, onChange, error, options }) => (
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

export const FormTextarea = ({ label, id, name, rows = 3, value, onChange, error, minLength, maxLength }) => (
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

export const FormSection = ({ title, children }) => (
  <div className="bg-brand-white border border-brand-gray-200 rounded-lg p-6 mb-6">
    <h3 className="text-lg font-bold mb-4">{title}</h3>
    {children}
  </div>
);

export const SuccessMessage = ({ message }) => (
  message ? (
    <div className="bg-green-50 border border-green-200 text-green-700 rounded-md p-4 mb-6">
      {message}
    </div>
  ) : null
);