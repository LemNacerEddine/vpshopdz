import React, { createContext, useContext, useState, useEffect, useCallback, useMemo } from 'react';
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
  const [user, setUser] = useState(() => {
    // Try to restore user from localStorage on initial load
    try {
      const savedUser = localStorage.getItem('agroyousfi_user');
      return savedUser ? JSON.parse(savedUser) : null;
    } catch {
      return null;
    }
  });
  const [loading, setLoading] = useState(true);

  // Memoize user_id to prevent unnecessary re-renders
  const userId = useMemo(() => user?.user_id, [user?.user_id]);

  useEffect(() => {
    checkAuth();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Persist user to localStorage
  useEffect(() => {
    if (user) {
      localStorage.setItem('agroyousfi_user', JSON.stringify(user));
    } else {
      localStorage.removeItem('agroyousfi_user');
    }
  }, [user]);

  const checkAuth = useCallback(async () => {
    try {
      const response = await axios.get(`${API}/auth/me`, {
        withCredentials: true
      });
      // response.data is the user object directly from /api/auth/me
      setUser(response.data);
    } catch (error) {
      // If 401, clear stored user
      if (error.response?.status === 401) {
        setUser(null);
      }
      // Keep existing user if network error (offline support)
    } finally {
      setLoading(false);
    }
  }, []);

  const sendOTP = useCallback(async (email) => {
    const response = await axios.post(`${API}/auth/send-otp`, { email });
    return response.data;
  }, []);

  const verifyOTP = useCallback(async (email, code) => {
    const response = await axios.post(
      `${API}/auth/verify-otp`,
      { email, code },
      { withCredentials: true }
    );
    setUser(response.data.user);
    return response.data;
  }, []);

  const processGoogleSession = useCallback(async (sessionId) => {
    const response = await axios.post(
      `${API}/auth/session`,
      { session_id: sessionId },
      { withCredentials: true }
    );
    setUser(response.data.user);
    return response.data.user;  // Return user for redirect decision
  }, []);

  const logout = useCallback(async () => {
    try {
      await axios.post(`${API}/auth/logout`, {}, { withCredentials: true });
    } catch (error) {
      console.error('Logout error:', error);
    }
    setUser(null);
  }, []);

  const updateProfile = useCallback(async (data) => {
    const response = await axios.put(`${API}/auth/profile`, data, {
      withCredentials: true
    });
    setUser(response.data);
    return response.data;
  }, []);

  const isAdmin = user?.role === 'admin';

  const value = useMemo(() => ({
    user,
    userId,
    loading,
    sendOTP,
    verifyOTP,
    processGoogleSession,
    logout,
    updateProfile,
    checkAuth,
    isAdmin
  }), [user, userId, loading, logout, checkAuth, isAdmin]);

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};
