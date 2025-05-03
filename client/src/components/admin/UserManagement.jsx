// /client/src/components/admin/UserManagement.jsx
import React, { useState, useEffect } from 'react';
import axios from 'axios';

function UserManagement() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedUser, setSelectedUser] = useState(null);
  const [showRoleModal, setShowRoleModal] = useState(false);
  const [newRole, setNewRole] = useState('');

  useEffect(() => {
    fetchUsers();
  }, []);

  const fetchUsers = async () => {
    try {
      setLoading(true);
      const response = await axios.get('/api/admin/users', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('mpbh_token')}`
        }
      });
      
      setUsers(response.data.data);
      setLoading(false);
    } catch (error) {
      setError('Failed to load users. Please try again.');
      setLoading(false);
      console.error('Error fetching users:', error);
    }
  };

  const openRoleModal = (user) => {
    setSelectedUser(user);
    setNewRole(user.role);
    setShowRoleModal(true);
  };

  const closeRoleModal = () => {
    setSelectedUser(null);
    setNewRole('');
    setShowRoleModal(false);
  };

  const handleRoleChange = async () => {
    if (!selectedUser) return;
    
    try {
      const response = await axios.put(`/api/admin/users/${selectedUser.id}/role`, {
        role: newRole
      }, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('mpbh_token')}`
        }
      });
      
      // Update the user in the list
      setUsers(users.map(user => 
        user.id === selectedUser.id ? { ...user, role: newRole } : user
      ));
      
      closeRoleModal();
      alert('User role updated successfully!');
    } catch (error) {
      alert('Failed to update user role. Please try again.');
      console.error('Error updating user role:', error);
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
      <h1 className="text-2xl font-bold mb-6">User Management</h1>
      
      <div className="mb-6">
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-semibold">Users</h2>
          <button 
            className="bg-brand-black text-white px-4 py-2 rounded"
            onClick={fetchUsers}
          >
            Refresh
          </button>
        </div>
      </div>
      
      {users.length === 0 ? (
        <div className="text-center py-8">
          <p className="text-gray-500">No users found.</p>
        </div>
      ) : (
        <div className="overflow-x-auto">
          <table className="min-w-full bg-white border border-gray-200">
            <thead>
              <tr>
                <th className="py-3 px-4 bg-gray-100 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Name
                </th>
                <th className="py-3 px-4 bg-gray-100 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Email
                </th>
                <th className="py-3 px-4 bg-gray-100 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Role
                </th>
                <th className="py-3 px-4 bg-gray-100 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Verified
                </th>
                <th className="py-3 px-4 bg-gray-100 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {users.map((user) => (
                <tr key={user.id}>
                  <td className="py-4 px-4 whitespace-nowrap">
                    <div className="font-medium text-gray-900">{user.name}</div>
                  </td>
                  <td className="py-4 px-4 whitespace-nowrap">
                    <div className="text-gray-500">{user.email}</div>
                  </td>
                  <td className="py-4 px-4 whitespace-nowrap">
                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                      user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'
                    }`}>
                      {user.role}
                    </span>
                  </td>
                  <td className="py-4 px-4 whitespace-nowrap">
                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                      user.email_verified_at ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                    }`}>
                      {user.email_verified_at ? 'Yes' : 'No'}
                    </span>
                  </td>
                  <td className="py-4 px-4 whitespace-nowrap text-sm font-medium">
                    <button
                      onClick={() => openRoleModal(user)}
                      className="text-indigo-600 hover:text-indigo-900 mr-3"
                    >
                      Change Role
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
      
      {/* Role Change Modal */}
      {showRoleModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 className="text-lg font-bold mb-4">Change User Role</h3>
            <p className="mb-4">Change role for user: <strong>{selectedUser?.name}</strong></p>
            <div className="mb-4">
              <label className="block text-gray-700 text-sm font-bold mb-2">
                Role
              </label>
              <select
                className="shadow border rounded w-full py-2 px-3 text-gray-700"
                value={newRole}
                onChange={(e) => setNewRole(e.target.value)}
              >
                <option value="user">User</option>
                <option value="admin">Admin</option>
              </select>
            </div>
            <div className="flex justify-end space-x-2">
              <button
                className="px-4 py-2 border border-gray-300 rounded text-gray-700"
                onClick={closeRoleModal}
              >
                Cancel
              </button>
              <button
                className="px-4 py-2 bg-indigo-600 text-white rounded"
                onClick={handleRoleChange}
              >
                Update Role
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default UserManagement;