import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { useStore } from './StoreContext';
import { translations } from '../i18n/translations';

interface LanguageContextType {
  language: string;
  setLanguage: (lang: string) => void;
  t: (key: string) => string;
  isRTL: boolean;
  formatPrice: (price: number) => string;
  dir: 'rtl' | 'ltr';
}

const LanguageContext = createContext<LanguageContextType | null>(null);

export const useLanguage = (): LanguageContextType => {
  const context = useContext(LanguageContext);
  if (!context) {
    throw new Error('useLanguage must be used within a LanguageProvider');
  }
  return context;
};

export const LanguageProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { defaultLanguage, currency, store } = useStore();

  const [language, setLanguageState] = useState(() => {
    const saved = localStorage.getItem(`vpshopdz_lang_${store.slug}`);
    return saved || defaultLanguage || 'ar';
  });

  useEffect(() => {
    localStorage.setItem(`vpshopdz_lang_${store.slug}`, language);
    document.documentElement.dir = language === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.lang = language;
  }, [language, store.slug]);

  const setLanguage = useCallback((lang: string) => {
    if (['ar', 'fr', 'en'].includes(lang)) {
      setLanguageState(lang);
    }
  }, []);

  const t = useCallback((key: string): string => {
    const keys = key.split('.');
    let value: any = translations[language] || translations['ar'];
    for (const k of keys) {
      if (value && typeof value === 'object' && k in value) {
        value = value[k];
      } else {
        return key;
      }
    }
    return typeof value === 'string' ? value : key;
  }, [language]);

  const isRTL = language === 'ar';
  const dir = isRTL ? 'rtl' : 'ltr';

  const formatPrice = useCallback((price: number): string => {
    const formatted = new Intl.NumberFormat(
      language === 'ar' ? 'ar-DZ' : language === 'fr' ? 'fr-DZ' : 'en-DZ'
    ).format(price);

    const currencyLabel = currency === 'DZD'
      ? (language === 'ar' ? 'د.ج' : 'DA')
      : currency;

    return language === 'ar' ? `${formatted} ${currencyLabel}` : `${formatted} ${currencyLabel}`;
  }, [language, currency]);

  const value: LanguageContextType = {
    language,
    setLanguage,
    t,
    isRTL,
    formatPrice,
    dir,
  };

  return (
    <LanguageContext.Provider value={value}>
      {children}
    </LanguageContext.Provider>
  );
};
