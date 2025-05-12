import { useState, useEffect } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import toast from 'react-hot-toast'

function Pricing() {
  const [billingPeriod, setBillingPeriod] = useState('monthly')
  const [packages, setPackages] = useState([])
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState('')
  const navigate = useNavigate()
  const isAuthenticated = localStorage.getItem('mpbh_token') ? true : false

  // Fetch packages from API when component mounts
  useEffect(() => {
    const fetchPackages = async () => {
      setIsLoading(true)
      try {
        const response = await fetch('/api/packages')
        
        if (!response.ok) {
          throw new Error(`Failed to fetch packages: ${response.status} ${response.statusText}`)
        }
        
        const data = await response.json()
        
        if (data.status === 'success' && Array.isArray(data.data) && data.data.length > 0) {
          setPackages(data.data)
        } else {
          throw new Error('Invalid package data received from server')
        }
      } catch (error) {
        console.error('Error fetching packages:', error)
        setError(`Failed to load packages: ${error.message}. Please try refreshing the page.`)
      } finally {
        setIsLoading(false)
      }
    }
    
    fetchPackages()
  }, [])

  // Format packages for display
  const formatPackages = () => {
    return packages.map(pkg => ({
      id: pkg.id,
      name: pkg.name,
      price: { 
        monthly: pkg.price_monthly, 
        yearly: pkg.price_annual 
      },
      description: pkg.description || getDefaultDescription(pkg.name),
      features: getPackageFeatures(pkg),
      limitations: getPackageLimitations(pkg),
      buttonText: `Upgrade to ${pkg.name}`,
      recommended: pkg.name === 'Silver',
      color: getPackageColor(pkg.name)
    }))
  }

  // Get default description if not provided by API
  const getDefaultDescription = (packageName) => {
    switch (packageName) {
      case 'Basic': return 'Get started with a basic business listing'
      case 'Bronze': return 'Enhance your visibility with contact details'
      case 'Silver': return 'Showcase your products and get more exposure'
      case 'Gold': return 'Maximum visibility and premium features'
      default: return 'Choose the right plan for your business'
    }
  }

  // Get features based on package name and properties
  const getPackageFeatures = (pkg) => {
    const baseFeatures = [
      'Business Name Listing',
      'Area of Operation',
      'Basic Search Visibility',
      'Business Category',
    ]

    const bronzeFeatures = [
      'All Basic features',
      'Website Link',
      'WhatsApp Number',
      'Star Ratings',
      'Contact Information Display',
      'Enhanced Search Visibility',
    ]

    const silverFeatures = [
      'All Bronze features',
      'Email Contact',
      'Priority in Search Results',
      'Customer Reviews',
    ]

    const goldFeatures = [
      'All Silver features',
      'Featured Business Status',
      'Top Position in Search Results',
      'Premium Support',
    ]

    // Add product catalog feature based on product_limit
    const productFeature = getProductFeature(pkg.product_limit)
    
    // Add advert feature based on advert_limit
    const advertFeature = getAdvertFeature(pkg.advert_limit)
    
    // Add social media feature
    const socialFeature = getSocialFeature(pkg.name)

    switch (pkg.name) {
      case 'Basic':
        return [...baseFeatures]
      case 'Bronze':
        return [...bronzeFeatures, productFeature, advertFeature]
      case 'Silver':
        return [...silverFeatures, productFeature, advertFeature, socialFeature]
      case 'Gold':
        return [...goldFeatures, 'Unlimited Product Catalog', advertFeature, socialFeature, 'No Limitations']
      default:
        return baseFeatures
    }
  }

  // Get product feature text based on limit
  const getProductFeature = (limit) => {
    if (!limit || limit === 0) return 'No Product Catalog'
    if (limit < 10) return `Basic Product Catalog (up to ${limit} items)`
    if (limit < 50) return `Standard Product Catalog (up to ${limit} items)`
    return 'Unlimited Product Catalog'
  }

  // Get advert feature text based on limit
  const getAdvertFeature = (limit) => {
    if (!limit || limit === 0) return 'No Monthly Adverts'
    return `${limit} Monthly Advert${limit > 1 ? 's' : ''}`
  }

  // Get social media feature text based on package
  const getSocialFeature = (packageName) => {
    switch (packageName) {
      case 'Silver': return 'Social Media Feature (1 per month)'
      case 'Gold': return '2 Social Media Features per month'
      default: return 'No Social Media Features'
    }
  }

  // Get limitations based on package name
  const getPackageLimitations = (pkg) => {
    switch (pkg.name) {
      case 'Basic':
        return [
          'No contact information displayed',
          'No ratings or reviews',
          'No website links',
          'No product catalog',
          'No monthly adverts',
          'No social media features'
        ]
      case 'Bronze':
        return [
          'No email contact',
          'Limited product catalog',
          'Limited adverts'
        ]
      case 'Silver':
        return [
          'Limited product catalog',
          'Limited adverts'
        ]
      case 'Gold':
        return []
      default:
        return []
    }
  }

  // Get color based on package name
  const getPackageColor = (packageName) => {
    switch (packageName) {
      case 'Basic': return 'brand-gray-700'
      case 'Bronze': return 'brand-gray-500'
      case 'Silver': return 'brand-gray-300'
      case 'Gold': return 'brand-gray-200'
      default: return 'brand-gray-500'
    }
  }

  const handleUpgrade = (plan) => {
    if (!isAuthenticated) {
      toast.error('Please log in to upgrade your membership')
      navigate('/login')
      return
    }
    
    // Redirect to dashboard upgrade page
    navigate('/dashboard/upgrade')
  }

  // Show loading state
  if (isLoading) {
    return (
      <div className="bg-brand-gray-100 py-16 px-4 min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-brand-black mx-auto mb-4"></div>
          <p className="text-brand-gray-600 text-lg">Loading package information...</p>
        </div>
      </div>
    )
  }

  // Show error state
  if (error && packages.length === 0) {
    return (
      <div className="bg-brand-gray-100 py-16 px-4 min-h-screen">
        <div className="container mx-auto">
          <div className="p-6 bg-red-50 border border-red-200 rounded-lg max-w-2xl mx-auto">
            <h2 className="text-xl font-bold text-red-700 mb-2">Error Loading Packages</h2>
            <p className="text-red-600 mb-4">{error}</p>
            <button 
              onClick={() => window.location.reload()}
              className="px-4 py-2 bg-brand-black text-brand-white rounded-md hover:bg-brand-gray-800 transition-colors"
            >
              Refresh Page
            </button>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="bg-brand-gray-100 py-16 px-4">
      <div className="container mx-auto">
        {/* Header */}
        <div className="text-center mb-16">
          <h1 className="text-4xl md:text-5xl font-bold mb-4 font-serif text-brand-black">
            Membership Plans
          </h1>
          <p className="mt-5 max-w-xl mx-auto text-xl text-brand-gray-600">
            Choose the right plan to boost your business visibility in Mpumalanga
          </p>
          
          {/* Billing toggle */}
          <div className="mt-8 flex justify-center">
            <div className="relative bg-brand-white p-1 rounded-lg inline-flex shadow-brand-sm">
              <button
                onClick={() => setBillingPeriod('monthly')}
                className={`${
                  billingPeriod === 'monthly'
                    ? 'bg-brand-black text-brand-white'
                    : 'bg-brand-white text-brand-gray-700'
                } relative py-2 px-6 rounded-md transition-all duration-200 font-medium`}
              >
                Monthly
              </button>
              <button
                onClick={() => setBillingPeriod('yearly')}
                className={`${
                  billingPeriod === 'yearly'
                    ? 'bg-brand-black text-brand-white'
                    : 'bg-brand-white text-brand-gray-700'
                } relative py-2 px-6 rounded-md transition-all duration-200 font-medium`}
              >
                Yearly <span className="text-xs font-bold text-green-500 ml-1">Save 17%</span>
              </button>
            </div>
          </div>
        </div>

        {/* Pricing cards */}
        <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-8 mb-16">
          {formatPackages().map((plan) => (
            <div 
              key={plan.name}
              className={`rounded-lg shadow-brand-md hover:shadow-brand-lg overflow-hidden bg-brand-white border border-brand-gray-200 transform transition-all duration-200 hover:-translate-y-1 ${
                plan.recommended ? 'ring-2 ring-brand-black' : ''
              }`}
            >
              {plan.recommended && (
                <div className="bg-brand-black text-brand-white text-center py-2 font-medium">
                  Most Popular
                </div>
              )}
              
              <div className="p-6">
                <h2 className="text-2xl font-bold text-brand-black font-serif">{plan.name}</h2>
                <p className="mt-4 text-brand-gray-600">{plan.description}</p>
                
                <p className="mt-8">
                  <span className="text-4xl font-extrabold text-brand-black">
                    R{plan.price[billingPeriod]}
                  </span>
                  <span className="text-base font-medium text-brand-gray-500">
                    {billingPeriod === 'monthly' ? '/month' : '/year'}
                  </span>
                </p>
                
                <button
                  onClick={() => handleUpgrade(plan)}
                  className={`mt-8 block w-full bg-${plan.color} hover:bg-brand-black text-brand-white font-bold py-3 px-4 rounded transition duration-200`}
                >
                  {plan.buttonText}
                </button>
              </div>
              
              <div className="px-6 pt-6 pb-8">
                <h3 className="text-xs font-semibold text-brand-gray-900 tracking-wide uppercase">
                  What's included
                </h3>
                <ul className="mt-6 space-y-4">
                  {plan.features.map((feature, index) => (
                    <li key={index} className="flex items-start">
                      <div className="flex-shrink-0">
                        <svg className="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                        </svg>
                      </div>
                      <p className="ml-3 text-base text-brand-gray-700">{feature}</p>
                    </li>
                  ))}
                </ul>
                
                {plan.limitations.length > 0 && (
                  <>
                    <h3 className="mt-8 text-xs font-semibold text-brand-gray-900 tracking-wide uppercase">
                      Limitations
                    </h3>
                    <ul className="mt-6 space-y-4">
                      {plan.limitations.map((limitation, index) => (
                        <li key={index} className="flex items-start">
                          <div className="flex-shrink-0">
                            <svg className="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                          </div>
                          <p className="ml-3 text-base text-brand-gray-700">{limitation}</p>
                        </li>
                      ))}
                    </ul>
                  </>
                )}
              </div>
            </div>
          ))}
        </div>
        
        {/* FAQ Section */}
        <div className="bg-brand-white rounded-lg shadow-brand-md p-8 mb-16">
          <h2 className="text-3xl font-bold text-brand-black text-center font-serif mb-12">
            Frequently Asked Questions
          </h2>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div className="border-b border-brand-gray-200 pb-6">
              <h3 className="text-xl font-medium text-brand-black font-serif">How do I upgrade my membership?</h3>
              <p className="mt-2 text-brand-gray-600">
                Log in to your account, select your desired membership plan, and complete the payment process. Your account will be upgraded immediately after successful payment.
              </p>
            </div>
            <div className="border-b border-brand-gray-200 pb-6">
              <h3 className="text-xl font-medium text-brand-black font-serif">Can I change my plan later?</h3>
              <p className="mt-2 text-brand-gray-600">
                Yes, you can upgrade or downgrade your plan at any time. Upgrades take effect immediately, while downgrades will take effect at the end of your current billing cycle.
              </p>
            </div>
            <div className="border-b border-brand-gray-200 pb-6">
              <h3 className="text-xl font-medium text-brand-black font-serif">Do you offer refunds?</h3>
              <p className="mt-2 text-brand-gray-600">
                We offer a 7-day money-back guarantee for all paid plans. If you're not satisfied with your membership, contact our support team within 7 days of payment for a full refund.
              </p>
            </div>
            <div className="border-b border-brand-gray-200 pb-6">
              <h3 className="text-xl font-medium text-brand-black font-serif">How do I cancel my subscription?</h3>
              <p className="mt-2 text-brand-gray-600">
                You can cancel your subscription at any time from your account dashboard. Your plan will remain active until the end of your current billing period.
              </p>
            </div>
          </div>
        </div>
        
        {/* CTA */}
        <div className="bg-brand-black text-brand-white py-16 px-8 rounded-lg text-center">
          <h2 className="text-3xl font-bold font-serif mb-4">
            Ready to boost your business visibility?
          </h2>
          <p className="mt-4 text-lg text-brand-gray-300 max-w-2xl mx-auto mb-8">
            Join hundreds of businesses already thriving on MPBusinessHub
          </p>
          <div className="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
            <Link
              to="/register"
              className="px-6 py-3 bg-brand-white text-brand-black font-medium rounded hover:bg-brand-gray-200 transition-colors"
            >
              Register Your Business
            </Link>
            <Link
              to="/directory"
              className="px-6 py-3 border border-brand-white text-brand-white font-medium rounded hover:bg-brand-white hover:text-brand-black transition-colors"
            >
              Browse Directory
            </Link>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Pricing