import { useState, useRef } from 'react';

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

export default ImageUpload;