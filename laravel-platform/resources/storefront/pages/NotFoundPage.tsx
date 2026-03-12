import React from 'react';
import { Link } from 'react-router-dom';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { Home, ArrowRight, ArrowLeft } from 'lucide-react';

const NotFoundPage: React.FC = () => {
  const { colors } = useTheme();
  const { isRTL } = useLanguage();
  const Arrow = isRTL ? ArrowLeft : ArrowRight;

  return (
    <div className="min-h-[60vh] flex items-center justify-center" style={{ backgroundColor: colors.background }}>
      <div className="text-center px-4">
        <h1 className="text-8xl font-bold mb-4" style={{ color: colors.primary }}>404</h1>
        <h2 className="text-2xl font-bold mb-2" style={{ color: colors.foreground }}>
          {isRTL ? 'الصفحة غير موجودة' : 'Page Not Found'}
        </h2>
        <p className="text-base mb-8 max-w-md mx-auto" style={{ color: colors.mutedForeground }}>
          {isRTL
            ? 'عذراً، الصفحة التي تبحث عنها غير موجودة أو تم نقلها.'
            : 'Sorry, the page you are looking for does not exist or has been moved.'}
        </p>
        <Link
          to="/"
          className="inline-flex items-center gap-2 px-6 py-3 text-white font-medium transition-opacity hover:opacity-90"
          style={{ backgroundColor: colors.primary, borderRadius: colors.buttonRadius }}
        >
          <Home className="h-4 w-4" />
          {isRTL ? 'العودة للرئيسية' : 'Go Home'}
          <Arrow className="h-4 w-4" />
        </Link>
      </div>
    </div>
  );
};

export default NotFoundPage;
