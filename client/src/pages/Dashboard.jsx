import { useState, useEffect } from 'react'
import { Routes, Route, Link, useNavigate, useLocation } from 'react-router-dom'
import axios from 'axios'
import DashboardHome from '../components/dashboard/DashboardHome'
import BusinessProfile from '../components/dashboard/BusinessProfile'
import ProductsManagement from '../components/dashboard/ProductsManagement'
import AdvertsManagement from '../components/dashboard/AdvertsManagement'
import PaymentHistory from '../components/dashboard/PaymentHistory'
import UpgradePlan from '../components/dashboard/UpgradePlan'
import SessionManagement from './SessionManagement'
import BusinessHours from '../components/dashboard/BusinessHours'
import SocialMediaManagement from '../components/dashboard/SocialMediaManagement'

function Dashboard() {
  const [businessData, setBusinessData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const navigate = useNavigate()
  const location = useLocation()
  const [isAdmin, setIsAdmin] = useState(false);
  
  // Check which dashboard tab is active
  const isActive = (path) => {
    return location.pathname === path || location.pathname === path + '/'
  }

  // Function to update business data after profile update
  const updateBusinessData = (updatedData) => {
    setBusinessData(prevData => ({
      ...prevData,
      ...updatedData
    }));
    localStorage.setItem('simulatedBusinessData', JSON.stringify(prevData));
  };

  // Make the updateBusinessData function available globally for the BusinessProfile component
  window.updateBusinessData = updateBusinessData;

  // Add this function to handle package upgrades
  const handlePackageUpgrade = (updatedBusinessData) => {
    // Save to localStorage for persistence
    localStorage.setItem('simulatedBusinessData', JSON.stringify(updatedBusinessData));
    
    // Update the businessData in Dashboard's state
    setBusinessData(prev => ({
      ...prev,
      ...updatedBusinessData,
      subscription: {
        ...prev.subscription,
        ...updatedBusinessData.subscription,
        status: 'active'
      }
    }));
  };

  useEffect(() => {
    // Fetch the user's business data from our API
    const fetchBusinessData = async () => {
      try {
        // Get the auth token from localStorage
        const token = localStorage.getItem('mpbh_token');
        if (!token) {
          // No token found, redirect to login
          navigate('/login');
          return;
        }
        
        const checkAdminStatus = async () => {
          try {
            const response = await axios.get('/api/user/role', {
              headers: {
                'Authorization': `Bearer ${localStorage.getItem('mpbh_token')}`
              }
            });
            
            if (response.data.role === 'admin') {
              setIsAdmin(true);
            }
          } catch (error) {
            console.error('Error checking admin status:', error);
          }
        };
        
        checkAdminStatus();
        // Fetch business details from our Laravel backend
        const response = await fetch('/api/business/details', {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });
        
        if (!response.ok) {
          // If we get a 401 Unauthorized, redirect to login
          if (response.status === 401) {
            localStorage.removeItem('mpbh_token');
            navigate('/login');
            return;
          }
          
          throw new Error(`Failed to fetch business data: ${response.status}`);
        }
        
        // Parse the response data
        const result = await response.json();
        
        if (result.status === 'success') {
          // Get payment simulation data if it exists
          const paymentSimulation = localStorage.getItem('simulatedPaymentData') 
            ? JSON.parse(localStorage.getItem('simulatedPaymentData'))
            : {
                status: 'active',
                next_billing_date: '2025-05-01',
                amount: 0
              };

          // Use actual business data from the database, only simulate payment data
          const businessDataWithSimulatedPayment = {
            // Use all actual business data from the database
            ...result.data,
            // Only simulate subscription data
            subscription: paymentSimulation,
            // Ensure statistics are properly structured
            statistics: {
              views: result.data.views || 0,
              contacts: result.data.contacts || 0,
              reviews: result.data.review_count || 0
            }
          };
          
          setBusinessData(businessDataWithSimulatedPayment);
        } else {
          throw new Error(result.message || 'Failed to fetch business data');
        }
      } catch (error) {
        console.error('Error fetching business data:', error);
        
        // Handle error and redirect to login
        console.error('Error fetching business data:', error);
        localStorage.removeItem('mpbh_token');
        navigate('/login');
      } finally {
        setLoading(false);
      }
    };
    
    // Call the fetch function
    fetchBusinessData();
  }, [])
  
  const handleLogout = () => {
    // Clear authentication token and user data
    localStorage.removeItem('mpbh_token')
    localStorage.removeItem('mpbh_user')
    // Redirect to login page
    navigate('/login')
    window.location.reload() // Add this line to ensure complete logout
  }

  const toggleSidebar = () => {
    setSidebarOpen(!sidebarOpen)
  }

  if (loading) {
    return (
      <div className="min-h-screen bg-brand-gray-100 flex justify-center items-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-black"></div>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-brand-gray-100">
      {/* Dashboard header */}
      <header className="bg-brand-white shadow-brand-sm">
        <div className="container mx-auto px-4 py-4 flex justify-between items-center">
          <div className="flex items-center">
            <button 
              className="lg:hidden mr-4 text-gray-500 hover:text-gray-700"
              onClick={toggleSidebar}
            >
              <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
            <h1 className="text-2xl font-bold text-gray-800">Business Dashboard</h1>
          </div>
          
          <div className="flex items-center space-x-4">
            {isAdmin && (
              <Link 
                to="/admin"
                className="bg-purple-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-purple-700"
              >
                Admin Dashboard
              </Link>
            )}
            <div className="text-right">
              <p className="font-medium text-gray-800">{businessData.name}</p>
              <div className="text-sm text-gray-500 flex items-center">
                <span className={`inline-block w-2 h-2 rounded-full mr-1 ${businessData.package_type === 'Gold' ? 'bg-tier-gold' : businessData.package_type === 'Silver' ? 'bg-tier-silver' : businessData.package_type === 'Bronze' ? 'bg-tier-bronze' : 'bg-tier-basic'}`}></span>
                {businessData.package_type} Package
              </div>
            </div>
            <div className="relative">
              <button className="h-10 w-10 rounded-full bg-brand-gray-100 flex items-center justify-center text-brand-black font-bold uppercase">
                {businessData.name.charAt(0)}
              </button>
            </div>
          </div>
        </div>
      </header>

      <div className="container mx-auto px-4 py-8">
        <div className="flex flex-col lg:flex-row gap-8">
          {/* Sidebar navigation */}
          <aside className={`lg:w-1/4 bg-brand-white rounded-lg shadow-brand-md overflow-hidden ${sidebarOpen ? 'block' : 'hidden'} lg:block`}>
            <nav className="p-4">
              <ul className="space-y-2">
                <li>
                  <Link 
                    to="/dashboard" 
                    className={`block px-4 py-2 rounded-md ${isActive('/dashboard') ? 'bg-brand-black text-brand-white' : 'hover:bg-brand-gray-100'}`}
                    onClick={() => setSidebarOpen(false)}
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                      </svg>
                      Dashboard Home
                    </div>
                  </Link>
                </li>
                <li>
                  {isAdmin && (
                    <Link 
                      to="/admin" 
                      className={`block px-4 py-2 rounded-md ${isActive('/admin') ? 'bg-brand-black text-brand-white' : 'hover:bg-brand-gray-100'}`}
                      onClick={() => setSidebarOpen(false)}
                    >
                      <div className="flex items-center">
                        <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Admin Panel
                      </div>
                    </Link>
                  )}
                  <Link 
                    to="/dashboard/profile" 
                    className={`block px-4 py-2 rounded-md ${isActive('/dashboard/profile') ? 'bg-brand-black text-brand-white' : 'hover:bg-brand-gray-100'}`}
                    onClick={() => setSidebarOpen(false)}
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                      Business Profile
                    </div>
                  </Link>
                </li>
                <li>
                  <Link 
                    to="/dashboard/hours" 
                    className={`block px-4 py-2 rounded-md ${isActive('/dashboard/hours') ? 'bg-brand-black text-brand-white' : 'hover:bg-brand-gray-100'}`}
                    onClick={() => setSidebarOpen(false)}
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      Business Hours
                    </div>
                  </Link>
                </li>
                <li>
                  <Link 
                    to="/dashboard/products" 
                    className={`block px-4 py-2 rounded-md ${isActive('/dashboard/products') ? 'bg-brand-black text-brand-white' : 'hover:bg-brand-gray-100'}`}
                    onClick={() => setSidebarOpen(false)}
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                      </svg>
                      Manage Products
                      {(businessData.package_type === 'Basic' || businessData.package_type === 'Bronze') && (
                        <span className="ml-2 text-xs bg-brand-gray-100 text-brand-gray-700 px-2 py-1 rounded">
                          Upgrade
                        </span>
                      )}
                    </div>
                  </Link>
                </li>
                <li>
                  <Link 
                    to="/dashboard/adverts" 
                    className={`block px-4 py-2 rounded-md ${isActive('/dashboard/adverts') ? 'bg-brand-black text-brand-white' : 'hover:bg-brand-gray-100'}`}
                    onClick={() => setSidebarOpen(false)}
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                      </svg>
                      Adverts Management
                      <span className="ml-2 bg-brand-gray-100 text-brand-black text-xs px-2 py-1 rounded">
                        {businessData.adverts_remaining}
                      </span>
                    </div>
                  </Link>
                </li>
                <li>
                  <Link 
                    to="/dashboard/payments" 
                    className={`block px-4 py-2 rounded-md ${isActive('/dashboard/payments') ? 'bg-brand-black text-brand-white' : 'hover:bg-brand-gray-100'}`}
                    onClick={() => setSidebarOpen(false)}
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                      </svg>
                      Payment History
                    </div>
                  </Link>
                </li>
                <li>
                  <Link 
                    to="/dashboard/upgrade" 
                    className={`block px-4 py-2 rounded-md ${isActive('/dashboard/upgrade') ? 'bg-brand-black text-brand-white' : 'hover:bg-brand-gray-100'}`}
                    onClick={() => setSidebarOpen(false)}
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                      </svg>
                      Upgrade Plan
                    </div>
                  </Link>
                </li>
                <li className="border-t mt-4 pt-4">
                  <Link 
                    to="/dashboard/session-management" 
                    className="block px-4 py-2 rounded-md hover:bg-brand-gray-100 text-brand-gray-700"
                    onClick={() => setSidebarOpen(false)}
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                      </svg>
                      Manage Sessions
                    </div>
                  </Link>
                </li>
                <li>
                <Link 
                  to="/dashboard/social-media" 
                  className={`flex items-center p-2 rounded hover:bg-brand-gray-100 ${
                    location.pathname === '/dashboard/social-media' ? 'bg-brand-gray-100' : ''
                  }`}
                  onClick={() => setSidebarOpen(false)}
                >
                  <svg className="w-5 h-5 mr-2 text-brand-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  Social Media
                </Link>
                  <button 
                    onClick={handleLogout}
                    className="block w-full text-left px-4 py-2 rounded-md hover:bg-brand-gray-100 text-brand-gray-700"
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                      </svg>
                      Logout
                    </div>
                  </button>
                </li>
              </ul>
            </nav>
          </aside>

          {/* Main content area */}
          <main className="flex-1 bg-brand-white rounded-lg shadow-brand-md p-6">
            {businessData ? (
              <Routes>
                <Route path="/" element={<DashboardHome businessData={businessData} />} />
                <Route path="/profile" element={<BusinessProfile businessData={businessData} updateBusinessData={updateBusinessData} />} />
                <Route path="/hours" element={<BusinessHours businessData={businessData} />} />
                <Route path="/products" element={<ProductsManagement businessData={businessData} />} />
                <Route path="/adverts" element={<AdvertsManagement businessData={businessData} />} />
                <Route path="/payments" element={<PaymentHistory businessData={businessData} />} />
                <Route path="/upgrade" element={<UpgradePlan businessData={businessData} onUpgrade={handlePackageUpgrade} />} />
                <Route path="/session-management" element={<SessionManagement />} />
                <Route path="/social-media" element={<SocialMediaManagement businessData={businessData} onUpdate={updateBusinessData} />} />
              </Routes>
            ) : (
              <div className="text-center py-12">
                <h2 className="text-2xl font-bold mb-4">No Business Profile Found</h2>
                <p className="mb-6">You need to register a business to access the dashboard features.</p>
                <Link to="/register" className="px-4 py-2 bg-brand-black text-brand-white font-medium rounded hover:bg-brand-gray-800 transition-colors">
                  Register a Business
                </Link>
              </div>
            )}
          </main>
        </div>
      </div>
    </div>
  )
}

export default Dashboard
