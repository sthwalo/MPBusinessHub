import { useState, useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'

function BusinessDetails() {
  const { id } = useParams()
  const [business, setBusiness] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [userRating, setUserRating] = useState(0)
  const [reviewText, setReviewText] = useState('')
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [reviewSuccess, setReviewSuccess] = useState(false)
  const [isAuthenticated, setIsAuthenticated] = useState(false)

  useEffect(() => {
    const token = localStorage.getItem('mpbh_token')
    setIsAuthenticated(!!token)
    
    const fetchBusinessDetails = async () => {
      try {
        setLoading(true)
        const response = await fetch(`/api/businesses/${id}`, {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        })
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        
        const data = await response.json()
        setBusiness(data)
      } catch (err) {
        console.error("Error fetching business details:", err)
        setError("Failed to load business details. Please try again later.")
      } finally {
        setLoading(false)
      }
    }
    
    fetchBusinessDetails()
    window.scrollTo(0, 0)
  }, [id])

  const handleRatingChange = (rating) => {
    setUserRating(rating)
  }

  const handleReviewSubmit = async (e) => {
    e.preventDefault()
    
    if (!isAuthenticated) {
      alert('Please log in to submit a review')
      return
    }
    
    if (userRating === 0) {
      alert('Please select a rating')
      return
    }
    
    setIsSubmitting(true)
    
    try {
      const token = localStorage.getItem('mpbh_token')
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
      })
      
      const data = await response.json()
      
      if (!response.ok) {
        throw new Error(data.message || 'Failed to submit review')
      }
      
      const newReview = data.data
      setBusiness(prev => ({
        ...prev,
        reviews: [...(prev.reviews || []), newReview],
        rating: prev.reviews && prev.reviews.length > 0 
          ? ((prev.reviews.reduce((sum, r) => sum + r.rating, 0) + userRating) / (prev.reviews.length + 1))
          : userRating
      }))
      
      setUserRating(0)
      setReviewText('')
      setReviewSuccess(true)
      setTimeout(() => setReviewSuccess(false), 5000)
    } catch (error) {
      console.error('Error submitting review:', error)
      alert('Failed to submit review. Please try again.')
    } finally {
      setIsSubmitting(false)
    }
  }

  const canShowProducts = business?.package_type === 'Silver' || business?.package_type === 'Gold'
  const canShowContactInfo = business?.package_type !== 'Basic'

  const RatingStars = ({ interactive = false, value = 0, onChange }) => (
    <div className="flex items-center">
      {[1, 2, 3, 4, 5].map((star) => (
        <button
          key={star}
          type="button"
          onClick={() => interactive && onChange && onChange(star)}
          className={`${interactive ? 'cursor-pointer hover:text-yellow-400' : 'cursor-default'} focus:outline-none`}
          disabled={!interactive}
        >
          <svg 
            className={`h-8 w-8 ${value >= star ? 'text-yellow-400' : 'text-gray-300'}`}
            fill="currentColor" 
            viewBox="0 0 20 20"
          >
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        </button>
      ))}
    </div>
  )

  const ReviewForm = () => (
    <div className="bg-brand-white rounded-lg shadow-brand-md p-6 mb-6">
      <h3 className="text-xl font-bold font-serif text-brand-black mb-4">Write a Review</h3>
      
      {!isAuthenticated ? (
        <div className="bg-brand-gray-100 p-4 rounded-lg text-center">
          <p className="text-brand-gray-700 mb-3">Please log in to write a review</p>
          <Link to="/login" className="px-4 py-2 bg-brand-black text-brand-white rounded-md hover:bg-brand-gray-800 transition-colors">
            Log In
          </Link>
        </div>
      ) : reviewSuccess ? (
        <div className="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">
          <div className="flex items-center">
            <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>Thank you! Your review has been submitted.</span>
          </div>
        </div>
      ) : (
        <form onSubmit={handleReviewSubmit}>
          <div className="mb-4">
            <label className="block text-brand-gray-700 mb-2">Your Rating</label>
            <RatingStars interactive={true} value={userRating} onChange={handleRatingChange} />
          </div>
          
          <div className="mb-4">
            <label htmlFor="reviewText" className="block text-brand-gray-700 mb-2">Your Review</label>
            <textarea
              id="reviewText"
              value={reviewText}
              onChange={(e) => setReviewText(e.target.value)}
              className="w-full px-3 py-2 border border-brand-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-black"
              rows="4"
              placeholder="Share your experience with this business..."
              required
            ></textarea>
          </div>
          
          <button
            type="submit"
            disabled={isSubmitting}
            className={`px-4 py-2 bg-brand-black text-brand-white rounded-md hover:bg-brand-gray-800 transition-colors ${isSubmitting ? 'opacity-75 cursor-wait' : ''}`}
          >
            {isSubmitting ? (
              <>
                <svg className="animate-spin h-5 w-5 mr-2 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Submitting...
              </>
            ) : 'Submit Review'}
          </button>
        </form>
      )}
    </div>
  )

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-12">
        <div className="animate-pulse">
          <div className="h-8 bg-gray-200 rounded w-1/3 mb-6"></div>
          <div className="h-64 bg-gray-200 rounded mb-6"></div>
          <div className="h-4 bg-gray-200 rounded w-2/3 mb-4"></div>
          <div className="h-4 bg-gray-200 rounded mb-4"></div>
          <div className="h-4 bg-gray-200 rounded w-1/2 mb-8"></div>
          <div className="h-32 bg-gray-200 rounded mb-6"></div>
        </div>
      </div>
    )
  }

  if (error || !business) {
    return (
      <div className="container mx-auto px-4 py-12 text-center">
        <h2 className="text-2xl font-bold text-red-600 mb-4">Error Loading Business</h2>
        <p className="mb-6">{error || 'Business not found'}</p>
        <Link to="/directory" className="btn btn-primary">Return to Directory</Link>
      </div>
    )
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <Link to="/directory" className="flex items-center text-blue-600 mb-6 hover:text-blue-800">
        <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Directory
      </Link>
      
      <div className="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold mb-2">{business.name}</h1>
          <div className="flex items-center text-gray-600 mb-1">
            <svg className="h-5 w-5 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            {business.district}
          </div>
          <div className="flex items-center">
            <svg className="h-5 w-5 mr-1 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <span className="ml-1">{business.rating ? business.rating.toFixed(1) : '0.0'} ({business.reviews?.length || 0} reviews)</span>
          </div>
        </div>
        
        {business.package_type !== 'Basic' && (
          <div className="mt-4 md:mt-0">
            <span className="inline-block bg-gray-200 text-gray-800 text-xs px-2 py-1 rounded-full font-medium uppercase">
              {business.package_type} Package
            </span>
          </div>
        )}
      </div>
      
      <div className="flex flex-col lg:flex-row gap-8">
        <div className="lg:w-2/3">
          <div className="bg-gray-200 rounded-lg h-64 mb-6 flex items-center justify-center">
            {business.images?.[0] ? (
              <img src={business.images[0]} alt={business.name} className="w-full h-full object-cover rounded-lg" />
            ) : (
              <svg className="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            )}
          </div>
          
          <div className="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 className="text-xl font-bold mb-4">About {business.name}</h2>
            <p className="text-gray-700">
              {business.description || 'No description available.'}
            </p>
          </div>
          
          {canShowProducts && business.products && business.products.length > 0 && (
            <div className="bg-white rounded-lg shadow-md p-6 mb-6">
              <h2 className="text-xl font-bold mb-4">Products & Services</h2>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {business.products.map(product => (
                  <div key={product.id} className="border rounded-lg overflow-hidden">
                    <div className="h-48 bg-gray-200">
                      {product.image ? (
                        <img src={product.image} alt={product.name} className="w-full h-full object-cover" />
                      ) : (
                        <div className="flex items-center justify-center h-full">
                          <svg className="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                          </svg>
                        </div>
                      )}
                    </div>
                    <div className="p-4">
                      <h3 className="font-bold text-lg mb-1">{product.name}</h3>
                      <p className="text-gray-700 text-sm mb-2">{product.description}</p>
                      {product.price && (
                        <div className="font-bold text-green-700">R{product.price.toFixed(2)}</div>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
          
          <ReviewForm />
          
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-2xl font-bold mb-6">Customer Reviews</h2>
            {business.reviews?.length > 0 ? (
              <div className="space-y-6">
                {business.reviews.map(review => (
                  <div key={review.id} className="border-b pb-6 last:border-b-0 last:pb-0">
                    <div className="flex justify-between items-start mb-2">
                      <div>
                        <div className="font-semibold">{review.user}</div>
                        <div className="text-gray-500 text-sm">{new Date(review.date).toLocaleDateString()}</div>
                      </div>
                      <div className="flex items-center">
                        <div className="text-yellow-400">
                          <RatingStars value={review.rating} />
                        </div>
                      </div>
                    </div>
                    <p className="text-gray-700">{review.comment}</p>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-gray-500">No reviews yet. Be the first to review this business!</p>
            )}
          </div>
        </div>
        
        <div className="lg:w-1/3">
          {canShowContactInfo && (
            <div className="bg-white rounded-lg shadow-md p-6 mb-6">
              <h2 className="text-xl font-bold mb-4">Contact Information</h2>
              
              {business.contact?.phone && (
                <div className="flex items-center mb-3">
                  <svg className="h-5 w-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <span>{business.contact.phone}</span>
                </div>
              )}
              
              {business.contact?.email && (
                <div className="flex items-center mb-3">
                  <svg className="h-5 w-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <span>{business.contact.email}</span>
                </div>
              )}
              
              {business.contact?.website && (
                <div className="flex items-center mb-3">
                  <svg className="h-5 w-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                  </svg>
                  <a href={business.contact.website} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:text-blue-800 truncate">
                    {business.contact.website.replace(/(^https?:\/\/|\/$)/g, '')}
                  </a>
                </div>
              )}
              
              {business.contact?.address && (
                <div className="flex items-start mb-3">
                  <svg className="h-5 w-5 text-gray-500 mr-3 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span>{business.contact.address}</span>
                </div>
              )}
              
              <div className="mt-6 space-y-3">
                {business.contact?.phone && (
                  <a 
                    href={`tel:${business.contact.phone}`} 
                    className="btn btn-primary w-full flex items-center justify-center"
                  >
                    <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    Call Now
                  </a>
                )}
                
                {business.contact?.email && (
                  <a 
                    href={`mailto:${business.contact.email}`} 
                    className="btn btn-outline w-full flex items-center justify-center"
                  >
                    <svg className="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Email
                  </a>
                )}
              </div>
            </div>
          )}
          
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
              {canShowContactInfo && business.location && business.location.lat && business.location.lng && (
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
          
          {!canShowContactInfo && (
            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
              <h3 className="text-lg font-bold text-yellow-800 mb-2">Contact Information Hidden</h3>
              <p className="text-yellow-700 mb-4">This business is on a Basic membership plan. Contact information is only visible for Bronze tier and above.</p>
              <Link to="/pricing" className="btn btn-primary w-full">View Membership Options</Link>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}

export default BusinessDetails