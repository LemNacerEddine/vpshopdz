import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useLanguage } from '@/contexts/LanguageContext';
import { useCart } from '@/contexts/CartContext';
import { Button } from '@/components/ui/button';
import { 
  ShoppingBag, 
  Minus, 
  Plus, 
  Trash2, 
  ChevronRight,
  ChevronLeft,
  ArrowRight,
  ArrowLeft
} from 'lucide-react';

export const CartPage = () => {
  const { t, language, isRTL, formatPrice } = useLanguage();
  const { cart, updateQuantity, removeFromCart, cartTotal, cartCount } = useCart();
  const navigate = useNavigate();

  const ChevronIcon = isRTL ? ChevronLeft : ChevronRight;
  const ArrowIcon = isRTL ? ArrowLeft : ArrowRight;

  if (cart.items.length === 0) {
    return (
      <div className="min-h-[60vh] flex flex-col items-center justify-center px-4" data-testid="empty-cart">
        <ShoppingBag className="h-24 w-24 text-muted-foreground/30 mb-6" />
        <h2 className="text-2xl font-bold text-foreground mb-2">{t('cart.empty')}</h2>
        <p className="text-muted-foreground mb-6">
          {language === 'ar' ? 'أضف بعض المنتجات للبدء' : 'Add some products to get started'}
        </p>
        <Link to="/products">
          <Button className="rounded-full px-8">
            {t('cart.continueShopping')}
            <ChevronIcon className="h-5 w-5 ms-1" />
          </Button>
        </Link>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-background" data-testid="cart-page">
      {/* Breadcrumb */}
      <div className="bg-muted/30 py-4">
        <div className="container mx-auto px-4">
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <Link to="/" className="hover:text-primary">{t('nav.home')}</Link>
            <ChevronIcon className="h-4 w-4" />
            <span className="text-foreground">{t('cart.title')}</span>
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold mb-8">{t('cart.title')}</h1>

        <div className="grid lg:grid-cols-3 gap-8">
          {/* Cart Items */}
          <div className="lg:col-span-2 space-y-4">
            {cart.items.map((item) => {
              const product = item.product;
              if (!product) return null;
              
              const name = product[`name_${language}`] || product.name_ar;
              
              return (
                <div 
                  key={item.product_id}
                  className="bg-card rounded-2xl p-4 flex gap-4 border"
                  data-testid={`cart-item-${item.product_id}`}
                >
                  {/* Image */}
                  <Link to={`/products/${item.product_id}`} className="shrink-0">
                    <div className="w-24 h-24 rounded-xl overflow-hidden bg-muted">
                      <img
                        src={product.images?.[0] || 'https://via.placeholder.com/100'}
                        alt={name}
                        className="w-full h-full object-cover"
                      />
                    </div>
                  </Link>

                  {/* Details */}
                  <div className="flex-1 min-w-0">
                    <Link to={`/products/${item.product_id}`}>
                      <h3 className="font-semibold text-foreground hover:text-primary truncate">
                        {name}
                      </h3>
                    </Link>
                    <p className="text-lg font-bold text-primary mt-1">
                      {formatPrice(product.price)}
                    </p>

                    <div className="flex items-center justify-between mt-3">
                      {/* Quantity */}
                      <div className="flex items-center border rounded-full">
                        <Button
                          variant="ghost"
                          size="icon"
                          className="h-8 w-8 rounded-full"
                          onClick={() => updateQuantity(item.product_id, item.quantity - 1)}
                        >
                          <Minus className="h-3 w-3" />
                        </Button>
                        <span className="w-8 text-center text-sm font-medium">{item.quantity}</span>
                        <Button
                          variant="ghost"
                          size="icon"
                          className="h-8 w-8 rounded-full"
                          onClick={() => updateQuantity(item.product_id, item.quantity + 1)}
                          disabled={item.quantity >= product.stock}
                        >
                          <Plus className="h-3 w-3" />
                        </Button>
                      </div>

                      {/* Remove */}
                      <Button
                        variant="ghost"
                        size="icon"
                        className="text-destructive hover:text-destructive hover:bg-destructive/10"
                        onClick={() => removeFromCart(item.product_id)}
                        data-testid={`remove-item-${item.product_id}`}
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>

                  {/* Item Total (Desktop) */}
                  <div className="hidden sm:block text-right">
                    <p className="text-sm text-muted-foreground">{t('cart.subtotal')}</p>
                    <p className="text-lg font-bold text-foreground">
                      {formatPrice(product.price * item.quantity)}
                    </p>
                  </div>
                </div>
              );
            })}
          </div>

          {/* Order Summary */}
          <div className="lg:col-span-1">
            <div className="bg-card rounded-3xl p-6 border sticky top-24">
              <h2 className="text-xl font-bold mb-6">
                {language === 'ar' ? 'ملخص الطلب' : language === 'fr' ? 'Résumé de la commande' : 'Order Summary'}
              </h2>

              <div className="space-y-4 mb-6">
                <div className="flex justify-between text-muted-foreground">
                  <span>{t('cart.subtotal')} ({cartCount} {language === 'ar' ? 'منتج' : 'items'})</span>
                  <span>{formatPrice(cartTotal)}</span>
                </div>
                <div className="flex justify-between text-muted-foreground">
                  <span>{t('cart.shipping')}</span>
                  <span className="text-xs">
                    {language === 'ar' ? 'يحسب عند الدفع' : language === 'fr' ? 'Calculé au paiement' : 'Calculated at checkout'}
                  </span>
                </div>
                <div className="border-t pt-4">
                  <div className="flex justify-between text-lg font-bold">
                    <span>{t('cart.total')}</span>
                    <span className="text-primary">{formatPrice(cartTotal)}</span>
                  </div>
                </div>
              </div>

              <Button 
                className="w-full rounded-full h-12 text-base"
                onClick={() => navigate('/checkout')}
                data-testid="checkout-btn"
              >
                {t('cart.checkout')}
                <ArrowIcon className="h-5 w-5 ms-2" />
              </Button>

              <Link to="/products" className="block mt-4">
                <Button variant="outline" className="w-full rounded-full">
                  {t('cart.continueShopping')}
                </Button>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CartPage;
