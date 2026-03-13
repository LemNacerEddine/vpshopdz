import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useCustomerAuth } from '../contexts/CustomerAuthContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { api } from '../lib/api';
import { toast } from 'sonner';
import { Loader2, UserPlus, Eye, EyeOff, Phone, Mail } from 'lucide-react';

const RegisterPage: React.FC = () => {
  const { apiBase } = useStore();
  const { register, isAuthenticated } = useCustomerAuth();
  const { colors } = useTheme();
  const { isRTL } = useLanguage();
  const navigate = useNavigate();

  const [activeTab, setActiveTab] = useState<'phone' | 'email'>('phone');
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [wilayas, setWilayas] = useState<string[]>([]);
  const [communes, setCommunes] = useState<string[]>([]);

  const [form, setForm] = useState({
    name: '', phone: '', email: '', password: '', confirmPassword: '',
    wilaya: '', commune: '', address: '',
  });

  useEffect(() => { if (isAuthenticated) navigate('/profile', { replace: true }); }, [isAuthenticated]);

  useEffect(() => {
    api.get(`${apiBase}/shipping/wilayas`).then(r => setWilayas(r.data?.data || r.data || [])).catch(() => {});
  }, [apiBase]);

  const handleWilayaChange = (val: string) => {
    setForm(f => ({ ...f, wilaya: val, commune: '' }));
    if (val) {
      api.get(`${apiBase}/shipping/communes/${val}`).then(r => setCommunes(r.data?.data || r.data || [])).catch(() => setCommunes([]));
    }
  };

  const update = (k: string, v: string) => setForm(f => ({ ...f, [k]: v }));

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!form.name) { toast.error('الاسم مطلوب'); return; }
    if (activeTab === 'phone' && !form.phone) { toast.error('رقم الهاتف مطلوب'); return; }
    if (activeTab === 'email' && !form.email) { toast.error('البريد الإلكتروني مطلوب'); return; }
    if (form.password.length < 6) { toast.error('كلمة المرور 6 أحرف على الأقل'); return; }
    if (form.password !== form.confirmPassword) { toast.error('كلمتا المرور غير متطابقتين'); return; }

    const payload: Record<string, any> = {
      name: form.name, password: form.password,
      wilaya: form.wilaya || undefined, commune: form.commune || undefined, address: form.address || undefined,
    };
    if (activeTab === 'phone') payload.phone = form.phone;
    else { payload.email = form.email; payload.phone = form.phone || form.email; }

    setLoading(true);
    try {
      await register(apiBase, payload);
      toast.success('تم إنشاء الحساب بنجاح');
      navigate('/profile', { replace: true });
    } catch (err: any) {
      toast.error(err?.response?.data?.message || 'خطأ في إنشاء الحساب');
    } finally {
      setLoading(false);
    }
  };

  const inputClass = `w-full h-11 px-4 rounded-xl border text-sm focus:outline-none focus:ring-2`;
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
              <input type={showPassword ? 'text' : 'password'} value={form.password} onChange={e => update('password', e.target.value)} required placeholder="6 أحرف على الأقل" dir="ltr" className={`${inputClass} ${isRTL ? 'pl-10' : 'pr-10'}`} style={inputStyle} />
              <button type="button" onClick={() => setShowPassword(!showPassword)} className={`absolute bottom-3 ${isRTL ? 'left-3' : 'right-3'}`} style={{ color: colors.mutedForeground }}>
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
                <select value={form.wilaya} onChange={e => handleWilayaChange(e.target.value)} className={inputClass} style={inputStyle}>
                  <option value="">اختر الولاية</option>
                  {wilayas.map((w: any) => (
                    <option key={typeof w === 'object' ? w.id : w} value={typeof w === 'object' ? w.name : w}>
                      {typeof w === 'object' ? `${w.code} - ${w.name}` : w}
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
                    <option key={typeof c === 'object' ? c.id : c} value={typeof c === 'object' ? c.name : c}>
                      {typeof c === 'object' ? c.name : c}
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
