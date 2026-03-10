import React, { createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';

const StoreSettingsContext = createContext({});
const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

export const StoreSettingsProvider = ({ children }) => {
  const [storeInfo, setStoreInfo] = useState({});
  const [loaded, setLoaded] = useState(false);

  useEffect(() => {
    fetchPublicSettings();
  }, []);

  const fetchPublicSettings = async () => {
    try {
      const res = await axios.get(`${API}/settings/public`);
      setStoreInfo(res.data);
    } catch (error) {
      // Use defaults if settings not available
    } finally {
      setLoaded(true);
    }
  };

  const refreshSettings = () => fetchPublicSettings();

  return (
    <StoreSettingsContext.Provider value={{ storeInfo, loaded, refreshSettings }}>
      {children}
    </StoreSettingsContext.Provider>
  );
};

export const useStoreSettings = () => useContext(StoreSettingsContext);
