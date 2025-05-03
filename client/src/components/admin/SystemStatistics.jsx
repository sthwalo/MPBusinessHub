import React, { useState, useEffect } from 'react';
import axios from 'axios';

function SystemStatistics() {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [timeRange, setTimeRange] = useState('week'); // 'day', 'week', 'month', 'year'

  useEffect(() => {
    fetchStatistics();
  }, [timeRange]);

  const fetchStatistics = async () => {
    try {
      setLoading(true);
      const response = await axios.get(`/api/admin/statistics?range=${timeRange}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('mpbh_token')}`
        }
      });
      
      setStats(response.data);
      setLoading(false);
    } catch (error) {
      setError('Failed to load statistics. Please try again.');
      setLoading(false);
      console.error('Error fetching statistics:', error);
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

  // If we're still waiting for data
  if (!stats) {
    return (
      <div className="text-center py-8">
        <p className="text-gray-500">No statistics available.</p>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold mb-6">System Statistics</h1>
      
      <div className="mb-6">
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-semibold">Dashboard</h2>
          <div className="flex space-x-2">
            <select
              className="border border-gray-300 rounded px-3 py-2"
              value={timeRange}
              onChange={(e) => setTimeRange(e.target.value)}
            >
              <option value="day">Last 24 Hours</option>
              <option value="week">Last Week</option>
              <option value="month">Last Month</option>
              <option value="year">Last Year</option>
            </select>
            <button 
              className="bg-brand-black text-white px-4 py-2 rounded"
              onClick={fetchStatistics}
            >
              Refresh
            </button>
          </div>
        </div>
      </div>
      
      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div className="bg-white p-6 rounded-lg shadow-md">
          <h3 className="text-gray-500 text-sm font-medium mb-2">Total Users</h3>
          <div className="flex items-center">
            <div className="text-2xl font-bold">{stats.users.total}</div>
            <div className={`ml-2 text-sm ${stats.users.growth >= 0 ? 'text-green-600' : 'text-red-600'}`}>
              {stats.users.growth >= 0 ? '+' : ''}{stats.users.growth}%
            </div>
          </div>
        </div>
        
        <div className="bg-white p-6 rounded-lg shadow-md">
          <h3 className="text-gray-500 text-sm font-medium mb-2">Total Businesses</h3>
          <div className="flex items-center">
            <div className="text-2xl font-bold">{stats.businesses.total}</div>
            <div className={`ml-2 text-sm ${stats.businesses.growth >= 0 ? 'text-green-600' : 'text-red-600'}`}>
              {stats.businesses.growth >= 0 ? '+' : ''}{stats.businesses.growth}%
            </div>
          </div>
        </div>
        
        <div className="bg-white p-6 rounded-lg shadow-md">
          <h3 className="text-gray-500 text-sm font-medium mb-2">Total Reviews</h3>
          <div className="flex items-center">
            <div className="text-2xl font-bold">{stats.reviews.total}</div>
            <div className={`ml-2 text-sm ${stats.reviews.growth >= 0 ? 'text-green-600' : 'text-red-600'}`}>
              {stats.reviews.growth >= 0 ? '+' : ''}{stats.reviews.growth}%
            </div>
          </div>
        </div>
        
        <div className="bg-white p-6 rounded-lg shadow-md">
          <h3 className="text-gray-500 text-sm font-medium mb-2">Total Revenue</h3>
          <div className="flex items-center">
            <div className="text-2xl font-bold">${stats.revenue.total}</div>
            <div className={`ml-2 text-sm ${stats.revenue.growth >= 0 ? 'text-green-600' : 'text-red-600'}`}>
              {stats.revenue.growth >= 0 ? '+' : ''}{stats.revenue.growth}%
            </div>
          </div>
        </div>
      </div>
      
      {/* Business Status */}
      <div className="bg-white p-6 rounded-lg shadow-md mb-8">
        <h3 className="font-semibold text-lg mb-4">Business Status</h3>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className="bg-yellow-50 p-4 rounded-lg">
            <h4 className="text-yellow-700 font-medium mb-2">Pending</h4>
            <p className="text-2xl font-bold">{stats.businessStatus.pending}</p>
          </div>
          <div className="bg-green-50 p-4 rounded-lg">
            <h4 className="text-green-700 font-medium mb-2">Approved</h4>
            <p className="text-2xl font-bold">{stats.businessStatus.approved}</p>
          </div>
          <div className="bg-red-50 p-4 rounded-lg">
            <h4 className="text-red-700 font-medium mb-2">Rejected</h4>
            <p className="text-2xl font-bold">{stats.businessStatus.rejected}</p>
          </div>
        </div>
      </div>
      
      {/* Recent Activity */}
      <div className="bg-white p-6 rounded-lg shadow-md">
        <h3 className="font-semibold text-lg mb-4">Recent Activity</h3>
        {stats.recentActivity.length === 0 ? (
          <p className="text-gray-500">No recent activity.</p>
        ) : (
          <div className="space-y-4">
            {stats.recentActivity.map((activity, index) => (
              <div key={index} className="border-b border-gray-200 pb-3 last:border-b-0 last:pb-0">
                <div className="flex justify-between">
                  <p className="font-medium">{activity.description}</p>
                  <p className="text-gray-500 text-sm">{activity.time}</p>
                </div>
                <p className="text-gray-600 text-sm">{activity.user}</p>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}

export default SystemStatistics;