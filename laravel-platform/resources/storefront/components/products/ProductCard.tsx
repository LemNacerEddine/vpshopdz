import React, { useState, useMemo, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useTheme } from '../../contexts/ThemeContext';
import { useLanguage } from '../../contexts/LanguageContext';
import { useCart } from '../../contexts/CartContext';
import { useStore } from '../../contexts/StoreContext';
import { api } from '../../lib/api';
import { getImageUrl, getProductName, calculateDiscount } from '../../lib/utils';
import { ShoppingCart, Heart, Clock, Star, Eye } from 'lucide-react';
import { toast } from 'sonner';

interface ProductCardProps {
  product: any;
  style?: 'default' | 'minimal' | 'overlay' | 'horizontal';
}

export const ProductCard: React.FC<ProductCardProps> = ({ product, style }) => {
  const { colors, layout } = useTheme();
  const { t, language, isRTL, formatPrice } = useLanguage();
  const { addToCart } = useCart();
  const { apiBase } = useStore();
  const [isHovered, setIsHovered] = useState(false);
  const [timeLeft, setTimeLeft] = useState<any>(null);
  const [inWishlist, setInWishlist] = useState(false);

  const cardStyle = style || layout.productCardStyle || 'default';
  const name = getProductName(product, language);
  const image = product.images?.[0] || product.image;

  const discountData = useMemo(() => {
    const now = new Date();
    const discountStart = product.discount_start ? new Date(product.discount_start) : null;
    const discountEnd = product.discount_end ? new Date(product.discount_end) : null;

    const isActive = product.discount_percent && product.discount_percent > 0 && (
      (!discountStart && !discountEnd) ||
      (discountStart && discountEnd && now >= discountStart && now <= discountEnd) ||
      (discountStart && !discountEnd && now >= discountStart)
    );

    const originalPrice = product.price;
    const discountedPrice = isActive ? originalPrice * (1 - product.discount_percent / 100) : null;
    const hasLegacy = !isActive && product.old_price && product.old_price > product.price;
    const legacyPercent = hasLegacy ? Math.round((1 - product.price / product.old_price) * 100) : 0;

    const hasDiscount = isActive || hasLegacy;
    const percent = isActive ? product.discount_percent : legacyPercent;
    const displayPrice = isActive ? discountedPrice : product.price;
    const strikePrice = isActive ? originalPrice : (hasLegacy ? product.old_price : null);

    return { isActive, hasDiscount, percent, displayPrice, strikePrice, discountEnd };
  }, [product]);

  // Countdown timer
  useEffect(() => {
    if (!discountData.isActive || !discountData.discountEnd) return;
    const end = new Date(discountData.discountEnd);
    const update = () => {
      const diff = end.getTime() - Date.now();
      if (diff <= 0) { setTimeLeft(null); return; }
      const d = Math.floor(diff / 86400000);
      const h = Math.floor((diff % 86400000) / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      setTimeLeft(d > 0 ? `${d}d ${h}h` : h > 0 ? `${h}h ${m}m` : `${m}m`);
    };
    update();
    const interval = setInterval(update, 60000);
    return () => clearInterval(interval);
  }, [discountData]);

  const handleAddToCart = async (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    const success = await addToCart(product.id || product.product_id, 1);
    if (success) toast.success(t('products.addToCart'), { description: name });
  };

  const handleToggleWishlist = async (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    const newState = !inWishlist;
    setInWishlist(newState);
    try {
      const res = await api.post(`${apiBase}/wishlist/toggle`, {
        product_id: product.id || product.product_id,
      });
      setInWishlist(res.data?.added ?? newState);
      toast.success(res.data?.message || (newState ? 'تمت الإضافة للأمنيات' : 'تم الحذف من الأمنيات'));
    } catch {
      // keep optimistic update
    }
  };

  // Horizontal card style
  if (cardStyle === 'horizontal') {
    return (
      <Link to={`/products/${product.id || product.product_id}`}>
        <div
          className="flex gap-4 p-3 rounded-lg border transition-shadow hover:shadow-md"
          style={{ backgroundColor: colors.card, borderColor: colors.border, borderRadius: colors.cardRadius }}
        >
          <div className="relative w-32 h-32 shrink-0 rounded-lg overflow-hidden" style={{ backgroundColor: colors.muted }}>
            <img src={getImageUrl(image)} alt={name} className="w-full h-full object-cover" />
            {discountData.hasDiscount && (
              <span className="absolute top-1 left-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">
                -{discountData.percent}%
              </span>
            )}
          </div>
          <div className="flex-1 flex flex-col justify-between py-1">
            <div>
              <h3 className="font-medium text-sm line-clamp-2 mb-1" style={{ color: colors.cardForeground }}>{name}</h3>
              {product.rating > 0 && (
                <div className="flex items-center gap-1 mb-1">
                  <Star className="h-3 w-3 fill-yellow-400 text-yellow-400" />
                  <span className="text-xs">{product.rating}</span>
                </div>
              )}
            </div>
            <div className="flex items-center justify-between">
              <div>
                <span className={`text-sm font-bold ${discountData.hasDiscount ? 'text-red-500' : ''}`} style={{ color: discountData.hasDiscount ? undefined : colors.primary }}>
                  {formatPrice(discountData.displayPrice!)}
                </span>
                {discountData.strikePrice && (
                  <span className="text-xs line-through ml-2" style={{ color: colors.mutedForeground }}>
                    {formatPrice(discountData.strikePrice)}
                  </span>
                )}
              </div>
              <button
                onClick={handleAddToCart}
                className="h-8 w-8 rounded-full flex items-center justify-center text-white transition-opacity hover:opacity-90"
                style={{ backgroundColor: colors.primary }}
              >
                <ShoppingCart className="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </Link>
    );
  }

  // Overlay card style
  if (cardStyle === 'overlay') {
    return (
      <Link to={`/products/${product.id || product.product_id}`}>
        <div
          className="relative group overflow-hidden"
          style={{ borderRadius: colors.cardRadius }}
          onMouseEnter={() => setIsHovered(true)}
          onMouseLeave={() => setIsHovered(false)}
        >
          <div className="aspect-[3/4] overflow-hidden" style={{ backgroundColor: colors.muted }}>
            <img
              src={getImageUrl(image)}
              alt={name}
              className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
            />
          </div>

          {/* Overlay gradient */}
          <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />

          {/* Discount badge */}
          {discountData.hasDiscount && (
            <div className={`absolute top-3 ${isRTL ? 'right-3' : 'left-3'}`}>
              <span className="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                -{discountData.percent}%
              </span>
            </div>
          )}

          {/* Action buttons */}
          <div className={`absolute top-3 ${isRTL ? 'left-3' : 'right-3'} flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity`}>
            <button
              onClick={handleToggleWishlist}
              className="h-8 w-8 rounded-full bg-white/90 flex items-center justify-center hover:bg-white transition-colors"
            >
              <Heart className={`h-4 w-4 ${inWishlist ? 'fill-red-500 text-red-500' : 'text-gray-700'}`} />
            </button>
            <button className="h-8 w-8 rounded-full bg-white/90 flex items-center justify-center hover:bg-white transition-colors">
              <Eye className="h-4 w-4 text-gray-700" />
            </button>
          </div>

          {/* Content overlay */}
          <div className="absolute bottom-0 left-0 right-0 p-4 text-white">
            <h3 className="font-medium text-sm line-clamp-2 mb-2">{name}</h3>
            <div className="flex items-center justify-between">
              <div>
                <span className={`text-lg font-bold ${discountData.hasDiscount ? 'text-red-400' : ''}`}>
                  {formatPrice(discountData.displayPrice!)}
                </span>
                {discountData.strikePrice && (
                  <span className="text-xs line-through ml-2 opacity-70">
                    {formatPrice(discountData.strikePrice)}
                  </span>
                )}
              </div>
              <button
                onClick={handleAddToCart}
                className="h-9 w-9 rounded-full flex items-center justify-center text-white transition-opacity hover:opacity-90"
                style={{ backgroundColor: colors.primary }}
              >
                <ShoppingCart className="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </Link>
    );
  }

  // Minimal card style
  if (cardStyle === 'minimal') {
    return (
      <Link to={`/products/${product.id || product.product_id}`}>
        <div className="group">
          <div className="aspect-square overflow-hidden mb-3" style={{ backgroundColor: colors.muted, borderRadius: colors.cardRadius }}>
            <img
              src={getImageUrl(image)}
              alt={name}
              className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
          </div>
          <h3 className="font-medium text-sm line-clamp-1 mb-1 group-hover:underline" style={{ color: colors.foreground }}>
            {name}
          </h3>
          <div className="flex items-center gap-2">
            <span className={`text-sm font-bold ${discountData.hasDiscount ? 'text-red-500' : ''}`} style={{ color: discountData.hasDiscount ? undefined : colors.foreground }}>
              {formatPrice(discountData.displayPrice!)}
            </span>
            {discountData.strikePrice && (
              <span className="text-xs line-through" style={{ color: colors.mutedForeground }}>
                {formatPrice(discountData.strikePrice)}
              </span>
            )}
          </div>
        </div>
      </Link>
    );
  }

  // Default card style
  return (
    <Link to={`/products/${product.id || product.product_id}`}>
      <div
        className="group h-full flex flex-col overflow-hidden border transition-shadow hover:shadow-lg"
        style={{
          backgroundColor: colors.card,
          borderColor: colors.border,
          borderRadius: colors.cardRadius,
        }}
        onMouseEnter={() => setIsHovered(true)}
        onMouseLeave={() => setIsHovered(false)}
      >
        {/* Image */}
        <div className="relative aspect-[4/3] overflow-hidden" style={{ backgroundColor: colors.muted }}>
          <img
            src={getImageUrl(image)}
            alt={name}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          />

          {/* Discount Badge */}
          {discountData.hasDiscount && (
            <div className={`absolute top-0 ${isRTL ? 'right-0' : 'left-0'}`}>
              <div className="bg-red-500 text-white px-2 py-1 text-xs font-bold shadow-lg"
                style={{
                  clipPath: isRTL
                    ? 'polygon(0 0, 100% 0, 100% 100%, 20% 100%)'
                    : 'polygon(0 0, 100% 0, 80% 100%, 0 100%)'
                }}>
                -{discountData.percent}%
              </div>
            </div>
          )}

          {/* Featured Badge */}
          {product.featured && !discountData.hasDiscount && (
            <div className={`absolute top-2 ${isRTL ? 'right-2' : 'left-2'}`}>
              <span className="text-xs px-1.5 py-0.5 rounded font-medium" style={{ backgroundColor: colors.accent, color: '#fff' }}>
                {t('products.featured')}
              </span>
            </div>
          )}

          {/* Timer */}
          {timeLeft && (
            <div className={`absolute bottom-2 ${isRTL ? 'right-2' : 'left-2'}`}>
              <span className="bg-black/70 text-white text-[10px] px-1.5 py-0.5 rounded flex items-center gap-0.5">
                <Clock className="h-2.5 w-2.5" />
                {timeLeft}
              </span>
            </div>
          )}

          {/* Action Buttons */}
          <div className={`absolute bottom-2 ${isRTL ? 'left-2' : 'right-2'} flex gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity`}>
            <button
              onClick={handleToggleWishlist}
              className="h-7 w-7 rounded-full bg-white/90 hover:bg-white flex items-center justify-center shadow-lg transition-colors"
            >
              <Heart className={`h-3.5 w-3.5 ${inWishlist ? 'fill-red-500 text-red-500' : 'text-gray-600'}`} />
            </button>
            <button
              onClick={handleAddToCart}
              disabled={product.stock === 0}
              className="h-7 w-7 rounded-full flex items-center justify-center shadow-lg text-white transition-opacity hover:opacity-90"
              style={{ backgroundColor: colors.primary }}
            >
              <ShoppingCart className="h-3.5 w-3.5" />
            </button>
          </div>
        </div>

        {/* Content */}
        <div className="p-2.5 flex flex-col flex-1">
          <h3 className="font-medium text-sm line-clamp-2 mb-1 transition-colors leading-tight" style={{ color: colors.cardForeground }}>
            {name}
          </h3>

          {/* Rating */}
          {product.rating > 0 && (
            <div className="flex items-center gap-0.5 mb-1">
              <Star className="h-3 w-3 fill-yellow-400 text-yellow-400" />
              <span className="text-xs font-medium" style={{ color: colors.cardForeground }}>{product.rating}</span>
              <span className="text-[10px]" style={{ color: colors.mutedForeground }}>
                ({product.reviews_count || 0})
              </span>
            </div>
          )}

          {/* Price */}
          <div className="mt-auto">
            <div className="flex items-center gap-1.5 flex-wrap">
              <span className={`text-sm font-bold ${discountData.hasDiscount ? 'text-red-500' : ''}`} style={{ color: discountData.hasDiscount ? undefined : colors.primary }}>
                {formatPrice(discountData.displayPrice!)}
              </span>
              {discountData.strikePrice && (
                <span className="text-xs line-through" style={{ color: colors.mutedForeground }}>
                  {formatPrice(discountData.strikePrice)}
                </span>
              )}
            </div>
            {discountData.hasDiscount && discountData.strikePrice && (
              <p className="text-[10px] text-green-600 font-medium mt-0.5">
                {t('products.save')} {formatPrice(discountData.strikePrice - discountData.displayPrice!)}
              </p>
            )}
          </div>
        </div>
      </div>
    </Link>
  );
};

export default ProductCard;
