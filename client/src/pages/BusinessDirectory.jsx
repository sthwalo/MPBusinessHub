import { useState, useEffect } from 'react'
import { useSearchParams } from 'react-router-dom'
import Fuse from 'fuse.js'
import BusinessCard from '../components/BusinessCard'
import { Link } from 'react-router-dom'
import { businessMetadataApi } from '../utils/api'

// Main Business Directory component
function BusinessDirectory() {
  // State management
  const [searchParams, setSearchParams] = useSearchParams()
  const [businesses, setBusinesses] = useState([]) // All businesses from API
  const [loading, setLoading] = useState(true) // Loading state
  const [error, setError] = useState(null) // Error state
  const [searchTerm, setSearchTerm] = useState(searchParams.get('search') || '') // Search input
  const [selectedCategory, setSelectedCategory] = useState(searchParams.get('category') || '') // Selected category filter
  const [selectedDistrict, setSelectedDistrict] = useState(searchParams.get('district') || '') // Selected district filter
  const [filteredBusinesses, setFilteredBusinesses] = useState([]) // Filtered businesses based on search and filters
  const [debugMode, setDebugMode] = useState(false) // Debug mode toggle
  const [activeAdverts, setActiveAdverts] = useState([]) // Active adverts from API
  const [categories, setCategories] = useState([]) // Categories from API
  const [districts, setDistricts] = useState([]) // Districts from API
  const [isLoadingMetadata, setIsLoadingMetadata] = useState(true) // Loading state for categories and districts

  // Fetch businesses from API with filters
  useEffect(() => {
    const fetchBusinesses = async () => {
      try {
        setLoading(true)
        const queryParams = new URLSearchParams()
        
        // Add filters to query parameters
        if (selectedCategory) queryParams.append('category', selectedCategory)
        if (selectedDistrict) queryParams.append('district', selectedDistrict)
        
        // Fetch data from API - using the working /api/businesses/check endpoint
        const response = await fetch(`/api/businesses/check`)
        
        if (!response.ok) {
          throw new Error(`API request failed with status ${response.status}`)
        }
        
        const data = await response.json()
        
        if (data.status === 'success' && data.data) {
          // Transform the data to match the expected format if needed
          const transformedData = data.data.map(business => ({
            id: business.id,
            name: business.name,
            category: business.category,
            district: business.district,
            description: business.description,
            package_type: business.package_type, // Use the actual package type from the backend
            rating: business.rating, // Use the actual rating from the backend
            review_count: business.review_count || 0, // Use the actual review count from the backend
            contact: business.contact || {
              phone: business.phone,
              email: business.user ? business.user.email : null,
              website: business.website,
              address: business.address
            },
            image_url: business.image_url
          }));
          // Update state with fetched data
          setBusinesses(transformedData)
          setFilteredBusinesses(transformedData)
        } else {
          throw new Error(data.message || 'Failed to fetch businesses')
        }
        
      } catch (err) {
        // Log error for debugging
        console.error('Error fetching businesses:', err)
        setError(err.message)
      } finally {
        setLoading(false)
      }
    }
    
    // Execute the fetch function
    fetchBusinesses()
  }, [selectedCategory, selectedDistrict]) // Re-fetch when filters change

  // Fetch active adverts
  useEffect(() => {
    const fetchActiveAdverts = async () => {
      try {
        const response = await fetch('/api/adverts/active')
        
        if (!response.ok) {
          throw new Error(`API request failed with status ${response.status}`)
        }
        
        const data = await response.json()
        
        if (data.status === 'success' && data.data) {
          setActiveAdverts(data.data)
        }
      } catch (err) {
        console.error('Error fetching active adverts:', err)
        // Don't set error state here as it would affect the whole page
      }
    }
    
    fetchActiveAdverts()
  }, []) // Only fetch once on component mount

  // Fetch categories and districts from API
  useEffect(() => {
    const fetchMetadata = async () => {
      try {
        setIsLoadingMetadata(true)
        const [categoriesResponse, districtsResponse] = await Promise.all([
          businessMetadataApi.getCategories(),
          businessMetadataApi.getDistricts()
        ])
        
        if (categoriesResponse.status === 'success' && categoriesResponse.data) {
          setCategories(categoriesResponse.data)
        } else {
          console.error('Error fetching categories:', categoriesResponse)
          // Fallback to default categories if API fails
          setCategories(['Tourism', 'Agriculture', 'Construction', 'Events'])
        }
        
        if (districtsResponse.status === 'success' && districtsResponse.data) {
          setDistricts(districtsResponse.data)
        } else {
          console.error('Error fetching districts:', districtsResponse)
          // Fallback to default districts if API fails
          setDistricts(['Mbombela', 'Emalahleni', 'Bushbuckridge'])
        }
      } catch (error) {
        console.error('Error fetching metadata:', error)
        // Fallback to default values if API fails
        setCategories(['Tourism', 'Agriculture', 'Construction', 'Events'])
        setDistricts(['Mbombela', 'Emalahleni', 'Bushbuckridge'])
      } finally {
        setIsLoadingMetadata(false)
      }
    }
    
    fetchMetadata()
  }, [])

  // Update URL parameters when filters change
  useEffect(() => {
    const params = new URLSearchParams()
    if (searchTerm) params.set('search', searchTerm)
    if (selectedCategory) params.set('category', selectedCategory)
    if (selectedDistrict) params.set('district', selectedDistrict)
    setSearchParams(params)
  }, [searchTerm, selectedCategory, selectedDistrict, setSearchParams])

  // Handle search functionality using Fuse.js
  useEffect(() => {
    if (searchTerm) {
      const fuse = new Fuse(businesses, {
        keys: ['name', 'description', 'category'],
        threshold: 0.4,
      })
      const results = fuse.search(searchTerm).map(result => result.item)
      setFilteredBusinesses(results)
    } else {
      // Apply only category and district filters if no search term
      let filtered = [...businesses]
      
      if (selectedCategory) {
        filtered = filtered.filter(business => business.category === selectedCategory)
      }
      
      if (selectedDistrict) {
        filtered = filtered.filter(business => business.district === selectedDistrict)
      }
      
      setFilteredBusinesses(filtered)
    }
  }, [searchTerm, businesses, selectedCategory, selectedDistrict])

  // Handle search input changes
  const handleSearchChange = (e) => {
    setSearchTerm(e.target.value)
  }

  // Handle category selection
  const handleCategoryChange = (category) => {
    setSelectedCategory(category === selectedCategory ? '' : category)
  }

  // Handle district selection
  const handleDistrictChange = (district) => {
    setSelectedDistrict(district === selectedDistrict ? '' : district)
  }

  // Clear all filters
  const clearFilters = () => {
    setSearchTerm('')
    setSelectedCategory('')
    setSelectedDistrict('')
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold mb-8">Business Directory</h1>
      
      {/* Search and Filters section */}
      <div className="bg-white rounded-lg shadow-md p-6 mb-8">
        <div className="mb-6">
          <label htmlFor="search" className="sr-only">Search businesses</label>
          <div className="relative">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              type="text"
              id="search"
              className="form-control pl-10"
              placeholder="Search by business name or description"
              value={searchTerm}
              onChange={handleSearchChange}
            />
          </div>
        </div>
        
        {/* Debug toggle button */}
        {/* <div className="mb-6">
          <button onClick={() => setDebugMode(!debugMode)}>
            {debugMode ? 'Hide Debug' : 'Show Debug'}
          </button>
        </div> */}
      
        {/* Debug information display */}
        {debugMode && (
          <pre style={{ 
            whiteSpace: 'pre-wrap', 
            backgroundColor: '#f0f0f0', 
            padding: '1rem', 
            borderRadius: '4px',
            overflow: 'auto'
          }}>
            {JSON.stringify({
              businesses: businesses.length,
              filtered: filteredBusinesses.length,
              error,
              loading,
              searchTerm,
              selectedCategory,
              selectedDistrict
            }, null, 2)}
          </pre>
        )}
        
        {/* Filters grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          {/* Categories filter */}
          <div>
            <h3 className="font-semibold mb-3">Categories</h3>
            <div className="flex flex-wrap gap-2">
              {isLoadingMetadata ? (
                <div>Loading categories...</div>
              ) : (
                categories.map(category => (
                  <button
                    key={category}
                    className={`px-4 py-2 rounded-full text-sm ${selectedCategory === category 
                      ? 'bg-blue-600 text-white' 
                      : 'bg-gray-100 text-gray-800 hover:bg-gray-200'}`}
                    onClick={() => handleCategoryChange(category)}
                  >
                    {category}
                  </button>
                ))
              )}
            </div>
          </div>
          
          {/* Districts filter */}
          <div>
            <h3 className="font-semibold mb-3">Districts</h3>
            <div className="flex flex-wrap gap-2">
              {isLoadingMetadata ? (
                <div>Loading districts...</div>
              ) : (
                districts.map(district => (
                  <button
                    key={district}
                    className={`px-4 py-2 rounded-full text-sm ${selectedDistrict === district 
                      ? 'bg-blue-600 text-white' 
                      : 'bg-gray-100 text-gray-800 hover:bg-gray-200'}`}
                    onClick={() => handleDistrictChange(district)}
                  >
                    {district}
                  </button>
                ))
              )}
            </div>
          </div>
        </div>
        
        {/* Active filters display */}
        {(searchTerm || selectedCategory || selectedDistrict) && (
          <div className="flex items-center justify-between">
            <div className="text-sm text-gray-600">
              <span className="font-medium">{filteredBusinesses.length}</span> businesses found
            </div>
            <button 
              className="text-sm text-red-600 hover:text-red-800 font-medium"
              onClick={clearFilters}
            >
              Clear all filters
            </button>
          </div>
        )}
      </div>
      
      {/* Featured Adverts Section - only show if there are active adverts */}
      {activeAdverts.length > 0 && (
        <div className="mb-10">
          <h2 className="text-2xl font-semibold mb-4">Featured Businesses</h2>
          <div className="bg-blue-50 p-4 rounded-lg mb-6">
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {activeAdverts.map(advert => (
                <div key={advert.id} className="bg-white rounded-lg shadow-md overflow-hidden border-2 border-blue-400">
                  <div className="p-4">
                    <div className="flex justify-between items-start">
                      <h3 className="font-bold text-xl">{advert.title}</h3>
                      <span className="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">Sponsored</span>
                    </div>
                    <p className="text-gray-600 mt-2">{advert.description}</p>
                    <div className="mt-4 pt-4 border-t border-gray-200">
                      <p className="text-sm font-medium text-gray-900">{advert.business?.name}</p>
                      <Link to={`/business/${advert.business_id}`} className="mt-2 inline-block text-blue-600 hover:text-blue-800">
                        View Business →
                      </Link>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}
      
      {/* Business Listings section */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        {loading ? (
          // Loading state - show skeleton cards
          Array.from({ length: 6 }).map((_, index) => (
            <div key={index} className="bg-white rounded-lg shadow-md overflow-hidden animate-pulse">
              <div className="h-48 bg-gray-200"></div>
              <div className="p-4">
                <div className="h-6 bg-gray-200 rounded mb-2"></div>
                <div className="h-4 bg-gray-200 rounded mb-2 w-2/3"></div>
                <div className="h-4 bg-gray-200 rounded mb-2 w-1/2"></div>
              </div>
            </div>
          ))
        ) : error ? (
          // Error state - show error message
          <div className="col-span-3 text-center py-8">
            <svg className="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 className="mt-2 text-lg font-medium text-red-800">{error}</h3>
            <p className="mt-1 text-gray-500">Using mock data instead. In production, this would connect to the backend API.</p>
          </div>
        ) : filteredBusinesses.length === 0 ? (
          // No results state - show empty state message
          <div className="col-span-3 text-center py-8">
            <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 className="mt-2 text-lg font-medium text-gray-900">No businesses found</h3>
            <p className="mt-1 text-gray-500">Try adjusting your search or filter criteria</p>
            <button 
              className="mt-4 text-blue-600 hover:text-blue-800 font-medium"
              onClick={clearFilters}
            >
              Clear all filters
            </button>
          </div>
        ) : (
          // Success state - render business cards
          filteredBusinesses.map(business => (
            <BusinessCard key={business.id} business={business} />
          ))
        )}
      </div>
    </div>
  )
}

export default BusinessDirectory