import React, { useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from 'sonner';
import { Mail, Loader2, Eye, EyeOff, ArrowLeft, ArrowRight, CheckCircle, KeyRound } from 'lucide-react';

const LOGO_URL = "https://customer-assets.emergentagent.com/job_cb33075f-a467-40a3-8ccf-6a7d58e2dd7b/artifacts/9ov58a7g_548325177_122096850867034427_2184721735778021830_n.jpg";

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const ForgotPasswordPage = () => {
  const { language, isRTL } = useLanguage();
  const { checkAuth } = useAuth();
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  
  const resetToken = searchParams.get('token');
  
  const [email, setEmail] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [emailSent, setEmailSent] = useState(false);
  const [demoToken, setDemoToken] = useState('');

  const ArrowIcon = isRTL ? ArrowLeft : ArrowRight;

  const handleForgotPassword = async (e) => {
    e.preventDefault();
    
    if (!email) {
      toast.error(text.emailRequired);
      return;
    }

    try {
      setLoading(true);
      const response = await axios.post(`${API}/auth/forgot-password`, { email });
      setEmailSent(true);
      setDemoToken(response.data.demo_token || '');
      toast.success(text.emailSentSuccess);
    } catch (error) {
      toast.error(text.errorSending);
    } finally {
      setLoading(false);
    }
  };

  const handleResetPassword = async (e) => {
    e.preventDefault();
    
    if (newPassword.length < 6) {
      toast.error(text.passwordMin);
      return;
    }
    
    if (newPassword !== confirmPassword) {
      toast.error(text.passwordMismatch);
      return;
    }

    try {
      setLoading(true);
      await axios.post(
        `${API}/auth/reset-password`,
        { token: resetToken, new_password: newPassword },
        { withCredentials: true }
      );
      
      await checkAuth();
      toast.success(text.resetSuccess);
      navigate('/', { replace: true });
    } catch (error) {
      const errorMsg = error.response?.data?.detail || text.resetError;
      toast.error(errorMsg);
    } finally {
      setLoading(false);
    }
  };

  const l = {
    ar: {
      forgotTitle: 'نسيت كلمة السر؟',
      forgotDesc: 'أدخل بريدك الإلكتروني وسنرسل لك رابط إعادة التعيين',
      resetTitle: 'إعادة تعيين كلمة السر',
      resetDesc: 'أدخل كلمة السر الجديدة',
      email: 'البريد الإلكتروني',
      newPassword: 'كلمة السر الجديدة',
      confirmPassword: 'تأكيد كلمة السر',
      sendLink: 'إرسال رابط إعادة التعيين',
      resetPassword: 'إعادة تعيين كلمة السر',
      backToLogin: 'العودة لتسجيل الدخول',
      emailSent: 'تم إرسال الرابط!',
      emailSentDesc: 'تحقق من بريدك الإلكتروني للحصول على رابط إعادة تعيين كلمة السر',
      demoTokenLabel: 'رابط التجربة (للتطوير):',
      emailRequired: 'البريد الإلكتروني مطلوب',
      passwordMin: 'كلمة السر يجب أن تكون 6 أحرف على الأقل',
      passwordMismatch: 'كلمتا السر غير متطابقتين',
      emailSentSuccess: 'تم إرسال رابط إعادة التعيين',
      errorSending: 'خطأ في إرسال الرابط',
      resetSuccess: 'تم إعادة تعيين كلمة السر بنجاح',
      resetError: 'خطأ في إعادة التعيين'
    },
    fr: {
      forgotTitle: 'Mot de passe oublié?',
      forgotDesc: 'Entrez votre email et nous vous enverrons un lien de réinitialisation',
      resetTitle: 'Réinitialiser le mot de passe',
      resetDesc: 'Entrez votre nouveau mot de passe',
      email: 'Email',
      newPassword: 'Nouveau mot de passe',
      confirmPassword: 'Confirmer le mot de passe',
      sendLink: 'Envoyer le lien',
      resetPassword: 'Réinitialiser',
      backToLogin: 'Retour à la connexion',
      emailSent: 'Lien envoyé!',
      emailSentDesc: 'Vérifiez votre email pour le lien de réinitialisation',
      demoTokenLabel: 'Lien de démo (développement):',
      emailRequired: "L'email est requis",
      passwordMin: 'Le mot de passe doit contenir au moins 6 caractères',
      passwordMismatch: 'Les mots de passe ne correspondent pas',
      emailSentSuccess: 'Lien de réinitialisation envoyé',
      errorSending: "Erreur lors de l'envoi",
      resetSuccess: 'Mot de passe réinitialisé avec succès',
      resetError: 'Erreur de réinitialisation'
    },
    en: {
      forgotTitle: 'Forgot Password?',
      forgotDesc: "Enter your email and we'll send you a reset link",
      resetTitle: 'Reset Password',
      resetDesc: 'Enter your new password',
      email: 'Email',
      newPassword: 'New Password',
      confirmPassword: 'Confirm Password',
      sendLink: 'Send Reset Link',
      resetPassword: 'Reset Password',
      backToLogin: 'Back to Login',
      emailSent: 'Link Sent!',
      emailSentDesc: 'Check your email for the password reset link',
      demoTokenLabel: 'Demo link (development):',
      emailRequired: 'Email is required',
      passwordMin: 'Password must be at least 6 characters',
      passwordMismatch: 'Passwords do not match',
      emailSentSuccess: 'Reset link sent',
      errorSending: 'Error sending link',
      resetSuccess: 'Password reset successful',
      resetError: 'Reset error'
    }
  };

  const text = l[language] || l.ar;

  // Reset password form (when token is present)
  if (resetToken) {
    return (
      <div className="min-h-[80vh] flex items-center justify-center px-4 py-12">
        <div className="w-full max-w-md">
          <div className="bg-card rounded-3xl p-8 shadow-soft border">
            {/* Logo */}
            <div className="text-center mb-8">
              <div className="h-16 w-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                <KeyRound className="h-8 w-8 text-primary" />
              </div>
              <h1 className="text-2xl font-bold text-foreground">{text.resetTitle}</h1>
              <p className="text-muted-foreground mt-1 text-sm">{text.resetDesc}</p>
            </div>

            {/* Reset Form */}
            <form onSubmit={handleResetPassword} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="newPassword">{text.newPassword}</Label>
                <div className="relative">
                  <Input
                    id="newPassword"
                    type={showPassword ? 'text' : 'password'}
                    value={newPassword}
                    onChange={(e) => setNewPassword(e.target.value)}
                    className={isRTL ? 'pl-10' : 'pr-10'}
                    dir="ltr"
                    required
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

              <div className="space-y-2">
                <Label htmlFor="confirmPassword">{text.confirmPassword}</Label>
                <Input
                  id="confirmPassword"
                  type={showPassword ? 'text' : 'password'}
                  value={confirmPassword}
                  onChange={(e) => setConfirmPassword(e.target.value)}
                  dir="ltr"
                  required
                />
              </div>

              <Button 
                type="submit" 
                className="w-full rounded-full"
                disabled={loading}
              >
                {loading ? (
                  <Loader2 className="h-4 w-4 animate-spin" />
                ) : (
                  <>
                    {text.resetPassword}
                    <ArrowIcon className="h-4 w-4 ms-2" />
                  </>
                )}
              </Button>
            </form>

            <p className="text-center text-sm text-muted-foreground mt-6">
              <Link to="/login" className="text-primary hover:underline">
                {text.backToLogin}
              </Link>
            </p>
          </div>
        </div>
      </div>
    );
  }

  // Email sent confirmation
  if (emailSent) {
    return (
      <div className="min-h-[80vh] flex items-center justify-center px-4 py-12">
        <div className="w-full max-w-md">
          <div className="bg-card rounded-3xl p-8 shadow-soft border text-center">
            <div className="h-16 w-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-4">
              <CheckCircle className="h-8 w-8 text-green-600" />
            </div>
            <h1 className="text-2xl font-bold text-foreground mb-2">{text.emailSent}</h1>
            <p className="text-muted-foreground mb-6">{text.emailSentDesc}</p>
            
            {/* Demo token for testing */}
            {demoToken && (
              <div className="bg-muted/50 rounded-xl p-4 mb-6 text-start">
                <p className="text-xs text-muted-foreground mb-2">{text.demoTokenLabel}</p>
                <Link 
                  to={`/forgot-password?token=${demoToken}`}
                  className="text-sm text-primary hover:underline break-all"
                >
                  /forgot-password?token={demoToken.slice(0, 20)}...
                </Link>
              </div>
            )}

            <Link to="/login">
              <Button variant="outline" className="rounded-full">
                {text.backToLogin}
              </Button>
            </Link>
          </div>
        </div>
      </div>
    );
  }

  // Forgot password form
  return (
    <div className="min-h-[80vh] flex items-center justify-center px-4 py-12">
      <div className="w-full max-w-md">
        <div className="bg-card rounded-3xl p-8 shadow-soft border">
          {/* Logo */}
          <div className="text-center mb-8">
            <img 
              src={LOGO_URL} 
              alt="AgroYousfi" 
              className="h-16 w-16 rounded-full mx-auto mb-4 shadow-md"
            />
            <h1 className="text-2xl font-bold text-foreground">{text.forgotTitle}</h1>
            <p className="text-muted-foreground mt-1 text-sm">{text.forgotDesc}</p>
          </div>

          {/* Forgot Password Form */}
          <form onSubmit={handleForgotPassword} className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="email">{text.email}</Label>
              <div className="relative">
                <Mail className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'right-3' : 'left-3'} h-4 w-4 text-muted-foreground`} />
                <Input
                  id="email"
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="example@email.com"
                  className={isRTL ? 'pr-10' : 'pl-10'}
                  dir="ltr"
                  required
                />
              </div>
            </div>

            <Button 
              type="submit" 
              className="w-full rounded-full"
              disabled={loading}
            >
              {loading ? (
                <Loader2 className="h-4 w-4 animate-spin" />
              ) : (
                <>
                  {text.sendLink}
                  <ArrowIcon className="h-4 w-4 ms-2" />
                </>
              )}
            </Button>
          </form>

          <p className="text-center text-sm text-muted-foreground mt-6">
            <Link to="/login" className="text-primary hover:underline">
              {text.backToLogin}
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
};

export default ForgotPasswordPage;
