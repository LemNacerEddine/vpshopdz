import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { toast } from 'sonner';
import { Mail, Phone, Loader2, User, MapPin, ArrowLeft, ArrowRight } from 'lucide-react';

const LOGO_URL = "https://customer-assets.emergentagent.com/job_cb33075f-a467-40a3-8ccf-6a7d58e2dd7b/artifacts/9ov58a7g_548325177_122096850867034427_2184721735778021830_n.jpg";

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

export const LoginPage = () => {
  const { t, language, isRTL } = useLanguage();
  const { sendOTP, verifyOTP, checkAuth } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  
  // Email login state
  const [email, setEmail] = useState('');
  const [emailCode, setEmailCode] = useState('');
  const [emailStep, setEmailStep] = useState('email');
  const [emailDemoCode, setEmailDemoCode] = useState('');
  
  // Phone login state
  const [phone, setPhone] = useState('');
  const [phoneCode, setPhoneCode] = useState('');
  const [phoneStep, setPhoneStep] = useState('phone'); // 'phone' | 'code' | 'register'
  const [phoneDemoCode, setPhoneDemoCode] = useState('');
  
  // Registration form
  const [registerData, setRegisterData] = useState({
    name: '',
    wilaya: '',
    address: ''
  });
  
  const [wilayas, setWilayas] = useState([]);
  const [loading, setLoading] = useState(false);
  const [activeTab, setActiveTab] = useState('phone');

  const from = location.state?.from?.pathname || '/';
  const ArrowIcon = isRTL ? ArrowLeft : ArrowRight;

  useEffect(() => {
    fetchWilayas();
  }, []);

  const fetchWilayas = async () => {
    try {
      const response = await axios.get(`${API}/wilayas`);
      setWilayas(response.data);
    } catch (error) {
      console.error('Error fetching wilayas:', error);
    }
  };

  // ==================== EMAIL LOGIN ====================
  const handleSendEmailOTP = async (e) => {
    e.preventDefault();
    if (!email) return;

    try {
      setLoading(true);
      const response = await sendOTP(email);
      setEmailDemoCode(response.demo_code || '');
      setEmailStep('code');
      toast.success(t('auth.codeSent'));
    } catch (error) {
      toast.error(error.response?.data?.detail || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  const handleVerifyEmailOTP = async (e) => {
    e.preventDefault();
    if (!emailCode) return;

    try {
      setLoading(true);
      const result = await verifyOTP(email, emailCode);
      toast.success(t('auth.welcome'));
      
      // Redirect admin to dashboard
      if (result.user?.role === 'admin') {
        navigate('/admin', { replace: true });
      } else {
        navigate(from, { replace: true });
      }
    } catch (error) {
      toast.error(error.response?.data?.detail || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  // ==================== PHONE LOGIN ====================
  const handleSendPhoneOTP = async (e) => {
    e.preventDefault();
    if (!phone) return;

    try {
      setLoading(true);
      const response = await axios.post(`${API}/auth/phone/send-otp`, { phone });
      setPhoneDemoCode(response.data.demo_code || '');
      setPhoneStep('code');
      toast.success(language === 'ar' ? 'تم إرسال رمز التحقق' : 'Verification code sent');
    } catch (error) {
      toast.error(error.response?.data?.detail || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  const handleVerifyPhoneOTP = async (e) => {
    e.preventDefault();
    if (!phoneCode) return;

    try {
      setLoading(true);
      const response = await axios.post(
        `${API}/auth/phone/verify-otp`, 
        { phone, code: phoneCode },
        { withCredentials: true }
      );
      
      if (response.data.status === 'new_user') {
        // New user, need to complete registration
        setPhoneStep('register');
        toast.info(language === 'ar' ? 'أكمل بيانات التسجيل' : 'Complete your registration');
      } else {
        // Existing user, logged in
        await checkAuth();
        toast.success(t('auth.welcome'));
        
        // Redirect admin to dashboard
        if (response.data.user?.role === 'admin') {
          navigate('/admin', { replace: true });
        } else {
          navigate(from, { replace: true });
        }
      }
    } catch (error) {
      toast.error(error.response?.data?.detail || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  const handleCompleteRegistration = async (e) => {
    e.preventDefault();
    if (!registerData.name || !registerData.wilaya) {
      toast.error(language === 'ar' ? 'يرجى ملء الحقول المطلوبة' : 'Please fill required fields');
      return;
    }

    try {
      setLoading(true);
      await axios.post(
        `${API}/auth/phone/register`,
        {
          phone,
          name: registerData.name,
          wilaya: registerData.wilaya,
          address: registerData.address
        },
        { withCredentials: true }
      );
      
      await checkAuth();
      toast.success(t('auth.welcome'));
      navigate(from, { replace: true });
    } catch (error) {
      toast.error(error.response?.data?.detail || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  // ==================== GOOGLE LOGIN ====================
  const handleGoogleLogin = () => {
    // REMINDER: DO NOT HARDCODE THE URL, OR ADD ANY FALLBACKS OR REDIRECT URLS, THIS BREAKS THE AUTH
    const redirectUrl = window.location.origin + '/auth/callback';
    window.location.href = `https://auth.emergentagent.com/?redirect=${encodeURIComponent(redirectUrl)}`;
  };

  const l = {
    ar: {
      loginTitle: 'تسجيل الدخول',
      welcome: 'مرحباً بك في متجر اقرو يوسفي',
      phoneTab: 'رقم الهاتف',
      emailTab: 'البريد الإلكتروني',
      phone: 'رقم الهاتف',
      phonePlaceholder: '0XXX XX XX XX',
      sendCode: 'إرسال رمز التحقق',
      verificationCode: 'رمز التحقق',
      codeSentTo: 'تم إرسال رمز التحقق إلى',
      demoCode: 'رمز التجربة:',
      verify: 'تحقق',
      back: 'رجوع',
      orContinueWith: 'أو تابع باستخدام',
      google: 'Google',
      completeRegistration: 'أكمل بيانات التسجيل',
      name: 'الاسم الكامل',
      wilaya: 'الولاية',
      selectWilaya: 'اختر الولاية',
      address: 'العنوان (اختياري)',
      createAccount: 'إنشاء الحساب',
      email: 'البريد الإلكتروني'
    },
    fr: {
      loginTitle: 'Connexion',
      welcome: 'Bienvenue chez AgroYousfi',
      phoneTab: 'Téléphone',
      emailTab: 'Email',
      phone: 'Numéro de téléphone',
      phonePlaceholder: '0XXX XX XX XX',
      sendCode: 'Envoyer le code',
      verificationCode: 'Code de vérification',
      codeSentTo: 'Code envoyé à',
      demoCode: 'Code de démo:',
      verify: 'Vérifier',
      back: 'Retour',
      orContinueWith: 'Ou continuer avec',
      google: 'Google',
      completeRegistration: 'Complétez votre inscription',
      name: 'Nom complet',
      wilaya: 'Wilaya',
      selectWilaya: 'Sélectionnez la wilaya',
      address: 'Adresse (optionnel)',
      createAccount: 'Créer le compte',
      email: 'Email'
    },
    en: {
      loginTitle: 'Login',
      welcome: 'Welcome to AgroYousfi',
      phoneTab: 'Phone',
      emailTab: 'Email',
      phone: 'Phone Number',
      phonePlaceholder: '0XXX XX XX XX',
      sendCode: 'Send Code',
      verificationCode: 'Verification Code',
      codeSentTo: 'Code sent to',
      demoCode: 'Demo code:',
      verify: 'Verify',
      back: 'Back',
      orContinueWith: 'Or continue with',
      google: 'Google',
      completeRegistration: 'Complete Registration',
      name: 'Full Name',
      wilaya: 'Wilaya',
      selectWilaya: 'Select Wilaya',
      address: 'Address (optional)',
      createAccount: 'Create Account',
      email: 'Email'
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

          {/* Registration Step (when new user) */}
          {phoneStep === 'register' ? (
            <form onSubmit={handleCompleteRegistration} className="space-y-4">
              <div className="text-center mb-6">
                <div className="h-16 w-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-3">
                  <User className="h-8 w-8 text-primary" />
                </div>
                <h2 className="font-semibold text-lg">{text.completeRegistration}</h2>
                <p className="text-sm text-muted-foreground">{phone}</p>
              </div>

              <div className="space-y-2">
                <Label htmlFor="name">{text.name} *</Label>
                <Input
                  id="name"
                  value={registerData.name}
                  onChange={(e) => setRegisterData({ ...registerData, name: e.target.value })}
                  placeholder={text.name}
                  required
                  data-testid="register-name"
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="wilaya">{text.wilaya} *</Label>
                <Select
                  value={registerData.wilaya}
                  onValueChange={(value) => setRegisterData({ ...registerData, wilaya: value })}
                >
                  <SelectTrigger data-testid="register-wilaya">
                    <SelectValue placeholder={text.selectWilaya} />
                  </SelectTrigger>
                  <SelectContent className="max-h-60">
                    {wilayas.map((wilaya, index) => (
                      <SelectItem key={index} value={wilaya}>
                        {wilaya}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <Label htmlFor="address">{text.address}</Label>
                <Input
                  id="address"
                  value={registerData.address}
                  onChange={(e) => setRegisterData({ ...registerData, address: e.target.value })}
                  placeholder={text.address}
                  data-testid="register-address"
                />
              </div>

              <Button 
                type="submit" 
                className="w-full rounded-full"
                disabled={loading}
                data-testid="complete-register-btn"
              >
                {loading ? (
                  <Loader2 className="h-4 w-4 animate-spin" />
                ) : (
                  <>
                    {text.createAccount}
                    <ArrowIcon className="h-4 w-4 ms-2" />
                  </>
                )}
              </Button>

              <Button 
                type="button"
                variant="ghost" 
                className="w-full"
                onClick={() => {
                  setPhoneStep('phone');
                  setPhoneCode('');
                  setPhoneDemoCode('');
                }}
              >
                {text.back}
              </Button>
            </form>
          ) : (
            <>
              {/* Login Tabs */}
              <Tabs value={activeTab} onValueChange={setActiveTab} className="mb-6">
                <TabsList className="grid w-full grid-cols-2 rounded-full p-1">
                  <TabsTrigger value="phone" className="rounded-full">
                    <Phone className="h-4 w-4 me-2" />
                    {text.phoneTab}
                  </TabsTrigger>
                  <TabsTrigger value="email" className="rounded-full">
                    <Mail className="h-4 w-4 me-2" />
                    {text.emailTab}
                  </TabsTrigger>
                </TabsList>

                {/* Phone Login */}
                <TabsContent value="phone" className="mt-6">
                  {phoneStep === 'phone' && (
                    <form onSubmit={handleSendPhoneOTP} className="space-y-4">
                      <div className="space-y-2">
                        <Label htmlFor="phone">{text.phone}</Label>
                        <Input
                          id="phone"
                          type="tel"
                          value={phone}
                          onChange={(e) => setPhone(e.target.value)}
                          placeholder={text.phonePlaceholder}
                          dir="ltr"
                          required
                          data-testid="login-phone"
                        />
                      </div>

                      <Button 
                        type="submit" 
                        className="w-full rounded-full"
                        disabled={loading}
                        data-testid="send-phone-otp-btn"
                      >
                        {loading ? (
                          <Loader2 className="h-4 w-4 animate-spin" />
                        ) : (
                          <>
                            <Phone className="h-4 w-4 me-2" />
                            {text.sendCode}
                          </>
                        )}
                      </Button>
                    </form>
                  )}

                  {phoneStep === 'code' && (
                    <form onSubmit={handleVerifyPhoneOTP} className="space-y-4">
                      <div className="text-center mb-4">
                        <p className="text-sm text-muted-foreground">{text.codeSentTo}</p>
                        <p className="font-medium" dir="ltr">{phone}</p>
                      </div>

                      {phoneDemoCode && (
                        <div className="bg-muted/50 rounded-xl p-3 text-center">
                          <p className="text-xs text-muted-foreground">{text.demoCode}</p>
                          <p className="font-mono text-2xl font-bold text-primary tracking-widest">{phoneDemoCode}</p>
                        </div>
                      )}

                      <div className="space-y-2">
                        <Label htmlFor="phoneCode">{text.verificationCode}</Label>
                        <Input
                          id="phoneCode"
                          type="text"
                          value={phoneCode}
                          onChange={(e) => setPhoneCode(e.target.value)}
                          placeholder="000000"
                          dir="ltr"
                          maxLength={6}
                          className="text-center text-2xl tracking-widest font-mono"
                          required
                          data-testid="login-phone-code"
                        />
                      </div>

                      <Button 
                        type="submit" 
                        className="w-full rounded-full"
                        disabled={loading || phoneCode.length !== 6}
                        data-testid="verify-phone-otp-btn"
                      >
                        {loading ? (
                          <Loader2 className="h-4 w-4 animate-spin" />
                        ) : (
                          text.verify
                        )}
                      </Button>

                      <Button 
                        type="button"
                        variant="ghost" 
                        className="w-full"
                        onClick={() => {
                          setPhoneStep('phone');
                          setPhoneCode('');
                          setPhoneDemoCode('');
                        }}
                      >
                        {text.back}
                      </Button>
                    </form>
                  )}
                </TabsContent>

                {/* Email Login */}
                <TabsContent value="email" className="mt-6">
                  {emailStep === 'email' && (
                    <form onSubmit={handleSendEmailOTP} className="space-y-4">
                      <div className="space-y-2">
                        <Label htmlFor="email">{text.email}</Label>
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
                        data-testid="send-email-otp-btn"
                      >
                        {loading ? (
                          <Loader2 className="h-4 w-4 animate-spin" />
                        ) : (
                          <>
                            <Mail className="h-4 w-4 me-2" />
                            {text.sendCode}
                          </>
                        )}
                      </Button>
                    </form>
                  )}

                  {emailStep === 'code' && (
                    <form onSubmit={handleVerifyEmailOTP} className="space-y-4">
                      <div className="text-center mb-4">
                        <p className="text-sm text-muted-foreground">{text.codeSentTo}</p>
                        <p className="font-medium" dir="ltr">{email}</p>
                      </div>

                      {emailDemoCode && (
                        <div className="bg-muted/50 rounded-xl p-3 text-center">
                          <p className="text-xs text-muted-foreground">{text.demoCode}</p>
                          <p className="font-mono text-2xl font-bold text-primary tracking-widest">{emailDemoCode}</p>
                        </div>
                      )}

                      <div className="space-y-2">
                        <Label htmlFor="emailCode">{text.verificationCode}</Label>
                        <Input
                          id="emailCode"
                          type="text"
                          value={emailCode}
                          onChange={(e) => setEmailCode(e.target.value)}
                          placeholder="000000"
                          dir="ltr"
                          maxLength={6}
                          className="text-center text-2xl tracking-widest font-mono"
                          required
                          data-testid="login-email-code"
                        />
                      </div>

                      <Button 
                        type="submit" 
                        className="w-full rounded-full"
                        disabled={loading || emailCode.length !== 6}
                        data-testid="verify-email-otp-btn"
                      >
                        {loading ? (
                          <Loader2 className="h-4 w-4 animate-spin" />
                        ) : (
                          text.verify
                        )}
                      </Button>

                      <Button 
                        type="button"
                        variant="ghost" 
                        className="w-full"
                        onClick={() => {
                          setEmailStep('email');
                          setEmailCode('');
                          setEmailDemoCode('');
                        }}
                      >
                        {text.back}
                      </Button>
                    </form>
                  )}
                </TabsContent>
              </Tabs>

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
                {text.google}
              </Button>
            </>
          )}
        </div>
      </div>
    </div>
  );
};

export default LoginPage;
