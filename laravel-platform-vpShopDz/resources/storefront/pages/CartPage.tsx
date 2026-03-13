import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { useCart } from '../contexts/CartContext';
import { getImageUrl, getProductName } from '../lib/utils';
import {
  ShoppingBag, Trash2, Minus, Plus, ArrowRight, ArrowLeft, ShoppingCart
} from 'lucide-react';

const CartPage: React.FC = () => {
  const { colors } = useTheme();
  const { t, language, isRTL, formatPrice } = useLanguage();
  const { items, cartTotal, cartCount, updateQuantity, removeFromCart, clearCart, loading } = useCart();
  const navigate = useNavigate();

  if (items.length === 0) {
    return (
      <div className="py-20" style={{ backgroundColor: colors.background }}>
        <div className="container mx-auto px-4 text-center">
          <ShoppingBag className="h-20 w-20 mx-auto mb-4 opacity-20" style={{ color: colors.foreground }} />
          <h2 className="text-2xl font-bold mb-2" style={{ color: colors.foreground }}>{t('cart.empty')}</h2>
          <p className="mb-6" style={{ color: colors.mutedForeground }}>{t('cart.emptyDesc')}</p>
          <Link
            to="/products"
            className="inline-flex items-center gap-2 px-6 py-3 text-white font-semibold transition-opacity hover:opacity-90"
            style={{ backgroundColor: colors.primary, borderRadius: colors.buttonRadius }}
          >
            {t('cart.continueShopping')}
            {isRTL ? <ArrowLeft className="h-4 w-4" /> : <ArrowRight className="h-4 w-4" />}
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="py-6" style={{ backgroundColor: colors.background }}>
      <div className="container mx-auto px-4">
        <h1 className="text-2xl font-bold mb-6" style={{ color: colors.foreground }}>
          {t('cart.title')} ({cartCount} {cartCount === 1 ? t('cart.item') : t('cart.items')})
        </h1>

        <div className="grid lg:grid-cols-3 gap-6">
          {/* Cart Items */}
          <div className="lg:col-span-2 space-y-3">
            {items.map((item: any) => {
              const product = item.product || {};
              const name = getProductName(product, language);
              const image = product.images?.[0] || product.image;

              return (
                <div
                  key={item.product_id}
                  className="flex gap-4 p-4 rounded-xl border"
                  style={{ backgroundColor: colors.card, borderColor: colors.border, borderRadius: colors.cardRadius }}
                >
                  {/* Image */}
                  <Link to={`/products/${item.product_id}`} className="shrink-0">
                    <div className="w-20 h-20 md:w-24 md:h-24 rounded-lg overflow-hidden" style={{ backgroundColor: colors.muted }}>
                      <img src={getImageUrl(image)} alt={name} className="w-full h-full object-cover" />
                    </div>
                  </Link>

                  {/* Details */}
                  <div className="flex-1 flex flex-col justify-between">
                    <div>
                      <Link to={`/products/${item.product_id}`}>
                        <h3 className="font-medium text-sm line-clamp-2 hover:underline" style={{ color: colors.cardForeground }}>
                          {name}
                        </h3>
                      </Link>
                      <p className="text-sm font-bold mt-1" style={{ color: colors.primary }}>
                        {formatPrice(product.price || 0)}
                      </p>
                    </div>

                    <div className="flex items-center justify-between mt-2">
                      {/* Quantity Controls */}
                      <div className="flex items-center border rounded-lg" style={{ borderColor: colors.border }}>
                        <button
                          onClick={() => updateQuantity(item.product_id, Math.max(1, item.quantity - 1))}
                          className="h-8 w-8 flex items-center justify-center transition-colors"
                          style={{ color: colors.foreground }}
                          disabled={loading}
                        >
                          <Minus className="h-3 w-3" />
                        </button>
                        <span className="w-8 text-center text-sm font-medium" style={{ color: colors.foreground }}>
                          {item.quantity}
                        </span>
                        <button
                          onClick={() => updateQuantity(item.product_id, item.quantity + 1)}
                          className="h-8 w-8 flex items-center justify-center transition-colors"
                          style={{ color: colors.foreground }}
                          disabled={loading}
                        >
                          <Plus className="h-3 w-3" />
                        </button>
                      </div>

                      {/* Subtotal + Remove */}
                      <div className="flex items-center gap-3">
                        <span className="text-sm font-bold" style={{ color: colors.foreground }}>
                          {formatPrice((product.price || 0) * item.quantity)}
                        </span>
                        <button
                          onClick={() => removeFromCart(item.product_id)}
                          className="h-8 w-8 flex items-center justify-center rounded-lg text-red-500 hover:bg-red-50 transition-colors"
                          disabled={loading}
                        >
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              );
            })}

            {/* Clear Cart */}
            <div className="flex justify-between items-center pt-2">
              <Link
                to="/products"
                className="text-sm font-medium flex items-center gap-1"
                style={{ color: colors.primary }}
              >
                {isRTL ? <ArrowRight className="h-4 w-4" /> : <ArrowLeft className="h-4 w-4" />}
                {t('cart.continueShopping')}
              </Link>
              <button
                onClick={clearCart}
                className="text-sm text-red-500 hover:underline"
                disabled={loading}
              >
                {t('cart.clear')}
              </button>
            </div>
          </div>

          {/* Order Summary */}
          <div>
            <div
              className="p-6 rounded-xl border sticky top-20"
              style={{ backgroundColor: colors.card, borderColor: colors.border, borderRadius: colors.cardRadius }}
            >
              <h3 className="font-bold text-lg mb-4" style={{ color: colors.cardForeground }}>
                {t('checkout.orderSummary')}
              </h3>

              <div className="space-y-3 mb-6">
                <div className="flex justify-between text-sm">
                  <span style={{ color: colors.mutedForeground }}>{t('cart.subtotal')}</span>
                  <span className="font-medium" style={{ color: colors.cardForeground }}>{formatPrice(cartTotal)}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span style={{ color: colors.mutedForeground }}>{t('cart.shipping')}</span>
                  <span className="text-sm" style={{ color: colors.mutedForeground }}>
                    {isRTL ? 'يحسب عند الطلب' : 'Calculated at checkout'}
                  </span>
                </div>
                <div className="pt-3 border-t flex justify-between" style={{ borderColor: colors.border }}>
                  <span className="font-bold" style={{ color: colors.cardForeground }}>{t('cart.total')}</span>
                  <span className="font-bold text-lg" style={{ color: colors.primary }}>{formatPrice(cartTotal)}</span>
                </div>
              </div>

              <button
                onClick={() => navigate('/checkout')}
                className="w-full h-12 flex items-center justify-center gap-2 text-white font-semibold transition-opacity hover:opacity-90"
                style={{ backgroundColor: colors.primary, borderRadius: colors.buttonRadius }}
              >
                <ShoppingCart className="h-5 w-5" />
                {t('cart.checkout')}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CartPage;
