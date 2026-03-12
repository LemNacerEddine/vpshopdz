import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { api } from '../lib/api';

interface CustomerAddress {
  id: string;
  label: string | null;
  full_name: string;
  phone: string;
  wilaya: string;
  commune: string | null;
  address_line: string;
  is_default: boolean;
}

interface CustomerData {
  id: string;
  name: string;
  email: string | null;
  phone: string;
  wilaya: string | null;
  commune: string | null;
  address: string | null;
  orders_count: number;
  total_spent: string;
  addresses?: CustomerAddress[];
}

interface CustomerAuthContextType {
  customer: CustomerData | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  token: string | null;
  login: (apiBase: string, identifier: string, password: string) => Promise<void>;
  register: (apiBase: string, data: Record<string, any>) => Promise<void>;
  logout: (apiBase: string) => Promise<void>;
  loadProfile: (apiBase: string) => Promise<void>;
}

const CustomerAuthContext = createContext<CustomerAuthContextType | null>(null);

const TOKEN_KEY = 'customer_auth_token';

export const useCustomerAuth = (): CustomerAuthContextType => {
  const ctx = useContext(CustomerAuthContext);
  if (!ctx) throw new Error('useCustomerAuth must be used within CustomerAuthProvider');
  return ctx;
};

export const CustomerAuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [customer, setCustomer] = useState<CustomerData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [token, setToken] = useState<string | null>(() => localStorage.getItem(TOKEN_KEY));

  const authHeaders = (t: string) => ({ Authorization: `Bearer ${t}` });

  const loadProfile = useCallback(async (apiBase: string) => {
    const t = localStorage.getItem(TOKEN_KEY);
    if (!t) { setCustomer(null); setIsLoading(false); return; }
    try {
      const res = await api.get(`${apiBase}/customer/profile`, { headers: authHeaders(t) });
      setCustomer(res.data.data || res.data);
    } catch {
      localStorage.removeItem(TOKEN_KEY);
      setToken(null);
      setCustomer(null);
    } finally {
      setIsLoading(false);
    }
  }, []);

  const login = async (apiBase: string, identifier: string, password: string) => {
    const res = await api.post(`${apiBase}/customer/login`, { identifier, password });
    const { token: newToken, customer: c } = res.data;
    localStorage.setItem(TOKEN_KEY, newToken);
    setToken(newToken);
    setCustomer(c);
  };

  const register = async (apiBase: string, data: Record<string, any>) => {
    const res = await api.post(`${apiBase}/customer/register`, data);
    const { token: newToken, customer: c } = res.data;
    localStorage.setItem(TOKEN_KEY, newToken);
    setToken(newToken);
    setCustomer(c);
  };

  const logout = async (apiBase: string) => {
    const t = localStorage.getItem(TOKEN_KEY);
    if (t) {
      try { await api.post(`${apiBase}/customer/logout`, {}, { headers: authHeaders(t) }); } catch { /* ignore */ }
    }
    localStorage.removeItem(TOKEN_KEY);
    setToken(null);
    setCustomer(null);
  };

  return (
    <CustomerAuthContext.Provider value={{ customer, isAuthenticated: !!customer, isLoading, token, login, register, logout, loadProfile }}>
      {children}
    </CustomerAuthContext.Provider>
  );
};
