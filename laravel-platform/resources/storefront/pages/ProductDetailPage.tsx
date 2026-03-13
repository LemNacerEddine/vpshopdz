import React, { useState, useEffect, useMemo } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { useCart } from '../contexts/CartContext';
import { api } from '../lib/api';
import { getImageUrl, getProductName, getProductDescription, calculateDiscount } from '../lib/utils';
import { ProductCard } from '../components/products/ProductCard';
import { toast } from 'sonner';
import {
  ShoppingCart, Heart, Share2, Star, Minus, Plus, Truck,
  Shield, RotateCcw, ChevronLeft, ChevronRight, Check
} from 'lucide-react';

const ProductDetailPage: React.FC = () => {
  const { productId } = useParams<{ productId: string }>();
  const { store, apiBase } = useStore();
  const { colors } = useTheme();
  const { t, language, isRTL, formatPrice } = useLanguage();
  const { addToCart } = useCart();

  const [product, setProduct] = useState<any>(null);
  const [relatedProducts, setRelatedProducts] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedImage, setSelectedImage] = useState(0);
  const [quantity, setQuantity] = useState(1);
  const [selectedVariant, setSelectedVariant] = useState<string>('');
  const [selectedOptions, setSelectedOptions] = useState<Record<string, string>>({});
  const [activeTab, setActiveTab] = useState('description');

  useEffect(() => {
    const fetchProduct = async () => {
      try {
        setLoading(true);
        const res = await api.get(`${apiBase}/products/${productId}`);
        const productData = res.data?.data || res.data;
        setProduct(productData);
        setSelectedImage(0);
        setQuantity(1);
        setSelectedOptions({});
        setSelectedVariant('');
        // Track product view in localStorage
        try {
          const key = `vp_viewed_${store.slug}`;
          const viewed = JSON.parse(localStorage.getItem(key) || '[]');
          const entry = {
            id: productData.id,
            name_ar: productData.name_ar,
            name_fr: productData.name_fr,
            name_en: productData.name_en,
            images: productData.images,
            price: productData.price,
            old_price: productData.old_price,
          };
          const filtered = viewed.filter((p: any) => p.id !== productData.id);
          localStorage.setItem(key, JSON.stringify([entry, ...filtered].slice(0, 12)));
        } catch { /* ignore */ }
        // Fetch related products using dedicated endpoint
        const relRes = await api.get(`${apiBase}/products/${productId}/related`)
          .catch(() => ({ data: { data: [] } }));
        setRelatedProducts(relRes.data?.data || relRes.data || []);
      } catch (error) {
        console.error('Error fetching product:', error);
      } finally {
        setLoading(false);
      }
    };
    fetchProduct();
  }, [productId, apiBase]);

  const discountData = useMemo(() => {
    if (!product) return { hasDiscount: false, displayPrice: 0, strikePrice: null, percent: 0 };
    const now = new Date();
    const start = product.discount_start ? new Date(product.discount_start) : null;
    const end = product.discount_end ? new Date(product.discount_end) : null;
    const isActive = product.discount_percent > 0 && (
      (!start && !end) || (start && end && now >= start && now <= end) || (start && !end && now >= start)
    );
    const discounted = isActive ? product.price * (1 - product.discount_percent / 100) : null;
    const hasLegacy = !isActive && product.old_price && product.old_price > product.price;
    return {
      hasDiscount: isActive || hasLegacy,
      displayPrice: isActive ? discounted : product.price,
      strikePrice: isActive ? product.price : (hasLegacy ? product.old_price : null),
      percent: isActive ? product.discount_percent : (hasLegacy ? calculateDiscount(product.price, product.old_price) : 0),
    };
  }, [product]);

  // Compute active variant from selectedOptions
  const variants = product?.variants || [];
  const options = product?.options || [];
  const hasVariants = product?.has_variants && options.length > 0;

  const activeVariant = useMemo(() => {
    if (!hasVariants || !variants.length) return null;
    const keys = Object.keys(selectedOptions);
    if (keys.length !== options.length) return null;
    return variants.find((v: any) => {
      const vo = v.options || {};
      return keys.every(k => vo[k] === selectedOptions[k]);
    }) || null;
  }, [hasVariants, variants, options, selectedOptions]);

  // Check if a value is available given current selections
  const isValueAvailable = (optionName: string, value: string): boolean => {
    const testOptions = { ...selectedOptions, [optionName]: value };
    return variants.some((v: any) => {
      const vo = v.options || {};
      return Object.keys(testOptions).every(k => !testOptions[k] || vo[k] === testOptions[k])
        && v.is_active && v.stock_quantity > 0;
    });
  };

  const selectOption = (optionName: string, value: string) => {
    setSelectedOptions(prev => ({ ...prev, [optionName]: value }));
    // Find matching variant and set it
    const newOptions = { ...selectedOptions, [optionName]: value };
    const matched = variants.find((v: any) => {
      const vo = v.options || {};
      return Object.keys(newOptions).every(k => vo[k] === newOptions[k]);
    });
    if (matched) setSelectedVariant(matched.id);
  };

  // Stock and price from active variant (or product)
  const displayStock = hasVariants
    ? (activeVariant?.stock_quantity ?? null)
    : (product?.stock_quantity ?? product?.stock ?? 0);

  const displayPrice = hasVariants && activeVariant?.price
    ? activeVariant.price
    : discountData.displayPrice;

  const handleAddToCart = async () => {
    if (!product) return;
    const success = await addToCart(product.id || product.product_id, quantity, selectedVariant || undefined);
    if (success) {
      toast.success(t('products.addToCart'), { description: getProductName(product, language) });
    }
  };

  const handleShare = () => {
    if (navigator.share) {
      navigator.share({ title: getProductName(product, language), url: window.location.href });
    } else {
      navigator.clipboard.writeText(window.location.href);
      toast.success(isRTL ? 'تم نسخ الرابط' : 'Link copied');
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin h-8 w-8 border-4 rounded-full" style={{ borderColor: colors.muted, borderTopColor: colors.primary }} />
      </div>
    );
  }

  if (!product) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <p style={{ color: colors.foreground }}>{t('common.error')}</p>
      </div>
    );
  }

  const name = getProductName(product, language);
  const description = getProductDescription(product, language);
  const images = product.images || (product.image ? [product.image] : []);
  const reviews = product.reviews || [];

  return (
    <div className="py-6" style={{ backgroundColor: colors.background }}>
      <div className="container mx-auto px-4">
        {/* Breadcrumb */}
        <nav className="flex items-center gap-2 text-sm mb-6" style={{ color: colors.mutedForeground }}>
          <Link to="/" className="hover:underline">{t('nav.home')}</Link>
          <span>/</span>
          <Link to="/products" className="hover:underline">{t('nav.products')}</Link>
          <span>/</span>
          <span style={{ color: colors.foreground }}>{name}</span>
        </nav>

        {/* Product Main */}
        <div className="grid md:grid-cols-2 gap-8 mb-12">
          {/* Gallery */}
          <div>
            <div
              className="aspect-square rounded-xl overflow-hidden mb-3"
              style={{ backgroundColor: colors.muted, borderRadius: colors.cardRadius }}
            >
              {images.length > 0 ? (
                <img
                  src={getImageUrl(images[selectedImage])}
                  alt={name}
                  className="w-full h-full object-cover"
                />
              ) : (
                <div className="w-full h-full flex items-center justify-center">
                  <span className="text-6xl opacity-20">📷</span>
                </div>
              )}
            </div>
            {images.length > 1 && (
              <div className="flex gap-2 overflow-x-auto pb-2">
                {images.map((img: string, idx: number) => (
                  <button
                    key={idx}
                    onClick={() => setSelectedImage(idx)}
                    className="w-16 h-16 rounded-lg overflow-hidden shrink-0 border-2 transition-colors"
                    style={{
                      borderColor: selectedImage === idx ? colors.primary : colors.border,
                    }}
                  >
                    <img src={getImageUrl(img)} alt="" className="w-full h-full object-cover" />
                  </button>
                ))}
              </div>
            )}
          </div>

          {/* Product Info */}
          <div>
            <h1 className="text-2xl md:text-3xl font-bold mb-3" style={{ color: colors.foreground }}>
              {name}
            </h1>

            {/* Rating */}
            {product.rating > 0 && (
              <div className="flex items-center gap-2 mb-4">
                <div className="flex items-center gap-0.5">
                  {[1, 2, 3, 4, 5].map((star) => (
                    <Star
                      key={star}
                      className={`h-4 w-4 ${star <= product.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'}`}
                    />
                  ))}
                </div>
                <span className="text-sm" style={{ color: colors.mutedForeground }}>
                  ({product.reviews_count || 0} {t('products.reviews')})
                </span>
              </div>
            )}

            {/* Price */}
            <div className="mb-6">
              <div className="flex items-center gap-3 flex-wrap">
                <span className={`text-3xl font-bold ${discountData.hasDiscount && !activeVariant?.price ? 'text-red-500' : ''}`} style={{ color: discountData.hasDiscount && !activeVariant?.price ? undefined : colors.primary }}>
                  {formatPrice(displayPrice!)}
                </span>
                {discountData.strikePrice && (
                  <span className="text-lg line-through" style={{ color: colors.mutedForeground }}>
                    {formatPrice(discountData.strikePrice)}
                  </span>
                )}
                {discountData.hasDiscount && (
                  <span className="bg-red-500 text-white text-sm font-bold px-2 py-0.5 rounded">
                    -{discountData.percent}%
                  </span>
                )}
              </div>
              {discountData.hasDiscount && discountData.strikePrice && (
                <p className="text-sm text-green-600 font-medium mt-1">
                  {t('products.save')} {formatPrice(discountData.strikePrice - discountData.displayPrice!)}
                </p>
              )}
            </div>

            {/* Stock Status */}
            <div className="mb-4">
              {hasVariants && !activeVariant ? (
                <span className="text-sm" style={{ color: colors.mutedForeground }}>
                  {isRTL ? 'اختر خيارات المنتج' : 'Select product options'}
                </span>
              ) : displayStock === null ? null : displayStock > 0 ? (
                <div className="flex items-center gap-2 flex-wrap">
                  <span className="flex items-center gap-1 text-sm text-green-600">
                    <Check className="h-4 w-4" />
                    {t('products.inStock')}
                  </span>
                  {displayStock <= (product.low_stock_threshold || 5) && (
                    <span className="text-xs font-bold text-orange-500 bg-orange-50 px-2 py-0.5 rounded-full">
                      {isRTL ? `متبقي ${displayStock} فقط!` : `Only ${displayStock} left!`}
                    </span>
                  )}
                </div>
              ) : (
                <span className="text-sm text-red-500">{t('products.outOfStock')}</span>
              )}
            </div>

            {/* Options/Variants — Shopify-style per-option selectors */}
            {hasVariants && options.map((opt: any) => (
              <div key={opt.id || opt.name} className="mb-5">
                <div className="flex items-center gap-2 mb-2">
                  <label className="text-sm font-semibold" style={{ color: colors.foreground }}>
                    {opt.name}
                  </label>
                  {selectedOptions[opt.name] && (
                    <span className="text-sm" style={{ color: colors.mutedForeground }}>
                      : {selectedOptions[opt.name]}
                    </span>
                  )}
                </div>
                <div className="flex flex-wrap gap-2">
                  {(opt.values || []).map((valObj: any) => {
                    const val = typeof valObj === 'string' ? valObj : valObj.value;
                    const isSelected = selectedOptions[opt.name] === val;
                    const available = isValueAvailable(opt.name, val);
                    return (
                      <button
                        key={val}
                        onClick={() => available && selectOption(opt.name, val)}
                        disabled={!available}
                        className="px-4 py-2 rounded-lg text-sm border transition-all relative"
                        style={{
                          borderColor: isSelected ? colors.primary : colors.border,
                          backgroundColor: isSelected ? `${colors.primary}15` : available ? 'transparent' : colors.muted,
                          color: isSelected ? colors.primary : available ? colors.foreground : colors.mutedForeground,
                          opacity: available ? 1 : 0.5,
                          textDecoration: !available ? 'line-through' : 'none',
                        }}
                      >
                        {val}
                      </button>
                    );
                  })}
                </div>
              </div>
            ))}

            {/* Fallback: simple variant list for products without structured options */}
            {!hasVariants && variants.length > 0 && (
              <div className="mb-6">
                <label className="text-sm font-medium mb-2 block" style={{ color: colors.foreground }}>
                  {t('products.variant')}
                </label>
                <div className="flex flex-wrap gap-2">
                  {variants.map((v: any) => (
                    <button
                      key={v.id}
                      onClick={() => setSelectedVariant(v.id)}
                      className="px-4 py-2 rounded-lg text-sm border transition-colors"
                      style={{
                        borderColor: selectedVariant === v.id ? colors.primary : colors.border,
                        backgroundColor: selectedVariant === v.id ? `${colors.primary}15` : 'transparent',
                        color: selectedVariant === v.id ? colors.primary : colors.foreground,
                      }}
                    >
                      {v.name || v.value}
                    </button>
                  ))}
                </div>
              </div>
            )}

            {/* Quantity */}
            <div className="mb-6">
              <label className="text-sm font-medium mb-2 block" style={{ color: colors.foreground }}>
                {t('products.quantity')}
              </label>
              <div className="flex items-center gap-3">
                <div className="flex items-center border rounded-lg" style={{ borderColor: colors.border }}>
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="h-10 w-10 flex items-center justify-center transition-colors"
                    style={{ color: colors.foreground }}
                  >
                    <Minus className="h-4 w-4" />
                  </button>
                  <span className="w-12 text-center font-medium" style={{ color: colors.foreground }}>{quantity}</span>
                  <button
                    onClick={() => setQuantity(Math.min(displayStock || 99, quantity + 1))}
                    className="h-10 w-10 flex items-center justify-center transition-colors"
                    style={{ color: colors.foreground }}
                  >
                    <Plus className="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>

            {/* Action Buttons */}
            <div className="flex gap-3 mb-6">
              <button
                onClick={handleAddToCart}
                disabled={
                  (hasVariants && !activeVariant) ||
                  (hasVariants && activeVariant?.stock_quantity === 0) ||
                  (!hasVariants && displayStock === 0)
                }
                className="flex-1 h-12 flex items-center justify-center gap-2 text-white font-semibold transition-opacity hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
                style={{ backgroundColor: colors.primary, borderRadius: colors.buttonRadius }}
              >
                <ShoppingCart className="h-5 w-5" />
                {hasVariants && !activeVariant
                  ? (isRTL ? 'اختر خيارات المنتج' : 'Select options')
                  : t('products.addToCart')}
              </button>
              <button
                className="h-12 w-12 flex items-center justify-center border rounded-lg transition-colors"
                style={{ borderColor: colors.border, color: colors.foreground, borderRadius: colors.buttonRadius }}
              >
                <Heart className="h-5 w-5" />
              </button>
              <button
                onClick={handleShare}
                className="h-12 w-12 flex items-center justify-center border rounded-lg transition-colors"
                style={{ borderColor: colors.border, color: colors.foreground, borderRadius: colors.buttonRadius }}
              >
                <Share2 className="h-5 w-5" />
              </button>
            </div>

            {/* Features */}
            <div className="grid grid-cols-3 gap-3">
              {[
                { icon: Truck, text: isRTL ? 'توصيل سريع' : 'Fast Delivery' },
                { icon: Shield, text: isRTL ? 'ضمان الجودة' : 'Quality Guarantee' },
                { icon: RotateCcw, text: isRTL ? 'إرجاع سهل' : 'Easy Returns' },
              ].map((feat, idx) => (
                <div
                  key={idx}
                  className="flex flex-col items-center gap-1 p-3 rounded-lg text-center"
                  style={{ backgroundColor: colors.muted }}
                >
                  <feat.icon className="h-5 w-5" style={{ color: colors.primary }} />
                  <span className="text-[11px] font-medium" style={{ color: colors.foreground }}>{feat.text}</span>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Tabs: Description / Reviews */}
        <div className="mb-12">
          <div className="flex border-b mb-6" style={{ borderColor: colors.border }}>
            {['description', 'reviews'].map((tab) => (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className="px-6 py-3 text-sm font-medium border-b-2 transition-colors -mb-px"
                style={{
                  borderColor: activeTab === tab ? colors.primary : 'transparent',
                  color: activeTab === tab ? colors.primary : colors.mutedForeground,
                }}
              >
                {tab === 'description' ? t('products.description') : `${t('products.reviews')} (${reviews.length})`}
              </button>
            ))}
          </div>

          {activeTab === 'description' && (
            <div
              className="prose max-w-none text-sm leading-relaxed"
              style={{ color: colors.foreground }}
              dangerouslySetInnerHTML={{ __html: description || (isRTL ? 'لا يوجد وصف' : 'No description available') }}
            />
          )}

          {activeTab === 'reviews' && (
            <div>
              {reviews.length === 0 ? (
                <p className="text-center py-8" style={{ color: colors.mutedForeground }}>
                  {t('products.noReviews')}
                </p>
              ) : (
                <div className="space-y-4">
                  {reviews.map((review: any, idx: number) => (
                    <div
                      key={idx}
                      className="p-4 rounded-xl border"
                      style={{ backgroundColor: colors.card, borderColor: colors.border }}
                    >
                      <div className="flex items-center justify-between mb-2">
                        <div className="flex items-center gap-2">
                          <div className="h-8 w-8 rounded-full flex items-center justify-center text-white text-sm font-bold" style={{ backgroundColor: colors.primary }}>
                            {(review.customer_name || 'U').charAt(0)}
                          </div>
                          <span className="font-medium text-sm" style={{ color: colors.foreground }}>{review.customer_name}</span>
                        </div>
                        <div className="flex items-center gap-0.5">
                          {[1, 2, 3, 4, 5].map((star) => (
                            <Star key={star} className={`h-3 w-3 ${star <= review.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'}`} />
                          ))}
                        </div>
                      </div>
                      <p className="text-sm" style={{ color: colors.foreground }}>{review.comment}</p>
                    </div>
                  ))}
                </div>
              )}
            </div>
          )}
        </div>

        {/* Related Products */}
        {relatedProducts.length > 0 && (
          <div>
            <h2 className="text-xl font-bold mb-6" style={{ color: colors.foreground }}>
              {t('products.relatedProducts')}
            </h2>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              {relatedProducts.map((p: any) => (
                <ProductCard key={p.id || p.product_id} product={p} />
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default ProductDetailPage;
