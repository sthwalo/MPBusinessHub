import { useState, useEffect } from 'react'

function UpgradePlan({ businessData }) {
  const [selectedPlan, setSelectedPlan] = useState(businessData.package_type)
  const [billingCycle, setBillingCycle] = useState('monthly')
  const [isLoading, setIsLoading] = useState(false)
  const [successMessage, setSuccessMessage] = useState('')
  const [error, setError] = useState('')
  const [packages, setPackages] = useState([])

  // Fetch packages from API when component mounts
  useEffect(() => {
    const fetchPackages = async () => {
      try {
        const response = await fetch('/api/packages')
        const data = await response.json()
        
        if (data.status === 'success') {
          setPackages(data.data)
        }
      } catch (error) {
        console.error('Error fetching packages:', error)
        setError('Failed to load packages. Please try again later.')
      }
    }
    
    fetchPackages()
  }, [])

  // If we don't have packages yet, use these as fallback
  const fallbackPackages = [
    {
      id: 1,
      name: 'Basic',
      price_monthly: 0,
      price_annual: 0,
      advert_limit: 0,
      product_limit: 0,
      features: ['Basic listing', 'Business hours']
    },
    {
      id: 2,
      name: 'Bronze',
      price_monthly: 200,
      price_annual: 2000,
      advert_limit: 0,
      product_limit: 0,
      features: ['Basic listing', 'Business hours', 'Contact information']
    },
    {
      id: 3,
      name: 'Silver',
      price_monthly: 500,
      price_annual: 5000,
      advert_limit: 2,
      product_limit: 10,
      features: ['Basic listing', 'Business hours', 'Contact information', 'Products showcase', '2 monthly adverts']
    },
    {
      id: 4,
      name: 'Gold',
      price_monthly: 1000,
      price_annual: 10000,
      advert_limit: 4,
      product_limit: 50,
      features: ['Basic listing', 'Business hours', 'Contact information', 'Products showcase', '4 monthly adverts', 'Featured listing']
    }
  ]

  // Use API packages or fallback if API hasn't loaded yet
  const displayPackages = packages.length > 0 ? packages : fallbackPackages

  // Format packages for display
  const formattedPackages = displayPackages.map(pkg => ({
    id: pkg.id,
    name: pkg.name,
    monthlyPrice: pkg.price_monthly,
    annualPrice: pkg.price_annual,
    price_monthly: pkg.price_monthly,  // Keep original for calculations
    price_annual: pkg.price_annual,    // Keep original for calculations
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
    const selectedPackage = displayPackages.find(p => p.id === planId)
    if (selectedPackage) {
      setSelectedPlan(selectedPackage.name)
      // Reset success message if any
      setSuccessMessage('')
      setError('')
    }
  }

  const handleBillingCycleChange = (cycle) => {
    setBillingCycle(cycle)
    // Reset success message if any
    setSuccessMessage('')
    setError('')
  }

  const getDiscountPercentage = () => {
    return 17 // 17% discount for annual billing
  }

  const getCurrentPlanIndex = () => {
    return formattedPackages.findIndex(pkg => pkg.name === businessData.package_type)
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
    return selectedPlan === businessData.package_type
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    if (isCurrentPlan()) return
    
    setIsLoading(true)
    setError('')
    setSuccessMessage('')
    
    try {
      // Get the package ID based on the selected plan name
      const selectedPackage = displayPackages.find(p => p.name === selectedPlan)
      
      if (!selectedPackage) {
        throw new Error('Selected package not found')
      }
      
      // Call the API to upgrade the package
      const response = await fetch('/api/packages/upgrade', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('mpbh_token')}`
        },
        body: JSON.stringify({
          package_id: selectedPackage.id,
          billing_cycle: billingCycle
        })
      })
      
      const data = await response.json()
      
      if (!response.ok) {
        throw new Error(data.message || 'Failed to upgrade package')
      }
      
      // Update the business data with the response from the server
      businessData.package_id = data.data.business.package_id
      businessData.package_type = selectedPackage.name
      businessData.adverts_remaining = data.data.business.adverts_remaining
      businessData.billing_cycle = data.data.business.billing_cycle
      businessData.subscription_ends_at = data.data.business.subscription_ends_at
      
      // Update subscription amount based on billing cycle
      businessData.subscription = businessData.subscription || {}
      businessData.subscription.amount = billingCycle === 'monthly' 
        ? selectedPackage.price_monthly 
        : selectedPackage.price_annual / 12
      
      setSuccessMessage(`Successfully ${isUpgrade() ? 'upgraded' : 'changed'} to ${selectedPlan} plan!`)
    } catch (error) {
      console.error('Error upgrading package:', error)
      setError(error.message || 'An error occurred while upgrading your package')
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="space-y-8">
      <div className="mb-8">
        <h1 className="text-2xl font-bold font-serif text-brand-black mb-2">Upgrade Your Package</h1>
        <p className="text-brand-gray-600">Choose the right plan for your business needs</p>
      </div>
      
      {successMessage && (
        <div className="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 mb-6">
          {successMessage}
        </div>
      )}
      
      {error && (
        <div className="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6">
          {error}
        </div>
      )}
      
      {/* Billing Cycle Toggle */}
      <div className="bg-brand-white rounded-lg shadow-brand-md p-6 mb-8">
        <h2 className="text-lg font-bold font-serif text-brand-black mb-4">Billing Cycle</h2>
        <div className="flex space-x-4">
          <button
            onClick={() => handleBillingCycleChange('monthly')}
            className={`px-4 py-2 rounded-md ${billingCycle === 'monthly' 
              ? 'bg-brand-black text-brand-white' 
              : 'bg-brand-gray-100 text-brand-gray-700 hover:bg-brand-gray-200'}`}
          >
            Monthly
          </button>
          <button
            onClick={() => handleBillingCycleChange('annual')}
            className={`px-4 py-2 rounded-md ${billingCycle === 'annual' 
              ? 'bg-brand-black text-brand-white' 
              : 'bg-brand-gray-100 text-brand-gray-700 hover:bg-brand-gray-200'}`}
          >
            Annual <span className="text-green-600 text-xs font-bold ml-1">Save {getDiscountPercentage()}%</span>
          </button>
        </div>
      </div>
      
      {/* Package Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {formattedPackages.map((pkg) => {
          const isCurrent = pkg.name === businessData.package_type
          const isSelected = pkg.name === selectedPlan
          
          return (
            <div 
              key={pkg.id} 
              className={`rounded-lg overflow-hidden border ${isSelected 
                ? 'border-brand-black shadow-brand-md' 
                : 'border-brand-gray-200 shadow-brand-sm'} 
                ${pkg.popular ? 'relative' : ''}`}
            >
              {pkg.popular && (
                <div className="absolute top-0 right-0 bg-brand-black text-brand-white text-xs font-bold uppercase py-1 px-3 rounded-bl-lg">
                  Popular
                </div>
              )}
              
              <div className="p-6 bg-brand-white">
                <h3 className="text-xl font-bold font-serif text-brand-black mb-2">{pkg.name}</h3>
                
                <div className="mb-4">
                  <div className="flex items-baseline">
                    <span className="text-3xl font-bold text-brand-black">R{billingCycle === 'monthly' ? pkg.monthlyPrice : Math.round(pkg.annualPrice / 12)}</span>
                    <span className="text-brand-gray-500 ml-1">/mo</span>
                  </div>
                  
                  {billingCycle === 'annual' && pkg.annualPrice > 0 && (
                    <div className="text-sm text-green-600 mt-1">
                      R{pkg.annualPrice} billed annually
                      <br />
                      <span className="font-medium">
                        Save R{(pkg.monthlyPrice * 12 - pkg.annualPrice).toLocaleString()}
                      </span>
                    </div>
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
      
      {/* Action Section */}
      <div className="bg-brand-gray-100 border border-brand-gray-200 rounded-lg p-6 shadow-brand-sm">
        <div className="flex flex-col md:flex-row justify-between items-center">
          <div className="mb-4 md:mb-0">
            <h3 className="text-lg font-bold font-serif text-brand-black">
              {isCurrentPlan() 
                ? 'You are on this plan already' 
                : isUpgrade()
                  ? `Upgrade to ${selectedPlan} Package` 
                  : `Change to ${selectedPlan} Package`}
            </h3>
            <p className="text-brand-gray-600">
              {isCurrentPlan() 
                ? 'You can choose another plan to upgrade or downgrade' 
                : isUpgrade()
                  ? 'Get more features and benefits' 
                  : 'You will lose some features but pay less'}
            </p>
          </div>
          
          <button 
            onClick={handleSubmit}
            disabled={isCurrentPlan() || isLoading}
            className={`px-6 py-3 rounded-md transition-colors ${isCurrentPlan() 
              ? 'bg-brand-gray-300 text-brand-gray-700 cursor-not-allowed' 
              : isUpgrade() 
                ? 'bg-brand-black text-brand-white hover:bg-brand-gray-800' 
                : 'border border-brand-black text-brand-black hover:bg-brand-gray-100'} ${isLoading ? 'opacity-75 cursor-wait' : ''}`}
          >
            {isLoading ? (
              <>
                <svg className="animate-spin h-5 w-5 mr-3 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
              </>
            ) : isCurrentPlan() 
              ? 'Current Plan' 
              : isUpgrade() 
                ? 'Upgrade Now' 
                : 'Change Plan'}
          </button>
        </div>
      </div>
    </div>
  )
}

export default UpgradePlan
