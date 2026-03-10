import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useCart } from '@/contexts/CartContext';
import { useLanguage } from '@/contexts/LanguageContext';
import { Loader2, ShoppingCart, AlertCircle } from 'lucide-react';
import axios from 'axios';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;
const RECOVERY_OFFER_KEY = 'agroyousfi_recovery_offer';

const RecoverCheckoutPage = () => {
  const { checkoutId } = useParams();
  const navigate = useNavigate();
  const { addToCart, clearCart } = useCart();
  const { language } = useLanguage();
  const [status, setStatus] = useState('loading'); // loading | restoring | error
  const [error, setError] = useState('');

  const text = {
    ar: {
      loading: 'جاري تحميل طلبك...',
      restoring: 'جاري استعادة سلة التسوق...',
      error: 'لم يتم العثور على الطلب أو تم استرداده مسبقاً',
      redirect: 'جاري التوجيه لصفحة الدفع...',
    },
    fr: {
      loading: 'Chargement de votre commande...',
      restoring: 'Restauration de votre panier...',
      error: 'Commande introuvable ou déjà récupérée',
      redirect: 'Redirection vers le paiement...',
    },
    en: {
      loading: 'Loading your order...',
      restoring: 'Restoring your cart...',
      error: 'Order not found or already recovered',
      redirect: 'Redirecting to checkout...',
    },
  };
  const t = text[language] || text.ar;

  useEffect(() => {
    recoverCheckout();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [checkoutId]);

  const recoverCheckout = async () => {
    try {
      setStatus('loading');

      // Fetch abandoned checkout data + offer
      const res = await axios.get(`${API}/recover/${checkoutId}`);
      const data = res.data;

      setStatus('restoring');

      // Clear current cart
      await clearCart();

      // Add each item from the abandoned checkout to cart
      const items = data.items || [];
      for (const item of items) {
        if (item.product_id) {
          await addToCart(item.product_id, item.quantity || 1);
        }
      }

      // Store recovery offer in localStorage for CheckoutPage to apply
      if (data.offer) {
        localStorage.setItem(RECOVERY_OFFER_KEY, JSON.stringify({
          checkout_id: data.checkout_id,
          offer: data.offer,
          customer_name: data.customer_name,
          customer_phone: data.customer_phone,
          shipping_address: data.shipping_address,
          wilaya: data.wilaya,
          commune: data.commune,
        }));
      }

      // Redirect to checkout
      navigate('/checkout', { replace: true });
    } catch (err) {
      console.error('Recovery failed:', err);
      setStatus('error');
      setError(t.error);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-background">
      <div className="text-center space-y-4 p-8">
        {status === 'error' ? (
          <>
            <AlertCircle className="h-16 w-16 text-red-400 mx-auto" />
            <p className="text-lg text-muted-foreground">{error}</p>
          </>
        ) : (
          <>
            <div className="relative">
              <ShoppingCart className="h-16 w-16 text-primary mx-auto" />
              <Loader2 className="h-8 w-8 animate-spin text-primary absolute -bottom-1 -right-1 mx-auto" style={{ left: 'calc(50% + 12px)' }} />
            </div>
            <p className="text-lg font-medium">
              {status === 'loading' ? t.loading : t.restoring}
            </p>
            <p className="text-sm text-muted-foreground">{t.redirect}</p>
          </>
        )}
      </div>
    </div>
  );
};

export default RecoverCheckoutPage;
