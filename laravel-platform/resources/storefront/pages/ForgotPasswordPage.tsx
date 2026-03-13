import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { api } from '../lib/api';
import { toast } from 'sonner';
import { Loader2, KeyRound, ArrowRight, ArrowLeft } from 'lucide-react';

const ForgotPasswordPage: React.FC = () => {
  const { apiBase } = useStore();
  const { colors } = useTheme();
  const { isRTL } = useLanguage();
  const navigate = useNavigate();

  const [step, setStep] = useState<'request' | 'reset'>('request');
  const [identifier, setIdentifier] = useState('');
  const [code, setCode] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirm, setConfirm] = useState('');
  const [loading, setLoading] = useState(false);

  const BackIcon = isRTL ? ArrowRight : ArrowLeft;

  const handleRequest = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!identifier) { toast.error('يرجى إدخال الهاتف أو البريد الإلكتروني'); return; }
    setLoading(true);
    try {
      const res = await api.post(`${apiBase}/customer/forgot-password`, { identifier });
      toast.success(res.data?.message || 'تم إرسال رمز التحقق');
      // In dev show the code
      if (res.data?.code) toast.info(`رمز التحقق: ${res.data.code}`, { duration: 30000 });
      setStep('reset');
    } catch (err: any) {
      toast.error(err?.response?.data?.message || 'حدث خطأ');
    }
    setLoading(false);
  };

  const handleReset = async (e: React.FormEvent) => {
    e.preventDefault();
    if (newPassword !== confirm) { toast.error('كلمتا المرور غير متطابقتين'); return; }
    if (!code) { toast.error('يرجى إدخال رمز التحقق'); return; }
    setLoading(true);
    try {
      await api.post(`${apiBase}/customer/reset-password`, { identifier, code, password: newPassword });
      toast.success('تم تغيير كلمة المرور بنجاح');
      navigate('/login', { replace: true });
    } catch (err: any) {
      toast.error(err?.response?.data?.message || 'رمز غير صحيح أو منتهي الصلاحية');
    }
    setLoading(false);
  };

  const inputClass = 'w-full h-11 px-4 rounded-xl border text-sm focus:outline-none focus:ring-2';
  const inputStyle = { backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground };

  return (
    <div className="min-h-[70vh] flex items-center justify-center px-4 py-12">
      <div className="w-full max-w-md">
        <div className="rounded-2xl shadow-lg border p-8" style={{ backgroundColor: colors.card, borderColor: colors.border }}>
          <div className="text-center mb-6">
            <div className="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style={{ backgroundColor: colors.primary + '20' }}>
              <KeyRound className="h-8 w-8" style={{ color: colors.primary }} />
            </div>
            <h1 className="text-2xl font-bold" style={{ color: colors.cardForeground }}>
              {step === 'request' ? 'نسيت كلمة المرور؟' : 'إعادة تعيين كلمة المرور'}
            </h1>
            <p className="text-sm mt-1" style={{ color: colors.mutedForeground }}>
              {step === 'request' ? 'أدخل هاتفك أو بريدك الإلكتروني لإرسال رمز التحقق' : 'أدخل الرمز المُرسَل وكلمة المرور الجديدة'}
            </p>
          </div>

          {step === 'request' ? (
            <form onSubmit={handleRequest} className="space-y-4">
              <div>
                <label className="block text-sm font-medium mb-1.5" style={{ color: colors.cardForeground }}>
                  الهاتف أو البريد الإلكتروني
                </label>
                <input
                  type="text"
                  value={identifier}
                  onChange={e => setIdentifier(e.target.value)}
                  placeholder="example@email.com أو 0XXX XX XX XX"
                  required
                  dir="ltr"
                  className={inputClass}
                  style={inputStyle}
                />
              </div>
              <button
                type="submit"
                disabled={loading}
                className="w-full h-11 rounded-xl font-medium text-white flex items-center justify-center gap-2 transition-opacity hover:opacity-90 disabled:opacity-50"
                style={{ backgroundColor: colors.primary }}
              >
                {loading ? <Loader2 className="h-4 w-4 animate-spin" /> : 'إرسال رمز التحقق'}
              </button>
            </form>
          ) : (
            <form onSubmit={handleReset} className="space-y-4">
              <div>
                <label className="block text-sm font-medium mb-1.5" style={{ color: colors.cardForeground }}>رمز التحقق</label>
                <input
                  type="text"
                  value={code}
                  onChange={e => setCode(e.target.value)}
                  placeholder="123456"
                  required
                  dir="ltr"
                  maxLength={6}
                  className={`${inputClass} text-center text-xl tracking-widest font-bold`}
                  style={inputStyle}
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-1.5" style={{ color: colors.cardForeground }}>كلمة المرور الجديدة</label>
                <input
                  type="password"
                  value={newPassword}
                  onChange={e => setNewPassword(e.target.value)}
                  placeholder="••••••••"
                  required
                  dir="ltr"
                  minLength={6}
                  className={inputClass}
                  style={inputStyle}
                />
              </div>
              <div>
                <label className="block text-sm font-medium mb-1.5" style={{ color: colors.cardForeground }}>تأكيد كلمة المرور</label>
                <input
                  type="password"
                  value={confirm}
                  onChange={e => setConfirm(e.target.value)}
                  placeholder="••••••••"
                  required
                  dir="ltr"
                  className={inputClass}
                  style={inputStyle}
                />
              </div>
              <button
                type="submit"
                disabled={loading}
                className="w-full h-11 rounded-xl font-medium text-white flex items-center justify-center gap-2 transition-opacity hover:opacity-90 disabled:opacity-50"
                style={{ backgroundColor: colors.primary }}
              >
                {loading ? <Loader2 className="h-4 w-4 animate-spin" /> : 'تغيير كلمة المرور'}
              </button>
              <button type="button" onClick={() => setStep('request')} className="w-full flex items-center justify-center gap-1 text-sm" style={{ color: colors.mutedForeground }}>
                <BackIcon className="h-3 w-3" /> إرسال رمز جديد
              </button>
            </form>
          )}

          <div className="mt-6 text-center text-sm" style={{ color: colors.mutedForeground }}>
            تذكرت كلمة المرور؟{' '}
            <Link to="/login" className="font-semibold hover:underline" style={{ color: colors.primary }}>
              تسجيل الدخول
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ForgotPasswordPage;
