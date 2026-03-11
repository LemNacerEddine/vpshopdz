import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { useCart } from '../contexts/CartContext';
import { api } from '../lib/api';
import { getImageUrl, getProductName } from '../lib/utils';
import { toast } from 'sonner';
import {
  Truck, CheckCircle2, Package, Loader2, MapPin, Home, Building2, Tag
} from 'lucide-react';

const CheckoutPage: React.FC = () => {
  const { apiBase } = useStore();
  const { colors } = useTheme();
  const { t, language, isRTL, formatPrice } = useLanguage();
  const { items, cartTotal, clearCart, browserId } = useCart();
  const navigate = useNavigate();

  const [wilayas, setWilayas] = useState<any[]>([]);
  const [communes, setCommunes] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);
  const [orderSuccess, setOrderSuccess] = useState(false);
  const [orderId, setOrderId] = useState('');

  // Shipping
  const [shippingType, setShippingType] = useState('home');
  const [shippingOptions, setShippingOptions] = useState<any[]>([]);
  const [selectedShipping, setSelectedShipping] = useState<any>(null);
  const [loadingShipping, setLoadingShipping] = useState(false);

  // Coupon
  const [couponCode, setCouponCode] = useState('');
  const [couponDiscount, setCouponDiscount] = useState(0);
  const [couponApplied, setCouponApplied] = useState(false);

  const [formData, setFormData] = useState({
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    shipping_address: '',
    wilaya: '',
    commune: '',
    notes: '',
  });

  // Fetch wilayas on mount
  useEffect(() => {
    const fetchWilayas = async () => {
      try {
        const res = await api.get(`${apiBase}/shipping/wilayas`);
        setWilayas(res.data?.data || res.data || []);
      } catch { /* ignore */ }
    };
    fetchWilayas();
  }, [apiBase]);

  // Fetch communes when wilaya changes
  useEffect(() => {
    if (!formData.wilaya) { setCommunes([]); return; }
    const fetchCommunes = async () => {
      try {
        const res = await api.get(`${apiBase}/shipping/communes/${formData.wilaya}`);
        setCommunes(res.data?.data || res.data || []);
      } catch { /* ignore */ }
    };
    fetchCommunes();
  }, [formData.wilaya, apiBase]);

  // Fetch shipping options when wilaya/commune/type changes
  useEffect(() => {
    if (!formData.wilaya) { setShippingOptions([]); return; }
    const fetchShipping = async () => {
      try {
        setLoadingShipping(true);
        const totalWeight = items.reduce((sum: number, item: any) => sum + (item.product?.weight || 0.5) * item.quantity, 0);
        const res = await api.post(`${apiBase}/shipping/calculate`, {
          wilaya_code: formData.wilaya,
          commune_id: formData.commune || null,
          delivery_type: shippingType,
          total_weight: totalWeight,
          order_total: cartTotal,
        });
        setShippingOptions(res.data?.options || res.data || []);
        setSelectedShipping(null);
      } catch {
        setShippingOptions([]);
      } finally {
        setLoadingShipping(false);
      }
    };
    fetchShipping();
  }, [formData.wilaya, formData.commune, shippingType, apiBase, cartTotal, items]);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
    if (name === 'wilaya') {
      setFormData(prev => ({ ...prev, commune: '' }));
    }
  };

  const handleApplyCoupon = async () => {
    if (!couponCode.trim()) return;
    try {
      const res = await api.post(`${apiBase}/coupons/validate`, {
        code: couponCode,
        order_total: cartTotal,
      });
      if (res.data?.valid) {
        setCouponDiscount(res.data.discount_amount || 0);
        setCouponApplied(true);
        toast.success(t('checkout.couponApplied'));
      } else {
        toast.error(t('checkout.invalidCoupon'));
      }
    } catch {
      toast.error(t('checkout.invalidCoupon'));
    }
  };

  const shippingCost = selectedShipping?.price || 0;
  const finalTotal = cartTotal + shippingCost - couponDiscount;

  const handleSubmitOrder = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.customer_name || !formData.customer_phone || !formData.wilaya) {
      toast.error(isRTL ? 'يرجى ملء جميع الحقول المطلوبة' : 'Please fill all required fields');
      return;
    }

    try {
      setLoading(true);
      const res = await api.post(`${apiBase}/orders`, {
        ...formData,
        items: items.map((item: any) => ({
          product_id: item.product_id,
          quantity: item.quantity,
          variant_id: item.variant_id,
        })),
        shipping_company_id: selectedShipping?.company_id,
        shipping_type: shippingType,
        shipping_cost: shippingCost,
        coupon_code: couponApplied ? couponCode : null,
        browser_id: browserId,
      });

      setOrderId(res.data?.order_id || res.data?.data?.order_id || '');
      setOrderSuccess(true);
      clearCart();
    } catch (error: any) {
      toast.error(error.response?.data?.message || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  // Success screen
  if (orderSuccess) {
    return (
      <div className="py-20" style={{ backgroundColor: colors.background }}>
        <div className="container mx-auto px-4 max-w-md text-center">
          <div className="mb-6">
            <CheckCircle2 className="h-20 w-20 mx-auto text-green-500" />
          </div>
          <h2 className="text-2xl font-bold mb-2" style={{ color: colors.foreground }}>
            {t('checkout.orderSuccess')}
          </h2>
          <p className="text-lg mb-1" style={{ color: colors.mutedForeground }}>
            {t('checkout.orderNumber')}:
          </p>
          <p className="text-2xl font-bold mb-8" style={{ color: colors.primary }}>
            #{orderId}
          </p>
          <div className="flex flex-col gap-3">
            <Link
              to={`/track/${orderId}`}
              className="h-12 flex items-center justify-center gap-2 text-white font-semibold transition-opacity hover:opacity-90"
              style={{ backgroundColor: colors.primary, borderRadius: colors.buttonRadius }}
            >
              <Package className="h-5 w-5" />
              {t('checkout.trackOrder')}
            </Link>
            <Link
              to="/"
              className="h-12 flex items-center justify-center gap-2 font-semibold border transition-colors"
              style={{ borderColor: colors.border, color: colors.foreground, borderRadius: colors.buttonRadius }}
            >
              {t('checkout.backToStore')}
            </Link>
          </div>
        </div>
      </div>
    );
  }

  if (items.length === 0) {
    navigate('/cart');
    return null;
  }

  return (
    <div className="py-6" style={{ backgroundColor: colors.background }}>
      <div className="container mx-auto px-4">
        <h1 className="text-2xl font-bold mb-6" style={{ color: colors.foreground }}>
          {t('checkout.title')}
        </h1>

        <form onSubmit={handleSubmitOrder}>
          <div className="grid lg:grid-cols-3 gap-6">
            {/* Form */}
            <div className="lg:col-span-2 space-y-6">
              {/* Customer Info */}
              <div className="p-6 rounded-xl border" style={{ backgroundColor: colors.card, borderColor: colors.border, borderRadius: colors.cardRadius }}>
                <h3 className="font-bold mb-4" style={{ color: colors.cardForeground }}>
                  {t('checkout.customerInfo')}
                </h3>
                <div className="grid md:grid-cols-2 gap-4">
                  <div>
                    <label className="text-sm font-medium mb-1 block" style={{ color: colors.foreground }}>
                      {t('checkout.name')} *
                    </label>
                    <input
                      type="text"
                      name="customer_name"
                      value={formData.customer_name}
                      onChange={handleInputChange}
                      required
                      className="h-10 w-full rounded-lg border px-3 text-sm focus:outline-none"
                      style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                    />
                  </div>
                  <div>
                    <label className="text-sm font-medium mb-1 block" style={{ color: colors.foreground }}>
                      {t('checkout.phone')} *
                    </label>
                    <input
                      type="tel"
                      name="customer_phone"
                      value={formData.customer_phone}
                      onChange={handleInputChange}
                      required
                      dir="ltr"
                      className="h-10 w-full rounded-lg border px-3 text-sm focus:outline-none"
                      style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                    />
                  </div>
                  <div className="md:col-span-2">
                    <label className="text-sm font-medium mb-1 block" style={{ color: colors.foreground }}>
                      {t('checkout.email')}
                    </label>
                    <input
                      type="email"
                      name="customer_email"
                      value={formData.customer_email}
                      onChange={handleInputChange}
                      className="h-10 w-full rounded-lg border px-3 text-sm focus:outline-none"
                      style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                    />
                  </div>
                </div>
              </div>

              {/* Shipping Info */}
              <div className="p-6 rounded-xl border" style={{ backgroundColor: colors.card, borderColor: colors.border, borderRadius: colors.cardRadius }}>
                <h3 className="font-bold mb-4" style={{ color: colors.cardForeground }}>
                  {t('checkout.shippingInfo')}
                </h3>
                <div className="space-y-4">
                  {/* Wilaya & Commune */}
                  <div className="grid md:grid-cols-2 gap-4">
                    <div>
                      <label className="text-sm font-medium mb-1 block" style={{ color: colors.foreground }}>
                        {t('checkout.wilaya')} *
                      </label>
                      <select
                        name="wilaya"
                        value={formData.wilaya}
                        onChange={handleInputChange}
                        required
                        className="h-10 w-full rounded-lg border px-3 text-sm focus:outline-none"
                        style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                      >
                        <option value="">{t('checkout.selectWilaya')}</option>
                        {wilayas.map((w: any) => (
                          <option key={w.code || w.id} value={w.code || w.id}>
                            {w.code ? `${w.code} - ` : ''}{w[`name_${language}`] || w.name_ar || w.name}
                          </option>
                        ))}
                      </select>
                    </div>
                    <div>
                      <label className="text-sm font-medium mb-1 block" style={{ color: colors.foreground }}>
                        {t('checkout.commune')}
                      </label>
                      <select
                        name="commune"
                        value={formData.commune}
                        onChange={handleInputChange}
                        className="h-10 w-full rounded-lg border px-3 text-sm focus:outline-none"
                        style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                        disabled={!formData.wilaya}
                      >
                        <option value="">{t('checkout.selectCommune')}</option>
                        {communes.map((c: any) => (
                          <option key={c.id} value={c.id}>
                            {c[`name_${language}`] || c.name_ar || c.name}
                          </option>
                        ))}
                      </select>
                    </div>
                  </div>

                  {/* Address */}
                  <div>
                    <label className="text-sm font-medium mb-1 block" style={{ color: colors.foreground }}>
                      {t('checkout.address')} *
                    </label>
                    <input
                      type="text"
                      name="shipping_address"
                      value={formData.shipping_address}
                      onChange={handleInputChange}
                      required
                      className="h-10 w-full rounded-lg border px-3 text-sm focus:outline-none"
                      style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                    />
                  </div>

                  {/* Shipping Type */}
                  <div>
                    <label className="text-sm font-medium mb-2 block" style={{ color: colors.foreground }}>
                      {t('checkout.shippingMethod')}
                    </label>
                    <div className="grid grid-cols-2 gap-3">
                      {[
                        { value: 'home', label: t('checkout.homeDelivery'), icon: Home },
                        { value: 'office', label: t('checkout.officeDelivery'), icon: Building2 },
                      ].map((type) => (
                        <button
                          key={type.value}
                          type="button"
                          onClick={() => setShippingType(type.value)}
                          className="flex items-center gap-2 p-3 rounded-lg border text-sm font-medium transition-colors"
                          style={{
                            borderColor: shippingType === type.value ? colors.primary : colors.border,
                            backgroundColor: shippingType === type.value ? `${colors.primary}10` : 'transparent',
                            color: shippingType === type.value ? colors.primary : colors.foreground,
                          }}
                        >
                          <type.icon className="h-4 w-4" />
                          {type.label}
                        </button>
                      ))}
                    </div>
                  </div>

                  {/* Shipping Options */}
                  {formData.wilaya && (
                    <div>
                      <label className="text-sm font-medium mb-2 block" style={{ color: colors.foreground }}>
                        {t('checkout.shippingOptions')}
                      </label>
                      {loadingShipping ? (
                        <div className="flex items-center gap-2 py-4 justify-center" style={{ color: colors.mutedForeground }}>
                          <Loader2 className="h-4 w-4 animate-spin" />
                          {t('checkout.loadingShipping')}
                        </div>
                      ) : shippingOptions.length === 0 ? (
                        <p className="text-sm py-4 text-center" style={{ color: colors.mutedForeground }}>
                          {t('checkout.noShippingOptions')}
                        </p>
                      ) : (
                        <div className="space-y-2">
                          {shippingOptions.map((opt: any, idx: number) => (
                            <button
                              key={idx}
                              type="button"
                              onClick={() => setSelectedShipping(opt)}
                              className="w-full flex items-center justify-between p-3 rounded-lg border text-sm transition-colors"
                              style={{
                                borderColor: selectedShipping === opt ? colors.primary : colors.border,
                                backgroundColor: selectedShipping === opt ? `${colors.primary}10` : 'transparent',
                              }}
                            >
                              <div className="flex items-center gap-3">
                                <Truck className="h-4 w-4" style={{ color: colors.primary }} />
                                <div className={`text-${isRTL ? 'right' : 'left'}`}>
                                  <p className="font-medium" style={{ color: colors.foreground }}>{opt.company_name || opt.name}</p>
                                  {opt.delivery_days && (
                                    <p className="text-xs" style={{ color: colors.mutedForeground }}>
                                      {opt.delivery_days} {t('checkout.deliveryDays')}
                                    </p>
                                  )}
                                </div>
                              </div>
                              <span className="font-bold" style={{ color: opt.is_free ? '#16a34a' : colors.foreground }}>
                                {opt.is_free ? t('cart.freeShipping') : formatPrice(opt.price)}
                              </span>
                            </button>
                          ))}
                        </div>
                      )}
                    </div>
                  )}

                  {/* Notes */}
                  <div>
                    <label className="text-sm font-medium mb-1 block" style={{ color: colors.foreground }}>
                      {t('checkout.notes')}
                    </label>
                    <textarea
                      name="notes"
                      value={formData.notes}
                      onChange={handleInputChange}
                      rows={3}
                      className="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none resize-none"
                      style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                    />
                  </div>
                </div>
              </div>
            </div>

            {/* Order Summary */}
            <div>
              <div className="p-6 rounded-xl border sticky top-20" style={{ backgroundColor: colors.card, borderColor: colors.border, borderRadius: colors.cardRadius }}>
                <h3 className="font-bold mb-4" style={{ color: colors.cardForeground }}>
                  {t('checkout.orderSummary')}
                </h3>

                {/* Items */}
                <div className="space-y-3 mb-4">
                  {items.map((item: any) => (
                    <div key={item.product_id} className="flex items-center gap-3">
                      <div className="w-12 h-12 rounded-lg overflow-hidden shrink-0" style={{ backgroundColor: colors.muted }}>
                        <img src={getImageUrl(item.product?.images?.[0] || item.product?.image)} alt="" className="w-full h-full object-cover" />
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="text-sm line-clamp-1" style={{ color: colors.cardForeground }}>
                          {getProductName(item.product || {}, language)}
                        </p>
                        <p className="text-xs" style={{ color: colors.mutedForeground }}>x{item.quantity}</p>
                      </div>
                      <span className="text-sm font-medium" style={{ color: colors.cardForeground }}>
                        {formatPrice((item.product?.price || 0) * item.quantity)}
                      </span>
                    </div>
                  ))}
                </div>

                {/* Coupon */}
                <div className="mb-4">
                  <div className="flex gap-2">
                    <input
                      type="text"
                      placeholder={t('checkout.couponCode')}
                      value={couponCode}
                      onChange={(e) => setCouponCode(e.target.value)}
                      disabled={couponApplied}
                      className="flex-1 h-9 rounded-lg border px-3 text-sm focus:outline-none"
                      style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                    />
                    <button
                      type="button"
                      onClick={handleApplyCoupon}
                      disabled={couponApplied || !couponCode}
                      className="h-9 px-4 rounded-lg text-sm font-medium text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                      style={{ backgroundColor: colors.primary, borderRadius: colors.buttonRadius }}
                    >
                      {t('checkout.applyCoupon')}
                    </button>
                  </div>
                </div>

                {/* Totals */}
                <div className="space-y-2 pt-4 border-t" style={{ borderColor: colors.border }}>
                  <div className="flex justify-between text-sm">
                    <span style={{ color: colors.mutedForeground }}>{t('cart.subtotal')}</span>
                    <span style={{ color: colors.cardForeground }}>{formatPrice(cartTotal)}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span style={{ color: colors.mutedForeground }}>{t('cart.shipping')}</span>
                    <span style={{ color: shippingCost === 0 ? '#16a34a' : colors.cardForeground }}>
                      {selectedShipping ? (shippingCost === 0 ? t('cart.freeShipping') : formatPrice(shippingCost)) : '-'}
                    </span>
                  </div>
                  {couponDiscount > 0 && (
                    <div className="flex justify-between text-sm text-green-600">
                      <span>{t('cart.discount')}</span>
                      <span>-{formatPrice(couponDiscount)}</span>
                    </div>
                  )}
                  <div className="flex justify-between pt-3 border-t" style={{ borderColor: colors.border }}>
                    <span className="font-bold" style={{ color: colors.cardForeground }}>{t('cart.total')}</span>
                    <span className="font-bold text-lg" style={{ color: colors.primary }}>{formatPrice(finalTotal)}</span>
                  </div>
                </div>

                {/* Submit */}
                <button
                  type="submit"
                  disabled={loading || !formData.customer_name || !formData.customer_phone || !formData.wilaya}
                  className="w-full h-12 mt-6 flex items-center justify-center gap-2 text-white font-semibold transition-opacity hover:opacity-90 disabled:opacity-50"
                  style={{ backgroundColor: colors.primary, borderRadius: colors.buttonRadius }}
                >
                  {loading ? (
                    <>
                      <Loader2 className="h-5 w-5 animate-spin" />
                      {t('checkout.processing')}
                    </>
                  ) : (
                    <>
                      <CheckCircle2 className="h-5 w-5" />
                      {t('checkout.placeOrder')}
                    </>
                  )}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
};

export default CheckoutPage;
