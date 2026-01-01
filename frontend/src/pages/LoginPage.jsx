import React, { useState, useRef, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { useLanguage } from '../contexts/LanguageContext';
import { useAuth } from '../contexts/AuthContext';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { toast } from 'sonner';
import { Mail, Loader2, Leaf } from 'lucide-react';

const LOGO_URL = "https://customer-assets.emergentagent.com/job_cb33075f-a467-40a3-8ccf-6a7d58e2dd7b/artifacts/9ov58a7g_548325177_122096850867034427_2184721735778021830_n.jpg";

export const LoginPage = () => {
  const { t, language, isRTL } = useLanguage();
  const { sendOTP, verifyOTP } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  
  const [email, setEmail] = useState('');
  const [code, setCode] = useState('');
  const [step, setStep] = useState('email'); // 'email' | 'code'
  const [loading, setLoading] = useState(false);
  const [demoCode, setDemoCode] = useState('');

  const from = location.state?.from?.pathname || '/';

  const handleSendOTP = async (e) => {
    e.preventDefault();
    if (!email) return;

    try {
      setLoading(true);
      const response = await sendOTP(email);
      setDemoCode(response.demo_code || '');
      setStep('code');
      toast.success(t('auth.codeSent'));
    } catch (error) {
      toast.error(error.response?.data?.detail || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  const handleVerifyOTP = async (e) => {
    e.preventDefault();
    if (!code) return;

    try {
      setLoading(true);
      await verifyOTP(email, code);
      toast.success(t('auth.welcome'));
      navigate(from, { replace: true });
    } catch (error) {
      toast.error(error.response?.data?.detail || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  const handleGoogleLogin = () => {
    // REMINDER: DO NOT HARDCODE THE URL, OR ADD ANY FALLBACKS OR REDIRECT URLS, THIS BREAKS THE AUTH
    const redirectUrl = window.location.origin + '/auth/callback';
    window.location.href = `https://auth.emergentagent.com/?redirect=${encodeURIComponent(redirectUrl)}`;
  };

  return (
    <div className="min-h-[80vh] flex items-center justify-center px-4 py-12" data-testid="login-page">
      <div className="w-full max-w-md">
        <div className="bg-card rounded-3xl p-8 shadow-soft border">
          {/* Logo */}
          <div className="text-center mb-8">
            <img 
              src={LOGO_URL} 
              alt="AgroYousfi" 
              className="h-20 w-20 rounded-full mx-auto mb-4 shadow-md"
            />
            <h1 className="text-2xl font-bold text-foreground">{t('auth.login')}</h1>
            <p className="text-muted-foreground mt-1">
              {language === 'ar' ? 'مرحباً بك في متجر اقرو يوسفي' : 'Welcome to AgroYousfi'}
            </p>
          </div>

          {/* Email Step */}
          {step === 'email' && (
            <form onSubmit={handleSendOTP} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="email">{t('auth.email')}</Label>
                <Input
                  id="email"
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="example@email.com"
                  dir="ltr"
                  required
                  data-testid="login-email"
                />
              </div>

              <Button 
                type="submit" 
                className="w-full rounded-full"
                disabled={loading}
                data-testid="send-otp-btn"
              >
                {loading ? (
                  <Loader2 className="h-4 w-4 animate-spin" />
                ) : (
                  <>
                    <Mail className="h-4 w-4 me-2" />
                    {t('auth.sendCode')}
                  </>
                )}
              </Button>
            </form>
          )}

          {/* Code Step */}
          {step === 'code' && (
            <form onSubmit={handleVerifyOTP} className="space-y-4">
              <div className="text-center mb-4">
                <p className="text-sm text-muted-foreground">
                  {language === 'ar' ? 'تم إرسال رمز التحقق إلى' : 'Verification code sent to'}
                </p>
                <p className="font-medium" dir="ltr">{email}</p>
              </div>

              {/* Demo code display (for testing) */}
              {demoCode && (
                <div className="bg-muted/50 rounded-xl p-3 text-center">
                  <p className="text-xs text-muted-foreground">
                    {language === 'ar' ? 'رمز التجربة:' : 'Demo code:'}
                  </p>
                  <p className="font-mono text-2xl font-bold text-primary tracking-widest">{demoCode}</p>
                </div>
              )}

              <div className="space-y-2">
                <Label htmlFor="code">{t('auth.code')}</Label>
                <Input
                  id="code"
                  type="text"
                  value={code}
                  onChange={(e) => setCode(e.target.value)}
                  placeholder="000000"
                  dir="ltr"
                  maxLength={6}
                  className="text-center text-2xl tracking-widest font-mono"
                  required
                  data-testid="login-code"
                />
              </div>

              <Button 
                type="submit" 
                className="w-full rounded-full"
                disabled={loading || code.length !== 6}
                data-testid="verify-otp-btn"
              >
                {loading ? (
                  <Loader2 className="h-4 w-4 animate-spin" />
                ) : (
                  t('auth.verify')
                )}
              </Button>

              <Button 
                type="button"
                variant="ghost" 
                className="w-full"
                onClick={() => {
                  setStep('email');
                  setCode('');
                  setDemoCode('');
                }}
              >
                {t('common.back')}
              </Button>
            </form>
          )}

          {/* Divider */}
          <div className="relative my-6">
            <div className="absolute inset-0 flex items-center">
              <div className="w-full border-t"></div>
            </div>
            <div className="relative flex justify-center text-xs uppercase">
              <span className="bg-card px-2 text-muted-foreground">
                {t('auth.orContinueWith')}
              </span>
            </div>
          </div>

          {/* Google Login */}
          <Button 
            variant="outline" 
            className="w-full rounded-full"
            onClick={handleGoogleLogin}
            data-testid="google-login-btn"
          >
            <svg className="h-5 w-5 me-2" viewBox="0 0 24 24">
              <path
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                fill="#4285F4"
              />
              <path
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                fill="#34A853"
              />
              <path
                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                fill="#FBBC05"
              />
              <path
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                fill="#EA4335"
              />
            </svg>
            {t('auth.google')}
          </Button>
        </div>
      </div>
    </div>
  );
};

export default LoginPage;
