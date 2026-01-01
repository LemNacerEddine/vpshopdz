import React from 'react';
import { Link } from 'react-router-dom';
import { useLanguage } from '../../contexts/LanguageContext';
import { useCart } from '../../contexts/CartContext';
import { Card } from '../ui/card';
import { Button } from '../ui/button';
import { Badge } from '../ui/badge';
import { Star, ShoppingCart } from 'lucide-react';
import { toast } from 'sonner';

export const ProductCard = ({ product }) => {
  const { t, language, formatPrice, isRTL } = useLanguage();
  const { addToCart } = useCart();

  const name = product[`name_${language}`] || product.name_ar;
  const hasDiscount = product.old_price && product.old_price > product.price;
  const discountPercent = hasDiscount 
    ? Math.round((1 - product.price / product.old_price) * 100) 
    : 0;

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

  return (
    <Link to={`/products/${product.product_id}`} data-testid={`product-card-${product.product_id}`}>
      <Card className="product-card group h-full flex flex-col">
        {/* Image */}
        <div className="relative aspect-square overflow-hidden bg-muted">
          <img
            src={product.images?.[0] || 'https://via.placeholder.com/300'}
            alt={name}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          />
          
          {/* Badges */}
          <div className={`absolute top-3 ${isRTL ? 'right-3' : 'left-3'} flex flex-col gap-2`}>
            {hasDiscount && (
              <Badge className="bg-secondary text-white">
                -{discountPercent}%
              </Badge>
            )}
            {product.featured && (
              <Badge className="bg-accent text-accent-foreground">
                {t('products.featured')}
              </Badge>
            )}
          </div>

          {/* Quick Add Button */}
          <Button
            size="icon"
            className={`absolute bottom-3 ${isRTL ? 'left-3' : 'right-3'} opacity-0 group-hover:opacity-100 transition-opacity rounded-full bg-primary hover:bg-primary/90 shadow-lg`}
            onClick={handleAddToCart}
            disabled={product.stock === 0}
            data-testid={`add-to-cart-${product.product_id}`}
          >
            <ShoppingCart className="h-4 w-4" />
          </Button>
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
          <div className="mt-auto flex items-center gap-2">
            <span className="text-lg font-bold text-primary">
              {formatPrice(product.price)}
            </span>
            {hasDiscount && (
              <span className="text-sm text-muted-foreground line-through">
                {formatPrice(product.old_price)}
              </span>
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
