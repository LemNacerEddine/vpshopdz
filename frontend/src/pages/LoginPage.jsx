import React, { useState } from 'react';
import { useNavigate, useLocation, Link } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useAuth } from '@/contexts/AuthContext';
import { useCart } from '@/contexts/CartContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'sonner';
import { Mail, Phone, Loader2, Eye, EyeOff, ArrowLeft, ArrowRight } from 'lucide-react';

const LOGO_URL = "https://customer-assets.emergentagent.com/job_cb33075f-a467-40a3-8ccf-6a7d58e2dd7b/artifacts/9ov58a7g_548325177_122096850867034427_2184721735778021830_n.jpg";

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

export const LoginPage = () => {
  const { language, isRTL } = useLanguage();
  const { checkAuth } = useAuth();
  const { syncCartToServer } = useCart();
  const navigate = useNavigate();
  const location = useLocation();

  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);

  const from = location.state?.from?.pathname || '/';
  const ArrowIcon = isRTL ? ArrowLeft : ArrowRight;

  const handleLogin = async (e) => {
    e.preventDefault();

    if (!identifier || !password) {
      toast.error(text.fillAllFields);
      return;
    }

    try {
      setLoading(true);
      const response = await axios.post(
        `${API}/auth/login`,
        { identifier, password },
        { withCredentials: true }
      );

      await checkAuth();
      await syncCartToServer();
      toast.success(text.loginSuccess);

      // Redirect admin to dashboard
      if (response.data.user?.role === 'admin') {
        navigate('/admin', { replace: true });
      } else {
        navigate(from, { replace: true });
      }
    } catch (error) {
      const errorMsg = error.response?.data?.detail || text.loginError;
      toast.error(errorMsg);
    } finally {
      setLoading(false);
    }
  };

  const handleGoogleLogin = async () => {
    try {
      setLoading(true);

      // Get Google OAuth URL from backend
      const response = await axios.get(`${API}/auth/google`);

      // Validate response
      if (!response.data || !response.data.authUrl) {
        console.error('Invalid response:', response.data);
        toast.error(text.googleLoginError || 'فشل تسجيل الدخول عبر Google');
        setLoading(false);
        return;
      }

      const { authUrl } = response.data;

      // Validate authUrl
      if (!authUrl.startsWith('https://accounts.google.com/')) {
        console.error('Invalid authUrl:', authUrl);
        toast.error(text.googleLoginError || 'فشل تسجيل الدخول عبر Google');
        setLoading(false);
        return;
      }

      // Redirect to Google OAuth (instead of popup)
      window.location.href = authUrl;

    } catch (error) {
      console.error('Google Sign In error:', error);
      setLoading(false);
      toast.error(text.googleLoginError || 'فشل تسجيل الدخول عبر Google');
    }
  };

  const l = {
    ar: {
      loginTitle: 'تسجيل الدخول',
      welcome: 'مرحباً بك في متجر اقرو يوسفي',
      identifier: 'البريد الإلكتروني أو رقم الهاتف',
      identifierPlaceholder: 'example@email.com أو 0XXX XX XX XX',
      password: 'كلمة السر',
      login: 'تسجيل الدخول',
      forgotPassword: 'نسيت كلمة السر؟',
      orContinueWith: 'أو تابع باستخدام',
      google: 'Google',
      noAccount: 'ليس لديك حساب؟',
      register: 'إنشاء حساب جديد',
      fillAllFields: 'يرجى ملء جميع الحقول',
      loginSuccess: 'تم تسجيل الدخول بنجاح',
      loginError: 'خطأ في تسجيل الدخول',
      googleLoginError: 'فشل تسجيل الدخول عبر Google'
    },
    fr: {
      loginTitle: 'Connexion',
      welcome: 'Bienvenue chez AgroYousfi',
      identifier: 'Email ou téléphone',
      identifierPlaceholder: 'example@email.com ou 0XXX XX XX XX',
      password: 'Mot de passe',
      login: 'Connexion',
      forgotPassword: 'Mot de passe oublié?',
      orContinueWith: 'Ou continuer avec',
      google: 'Google',
      noAccount: "Vous n'avez pas de compte?",
      register: 'Créer un compte',
      fillAllFields: 'Veuillez remplir tous les champs',
      loginSuccess: 'Connexion réussie',
      loginError: 'Erreur de connexion',
      googleLoginError: 'Échec de la connexion Google'
    },
    en: {
      loginTitle: 'Login',
      welcome: 'Welcome to AgroYousfi',
      identifier: 'Email or Phone',
      identifierPlaceholder: 'example@email.com or 0XXX XX XX XX',
      password: 'Password',
      login: 'Login',
      forgotPassword: 'Forgot password?',
      orContinueWith: 'Or continue with',
      google: 'Google',
      noAccount: "Don't have an account?",
      register: 'Create Account',
      fillAllFields: 'Please fill all fields',
      loginSuccess: 'Login successful',
      loginError: 'Login error',
      googleLoginError: 'Google login failed'
    }
  };

  const text = l[language] || l.ar;

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
            <h1 className="text-2xl font-bold text-foreground">{text.loginTitle}</h1>
            <p className="text-muted-foreground mt-1">{text.welcome}</p>
          </div>

          {/* Login Form */}
          <form onSubmit={handleLogin} className="space-y-4">
            {/* Email/Phone Input */}
            <div className="space-y-2">
              <Label htmlFor="identifier">{text.identifier}</Label>
              <div className="relative">
                <Mail className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'right-3' : 'left-3'} h-4 w-4 text-muted-foreground`} />
                <Input
                  id="identifier"
                  type="text"
                  value={identifier}
                  onChange={(e) => setIdentifier(e.target.value)}
                  placeholder={text.identifierPlaceholder}
                  className={isRTL ? 'pr-10' : 'pl-10'}
                  dir="ltr"
                  required
                  data-testid="login-identifier"
                />
              </div>
            </div>

            {/* Password Input */}
            <div className="space-y-2">
              <div className="flex items-center justify-between">
                <Label htmlFor="password">{text.password}</Label>
                <Link to="/forgot-password" className="text-xs text-primary hover:underline">
                  {text.forgotPassword}
                </Link>
              </div>
              <div className="relative">
                <Input
                  id="password"
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className={isRTL ? 'pl-10' : 'pr-10'}
                  dir="ltr"
                  required
                  data-testid="login-password"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'left-3' : 'right-3'} text-muted-foreground hover:text-foreground`}
                >
                  {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                </button>
              </div>
            </div>

            {/* Submit Button */}
            <Button
              type="submit"
              className="w-full rounded-full"
              disabled={loading}
              data-testid="login-btn"
            >
              {loading ? (
                <Loader2 className="h-4 w-4 animate-spin" />
              ) : (
                <>
                  {text.login}
                  <ArrowIcon className="h-4 w-4 ms-2" />
                </>
              )}
            </Button>
          </form>

          {/* Divider */}
          <div className="relative my-6">
            <div className="absolute inset-0 flex items-center">
              <div className="w-full border-t"></div>
            </div>
            <div className="relative flex justify-center text-xs uppercase">
              <span className="bg-card px-2 text-muted-foreground">
                {text.orContinueWith}
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
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05" />
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
            </svg>
            {text.google}
          </Button>

          {/* Register Link */}
          <p className="text-center text-sm text-muted-foreground mt-6">
            {text.noAccount}{' '}
            <Link to="/register" className="text-primary hover:underline font-medium">
              {text.register}
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
};

export default LoginPage;
