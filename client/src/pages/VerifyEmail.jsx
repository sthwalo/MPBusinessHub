import { useState, useEffect } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import toast from 'react-hot-toast'

function VerifyEmail() {
  const [status, setStatus] = useState('loading') // loading, success, error
  const [message, setMessage] = useState('')
  const location = useLocation()
  const navigate = useNavigate()

  useEffect(() => {
    const verifyEmail = async () => {
      try {
        // Get query parameters from URL
        const queryParams = new URLSearchParams(location.search)
        const id = queryParams.get('id')
        const hash = queryParams.get('hash')
        const expires = queryParams.get('expires')
        const signature = queryParams.get('signature')

        if (!id || !hash || !expires || !signature) {
          setStatus('error')
          setMessage('Invalid verification link')
          return
        }

        // Call the API to verify the email
        const response = await fetch(`/api/email/verify/${id}/${hash}?expires=${expires}&signature=${signature}`, {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        })

        const data = await response.json()

        if (response.ok) {
          setStatus('success')
          setMessage(data.message || 'Email verified successfully')
          toast.success('Email verified successfully')
          
          // If user is logged in, update their status
          const token = localStorage.getItem('mpbh_token')
          if (token) {
            // Redirect to dashboard after 3 seconds
            setTimeout(() => {
              navigate('/dashboard')
            }, 3000)
          } else {
            // Redirect to login after 3 seconds
            setTimeout(() => {
              navigate('/login')
            }, 3000)
          }
        } else {
          setStatus('error')
          setMessage(data.message || 'Failed to verify email')
          toast.error('Failed to verify email')
        }
      } catch (error) {
        console.error('Verification error:', error)
        setStatus('error')
        setMessage('An error occurred during verification')
        toast.error('An error occurred during verification')
      }
    }

    verifyEmail()
  }, [location, navigate])

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="max-w-md mx-auto bg-brand-white rounded-lg shadow-md border border-brand-gray-200 overflow-hidden">
        <div className="p-8">
          <h2 className="text-2xl font-bold text-center mb-6">Email Verification</h2>
          
          {status === 'loading' && (
            <div className="text-center">
              <div className="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-brand-black mb-4"></div>
              <p className="text-brand-gray-600">Verifying your email address...</p>
            </div>
          )}
          
          {status === 'success' && (
            <div className="text-center">
              <div className="bg-green-50 text-green-800 p-4 rounded-md mb-6">
                <p>{message}</p>
                <p className="mt-2">You will be redirected shortly.</p>
              </div>
              <Link 
                to="/login" 
                className="inline-block px-4 py-2 bg-brand-black text-brand-white font-medium rounded hover:bg-brand-gray-800 transition-colors"
              >
                Go to Login
              </Link>
            </div>
          )}
          
          {status === 'error' && (
            <div className="text-center">
              <div className="bg-red-50 text-red-800 p-4 rounded-md mb-6">
                <p>{message}</p>
                <p className="mt-2">Please try again or request a new verification link.</p>
              </div>
              <Link 
                to="/login" 
                className="inline-block px-4 py-2 bg-brand-black text-brand-white font-medium rounded hover:bg-brand-gray-800 transition-colors mr-4"
              >
                Go to Login
              </Link>
              <button 
                onClick={() => navigate('/resend-verification')}
                className="inline-block px-4 py-2 border border-brand-black text-brand-black font-medium rounded hover:bg-brand-gray-100 transition-colors"
              >
                Resend Verification
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}

export default VerifyEmail