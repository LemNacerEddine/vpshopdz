import React, { useState, useEffect, useCallback } from 'react';
import { Link } from 'react-router-dom';
import { useStore } from '../../contexts/StoreContext';
import { useTheme } from '../../contexts/ThemeContext';
import { useLanguage } from '../../contexts/LanguageContext';
import { api } from '../../lib/api';
import { getImageUrl, getProductName, calculateDiscount } from '../../lib/utils';
import { ChevronLeft, ChevronRight, ArrowRight, ArrowLeft, Star } from 'lucide-react';

interface HeroSectionProps {
  style?: 'slider' | 'banner' | 'split' | 'video' | 'product-split';
  slides?: any[];
}

export const HeroSection: React.FC<HeroSectionProps> = ({ style, slides: propSlides }) => {
  const { store, apiBase, getSetting } = useStore();
  const { colors, layout } = useTheme();
  const { t, language, isRTL, formatPrice } = useLanguage();
  const [currentSlide, setCurrentSlide] = useState(0);
  const [heroProducts, setHeroProducts] = useState<any[]>([]);
  const [loadingProducts, setLoadingProducts] = useState(true);

  const heroStyle = style || layout.heroStyle || 'product-split';

  const slides = propSlides || getSetting('hero.slides', [
    {
      title: t('hero.title'),
      subtitle: t('hero.subtitle'),
      cta: t('hero.cta'),
      link: '/products',
      image: null,
    },
  ]);

  // Fetch products for product-split style
  useEffect(() => {
    if (heroStyle !== 'product-split') { setLoadingProducts(false); return; }
    const fetchHeroProducts = async () => {
      try {
        // Try discounted products first
        const res = await api.get(`${apiBase}/products?sale=true&limit=6`);
        const products = res.data?.data || res.data || [];
        if (products.length > 0) {
          setHeroProducts(products.slice(0, 5));
        } else {
          // Fallback to featured
          const featRes = await api.get(`${apiBase}/products?featured=true&limit=6`);
          setHeroProducts((featRes.data?.data || featRes.data || []).slice(0, 5));
        }
      } catch {
        try {
          const res = await api.get(`${apiBase}/products?limit=6`);
          setHeroProducts((res.data?.data || res.data || []).slice(0, 5));
        } catch { /* ignore */ }
      } finally {
        setLoadingProducts(false);
      }
    };
    fetchHeroProducts();
  }, [apiBase, heroStyle]);

  // Auto-advance
  useEffect(() => {
    const count = heroStyle === 'product-split' ? heroProducts.length : slides.length;
    if (count <= 1) return;
    const interval = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % count);
    }, 4000);
    return () => clearInterval(interval);
  }, [heroStyle, heroProducts.length, slides.length]);

  const nextSlide = useCallback(() => {
    const count = heroStyle === 'product-split' ? heroProducts.length : slides.length;
    setCurrentSlide((prev) => (prev + 1) % count);
  }, [heroStyle, heroProducts.length, slides.length]);

  const prevSlide = useCallback(() => {
    const count = heroStyle === 'product-split' ? heroProducts.length : slides.length;
    setCurrentSlide((prev) => (prev - 1 + count) % count);
  }, [heroStyle, heroProducts.length, slides.length]);

  const PrevIcon = isRTL ? ChevronRight : ChevronLeft;
  const NextIcon = isRTL ? ChevronLeft : ChevronRight;
  const ArrowIcon = isRTL ? ArrowLeft : ArrowRight;

  // ── PRODUCT-SPLIT style (agro-yousfi style) ──────────────────────────────
  if (heroStyle === 'product-split') {
    const slide = slides[0] || {};
    const currentProduct = heroProducts[currentSlide];

    const getDiscount = (p: any): number => {
      if (p?.discount_percent > 0) return p.discount_percent;
      if (p?.old_price && p.old_price > p.price) return calculateDiscount(p.price, p.old_price);
      return 0;
    };

    const getImg = (p: any): string | null => {
      if (!p) return null;
      const imgs = p.images || [];
      if (imgs.length === 0) return null;
      const first = imgs[0];
      return typeof first === 'string' ? first : (first?.url || first?.path || null);
    };

    return (
      <section
        className="relative overflow-hidden py-10 md:py-16"
        style={{ background: `linear-gradient(135deg, ${colors.primary}08 0%, ${colors.background} 60%)` }}
      >
        <div className="container mx-auto px-4">
          <div className={`grid lg:grid-cols-2 gap-8 lg:gap-12 items-center ${isRTL ? '' : ''}`}>

            {/* Text Content */}
            <div className={`space-y-5 ${isRTL ? 'lg:order-2 text-right' : 'text-left'}`}>
              {/* Welcome badge */}
              <span
                className="inline-block text-sm font-medium px-4 py-1.5 rounded-full"
                style={{ backgroundColor: `${colors.primary}20`, color: colors.primary }}
              >
                🌿 {language === 'ar' ? 'مرحباً بكم في متجرنا' : language === 'fr' ? 'Bienvenue dans notre boutique' : 'Welcome to our store'}
              </span>

              <h1 className="text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight" style={{ color: colors.foreground }}>
                {slide.title || t('hero.title')}
              </h1>

              <p className="text-base lg:text-lg" style={{ color: colors.mutedForeground }}>
                {slide.subtitle || t('hero.subtitle')}
              </p>

              <div className="flex flex-wrap gap-3 pt-2">
                <Link
                  to={slide.link || '/products'}
                  className="inline-flex items-center gap-2 px-7 py-3 rounded-full text-white font-semibold transition-transform hover:scale-105 shadow-md"
                  style={{ backgroundColor: colors.primary }}
                >
                  {slide.cta || t('hero.cta')}
                  <ArrowIcon className="h-5 w-5" />
                </Link>
                <Link
                  to="/products"
                  className="inline-flex items-center gap-2 px-7 py-3 rounded-full font-semibold border-2 transition-colors hover:opacity-80"
                  style={{ borderColor: colors.border, color: colors.foreground }}
                >
                  {language === 'ar' ? 'عرض الكل' : language === 'fr' ? 'Voir tout' : 'View All'}
                </Link>
              </div>
            </div>

            {/* Product Carousel */}
            <div className={`relative ${isRTL ? 'lg:order-1' : ''}`}>
              {loadingProducts ? (
                <div
                  className="aspect-square max-w-lg mx-auto rounded-3xl animate-pulse"
                  style={{ backgroundColor: colors.muted }}
                />
              ) : heroProducts.length > 0 && currentProduct ? (
                <div className="relative max-w-lg mx-auto">
                  {/* Main card */}
                  <Link
                    to={`/products/${currentProduct.id || currentProduct.product_id}`}
                    className="block relative aspect-square rounded-3xl overflow-hidden shadow-2xl group"
                    style={{ backgroundColor: colors.muted }}
                  >
                    {/* Product Image */}
                    {getImg(currentProduct) ? (
                      <img
                        src={getImageUrl(getImg(currentProduct)!)}
                        alt={getProductName(currentProduct, language)}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                      />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center text-8xl opacity-20">
                        🛍️
                      </div>
                    )}

                    {/* Gradient overlay */}
                    <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent" />

                    {/* Discount badge */}
                    {getDiscount(currentProduct) > 0 && (
                      <div className={`absolute top-4 ${isRTL ? 'left-4' : 'right-4'}`}>
                        <span className="px-3 py-1.5 rounded-full text-sm font-bold text-white"
                          style={{ backgroundColor: colors.accent }}>
                          -{getDiscount(currentProduct)}%
                        </span>
                      </div>
                    )}

                    {/* Product info overlay */}
                    <div className={`absolute bottom-0 left-0 right-0 p-5 text-white ${isRTL ? 'text-right' : 'text-left'}`}>
                      <h3 className="text-lg sm:text-xl font-bold mb-2 line-clamp-2">
                        {getProductName(currentProduct, language)}
                      </h3>
                      <div className="flex items-center gap-3 mb-2 flex-wrap">
                        <span className="text-2xl font-bold" style={{ color: '#fff' }}>
                          {formatPrice(currentProduct.price)}
                        </span>
                        {(currentProduct.old_price || currentProduct.original_price) && (
                          <span className="text-base text-white/60 line-through">
                            {formatPrice(currentProduct.old_price || currentProduct.original_price)}
                          </span>
                        )}
                      </div>
                      {currentProduct.rating > 0 && (
                        <div className="flex items-center gap-1.5">
                          <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                          <span className="font-medium text-sm">{currentProduct.rating}</span>
                          {currentProduct.reviews_count > 0 && (
                            <span className="text-white/60 text-xs">
                              ({currentProduct.reviews_count} {t('products.reviews')})
                            </span>
                          )}
                        </div>
                      )}
                    </div>
                  </Link>

                  {/* Navigation arrows */}
                  {heroProducts.length > 1 && (
                    <>
                      <button
                        onClick={prevSlide}
                        className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? '-right-4 sm:-right-5' : '-left-4 sm:-left-5'} h-11 w-11 rounded-full shadow-lg flex items-center justify-center transition-colors z-10`}
                        style={{ backgroundColor: colors.card, color: colors.foreground }}
                        onMouseEnter={(e) => { (e.currentTarget as HTMLElement).style.backgroundColor = colors.muted; }}
                        onMouseLeave={(e) => { (e.currentTarget as HTMLElement).style.backgroundColor = colors.card; }}
                      >
                        <PrevIcon className="h-5 w-5" />
                      </button>
                      <button
                        onClick={nextSlide}
                        className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? '-left-4 sm:-left-5' : '-right-4 sm:-right-5'} h-11 w-11 rounded-full shadow-lg flex items-center justify-center transition-colors z-10`}
                        style={{ backgroundColor: colors.card, color: colors.foreground }}
                        onMouseEnter={(e) => { (e.currentTarget as HTMLElement).style.backgroundColor = colors.muted; }}
                        onMouseLeave={(e) => { (e.currentTarget as HTMLElement).style.backgroundColor = colors.card; }}
                      >
                        <NextIcon className="h-5 w-5" />
                      </button>
                    </>
                  )}

                  {/* Dots */}
                  {heroProducts.length > 1 && (
                    <div className="flex justify-center gap-2 mt-4">
                      {heroProducts.map((_: any, index: number) => (
                        <button
                          key={index}
                          onClick={() => setCurrentSlide(index)}
                          className="h-2 rounded-full transition-all"
                          style={{
                            width: index === currentSlide ? '2rem' : '0.5rem',
                            backgroundColor: index === currentSlide ? colors.primary : `${colors.mutedForeground}40`,
                          }}
                        />
                      ))}
                    </div>
                  )}
                </div>
              ) : (
                // Fallback: colored box
                <div
                  className="relative aspect-square max-w-lg mx-auto rounded-3xl flex items-center justify-center"
                  style={{ backgroundColor: `${colors.primary}15` }}
                >
                  <span className="text-8xl opacity-30">🛍️</span>
                </div>
              )}
            </div>
          </div>
        </div>
      </section>
    );
  }

  // ── BANNER style ─────────────────────────────────────────────────────────
  if (heroStyle === 'banner') {
    const slide = slides[0] || {};
    return (
      <section
        className="relative py-16 md:py-24 overflow-hidden"
        style={{ backgroundColor: colors.primary }}
      >
        <div className="absolute inset-0 opacity-10">
          <div className="absolute inset-0" style={{
            backgroundImage: 'radial-gradient(circle at 25% 25%, rgba(255,255,255,0.3) 0%, transparent 50%)',
          }} />
        </div>
        <div className="container mx-auto px-4 relative z-10 text-center">
          <h1 className="text-3xl md:text-5xl font-bold text-white mb-4 leading-tight">
            {slide.title || t('hero.title')}
          </h1>
          <p className="text-lg md:text-xl text-white/80 mb-8 max-w-2xl mx-auto">
            {slide.subtitle || t('hero.subtitle')}
          </p>
          <Link
            to={slide.link || '/products'}
            className="inline-flex items-center gap-2 px-8 py-3 rounded-full text-lg font-semibold transition-transform hover:scale-105"
            style={{ backgroundColor: colors.accent, color: '#fff', borderRadius: colors.buttonRadius }}
          >
            {slide.cta || t('hero.cta')}
            <ArrowIcon className="h-5 w-5" />
          </Link>
        </div>
      </section>
    );
  }

  // ── SPLIT style ───────────────────────────────────────────────────────────
  if (heroStyle === 'split') {
    const slide = slides[0] || {};
    return (
      <section className="py-8 md:py-16" style={{ backgroundColor: colors.background }}>
        <div className="container mx-auto px-4">
          <div className={`grid md:grid-cols-2 gap-8 items-center`}>
            <div className={isRTL ? 'order-2' : 'order-1'}>
              <h1 className="text-3xl md:text-5xl font-bold mb-4 leading-tight" style={{ color: colors.foreground }}>
                {slide.title || t('hero.title')}
              </h1>
              <p className="text-lg mb-8" style={{ color: colors.mutedForeground }}>
                {slide.subtitle || t('hero.subtitle')}
              </p>
              <Link
                to={slide.link || '/products'}
                className="inline-flex items-center gap-2 px-8 py-3 text-lg font-semibold text-white transition-transform hover:scale-105"
                style={{ backgroundColor: colors.primary, borderRadius: colors.buttonRadius }}
              >
                {slide.cta || t('hero.cta')}
                <ArrowIcon className="h-5 w-5" />
              </Link>
            </div>
            <div className={isRTL ? 'order-1' : 'order-2'}>
              {slide.image ? (
                <img
                  src={getImageUrl(slide.image)}
                  alt=""
                  className="w-full rounded-2xl shadow-2xl"
                />
              ) : (
                <div
                  className="w-full aspect-[4/3] rounded-2xl flex items-center justify-center"
                  style={{ backgroundColor: colors.muted }}
                >
                  <span className="text-6xl opacity-20">🛍️</span>
                </div>
              )}
            </div>
          </div>
        </div>
      </section>
    );
  }

  // ── Default: SLIDER style ─────────────────────────────────────────────────
  return (
    <section className="relative overflow-hidden" style={{ backgroundColor: colors.muted }}>
      <div className="relative h-[300px] md:h-[450px]">
        {slides.map((slide: any, index: number) => (
          <div
            key={index}
            className={`absolute inset-0 transition-opacity duration-700 ${index === currentSlide ? 'opacity-100 z-10' : 'opacity-0 z-0'}`}
          >
            {slide.image ? (
              <img src={getImageUrl(slide.image)} alt="" className="w-full h-full object-cover" />
            ) : (
              <div className="w-full h-full" style={{ backgroundColor: colors.primary }}>
                <div className="absolute inset-0 opacity-10">
                  <div className="absolute inset-0" style={{
                    backgroundImage: `radial-gradient(circle at ${isRTL ? '75%' : '25%'} 50%, rgba(255,255,255,0.4) 0%, transparent 60%)`,
                  }} />
                </div>
              </div>
            )}
            <div className="absolute inset-0 flex items-center">
              <div className="container mx-auto px-4">
                <div className={`max-w-lg ${isRTL ? 'mr-0 ml-auto text-right' : 'ml-0 mr-auto text-left'}`}>
                  <h2 className="text-2xl md:text-4xl font-bold text-white mb-3 leading-tight drop-shadow-lg">
                    {slide.title || t('hero.title')}
                  </h2>
                  <p className="text-sm md:text-lg text-white/90 mb-6 drop-shadow">
                    {slide.subtitle || t('hero.subtitle')}
                  </p>
                  <Link
                    to={slide.link || '/products'}
                    className="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white transition-transform hover:scale-105 shadow-lg"
                    style={{ backgroundColor: colors.accent, borderRadius: colors.buttonRadius }}
                  >
                    {slide.cta || t('hero.cta')}
                    <ArrowIcon className="h-4 w-4" />
                  </Link>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>

      {slides.length > 1 && (
        <>
          <button
            onClick={isRTL ? nextSlide : prevSlide}
            className="absolute left-4 top-1/2 -translate-y-1/2 z-20 h-10 w-10 rounded-full bg-white/30 hover:bg-white/50 flex items-center justify-center text-white backdrop-blur-sm transition-colors"
          >
            <ChevronLeft className="h-5 w-5" />
          </button>
          <button
            onClick={isRTL ? prevSlide : nextSlide}
            className="absolute right-4 top-1/2 -translate-y-1/2 z-20 h-10 w-10 rounded-full bg-white/30 hover:bg-white/50 flex items-center justify-center text-white backdrop-blur-sm transition-colors"
          >
            <ChevronRight className="h-5 w-5" />
          </button>
          <div className="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2">
            {slides.map((_: any, index: number) => (
              <button
                key={index}
                onClick={() => setCurrentSlide(index)}
                className={`h-2 rounded-full transition-all ${index === currentSlide ? 'w-6 bg-white' : 'w-2 bg-white/50'}`}
              />
            ))}
          </div>
        </>
      )}
    </section>
  );
};

export default HeroSection;
