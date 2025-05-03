// /client/src/pages/AdminDashboard.jsx
import React, { useState, useEffect } from 'react';
import { Routes, Route, Navigate, useNavigate } from 'react-router-dom';
import axios from 'axios';
import BusinessManagement from '../components/admin/BusinessManagement';

function AdminDashboard() {
  const [isAdmin, setIsAdmin] = useState(false);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    // Check if user is admin
    const checkAdminStatus = async () => {
      try {
        const response = await axios.get('/api/user/role', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('mpbh_token')}`
          }
        });
        
        if (response.data.role === 'admin') {
          setIsAdmin(true);
        } else {
          // Redirect non-admin users
          navigate('/dashboard');
        }
      } catch (error) {
        console.error('Error checking admin status:', error);
        navigate('/login');
      } finally {
        setLoading(false);
      }
    };
    
    checkAdminStatus();
  }, [navigate]);

  if (loading) {
    return (
      <div className="flex justify-center items-center h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900"></div>
      </div>
    );
  }

  if (!isAdmin) {
    return <Navigate to="/dashboard" replace />;
  }

  return (
    <div className="bg-brand-gray-50 min-h-screen">
      <div className="container mx-auto px-4 py-8">
        <div className="flex flex-col md:flex-row gap-6">
          {/* Sidebar */}
          <aside className="w-full md:w-64 bg-brand-white rounded-lg shadow-brand-md p-6">
            <h2 className="text-xl font-bold mb-6">Admin Panel</h2>
            <nav>
              <ul className="space-y-2">
                <li>
                  <a 
                    href="/admin/businesses" 
                    className="block px-4 py-2 rounded-md hover:bg-brand-gray-100 text-brand-gray-700"
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                      Business Management
                    </div>
                  </a>
                </li>
                <li>
                  <a 
                    href="/dashboard" 
                    className="block px-4 py-2 rounded-md hover:bg-brand-gray-100 text-brand-gray-700"
                  >
                    <div className="flex items-center">
                      <svg className="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                      </svg>
                      Return to Dashboard
                    </div>
                  </a>
                </li>
              </ul>
            </nav>
          </aside>

          {/* Main content area */}
          <main className="flex-1 bg-brand-white rounded-lg shadow-brand-md p-6">
            <Routes>
              <Route path="/" element={<Navigate to="/admin/businesses" replace />} />
              <Route path="/businesses" element={<BusinessManagement />} />
            </Routes>
          </main>
        </div>
      </div>
    </div>
  );
}

export default AdminDashboard;