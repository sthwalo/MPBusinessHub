// ContactInfo.jsx
import { memo } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { packageFeatures, iconPaths } from '../../../utils/businessUtils';
import { FaFacebook, FaInstagram, FaTwitter, FaLinkedin, FaYoutube, FaTiktok, FaWhatsapp } from 'react-icons/fa';

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
  
  const { phone, email, website, address, whatsapp } = business.contact;
  
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
      
      {/* Social Media Links */}
      {(business.contact?.social_media || business.social_media) && 
        (Object.keys(business.contact?.social_media || {}).length > 0 || 
         Object.keys(business.social_media || {}).length > 0) && (
        <div className="mt-4">
          <h3 className="text-md font-semibold mb-2">Social Media</h3>
          <div className="flex flex-wrap gap-3">
            {(business.contact?.social_media?.facebook || business.social_media?.facebook) && (
              <a 
                href={business.contact?.social_media?.facebook || business.social_media?.facebook} 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-blue-600 hover:text-blue-800"
                onClick={handleContactClick}
              >
                <FaFacebook size={24} />
              </a>
            )}
            {(business.contact?.social_media?.instagram || business.social_media?.instagram) && (
              <a 
                href={business.contact?.social_media?.instagram || business.social_media?.instagram} 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-pink-600 hover:text-pink-800"
                onClick={handleContactClick}
              >
                <FaInstagram size={24} />
              </a>
            )}
            {(business.contact?.social_media?.twitter || business.social_media?.twitter) && (
              <a 
                href={business.contact?.social_media?.twitter || business.social_media?.twitter} 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-blue-400 hover:text-blue-600"
                onClick={handleContactClick}
              >
                <FaTwitter size={24} />
              </a>
            )}
            {(business.contact?.social_media?.linkedin || business.social_media?.linkedin) && (
              <a 
                href={business.contact?.social_media?.linkedin || business.social_media?.linkedin} 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-blue-700 hover:text-blue-900"
                onClick={handleContactClick}
              >
                <FaLinkedin size={24} />
              </a>
            )}
            {(business.contact?.social_media?.youtube || business.social_media?.youtube) && (
              <a 
                href={business.contact?.social_media?.youtube || business.social_media?.youtube} 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-red-600 hover:text-red-800"
                onClick={handleContactClick}
              >
                <FaYoutube size={24} />
              </a>
            )}
            {(business.contact?.social_media?.tiktok || business.social_media?.tiktok) && (
              <a 
                href={business.contact?.social_media?.tiktok || business.social_media?.tiktok} 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-black hover:text-gray-700"
                onClick={handleContactClick}
              >
                <FaTiktok size={24} />
              </a>
            )}
          </div>
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
        
        {business.contact.whatsapp && (
          <a 
            href={`https://wa.me/${business.contact.whatsapp.replace(/[^0-9]/g, '')}`} 
            className="btn btn-success w-full flex items-center justify-center bg-green-500 hover:bg-green-600 text-white"
            target="_blank"
            rel="noopener noreferrer"
            onClick={handleContactClick}
          >
            <FaWhatsapp className="h-5 w-5 mr-2" />
            WhatsApp
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