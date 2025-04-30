import { useState } from 'react'
import { Link } from 'react-router-dom'
import { useNavigate } from 'react-router-dom'
import toast from 'react-hot-toast'

function Pricing() {
  const [billingPeriod, setBillingPeriod] = useState('monthly')
  const navigate = useNavigate()
  const isAuthenticated = localStorage.getItem('token') ? true : false

  // Pricing data based on the membership tiers from your project
  const plans = [
    {
      name: 'Basic',
      price: { monthly: 0, yearly: 0 },
      description: 'Get started with a basic business listing',
      features: [
        'Business Name Listing',
        'Area of Operation',
        'Basic Search Visibility',
        'Business Category'
      ],
      limitations: [
        'No contact information displayed',
        'No ratings or reviews',
        'No website links',
        'No product catalog'
      ],
      buttonText: 'Get Started Free',
      recommended: false,
      color: 'brand-gray-700'
    },
    {
      name: 'Bronze',
      price: { monthly: 200, yearly: 2000 },
      description: 'Enhance your visibility with contact details',
      features: [
        'All Basic features',
        'Website Link',
        'WhatsApp Number',
        'Star Ratings',
        'Contact Information Display',
        'Enhanced Search Visibility'
      ],
      limitations: [
        'No email contact',
        'No product catalog',
        'No monthly adverts'
      ],
      buttonText: 'Upgrade to Bronze',
      recommended: false,
      color: 'brand-gray-500'
    },
    {
      name: 'Silver',
      price: { monthly: 500, yearly: 5000 },
      description: 'Showcase your products and get more exposure',
      features: [
        'All Bronze features',
        'Email Contact',
        'Product Catalog (up to 10 items)',
        '1 Monthly Advert',
        'Priority in Search Results',
        'Customer Reviews'
      ],
      limitations: [
        'Limited adverts',
        'No social media features'
      ],
      buttonText: 'Upgrade to Silver',
      recommended: true,
      color: 'brand-gray-300'
    },
    {
      name: 'Gold',
      price: { monthly: 1000, yearly: 10000 },
      description: 'Maximum visibility and premium features',
      features: [
        'All Silver features',
        '4 Monthly Adverts',
        '1 Social Media Feature per month',
        'Unlimited Product Catalog',
        'Featured Business Status',
        'Top Position in Search Results',
        'Premium Support'
      ],
      limitations: [],
      buttonText: 'Upgrade to Gold',
      recommended: false,
      color: 'brand-gray-200'
    }
  ]

  const handleUpgrade = (plan) => {
    if (!isAuthenticated) {
      toast.error('Please log in to upgrade your membership')
      navigate('/login')
      return
    }
    
    // In a real implementation, this would redirect to a payment page
    // or process the upgrade through an API
    toast.success(`You selected the ${plan.name} plan. Redirecting to payment...`)
    
    // For demo purposes, just log the selection
    console.log(`Selected plan: ${plan.name}, billing: ${billingPeriod}`)
    
    // In production, this would redirect to a payment gateway
    // navigate('/payment', { state: { plan: plan.name, billing: billingPeriod } })
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
          {plans.map((plan) => (
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