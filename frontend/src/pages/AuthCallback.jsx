import React, { useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '@/contexts/AuthContext';
import { useLanguage } from '@/contexts/LanguageContext';
import { Loader2 } from 'lucide-react';

export const AuthCallback = () => {
  const { processGoogleSession } = useAuth();
  const navigate = useNavigate();
  const { language } = useLanguage();
  const hasProcessed = useRef(false);

  useEffect(() => {
    const processAuth = async () => {
      // Prevent double processing in StrictMode
      if (hasProcessed.current) return;
      hasProcessed.current = true;

      const hash = window.location.hash;
      const sessionId = new URLSearchParams(hash.substring(1)).get('session_id');

      if (!sessionId) {
        navigate('/login');
        return;
      }

      try {
        const user = await processGoogleSession(sessionId);
        // Clear the hash from URL
        window.history.replaceState(null, '', window.location.pathname);
        // Redirect admin to dashboard, others to homepage
        if (user?.role === 'admin') {
          navigate('/admin', { replace: true });
        } else {
          navigate('/', { replace: true });
        }
      } catch (error) {
        console.error('Auth error:', error);
        navigate('/login');
      }
    };

    processAuth();
  }, []);

  return (
    <div className="min-h-[80vh] flex flex-col items-center justify-center">
      <Loader2 className="h-12 w-12 animate-spin text-primary mb-4" />
      <p className="text-muted-foreground">
        {language === 'ar' ? 'جاري تسجيل الدخول...' : 'Signing in...'}
      </p>
    </div>
  );
};

export default AuthCallback;
