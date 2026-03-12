import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useStore } from '../../contexts/StoreContext';
import { useTheme } from '../../contexts/ThemeContext';
import { useLanguage } from '../../contexts/LanguageContext';
import { getImageUrl } from '../../lib/utils';
import { ChevronLeft, ChevronRight, ArrowRight, ArrowLeft } from 'lucide-react';

interface HeroSectionProps {
  style?: 'slider' | 'banner' | 'split' | 'video';
  slides?: any[];
}

export const HeroSection: React.FC<HeroSectionProps> = ({ style, slides: propSlides }) => {
  const { store, getSetting } = useStore();
  const { colors, layout } = useTheme();
  const { t, language, isRTL } = useLanguage();
  const [currentSlide, setCurrentSlide] = useState(0);

  const heroStyle = style || layout.heroStyle || 'slider';

  const slides = propSlides || getSetting('hero.slides', [
    {
      title: t('hero.title'),
      subtitle: t('hero.subtitle'),
      cta: t('hero.cta'),
      link: '/products',
      image: null,
    },
  ]);

  // Auto-advance slider
  useEffect(() => {
    if (heroStyle !== 'slider' || slides.length <= 1) return;
    const interval = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % slides.length);
    }, 5000);
    return () => clearInterval(interval);
  }, [heroStyle, slides.length]);

  const goToSlide = (index: number) => setCurrentSlide(index);
  const nextSlide = () => setCurrentSlide((prev) => (prev + 1) % slides.length);
  const prevSlide = () => setCurrentSlide((prev) => (prev - 1 + slides.length) % slides.length);

  // Banner style
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
            {isRTL ? <ArrowLeft className="h-5 w-5" /> : <ArrowRight className="h-5 w-5" />}
          </Link>
        </div>
      </section>
    );
  }

  // Split style
  if (heroStyle === 'split') {
    const slide = slides[0] || {};
    return (
      <section className="py-8 md:py-16" style={{ backgroundColor: colors.background }}>
        <div className="container mx-auto px-4">
          <div className={`grid md:grid-cols-2 gap-8 items-center ${isRTL ? '' : ''}`}>
            <div className={`${isRTL ? 'order-1' : 'order-1'}`}>
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
                {isRTL ? <ArrowLeft className="h-5 w-5" /> : <ArrowRight className="h-5 w-5" />}
              </Link>
            </div>
            <div className={`${isRTL ? 'order-2' : 'order-2'}`}>
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

  // Default: Slider style
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

            {/* Content overlay */}
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
                    {isRTL ? <ArrowLeft className="h-4 w-4" /> : <ArrowRight className="h-4 w-4" />}
                  </Link>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Navigation arrows */}
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

          {/* Dots */}
          <div className="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2">
            {slides.map((_: any, index: number) => (
              <button
                key={index}
                onClick={() => goToSlide(index)}
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
