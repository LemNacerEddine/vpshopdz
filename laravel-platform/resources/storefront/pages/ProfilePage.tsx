import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useCustomerAuth } from '../contexts/CustomerAuthContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { api } from '../lib/api';
import { toast } from 'sonner';
import { Loader2, Package, User, LogOut, ChevronRight, ChevronLeft, Clock, CheckCircle2, Truck, PackageCheck, XCircle, RotateCcw } from 'lucide-react';

const STATUS_CONFIG: Record<string, { label: string; color: string; bg: string; icon: React.ElementType }> = {
  pending:   { label: 'جديد',        color: '#f59e0b', bg: '#fef3c7', icon: Clock },
  confirmed: { label: 'مؤكد',        color: '#3b82f6', bg: '#dbeafe', icon: CheckCircle2 },
  processing:{ label: 'قيد التجهيز', color: '#8b5cf6', bg: '#ede9fe', icon: RotateCcw },
  shipped:   { label: 'في الطريق',   color: '#f97316', bg: '#ffedd5', icon: Truck },
  delivered: { label: 'تم التسليم',  color: '#10b981', bg: '#d1fae5', icon: PackageCheck },
  cancelled: { label: 'ملغي',        color: '#ef4444', bg: '#fee2e2', icon: XCircle },
};

