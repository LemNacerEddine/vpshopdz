import React, { createContext, useContext, useMemo } from 'react';

interface StoreTheme {
  slug: string;
  colors: Record<string, string>;
  fonts: Record<string, string>;
  layout: Record<string, any>;
  sections: any[];
}

interface StoreData {
  id: string;
  name: string;
  slug: string;
  logo: string | null;
  description: string;
  currency: string;
  language: string;
  settings: Record<string, any>;
  theme: StoreTheme | null;
}

interface StoreContextType {
  store: StoreData;
  apiBase: string;
  storeName: string;
  storeLogo: string | null;
  storeDescription: string;
  currency: string;
  defaultLanguage: string;
  socialLinks: Record<string, string>;
  contactInfo: Record<string, string>;
  getSetting: (key: string, fallback?: any) => any;
}

const StoreContext = createContext<StoreContextType | null>(null);

export const useStore = (): StoreContextType => {
  const context = useContext(StoreContext);
  if (!context) {
    throw new Error('useStore must be used within a StoreProvider');
  }
  return context;
};

export const StoreProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const storeData: StoreData = useMemo(() => {
    // Get store data injected by Laravel blade
    const data = (window as any).__STORE_DATA__;
    if (data) return data;

    // Fallback for development
    return {
      id: 'dev-store',
      name: 'متجر تجريبي',
      slug: 'demo',
      logo: null,
      description: 'متجر تجريبي للتطوير',
      currency: 'DZD',
      language: 'ar',
      settings: {},
      theme: null,
    };
  }, []);

  const apiBase: string = useMemo(() => {
    return (window as any).__API_BASE__ || `/api/v1/store/${storeData.slug}`;
  }, [storeData.slug]);

  const getSetting = (key: string, fallback: any = null): any => {
    const keys = key.split('.');
    let value: any = storeData.settings;
    for (const k of keys) {
      if (value && typeof value === 'object' && k in value) {
        value = value[k];
      } else {
        return fallback;
      }
    }
    return value ?? fallback;
  };

  const socialLinks = useMemo(() => ({
    facebook: getSetting('social.facebook', ''),
    instagram: getSetting('social.instagram', ''),
    twitter: getSetting('social.twitter', ''),
    tiktok: getSetting('social.tiktok', ''),
    whatsapp: getSetting('social.whatsapp', ''),
  }), [storeData.settings]);

  const contactInfo = useMemo(() => ({
    phone: getSetting('contact.phone', ''),
    email: getSetting('contact.email', ''),
    address: getSetting('contact.address', ''),
    whatsapp: getSetting('contact.whatsapp', ''),
  }), [storeData.settings]);

  const value: StoreContextType = {
    store: storeData,
    apiBase,
    storeName: storeData.name,
    storeLogo: storeData.logo,
    storeDescription: storeData.description,
    currency: storeData.currency,
    defaultLanguage: storeData.language,
    socialLinks,
    contactInfo,
    getSetting,
  };

  return (
    <StoreContext.Provider value={value}>
      {children}
    </StoreContext.Provider>
  );
};
