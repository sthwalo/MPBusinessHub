import { useState, useEffect, useMemo, useCallback, useRef } from 'react'
import { useSearchParams } from 'react-router-dom'
import BusinessCard from '../components/BusinessCard'
import { Link } from 'react-router-dom'
import { businessMetadataApi } from '../utils/api'
import { businessService } from '../services/businessService'
import { advertService } from '../services/advertService'
import { FALLBACK_CATEGORIES, FALLBACK_DISTRICTS, SEARCH_DEBOUNCE, RESULTS_PER_PAGE } from '../constants/businessDirectory'

function BusinessDirectory() {
  const [searchParams, setSearchParams] = useSearchParams()
  const [businesses, setBusinesses] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [searchInput, setSearchInput] = useState(searchParams.get('search') || '')
  const [selectedCategory, setSelectedCategory] = useState(searchParams.get('category') || '')
  const [selectedDistrict, setSelectedDistrict] = useState(searchParams.get('district') || '')
  const [currentPage, setCurrentPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)
  const [activeAdverts, setActiveAdverts] = useState([])
  const [categories, setCategories] = useState(FALLBACK_CATEGORIES)
  const [districts, setDistricts] = useState(FALLBACK_DISTRICTS)

  // Derived state
  const searchTerm = useDebounce(searchInput, SEARCH_DEBOUNCE)
  const hasActiveFilters = useMemo(() => 
    !!searchTerm || !!selectedCategory || !!selectedDistrict
  , [searchTerm, selectedCategory, selectedDistrict])

  // Fetch businesses with server-side filtering
  const fetchBusinesses = useCallback(async () => {
    try {
      setLoading(true)
      setError(null)

      const filters = {
        search: searchTerm,
        category: selectedCategory,
        district: selectedDistrict,
        page: currentPage,
        perPage: RESULTS_PER_PAGE
      }

      const response = await businessService.getFilteredBusinesses(filters)
      
      if (response.status === 'success' && response.data) {
        setBusinesses(response.data)
        
        if (response.pagination) {
          setTotalPages(response.pagination.last_page)
        }
      }
    } catch (err) {
      setError(`Failed to fetch businesses: ${err.message || 'Please try again.'}`)
    } finally {
      setLoading(false)
    }
  }, [searchTerm, selectedCategory, selectedDistrict, currentPage])

  useEffect(() => {
    fetchBusinesses()
  }, [fetchBusinesses])

  // Fetch initial metadata
  useEffect(() => {
    const fetchMetadata = async () => {
      try {
        const [categoriesRes, districtsRes] = await Promise.all([
          businessMetadataApi.getCategories(),
          businessMetadataApi.getDistricts()
        ])
        
        setCategories(categoriesRes.data || FALLBACK_CATEGORIES)
        setDistricts(districtsRes.data || FALLBACK_DISTRICTS)
      } catch (error) {
        console.error('Error fetching metadata:', error)
      }
    }
    
    fetchMetadata()
  }, [])

  // Fetch ads once
  useEffect(() => {
    advertService.getActiveAdverts()
      .then(({ data }) => setActiveAdverts(data || []))
      .catch(console.error)
  }, [])

  // URL parameter synchronization
  useUpdateEffect(() => {
    const params = {
      ...(searchTerm && { search: searchTerm }),
      ...(selectedCategory && { category: selectedCategory }),
      ...(selectedDistrict && { district: selectedDistrict })
    }
    setSearchParams(params)
  }, [searchTerm, selectedCategory, selectedDistrict])

  // Reset pagination on filter changes
  useUpdateEffect(() => {
    setCurrentPage(1)
  }, [searchTerm, selectedCategory, selectedDistrict])

  // Handler utilities
  const clearFilters = useCallback(() => {
    setSearchInput('')
    setSelectedCategory('')
    setSelectedDistrict('')
  }, [])

  const handleFilterChange = useCallback((type, value) => {
    if (type === 'category') {
      setSelectedCategory(prev => prev === value ? '' : value);
    } else if (type === 'district') {
      setSelectedDistrict(prev => prev === value ? '' : value);
    }
  }, [])

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold mb-8">Business Directory</h1>
      
      <SearchFilters 
        searchInput={searchInput}
        categories={categories}
        districts={districts}
        selectedCategory={selectedCategory}
        selectedDistrict={selectedDistrict}
        onSearchChange={setSearchInput}
        onFilterChange={handleFilterChange}
        onClear={clearFilters}
        hasActiveFilters={hasActiveFilters}
      />

      <FeaturedAdverts activeAdverts={activeAdverts} />

      <BusinessList 
        loading={loading}
        error={error}
        businesses={businesses}
        hasActiveFilters={hasActiveFilters}
        clearFilters={clearFilters}
      />

      <PaginationControls
        currentPage={currentPage}
        totalPages={totalPages}
        onChangePage={setCurrentPage}
        visible={!loading && !error && businesses.length > 0}
      />
    </div>
  )
}

// Custom hook for debouncing
function useDebounce(value, delay) {
  const [debouncedValue, setDebouncedValue] = useState(value)
  
  useEffect(() => {
    const handler = setTimeout(() => setDebouncedValue(value), delay)
    return () => clearTimeout(handler)
  }, [value, delay])

  return debouncedValue
}

// Custom hook for ignoring initial effect
function useUpdateEffect(effect, deps) {
  const isInitial = useRef(true)

  useEffect(() => {
    if (isInitial.current) {
      isInitial.current = false
      return
    }
    return effect()
  }, deps)
}

