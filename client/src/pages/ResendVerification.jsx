import { useState } from 'react'
import { Link } from 'react-router-dom'
import toast from 'react-hot-toast'

function ResendVerification() {
  const [email, setEmail] = useState('')
  const [errors, setErrors] = useState({})
  const [isLoading, setIsLoading] = useState(false)
  const [isEmailSent, setIsEmailSent] = useState(false)

  const handleChange = (e) => {
    setEmail(e.target.value)
    // Clear error when user starts typing
    if (errors.email) {
      setErrors({})
    }
  }

  const validateForm = () => {
    const newErrors = {}
    
    if (!email) {
      newErrors.email = 'Email is required'
    } else if (!/\S+@\S+\.\S+/.test(email)) {
      newErrors.email = 'Email is invalid'
    }
    
    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    if (!validateForm()) return
    
    setIsLoading(true)
    
    try {
      // Show loading message
      const loadingToast = toast.loading('Sending verification email...')
      
      // First try to login to get a token
      const loginResponse = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          email: email,
          password: '' // This will fail, but we're just trying to get a token
        })
      })
      
      // If login succeeds (unlikely), use the token
      let token = null
      if (loginResponse.ok) {
        const loginData = await loginResponse.json()
        token = loginData.data.token
        localStorage.setItem('mpbh_token', token)
      }
      
      // Make the API call to resend verification email
      const headers = { 
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
      
      // Add token if available
      if (token) {
        headers['Authorization'] = `Bearer ${token}`
      }
      
      const response = await fetch('/api/email/verification-notification', {
        method: 'POST',
        headers: headers,
        body: JSON.stringify({ email })
      })
      
      // Clear loading toast
      toast.dismiss(loadingToast)
      
      const data = await response.json()
      
      if (response.ok) {
        // Show success message
        toast.success('Verification email sent successfully')
        setIsEmailSent(true)
      } else {
        throw new Error(data.message || 'Failed to send verification email')
      }
    } catch (error) {
      console.error('Verification email error:', error)
      
      // Generic error
      setErrors({ form: error.message || 'Failed to send verification email. Please try again.' })
      
      toast.error('Failed to send verification email')
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="max-w-md mx-auto bg-brand-white rounded-lg shadow-md border border-brand-gray-200 overflow-hidden">
        <div className="p-8">
          <h2 className="text-2xl font-bold text-center mb-6">Resend Verification Email</h2>
          
          {isEmailSent ? (
            <div className="text-center">
              <div className="bg-green-50 text-green-800 p-4 rounded-md mb-6">
                <p>We've sent a verification email to your address.</p>
                <p className="mt-2">Please check your inbox and follow the instructions to verify your email.</p>
              </div>
              <Link 
                to="/login" 
                className="inline-block px-4 py-2 bg-brand-black text-brand-white font-medium rounded hover:bg-brand-gray-800 transition-colors"
              >
                Return to Login
              </Link>
            </div>
          ) : (
            <>
              {errors.form && (
                <div className="bg-red-50 text-red-800 p-4 rounded-md mb-6">
                  {errors.form}
                </div>
              )}
              
              <p className="text-brand-gray-600 mb-6">
                Enter your email address and we'll send you a verification email.
              </p>
              
              <form onSubmit={handleSubmit}>
                <div className="mb-6">
                  <label htmlFor="email" className="block text-brand-black font-medium mb-1">Email Address</label>
                  <input
                    type="email"
                    id="email"
                    className={`w-full p-2 border ${errors.email ? 'border-red-500' : 'border-brand-gray-300'} rounded focus:outline-none focus:ring-1 focus:ring-brand-black`}
                    value={email}
                    onChange={handleChange}
                    placeholder="youremail@example.com"
                  />
                  {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email}</p>}
                </div>
                
                <button
                  type="submit"
                  className={`px-4 py-2 bg-brand-black text-brand-white font-medium rounded hover:bg-brand-gray-800 transition-colors w-full ${isLoading ? 'opacity-75' : ''}`}
                  disabled={isLoading}
                >
                  {isLoading ? (
                    <>
                      <svg className="animate-spin inline-block h-5 w-5 mr-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Sending...
                    </>
                  ) : 'Send Verification Email'}
                </button>
              </form>
              
              <div className="text-center mt-6">
                <p className="text-brand-gray-600">
                  Already verified?{' '}
                  <Link to="/login" className="text-brand-black hover:text-brand-gray-600 border-b border-brand-gray-400 font-medium">
                    Back to Login
                  </Link>
                </p>
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  )
}

export default ResendVerification