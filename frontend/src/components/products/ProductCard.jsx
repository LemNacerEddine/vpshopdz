import React, { useState, useEffect, useMemo } from 'react';
import { Link } from 'react-router-dom';
import { useLanguage } from '@/contexts/LanguageContext';
import { useCart } from '@/contexts/CartContext';
import { useAuth } from '@/contexts/AuthContext';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Star, ShoppingCart, Clock, Heart } from 'lucide-react';
import { toast } from 'sonner';
import axios from 'axios';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

export const ProductCard = ({ product }) => {
  const { t, language, formatPrice, isRTL } = useLanguage();
  const { addToCart } = useCart();
  const { user, userId } = useAuth();
  const [timeLeft, setTimeLeft] = useState(null);
  const [isInWishlist, setIsInWishlist] = useState(false);
  const [wishlistLoading, setWishlistLoading] = useState(false);

  const name = product[`name_${language}`] || product.name_ar;
  
  // Memoize discount calculations to prevent infinite re-renders
  const discountData = useMemo(() => {
    const now = new Date();
    const discountStart = product.discount_start ? new Date(product.discount_start) : null;
    const discountEnd = product.discount_end ? new Date(product.discount_end) : null;
    
    // Determine if discount is currently active
    const isDiscountActive = product.discount_percent && product.discount_percent > 0 && (
      (!discountStart && !discountEnd) || // No date restriction
      (discountStart && discountEnd && now >= discountStart && now <= discountEnd) || // Within range
      (discountStart && !discountEnd && now >= discountStart) // Started but no end
    );

    // Calculate prices
    const originalPrice = product.price;
    const discountedPrice = isDiscountActive 
      ? originalPrice * (1 - product.discount_percent / 100) 
      : null;
    
    // Legacy discount support (old_price field)
    const hasLegacyDiscount = !isDiscountActive && product.old_price && product.old_price > product.price;
    const legacyDiscountPercent = hasLegacyDiscount 
      ? Math.round((1 - product.price / product.old_price) * 100) 
      : 0;

    // Determine which discount to show
    const hasDiscount = isDiscountActive || hasLegacyDiscount;
    const discountPercent = isDiscountActive ? product.discount_percent : legacyDiscountPercent;
    const displayPrice = isDiscountActive ? discountedPrice : product.price;
    const strikePrice = isDiscountActive ? originalPrice : (hasLegacyDiscount ? product.old_price : null);

    return { discountStart, discountEnd, isDiscountActive, hasDiscount, discountPercent, displayPrice, strikePrice };
  }, [product.discount_start, product.discount_end, product.discount_percent, product.price, product.old_price]);

  const { discountEnd, isDiscountActive, hasDiscount, discountPercent, displayPrice, strikePrice } = discountData;

  // Calculate time remaining for discount
  useEffect(() => {
    if (!isDiscountActive || !discountEnd) {
      setTimeLeft(null);
      return;
    }

    const updateTimeLeft = () => {
      const now = new Date();
      const diff = discountEnd - now;
      
      if (diff <= 0) {
        setTimeLeft(null);
        return;
      }

      const days = Math.floor(diff / (1000 * 60 * 60 * 24));
      const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

      if (days > 0) {
        setTimeLeft({ days, hours, type: 'days' });
      } else if (hours > 0) {
        setTimeLeft({ hours, minutes, type: 'hours' });
      } else {
        setTimeLeft({ minutes, type: 'minutes' });
      }
    };

    updateTimeLeft();
    const interval = setInterval(updateTimeLeft, 60000); // Update every minute

    return () => clearInterval(interval);
  }, [isDiscountActive, discountEnd]);

  // Check wishlist status on mount
  useEffect(() => {
    const checkWishlistStatus = async () => {
      if (!userId) return;
      try {
        const response = await axios.get(`${API}/wishlist`, { withCredentials: true });
        const inWishlist = response.data.some(item => item.product_id === product.product_id);
        setIsInWishlist(inWishlist);
      } catch (error) {
        // Silently fail - user might not be logged in
      }
    };
    checkWishlistStatus();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [userId, product.product_id]);

  const handleToggleWishlist = async (e) => {
    e.preventDefault();
    e.stopPropagation();
    
    if (!user) {
      toast.error(language === 'ar' ? 'يجب تسجيل الدخول أولاً' : 'Please login first');
      return;
    }

    try {
      setWishlistLoading(true);
      if (isInWishlist) {
        await axios.delete(`${API}/wishlist/${product.product_id}`, { withCredentials: true });
        setIsInWishlist(false);
        toast.success(language === 'ar' ? 'تمت الإزالة من المفضلة' : 'Removed from wishlist');
      } else {
        await axios.post(`${API}/wishlist/${product.product_id}`, {}, { withCredentials: true });
        setIsInWishlist(true);
        toast.success(language === 'ar' ? 'تمت الإضافة للمفضلة' : 'Added to wishlist');
      }
    } catch (error) {
      toast.error(error.response?.data?.detail || (language === 'ar' ? 'حدث خطأ' : 'Error'));
    } finally {
      setWishlistLoading(false);
    }
  };

  const handleAddToCart = async (e) => {
    e.preventDefault();
    e.stopPropagation();
    const success = await addToCart(product.product_id, 1);
    if (success) {
      toast.success(t('products.addToCart'), {
        description: name
      });
    }
  };

  const formatTimeLeft = () => {
    if (!timeLeft) return null;
    
    if (language === 'ar') {
      if (timeLeft.type === 'days') {
        return `${timeLeft.days} يوم ${timeLeft.hours} س`;
      } else if (timeLeft.type === 'hours') {
        return `${timeLeft.hours} س ${timeLeft.minutes} د`;
      } else {
        return `${timeLeft.minutes} دقيقة`;
      }
    } else if (language === 'fr') {
      if (timeLeft.type === 'days') {
        return `${timeLeft.days}j ${timeLeft.hours}h`;
      } else if (timeLeft.type === 'hours') {
        return `${timeLeft.hours}h ${timeLeft.minutes}m`;
      } else {
        return `${timeLeft.minutes} min`;
      }
    } else {
      if (timeLeft.type === 'days') {
        return `${timeLeft.days}d ${timeLeft.hours}h`;
      } else if (timeLeft.type === 'hours') {
        return `${timeLeft.hours}h ${timeLeft.minutes}m`;
      } else {
        return `${timeLeft.minutes} min`;
      }
    }
  };

  return (
    <Link to={`/products/${product.product_id}`} data-testid={`product-card-${product.product_id}`}>
      <Card className="product-card group h-full flex flex-col overflow-hidden">
        {/* Image */}
        <div className="relative aspect-square overflow-hidden bg-muted">
          <img
            src={product.images?.[0] || 'https://via.placeholder.com/300'}
            alt={name}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          />
          
          {/* Discount Badge */}
          {hasDiscount && (
            <div className={`absolute top-0 ${isRTL ? 'right-0' : 'left-0'}`}>
              <div className="bg-red-500 text-white px-3 py-1.5 text-sm font-bold shadow-lg"
                   style={{ 
                     clipPath: isRTL 
                       ? 'polygon(0 0, 100% 0, 100% 100%, 20% 100%)' 
                       : 'polygon(0 0, 100% 0, 80% 100%, 0 100%)' 
                   }}>
                -{discountPercent}%
              </div>
            </div>
          )}

          {/* Featured Badge */}
          {product.featured && !hasDiscount && (
            <div className={`absolute top-3 ${isRTL ? 'right-3' : 'left-3'}`}>
              <Badge className="bg-accent text-accent-foreground">
                {t('products.featured')}
              </Badge>
            </div>
          )}

          {/* Time Left Badge */}
          {timeLeft && (
            <div className={`absolute bottom-3 ${isRTL ? 'right-3' : 'left-3'}`}>
              <Badge variant="secondary" className="bg-black/70 text-white border-0 flex items-center gap-1">
                <Clock className="h-3 w-3" />
                <span className="text-xs">{formatTimeLeft()}</span>
              </Badge>
            </div>
          )}

          {/* Action Buttons Container */}
          <div className={`absolute bottom-3 ${isRTL ? 'left-3' : 'right-3'} flex ${isRTL ? 'flex-row-reverse' : 'flex-row'} gap-2 opacity-0 group-hover:opacity-100 transition-opacity`}>
            {/* Wishlist Button */}
            <Button
              size="icon"
              variant="secondary"
              className={`rounded-full shadow-lg ${isInWishlist ? 'bg-red-100 text-red-500 hover:bg-red-200' : 'bg-white/90 hover:bg-white'}`}
              onClick={handleToggleWishlist}
              disabled={wishlistLoading}
              data-testid={`wishlist-${product.product_id}`}
            >
              <Heart className={`h-4 w-4 ${isInWishlist ? 'fill-red-500' : ''}`} />
            </Button>
            
            {/* Quick Add to Cart Button */}
            <Button
              size="icon"
              className="rounded-full bg-primary hover:bg-primary/90 shadow-lg"
              onClick={handleAddToCart}
              disabled={product.stock === 0}
              data-testid={`add-to-cart-${product.product_id}`}
            >
              <ShoppingCart className="h-4 w-4" />
            </Button>
          </div>
        </div>

        {/* Content */}
        <div className="p-4 flex flex-col flex-1">
          <h3 className="font-semibold text-foreground line-clamp-2 mb-2 group-hover:text-primary transition-colors">
            {name}
          </h3>

          {/* Rating */}
          {product.rating > 0 && (
            <div className="flex items-center gap-1 mb-2">
              <Star className="h-4 w-4 fill-accent text-accent" />
              <span className="text-sm font-medium">{product.rating}</span>
              <span className="text-xs text-muted-foreground">
                ({product.reviews_count} {t('products.reviews')})
              </span>
            </div>
          )}

          {/* Price */}
          <div className="mt-auto">
            <div className="flex items-center gap-2 flex-wrap">
              <span className={`text-lg font-bold ${hasDiscount ? 'text-red-500' : 'text-primary'}`}>
                {formatPrice(displayPrice)}
              </span>
              {strikePrice && (
                <span className="text-sm text-muted-foreground line-through">
                  {formatPrice(strikePrice)}
                </span>
              )}
            </div>
            
            {/* Savings Amount */}
            {hasDiscount && strikePrice && (
              <p className="text-xs text-green-600 font-medium mt-1">
                {language === 'ar' ? 'وفّر ' : language === 'fr' ? 'Économisez ' : 'Save '}
                {formatPrice(strikePrice - displayPrice)}
              </p>
            )}
          </div>

          {/* Stock Status */}
          <div className="mt-2">
            {product.stock > 0 ? (
              <span className="text-xs text-green-600 font-medium">
                {t('products.inStock')}
              </span>
            ) : (
              <span className="text-xs text-destructive font-medium">
                {t('products.outOfStock')}
              </span>
            )}
          </div>
        </div>
      </Card>
    </Link>
  );
};

export default ProductCard;