// Sub-components for better separation
function SearchFilters({ 
  searchInput,
  categories,
  districts,
  selectedCategory,
  selectedDistrict,
  onSearchChange,
  onFilterChange,
  onClear,
  hasActiveFilters
}) {
  return (
    <div className="bg-white rounded-lg shadow-md p-6 mb-8">
      <div className="mb-6">
        <input
          type="text"
          className="form-control pl-10"
          placeholder="Search businesses..."
          value={searchInput}
          onChange={(e) => onSearchChange(e.target.value)}
        />
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <FilterGroup
          title="Categories"
          items={categories}
          selected={selectedCategory}
          onSelect={(cat) => onFilterChange('category', cat)}
        />
        <FilterGroup
          title="Districts"
          items={districts}
          selected={selectedDistrict}
          onSelect={(dist) => onFilterChange('district', dist)}
        />
      </div>

      {hasActiveFilters && (
        <div className="flex items-center justify-between">
          <button 
            className="text-sm text-red-600 hover:text-red-800 font-medium"
            onClick={onClear}
          >
            Clear all filters
          </button>
        </div>
      )}
    </div>
  )
}

function FilterGroup({ title, items, selected, onSelect }) {
  return (
    <div>
      <h3 className="font-semibold mb-3">{title}</h3>
      <div className="flex flex-wrap gap-2">
        {items.map(item => (
          <button
            key={item}
            onClick={() => onSelect(item)}
            className={`px-4 py-2 rounded-full text-sm ${
              selected === item 
                ? 'bg-blue-600 text-white' 
                : 'bg-gray-100 text-gray-800 hover:bg-gray-200'
            }`}
          >
            {item}
          </button>
        ))}
      </div>
    </div>
  )
}

function FeaturedAdverts({ activeAdverts }) {
  if (!activeAdverts.length) return null

  return (
    <div className="mb-10">
      <h2 className="text-2xl font-semibold mb-4">Featured Businesses</h2>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {activeAdverts.map(advert => (
          <AdvertCard key={advert.id} advert={advert} />
        ))}
      </div>
    </div>
  )
}

function BusinessList({ loading, error, businesses, hasActiveFilters, clearFilters }) {
  if (loading) return <LoadingSkeletons />
  if (error) return <ErrorState error={error} />
  if (!businesses.length) return <EmptyState hasFilters={hasActiveFilters} onClear={clearFilters} />

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      {businesses.map(business => (
        <BusinessCard key={business.id} business={business} />
      ))}
    </div>
  )
}

function LoadingSkeletons() {
  return (
    <>
      {Array.from({ length: 6 }).map((_, index) => (
        <div key={index} className="bg-white rounded-lg shadow-md overflow-hidden animate-pulse">
          <div className="h-48 bg-gray-200"></div>
          <div className="p-4">
            <div className="h-6 bg-gray-200 rounded mb-2"></div>
            <div className="h-4 bg-gray-200 rounded mb-2 w-2/3"></div>
            <div className="h-4 bg-gray-200 rounded mb-2 w-1/2"></div>
          </div>
        </div>
      ))}
    </>
  )
}

function ErrorState({ error }) {
  return (
    <div className="col-span-3 text-center py-8">
      <svg className="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      <h3 className="mt-2 text-lg font-medium text-red-800">{error}</h3>
      <p className="mt-1 text-gray-500">Using mock data instead. In production, this would connect to the backend API.</p>
    </div>
  )
}

function EmptyState({ hasFilters, onClear }) {
  return (
    <div className="col-span-3 text-center py-8">
      <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h3 className="mt-2 text-lg font-medium text-gray-900">No businesses found</h3>
      <p className="mt-1 text-gray-500">Try adjusting your search or filter criteria</p>
      {hasFilters && (
        <button 
          className="mt-4 text-blue-600 hover:text-blue-800 font-medium"
          onClick={onClear}
        >
          Clear all filters
        </button>
      )}
    </div>
  )
}

function AdvertCard({ advert }) {
  return (
    <div className="bg-white rounded-lg shadow-md overflow-hidden border-2 border-blue-400">
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
  )
}

function PaginationControls({ currentPage, totalPages, onChangePage, visible }) {
  if (!visible) return null;
  
  const handlePageChange = (newPage) => {
    onChangePage(newPage);
    // Scroll to top when changing page
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <div className="mt-8 flex justify-center">
      <nav className="flex items-center">
        <button 
          onClick={() => handlePageChange(currentPage - 1)}
          disabled={currentPage === 1}
          className={`px-3 py-1 rounded-l-md border ${
            currentPage === 1 
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
              : 'bg-white text-blue-600 hover:bg-blue-50'
          }`}
        >
          Previous
        </button>
        
        {/* Page numbers */}
        <div className="flex">
          {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
            // Show pages around current page
            let pageNum;
            if (totalPages <= 5) {
              pageNum = i + 1;
            } else if (currentPage <= 3) {
              pageNum = i + 1;
            } else if (currentPage >= totalPages - 2) {
              pageNum = totalPages - 4 + i;
            } else {
              pageNum = currentPage - 2 + i;
            }
            
            return (
              <button
                key={pageNum}
                onClick={() => handlePageChange(pageNum)}
                className={`px-3 py-1 border-t border-b ${
                  currentPage === pageNum
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-blue-600 hover:bg-blue-50'
                }`}
              >
                {pageNum}
              </button>
            );
          })}
        </div>
        
        <button 
          onClick={() => handlePageChange(currentPage + 1)}
          disabled={currentPage === totalPages}
          className={`px-3 py-1 rounded-r-md border ${
            currentPage === totalPages 
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
              : 'bg-white text-blue-600 hover:bg-blue-50'
          }`}
        >
          Next
        </button>
      </nav>
    </div>
  );
}

export default BusinessDirectory