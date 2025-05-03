// PaymentForm.jsx
import React, { useState } from 'react';
import axios from 'axios';

function PaymentForm({ businessId, packageId, onSuccess }) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  
  const handlePayment = async () => {
    try {
      setLoading(true);
      const response = await axios.post('/api/payments/initiate', {
        business_id: businessId,
        package_id: packageId
      }, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('mpbh_token')}`
        }
      });
      
      // Create a form and submit it to PayFast
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = response.data.payfast_url;
      
      // Add all the PayFast fields
      Object.entries(response.data.payfast_data).forEach(([key, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
      });
      
      document.body.appendChild(form);
      form.submit();
      
      setLoading(false);
    } catch (error) {
      setError('Failed to initiate payment. Please try again.');
      setLoading(false);
      console.error('Error initiating payment:', error);
    }
  };
  
  return (
    <div className="payment-form">
      <h3>Upgrade to {packageId.charAt(0).toUpperCase() + packageId.slice(1)} Package</h3>
      
      {error && <div className="error-message">{error}</div>}
      
      <button 
        onClick={handlePayment}
        disabled={loading}
        className="bg-brand-primary text-white px-4 py-2 rounded-md"
      >
        {loading ? 'Processing...' : 'Pay with Credit/Debit Card'}
      </button>
      
      <p className="text-sm text-gray-500 mt-2">
        Secure payment powered by PayFast
      </p>
    </div>
  );
}

export default PaymentForm;