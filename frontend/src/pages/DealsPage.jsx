import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { ProductCard } from '@/components/products/ProductCard';
import { Skeleton } from '@/components/ui/skeleton';
import { Badge } from '@/components/ui/badge';
import { Flame, Clock, Percent } from 'lucide-react';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const DealsPage = () => {
  const { t, language, isRTL } = useLanguage();
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);

  const texts = {
    ar: {
      title: 'العروض والتخفيضات',
      subtitle: 'أفضل العروض على المنتجات الزراعية',
      noDeals: 'لا توجد عروض حالياً',
      checkLater: 'تابعنا للحصول على أفضل العروض',
      limitedTime: 'عروض محدودة الوقت',
      discountBadge: 'خصم'
    },
    fr: {
      title: 'Offres et Promotions',
      subtitle: 'Les meilleures offres sur les produits agricoles',
      noDeals: 'Aucune offre disponible',
      checkLater: 'Suivez-nous pour les meilleures offres',
      limitedTime: 'Offres à durée limitée',
      discountBadge: 'Remise'
    },
    en: {
      title: 'Deals & Offers',
      subtitle: 'Best deals on agricultural products',
      noDeals: 'No deals available',
      checkLater: 'Follow us for the best deals',
      limitedTime: 'Limited Time Offers',
      discountBadge: 'Discount'
    }
  };

  const text = texts[language] || texts.ar;

  useEffect(() => {
    fetchDeals();
  }, []);

  const fetchDeals = async () => {
    try {
      setLoading(true);
      const response = await axios.get(`${API}/products-on-sale`);
      setProducts(response.data);
    } catch (error) {
      console.error('Error fetching deals:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-6">
        {/* Header Skeleton */}
        <div className="text-center mb-8">
          <Skeleton className="h-10 w-64 mx-auto mb-2" />
          <Skeleton className="h-5 w-96 mx-auto" />
        </div>
        
        {/* Products Grid Skeleton */}
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
          {[...Array(12)].map((_, i) => (
            <div key={i} className="space-y-2">
              <Skeleton className="aspect-square rounded-lg" />
              <Skeleton className="h-4 w-3/4" />
              <Skeleton className="h-4 w-1/2" />
            </div>
          ))}
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-b from-red-50/50 to-background dark:from-red-950/10">
      <div className="container mx-auto px-4 py-6">
        {/* Header */}
        <div className="text-center mb-8">
          <div className="inline-flex items-center gap-2 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 px-4 py-2 rounded-full mb-4">
            <Flame className="h-5 w-5 animate-pulse" />
            <span className="font-semibold">{text.limitedTime}</span>
            <Flame className="h-5 w-5 animate-pulse" />
          </div>
          <h1 className="text-3xl md:text-4xl font-bold mb-2">{text.title}</h1>
          <p className="text-muted-foreground">{text.subtitle}</p>
        </div>

        {/* Products Grid */}
        {products.length > 0 ? (
          <div className={`grid gap-3 ${
            products.length === 1 
              ? 'grid-cols-1 max-w-xs mx-auto' 
              : products.length === 2 
                ? 'grid-cols-2 max-w-lg mx-auto'
                : products.length === 3
                  ? 'grid-cols-3 max-w-2xl mx-auto'
                  : 'grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6'
          }`}>
            {products.map((product) => (
              <ProductCard key={product.product_id} product={product} />
            ))}
          </div>
        ) : (
          <div className="text-center py-16">
            <div className="w-24 h-24 mx-auto mb-4 bg-muted rounded-full flex items-center justify-center">
              <Percent className="h-12 w-12 text-muted-foreground" />
            </div>
            <h3 className="text-xl font-semibold mb-2">{text.noDeals}</h3>
            <p className="text-muted-foreground">{text.checkLater}</p>
          </div>
        )}
      </div>
    </div>
  );
};

export default DealsPage;
