import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { api } from '../lib/api';
import { getImageUrl, getProductName } from '../lib/utils';
import {
  Package, Truck, CheckCircle2, Clock, Search, Loader2,
  MapPin, Phone, User
} from 'lucide-react';

const statusSteps = [
  { key: 'pending', icon: Clock, label_ar: 'قيد الانتظار', label_fr: 'En attente', label_en: 'Pending' },
  { key: 'confirmed', icon: CheckCircle2, label_ar: 'مؤكد', label_fr: 'Confirmé', label_en: 'Confirmed' },
  { key: 'processing', icon: Package, label_ar: 'قيد التحضير', label_fr: 'En préparation', label_en: 'Processing' },
  { key: 'shipped', icon: Truck, label_ar: 'تم الشحن', label_fr: 'Expédié', label_en: 'Shipped' },
  { key: 'delivered', icon: CheckCircle2, label_ar: 'تم التوصيل', label_fr: 'Livré', label_en: 'Delivered' },
];

const OrderTrackingPage: React.FC = () => {
  const { orderId: paramOrderId } = useParams<{ orderId: string }>();
  const { apiBase } = useStore();
  const { colors } = useTheme();
  const { t, language, isRTL, formatPrice } = useLanguage();

  const [searchId, setSearchId] = useState(paramOrderId || '');
  const [order, setOrder] = useState<any>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const fetchOrder = async (id: string) => {
    if (!id.trim()) return;
    try {
      setLoading(true);
      setError('');
      const res = await api.get(`${apiBase}/orders/track/${id}`);
      setOrder(res.data?.data || res.data);
    } catch {
      setError(isRTL ? 'لم يتم العثور على الطلب' : 'Order not found');
      setOrder(null);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (paramOrderId) fetchOrder(paramOrderId);
  }, [paramOrderId]);

  const currentStepIndex = order ? statusSteps.findIndex(s => s.key === order.status) : -1;

  return (
    <div className="py-8" style={{ backgroundColor: colors.background }}>
      <div className="container mx-auto px-4 max-w-2xl">
        <h1 className="text-2xl font-bold text-center mb-8" style={{ color: colors.foreground }}>
          {t('checkout.trackOrder')}
        </h1>

        {/* Search */}
        <div className="flex gap-2 mb-8">
          <div className="relative flex-1">
            <Search className={`absolute ${isRTL ? 'right-3' : 'left-3'} top-1/2 -translate-y-1/2 h-4 w-4`} style={{ color: colors.mutedForeground }} />
            <input
              type="text"
              placeholder={isRTL ? 'أدخل رقم الطلب...' : 'Enter order number...'}
              value={searchId}
              onChange={(e) => setSearchId(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && fetchOrder(searchId)}
              className={`${isRTL ? 'pr-10' : 'pl-10'} h-12 w-full rounded-lg border text-sm focus:outline-none`}
              style={{ backgroundColor: colors.card, borderColor: colors.border, color: colors.foreground }}
            />
          </div>
          <button
            onClick={() => fetchOrder(searchId)}
            disabled={loading}
            className="h-12 px-6 rounded-lg text-white font-medium transition-opacity hover:opacity-90"
            style={{ backgroundColor: colors.primary, borderRadius: colors.buttonRadius }}
          >
            {loading ? <Loader2 className="h-5 w-5 animate-spin" /> : t('nav.search')}
          </button>
        </div>

        {error && (
          <div className="text-center py-12">
            <Package className="h-16 w-16 mx-auto mb-4 opacity-20" style={{ color: colors.foreground }} />
            <p className="text-lg" style={{ color: colors.mutedForeground }}>{error}</p>
          </div>
        )}

        {order && (
          <div className="space-y-6">
            {/* Order Info */}
            <div className="p-6 rounded-xl border" style={{ backgroundColor: colors.card, borderColor: colors.border, borderRadius: colors.cardRadius }}>
              <div className="flex items-center justify-between mb-4">
                <h3 className="font-bold" style={{ color: colors.cardForeground }}>
                  {isRTL ? 'طلب رقم' : 'Order'} #{order.order_id || order.id}
                </h3>
                <span className="text-sm" style={{ color: colors.mutedForeground }}>
                  {new Date(order.created_at).toLocaleDateString(language === 'ar' ? 'ar-DZ' : 'fr-FR')}
                </span>
              </div>

              {/* Status Timeline */}
              <div className="relative py-6">
                <div className="flex items-center justify-between relative">
                  {/* Progress line */}
                  <div className="absolute top-5 left-0 right-0 h-0.5" style={{ backgroundColor: colors.border }}>
                    <div
                      className="h-full transition-all duration-500"
                      style={{
                        backgroundColor: colors.primary,
                        width: currentStepIndex >= 0 ? `${(currentStepIndex / (statusSteps.length - 1)) * 100}%` : '0%',
                      }}
                    />
                  </div>

                  {statusSteps.map((step, idx) => {
                    const isActive = idx <= currentStepIndex;
                    const isCurrent = idx === currentStepIndex;
                    return (
                      <div key={step.key} className="relative flex flex-col items-center z-10">
                        <div
                          className={`h-10 w-10 rounded-full flex items-center justify-center transition-colors ${isCurrent ? 'ring-4' : ''}`}
                          style={{
                            backgroundColor: isActive ? colors.primary : colors.muted,
                            color: isActive ? '#fff' : colors.mutedForeground,
                            ringColor: isCurrent ? `${colors.primary}30` : undefined,
                          }}
                        >
                          <step.icon className="h-5 w-5" />
                        </div>
                        <span className="text-[10px] mt-2 font-medium text-center max-w-[60px]" style={{ color: isActive ? colors.primary : colors.mutedForeground }}>
                          {step[`label_${language}` as keyof typeof step] || step.label_en}
                        </span>
                      </div>
                    );
                  })}
                </div>
              </div>

              {/* Customer Info */}
              <div className="grid grid-cols-2 gap-4 pt-4 border-t" style={{ borderColor: colors.border }}>
                <div className="flex items-center gap-2 text-sm">
                  <User className="h-4 w-4" style={{ color: colors.mutedForeground }} />
                  <span style={{ color: colors.cardForeground }}>{order.customer_name}</span>
                </div>
                <div className="flex items-center gap-2 text-sm">
                  <Phone className="h-4 w-4" style={{ color: colors.mutedForeground }} />
                  <span dir="ltr" style={{ color: colors.cardForeground }}>{order.customer_phone}</span>
                </div>
                <div className="flex items-center gap-2 text-sm col-span-2">
                  <MapPin className="h-4 w-4 shrink-0" style={{ color: colors.mutedForeground }} />
                  <span style={{ color: colors.cardForeground }}>{order.shipping_address}</span>
                </div>
              </div>
            </div>

            {/* Order Items */}
            <div className="p-6 rounded-xl border" style={{ backgroundColor: colors.card, borderColor: colors.border, borderRadius: colors.cardRadius }}>
              <h3 className="font-bold mb-4" style={{ color: colors.cardForeground }}>
                {isRTL ? 'المنتجات' : 'Products'}
              </h3>
              <div className="space-y-3">
                {(order.items || []).map((item: any, idx: number) => (
                  <div key={idx} className="flex items-center gap-3 pb-3 border-b last:border-0" style={{ borderColor: colors.border }}>
                    <div className="w-12 h-12 rounded-lg overflow-hidden shrink-0" style={{ backgroundColor: colors.muted }}>
                      {item.image && <img src={getImageUrl(item.image)} alt="" className="w-full h-full object-cover" />}
                    </div>
                    <div className="flex-1">
                      <p className="text-sm font-medium" style={{ color: colors.cardForeground }}>{item.product_name || item.name}</p>
                      <p className="text-xs" style={{ color: colors.mutedForeground }}>x{item.quantity}</p>
                    </div>
                    <span className="text-sm font-bold" style={{ color: colors.cardForeground }}>
                      {formatPrice(item.price * item.quantity)}
                    </span>
                  </div>
                ))}
              </div>

              {/* Totals */}
              <div className="space-y-2 pt-4 mt-4 border-t" style={{ borderColor: colors.border }}>
                <div className="flex justify-between text-sm">
                  <span style={{ color: colors.mutedForeground }}>{t('cart.subtotal')}</span>
                  <span style={{ color: colors.cardForeground }}>{formatPrice(order.subtotal || order.total_amount)}</span>
                </div>
                {order.shipping_cost > 0 && (
                  <div className="flex justify-between text-sm">
                    <span style={{ color: colors.mutedForeground }}>{t('cart.shipping')}</span>
                    <span style={{ color: colors.cardForeground }}>{formatPrice(order.shipping_cost)}</span>
                  </div>
                )}
                <div className="flex justify-between pt-2 border-t" style={{ borderColor: colors.border }}>
                  <span className="font-bold" style={{ color: colors.cardForeground }}>{t('cart.total')}</span>
                  <span className="font-bold text-lg" style={{ color: colors.primary }}>{formatPrice(order.total_amount)}</span>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default OrderTrackingPage;
