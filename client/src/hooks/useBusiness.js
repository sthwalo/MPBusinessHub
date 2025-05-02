import { useState, useEffect, useCallback } from 'react';
import { calculateAverageRating } from '../utils/businessUtils';

/**
 * Custom hook for fetching and managing business details
 * @param {string} id - Business ID
 * @returns {Object} Business data, loading state, error state, and functions
 */
export default function useBusiness(id) {
  const [business, setBusiness] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [userRating, setUserRating] = useState(0);
  const [reviewText, setReviewText] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [reviewSuccess, setReviewSuccess] = useState(false);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  // Check authentication status
  useEffect(() => {
    const token = localStorage.getItem('mpbh_token');
    setIsAuthenticated(!!token);
  }, []);

  // Fetch business details
  useEffect(() => {
    const fetchBusinessDetails = async () => {
      try {
        setLoading(true);
        const response = await fetch(`/api/businesses/${id}`, {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        });
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Business data:', data); // Debug log
        setBusiness(data);
      } catch (err) {
        console.error("Error fetching business details:", err);
        setError("Failed to load business details. Please try again later.");
      } finally {
        setLoading(false);
      }
    };
    
    fetchBusinessDetails();
    window.scrollTo(0, 0); // Scroll to top when business changes
  }, [id]);

  // Handle rating change
  const handleRatingChange = useCallback((rating) => {
    setUserRating(rating);
  }, []);

  // Handle review submission
  const handleReviewSubmit = useCallback(async (e) => {
    e.preventDefault();
    
    if (!isAuthenticated) {
      alert('Please log in to submit a review');
      return;
    }
    
    if (userRating === 0) {
      alert('Please select a rating');
      return;
    }
    
    setIsSubmitting(true);
    
    try {
      const token = localStorage.getItem('mpbh_token');
      const response = await fetch(`/api/reviews`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          business_id: business.id,
          rating: userRating,
          comment: reviewText
        })
      });
      
      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.message || 'Failed to submit review');
      }
      
      const newReview = data.data;
      setBusiness(prev => {
        if (!prev) return prev;
        
        const updatedReviews = [...(prev.reviews || []), newReview];
        return {
          ...prev,
          reviews: updatedReviews,
          rating: calculateAverageRating(prev.reviews, userRating)
        };
      });
      
      setUserRating(0);
      setReviewText('');
      setReviewSuccess(true);
      setTimeout(() => setReviewSuccess(false), 5000);
    } catch (error) {
      console.error('Error submitting review:', error);
      alert('Failed to submit review. Please try again.');
    } finally {
      setIsSubmitting(false);
    }
  }, [business, isAuthenticated, reviewText, userRating]);

  return {
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
  };
}