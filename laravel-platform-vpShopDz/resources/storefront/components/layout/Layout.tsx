import React, { useEffect } from 'react';
import { Outlet, useLocation } from 'react-router-dom';
import { Header } from './Header';
import { Footer } from './Footer';
import { WhatsAppButton } from '../WhatsAppButton';
import { useTheme } from '../../contexts/ThemeContext';
import { useLanguage } from '../../contexts/LanguageContext';
import { trackPageView } from '../PixelTracker';

export const Layout: React.FC = () => {
  const { layout, colors } = useTheme();
  const { isRTL } = useLanguage();
  const location = useLocation();

  // Track page views on route change
  useEffect(() => {
    trackPageView();
  }, [location.pathname]);

  return (
    <div
      className="min-h-screen flex flex-col"
      dir={isRTL ? 'rtl' : 'ltr'}
      style={{
        backgroundColor: colors.background,
        color: colors.foreground,
        fontFamily: 'var(--font-body)',
      }}
    >
      <Header style={layout.headerStyle} />
      <main className="flex-1">
        <Outlet />
      </main>
      <Footer style={layout.footerStyle} />
      <WhatsAppButton />
    </div>
  );
};

export default Layout;
