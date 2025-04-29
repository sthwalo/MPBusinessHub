import { useState, useEffect } from 'react'
import { Link, useNavigate, useLocation } from 'react-router-dom'
import toast from 'react-hot-toast'

function ResetPassword() {
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    password_confirmation: '',
    token: ''
  })
  const [errors, setErrors] = useState({})
  const [isLoading, setIsLoading] = useState(false)
  const [isReset, setIsReset] = useState(false)
  const navigate = useNavigate()
  const location = useLocation()

  useEffect(() => {
    // Extract token and email from URL query parameters
    const queryParams = new URLSearchParams(location.search)
    const token = queryParams.get('token')
    const email = queryParams.get('email')
    
    if (token && email) {
      setFormData(prev => ({
        ...prev,
        email: decodeURIComponent(email),
        token
      }))
    }
  }, [location])

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData(prevData => ({
      ...prevData,
      [name]: value
    }))
    
    // Clear error when user starts typing
    if (errors[name]) {
      setErrors(prev => ({ ...prev, [name]: '' }))
    }
  }

  const validateForm = () => {
    const newErrors = {}
    
    if (!formData.email) {
      newErrors.email = 'Email is required'
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Email is invalid'
    }
    
    if (!formData.password) {
      newErrors.password = 'Password is required'
    } else if (formData.password.length < 8) {
      newErrors.password = 'Password must be at least 8 characters'
    }
    
    if (!formData.password_confirmation) {
      newErrors.password_confirmation = 'Please confirm your password'
    } else if (formData.password !== formData.password_confirmation) {
      newErrors.password_confirmation = 'Passwords do not match'
    }
    
    if (!formData.token) {
      newErrors.token = 'Reset token is missing'
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
      const loadingToast = toast.loading('Resetting password...')
      
      // Make the API call to our Laravel backend
      await fetch('/api/password/reset', {
        method: 'POST',
        headers: { 
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
      }).then(async (response) => {
        // Clear loading toast
        toast.dismiss(loadingToast)
        
        if (!response.ok) {
          const errorData = await response.json()
          throw new Error(errorData.message || 'Failed to reset password')
        }
        
        return response.json()
      })
      
      // Show success message
      toast.success('Password has been reset successfully')
      setIsReset(true)
      
      // Redirect to login after 3 seconds
      setTimeout(() => {
        navigate('/login')
      }, 3000)
    } catch (error) {
      console.error('Password reset error:', error)
      
      if (error.response && error.response.status === 422) {
        // Validation errors
        if (error.response.data.errors) {
          setErrors(error.response.data.errors)
        } else {
          setErrors({ form: 'Invalid or expired password reset token' })
        }
      } else {
        // Generic error
        setErrors({ form: error.message || 'Failed to reset password. Please try again.' })
      }
      
      toast.error('Failed to reset password')
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="max-w-md mx-auto bg-brand-white rounded-lg shadow-md border border-brand-gray-200 overflow-hidden">
        <div className="p-8">
          <h2 className="text-2xl font-bold text-center mb-6">Reset Your Password</h2>
          
          {isReset ? (
            <div className="text-center">
              <div className="bg-green-50 text-green-800 p-4 rounded-md mb-6">
                <p>Your password has been reset successfully!</p>
                <p className="mt-2">You will be redirected to the login page shortly.</p>
              </div>
              <Link 
                to="/login" 
                className="inline-block px-4 py-2 bg-brand-black text-brand-white font-medium rounded hover:bg-brand-gray-800 transition-colors"
              >
                Go to Login
              </Link>
            </div>
          ) : (
            <>
              {errors.form && (
                <div className="bg-red-50 text-red-800 p-4 rounded-md mb-6">
                  {errors.form}
                </div>
              )}
              
              {!formData.token && (
                <div className="bg-yellow-50 text-yellow-800 p-4 rounded-md mb-6">
                  Invalid or missing reset token. Please use the link from your email.
                </div>
              )}
              
              <form onSubmit={handleSubmit}>
                <div className="mb-6">
                  <label htmlFor="email" className="block text-brand-black font-medium mb-1">Email Address</label>
                  <input
                    type="email"
                    id="email"
                    name="email"
                    className={`w-full p-2 border ${errors.email ? 'border-red-500' : 'border-brand-gray-300'} rounded focus:outline-none focus:ring-1 focus:ring-brand-black`}
                    value={formData.email}
                    onChange={handleChange}
                    placeholder="youremail@example.com"
                    readOnly={!!formData.email}
                  />
                  {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email}</p>}
                </div>
                
                <div className="mb-6">
                  <label htmlFor="password" className="block text-brand-black font-medium mb-1">New Password</label>
                  <input
                    type="password"
                    id="password"
                    name="password"
                    className={`w-full p-2 border ${errors.password ? 'border-red-500' : 'border-brand-gray-300'} rounded focus:outline-none focus:ring-1 focus:ring-brand-black`}
                    value={formData.password}
                    onChange={handleChange}
                    placeholder="••••••••"
                  />
                  {errors.password && <p className="text-red-500 text-sm mt-1">{errors.password}</p>}
                </div>
                
                <div className="mb-6">
                  <label htmlFor="password_confirmation" className="block text-brand-black font-medium mb-1">Confirm New Password</label>
                  <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    className={`w-full p-2 border ${errors.password_confirmation ? 'border-red-500' : 'border-brand-gray-300'} rounded focus:outline-none focus:ring-1 focus:ring-brand-black`}
                    value={formData.password_confirmation}
                    onChange={handleChange}
                    placeholder="••••••••"
                  />
                  {errors.password_confirmation && <p className="text-red-500 text-sm mt-1">{errors.password_confirmation}</p>}
                </div>
                
                <input type="hidden" name="token" value={formData.token} />
                
                <button
                  type="submit"
                  className={`px-4 py-2 bg-brand-black text-brand-white font-medium rounded hover:bg-brand-gray-800 transition-colors w-full ${isLoading || !formData.token ? 'opacity-75' : ''}`}
                  disabled={isLoading || !formData.token}
                >
                  {isLoading ? (
                    <>
                      <svg className="animate-spin inline-block h-5 w-5 mr-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Resetting...
                    </>
                  ) : 'Reset Password'}
                </button>
              </form>
              
              <div className="text-center mt-6">
                <p className="text-brand-gray-600">
                  Remember your password?{' '}
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

export default ResetPassword