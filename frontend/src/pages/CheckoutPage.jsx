import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useCart } from '@/contexts/CartContext';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { toast } from 'sonner';
import { 
  ChevronRight, 
  ChevronLeft, 
  CreditCard, 
  Truck,
  CheckCircle2,
  Package
} from 'lucide-react';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

export const CheckoutPage = () => {
  const { t, language, isRTL, formatPrice } = useLanguage();
  const { cart, cartTotal, clearCart } = useCart();
  const { user } = useAuth();
  const navigate = useNavigate();

  const [wilayas, setWilayas] = useState([]);
  const [loading, setLoading] = useState(false);
  const [orderSuccess, setOrderSuccess] = useState(false);
  const [orderId, setOrderId] = useState('');
  
  const [formData, setFormData] = useState({
    customer_name: user?.name || '',
    phone: user?.phone || '',
    address: user?.address || '',
    wilaya: user?.wilaya || '',
    notes: ''
  });

  useEffect(() => {
    fetchWilayas();
  }, []);

  useEffect(() => {
    if (user) {
      setFormData(prev => ({
        ...prev,
        customer_name: user.name || prev.customer_name,
        phone: user.phone || prev.phone,
        address: user.address || prev.address,
        wilaya: user.wilaya || prev.wilaya
      }));
    }
  }, [user]);

  const fetchWilayas = async () => {
    try {
      const response = await axios.get(`${API}/wilayas`);
      setWilayas(response.data);
    } catch (error) {
      console.error('Error fetching wilayas:', error);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!formData.customer_name || !formData.phone || !formData.address || !formData.wilaya) {
      toast.error(language === 'ar' ? 'يرجى ملء جميع الحقول المطلوبة' : 'Please fill all required fields');
      return;
    }

    try {
      setLoading(true);
      const response = await axios.post(`${API}/orders`, formData, { withCredentials: true });
      setOrderId(response.data.order_id);
      setOrderSuccess(true);
      await clearCart();
    } catch (error) {
      toast.error(error.response?.data?.detail || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  const ChevronIcon = isRTL ? ChevronLeft : ChevronRight;

  if (cart.items.length === 0 && !orderSuccess) {
    return (
      <div className="min-h-[60vh] flex flex-col items-center justify-center px-4">
        <Package className="h-24 w-24 text-muted-foreground/30 mb-6" />
        <h2 className="text-2xl font-bold text-foreground mb-2">{t('cart.empty')}</h2>
        <Link to="/products">
          <Button className="rounded-full px-8">
            {t('cart.continueShopping')}
          </Button>
        </Link>
      </div>
    );
  }

  if (orderSuccess) {
    return (
      <div className="min-h-[60vh] flex flex-col items-center justify-center px-4" data-testid="order-success">
        <div className="text-center max-w-md">
          <div className="h-20 w-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
            <CheckCircle2 className="h-10 w-10 text-green-600" />
          </div>
          <h2 className="text-2xl font-bold text-foreground mb-2">
            {t('checkout.orderSuccess')}
          </h2>
          <p className="text-muted-foreground mb-4">
            {t('checkout.orderNumber')}: <span className="font-mono font-bold">{orderId}</span>
          </p>
          <p className="text-sm text-muted-foreground mb-8">
            {language === 'ar' 
              ? 'سيتم التواصل معك قريباً لتأكيد الطلب' 
              : 'We will contact you soon to confirm your order'
            }
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            {user && (
              <Link to="/orders">
                <Button variant="outline" className="rounded-full px-6">
                  {t('profile.orderHistory')}
                </Button>
              </Link>
            )}
            <Link to="/products">
              <Button className="rounded-full px-6">
                {t('cart.continueShopping')}
              </Button>
            </Link>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-background" data-testid="checkout-page">
      {/* Breadcrumb */}
      <div className="bg-muted/30 py-4">
        <div className="container mx-auto px-4">
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <Link to="/" className="hover:text-primary">{t('nav.home')}</Link>
            <ChevronIcon className="h-4 w-4" />
            <Link to="/cart" className="hover:text-primary">{t('cart.title')}</Link>
            <ChevronIcon className="h-4 w-4" />
            <span className="text-foreground">{t('checkout.title')}</span>
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold mb-8">{t('checkout.title')}</h1>

        <div className="grid lg:grid-cols-3 gap-8">
          {/* Form */}
          <div className="lg:col-span-2">
            <form onSubmit={handleSubmit} className="space-y-8">
              {/* Customer Info */}
              <div className="bg-card rounded-3xl p-6 border">
                <h2 className="text-xl font-bold mb-6 flex items-center gap-2">
                  <div className="h-8 w-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-sm font-bold">1</div>
                  {t('checkout.customerInfo')}
                </h2>
                
                <div className="grid sm:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="name">{t('checkout.name')} *</Label>
                    <Input
                      id="name"
                      value={formData.customer_name}
                      onChange={(e) => setFormData({ ...formData, customer_name: e.target.value })}
                      placeholder={language === 'ar' ? 'الاسم الكامل' : 'Full name'}
                      required
                      data-testid="checkout-name"
                    />
                  </div>
                  
                  <div className="space-y-2">
                    <Label htmlFor="phone">{t('checkout.phone')} *</Label>
                    <Input
                      id="phone"
                      type="tel"
                      value={formData.phone}
                      onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                      placeholder="0XX XX XX XX"
                      dir="ltr"
                      required
                      data-testid="checkout-phone"
                    />
                  </div>
                  
                  <div className="space-y-2">
                    <Label htmlFor="wilaya">{t('checkout.wilaya')} *</Label>
                    <Select
                      value={formData.wilaya}
                      onValueChange={(value) => setFormData({ ...formData, wilaya: value })}
                      required
                    >
                      <SelectTrigger data-testid="checkout-wilaya">
                        <SelectValue placeholder={t('checkout.selectWilaya')} />
                      </SelectTrigger>
                      <SelectContent>
                        {wilayas.map((wilaya, index) => (
                          <SelectItem key={index} value={wilaya}>
                            {wilaya}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                  
                  <div className="space-y-2 sm:col-span-2">
                    <Label htmlFor="address">{t('checkout.address')} *</Label>
                    <Textarea
                      id="address"
                      value={formData.address}
                      onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                      placeholder={language === 'ar' ? 'العنوان التفصيلي' : 'Detailed address'}
                      rows={2}
                      required
                      data-testid="checkout-address"
                    />
                  </div>
                  
                  <div className="space-y-2 sm:col-span-2">
                    <Label htmlFor="notes">{t('checkout.notes')}</Label>
                    <Textarea
                      id="notes"
                      value={formData.notes}
                      onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
                      placeholder={language === 'ar' ? 'ملاحظات إضافية...' : 'Additional notes...'}
                      rows={2}
                    />
                  </div>
                </div>
              </div>

              {/* Payment Method */}
              <div className="bg-card rounded-3xl p-6 border">
                <h2 className="text-xl font-bold mb-6 flex items-center gap-2">
                  <div className="h-8 w-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-sm font-bold">2</div>
                  {t('checkout.paymentMethod')}
                </h2>
                
                <div className="flex items-center gap-4 p-4 border-2 border-primary rounded-2xl bg-primary/5">
                  <div className="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                    <Truck className="h-6 w-6 text-primary" />
                  </div>
                  <div>
                    <p className="font-semibold">{t('checkout.cod')}</p>
                    <p className="text-sm text-muted-foreground">
                      {language === 'ar' ? 'ادفع عند استلام طلبك' : 'Pay when you receive your order'}
                    </p>
                  </div>
                </div>
              </div>

              {/* Submit Button (Mobile) */}
              <div className="lg:hidden">
                <Button 
                  type="submit" 
                  className="w-full rounded-full h-12 text-base"
                  disabled={loading}
                  data-testid="place-order-btn"
                >
                  {loading ? t('common.loading') : t('checkout.placeOrder')}
                </Button>
              </div>
            </form>
          </div>

          {/* Order Summary */}
          <div className="lg:col-span-1">
            <div className="bg-card rounded-3xl p-6 border sticky top-24">
              <h2 className="text-xl font-bold mb-6">
                {language === 'ar' ? 'ملخص الطلب' : 'Order Summary'}
              </h2>

              {/* Items */}
              <div className="space-y-4 mb-6 max-h-64 overflow-y-auto custom-scrollbar">
                {cart.items.map((item) => {
                  const product = item.product;
                  if (!product) return null;
                  const name = product[`name_${language}`] || product.name_ar;
                  
                  return (
                    <div key={item.product_id} className="flex gap-3">
                      <div className="w-16 h-16 rounded-xl overflow-hidden bg-muted shrink-0">
                        <img
                          src={product.images?.[0] || 'https://via.placeholder.com/64'}
                          alt={name}
                          className="w-full h-full object-cover"
                        />
                      </div>
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium truncate">{name}</p>
                        <p className="text-xs text-muted-foreground">x{item.quantity}</p>
                        <p className="text-sm font-bold text-primary">
                          {formatPrice(product.price * item.quantity)}
                        </p>
                      </div>
                    </div>
                  );
                })}
              </div>

              {/* Totals */}
              <div className="space-y-3 pt-4 border-t">
                <div className="flex justify-between text-muted-foreground">
                  <span>{t('cart.subtotal')}</span>
                  <span>{formatPrice(cartTotal)}</span>
                </div>
                <div className="flex justify-between text-muted-foreground">
                  <span>{t('cart.shipping')}</span>
                  <span className="text-green-600 font-medium">{t('cart.free')}</span>
                </div>
                <div className="flex justify-between text-lg font-bold pt-3 border-t">
                  <span>{t('cart.total')}</span>
                  <span className="text-primary">{formatPrice(cartTotal)}</span>
                </div>
              </div>

              {/* Submit Button (Desktop) */}
              <Button 
                type="submit"
                form="checkout-form"
                className="w-full rounded-full h-12 text-base mt-6 hidden lg:flex"
                disabled={loading}
                onClick={handleSubmit}
                data-testid="place-order-btn-desktop"
              >
                {loading ? t('common.loading') : t('checkout.placeOrder')}
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CheckoutPage;
