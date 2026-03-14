import React, { useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useCustomerAuth } from '../contexts/CustomerAuthContext';
import { useTheme } from '../contexts/ThemeContext';
import { toast } from 'sonner';
import { Loader2 } from 'lucide-react';

/**
 * Handles the Google OAuth redirect callback.
 * Google redirects to: /auth/callback#session_id=<token>
 */
const AuthCallbackPage: React.FC = () => {
  const { apiBase } = useStore();
  const { loginWithGoogleSession } = useCustomerAuth();
  const { colors } = useTheme();
  const navigate = useNavigate();
  const processed = useRef(false);

  useEffect(() => {
    if (processed.current) return;
    processed.current = true;

    const hash = window.location.hash; // #session_id=xxx
    const params = new URLSearchParams(hash.startsWith('#') ? hash.slice(1) : '');
    const sessionId = params.get('session_id');

    if (!sessionId) {
      toast.error('رابط التحقق غير صالح');
      navigate('/login', { replace: true });
      return;
    }

    loginWithGoogleSession(apiBase, sessionId)
      .then(() => {
        // Clear hash from URL
        window.history.replaceState(null, '', window.location.pathname);
        toast.success('تم تسجيل الدخول بنجاح');
        navigate('/', { replace: true });
      })
      .catch(() => {
        toast.error('فشل تسجيل الدخول عبر Google');
        navigate('/login', { replace: true });
      });
  }, []);

  return (
    <div className="min-h-[60vh] flex flex-col items-center justify-center gap-4">
      <Loader2 className="h-10 w-10 animate-spin" style={{ color: colors.primary }} />
      <p className="text-sm" style={{ color: colors.mutedForeground }}>جاري تسجيل الدخول...</p>
    </div>
  );
};

export default AuthCallbackPage;
