import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useCart } from '@/contexts/CartContext';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import { ProductCard } from '@/components/products/ProductCard';
import { 
  ChevronRight, 
  ChevronLeft,
  Leaf, 
  Droplets, 
  Wrench, 
  Shield, 
  Droplet, 
  Home,
  Truck,
  Headphones,
  BadgePercent,
  Star,
  Sparkles,
  Clock
} from 'lucide-react';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const iconMap = {
  'Leaf': Leaf,
  'Droplets': Droplets,
  'Wrench': Wrench,
  'Shield': Shield,
  'Droplet': Droplet,
  'Home': Home
};

export const HomePage = () => {
  const { t, language, isRTL, formatPrice } = useLanguage();
  const [categories, setCategories] = useState([]);
  const [featuredProducts, setFeaturedProducts] = useState([]);
  const [newArrivals, setNewArrivals] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        // Seed database first
        await axios.post(`${API}/seed`).catch(() => {});
        
        const [catRes, prodRes] = await Promise.all([
          axios.get(`${API}/categories`),
          axios.get(`${API}/products?limit=20`)
        ]);
        setCategories(catRes.data);
        
        const allProducts = prodRes.data;
        // Featured products (with discount or marked as featured)
        const featured = allProducts.filter(p => p.featured || p.old_price);
        setFeaturedProducts(featured.length > 0 ? featured.slice(0, 4) : allProducts.slice(0, 4));
        
        // New arrivals (latest products - different from featured)
        const arrivals = allProducts.filter(p => !p.featured && !p.old_price);
        setNewArrivals(arrivals.length > 0 ? arrivals.slice(0, 4) : allProducts.slice(4, 8));
      } catch (error) {
        console.error('Error fetching data:', error);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, []);

  const ChevronIcon = isRTL ? ChevronLeft : ChevronRight;

  const features = [
    {
      icon: Star,
      title: t('whyUs.quality'),
      description: t('whyUs.qualityDesc'),
      color: 'bg-accent/20 text-accent-foreground'
    },
    {
      icon: Truck,
      title: t('whyUs.delivery'),
      description: t('whyUs.deliveryDesc'),
      color: 'bg-secondary/20 text-secondary'
    },
    {
      icon: Headphones,
      title: t('whyUs.support'),
      description: t('whyUs.supportDesc'),
      color: 'bg-primary/20 text-primary'
    },
    {
      icon: BadgePercent,
      title: t('whyUs.prices'),
      description: t('whyUs.pricesDesc'),
      color: 'bg-accent/20 text-accent-foreground'
    }
  ];

  const newArrivalsTitle = {
    ar: 'وصل حديثاً',
    fr: 'Nouveautés',
    en: 'New Arrivals'
  };

  const newArrivalsSubtitle = {
    ar: 'أحدث المنتجات المضافة لمتجرنا',
    fr: 'Les derniers produits ajoutés à notre boutique',
    en: 'Latest products added to our store'
  };

  const offersTitle = {
    ar: 'عروض مميزة',
    fr: 'Offres Spéciales',
    en: 'Special Offers'
  };

  const offersSubtitle = {
    ar: 'خصومات حصرية على منتجات مختارة',
    fr: 'Remises exclusives sur des produits sélectionnés',
    en: 'Exclusive discounts on selected products'
  };

  return (
    <div className="min-h-screen" data-testid="home-page">
      {/* Hero Section */}
      <section className="relative overflow-hidden bg-gradient-to-b from-primary/5 to-background">
        <div className="container mx-auto px-4 py-16 lg:py-24">
          <div className="grid lg:grid-cols-2 gap-12 items-center">
            {/* Content */}
            <div className={`space-y-6 ${isRTL ? 'lg:order-2' : ''}`}>
              <Badge variant="secondary" className="px-4 py-1.5">
                🌱 {language === 'ar' ? 'مرحباً بكم في متجرنا' : language === 'fr' ? 'Bienvenue dans notre boutique' : 'Welcome to our store'}
              </Badge>
              
              <h1 className="text-4xl sm:text-5xl lg:text-6xl font-bold text-foreground leading-tight">
                {t('hero.title')}
              </h1>
              
              <p className="text-lg text-muted-foreground max-w-xl">
                {t('hero.subtitle')}
              </p>
              
              <div className="flex flex-wrap gap-4">
                <Link to="/products">
                  <Button size="lg" className="rounded-full px-8 shadow-soft" data-testid="hero-cta">
                    {t('hero.cta')}
                    <ChevronIcon className="h-5 w-5 ms-2" />
                  </Button>
                </Link>
                <Link to="/categories">
                  <Button variant="outline" size="lg" className="rounded-full px-8">
                    {t('categories.viewAll')}
                  </Button>
                </Link>
              </div>
            </div>

            {/* Hero Image */}
            <div className={`relative ${isRTL ? 'lg:order-1' : ''}`}>
              <div className="relative aspect-square max-w-lg mx-auto">
                <div className="absolute inset-0 bg-gradient-to-br from-primary/20 to-accent/20 rounded-3xl rotate-6"></div>
                <img
                  src="https://images.pexels.com/photos/5529765/pexels-photo-5529765.jpeg"
                  alt="Agriculture"
                  className="relative z-10 w-full h-full object-cover rounded-3xl shadow-soft-lg"
                />
                {/* Floating Cards */}
                <div className={`absolute -bottom-4 ${isRTL ? '-left-4' : '-right-4'} z-20 bg-card rounded-2xl p-4 shadow-lg`}>
                  <div className="flex items-center gap-3">
                    <div className="h-12 w-12 rounded-full bg-accent/20 flex items-center justify-center">
                      <Leaf className="h-6 w-6 text-primary" />
                    </div>
                    <div>
                      <p className="text-2xl font-bold text-primary">500+</p>
                      <p className="text-sm text-muted-foreground">{t('admin.totalProducts')}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Categories Section */}
      <section className="py-16 bg-muted/30">
        <div className="container mx-auto px-4">
          <div className="flex items-center justify-between mb-10">
            <div>
              <h2 className="text-3xl font-bold text-foreground mb-2">
                {t('categories.title')}
              </h2>
              <p className="text-muted-foreground">
                {t('categories.subtitle')}
              </p>
            </div>
            <Link to="/categories" className="hidden sm:block">
              <Button variant="outline" className="rounded-full">
                {t('categories.viewAll')}
                <ChevronIcon className="h-4 w-4 ms-1" />
              </Button>
            </Link>
          </div>

          {loading ? (
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
              {[...Array(6)].map((_, i) => (
                <Skeleton key={i} className="aspect-square rounded-3xl" />
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
              {categories.map((category, index) => {
                const IconComponent = iconMap[category.icon] || Leaf;
                const name = category[`name_${language}`] || category.name_ar;
                
                return (
                  <Link 
                    key={category.category_id} 
                    to={`/products?category=${category.category_id}`}
                    className="group"
                    data-testid={`category-${category.category_id}`}
                    style={{ animationDelay: `${index * 0.1}s` }}
                  >
                    <div className="category-card text-center h-full flex flex-col items-center justify-center aspect-square">
                      <div className="h-16 w-16 rounded-2xl bg-primary/10 group-hover:bg-primary/20 flex items-center justify-center mb-4 transition-colors">
                        <IconComponent className="h-8 w-8 text-primary" strokeWidth={1.5} />
                      </div>
                      <h3 className="font-semibold text-foreground group-hover:text-primary transition-colors">
                        {name}
                      </h3>
                    </div>
                  </Link>
                );
              })}
            </div>
          )}
        </div>
      </section>

      {/* New Arrivals Section */}
      <section className="py-16">
        <div className="container mx-auto px-4">
          <div className="flex items-center justify-between mb-10">
            <div className="flex items-center gap-3">
              <div className="h-12 w-12 rounded-2xl bg-secondary/10 flex items-center justify-center">
                <Sparkles className="h-6 w-6 text-secondary" />
              </div>
              <div>
                <h2 className="text-2xl font-bold text-foreground">
                  {newArrivalsTitle[language]}
                </h2>
                <p className="text-sm text-muted-foreground">
                  {newArrivalsSubtitle[language]}
                </p>
              </div>
            </div>
            <Link to="/products" className="hidden sm:block">
              <Button variant="outline" className="rounded-full">
                {t('categories.viewAll')}
                <ChevronIcon className="h-4 w-4 ms-1" />
              </Button>
            </Link>
          </div>

          {loading ? (
            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              {[...Array(4)].map((_, i) => (
                <Skeleton key={i} className="aspect-[3/4] rounded-2xl" />
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              {newArrivals.map((product) => (
                <ProductCard key={product.product_id} product={product} />
              ))}
            </div>
          )}
        </div>
      </section>

      {/* Special Offers / Featured Products */}
      <section className="py-16 bg-gradient-to-b from-accent/5 to-background">
        <div className="container mx-auto px-4">
          <div className="flex items-center justify-between mb-10">
            <div className="flex items-center gap-3">
              <div className="h-12 w-12 rounded-2xl bg-accent/20 flex items-center justify-center">
                <BadgePercent className="h-6 w-6 text-accent-foreground" />
              </div>
              <div>
                <h2 className="text-2xl font-bold text-foreground">
                  {offersTitle[language]}
                </h2>
                <p className="text-sm text-muted-foreground">
                  {offersSubtitle[language]}
                </p>
              </div>
            </div>
            <Link to="/products" className="hidden sm:block">
              <Button variant="outline" className="rounded-full">
                {t('categories.viewAll')}
                <ChevronIcon className="h-4 w-4 ms-1" />
              </Button>
            </Link>
          </div>

          {loading ? (
            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              {[...Array(4)].map((_, i) => (
                <Skeleton key={i} className="aspect-[3/4] rounded-2xl" />
              ))}
            </div>
          ) : (
            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              {featuredProducts.map((product) => (
                <ProductCard key={product.product_id} product={product} />
              ))}
            </div>
          )}

          <div className="text-center mt-10 sm:hidden">
            <Link to="/products">
              <Button className="rounded-full px-8">
                {t('categories.viewAll')}
                <ChevronIcon className="h-4 w-4 ms-1" />
              </Button>
            </Link>
          </div>
        </div>
      </section>

      {/* Why Choose Us */}
      <section className="py-16 bg-primary text-primary-foreground">
        <div className="container mx-auto px-4">
          <h2 className="text-3xl font-bold text-center mb-12">
            {t('whyUs.title')}
          </h2>
          
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-6">
            {features.map((feature, index) => (
              <div 
                key={index}
                className="text-center p-6 rounded-3xl bg-primary-foreground/5 hover:bg-primary-foreground/10 transition-colors"
              >
                <div className="h-16 w-16 rounded-2xl bg-primary-foreground/10 flex items-center justify-center mx-auto mb-4">
                  <feature.icon className="h-8 w-8" strokeWidth={1.5} />
                </div>
                <h3 className="font-semibold text-lg mb-2">{feature.title}</h3>
                <p className="text-sm text-primary-foreground/70">{feature.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-16">
        <div className="container mx-auto px-4">
          <div className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-secondary to-secondary/80 p-8 lg:p-12">
            <div className="relative z-10 max-w-2xl">
              <h2 className="text-3xl lg:text-4xl font-bold text-white mb-4">
                {language === 'ar' ? 'ابدأ مشروعك الزراعي اليوم' : language === 'fr' ? 'Commencez votre projet agricole aujourd\'hui' : 'Start your agricultural project today'}
              </h2>
              <p className="text-white/80 mb-6">
                {language === 'ar' ? 'نوفر لك كل ما تحتاجه من بذور وأسمدة وأدوات بأفضل الأسعار' : language === 'fr' ? 'Nous vous fournissons tout ce dont vous avez besoin: semences, engrais et outils aux meilleurs prix' : 'We provide everything you need: seeds, fertilizers and tools at the best prices'}
              </p>
              <Link to="/products">
                <Button size="lg" className="bg-white text-secondary hover:bg-white/90 rounded-full px-8">
                  {t('hero.cta')}
                  <ChevronIcon className="h-5 w-5 ms-2" />
                </Button>
              </Link>
            </div>
            {/* Decorative */}
            <div className="absolute top-0 right-0 w-1/2 h-full opacity-10">
              <Leaf className="w-full h-full" />
            </div>
          </div>
        </div>
      </section>
    </div>
  );
};

export default HomePage;
