import React, { useState, useEffect } from 'react';
import { FaFacebook, FaInstagram, FaTwitter, FaLinkedin, FaYoutube, FaTiktok } from 'react-icons/fa';

function SocialMediaManagement({ businessData, onUpdate }) {
  const [socialLinks, setSocialLinks] = useState({
    facebook: '',
    instagram: '',
    twitter: '',
    linkedin: '',
    youtube: '',
    tiktok: ''
  });
  
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  
  // For Gold tier feature
  const [featurePost, setFeaturePost] = useState({
    platform: 'facebook',
    content: '',
    image: null
  });
  
  useEffect(() => {
    // Initialize form with existing data
    if (businessData.social_media) {
      setSocialLinks(businessData.social_media);
    }
  }, [businessData]);
  
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setSocialLinks(prev => ({
      ...prev,
      [name]: value
    }));
  };
  
  const handleFeatureInputChange = (e) => {
    const { name, value } = e.target;
    setFeaturePost(prev => ({
      ...prev,
      [name]: value
    }));
  };
  
  const handleImageChange = (e) => {
    setFeaturePost(prev => ({
      ...prev,
      image: e.target.files[0]
    }));
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    setError('');
    setSuccess('');
    
    try {
      const token = localStorage.getItem('mpbh_token');
      const response = await fetch(`/api/business/social-media`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        },
        body: JSON.stringify(socialLinks)
      });
      
      const data = await response.json();
      
      if (response.ok) {
        setSuccess('Social media links updated successfully');
        if (onUpdate) onUpdate(data.data);
      } else {
        setError(data.message || 'Failed to update social media links');
      }
    } catch (err) {
      setError('An error occurred. Please try again.');
      console.error(err);
    } finally {
      setIsSubmitting(false);
    }
  };
  
  const handleFeatureSubmit = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    setError('');
    setSuccess('');
    
    try {
      const token = localStorage.getItem('mpbh_token');
      const formData = new FormData();
      formData.append('platform', featurePost.platform);
      formData.append('content', featurePost.content);
      if (featurePost.image) {
        formData.append('image', featurePost.image);
      }
      
      const response = await fetch(`/api/business/social-media/feature`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        },
        body: formData
      });
      
      const data = await response.json();
      
      if (response.ok) {
        setSuccess('Social media feature post created successfully');
        setFeaturePost({
          platform: 'facebook',
          content: '',
          image: null
        });
        if (onUpdate) onUpdate({ ...businessData, social_features_remaining: data.remaining_features });
      } else {
        setError(data.message || 'Failed to create social media feature post');
      }
    } catch (err) {
      setError('An error occurred. Please try again.');
      console.error(err);
    } finally {
      setIsSubmitting(false);
    }
  };
  
  const canAccessSocialLinks = ['Bronze', 'Silver', 'Gold'].includes(businessData.package_type);
  const canAccessFeaturePost = businessData.package_type === 'Gold' && businessData.social_features_remaining > 0;
  
  if (!canAccessSocialLinks) {
    return (
      <div className="bg-white p-6 rounded-lg shadow-md">
        <h2 className="text-xl font-semibold mb-4">Social Media Management</h2>
        <div className="bg-yellow-50 p-4 rounded border border-yellow-200">
          <p className="text-yellow-700">
            Social media links are available with Bronze package and higher. 
            <a href="/dashboard/upgrade" className="text-blue-600 ml-2 font-medium">Upgrade now</a>
          </p>
        </div>
      </div>
    );
  }
  
  return (
    <div className="bg-white p-6 rounded-lg shadow-md">
      <h2 className="text-xl font-semibold mb-4">Social Media Management</h2>
      
      {error && (
        <div className="bg-red-50 p-4 rounded border border-red-200 mb-4">
          <p className="text-red-700">{error}</p>
        </div>
      )}
      
      {success && (
        <div className="bg-green-50 p-4 rounded border border-green-200 mb-4">
          <p className="text-green-700">{success}</p>
        </div>
      )}
      
      <form onSubmit={handleSubmit} className="mb-8">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label className="block mb-1 font-medium flex items-center">
              <FaFacebook className="mr-2 text-blue-600" /> Facebook
            </label>
            <input
              type="url"
              name="facebook"
              value={socialLinks.facebook || ''}
              onChange={handleInputChange}
              placeholder="https://facebook.com/yourbusiness"
              className="w-full p-2 border rounded"
            />
          </div>
          
          <div>
            <label className="block mb-1 font-medium flex items-center">
              <FaInstagram className="mr-2 text-pink-600" /> Instagram
            </label>
            <input
              type="url"
              name="instagram"
              value={socialLinks.instagram || ''}
              onChange={handleInputChange}
              placeholder="https://instagram.com/yourbusiness"
              className="w-full p-2 border rounded"
            />
          </div>
          
          <div>
            <label className="block mb-1 font-medium flex items-center">
              <FaTwitter className="mr-2 text-blue-400" /> Twitter
            </label>
            <input
              type="url"
              name="twitter"
              value={socialLinks.twitter || ''}
              onChange={handleInputChange}
              placeholder="https://twitter.com/yourbusiness"
              className="w-full p-2 border rounded"
            />
          </div>
          
          <div>
            <label className="block mb-1 font-medium flex items-center">
              <FaLinkedin className="mr-2 text-blue-700" /> LinkedIn
            </label>
            <input
              type="url"
              name="linkedin"
              value={socialLinks.linkedin || ''}
              onChange={handleInputChange}
              placeholder="https://linkedin.com/company/yourbusiness"
              className="w-full p-2 border rounded"
            />
          </div>
          
          <div>
            <label className="block mb-1 font-medium flex items-center">
              <FaYoutube className="mr-2 text-red-600" /> YouTube
            </label>
            <input
              type="url"
              name="youtube"
              value={socialLinks.youtube || ''}
              onChange={handleInputChange}
              placeholder="https://youtube.com/c/yourbusiness"
              className="w-full p-2 border rounded"
            />
          </div>
          
          <div>
            <label className="block mb-1 font-medium flex items-center">
              <FaTiktok className="mr-2" /> TikTok
            </label>
            <input
              type="url"
              name="tiktok"
              value={socialLinks.tiktok || ''}
              onChange={handleInputChange}
              placeholder="https://tiktok.com/@yourbusiness"
              className="w-full p-2 border rounded"
            />
          </div>
        </div>
        
        <button
          type="submit"
          disabled={isSubmitting}
          className="px-4 py-2 bg-brand-primary text-white rounded hover:bg-brand-primary-dark transition-colors disabled:opacity-50"
        >
          {isSubmitting ? 'Saving...' : 'Save Social Links'}
        </button>
      </form>
      
      {canAccessFeaturePost && (
        <>
          <h3 className="text-lg font-semibold mb-3 mt-8">Create Social Media Feature Post</h3>
          <div className="bg-blue-50 p-4 rounded border border-blue-200 mb-4">
            <p className="text-blue-700">
              As a Gold tier member, you can create {businessData.social_features_remaining} more featured social media post{businessData.social_features_remaining !== 1 ? 's' : ''} this month.
            </p>
          </div>
          
          <form onSubmit={handleFeatureSubmit}>
            <div className="mb-4">
              <label className="block mb-1 font-medium">Platform</label>
              <select
                name="platform"
                value={featurePost.platform}
                onChange={handleFeatureInputChange}
                className="w-full p-2 border rounded"
              >
                <option value="facebook">Facebook</option>
                <option value="instagram">Instagram</option>
                <option value="twitter">Twitter</option>
                <option value="linkedin">LinkedIn</option>
              </select>
            </div>
            
            <div className="mb-4">
              <label className="block mb-1 font-medium">Content</label>
              <textarea
                name="content"
                value={featurePost.content}
                onChange={handleFeatureInputChange}
                placeholder="Write your post content here..."
                className="w-full p-2 border rounded h-32"
                maxLength={500}
              ></textarea>
              <p className="text-xs text-right text-gray-500">
                {featurePost.content.length}/500 characters
              </p>
            </div>
            
            <div className="mb-4">
              <label className="block mb-1 font-medium">Image (optional)</label>
              <input
                type="file"
                name="image"
                onChange={handleImageChange}
                accept="image/*"
                className="w-full p-2 border rounded"
              />
            </div>
            
            <button
              type="submit"
              disabled={isSubmitting}
              className="px-4 py-2 bg-brand-primary text-white rounded hover:bg-brand-primary-dark transition-colors disabled:opacity-50"
            >
              {isSubmitting ? 'Creating...' : 'Create Feature Post'}
            </button>
          </form>
        </>
      )}
    </div>
  );
}

export default SocialMediaManagement;