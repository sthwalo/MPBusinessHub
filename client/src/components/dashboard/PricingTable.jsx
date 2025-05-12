import { useState, useEffect } from 'react'
import api from '../../utils/api'

function PricingTable({ businessData, onPlanSelected }) {
  const [selectedPlan, setSelectedPlan] = useState(null)
  const [billingCycle, setBillingCycle] = useState('monthly')
  const [error, setError] = useState('')
  const [packages, setPackages] = useState([])
  const [isLoadingPackages, setIsLoadingPackages] = useState(true)

  // Fetch packages from API when component mounts
  useEffect(() => {
    const fetchPackages = async () => {
      setIsLoadingPackages(true)
      setError('') // Clear any previous errors
      
      try {
        // Make the API request to get packages from the database using the axios instance
        const response = await api.get('/packages')
        
        // Handle different API response formats but always use data from API
        let packagesData = [];
        
        if (Array.isArray(response.data)) {
          // Case 1: API directly returns an array of packages
          packagesData = response.data;
        } else if (response.data && typeof response.data === 'object') {
          // Case 2: API returns an object with a data property containing packages
          if (Array.isArray(response.data.data)) {
            packagesData = response.data.data;
          } else if (response.data.packages && Array.isArray(response.data.packages)) {
            // Case 3: API returns an object with a packages property
            packagesData = response.data.packages;
          }
        }
        
        // Check if we received a warning status
        if (response.data && response.data.status === 'warning') {
          console.warn(response.data.message || 'Warning from API');
        }
        
        if (packagesData.length === 0) {
          // Instead of throwing an error, set default packages if none are found
          console.warn('No packages found in the database, using default packages');
          packagesData = [
            {
              id: 'free',
              name: 'Free',
              price_monthly: 0,
              price_annual: 0,
              advert_limit: 0
            },
            {
              id: 'bronze',
              name: 'Bronze',
              price_monthly: 299,
              price_annual: 3229,
              advert_limit: 2
            },
            {
              id: 'silver',
              name: 'Silver',
              price_monthly: 599,
              price_annual: 6469,
              advert_limit: 5,
            },
            {
              id: 'gold',
              name: 'Gold',
              price_monthly: 999,
              price_annual: 10789,
              advert_limit: 10
            }
          ];
        }
        
        setPackages(packagesData);
        
        // Set the current package as selected by default
        if (businessData && businessData.package_type) {
          const currentPackage = packagesData.find(p => p.name === businessData.package_type);
          if (currentPackage) {
            setSelectedPlan(currentPackage.name);
          } else {
            // If current package not found in API response, select the first one
            setSelectedPlan(packagesData[0].name);
          }
        } else {
          // If no current package, select the first one
          setSelectedPlan(packagesData[0].name);
        }
      } catch (error) {
        console.error('Error fetching packages:', error);
        setError(`Failed to load packages from database: ${error.message}. Please try refreshing the page or contact support.`);
        // Do not set any packages if the API fails - show the error instead
        setPackages([]);
      } finally {
        setIsLoadingPackages(false);
      }
    };
    
    fetchPackages();
  }, [businessData]);

  // Format packages for display
  const formattedPackages = packages.map(pkg => ({
    id: pkg.id,
    name: pkg.name,
    monthlyPrice: pkg.price_monthly,
    annualPrice: pkg.price_annual,
    price_monthly: pkg.price_monthly,
    price_annual: pkg.price_annual,
    popular: pkg.name === 'Silver',
    features: [
      { name: 'Listing Visibility', included: true },
      { name: 'Contact Links', included: ['Bronze', 'Silver', 'Gold'].includes(pkg.name) },
      { name: 'E-Commerce', included: ['Silver', 'Gold'].includes(pkg.name) },
      { name: 'Monthly Adverts', included: pkg.advert_limit > 0, details: pkg.advert_limit.toString() }
    ]
  }))

  const handlePlanChange = (planId) => {
    // Find the package with this ID
    const selectedPackage = packages.find(p => p.id === planId)
    if (selectedPackage) {
      setSelectedPlan(selectedPackage.name)
      setError('')
      
      // Notify parent component about the selected plan and billing cycle
      if (onPlanSelected) {
        onPlanSelected({
          planId: selectedPackage.id,
          planName: selectedPackage.name,
          billingCycle,
          price: billingCycle === 'monthly' ? selectedPackage.price_monthly : selectedPackage.price_annual
        })
      }
    }
  }

  const handleBillingCycleChange = (cycle) => {
    setBillingCycle(cycle)
    
    // If a plan is already selected, update the parent component with the new billing cycle
    if (selectedPlan && onPlanSelected) {
      const selectedPackage = packages.find(p => p.name === selectedPlan)
      if (selectedPackage) {
        onPlanSelected({
          planId: selectedPackage.id,
          planName: selectedPackage.name,
          billingCycle: cycle,
          price: cycle === 'monthly' ? selectedPackage.price_monthly : selectedPackage.price_annual
        })
      }
    }
  }

  const getDiscountPercentage = () => {
    return 15 // 15% discount for annual billing
  }

  const getCurrentPlanIndex = () => {
    const currentPlan = businessData?.package_type
    return formattedPackages.findIndex(pkg => pkg.name === currentPlan)
  }

  const getSelectedPlanIndex = () => {
    return formattedPackages.findIndex(pkg => pkg.name === selectedPlan)
  }

  const isDowngrade = () => {
    return getSelectedPlanIndex() < getCurrentPlanIndex()
  }

  const isUpgrade = () => {
    return getSelectedPlanIndex() > getCurrentPlanIndex()
  }

  const isCurrentPlan = () => {
    return selectedPlan === businessData?.package_type
  }

  if (isLoadingPackages) {
    return (
      <div className="flex justify-center items-center p-8">
        <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-brand-black"></div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
        <p className="font-medium">Error Loading Packages</p>
        <p className="text-sm">{error}</p>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      {/* Billing Toggle */}
      <div className="flex justify-center mb-8">
        <div className="inline-flex items-center bg-brand-gray-100 rounded-full p-1">
          <button
            onClick={() => handleBillingCycleChange('monthly')}
            className={`px-4 py-2 rounded-full text-sm font-medium transition-colors ${billingCycle === 'monthly' ? 'bg-white shadow-sm' : 'text-brand-gray-600 hover:text-brand-gray-900'}`}
          >
            Monthly
          </button>
          <button
            onClick={() => handleBillingCycleChange('annual')}
            className={`px-4 py-2 rounded-full text-sm font-medium transition-colors ${billingCycle === 'annual' ? 'bg-white shadow-sm' : 'text-brand-gray-600 hover:text-brand-gray-900'}`}
          >
            Annual <span className="text-brand-green-600 ml-1">Save {getDiscountPercentage()}%</span>
          </button>
        </div>
      </div>
      
      {/* Package Cards */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {formattedPackages.map((pkg) => {
          const isSelected = pkg.name === selectedPlan
          const isCurrent = pkg.name === businessData?.package_type
          
          return (
            <div key={pkg.id} className={`relative rounded-lg overflow-hidden ${pkg.popular ? 'ring-2 ring-brand-black' : 'border border-brand-gray-200'}`}>
              {pkg.popular && (
                <div className="absolute top-0 inset-x-0 bg-brand-black text-brand-white text-xs font-medium text-center py-1">
                  Most Popular
                </div>
              )}
              
              <div className={`p-6 ${pkg.popular ? 'pt-8' : ''}`}>
                <h3 className="text-xl font-bold font-serif text-brand-black mb-2">{pkg.name}</h3>
                
                <div className="mb-4">
                  <div className="flex items-baseline">
                    <span className="text-3xl font-bold text-brand-black">
                      {billingCycle === 'monthly' ? `R${pkg.monthlyPrice}` : `R${pkg.annualPrice}`}
                    </span>
                    <span className="text-brand-gray-600 ml-1">
                      /{billingCycle === 'monthly' ? 'month' : 'year'}
                    </span>
                  </div>
                  
                  {billingCycle === 'annual' && (
                    <p className="text-sm text-brand-green-600 mt-1">
                      Save {(pkg.monthlyPrice * 12 - pkg.annualPrice).toFixed(2)} per year
                    </p>
                  )}
                </div>
                
                <ul className="space-y-3 mb-6">
                  {pkg.features.map((feature, index) => (
                    <li key={index} className="flex items-start">
                      {feature.included ? (
                        <svg className="h-5 w-5 text-green-500 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7" />
                        </svg>
                      ) : (
                        <svg className="h-5 w-5 text-red-500 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      )}
                      <span className="text-brand-gray-700">
                        {feature.name}
                        {feature.details && <span className="text-brand-gray-500 ml-1">({feature.details})</span>}
                      </span>
                    </li>
                  ))}
                </ul>
                
                <button 
                  onClick={() => handlePlanChange(pkg.id)}
                  className={`w-full py-2 px-4 rounded-md transition-colors ${isSelected 
                    ? 'bg-brand-black text-brand-white' 
                    : 'border border-brand-black text-brand-black hover:bg-brand-gray-100'}`}
                  disabled={isCurrent && isSelected}
                >
                  {isCurrent ? 'Current Plan' : isSelected ? 'Selected' : 'Select'}
                </button>
              </div>
            </div>
          )
        })}
      </div>
    </div>
  )
}

export default PricingTable
