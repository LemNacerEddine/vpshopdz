import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
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
import { Mail, Phone, Loader2, User, MapPin, Eye, EyeOff, Chrome, ArrowLeft, ArrowRight } from 'lucide-react';

const LOGO_URL = "https://customer-assets.emergentagent.com/job_cb33075f-a467-40a3-8ccf-6a7d58e2dd7b/artifacts/9ov58a7g_548325177_122096850867034427_2184721735778021830_n.jpg";

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const RegisterPage = () => {
  const { language, isRTL } = useLanguage();
  const { checkAuth } = useAuth();
  const navigate = useNavigate();
  
  const [activeTab, setActiveTab] = useState('email');
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [wilayas, setWilayas] = useState([]);
  
  const [formData, setFormData] = useState({
    email: '',
    phone: '',
    password: '',
    confirmPassword: '',
    name: '',
    wilaya: '',
    address: ''
  });

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

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Validation
    if (!formData.name.trim()) {
      toast.error(text.nameRequired);
      return;
    }
    
    if (activeTab === 'email' && !formData.email) {
      toast.error(text.emailRequired);
      return;
    }
    
    if (activeTab === 'phone' && !formData.phone) {
      toast.error(text.phoneRequired);
      return;
    }
    
    if (!formData.password || formData.password.length < 6) {
      toast.error(text.passwordMin);
      return;
    }
    
    if (formData.password !== formData.confirmPassword) {
      toast.error(text.passwordMismatch);
      return;
    }
    
    if (!formData.wilaya) {
      toast.error(text.wilayaRequired);
      return;
    }

    try {
      setLoading(true);
      
      const payload = {
        password: formData.password,
        name: formData.name,
        wilaya: formData.wilaya,
        address: formData.address || null
      };
      
      if (activeTab === 'email') {
        payload.email = formData.email;
      } else {
        payload.phone = formData.phone;
      }
      
      await axios.post(`${API}/auth/register`, payload, { withCredentials: true });
      
      await checkAuth();
      toast.success(text.registerSuccess);
      navigate('/', { replace: true });
    } catch (error) {
      const errorMsg = error.response?.data?.detail || text.registerError;
      toast.error(errorMsg);
    } finally {
      setLoading(false);
    }
  };

  const handleGoogleLogin = () => {
    const redirectUrl = window.location.origin + '/auth/callback';
    window.location.href = `https://auth.emergentagent.com/?redirect=${encodeURIComponent(redirectUrl)}`;
  };

  const l = {
    ar: {
      registerTitle: 'إنشاء حساب جديد',
      welcome: 'انضم إلى متجر اقرو يوسفي',
      emailTab: 'البريد الإلكتروني',
      phoneTab: 'رقم الهاتف',
      email: 'البريد الإلكتروني',
      phone: 'رقم الهاتف',
      phonePlaceholder: '0XXX XX XX XX',
      password: 'كلمة السر',
      confirmPassword: 'تأكيد كلمة السر',
      name: 'الاسم الكامل',
      wilaya: 'الولاية',
      selectWilaya: 'اختر الولاية',
      address: 'العنوان (اختياري)',
      register: 'إنشاء الحساب',
      orContinueWith: 'أو سجل باستخدام',
      google: 'Google',
      haveAccount: 'لديك حساب؟',
      login: 'تسجيل الدخول',
      nameRequired: 'الاسم مطلوب',
      emailRequired: 'البريد الإلكتروني مطلوب',
      phoneRequired: 'رقم الهاتف مطلوب',
      passwordMin: 'كلمة السر يجب أن تكون 6 أحرف على الأقل',
      passwordMismatch: 'كلمتا السر غير متطابقتين',
      wilayaRequired: 'الولاية مطلوبة',
      registerSuccess: 'تم إنشاء الحساب بنجاح',
      registerError: 'خطأ في إنشاء الحساب'
    },
    fr: {
      registerTitle: 'Créer un compte',
      welcome: 'Rejoignez AgroYousfi',
      emailTab: 'Email',
      phoneTab: 'Téléphone',
      email: 'Email',
      phone: 'Numéro de téléphone',
      phonePlaceholder: '0XXX XX XX XX',
      password: 'Mot de passe',
      confirmPassword: 'Confirmer le mot de passe',
      name: 'Nom complet',
      wilaya: 'Wilaya',
      selectWilaya: 'Sélectionnez la wilaya',
      address: 'Adresse (optionnel)',
      register: 'Créer le compte',
      orContinueWith: 'Ou inscrivez-vous avec',
      google: 'Google',
      haveAccount: 'Vous avez un compte?',
      login: 'Connexion',
      nameRequired: 'Le nom est requis',
      emailRequired: "L'email est requis",
      phoneRequired: 'Le téléphone est requis',
      passwordMin: 'Le mot de passe doit contenir au moins 6 caractères',
      passwordMismatch: 'Les mots de passe ne correspondent pas',
      wilayaRequired: 'La wilaya est requise',
      registerSuccess: 'Compte créé avec succès',
      registerError: "Erreur lors de la création du compte"
    },
    en: {
      registerTitle: 'Create Account',
      welcome: 'Join AgroYousfi',
      emailTab: 'Email',
      phoneTab: 'Phone',
      email: 'Email',
      phone: 'Phone Number',
      phonePlaceholder: '0XXX XX XX XX',
      password: 'Password',
      confirmPassword: 'Confirm Password',
      name: 'Full Name',
      wilaya: 'Wilaya',
      selectWilaya: 'Select Wilaya',
      address: 'Address (optional)',
      register: 'Create Account',
      orContinueWith: 'Or sign up with',
      google: 'Google',
      haveAccount: 'Already have an account?',
      login: 'Login',
      nameRequired: 'Name is required',
      emailRequired: 'Email is required',
      phoneRequired: 'Phone is required',
      passwordMin: 'Password must be at least 6 characters',
      passwordMismatch: 'Passwords do not match',
      wilayaRequired: 'Wilaya is required',
      registerSuccess: 'Account created successfully',
      registerError: 'Error creating account'
    }
  };

  const text = l[language] || l.ar;

  return (
    <div className="min-h-[80vh] flex items-center justify-center px-4 py-12">
      <div className="w-full max-w-md">
        <div className="bg-card rounded-3xl p-8 shadow-soft border">
          {/* Logo */}
          <div className="text-center mb-6">
            <img 
              src={LOGO_URL} 
              alt="AgroYousfi" 
              className="h-16 w-16 rounded-full mx-auto mb-3 shadow-md"
            />
            <h1 className="text-2xl font-bold text-foreground">{text.registerTitle}</h1>
            <p className="text-muted-foreground mt-1 text-sm">{text.welcome}</p>
          </div>

          {/* Google Register Button */}
          <Button 
            variant="outline" 
            className="w-full rounded-full mb-6"
            onClick={handleGoogleLogin}
          >
            <svg className="h-5 w-5 me-2" viewBox="0 0 24 24">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05" />
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
            </svg>
            {text.google}
          </Button>

          {/* Divider */}
          <div className="relative my-6">
            <div className="absolute inset-0 flex items-center">
              <div className="w-full border-t"></div>
            </div>
            <div className="relative flex justify-center text-xs uppercase">
              <span className="bg-card px-2 text-muted-foreground">{text.orContinueWith}</span>
            </div>
          </div>

          {/* Registration Form */}
          <form onSubmit={handleSubmit}>
            {/* Email/Phone Tabs */}
            <Tabs value={activeTab} onValueChange={setActiveTab} className="mb-4">
              <TabsList className="grid w-full grid-cols-2 rounded-full p-1">
                <TabsTrigger value="email" className="rounded-full">
                  <Mail className="h-4 w-4 me-2" />
                  {text.emailTab}
                </TabsTrigger>
                <TabsTrigger value="phone" className="rounded-full">
                  <Phone className="h-4 w-4 me-2" />
                  {text.phoneTab}
                </TabsTrigger>
              </TabsList>

              <TabsContent value="email" className="mt-4">
                <div className="space-y-2">
                  <Label htmlFor="email">{text.email}</Label>
                  <Input
                    id="email"
                    name="email"
                    type="email"
                    value={formData.email}
                    onChange={handleChange}
                    placeholder="example@email.com"
                    dir="ltr"
                  />
                </div>
              </TabsContent>

              <TabsContent value="phone" className="mt-4">
                <div className="space-y-2">
                  <Label htmlFor="phone">{text.phone}</Label>
                  <Input
                    id="phone"
                    name="phone"
                    type="tel"
                    value={formData.phone}
                    onChange={handleChange}
                    placeholder={text.phonePlaceholder}
                    dir="ltr"
                  />
                </div>
              </TabsContent>
            </Tabs>

            {/* Name */}
            <div className="space-y-2 mb-4">
              <Label htmlFor="name">{text.name} *</Label>
              <div className="relative">
                <User className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'right-3' : 'left-3'} h-4 w-4 text-muted-foreground`} />
                <Input
                  id="name"
                  name="name"
                  value={formData.name}
                  onChange={handleChange}
                  className={isRTL ? 'pr-10' : 'pl-10'}
                  required
                />
              </div>
            </div>

            {/* Password */}
            <div className="space-y-2 mb-4">
              <Label htmlFor="password">{text.password} *</Label>
              <div className="relative">
                <Input
                  id="password"
                  name="password"
                  type={showPassword ? 'text' : 'password'}
                  value={formData.password}
                  onChange={handleChange}
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

            {/* Confirm Password */}
            <div className="space-y-2 mb-4">
              <Label htmlFor="confirmPassword">{text.confirmPassword} *</Label>
              <div className="relative">
                <Input
                  id="confirmPassword"
                  name="confirmPassword"
                  type={showConfirmPassword ? 'text' : 'password'}
                  value={formData.confirmPassword}
                  onChange={handleChange}
                  className={isRTL ? 'pl-10' : 'pr-10'}
                  dir="ltr"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                  className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'left-3' : 'right-3'} text-muted-foreground hover:text-foreground`}
                >
                  {showConfirmPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                </button>
              </div>
            </div>

            {/* Wilaya */}
            <div className="space-y-2 mb-4">
              <Label>{text.wilaya} *</Label>
              <Select value={formData.wilaya} onValueChange={(value) => setFormData(prev => ({ ...prev, wilaya: value }))}>
                <SelectTrigger>
                  <SelectValue placeholder={text.selectWilaya} />
                </SelectTrigger>
                <SelectContent>
                  {wilayas.map((wilaya, idx) => (
                    <SelectItem key={idx} value={wilaya}>{wilaya}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            {/* Address */}
            <div className="space-y-2 mb-6">
              <Label htmlFor="address">{text.address}</Label>
              <div className="relative">
                <MapPin className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'right-3' : 'left-3'} h-4 w-4 text-muted-foreground`} />
                <Input
                  id="address"
                  name="address"
                  value={formData.address}
                  onChange={handleChange}
                  className={isRTL ? 'pr-10' : 'pl-10'}
                />
              </div>
            </div>

            {/* Submit Button */}
            <Button 
              type="submit" 
              className="w-full rounded-full"
              disabled={loading}
            >
              {loading ? (
                <Loader2 className="h-4 w-4 animate-spin" />
              ) : (
                <>
                  {text.register}
                  <ArrowIcon className="h-4 w-4 ms-2" />
                </>
              )}
            </Button>
          </form>

          {/* Login Link */}
          <p className="text-center text-sm text-muted-foreground mt-6">
            {text.haveAccount}{' '}
            <Link to="/login" className="text-primary hover:underline font-medium">
              {text.login}
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
};

export default RegisterPage;
