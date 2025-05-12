import { useState } from 'react'
import { Link } from 'react-router-dom'
import api from '../../utils/api'
import PricingTable from './PricingTable'

function UpgradePlan({ businessData, onUpgrade }) {
  const [isLoading, setIsLoading] = useState(false)
  const [successMessage, setSuccessMessage] = useState('')
  const [error, setError] = useState('')
  const [selectedPlanData, setSelectedPlanData] = useState(null)

  const handlePlanSelected = (planData) => {
    setSelectedPlanData(planData)
    // Reset messages when plan changes
    setSuccessMessage('')
    setError('')
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    if (!selectedPlanData) {
      setError('Please select a plan first')
      return
    }
    
    setIsLoading(true)
    setError('')
    setSuccessMessage('')
    
    try {
      // Get the current business ID from the business data
      const businessId = businessData.id
      
      if (!businessId) {
        throw new Error('Business ID is required')
      }
      
      // Prepare the payload for the upgrade request
      const payload = {
        business_id: businessId,
        package_id: selectedPlanData.planId,
        billing_cycle: selectedPlanData.billingCycle
      }
      
      // Make the API request to process the payment and upgrade the plan
      const response = await api.post('/businesses/upgrade-plan', payload)
      
      // Handle the response
      if (response && response.success) {
        setSuccessMessage(`Successfully upgraded to ${selectedPlanData.planName} plan!`)
        
        // Call the onUpgrade callback if provided
        if (onUpgrade && typeof onUpgrade === 'function') {
          // Create an updated business data object with the new package type
          const updatedBusinessData = {
            ...businessData,
            package_type: selectedPlanData.planName,
            billing_cycle: selectedPlanData.billingCycle
          }
          
          onUpgrade(updatedBusinessData)
        }
      } else {
        throw new Error(response.message || 'Failed to upgrade plan')
      }
    } catch (error) {
      console.error('Error upgrading plan:', error)
      setError(`Failed to process payment: ${error.message || 'Unknown error'}. Please try again or contact support.`)
    } finally {
      setIsLoading(false)
    }
  }

  const isCurrentPlan = () => {
    return selectedPlanData && selectedPlanData.planName === businessData?.package_type
  }

  const isUpgrade = () => {
    if (!selectedPlanData || !businessData?.package_type) return false
    
    // Define the order of plans for comparison
    const planOrder = ['Free', 'Bronze', 'Silver', 'Gold']
    
    const currentPlanIndex = planOrder.indexOf(businessData.package_type)
    const selectedPlanIndex = planOrder.indexOf(selectedPlanData.planName)
    
    return selectedPlanIndex > currentPlanIndex
  }

  return (
    <div className="space-y-8">
      {/* Success Message */}
      {successMessage && (
        <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
          <p className="font-medium">{successMessage}</p>
          <p className="text-sm mt-1">Your business package has been updated successfully.</p>
        </div>
      )}
      
      {/* Error Message */}
      {error && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
          <p className="font-medium">Error Processing Payment</p>
          <p className="text-sm">{error}</p>
        </div>
      )}
      
      {/* Pricing Table Component */}
      <PricingTable 
        businessData={businessData} 
        onPlanSelected={handlePlanSelected} 
      />
      
      {/* Action Section */}
      {selectedPlanData && (
        <div className="bg-brand-gray-100 border border-brand-gray-200 rounded-lg p-6 shadow-brand-sm">
          <div className="flex flex-col md:flex-row justify-between items-center">
            <div className="mb-4 md:mb-0">
              <h3 className="text-lg font-bold font-serif text-brand-black">
                {isCurrentPlan() 
                  ? 'You are on this plan already' 
                  : isUpgrade()
                    ? `Upgrade to ${selectedPlanData.planName} Package` 
                    : `Change to ${selectedPlanData.planName} Package`}
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
      )}
    </div>
  )
}

export default UpgradePlan
