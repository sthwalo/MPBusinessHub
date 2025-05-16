// client/src/pages/SessionManagement.jsx
import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';

const SessionManagement = () => {
  const [sessions, setSessions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const navigate = useNavigate();

  const fetchSessions = async () => {
    try {
      setLoading(true);
      // Get the auth token from localStorage
      const token = localStorage.getItem('mpbh_token');
      if (!token) {
        navigate('/login');
        return;
      }
      
      const response = await axios.get('/api/sessions', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });
      setSessions(response.data.sessions);
      setError(null);
    } catch (err) {
      setError('Failed to load sessions. Please try again.');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchSessions();
  }, []);

  const revokeSession = async (sessionId) => {
    try {
      // Get the auth token from localStorage
      const token = localStorage.getItem('mpbh_token');
      if (!token) {
        navigate('/login');
        return;
      }
      
      await axios.delete(`/api/sessions/${sessionId}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });
      fetchSessions();
    } catch (err) {
      setError('Failed to revoke session. Please try again.');
      console.error(err);
    }
  };

  const revokeAllSessions = async () => {
    try {
      // Get the auth token from localStorage
      const token = localStorage.getItem('mpbh_token');
      if (!token) {
        navigate('/login');
        return;
      }
      
      await axios.delete('/api/sessions', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      });
      fetchSessions();
    } catch (err) {
      setError('Failed to revoke all sessions. Please try again.');
      console.error(err);
    }
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-2xl font-bold mb-6">Manage Your Sessions</h1>
      
      {error && (
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          {error}
        </div>
      )}
      
      <div className="mb-4">
        <button 
          onClick={revokeAllSessions}
          className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
        >
          Sign Out From All Other Devices
        </button>
      </div>
      
      {loading ? (
        <p>Loading sessions...</p>
      ) : (
        <div className="bg-white shadow-md rounded my-6">
          <table className="min-w-full">
            <thead>
              <tr className="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                <th className="py-3 px-6 text-left">Device</th>
                <th className="py-3 px-6 text-left">IP Address</th>
                <th className="py-3 px-6 text-left">Last Active</th>
                <th className="py-3 px-6 text-left">Created</th>
                <th className="py-3 px-6 text-left">Expires</th>
                <th className="py-3 px-6 text-center">Actions</th>
              </tr>
            </thead>
            <tbody className="text-gray-600 text-sm">
              {sessions.map((session) => (
                <tr key={session.id} className="border-b border-gray-200 hover:bg-gray-100">
                  <td className="py-3 px-6 text-left">
                    {session.device}
                    {session.is_current_device && (
                      <span className="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                        Current
                      </span>
                    )}
                  </td>
                  <td className="py-3 px-6 text-left">{session.ip_address}</td>
                  <td className="py-3 px-6 text-left">{session.last_active}</td>
                  <td className="py-3 px-6 text-left">{session.created_at}</td>
                  <td className="py-3 px-6 text-left">{session.expires_at}</td>
                  <td className="py-3 px-6 text-center">
                    {!session.is_current_device && (
                      <button
                        onClick={() => revokeSession(session.id)}
                        className="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-xs"
                      >
                        Sign Out
                      </button>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};

export default SessionManagement;