import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { ProductCard } from '../components/products/ProductCard';
import { Heart, ShoppingBag } from 'lucide-react';

const WishlistPage: React.FC = () => {
  const { colors } = useTheme();
  const { t, isRTL } = useLanguage();
  const [wishlist, setWishlist] = useState<any[]>([]);

  useEffect(() => {
    // Load wishlist from localStorage
    try {
      const saved = localStorage.getItem('wishlist');
      if (saved) {
        setWishlist(JSON.parse(saved));
      }
    } catch {}
  }, []);

  const removeFromWishlist = (productId: string | number) => {
    const updated = wishlist.filter(p => (p.id || p.product_id) !== productId);
    setWishlist(updated);
    localStorage.setItem('wishlist', JSON.stringify(updated));
  };

  return (
    <div className="py-6 min-h-screen" style={{ backgroundColor: colors.background }}>
      <div className="container mx-auto px-4">
        <h1 className="text-2xl font-bold mb-6" style={{ color: colors.foreground }}>
          {isRTL ? 'قائمة الأمنيات' : 'Wishlist'}
          {wishlist.length > 0 && (
            <span className="text-base font-normal mr-2" style={{ color: colors.mutedForeground }}>
              ({wishlist.length})
            </span>
          )}
        </h1>

        {wishlist.length === 0 ? (
          <div className="text-center py-20">
            <Heart className="h-16 w-16 mx-auto mb-4 opacity-20" style={{ color: colors.foreground }} />
            <p className="text-lg font-medium mb-2" style={{ color: colors.foreground }}>
              {isRTL ? 'قائمة الأمنيات فارغة' : 'Your wishlist is empty'}
            </p>
            <p className="text-sm mb-6" style={{ color: colors.mutedForeground }}>
              {isRTL ? 'أضف منتجات إلى قائمة الأمنيات لحفظها لاحقاً' : 'Add products to your wishlist to save them for later'}
            </p>
            <Link
              to="/products"
              className="inline-flex items-center gap-2 px-6 py-3 text-white font-medium"
              style={{ backgroundColor: colors.primary, borderRadius: colors.buttonRadius }}
            >
              <ShoppingBag className="h-4 w-4" />
              {isRTL ? 'تصفح المنتجات' : 'Browse Products'}
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {wishlist.map((product: any) => (
              <ProductCard key={product.id || product.product_id} product={product} />
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default WishlistPage;
