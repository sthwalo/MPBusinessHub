import { useState, useEffect } from 'react'
import toast from 'react-hot-toast'

function BusinessHours({ businessData, updateBusinessData }) {
  const daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
  
  // Initialize operating hours
  const [operatingHours, setOperatingHours] = useState({})
  const [isLoading, setIsLoading] = useState(false)
  const [errors, setErrors] = useState({})

  useEffect(() => {
    // Initialize operating hours from business data or with defaults
    const initialHours = {}
    
    daysOfWeek.forEach(day => {
      if (businessData.operatingHours && businessData.operatingHours[day]) {
        initialHours[day] = {
          isClosed: businessData.operatingHours[day] === 'Closed',
          openingTime: businessData.operatingHours[day] !== 'Closed' 
            ? businessData.operatingHours[day].split(' - ')[0] 
            : '09:00',
          closingTime: businessData.operatingHours[day] !== 'Closed' 
            ? businessData.operatingHours[day].split(' - ')[1] 
            : '17:00'
        }
      } else {
        // Default values
        initialHours[day] = {
          isClosed: false,
          openingTime: '09:00',
          closingTime: '17:00'
        }
      }
    })
    
    setOperatingHours(initialHours)
  }, [businessData])

  const handleToggleClosed = (day) => {
    setOperatingHours(prev => ({
      ...prev,
      [day]: {
        ...prev[day],
        isClosed: !prev[day].isClosed
      }
    }))
  }

  const handleTimeChange = (day, field, value) => {
    setOperatingHours(prev => ({
      ...prev,
      [day]: {
        ...prev[day],
        [field]: value
      }
    }))
  }

  const validateHours = () => {
    const newErrors = {}
    
    // Validate each day's hours
    Object.entries(operatingHours).forEach(([day, hours]) => {
      if (!hours.isClosed) {
        // Validate time format (HH:MM)
        const timeRegex = /^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/
        
        if (!timeRegex.test(hours.openingTime)) {
          newErrors[`${day}_opening`] = 'Invalid time format (use HH:MM)'
        }
        
        if (!timeRegex.test(hours.closingTime)) {
          newErrors[`${day}_closing`] = 'Invalid time format (use HH:MM)'
        }
        
        // Validate opening time is before closing time
        if (timeRegex.test(hours.openingTime) && timeRegex.test(hours.closingTime)) {
          const opening = hours.openingTime.split(':').map(Number)
          const closing = hours.closingTime.split(':').map(Number)
          
          if (opening[0] > closing[0] || (opening[0] === closing[0] && opening[1] >= closing[1])) {
            newErrors[`${day}_range`] = 'Opening time must be before closing time'
          }
        }
      }
    })
    
    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const formatHoursForAPI = () => {
    const formattedHours = {}
    
    Object.entries(operatingHours).forEach(([day, hours]) => {
      if (hours.isClosed) {
        formattedHours[day] = 'Closed'
      } else {
        formattedHours[day] = `${hours.openingTime} - ${hours.closingTime}`
      }
    })
    
    return formattedHours
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    if (!validateHours()) {
      toast.error('Please fix the errors in your operating hours')
      return
    }
    
    setIsLoading(true)
    
    try {
      // Get the auth token from localStorage
      const token = localStorage.getItem('mpbh_token')
      if (!token) {
        throw new Error('Authentication token not found')
      }
      
      // Format hours for API
      const formattedHours = formatHoursForAPI()
      
      // Make API request to update operating hours
      const response = await fetch('/api/business/update', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          businessName: businessData.name,
          category: businessData.category,
          district: businessData.district,
          description: businessData.description,
          phone: businessData.phone,
          email: businessData.email,
          website: businessData.website || '',
          address: businessData.address,
          operatingHours: formattedHours
        })
      })
      
      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Failed to update operating hours')
      }
      
      const data = await response.json()
      
      if (data.status === 'success') {
        toast.success('Operating hours updated successfully!')
        
        // Update the parent component's business data
        if (typeof updateBusinessData === 'function') {
          updateBusinessData({
            ...businessData,
            operatingHours: formattedHours
          })
        }
      } else {
        throw new Error(data.message || 'Failed to update operating hours')
      }
    } catch (error) {
      console.error('Error updating operating hours:', error)
      toast.error(error.message || 'An error occurred while updating operating hours')
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="bg-brand-white border border-brand-gray-200 rounded-lg p-6">
      <h2 className="text-2xl font-bold mb-6">Business Hours</h2>
      
      {Object.keys(operatingHours).length === 0 ? (
        <div className="flex justify-center items-center h-40">
          <div className="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-brand-black"></div>
        </div>
      ) : (
        <form onSubmit={handleSubmit}>
          <div className="space-y-6">
            {daysOfWeek.map((day) => (
              <div key={day} className="border-b border-brand-gray-200 pb-4">
                <div className="flex items-center justify-between mb-2">
                  <div className="flex items-center">
                    <span className="capitalize font-medium text-lg mr-4">{day}</span>
                    <div className="relative inline-block w-12 mr-2 align-middle select-none">
                      <input 
                        type="checkbox" 
                        id={`toggle-${day}`} 
                        checked={!operatingHours[day].isClosed}
                        onChange={() => handleToggleClosed(day)}
                        className="sr-only"
                      />
                      <label 
                        htmlFor={`toggle-${day}`}
                        className={`block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer ${!operatingHours[day].isClosed ? 'bg-brand-black' : ''}`}
                      >
                        <span 
                          className={`dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform duration-300 ease-in-out ${!operatingHours[day].isClosed ? 'transform translate-x-6' : ''}`}
                        ></span>
                      </label>
                    </div>
                    <span className="text-sm text-brand-gray-600 ml-2">
                      {operatingHours[day].isClosed ? 'Closed' : 'Open'}
                    </span>
                  </div>
                </div>
                
                {!operatingHours[day].isClosed && (
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label htmlFor={`opening-${day}`} className="block text-sm font-medium text-brand-gray-700 mb-1">
                        Opening Time
                      </label>
                      <input
                        type="time"
                        id={`opening-${day}`}
                        value={operatingHours[day].openingTime}
                        onChange={(e) => handleTimeChange(day, 'openingTime', e.target.value)}
                        className="w-full p-2 border border-brand-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-black"
                      />
                      {errors[`${day}_opening`] && (
                        <p className="text-red-500 text-xs mt-1">{errors[`${day}_opening`]}</p>
                      )}
                    </div>
                    <div>
                      <label htmlFor={`closing-${day}`} className="block text-sm font-medium text-brand-gray-700 mb-1">
                        Closing Time
                      </label>
                      <input
                        type="time"
                        id={`closing-${day}`}
                        value={operatingHours[day].closingTime}
                        onChange={(e) => handleTimeChange(day, 'closingTime', e.target.value)}
                        className="w-full p-2 border border-brand-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-brand-black"
                      />
                      {errors[`${day}_closing`] && (
                        <p className="text-red-500 text-xs mt-1">{errors[`${day}_closing`]}</p>
                      )}
                    </div>
                    {errors[`${day}_range`] && (
                      <p className="text-red-500 text-xs mt-1 col-span-2">{errors[`${day}_range`]}</p>
                    )}
                  </div>
                )}
              </div>
            ))}
          </div>
          
          <div className="mt-6 flex justify-end">
            <button
              type="submit"
              className={`px-6 py-2 bg-brand-black text-brand-white rounded-md hover:bg-brand-gray-800 transition-colors ${isLoading ? 'opacity-70 cursor-not-allowed' : ''}`}
              disabled={isLoading}
            >
              {isLoading ? (
                <>
                  <svg className="animate-spin h-5 w-5 mr-3 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Saving...
                </>
              ) : 'Save Hours'}
            </button>
          </div>
        </form>
      )}
    </div>
  )
}

export default BusinessHours