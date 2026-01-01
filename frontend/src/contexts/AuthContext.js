import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';

const AuthContext = createContext();

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    try {
      const response = await axios.get(`${API}/auth/me`, {
        withCredentials: true
      });
      setUser(response.data);
    } catch (error) {
      setUser(null);
    } finally {
      setLoading(false);
    }
  };

  const sendOTP = async (email) => {
    const response = await axios.post(`${API}/auth/send-otp`, { email });
    return response.data;
  };

  const verifyOTP = async (email, code) => {
    const response = await axios.post(
      `${API}/auth/verify-otp`,
      { email, code },
      { withCredentials: true }
    );
    setUser(response.data.user);
    return response.data;
  };

  const processGoogleSession = async (sessionId) => {
    const response = await axios.post(
      `${API}/auth/session`,
      { session_id: sessionId },
      { withCredentials: true }
    );
    setUser(response.data.user);
    return response.data;
  };

  const logout = async () => {
    try {
      await axios.post(`${API}/auth/logout`, {}, { withCredentials: true });
    } catch (error) {
      console.error('Logout error:', error);
    }
    setUser(null);
  };

  const updateProfile = async (data) => {
    const response = await axios.put(`${API}/auth/profile`, data, {
      withCredentials: true
    });
    setUser(response.data);
    return response.data;
  };

  const isAdmin = user?.role === 'admin';

  const value = {
    user,
    loading,
    sendOTP,
    verifyOTP,
    processGoogleSession,
    logout,
    updateProfile,
    checkAuth,
    isAdmin
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};
