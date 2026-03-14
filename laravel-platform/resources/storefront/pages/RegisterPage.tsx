import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useCustomerAuth } from '../contexts/CustomerAuthContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { api } from '../lib/api';
import { toast } from 'sonner';
import { Loader2, UserPlus, Eye, EyeOff, Phone, Mail } from 'lucide-react';

const GOOGLE_ICON = (
  <svg className="h-5 w-5" viewBox="0 0 24 24">
    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
  </svg>
);

const RegisterPage: React.FC = () => {
  const { apiBase } = useStore();
  const { register, isAuthenticated } = useCustomerAuth();
  const { colors } = useTheme();
  const { isRTL } = useLanguage();
  const navigate = useNavigate();

  const [activeTab, setActiveTab] = useState<'phone' | 'email'>('phone');
  const [loading, setLoading] = useState(false);
  const [googleLoading, setGoogleLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);

  // Wilaya objects: { id, name_ar, name_fr }
  const [wilayas, setWilayas] = useState<any[]>([]);
  // Communes objects: { id, name_ar }
  const [communes, setCommunes] = useState<any[]>([]);
  // Selected wilaya id (for fetching communes)
  const [selectedWilayaId, setSelectedWilayaId] = useState<number | null>(null);

  const [form, setForm] = useState({
    name: '', phone: '', email: '', password: '', confirmPassword: '',
    wilaya: '', commune: '', address: '',
  });

  useEffect(() => { if (isAuthenticated) navigate('/profile', { replace: true }); }, [isAuthenticated]);

  useEffect(() => {
    api.get(`${apiBase}/shipping/wilayas`)
      .then(r => setWilayas(r.data?.data || r.data || []))
      .catch(() => {});
  }, [apiBase]);

  const handleWilayaChange = (wilayaId: string) => {
    const id = parseInt(wilayaId);
    const found = wilayas.find((w: any) => w.id === id);
    const wilayaName = found ? (found.name_ar || found.name_fr || '') : '';
    setSelectedWilayaId(id || null);
    setForm(f => ({ ...f, wilaya: wilayaName, commune: '' }));
    setCommunes([]);
    if (id) {
      api.get(`${apiBase}/shipping/communes/${id}`)
        .then(r => setCommunes(r.data?.data || r.data || []))
        .catch(() => setCommunes([]));
    }
  };

  const update = (k: string, v: string) => setForm(f => ({ ...f, [k]: v }));

  const handleGoogleLogin = async () => {
    try {
      setGoogleLoading(true);
      const res = await api.get(`${apiBase}/auth/google`);
      const authUrl = res.data?.authUrl || res.data?.url;
      if (!authUrl) throw new Error('No auth URL');
      window.location.href = authUrl;
    } catch {
      toast.error('فشل تسجيل الدخول عبر Google');
      setGoogleLoading(false);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!form.name.trim()) { toast.error('الاسم مطلوب'); return; }
    if (activeTab === 'phone' && !form.phone) { toast.error('رقم الهاتف مطلوب'); return; }
    if (activeTab === 'email' && !form.email) { toast.error('البريد الإلكتروني مطلوب'); return; }
    if (form.password.length < 6) { toast.error('كلمة المرور 6 أحرف على الأقل'); return; }
    if (form.password !== form.confirmPassword) { toast.error('كلمتا المرور غير متطابقتين'); return; }

    const payload: Record<string, any> = {
      name: form.name,
      password: form.password,
      wilaya: form.wilaya || undefined,
      commune: form.commune || undefined,
      address: form.address || undefined,
    };

    if (activeTab === 'phone') {
      payload.phone = form.phone;
      if (form.email) payload.email = form.email;
    } else {
      payload.email = form.email;
      // phone is optional for email registration
      if (form.phone) payload.phone = form.phone;
    }

    setLoading(true);
    try {
      await register(apiBase, payload);
      toast.success('تم إنشاء الحساب بنجاح');
      navigate('/profile', { replace: true });
    } catch (err: any) {
      const msg = err?.response?.data?.message
        || err?.response?.data?.errors
        || 'خطأ في إنشاء الحساب';
      toast.error(typeof msg === 'object' ? Object.values(msg).flat().join(', ') : msg);
    } finally {
      setLoading(false);
    }
  };

  const inputClass = `w-full h-11 px-4 rounded-xl border text-sm focus:outline-none focus:ring-2 transition-colors`;
  const inputStyle = { backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground };

  return (
    <div className="min-h-[70vh] flex items-center justify-center px-4 py-12">
      <div className="w-full max-w-md">
        <div className="rounded-2xl shadow-lg border p-8" style={{ backgroundColor: colors.card, borderColor: colors.border }}>
          <div className="text-center mb-6">
            <div className="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style={{ backgroundColor: colors.primary + '20' }}>
              <UserPlus className="h-8 w-8" style={{ color: colors.primary }} />
            </div>
            <h1 className="text-2xl font-bold" style={{ color: colors.cardForeground }}>إنشاء حساب جديد</h1>
            <p className="text-sm mt-1" style={{ color: colors.mutedForeground }}>سجّل لتتبع طلباتك وإدارة عناوينك</p>
          </div>

          {/* Google Sign-Up */}
          <button
            type="button"
            onClick={handleGoogleLogin}
            disabled={googleLoading}
            className="w-full h-11 rounded-xl border flex items-center justify-center gap-3 font-medium text-sm mb-4 hover:opacity-80 transition-opacity disabled:opacity-50"
            style={{ borderColor: colors.border, color: colors.foreground, backgroundColor: colors.background }}
          >
            {googleLoading ? <Loader2 className="h-4 w-4 animate-spin" /> : GOOGLE_ICON}
            التسجيل عبر Google
          </button>

          <div className="relative flex items-center gap-3 mb-4">
            <div className="flex-1 h-px" style={{ backgroundColor: colors.border }} />
            <span className="text-xs" style={{ color: colors.mutedForeground }}>أو</span>
            <div className="flex-1 h-px" style={{ backgroundColor: colors.border }} />
          </div>

          {/* Tabs */}
          <div className="flex rounded-xl overflow-hidden border mb-5" style={{ borderColor: colors.border }}>
            {[
              { key: 'phone', label: 'رقم الهاتف', icon: <Phone className="h-4 w-4" /> },
              { key: 'email', label: 'البريد الإلكتروني', icon: <Mail className="h-4 w-4" /> },
            ].map(tab => (
              <button
                key={tab.key}
                type="button"
                onClick={() => setActiveTab(tab.key as any)}
                className="flex-1 py-2.5 flex items-center justify-center gap-2 text-sm font-medium transition-colors"
                style={{
                  backgroundColor: activeTab === tab.key ? colors.primary : 'transparent',
                  color: activeTab === tab.key ? '#fff' : colors.mutedForeground,
                }}
              >
                {tab.icon} {tab.label}
              </button>
            ))}
          </div>

          <form onSubmit={handleSubmit} className="space-y-3">
            {/* Name */}
            <div>
              <label className="block text-sm font-medium mb-1" style={{ color: colors.cardForeground }}>الاسم الكامل *</label>
              <input type="text" value={form.name} onChange={e => update('name', e.target.value)} required placeholder="أحمد محمد" className={inputClass} style={inputStyle} />
            </div>

            {/* Phone */}
            {activeTab === 'phone' ? (
              <div>
                <label className="block text-sm font-medium mb-1" style={{ color: colors.cardForeground }}>رقم الهاتف *</label>
                <input type="tel" value={form.phone} onChange={e => update('phone', e.target.value)} required placeholder="0555 00 00 00" dir="ltr" className={inputClass} style={inputStyle} />
              </div>
            ) : (
              <>
                <div>
                  <label className="block text-sm font-medium mb-1" style={{ color: colors.cardForeground }}>البريد الإلكتروني *</label>
                  <input type="email" value={form.email} onChange={e => update('email', e.target.value)} required placeholder="example@email.com" dir="ltr" className={inputClass} style={inputStyle} />
                </div>
                <div>
                  <label className="block text-sm font-medium mb-1" style={{ color: colors.cardForeground }}>رقم الهاتف (اختياري)</label>
                  <input type="tel" value={form.phone} onChange={e => update('phone', e.target.value)} placeholder="0555 00 00 00" dir="ltr" className={inputClass} style={inputStyle} />
                </div>
              </>
            )}

            {/* Password */}
            <div className="relative">
              <label className="block text-sm font-medium mb-1" style={{ color: colors.cardForeground }}>كلمة المرور *</label>
              <input
                type={showPassword ? 'text' : 'password'}
                value={form.password}
                onChange={e => update('password', e.target.value)}
                required placeholder="6 أحرف على الأقل" dir="ltr"
                className={`${inputClass} ${isRTL ? 'pl-10' : 'pr-10'}`}
                style={inputStyle}
              />
              <button type="button" onClick={() => setShowPassword(!showPassword)}
                className={`absolute bottom-3 ${isRTL ? 'left-3' : 'right-3'}`} style={{ color: colors.mutedForeground }}>
                {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
              </button>
            </div>
            <div>
              <label className="block text-sm font-medium mb-1" style={{ color: colors.cardForeground }}>تأكيد كلمة المرور *</label>
              <input type="password" value={form.confirmPassword} onChange={e => update('confirmPassword', e.target.value)} required placeholder="أعد كتابة كلمة المرور" dir="ltr" className={inputClass} style={inputStyle} />
            </div>

            {/* Wilaya */}
            {wilayas.length > 0 && (
              <div>
                <label className="block text-sm font-medium mb-1" style={{ color: colors.cardForeground }}>الولاية</label>
                <select
                  value={selectedWilayaId ?? ''}
                  onChange={e => handleWilayaChange(e.target.value)}
                  className={inputClass}
                  style={inputStyle}
                >
                  <option value="">اختر الولاية</option>
                  {wilayas.map((w: any) => (
                    <option key={w.id} value={w.id}>
                      {w.id} - {w.name_ar}
                    </option>
                  ))}
                </select>
              </div>
            )}

            {/* Commune */}
            {communes.length > 0 && (
              <div>
                <label className="block text-sm font-medium mb-1" style={{ color: colors.cardForeground }}>البلدية</label>
                <select value={form.commune} onChange={e => update('commune', e.target.value)} className={inputClass} style={inputStyle}>
                  <option value="">اختر البلدية</option>
                  {communes.map((c: any) => (
                    <option key={c.id} value={c.name_ar || c.name}>
                      {c.name_ar || c.name}
                    </option>
                  ))}
                </select>
              </div>
            )}

            {/* Address */}
            <div>
              <label className="block text-sm font-medium mb-1" style={{ color: colors.cardForeground }}>العنوان (اختياري)</label>
              <input type="text" value={form.address} onChange={e => update('address', e.target.value)} placeholder="الشارع، الحي..." className={inputClass} style={inputStyle} />
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full h-11 rounded-xl font-medium text-white flex items-center justify-center gap-2 transition-opacity hover:opacity-90 disabled:opacity-50 mt-2"
              style={{ backgroundColor: colors.primary }}
            >
              {loading ? <Loader2 className="h-4 w-4 animate-spin" /> : <><UserPlus className="h-4 w-4" /><span>إنشاء الحساب</span></>}
            </button>
          </form>

          <div className="mt-6 text-center text-sm" style={{ color: colors.mutedForeground }}>
            لديك حساب بالفعل؟{' '}
            <Link to="/login" className="font-semibold hover:underline" style={{ color: colors.primary }}>تسجيل الدخول</Link>
          </div>
        </div>
      </div>
    </div>
  );
};

export default RegisterPage;
