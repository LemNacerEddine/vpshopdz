import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useCart } from '@/contexts/CartContext';
import { initFBPixel, trackViewContent, trackAddToCart } from '@/lib/fbPixel';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import {
  Star,
  ShoppingCart,
  Truck,
  Shield,
  RotateCcw,
  ChevronLeft,
  ChevronRight,
  Loader2,
  MessageCircle,
  Store,
  Globe,
} from 'lucide-react';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

// ---------- translations ----------
const translations = {
  ar: {
    orderNow: 'اطلب الآن',
    addToCart: 'أضف إلى السلة',
    freeDelivery: 'توصيل سريع',
    qualityGuarantee: 'ضمان الجودة',
    easyReturns: 'إرجاع سهل',
    readMore: 'اقرأ المزيد',
    readLess: 'اقرأ أقل',
    outOfStock: 'غير متوفر حالياً',
    reviews: 'تقييم',
    description: 'وصف المنتج',
    loading: 'جاري التحميل...',
    notFound: 'المنتج غير موجود',
    backToStore: 'العودة للمتجر',
    contactUs: 'تواصل معنا',
    storeName: 'AgroYousfi',
    allRightsReserved: 'جميع الحقوق محفوظة',
    limitedOffer: 'عرض محدود',
    inStock: 'متوفر',
    off: 'خصم',
    relatedProducts: 'منتجات قد تعجبك',
  },
  fr: {
    orderNow: 'Commander maintenant',
    addToCart: 'Ajouter au panier',
    freeDelivery: 'Livraison rapide',
    qualityGuarantee: 'Qualité garantie',
    easyReturns: 'Retour facile',
    readMore: 'Lire plus',
    readLess: 'Lire moins',
    outOfStock: 'Rupture de stock',
    reviews: 'avis',
    description: 'Description du produit',
    loading: 'Chargement...',
    notFound: 'Produit introuvable',
    backToStore: 'Retour à la boutique',
    contactUs: 'Contactez-nous',
    storeName: 'AgroYousfi',
    allRightsReserved: 'Tous droits réservés',
    limitedOffer: 'Offre limitée',
    inStock: 'En stock',
    off: 'de réduction',
    relatedProducts: 'Vous aimerez aussi',
  },
  en: {
    orderNow: 'Order Now',
    addToCart: 'Add to Cart',
    freeDelivery: 'Fast Delivery',
    qualityGuarantee: 'Quality Guarantee',
    easyReturns: 'Easy Returns',
    readMore: 'Read more',
    readLess: 'Read less',
    outOfStock: 'Out of Stock',
    reviews: 'reviews',
    description: 'Product Description',
    loading: 'Loading...',
    notFound: 'Product not found',
    backToStore: 'Back to Store',
    contactUs: 'Contact Us',
    storeName: 'AgroYousfi',
    allRightsReserved: 'All rights reserved',
    limitedOffer: 'Limited Offer',
    inStock: 'In Stock',
    off: 'OFF',
    relatedProducts: 'You may also like',
  },
};

// ---------- helper: format price ----------
const formatPrice = (price) => {
  if (price == null || isNaN(price)) return '';
  return price.toLocaleString('ar-DZ') + ' د.ج';
};

