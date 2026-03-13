import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { useCart } from '../contexts/CartContext';
import { api } from '../lib/api';
import { toast } from 'sonner';
import { Loader2, ShoppingCart, Truck, Shield, RotateCcw, ChevronLeft, ChevronRight, Star, Phone, MessageCircle } from 'lucide-react';
import { Toaster } from 'sonner';

const ProductLandingPage: React.FC = () => {
  const { productId } = useParams<{ productId: string }>();
  const { apiBase, store } = useStore();
  const { colors } = useTheme();
  const { isRTL } = useLanguage();
  const { addItem } = useCart();

  const [product, setProduct] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [activeImage, setActiveImage] = useState(0);
  const [quantity, setQuantity] = useState(1);
  const [selectedVariant, setSelectedVariant] = useState<any>(null);
  const [adding, setAdding] = useState(false);
  const [form, setForm] = useState({ name: '', phone: '', wilaya: '', notes: '' });
  const [submitting, setSubmitting] = useState(false);

  const ChevronPrev = isRTL ? ChevronRight : ChevronLeft;
  const ChevronNext = isRTL ? ChevronLeft : ChevronRight;

  useEffect(() => {
    const fetchProduct = async () => {
      setLoading(true);
      try {
        const res = await api.get(`${apiBase}/products/${productId}`);
        const p = res.data?.data || res.data;
        setProduct(p);
        if (p?.variants?.length) setSelectedVariant(p.variants[0]);
      } catch {
        setProduct(null);
      }
      setLoading(false);
    };
    fetchProduct();
  }, [apiBase, productId]);

  const price = selectedVariant?.price || product?.sale_price || product?.price || 0;
  const originalPrice = product?.sale_price ? product?.price : null;
  const images = product?.images || [];
  const name = product?.name_ar || product?.name || '';

  const handleOrder = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!form.name || !form.phone) { toast.error('يرجى ملء الاسم والهاتف'); return; }
    setSubmitting(true);
    try {
      await api.post(`${apiBase}/orders`, {
        customer_name: form.name,
        customer_phone: form.phone,
        wilaya: form.wilaya,
        notes: form.notes,
        items: [{ product_id: product.id, quantity, variant_id: selectedVariant?.id }],
        source: 'landing',
      });
      toast.success('تم تسجيل طلبك بنجاح! سنتصل بك قريباً');
      setForm({ name: '', phone: '', wilaya: '', notes: '' });
    } catch (err: any) {
      toast.error(err?.response?.data?.message || 'حدث خطأ، حاول مرة أخرى');
    }
    setSubmitting(false);
  };

  if (loading) return (
    <div className="min-h-screen flex items-center justify-center" style={{ backgroundColor: colors.background }}>
      <Loader2 className="h-10 w-10 animate-spin" style={{ color: colors.primary }} />
    </div>
  );

  if (!product) return (
    <div className="min-h-screen flex items-center justify-center" style={{ backgroundColor: colors.background }}>
      <p style={{ color: colors.mutedForeground }}>المنتج غير موجود</p>
    </div>
  );

  return (
    <div className="min-h-screen" style={{ backgroundColor: colors.background, color: colors.foreground, fontFamily: 'var(--font-body)' }} dir={isRTL ? 'rtl' : 'ltr'}>
      <Toaster position="top-center" richColors />

      {/* Top bar */}
      <div className="py-3 px-4 text-center text-white text-sm font-medium" style={{ backgroundColor: colors.primary }}>
        🚚 توصيل سريع لجميع الولايات | اتصل بنا: {(store as any)?.phone || ''}
      </div>

      <div className="max-w-5xl mx-auto px-4 py-8">
        <div className="grid md:grid-cols-2 gap-8 items-start">
          {/* Image Gallery */}
          <div>
            <div className="relative aspect-square rounded-2xl overflow-hidden mb-3" style={{ backgroundColor: colors.muted }}>
              {images.length > 0 ? (
                <img src={images[activeImage]} alt={name} className="w-full h-full object-cover" />
              ) : (
                <div className="w-full h-full flex items-center justify-center">
                  <ShoppingCart className="h-20 w-20 opacity-20" />
                </div>
              )}
              {images.length > 1 && (
                <>
                  <button onClick={() => setActiveImage(i => (i - 1 + images.length) % images.length)}
                    className="absolute start-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 flex items-center justify-center shadow">
                    <ChevronPrev className="h-5 w-5" />
                  </button>
                  <button onClick={() => setActiveImage(i => (i + 1) % images.length)}
                    className="absolute end-2 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 flex items-center justify-center shadow">
                    <ChevronNext className="h-5 w-5" />
                  </button>
                </>
              )}
            </div>
            {images.length > 1 && (
              <div className="flex gap-2 flex-wrap">
                {images.map((img: string, i: number) => (
                  <button key={i} onClick={() => setActiveImage(i)}
                    className={`w-16 h-16 rounded-xl overflow-hidden border-2 transition-colors ${activeImage === i ? 'border-current' : 'border-transparent'}`}
                    style={{ borderColor: activeImage === i ? colors.primary : 'transparent' }}>
                    <img src={img} alt="" className="w-full h-full object-cover" />
                  </button>
                ))}
              </div>
            )}
          </div>

          {/* Product Info + Order Form */}
          <div className="space-y-5">
            <div>
              <h1 className="text-2xl font-black mb-2" style={{ color: colors.foreground }}>{name}</h1>
              {product?.description_ar || product?.description ? (
                <p className="text-sm leading-relaxed" style={{ color: colors.mutedForeground }}>
                  {product.description_ar || product.description}
                </p>
              ) : null}
            </div>

            {/* Price */}
            <div className="flex items-baseline gap-3">
              <span className="text-3xl font-black" style={{ color: colors.primary }}>
                {parseFloat(price).toLocaleString()} د.ج
              </span>
              {originalPrice && (
                <span className="text-lg line-through" style={{ color: colors.mutedForeground }}>
                  {parseFloat(originalPrice).toLocaleString()} د.ج
                </span>
              )}
            </div>

            {/* Variants */}
            {product?.variants?.length > 0 && (
              <div>
                <p className="text-sm font-medium mb-2" style={{ color: colors.foreground }}>اختر المقاس / اللون:</p>
                <div className="flex flex-wrap gap-2">
                  {product.variants.map((v: any) => (
                    <button key={v.id} onClick={() => setSelectedVariant(v)}
                      className="px-3 py-1.5 rounded-lg border text-sm font-medium transition-colors"
                      style={{
                        backgroundColor: selectedVariant?.id === v.id ? colors.primary : 'transparent',
                        borderColor: selectedVariant?.id === v.id ? colors.primary : colors.border,
                        color: selectedVariant?.id === v.id ? '#fff' : colors.foreground,
                      }}>
                      {v.name}
                    </button>
                  ))}
                </div>
              </div>
            )}

            {/* Trust badges */}
            <div className="grid grid-cols-3 gap-2 py-3 border-y" style={{ borderColor: colors.border }}>
              {[
                { icon: Truck, label: 'توصيل لكل الجزائر' },
                { icon: Shield, label: 'جودة مضمونة 100%' },
                { icon: RotateCcw, label: 'إرجاع مجاني' },
              ].map(({ icon: Icon, label }) => (
                <div key={label} className="flex flex-col items-center gap-1 text-center">
                  <Icon className="h-5 w-5" style={{ color: colors.primary }} />
                  <span className="text-xs" style={{ color: colors.mutedForeground }}>{label}</span>
                </div>
              ))}
            </div>

            {/* Order Form */}
            <div className="rounded-2xl border p-5" style={{ backgroundColor: colors.card, borderColor: colors.border }}>
              <h2 className="font-bold text-lg mb-4" style={{ color: colors.foreground }}>🛒 اطلب الآن</h2>
              <form onSubmit={handleOrder} className="space-y-3">
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <label className="block text-xs font-medium mb-1" style={{ color: colors.mutedForeground }}>الاسم الكامل *</label>
                    <input
                      type="text" required value={form.name} onChange={e => setForm(f => ({ ...f, name: e.target.value }))}
                      placeholder="محمد أحمد"
                      className="w-full h-10 px-3 rounded-lg border text-sm focus:outline-none focus:ring-2"
                      style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-medium mb-1" style={{ color: colors.mutedForeground }}>رقم الهاتف *</label>
                    <input
                      type="tel" required value={form.phone} onChange={e => setForm(f => ({ ...f, phone: e.target.value }))}
                      placeholder="0XXX XX XX XX" dir="ltr"
                      className="w-full h-10 px-3 rounded-lg border text-sm focus:outline-none focus:ring-2"
                      style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                    />
                  </div>
                </div>
                <div>
                  <label className="block text-xs font-medium mb-1" style={{ color: colors.mutedForeground }}>الولاية</label>
                  <input
                    type="text" value={form.wilaya} onChange={e => setForm(f => ({ ...f, wilaya: e.target.value }))}
                    placeholder="الجزائر"
                    className="w-full h-10 px-3 rounded-lg border text-sm focus:outline-none focus:ring-2"
                    style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                  />
                </div>
                {/* Quantity */}
                <div className="flex items-center gap-3">
                  <span className="text-xs font-medium" style={{ color: colors.mutedForeground }}>الكمية:</span>
                  <div className="flex items-center gap-1 border rounded-lg overflow-hidden" style={{ borderColor: colors.border }}>
                    <button type="button" onClick={() => setQuantity(q => Math.max(1, q - 1))}
                      className="w-8 h-8 flex items-center justify-center text-lg font-bold transition-colors hover:opacity-70"
                      style={{ backgroundColor: colors.muted, color: colors.foreground }}>−</button>
                    <span className="px-4 text-sm font-bold" style={{ color: colors.foreground }}>{quantity}</span>
                    <button type="button" onClick={() => setQuantity(q => q + 1)}
                      className="w-8 h-8 flex items-center justify-center text-lg font-bold transition-colors hover:opacity-70"
                      style={{ backgroundColor: colors.muted, color: colors.foreground }}>+</button>
                  </div>
                </div>
                <div>
                  <label className="block text-xs font-medium mb-1" style={{ color: colors.mutedForeground }}>ملاحظات (اختياري)</label>
                  <textarea
                    value={form.notes} onChange={e => setForm(f => ({ ...f, notes: e.target.value }))}
                    rows={2} placeholder="أي ملاحظات إضافية..."
                    className="w-full px-3 py-2 rounded-lg border text-sm focus:outline-none focus:ring-2 resize-none"
                    style={{ backgroundColor: colors.muted, borderColor: colors.border, color: colors.foreground }}
                  />
                </div>
                <button
                  type="submit" disabled={submitting}
                  className="w-full py-3 rounded-xl text-white font-bold text-base flex items-center justify-center gap-2 transition-opacity hover:opacity-90 disabled:opacity-50"
                  style={{ backgroundColor: colors.primary }}
                >
                  {submitting ? <Loader2 className="h-5 w-5 animate-spin" /> : <><ShoppingCart className="h-5 w-5" /> اطلب الآن — {(parseFloat(price) * quantity).toLocaleString()} د.ج</>}
                </button>
              </form>
            </div>

            {/* Reviews teaser */}
            {product?.reviews_count > 0 && (
              <div className="flex items-center gap-2 text-sm" style={{ color: colors.mutedForeground }}>
                <div className="flex">
                  {[1,2,3,4,5].map(s => (
                    <Star key={s} className="h-4 w-4" fill={s <= Math.round(product.rating || 0) ? '#f59e0b' : 'transparent'} stroke={s <= Math.round(product.rating || 0) ? '#f59e0b' : colors.mutedForeground} />
                  ))}
                </div>
                <span>{product.rating?.toFixed(1)} ({product.reviews_count} تقييم)</span>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProductLandingPage;
