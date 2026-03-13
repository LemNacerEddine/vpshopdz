import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useCustomerAuth } from '../contexts/CustomerAuthContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { toast } from 'sonner';
import { Loader2, Mail, Phone, Eye, EyeOff, LogIn } from 'lucide-react';

const LoginPage: React.FC = () => {
  const { apiBase } = useStore();
  const { login, isAuthenticated } = useCustomerAuth();
  const { colors } = useTheme();
  const { t, isRTL } = useLanguage();
  const navigate = useNavigate();
  const location = useLocation();

  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);

  const from = (location.state as any)?.from?.pathname || '/profile';

  useEffect(() => {
    if (isAuthenticated) navigate(from, { replace: true });
  }, [isAuthenticated]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!identifier || !password) { toast.error('يرجى ملء جميع الحقول'); return; }
    setLoading(true);
    try {
      await login(apiBase, identifier, password);
      toast.success('تم تسجيل الدخول بنجاح');
      navigate(from, { replace: true });
    } catch (err: any) {
      toast.error(err?.response?.data?.message || 'بيانات الدخول غير صحيحة');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-[70vh] flex items-center justify-center px-4 py-12">
      <div className="w-full max-w-md">
        <div className="rounded-2xl shadow-lg border p-8" style={{ backgroundColor: colors.card, borderColor: colors.border }}>
          <div className="text-center mb-6">
            <div className="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style={{ backgroundColor: colors.primary + '20' }}>
              <LogIn className="h-8 w-8" style={{ color: colors.primary }} />
            </div>
            <h1 className="text-2xl font-bold" style={{ color: colors.cardForeground }}>تسجيل الدخول</h1>
            <p className="text-sm mt-1" style={{ color: colors.mutedForeground }}>مرحباً بك، قم بتسجيل الدخول لمتابعة طلباتك</p>
          </div>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium mb-1.5" style={{ color: colors.cardForeground }}>
                البريد الإلكتروني أو رقم الهاتف
              </label>
              <div className="relative">
                <Mail className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'right-3' : 'left-3'} h-4 w-4`} style={{ color: colors.mutedForeground }} />
                <input
                  type="text"
                  value={identifier}
                  onChange={e => setIdentifier(e.target.value)}
                  placeholder="example@email.com أو 0XXX XX XX XX"
                  required
                  dir="ltr"
                  className={`w-full h-11 ${isRTL ? 'pr-10 pl-4' : 'pl-10 pr-4'} rounded-xl border text-sm focus:outline-none focus:ring-2`}
                  style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                />
              </div>
            </div>

            <div>
              <div className="flex items-center justify-between mb-1.5">
                <label className="block text-sm font-medium" style={{ color: colors.cardForeground }}>كلمة المرور</label>
              </div>
              <div className="relative">
                <input
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={e => setPassword(e.target.value)}
                  placeholder="••••••••"
                  required
                  dir="ltr"
                  className={`w-full h-11 ${isRTL ? 'pr-4 pl-10' : 'pl-4 pr-10'} rounded-xl border text-sm focus:outline-none focus:ring-2`}
                  style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'left-3' : 'right-3'}`}
                  style={{ color: colors.mutedForeground }}
                >
                  {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                </button>
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full h-11 rounded-xl font-medium text-white flex items-center justify-center gap-2 transition-opacity hover:opacity-90 disabled:opacity-50"
              style={{ backgroundColor: colors.primary }}
            >
              {loading ? <Loader2 className="h-4 w-4 animate-spin" /> : <><LogIn className="h-4 w-4" /><span>تسجيل الدخول</span></>}
            </button>
          </form>

          <div className="mt-6 text-center text-sm" style={{ color: colors.mutedForeground }}>
            ليس لديك حساب؟{' '}
            <Link to="/register" className="font-semibold hover:underline" style={{ color: colors.primary }}>
              إنشاء حساب جديد
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};

export default LoginPage;