// ---------- Image Gallery (swipeable) ----------
const ImageGallery = ({ images, productName }) => {
  const [current, setCurrent] = useState(0);
  const [touchStart, setTouchStart] = useState(null);
  const [touchEnd, setTouchEnd] = useState(null);

  const minSwipeDistance = 50;

  const onTouchStart = (e) => {
    setTouchEnd(null);
    setTouchStart(e.targetTouches[0].clientX);
  };

  const onTouchMove = (e) => {
    setTouchEnd(e.targetTouches[0].clientX);
  };

  const onTouchEnd = () => {
    if (!touchStart || !touchEnd) return;
    const distance = touchStart - touchEnd;
    const isLeftSwipe = distance > minSwipeDistance;
    const isRightSwipe = distance < -minSwipeDistance;

    if (isLeftSwipe && current < images.length - 1) {
      setCurrent((prev) => prev + 1);
    }
    if (isRightSwipe && current > 0) {
      setCurrent((prev) => prev - 1);
    }
  };

  if (!images || images.length === 0) {
    return (
      <div className="w-full aspect-square bg-muted rounded-2xl flex items-center justify-center">
        <Store className="h-16 w-16 text-muted-foreground/40" />
      </div>
    );
  }

  const imgSrc = (img) => {
    if (typeof img === 'string') {
      return img.startsWith('http') ? img : `${process.env.REACT_APP_BACKEND_URL}/storage/${img}`;
    }
    return img?.url || img?.path || '';
  };

  return (
    <div className="relative w-full">
      <div
        className="w-full aspect-square overflow-hidden rounded-2xl bg-muted"
        onTouchStart={onTouchStart}
        onTouchMove={onTouchMove}
        onTouchEnd={onTouchEnd}
      >
        <img
          src={imgSrc(images[current])}
          alt={productName}
          className="w-full h-full object-cover transition-transform duration-300"
          loading="eager"
        />
      </div>

      {/* Navigation arrows (desktop) */}
      {images.length > 1 && (
        <>
          <button
            onClick={() => setCurrent((prev) => Math.max(0, prev - 1))}
            disabled={current === 0}
            className="absolute top-1/2 left-2 -translate-y-1/2 bg-white/80 backdrop-blur-sm rounded-full p-1.5 shadow-md disabled:opacity-30 hover:bg-white transition-colors hidden sm:flex items-center justify-center"
            aria-label="Previous image"
          >
            <ChevronLeft className="h-5 w-5 text-gray-700" />
          </button>
          <button
            onClick={() => setCurrent((prev) => Math.min(images.length - 1, prev + 1))}
            disabled={current === images.length - 1}
            className="absolute top-1/2 right-2 -translate-y-1/2 bg-white/80 backdrop-blur-sm rounded-full p-1.5 shadow-md disabled:opacity-30 hover:bg-white transition-colors hidden sm:flex items-center justify-center"
            aria-label="Next image"
          >
            <ChevronRight className="h-5 w-5 text-gray-700" />
          </button>
        </>
      )}

      {/* Dots indicator */}
      {images.length > 1 && (
        <div className="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
          {images.map((_, idx) => (
            <button
              key={idx}
              onClick={() => setCurrent(idx)}
              className={`rounded-full transition-all duration-300 ${
                idx === current
                  ? 'w-6 h-2 bg-white shadow-md'
                  : 'w-2 h-2 bg-white/60'
              }`}
              aria-label={`Go to image ${idx + 1}`}
            />
          ))}
        </div>
      )}

      {/* Image counter badge */}
      {images.length > 1 && (
        <div className="absolute top-3 right-3 bg-black/50 backdrop-blur-sm text-white text-xs px-2.5 py-1 rounded-full">
          {current + 1}/{images.length}
        </div>
      )}
    </div>
  );
};

// ---------- Star rating ----------
const StarRating = ({ rating, count, reviewsLabel }) => {
  const rounded = Math.round(rating * 2) / 2;
  return (
    <div className="flex items-center gap-2">
      <div className="flex items-center gap-0.5">
        {[1, 2, 3, 4, 5].map((star) => (
          <Star
            key={star}
            className={`h-4 w-4 ${
              star <= rounded
                ? 'fill-yellow-400 text-yellow-400'
                : star - 0.5 <= rounded
                ? 'fill-yellow-400/50 text-yellow-400'
                : 'text-gray-200 fill-gray-200'
            }`}
          />
        ))}
      </div>
      <span className="text-sm font-medium text-foreground">{rating?.toFixed(1)}</span>
      {count != null && (
        <span className="text-sm text-muted-foreground">
          ({count} {reviewsLabel})
        </span>
      )}
    </div>
  );
};

// ---------- Trust indicator card ----------
const TrustItem = ({ icon: Icon, label }) => (
  <div className="flex flex-col items-center gap-1.5 p-3 rounded-xl bg-green-50 dark:bg-green-950/20 border border-green-100 dark:border-green-900/30">
    <div className="h-10 w-10 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
      <Icon className="h-5 w-5 text-green-700 dark:text-green-400" />
    </div>
    <span className="text-xs font-semibold text-green-800 dark:text-green-300 text-center leading-tight">
      {label}
    </span>
  </div>
);

