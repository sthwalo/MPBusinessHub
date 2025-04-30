import { useState } from 'react'
import { Routes, Route, Navigate } from 'react-router-dom'
import { Toaster } from 'react-hot-toast'
import Header from './components/Header'
import Footer from './components/Footer'
import Home from './pages/Home'
import BusinessDirectory from './pages/BusinessDirectory'
import BusinessDetails from './pages/BusinessDetails'
import Dashboard from './pages/Dashboard'
import Login from './pages/Login'
import Register from './pages/Register'
import ForgotPassword from './pages/ForgotPassword'
import ResetPassword from './pages/ResetPassword'
import VerifyEmail from './pages/VerifyEmail'
import ResendVerification from './pages/ResendVerification'
import NotFound from './pages/NotFound'
import Pricing from './pages/Pricing'
/* App.css import removed - using Tailwind CSS now */

function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false)

  return (
    <div className="app-container">
      <Header isAuthenticated={isAuthenticated} />
      <main className="main-content">
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
          <Route path="/login" element={<Login setIsAuthenticated={setIsAuthenticated} />} />
          <Route path="/register" element={<Register />} />
          <Route path="/pricing" element={<Pricing />} />
          <Route path="/forgot-password" element={<ForgotPassword />} />
          <Route path="/reset-password" element={<ResetPassword />} />
          <Route path="/verify-email" element={<VerifyEmail />} />
          <Route path="/resend-verification" element={<ResendVerification />} />
          <Route path="*" element={<NotFound />} />
        </Routes>
      </main>
      <Footer />
      {/* Toast notifications */}
      <Toaster 
        position="top-right" 
        toastOptions={{
          duration: 3000,
          style: {
            background: '#363636',
            color: '#fff',
          },
          success: {
            style: {
              background: '#166534', // Tailwind green-800
            },
          },
          error: {
            style: {
              background: '#991b1b', // Tailwind red-800
            },
          }
        }}
      />
    </div>
  )
}

export default App