const ProfilePage: React.FC = () => {
  const { apiBase } = useStore();
  const { customer, isAuthenticated, isLoading, logout, loadProfile, token } = useCustomerAuth();
  const { colors } = useTheme();
  const { isRTL } = useLanguage();
  const navigate = useNavigate();

  const [activeTab, setActiveTab] = useState<'orders' | 'profile'>('orders');
  const [orders, setOrders] = useState<any[]>([]);
  const [ordersLoading, setOrdersLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({ name: '', phone: '', email: '', wilaya: '', commune: '', address: '' });
  const [passwordForm, setPasswordForm] = useState({ current_password: '', new_password: '', new_password_confirmation: '' });
  const ChevronIcon = isRTL ? ChevronLeft : ChevronRight;

  useEffect(() => {
    if (!isLoading && !isAuthenticated) navigate('/login', { replace: true });
  }, [isLoading, isAuthenticated]);

  useEffect(() => {
    if (customer) {
      setForm({
        name: customer.name || '', phone: customer.phone || '',
        email: customer.email || '', wilaya: customer.wilaya || '',
        commune: customer.commune || '', address: customer.address || '',
      });
    }
  }, [customer]);

  useEffect(() => {
    loadProfile(apiBase);
  }, [apiBase]);

  useEffect(() => {
    if (isAuthenticated && activeTab === 'orders') fetchOrders();
  }, [isAuthenticated, activeTab]);

  const fetchOrders = async () => {
    if (!token) return;
    setOrdersLoading(true);
    try {
      const res = await api.get(`${apiBase}/customer/orders`, { headers: { Authorization: `Bearer ${token}` } });
      setOrders(res.data?.data || res.data?.items || []);
    } catch { setOrders([]); }
    setOrdersLoading(false);
  };

  const handleSaveProfile = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!token) return;
    setSaving(true);
    try {
      await api.put(`${apiBase}/customer/profile`, form, { headers: { Authorization: `Bearer ${token}` } });
      await loadProfile(apiBase);
      toast.success('تم تحديث الملف الشخصي');
    } catch (err: any) {
      toast.error(err?.response?.data?.message || 'حدث خطأ');
    }
    setSaving(false);
  };

  const handleChangePassword = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!token) return;
    if (passwordForm.new_password !== passwordForm.new_password_confirmation) { toast.error('كلمتا المرور غير متطابقتين'); return; }
    setSaving(true);
    try {
      await api.put(`${apiBase}/customer/password`, passwordForm, { headers: { Authorization: `Bearer ${token}` } });
      toast.success('تم تغيير كلمة المرور');
      setPasswordForm({ current_password: '', new_password: '', new_password_confirmation: '' });
    } catch (err: any) {
      toast.error(err?.response?.data?.message || 'كلمة المرور الحالية غير صحيحة');
    }
    setSaving(false);
  };

  const handleLogout = async () => {
    await logout(apiBase);
    navigate('/', { replace: true });
    toast.success('تم تسجيل الخروج');
  };

  if (isLoading) return (
    <div className="min-h-[60vh] flex items-center justify-center">
      <Loader2 className="h-8 w-8 animate-spin" style={{ color: colors.primary }} />
    </div>
  );

  const inputClass = 'w-full h-10 px-3 rounded-lg border text-sm focus:outline-none focus:ring-2';
  const inputStyle = { backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground };

  return (
    <div className="container mx-auto px-4 py-8 max-w-3xl">
      {/* Header */}
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-3">
          <div className="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-xl" style={{ backgroundColor: colors.primary }}>
            {customer?.name?.charAt(0) || '?'}
          </div>
          <div>
            <h1 className="font-bold text-lg" style={{ color: colors.foreground }}>{customer?.name}</h1>
            <p className="text-sm" style={{ color: colors.mutedForeground }}>{customer?.phone || customer?.email}</p>
          </div>
        </div>
        <button onClick={handleLogout} className="flex items-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition-colors hover:bg-red-50 hover:text-red-600 hover:border-red-200" style={{ borderColor: colors.border, color: colors.mutedForeground }}>
          <LogOut className="h-4 w-4" />
          <span className="hidden sm:block">تسجيل الخروج</span>
        </button>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-2 gap-4 mb-6">
        <div className="rounded-xl p-4 border" style={{ backgroundColor: colors.card, borderColor: colors.border }}>
          <p className="text-sm" style={{ color: colors.mutedForeground }}>إجمالي الطلبات</p>
          <p className="text-2xl font-black mt-1" style={{ color: colors.foreground }}>{customer?.orders_count || 0}</p>
        </div>
        <div className="rounded-xl p-4 border" style={{ backgroundColor: colors.card, borderColor: colors.border }}>
          <p className="text-sm" style={{ color: colors.mutedForeground }}>إجمالي المشتريات</p>
          <p className="text-2xl font-black mt-1" style={{ color: colors.foreground }}>{parseFloat(customer?.total_spent || '0').toLocaleString()} د.ج</p>
        </div>
      </div>

      {/* Tabs */}
      <div className="flex rounded-xl overflow-hidden border mb-6" style={{ borderColor: colors.border }}>
        {[
          { key: 'orders', label: 'طلباتي', icon: <Package className="h-4 w-4" /> },
          { key: 'profile', label: 'حسابي', icon: <User className="h-4 w-4" /> },
        ].map(tab => (
          <button
            key={tab.key}
            onClick={() => setActiveTab(tab.key as any)}
            className="flex-1 py-2.5 flex items-center justify-center gap-2 text-sm font-medium transition-colors"
            style={{ backgroundColor: activeTab === tab.key ? colors.primary : 'transparent', color: activeTab === tab.key ? '#fff' : colors.mutedForeground }}
          >
            {tab.icon} {tab.label}
          </button>
        ))}
      </div>

      {/* Orders Tab */}
      {activeTab === 'orders' && (
        <div className="space-y-3">
          {ordersLoading ? (
            <div className="text-center py-12"><Loader2 className="h-6 w-6 animate-spin mx-auto" style={{ color: colors.primary }} /></div>
          ) : orders.length === 0 ? (
            <div className="text-center py-16 rounded-2xl border" style={{ backgroundColor: colors.card, borderColor: colors.border }}>
              <Package className="h-12 w-12 mx-auto mb-3 opacity-30" style={{ color: colors.foreground }} />
              <p className="font-medium" style={{ color: colors.foreground }}>لا توجد طلبات بعد</p>
              <Link to="/products" className="mt-4 inline-block px-6 py-2 rounded-xl text-white text-sm" style={{ backgroundColor: colors.primary }}>تسوّق الآن</Link>
            </div>
          ) : (
            orders.map((order: any) => {
              const status = STATUS_CONFIG[order.status] || STATUS_CONFIG.pending;
              const StatusIcon = status.icon;
              return (
                <div key={order.id} className="rounded-xl border p-4" style={{ backgroundColor: colors.card, borderColor: colors.border }}>
                  <div className="flex items-center justify-between mb-3">
                    <div>
                      <span className="font-mono font-bold text-sm" style={{ color: colors.foreground }}>#{order.tracking_number || order.id?.slice(-8)}</span>
                      <span className="text-xs ms-2" style={{ color: colors.mutedForeground }}>{new Date(order.created_at).toLocaleDateString('ar-DZ')}</span>
                    </div>
                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold" style={{ backgroundColor: status.bg, color: status.color }}>
                      <StatusIcon className="h-3 w-3" /> {status.label}
                    </span>
                  </div>
                  <div className="flex items-center justify-between">
                    <div className="flex gap-2">
                      {order.items?.slice(0, 3).map((item: any) => (
                        item.product?.images?.[0] && <img key={item.id} src={item.product.images[0]} className="w-10 h-10 rounded-lg object-cover" alt="" />
                      ))}
                      {order.items?.length > 3 && <div className="w-10 h-10 rounded-lg flex items-center justify-center text-xs font-bold" style={{ backgroundColor: colors.muted, color: colors.mutedForeground }}>+{order.items.length - 3}</div>}
                    </div>
                    <span className="font-bold" style={{ color: colors.foreground }}>{parseFloat(order.total).toLocaleString()} د.ج</span>
                  </div>
                </div>
              );
            })
          )}
        </div>
      )}

      {/* Profile Tab */}
      {activeTab === 'profile' && (
        <div className="space-y-6">
          {/* Profile Form */}
          <div className="rounded-xl border p-5" style={{ backgroundColor: colors.card, borderColor: colors.border }}>
            <h2 className="font-bold mb-4" style={{ color: colors.foreground }}>المعلومات الشخصية</h2>
            <form onSubmit={handleSaveProfile} className="space-y-3">
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium mb-1" style={{ color: colors.mutedForeground }}>الاسم</label>
                  <input type="text" value={form.name} onChange={e => setForm(f => ({...f, name: e.target.value}))} className={inputClass} style={inputStyle} />
                </div>
                <div>
                  <label className="block text-xs font-medium mb-1" style={{ color: colors.mutedForeground }}>الهاتف</label>
                  <input type="tel" value={form.phone} onChange={e => setForm(f => ({...f, phone: e.target.value}))} dir="ltr" className={inputClass} style={inputStyle} />
                </div>
              </div>
              <div>
                <label className="block text-xs font-medium mb-1" style={{ color: colors.mutedForeground }}>البريد الإلكتروني</label>
                <input type="email" value={form.email} onChange={e => setForm(f => ({...f, email: e.target.value}))} dir="ltr" className={inputClass} style={inputStyle} />
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium mb-1" style={{ color: colors.mutedForeground }}>الولاية</label>
                  <input type="text" value={form.wilaya} onChange={e => setForm(f => ({...f, wilaya: e.target.value}))} className={inputClass} style={inputStyle} />
                </div>
                <div>
                  <label className="block text-xs font-medium mb-1" style={{ color: colors.mutedForeground }}>البلدية</label>
                  <input type="text" value={form.commune} onChange={e => setForm(f => ({...f, commune: e.target.value}))} className={inputClass} style={inputStyle} />
                </div>
              </div>
              <div>
                <label className="block text-xs font-medium mb-1" style={{ color: colors.mutedForeground }}>العنوان</label>
                <input type="text" value={form.address} onChange={e => setForm(f => ({...f, address: e.target.value}))} className={inputClass} style={inputStyle} />
              </div>
              <button type="submit" disabled={saving} className="w-full h-10 rounded-lg text-white text-sm font-medium flex items-center justify-center gap-2 disabled:opacity-50" style={{ backgroundColor: colors.primary }}>
                {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : 'حفظ التغييرات'}
              </button>
            </form>
          </div>

          {/* Change Password */}
          <div className="rounded-xl border p-5" style={{ backgroundColor: colors.card, borderColor: colors.border }}>
            <h2 className="font-bold mb-4" style={{ color: colors.foreground }}>تغيير كلمة المرور</h2>
            <form onSubmit={handleChangePassword} className="space-y-3">
              {[
                { key: 'current_password', label: 'كلمة المرور الحالية' },
                { key: 'new_password', label: 'كلمة المرور الجديدة' },
                { key: 'new_password_confirmation', label: 'تأكيد كلمة المرور الجديدة' },
              ].map(field => (
                <div key={field.key}>
                  <label className="block text-xs font-medium mb-1" style={{ color: colors.mutedForeground }}>{field.label}</label>
                  <input type="password" value={(passwordForm as any)[field.key]} onChange={e => setPasswordForm(f => ({...f, [field.key]: e.target.value}))} required dir="ltr" className={inputClass} style={inputStyle} />
                </div>
              ))}
              <button type="submit" disabled={saving} className="w-full h-10 rounded-lg border text-sm font-medium flex items-center justify-center gap-2 disabled:opacity-50 transition-colors hover:opacity-80" style={{ borderColor: colors.primary, color: colors.primary }}>
                {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : 'تغيير كلمة المرور'}
              </button>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default ProfilePage;