// ---------- Related product card ----------
const RelatedProductCard = ({ product: rp, language, isRTL, onClick }) => {
  const rpName = rp[`name_${language}`] || rp.name_ar || '';
  const hasDisc = rp.discount_percent && rp.discount_percent > 0;
  const hasLegacy = !hasDisc && rp.old_price && rp.old_price > rp.price;
  const discPct = hasDisc
    ? rp.discount_percent
    : hasLegacy
    ? Math.round((1 - rp.price / rp.old_price) * 100)
    : 0;
  const finalPrice = hasDisc ? rp.price * (1 - rp.discount_percent / 100) : rp.price;
  const oldPrice = hasDisc ? rp.price : hasLegacy ? rp.old_price : null;

  const imgSrc = (img) => {
    if (typeof img === 'string') {
      return img.startsWith('http') ? img : `${process.env.REACT_APP_BACKEND_URL}/storage/${img}`;
    }
    return img?.url || img?.path || '';
  };

  const thumb = rp.images && rp.images.length > 0 ? imgSrc(rp.images[0]) : '';

  return (
    <button
      onClick={onClick}
      className="flex-shrink-0 w-36 sm:w-40 snap-start bg-card border rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow text-start"
    >
      {/* Image */}
      <div className="relative w-full aspect-square bg-muted">
        {thumb ? (
          <img src={thumb} alt={rpName} className="w-full h-full object-cover" loading="lazy" />
        ) : (
          <div className="w-full h-full flex items-center justify-center">
            <Store className="h-8 w-8 text-muted-foreground/40" />
          </div>
        )}
        {discPct > 0 && (
          <span className={`absolute top-1.5 ${isRTL ? 'right-1.5' : 'left-1.5'} bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded`}>
            -{discPct}%
          </span>
        )}
      </div>
      {/* Info */}
      <div className="p-2.5 space-y-1.5">
        <h3 className="text-xs font-semibold text-foreground leading-tight line-clamp-2">
          {rpName}
        </h3>
        {rp.rating != null && (
          <div className="flex items-center gap-0.5">
            {[1, 2, 3, 4, 5].map((s) => (
              <Star
                key={s}
                className={`h-3 w-3 ${
                  s <= Math.round(rp.rating) ? 'fill-yellow-400 text-yellow-400' : 'text-gray-200 fill-gray-200'
                }`}
              />
            ))}
            <span className="text-[10px] text-muted-foreground ms-0.5">{rp.rating?.toFixed(1)}</span>
          </div>
        )}
        <div className="flex items-baseline gap-1.5 flex-wrap">
          <span className="text-sm font-bold text-red-600">{formatPrice(finalPrice)}</span>
          {oldPrice && (
            <span className="text-[10px] text-muted-foreground line-through">{formatPrice(oldPrice)}</span>
          )}
        </div>
      </div>
    </button>
  );
};

