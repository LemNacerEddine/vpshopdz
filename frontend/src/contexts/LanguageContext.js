import React, { createContext, useContext, useState, useEffect } from 'react';
import { translations, getTranslation } from '../i18n/translations';

const LanguageContext = createContext();

export const useLanguage = () => {
  const context = useContext(LanguageContext);
  if (!context) {
    throw new Error('useLanguage must be used within a LanguageProvider');
  }
  return context;
};

export const LanguageProvider = ({ children }) => {
  const [language, setLanguage] = useState(() => {
    const saved = localStorage.getItem('agroyousfi_language');
    return saved || 'ar';
  });

  useEffect(() => {
    localStorage.setItem('agroyousfi_language', language);
    // Set document direction
    document.documentElement.dir = language === 'ar' ? 'rtl' : 'ltr';
    document.documentElement.lang = language;
    // Set font family
    document.body.style.fontFamily = language === 'ar' 
      ? "'Tajawal', sans-serif" 
      : "'Manrope', sans-serif";
  }, [language]);

  const t = (path) => getTranslation(language, path);

  const changeLanguage = (lang) => {
    if (['ar', 'fr', 'en'].includes(lang)) {
      setLanguage(lang);
    }
  };

  const isRTL = language === 'ar';

  const formatPrice = (price) => {
    const formatted = new Intl.NumberFormat(
      language === 'ar' ? 'ar-DZ' : language === 'fr' ? 'fr-DZ' : 'en-DZ'
    ).format(price);
    
    if (language === 'ar') {
      return `${formatted} د.ج`;
    }
    return `${formatted} DA`;
  };

  const value = {
    language,
    setLanguage: changeLanguage,
    t,
    isRTL,
    formatPrice,
    translations: translations[language]
  };

  return (
    <LanguageContext.Provider value={value}>
      {children}
    </LanguageContext.Provider>
  );
};
