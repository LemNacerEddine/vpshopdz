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
  ChevronRight, ChevronLeft
} from 'lucide-react';

const HomePage: React.FC = () => {
  const { apiBase, getSetting } = useStore();
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

  const SectionHeader: React.FC<{ title: string; link?: string; linkText?: string }> = ({ title, link, linkText }) => (
    <div className="flex items-center justify-between mb-6">
      <h2 className="text-xl md:text-2xl font-bold" style={{ color: colors.foreground }}>{title}</h2>
      {link && (
        <Link
          to={link}
          className="flex items-center gap-1 text-sm font-medium transition-opacity hover:opacity-80"
          style={{ color: colors.primary }}
        >
          {linkText || t('hero.viewAll')}
          {isRTL ? <ChevronLeft className="h-4 w-4" /> : <ChevronRight className="h-4 w-4" />}
        </Link>
      )}
    </div>
  );

  const whyUsItems = [
    { icon: Shield, title: t('whyUs.quality'), desc: t('whyUs.qualityDesc') },
    { icon: Truck, title: t('whyUs.delivery'), desc: t('whyUs.deliveryDesc') },
    { icon: Headphones, title: t('whyUs.support'), desc: t('whyUs.supportDesc') },
    { icon: BadgePercent, title: t('whyUs.prices'), desc: t('whyUs.pricesDesc') },
  ];

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin h-8 w-8 border-4 rounded-full" style={{ borderColor: colors.muted, borderTopColor: colors.primary }} />
      </div>
    );
  }

  return (
    <div>
      {/* Hero Section */}
      <HeroSection />

      {/* Categories Section */}
      {categories.length > 0 && (
        <section className="py-8 md:py-12" style={{ backgroundColor: colors.background }}>
          <div className="container mx-auto px-4">
            <SectionHeader title={t('nav.categories')} link="/products" />
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

      {/* Featured Products */}
      {featuredProducts.length > 0 && (
        <section className="py-8 md:py-12" style={{ backgroundColor: colors.background }}>
          <div className="container mx-auto px-4">
            <SectionHeader title={t('products.featured')} link="/products?featured=1" />
            <div className={gridClass}>
              {featuredProducts.map((product: any) => (
                <ProductCard key={product.id || product.product_id} product={product} />
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Deals Section */}
      {dealProducts.length > 0 && (
        <section className="py-8 md:py-12" style={{ backgroundColor: colors.muted }}>
          <div className="container mx-auto px-4">
            <SectionHeader title={t('products.specialOffers')} link="/products?deals=1" />
            <div className={gridClass}>
              {dealProducts.map((product: any) => (
                <ProductCard key={product.id || product.product_id} product={product} />
              ))}
            </div>
          </div>
        </section>
      )}

      {/* Why Us Section */}
      <section className="py-12 md:py-16" style={{ backgroundColor: colors.background }}>
        <div className="container mx-auto px-4">
          <h2 className="text-xl md:text-2xl font-bold text-center mb-8" style={{ color: colors.foreground }}>
            {t('whyUs.title')}
          </h2>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            {whyUsItems.map((item, index) => (
              <div
                key={index}
                className="flex flex-col items-center text-center p-4 rounded-xl transition-shadow hover:shadow-md"
                style={{ backgroundColor: colors.card, border: `1px solid ${colors.border}`, borderRadius: colors.cardRadius }}
              >
                <div
                  className="h-14 w-14 rounded-full flex items-center justify-center mb-3"
                  style={{ backgroundColor: `${colors.primary}15` }}
                >
                  <item.icon className="h-7 w-7" style={{ color: colors.primary }} />
                </div>
                <h3 className="font-semibold text-sm mb-1" style={{ color: colors.foreground }}>{item.title}</h3>
                <p className="text-xs leading-relaxed" style={{ color: colors.mutedForeground }}>{item.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* New Arrivals */}
      {newProducts.length > 0 && (
        <section className="py-8 md:py-12" style={{ backgroundColor: colors.muted }}>
          <div className="container mx-auto px-4">
            <SectionHeader title={t('products.newArrivals')} link="/products?sort=newest" />
            <div className={gridClass}>
              {newProducts.map((product: any) => (
                <ProductCard key={product.id || product.product_id} product={product} />
              ))}
            </div>
          </div>
        </section>
      )}
    </div>
  );
};

export default HomePage;
