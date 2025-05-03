import { useState, useEffect } from 'react'
import axios from 'axios'
import { useParams, Link } from 'react-router-dom'
import useBusiness from '../hooks/useBusiness';
import LoadingSkeleton from '../components/ui/LoadingSkeleton';
import ErrorMessage from '../components/ui/ErrorMessage';
import BusinessHeader from '../components/business/details/BusinessHeader';
import ContactInfo from '../components/business/details/ContactInfo';
import ProductsGrid from '../components/business/details/ProductsGrid';
import ReviewForm from '../components/business/details/ReviewForm';
import ReviewsList from '../components/business/details/ReviewsList';

function BusinessDetails() {
  const { id } = useParams()
  const {
    business,
    loading,
    error,
    userRating,
    reviewText,
    isSubmitting,
    reviewSuccess,
    isAuthenticated,
    setReviewText,
    handleRatingChange,
    handleReviewSubmit
  } = useBusiness(id);

  useEffect(() => {
    // Track view when component mounts
    const trackView = async () => {
      if (business && business.id) {
        try {
          await axios.post(`/api/businesses/${business.id}/view`);
        } catch (error) {
          console.error('Error tracking view:', error);
        }
      }
    };
    
    trackView();
  }, [business]);

  const handleContactClick = async () => {
    if (business && business.id) {
      try {
        await axios.post(`/api/businesses/${business.id}/contact`);
      } catch (error) {
        console.error('Error tracking contact click:', error);
      }
    }
  };

  if (loading) return <LoadingSkeleton />;
  if (error) return <ErrorMessage message={error} />;
  if (!business) return <ErrorMessage message="Business not found" />;
  
  return (
    <div className="container mx-auto px-4 py-8">
      <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div className="md:col-span-2">
          <BusinessHeader business={business} />
          
          <div className="bg-white rounded-lg shadow-md p-6 mb-6">
            <div className="relative h-64 bg-gray-200 rounded-lg mb-6">
              {business.image_url ? (
                <img 
                  src={business.image_url} 
                  alt={business.name} 
                  className="w-full h-full object-cover rounded-lg"
                />
              ) : (
                <div className="flex items-center justify-center h-full">
                  <svg className="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              )}
            </div>
            
            <h2 className="text-xl font-bold mb-4">About</h2>
            <p className="text-gray-700 mb-6">{business.description || 'No description provided.'}</p>
            
            <ProductsGrid business={business} />
          </div>
          
          <ReviewForm
            userRating={userRating}
            reviewText={reviewText}
            isSubmitting={isSubmitting}
            reviewSuccess={reviewSuccess}
            isAuthenticated={isAuthenticated}
            setReviewText={setReviewText}
            handleRatingChange={handleRatingChange}
            handleReviewSubmit={handleReviewSubmit}
          />
          
          <ReviewsList reviews={business.reviews} />
        </div>
        
        <div className="md:col-span-1">
          <ContactInfo business={business} />
          
          {business.hours && (
            <div className="bg-white rounded-lg shadow-md p-6 mb-6">
              <h2 className="text-xl font-bold mb-4">Business Hours</h2>
              <div className="space-y-2">
                {Object.entries(business.hours).map(([day, hours]) => (
                  <div key={day} className="flex justify-between items-center">
                    <div className="capitalize">{day}</div>
                    <div className="font-medium">{hours}</div>
                  </div>
                ))}
              </div>
            </div>
          )}
          {business.location && (
            <div className="bg-white rounded-lg shadow-md p-6 mb-6">
              <h2 className="text-xl font-bold mb-4">Location</h2>
              <div className="h-64 bg-gray-200 rounded-lg mb-4">
                <div className="flex items-center justify-center h-full">
                  <p className="text-gray-500">Map would display here</p>
                </div>
              </div>
              {business.contact?.address && (
                <address className="not-italic text-gray-700">{business.contact.address}</address>
              )}
              {business.location.lat && business.location.lng && (
                <a 
                  href={`https://www.google.com/maps/search/?api=1&query=${business.location.lat},${business.location.lng}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="mt-4 inline-block text-blue-600 hover:text-blue-800"
                >
                  Get Directions
                </a>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default BusinessDetails;