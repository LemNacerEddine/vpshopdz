import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { api } from '../lib/api';
import { getImageUrl, getCategoryName } from '../lib/utils';
import { HeroSection } from '../components/layout/HeroSection';
import { ProductCard } from '../components/products/ProductCard';
import {
  ArrowRight, ArrowLeft, Shield, Truck, Headphones, BadgePercent,
  ChevronRight, ChevronLeft, Flame,
} from 'lucide-react';

const HomePage: React.FC = () => {
  const { apiBase, storeName, getSetting } = useStore();
  const { colors, layout } = useTheme();
  const { t, language, isRTL } = useLanguage();

  const [categories, setCategories] = useState<any[]>([]);
  const [featuredProducts, setFeaturedProducts] = useState<any[]>([]);
  const [newProducts, setNewProducts] = useState<any[]>([]);
  const [dealProducts, setDealProducts] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const [catRes, featRes, newRes, dealRes] = await Promise.all([
          api.get(`${apiBase}/categories`).catch(() => ({ data: [] })),
          api.get(`${apiBase}/products`, { params: { featured: 1, limit: 8 } }).catch(() => ({ data: { data: [] } })),
          api.get(`${apiBase}/products`, { params: { sort: 'newest', limit: 8 } }).catch(() => ({ data: { data: [] } })),
          api.get(`${apiBase}/products`, { params: { deals: 1, limit: 8 } }).catch(() => ({ data: { data: [] } })),
        ]);
        setCategories(catRes.data?.data || catRes.data || []);
        setFeaturedProducts(featRes.data?.data || featRes.data || []);
        setNewProducts(newRes.data?.data || newRes.data || []);
        setDealProducts(dealRes.data?.data || dealRes.data || []);
      } catch (error) {
        console.error('Error fetching homepage data:', error);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [apiBase]);

  const gridCols = layout.gridColumns || 4;
  const gridClass = `grid grid-cols-2 md:grid-cols-3 lg:grid-cols-${gridCols} gap-4`;
  const ArrowIcon = isRTL ? ArrowRight : ArrowLeft;
  const ChevronIcon = isRTL ? ChevronLeft : ChevronRight;

  const SectionHeader: React.FC<{
    title: string;
    link?: string;
    linkText?: string;
    icon?: React.ElementType;
  }> = ({ title, link, linkText, icon: Icon }) => (
    <div className={`flex items-center justify-between mb-6 ${isRTL ? 'flex-row-reverse' : ''}`}>
      <h2 className="flex items-center gap-2 text-xl md:text-2xl font-bold" style={{ color: colors.foreground }}>
        {Icon && <Icon className="h-6 w-6" style={{ color: colors.primary }} />}
        {title}
      </h2>
      {link && (
        <Link
          to={link}
          className="flex items-center gap-1 text-sm font-medium transition-opacity hover:opacity-80"
          style={{ color: colors.primary }}
        >
          {linkText || t('hero.viewAll')}
          <ChevronIcon className="h-4 w-4" />
        </Link>
      )}
    </div>
  );

  const whyUsItems = [
    { icon: Truck, title: t('whyUs.delivery'), desc: t('whyUs.deliveryDesc') },
    { icon: Shield, title: t('whyUs.quality'), desc: t('whyUs.qualityDesc') },
    { icon: BadgePercent, title: t('whyUs.prices'), desc: t('whyUs.pricesDesc') },
    { icon: Headphones, title: t('whyUs.support'), desc: t('whyUs.supportDesc') },
  ];

  const showWhyUs = layout.showWhyUs !== false;

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin h-8 w-8 border-4 rounded-full" style={{ borderColor: colors.muted, borderTopColor: colors.primary }} />
      </div>
    );
  }

  return (
    <div>
      {/* 1. Hero Section */}
      <HeroSection />

      {/* 2. Categories */}
      {categories.length > 0 && (
        <section className="py-8 md:py-12" style={{ backgroundColor: colors.background }}>
          <div className="container mx-auto px-4">
            <SectionHeader
              title={t('nav.categories')}
              link="/products"
              linkText={t('hero.viewAll')}
            />
            <div className="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
              {categories.slice(0, 6).map((cat: any) => (
                <Link
                  key={cat.id}
                  to={`/category/${cat.id}`}
                  className="group flex flex-col items-center gap-2 p-3 rounded-xl transition-all hover:shadow-md"
                  style={{ backgroundColor: colors.card, border: `1px solid ${colors.border}` }}
                >
                  <div
                    className="w-16 h-16 rounded-full overflow-hidden flex items-center justify-center transition-transform group-hover:scale-110"
                    style={{ backgroundColor: colors.muted }}
                  >
                    {cat.image ? (
                      <img src={getImageUrl(cat.image)} alt={getCategoryName(cat, language)} className="w-full h-full object-cover" />
                    ) : (
                      <span className="text-2xl">📦</span>
                    )}
                  </div>
                  <span className="text-xs font-medium text-center line-clamp-2" style={{ color: colors.foreground }}>
                    {getCategoryName(cat, language)}
                  </span>
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* 3. New Arrivals — وصل حديثاً */}
      {newProducts.length > 0 && (
        <section className="py-8 md:py-12" style={{ backgroundColor: colors.muted }}>
          <div className="container mx-auto px-4">
            <SectionHeader
              title={t('products.newArrivals')}
              link="/products?sort=newest"
              linkText={t('hero.viewAll')}
            />
            <div className={gridClass}>
              {newProducts.map((product: any) => (
                <ProductCard key={product.id || product.product_id} product={product} />
              ))}
            </div>
          </div>
        </section>
      )}

      {/* 4. Deals — عروض مميزة */}
      {dealProducts.length > 0 && (
        <section className="py-8 md:py-12" style={{ backgroundColor: colors.background }}>
          <div className="container mx-auto px-4">
            <SectionHeader
              title={t('products.specialOffers')}
              link="/deals"
              linkText={t('hero.viewAll')}
              icon={Flame}
            />
            <div className={gridClass}>
              {dealProducts.map((product: any) => (
                <ProductCard key={product.id || product.product_id} product={product} />
              ))}
            </div>
          </div>
        </section>
      )}

      {/* 5. Featured Products — منتجاتنا + زر عرض الكل */}
      {featuredProducts.length > 0 && (
        <section className="py-8 md:py-12" style={{ backgroundColor: colors.muted }}>
          <div className="container mx-auto px-4">
            <SectionHeader
              title={language === 'ar' ? 'منتجاتنا' : language === 'fr' ? 'Nos Produits' : 'Our Products'}
              link="/products"
            />
            <div className={gridClass}>
              {featuredProducts.map((product: any) => (
                <ProductCard key={product.id || product.product_id} product={product} />
              ))}
            </div>
            {/* Big centered View All button */}
            <div className="flex justify-center mt-8">
              <Link
                to="/products"
                className="inline-flex items-center gap-2 px-8 py-3 rounded-full font-semibold text-white transition-transform hover:scale-105 shadow-md"
                style={{ backgroundColor: colors.primary }}
              >
                <ArrowIcon className="h-5 w-5" />
                {t('hero.viewAll')}
              </Link>
            </div>
          </div>
        </section>
      )}

      {/* 6. Why Us — لماذا تختار... (آخر قسم، قابل للإخفاء) */}
      {showWhyUs && (
        <section className="py-12 md:py-16" style={{ backgroundColor: colors.primary }}>
          <div className="container mx-auto px-4">
            <h2 className="text-xl md:text-2xl font-bold text-center mb-10 text-white">
              {language === 'ar'
                ? `لماذا تختار ${storeName}؟`
                : language === 'fr'
                  ? `Pourquoi choisir ${storeName} ?`
                  : `Why Choose ${storeName}?`}
            </h2>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-5">
              {whyUsItems.map((item, index) => (
                <div
                  key={index}
                  className="flex flex-col items-center text-center p-5 rounded-2xl"
                  style={{ backgroundColor: 'rgba(255,255,255,0.12)' }}
                >
                  <div
                    className="h-14 w-14 rounded-2xl flex items-center justify-center mb-3"
                    style={{ backgroundColor: 'rgba(255,255,255,0.15)' }}
                  >
                    <item.icon className="h-7 w-7 text-white" />
                  </div>
                  <h3 className="font-bold text-sm mb-1 text-white">{item.title}</h3>
                  <p className="text-xs leading-relaxed" style={{ color: 'rgba(255,255,255,0.75)' }}>{item.desc}</p>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}
    </div>
  );
};

export default HomePage;