// ---------- MAIN COMPONENT ----------
const ProductLandingPage = () => {
  const { productId } = useParams();
  const navigate = useNavigate();
  const { language, isRTL, setLanguage } = useLanguage();
  const { addToCart } = useCart();

  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);
  const [descExpanded, setDescExpanded] = useState(false);
  const [ctaLoading, setCtaLoading] = useState(false);
  const [langMenuOpen, setLangMenuOpen] = useState(false);
  const [relatedProducts, setRelatedProducts] = useState([]);
  const [relatedLoading, setRelatedLoading] = useState(false);

  const l = translations[language] || translations.ar;

  // ---------- init FB Pixel ----------
  useEffect(() => {
    initFBPixel();
  }, []);

  // ---------- fetch product ----------
  useEffect(() => {
    const fetchProduct = async () => {
      try {
        setLoading(true);
        setError(false);
        const response = await axios.get(`${API}/products/${productId}`);
        setProduct(response.data);

        // FB Pixel: ViewContent
        const p = response.data;
        if (p) {
          trackViewContent({
            id: p.product_id || productId,
            name: p[`name_${language}`] || p.name_ar,
            category: p.category || '',
            price: p.price,
          });
        }
      } catch (err) {
        console.error('Error fetching product:', err);
        setError(true);
      } finally {
        setLoading(false);
      }
    };

    if (productId) {
      fetchProduct();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [productId]);

  // ---------- fetch related products ----------
  useEffect(() => {
    if (!product || !product.category_id) return;

    const fetchRelated = async () => {
      try {
        setRelatedLoading(true);
        const res = await axios.get(`${API}/products`, {
          params: { category_id: product.category_id, limit: 7 },
        });
        const items = (res.data?.data || res.data || [])
          .filter((p) => p.product_id !== product.product_id)
          .sort((a, b) => (b.sold_count || 0) - (a.sold_count || 0))
          .slice(0, 6);
        setRelatedProducts(items);
      } catch {
        // silently fail — section just stays hidden
      } finally {
        setRelatedLoading(false);
      }
    };

    fetchRelated();
  }, [product]);

  // ---------- CTA handler ----------
  const handleOrderNow = useCallback(async () => {
    if (!product || ctaLoading) return;
    setCtaLoading(true);

    // FB Pixel: AddToCart
    trackAddToCart({
      id: product.product_id || productId,
      name: product[`name_${language}`] || product.name_ar,
      price: product.price,
      quantity: 1,
    });

    await addToCart(product.product_id || productId, 1);
    setCtaLoading(false);
    navigate(`/products/${productId}`);
  }, [product, productId, language, addToCart, navigate, ctaLoading]);

  // ---------- language switcher ----------
  const languages = [
    { code: 'ar', label: 'العربية' },
    { code: 'fr', label: 'Français' },
    { code: 'en', label: 'English' },
  ];

  // ---------- derived product data ----------
  const name = product ? (product[`name_${language}`] || product.name_ar) : '';
  const description = product ? (product[`description_${language}`] || product.description_ar) : '';
  const hasDiscount = product && product.discount_percent && product.discount_percent > 0;
  const hasLegacyDiscount = product && !hasDiscount && product.old_price && product.old_price > product.price;
  const discountPercent = hasDiscount
    ? product.discount_percent
    : hasLegacyDiscount
    ? Math.round((1 - product.price / product.old_price) * 100)
    : 0;
  const displayPrice = hasDiscount
    ? product.price * (1 - product.discount_percent / 100)
    : product?.price;
  const originalPrice = hasDiscount
    ? product.price
    : hasLegacyDiscount
    ? product.old_price
    : null;
  const showDiscount = discountPercent > 0;
  const isOutOfStock = product && product.stock <= 0;

  // Description truncation
  const DESC_LIMIT = 150;
  const isDescLong = description && description.length > DESC_LIMIT;
  const displayDesc = descExpanded || !isDescLong
    ? description
    : description.slice(0, DESC_LIMIT) + '...';

  // ===================== LOADING STATE =====================
  if (loading) {
    return (
      <div className="min-h-screen bg-background" dir={isRTL ? 'rtl' : 'ltr'}>
        {/* Top bar skeleton */}
        <div className="border-b bg-card px-4 py-3 flex items-center justify-between">
          <Skeleton className="h-6 w-28" />
          <Skeleton className="h-8 w-8 rounded-full" />
        </div>
        {/* Image skeleton */}
        <Skeleton className="w-full aspect-square" />
        {/* Content skeleton */}
        <div className="p-4 space-y-4">
          <Skeleton className="h-7 w-3/4" />
          <Skeleton className="h-5 w-1/2" />
          <Skeleton className="h-10 w-1/3" />
          <Skeleton className="h-20 w-full" />
          <div className="grid grid-cols-3 gap-3">
            <Skeleton className="h-20 rounded-xl" />
            <Skeleton className="h-20 rounded-xl" />
            <Skeleton className="h-20 rounded-xl" />
          </div>
        </div>
        {/* Sticky CTA skeleton */}
        <div className="fixed bottom-0 inset-x-0 p-4 bg-card border-t">
          <Skeleton className="h-12 w-full rounded-full" />
        </div>
      </div>
    );
  }

  // ===================== ERROR / NOT FOUND =====================
  if (error || !product) {
    return (
      <div className="min-h-screen bg-background flex flex-col items-center justify-center gap-6 p-4" dir={isRTL ? 'rtl' : 'ltr'}>
        <div className="h-20 w-20 rounded-full bg-muted flex items-center justify-center">
          <Store className="h-10 w-10 text-muted-foreground" />
        </div>
        <h1 className="text-xl font-bold text-foreground">{l.notFound}</h1>
        <Button
          onClick={() => navigate('/')}
          variant="outline"
          className="rounded-full gap-2"
        >
          {isRTL ? <ChevronRight className="h-4 w-4" /> : <ChevronLeft className="h-4 w-4" />}
          {l.backToStore}
        </Button>
      </div>
    );
  }

  // ===================== MAIN RENDER =====================
  return (
    <div className="min-h-screen bg-background pb-24 md:pb-8" dir={isRTL ? 'rtl' : 'ltr'}>
      {/* ========== TOP BAR ========== */}
      <header className="sticky top-0 z-50 bg-card/95 backdrop-blur-md border-b shadow-sm">
        <div className="max-w-2xl mx-auto px-4 py-3 flex items-center justify-between">
          {/* Logo */}
          <button
            onClick={() => navigate('/')}
            className="flex items-center gap-2 hover:opacity-80 transition-opacity"
          >
            <span className="text-lg font-bold text-primary tracking-tight">
              Agro<span className="text-green-600">Yousfi</span>
            </span>
          </button>

          {/* Language selector */}
          <div className="relative">
            <button
              onClick={() => setLangMenuOpen(!langMenuOpen)}
              className="flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors bg-muted/60 rounded-full px-3 py-1.5"
            >
              <Globe className="h-4 w-4" />
              <span>{language.toUpperCase()}</span>
            </button>
            {langMenuOpen && (
              <>
                <div
                  className="fixed inset-0 z-40"
                  onClick={() => setLangMenuOpen(false)}
                />
                <div className="absolute top-full mt-1 end-0 z-50 bg-card border rounded-xl shadow-lg overflow-hidden min-w-[140px]">
                  {languages.map((lang) => (
                    <button
                      key={lang.code}
                      onClick={() => {
                        setLanguage(lang.code);
                        setLangMenuOpen(false);
                      }}
                      className={`w-full text-start px-4 py-2.5 text-sm hover:bg-muted transition-colors ${
                        language === lang.code
                          ? 'bg-primary/5 text-primary font-medium'
                          : 'text-foreground'
                      }`}
                    >
                      {lang.label}
                    </button>
                  ))}
                </div>
              </>
            )}
          </div>
        </div>
      </header>

      <main className="max-w-2xl mx-auto">
        {/* ========== HERO IMAGE ========== */}
        <section className="relative">
          <ImageGallery images={product.images || []} productName={name} />

          {/* Discount badge overlay */}
          {showDiscount && (
            <div className="absolute top-3 left-3">
              <Badge className="bg-red-600 hover:bg-red-600 text-white text-sm font-bold px-3 py-1 rounded-lg shadow-lg">
                -{discountPercent}% {l.off}
              </Badge>
            </div>
          )}
        </section>

        {/* ========== PRODUCT INFO ========== */}
        <section className="px-4 pt-5 pb-4 space-y-4">
          {/* Name */}
          <h1 className="text-2xl font-bold text-foreground leading-tight">
            {name}
          </h1>

          {/* Rating */}
          {product.rating != null && (
            <StarRating
              rating={product.rating}
              count={product.reviews_count}
              reviewsLabel={l.reviews}
            />
          )}

          {/* Price block */}
          <div className="flex items-end gap-3 flex-wrap">
            <span className="text-3xl font-extrabold text-red-600">
              {formatPrice(displayPrice)}
            </span>
            {showDiscount && originalPrice && (
              <span className="text-lg text-muted-foreground line-through">
                {formatPrice(originalPrice)}
              </span>
            )}
            {showDiscount && (
              <Badge
                variant="secondary"
                className="bg-red-50 dark:bg-red-950/30 text-red-600 border-red-200 dark:border-red-800 text-xs font-semibold"
              >
                -{discountPercent}%
              </Badge>
            )}
          </div>

          {/* Stock indicator */}
          {isOutOfStock ? (
            <Badge variant="secondary" className="bg-gray-100 text-gray-500 text-sm">
              {l.outOfStock}
            </Badge>
          ) : (
            <div className="flex items-center gap-1.5 text-sm text-green-700 dark:text-green-400">
              <div className="h-2 w-2 rounded-full bg-green-500 animate-pulse" />
              <span className="font-medium">{l.inStock}</span>
              {product.unit && (
                <span className="text-muted-foreground">
                  &mdash; {product.stock} {product.unit}
                </span>
              )}
            </div>
          )}

          {/* Primary CTA */}
          <Button
            size="lg"
            onClick={handleOrderNow}
            disabled={isOutOfStock || ctaLoading}
            className="w-full h-14 text-lg font-bold rounded-2xl bg-green-600 hover:bg-green-700 text-white shadow-lg shadow-green-600/25 transition-all duration-200 active:scale-[0.98]"
          >
            {ctaLoading ? (
              <Loader2 className="h-5 w-5 animate-spin me-2" />
            ) : (
              <ShoppingCart className="h-5 w-5 me-2" />
            )}
            {l.orderNow}
          </Button>
        </section>

        {/* ========== TRUST INDICATORS ========== */}
        <section className="px-4 pb-5">
          <div className="grid grid-cols-3 gap-3">
            <TrustItem icon={Truck} label={l.freeDelivery} />
            <TrustItem icon={Shield} label={l.qualityGuarantee} />
            <TrustItem icon={RotateCcw} label={l.easyReturns} />
          </div>
        </section>

        {/* ========== DESCRIPTION ========== */}
        {description && (
          <section className="px-4 pb-6">
            <div className="bg-card border rounded-2xl p-5">
              <h2 className="text-base font-bold text-foreground mb-3">
                {l.description}
              </h2>
              <p className="text-sm text-muted-foreground leading-relaxed whitespace-pre-line">
                {displayDesc}
              </p>
              {isDescLong && (
                <button
                  onClick={() => setDescExpanded(!descExpanded)}
                  className="mt-2 text-sm font-semibold text-primary hover:underline transition-colors"
                >
                  {descExpanded ? l.readLess : l.readMore}
                </button>
              )}
            </div>
          </section>
        )}

        {/* ========== SECOND CTA ========== */}
        <section className="px-4 pb-8">
          <Button
            size="lg"
            onClick={handleOrderNow}
            disabled={isOutOfStock || ctaLoading}
            className="w-full h-14 text-lg font-bold rounded-2xl bg-green-600 hover:bg-green-700 text-white shadow-lg shadow-green-600/25 transition-all duration-200 active:scale-[0.98]"
          >
            {ctaLoading ? (
              <Loader2 className="h-5 w-5 animate-spin me-2" />
            ) : (
              <ShoppingCart className="h-5 w-5 me-2" />
            )}
            {l.orderNow}
          </Button>
        </section>

        {/* ========== RELATED PRODUCTS ========== */}
        {relatedProducts.length > 0 && (
          <section className="pb-6">
            <h2 className="text-base font-bold text-foreground px-4 mb-3">
              {l.relatedProducts}
            </h2>
            <div
              className="flex gap-3 px-4 overflow-x-auto no-scrollbar"
              style={{ scrollSnapType: 'x mandatory' }}
            >
              {relatedProducts.map((rp) => (
                <RelatedProductCard
                  key={rp.product_id}
                  product={rp}
                  language={language}
                  isRTL={isRTL}
                  onClick={() => navigate(`/p/${rp.product_id}`)}
                />
              ))}
            </div>
          </section>
        )}

        {/* ========== MINIMAL FOOTER ========== */}
        <footer className="border-t bg-card/50">
          <div className="px-4 py-6 flex items-center justify-between">
            <div>
              <span className="text-sm font-semibold text-foreground">
                Agro<span className="text-green-600">Yousfi</span>
              </span>
              <p className="text-xs text-muted-foreground mt-0.5">
                &copy; {new Date().getFullYear()} {l.allRightsReserved}
              </p>
            </div>
            <a
              href="https://wa.me/213555000000"
              target="_blank"
              rel="noopener noreferrer"
              className="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-full px-4 py-2.5 transition-colors shadow-md"
            >
              <MessageCircle className="h-4 w-4" />
              <span>{l.contactUs}</span>
            </a>
          </div>
        </footer>
      </main>

      {/* ========== STICKY MOBILE CTA ========== */}
      <div className="fixed bottom-0 inset-x-0 z-50 md:hidden">
        <div className="bg-card/95 backdrop-blur-md border-t shadow-[0_-4px_20px_rgba(0,0,0,0.08)] px-4 py-3 safe-area-pb">
          <div className="max-w-2xl mx-auto flex items-center gap-3">
            {/* Price summary */}
            <div className="flex-shrink-0">
              <span className="text-lg font-extrabold text-red-600 block leading-tight">
                {formatPrice(displayPrice)}
              </span>
              {showDiscount && originalPrice && (
                <span className="text-xs text-muted-foreground line-through">
                  {formatPrice(originalPrice)}
                </span>
              )}
            </div>
            {/* CTA button */}
            <Button
              size="lg"
              onClick={handleOrderNow}
              disabled={isOutOfStock || ctaLoading}
              className="flex-1 h-12 text-base font-bold rounded-xl bg-green-600 hover:bg-green-700 text-white shadow-md transition-all duration-200 active:scale-[0.98]"
            >
              {ctaLoading ? (
                <Loader2 className="h-5 w-5 animate-spin me-2" />
              ) : (
                <ShoppingCart className="h-5 w-5 me-2" />
              )}
              {l.orderNow}
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProductLandingPage;
