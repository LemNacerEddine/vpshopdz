import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useStore } from '../contexts/StoreContext';
import { useTheme } from '../contexts/ThemeContext';
import { useLanguage } from '../contexts/LanguageContext';
import { useCart } from '../contexts/CartContext';
import { api } from '../lib/api';
import { toast } from 'sonner';
import { ShoppingCart, Loader2, CheckCircle } from 'lucide-react';

const AbandonedCartPage: React.FC = () => {
  const { token } = useParams<{ token: string }>();
  const { apiBase } = useStore();
  const { colors } = useTheme();
  const { isRTL } = useLanguage();
  const { addToCart } = useCart();
  const navigate = useNavigate();

  const [status, setStatus] = useState<'loading' | 'success' | 'error'>('loading');
  const [message, setMessage] = useState('');

  useEffect(() => {
    if (!token) {
      setStatus('error');
      setMessage(isRTL ? 'رابط الاسترداد غير صحيح' : 'Invalid recovery link');
      return;
    }

    const recoverCart = async () => {
      try {
        const res = await api.get(`${apiBase}/cart/recover/${token}`);
        const items: { product_id: string; quantity: number; variant_id?: string }[] =
          res.data?.items || res.data?.data?.items || [];

        if (items.length === 0) {
          setStatus('error');
          setMessage(isRTL ? 'لا توجد منتجات في هذه السلة' : 'No products found in this cart');
          return;
        }

        // Add all recovered items to cart
        for (const item of items) {
          await addToCart(item.product_id, item.quantity, item.variant_id);
        }

        setStatus('success');
        toast.success(isRTL ? 'تم استرداد سلة التسوق' : 'Cart recovered successfully');

        // Redirect to checkout after short delay
        setTimeout(() => navigate('/checkout'), 1500);
      } catch {
        setStatus('error');
        setMessage(isRTL ? 'الرابط منتهي الصلاحية أو غير صحيح' : 'Link has expired or is invalid');
      }
    };

    recoverCart();
  }, [token, apiBase]);

  return (
    <div className="min-h-[70vh] flex items-center justify-center px-4">
      <div
        className="w-full max-w-md rounded-2xl shadow-lg border p-10 text-center"
        style={{ backgroundColor: colors.card, borderColor: colors.border }}
      >
        {status === 'loading' && (
          <>
            <Loader2
              className="h-14 w-14 animate-spin mx-auto mb-4"
              style={{ color: colors.primary }}
            />
            <h2 className="text-lg font-bold mb-2" style={{ color: colors.foreground }}>
              {isRTL ? 'جاري استرداد سلة التسوق...' : 'Recovering your cart...'}
            </h2>
            <p className="text-sm" style={{ color: colors.mutedForeground }}>
              {isRTL ? 'يرجى الانتظار' : 'Please wait'}
            </p>
          </>
        )}

        {status === 'success' && (
          <>
            <CheckCircle
              className="h-14 w-14 mx-auto mb-4 text-green-500"
            />
            <h2 className="text-lg font-bold mb-2" style={{ color: colors.foreground }}>
              {isRTL ? 'تم استرداد السلة!' : 'Cart Recovered!'}
            </h2>
            <p className="text-sm" style={{ color: colors.mutedForeground }}>
              {isRTL ? 'جاري تحويلك إلى صفحة الدفع...' : 'Redirecting to checkout...'}
            </p>
          </>
        )}

        {status === 'error' && (
          <>
            <ShoppingCart
              className="h-14 w-14 mx-auto mb-4 opacity-30"
              style={{ color: colors.mutedForeground }}
            />
            <h2 className="text-lg font-bold mb-2" style={{ color: colors.foreground }}>
              {isRTL ? 'تعذر استرداد السلة' : 'Could not recover cart'}
            </h2>
            <p className="text-sm mb-6" style={{ color: colors.mutedForeground }}>
              {message}
            </p>
            <button
              onClick={() => navigate('/')}
              className="px-6 py-2.5 rounded-xl text-white font-medium transition-opacity hover:opacity-90"
              style={{ backgroundColor: colors.primary }}
            >
              {isRTL ? 'العودة للمتجر' : 'Back to Store'}
            </button>
          </>
        )}
      </div>
    </div>
  );
};

export default AbandonedCartPage;
