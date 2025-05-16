import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';

function BusinessManagement() {
  const [businesses, setBusinesses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [rejectionReason, setRejectionReason] = useState('');
  const [selectedBusinessId, setSelectedBusinessId] = useState(null);
  const [showRejectModal, setShowRejectModal] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    fetchBusinesses();
  }, []);

  const fetchBusinesses = async () => {
    try {
      setLoading(true);
      const response = await axios.get('/api/admin/businesses', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('mpbh_token')}`
        }
      });
      
      setBusinesses(response.data.data);
      setLoading(false);
    } catch (error) {
      setError('Failed to load businesses. Please try again.');
      setLoading(false);
      console.error('Error fetching businesses:', error);
    }
  };

  const handleViewBusiness = (id) => {
    navigate(`/business/${id}`);
  };

  const handleApproveBusiness = async (id) => {
    try {
      const response = await axios.put(`/api/businesses/${id}/approve`, {}, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('mpbh_token')}`
        }
      });
      
      // Update the business in the list
      setBusinesses(businesses.map(business => 
        business.id === id ? { ...business, status: 'approved' } : business
      ));
      
      alert('Business approved successfully!');
    } catch (error) {
      alert('Failed to approve business. Please try again.');
      console.error('Error approving business:', error);
    }
  };

  const openRejectModal = (id) => {
    setSelectedBusinessId(id);
    setShowRejectModal(true);
  };

  const closeRejectModal = () => {
    setSelectedBusinessId(null);
    setRejectionReason('');
    setShowRejectModal(false);
  };

  const handleRejectBusiness = async () => {
    if (!rejectionReason.trim()) {
      alert('Please provide a reason for rejection.');
      return;
    }
    
    try {
      const response = await axios.put(`/api/businesses/${selectedBusinessId}/reject`, {
        reason: rejectionReason
      }, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('mpbh_token')}`
        }
      });
      
      // Update the business in the list
      setBusinesses(businesses.map(business => 
        business.id === selectedBusinessId ? { ...business, status: 'rejected' } : business
      ));
      
      closeRejectModal();
      alert('Business rejected successfully!');
    } catch (error) {
      alert('Failed to reject business. Please try again.');
      console.error('Error rejecting business:', error);
    }
  };

  const getStatusBadgeClass = (status) => {
    switch (status) {
      case 'pending':
        return 'bg-yellow-100 text-yellow-800';
      case 'approved':
        return 'bg-green-100 text-green-800';
      case 'rejected':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong className="font-bold">Error!</strong>
        <span className="block sm:inline"> {error}</span>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold mb-6">Business Management</h1>
      
      <div className="mb-6">
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-semibold">Businesses</h2>
          <button 
            className="bg-brand-black text-white px-4 py-2 rounded"
            onClick={fetchBusinesses}
          >
            Refresh
          </button>
        </div>
      </div>
      
      {businesses.length === 0 ? (
        <div className="text-center py-8">
          <p className="text-gray-500">No businesses found.</p>
        </div>
      ) : (
        <div className="overflow-x-auto">
          <table className="min-w-full bg-white border border-gray-200">
            <thead>
              <tr>
                <th className="py-3 px-4 bg-gray-100 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Business Name
                </th>
                <th className="py-3 px-4 bg-gray-100 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Category
                </th>
                <th className="py-3 px-4 bg-gray-100 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  District
                </th>
                <th className="py-3 px-4 bg-gray-100 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th className="py-3 px-4 bg-gray-100 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {businesses.map((business) => (
                <tr key={business.id}>
                  <td className="py-4 px-4 whitespace-nowrap">
                    <div className="font-medium text-gray-900">{business.name}</div>
                  </td>
                  <td className="py-4 px-4 whitespace-nowrap">
                    <div className="text-gray-500">{business.category}</div>
                  </td>
                  <td className="py-4 px-4 whitespace-nowrap">
                    <div className="text-gray-500">{business.district}</div>
                  </td>
                  <td className="py-4 px-4 whitespace-nowrap">
                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusBadgeClass(business.status)}`}>
                      {business.status}
                    </span>
                  </td>
                  <td className="py-4 px-4 whitespace-nowrap text-sm font-medium">
                    <button
                      onClick={() => handleViewBusiness(business.id)}
                      className="text-blue-600 hover:text-blue-900 mr-3"
                    >
                      View
                    </button>
                    {business.status === 'pending' && (
                      <>
                        <button
                          onClick={() => handleApproveBusiness(business.id)}
                          className="text-green-600 hover:text-green-900 mr-3"
                        >
                          Approve
                        </button>
                        <button
                          onClick={() => openRejectModal(business.id)}
                          className="text-red-600 hover:text-red-900"
                        >
                          Reject
                        </button>
                      </>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
      
      {/* Rejection Modal */}
      {showRejectModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 className="text-lg font-bold mb-4">Reject Business</h3>
            <p className="mb-4">Please provide a reason for rejecting this business:</p>
            <textarea
              className="w-full border border-gray-300 rounded p-2 mb-4"
              rows="4"
              value={rejectionReason}
              onChange={(e) => setRejectionReason(e.target.value)}
              placeholder="Reason for rejection..."
            ></textarea>
            <div className="flex justify-end space-x-2">
              <button
                className="px-4 py-2 border border-gray-300 rounded text-gray-700"
                onClick={closeRejectModal}
              >
                Cancel
              </button>
              <button
                className="px-4 py-2 bg-red-600 text-white rounded"
                onClick={handleRejectBusiness}
              >
                Reject Business
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default BusinessManagement;