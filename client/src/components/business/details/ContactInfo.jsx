// ContactInfo.jsx
import { memo } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { packageFeatures, iconPaths } from '../../../utils/businessUtils';

/**
 * ContactInfo component displays business contact information
 * @param {Object} props
 * @param {Object} props.business - Business data object
 * @returns {JSX.Element}
 */
const ContactInfo = ({ business }) => {
  if (!business || !business.contact) return null;
  
  const packageType = business.package_type || 'Basic';
  const canShowContactInfo = packageFeatures[packageType]?.contact || false;
  
  // If contact info should be hidden based on package type
  if (!canShowContactInfo) {
    return (
      <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
        <h3 className="text-lg font-bold text-yellow-800 mb-2">Contact Information Hidden</h3>
        <p className="text-yellow-700 mb-4">This business is on a Basic membership plan. Contact information is only visible for Bronze tier and above.</p>
        <Link to="/pricing" className="btn btn-primary w-full">View Membership Options</Link>
      </div>
    );
  }
  
  const { phone, email, website, address } = business.contact;
  
  // Track contact clicks
  const handleContactClick = async () => {
    if (business && business.id) {
      try {
        await axios.post(`/api/businesses/${business.id}/contact`);
      } catch (error) {
        console.error('Error tracking contact click:', error);
      }
    }
  };
  
  return (
    <div className="bg-white rounded-lg shadow-md p-6 mb-6">
      <h2 className="text-xl font-bold mb-4">Contact Information</h2>
      
      {phone && (
        <div className="flex items-center mb-3">
          <svg className="h-5 w-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={iconPaths.phone} />
          </svg>
          <span>{phone}</span>
        </div>
      )}
      
      {email && (
        <div className="flex items-center mb-3">
          <svg className="h-5 w-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={iconPaths.email} />
          </svg>
          <span>{email}</span>
        </div>
      )}
      
      {website && (
        <div className="flex items-center mb-3">
          <svg className="h-5 w-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={iconPaths.website} />
          </svg>
          <a 
            href={website.startsWith('http') ? website : `https://${website}`} 
            target="_blank" 
            rel="noopener noreferrer" 
            className="text-blue-600 hover:text-blue-800 truncate"
          >
            {website.replace(/(^https?:\/\/|\/$)/g, '')}
          </a>
        </div>
      )}
      
      {address && (
        <div className="flex items-center mb-3">
          <svg className="h-5 w-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={iconPaths.address} />
          </svg>
          <span>{address}</span>
        </div>
      )}
      
      <div className="mt-6 space-y-3">
        {phone && (
          <a 
            href={`tel:${phone}`} 
            className="btn btn-primary w-full flex items-center justify-center"
            onClick={handleContactClick}
          >
            <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={iconPaths.phone} />
            </svg>
            Call Now
          </a>
        )}
        
        {email && (
          <a 
            href={`mailto:${email}`} 
            className="btn btn-outline w-full flex items-center justify-center"
            onClick={handleContactClick}
          >
            <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={iconPaths.email} />
            </svg>
            Email
          </a>
        )}
      </div>
    </div>
  );
};

export default memo(ContactInfo);