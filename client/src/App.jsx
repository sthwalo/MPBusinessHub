import { useState, useEffect, lazy, Suspense } from 'react'
import { Routes, Route, Navigate } from 'react-router-dom'
import { Toaster } from 'react-hot-toast'
import Header from './components/Header'
import Footer from './components/Footer'
import LoadingSpinner from './components/LoadingSpinner.jsx' 

// Lazy load all page components
const Home = lazy(() => import('./pages/Home'))
const BusinessDirectory = lazy(() => import('./pages/BusinessDirectory'))
const BusinessDetails = lazy(() => import('./pages/BusinessDetails'))
const AdminDashboard = lazy(() => import('./pages/AdminDashboard'))
const Dashboard = lazy(() => import('./pages/Dashboard'))
const Login = lazy(() => import('./pages/Login'))
const Register = lazy(() => import('./pages/Register'))
const ForgotPassword = lazy(() => import('./pages/ForgotPassword'))
const ResetPassword = lazy(() => import('./pages/ResetPassword'))
const VerifyEmail = lazy(() => import('./pages/VerifyEmail'))
const ResendVerification = lazy(() => import('./pages/ResendVerification'))
const NotFound = lazy(() => import('./pages/NotFound'))
const Pricing = lazy(() => import('./pages/Pricing'))

function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false)

  useEffect(() => {
    const token = localStorage.getItem('mpbh_token')
    if (token) {
      setIsAuthenticated(true)
    }
  }, [])

  return (
    <div className="app-container">
      <Header isAuthenticated={isAuthenticated} />
      <main className="main-content">
        <Suspense fallback={<LoadingSpinner />}>
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/directory" element={<BusinessDirectory />} />
            <Route path="/business/:id" element={<BusinessDetails />} />
            <Route 
              path="/dashboard/*" 
              element={
                isAuthenticated ? <Dashboard /> : <Navigate to="/login" replace />
              } 
            />
            <Route 
              path="/admin/*" 
              element={
                isAuthenticated ? <AdminDashboard /> : <Navigate to="/login" replace />
              } 
            />
            <Route path="/login" element={<Login setIsAuthenticated={setIsAuthenticated} />} />
            <Route path="/pricing" element={<Pricing />} />
            
            {/* Add the missing routes */}
            <Route path="/register" element={<Register />} />
            <Route path="/forgot-password" element={<ForgotPassword />} />
            <Route path="/reset-password/:token" element={<ResetPassword />} />
            <Route path="/verify-email/:token" element={<VerifyEmail />} />
            <Route path="/resend-verification" element={<ResendVerification />} />
            <Route path="*" element={<NotFound />} /> {/* 404 catch-all route */}
          </Routes>
        </Suspense>
      </main>
      <Footer />
      <Toaster position="top-right" />
    </div>
  )
}

export default App