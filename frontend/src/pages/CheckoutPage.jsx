import React, { useState, useEffect, useRef } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useCart } from '@/contexts/CartContext';
import { useAuth } from '@/contexts/AuthContext';
import { trackInitiateCheckout, trackPurchase } from '@/lib/fbPixel';
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
  Truck,
  CheckCircle2,
  Package,
  Loader2,
  MapPin,
  Building2,
  Home,
  Gift,
  Percent,
} from 'lucide-react';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

export const CheckoutPage = () => {
  const { t, language, isRTL, formatPrice } = useLanguage();
  const { cart, cartTotal, clearCart } = useCart();
  const { user } = useAuth();
  const navigate = useNavigate();

  const [wilayas, setWilayas] = useState([]);
  const [communes, setCommunes] = useState([]);
  const [addresses, setAddresses] = useState([]);
  const [selectedAddress, setSelectedAddress] = useState(null);
  const [showAddressSelector, setShowAddressSelector] = useState(false);
  const [loading, setLoading] = useState(false);
  const [orderSuccess, setOrderSuccess] = useState(false);
  const [orderId, setOrderId] = useState('');

  // Shipping
  const [shippingType, setShippingType] = useState('home');
  const [shippingOptions, setShippingOptions] = useState(null);
  const [selectedShipping, setSelectedShipping] = useState(null);
  const [loadingShipping, setLoadingShipping] = useState(false);

  const [formData, setFormData] = useState({
    customer_name: user?.name || '',
    customer_phone: user?.phone || '',
    shipping_address: user?.address || '',
    wilaya: user?.wilaya || '',
    commune: '',
    notes: ''
  });

  // Recovery offer from abandoned checkout link
  const [recoveryOffer, setRecoveryOffer] = useState(null);
  const recoveryDataRef = useRef(null);

  // Load recovery offer on mount (store in ref first, apply after user effect)
  useEffect(() => {
    const RECOVERY_KEY = 'agroyousfi_recovery_offer';
    try {
      const saved = localStorage.getItem(RECOVERY_KEY);
      if (saved) {
        const data = JSON.parse(saved);
        recoveryDataRef.current = data;
        setRecoveryOffer(data.offer || null);
      }
    } catch {
      // ignore
    }
  }, []);

  const l = {
    ar: {
      selectCommune: 'اختر البلدية',
      commune: 'البلدية',
      shippingMethod: 'طريقة الشحن',
      homeDelivery: 'توصيل للمنزل',
      officeDelivery: 'توصيل للمكتب',
      shippingOptions: 'خيارات الشحن',
      selectShipping: 'اختر شركة الشحن',
      deliveryDays: 'يوم',
      freeShipping: 'شحن مجاني',
      freeShippingReason: 'السبب',
      noShippingOptions: 'لا توجد خيارات شحن متاحة لهذه المنطقة',
      selectWilayaFirst: 'اختر الولاية أولاً لعرض خيارات الشحن',
      loadingShipping: 'جاري تحميل خيارات الشحن...',
    },
    fr: {
      selectCommune: 'Sélectionner la commune',
      commune: 'Commune',
      shippingMethod: 'Méthode de livraison',
      homeDelivery: 'Livraison à domicile',
      officeDelivery: 'Livraison au bureau',
      shippingOptions: 'Options de livraison',
      selectShipping: 'Choisir le transporteur',
      deliveryDays: 'jours',
      freeShipping: 'Livraison gratuite',
      freeShippingReason: 'Raison',
      noShippingOptions: 'Pas d\'options de livraison disponibles pour cette zone',
      selectWilayaFirst: 'Sélectionnez d\'abord la wilaya pour voir les options',
      loadingShipping: 'Chargement des options de livraison...',
    },
    en: {
      selectCommune: 'Select commune',
      commune: 'Commune',
      shippingMethod: 'Shipping Method',
      homeDelivery: 'Home Delivery',
      officeDelivery: 'Office Delivery',
      shippingOptions: 'Shipping Options',
      selectShipping: 'Select carrier',
      deliveryDays: 'days',
      freeShipping: 'Free Shipping',
      freeShippingReason: 'Reason',
      noShippingOptions: 'No shipping options available for this area',
      selectWilayaFirst: 'Select a wilaya first to see shipping options',
      loadingShipping: 'Loading shipping options...',
    }
  };
  const text = l[language] || l.ar;

  const initiateCheckoutTracked = useRef(false);
  const abandonedSaveTimer = useRef(null);

  // Auto-save abandoned checkout data (debounced)
  // Skip auto-save when using recovery offer (avoid creating duplicate abandoned checkouts)
  useEffect(() => {
    if (cart.items.length === 0 || orderSuccess || recoveryOffer) return;

    if (abandonedSaveTimer.current) clearTimeout(abandonedSaveTimer.current);
    abandonedSaveTimer.current = setTimeout(() => {
      const hasAnyData = formData.customer_name || formData.customer_phone || formData.wilaya;
      if (!hasAnyData && !cart.items.length) return;

      let browserId = localStorage.getItem('browser_id');
      if (!browserId) {
        browserId = 'browser_' + Math.random().toString(36).substring(2, 15);
        localStorage.setItem('browser_id', browserId);
      }

      const items = cart.items.map(item => ({
        product_id: item.product_id,
        name: item.product?.[`name_${language}`] || item.product?.name_ar || '',
        image: item.product?.images?.[0] || '',
        price: item.product?.price || 0,
        quantity: item.quantity,
      }));

      axios.post(`${API}/abandoned-checkouts`, {
        browser_id: browserId,
        customer_name: formData.customer_name || null,
        customer_phone: formData.customer_phone || null,
        shipping_address: formData.shipping_address || null,
        wilaya: formData.wilaya || null,
        commune: formData.commune || null,
        items,
        cart_total: cartTotal,
        item_count: cart.items.reduce((sum, i) => sum + (i.quantity || 1), 0),
      }, { withCredentials: true }).catch(() => {});
    }, 3000);

    return () => { if (abandonedSaveTimer.current) clearTimeout(abandonedSaveTimer.current); };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [formData, cart.items, cartTotal, orderSuccess, recoveryOffer]);

  useEffect(() => {
    fetchWilayas();
    // Facebook Pixel: InitiateCheckout (only once)
    if (!initiateCheckoutTracked.current && cart.items.length > 0) {
      trackInitiateCheckout({ items: cart.items, total: cartTotal });
      initiateCheckoutTracked.current = true;
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    if (user) {
      fetchAddresses();
    }
  }, [user]);

  useEffect(() => {
    if (user) {
      setFormData(prev => ({
        ...prev,
        customer_name: user.name || prev.customer_name,
        customer_phone: user.phone || prev.customer_phone,
        shipping_address: user.address || prev.shipping_address,
        wilaya: user.wilaya || prev.wilaya
      }));
    }
  }, [user]);

  // Apply recovery data AFTER user effect (recovery takes priority)
  useEffect(() => {
    const data = recoveryDataRef.current;
    if (!data) return;
    // Apply once, then clear ref
    recoveryDataRef.current = null;
    setFormData(prev => ({
      ...prev,
      customer_name: data.customer_name || prev.customer_name,
      customer_phone: data.customer_phone || prev.customer_phone,
      shipping_address: data.shipping_address || prev.shipping_address,
      wilaya: data.wilaya || prev.wilaya,
      commune: data.commune || prev.commune,
    }));
  }, [user]);

  // Fetch communes when wilaya changes (preserve commune from recovery)
  const pendingCommuneRef = useRef(null);
  useEffect(() => {
    if (formData.wilaya) {
      // Save current commune before fetching (recovery may have set it)
      setFormData(prev => {
        if (prev.commune) {
          pendingCommuneRef.current = prev.commune;
        }
        return prev;
      });
      fetchCommunes(formData.wilaya);
    } else {
      setCommunes([]);
      setFormData(prev => ({ ...prev, commune: '' }));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [formData.wilaya]);

  // Fetch shipping options when wilaya/commune/shippingType changes
  useEffect(() => {
    if (formData.wilaya) {
      fetchShippingOptions();
    } else {
      setShippingOptions(null);
      setSelectedShipping(null);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [formData.wilaya, formData.commune, shippingType]);

  const fetchWilayas = async () => {
    try {
      const response = await axios.get(`${API}/wilayas`);
      setWilayas(response.data);
    } catch (error) {
      console.error('Error fetching wilayas:', error);
    }
  };

  const fetchCommunes = async (wilaya) => {
    try {
      // Extract wilaya code from string like "01 - أدرار (Adrar)"
      const code = parseInt(wilaya);
      let communesList;
      if (code > 0) {
        const response = await axios.get(`${API}/communes?wilaya=${code}`);
        communesList = response.data;
      } else {
        // Try by name
        const response = await axios.get(`${API}/communes?wilaya_name=${encodeURIComponent(wilaya)}`);
        communesList = response.data;
      }
      setCommunes(communesList);
      // Restore pending commune from recovery if it exists in the loaded list
      if (pendingCommuneRef.current) {
        const pending = pendingCommuneRef.current;
        pendingCommuneRef.current = null;
        if (communesList.includes(pending)) {
          setFormData(prev => ({ ...prev, commune: pending }));
        }
      }
    } catch (error) {
      console.error('Error fetching communes:', error);
      setCommunes([]);
    }
  };

  const fetchShippingOptions = async () => {
    try {
      setLoadingShipping(true);
      const params = new URLSearchParams({
        wilaya: formData.wilaya,
        shipping_type: shippingType
      });
      if (formData.commune) {
        params.set('commune', formData.commune);
      }
      const response = await axios.get(`${API}/shipping/options?${params}`, { withCredentials: true });
      setShippingOptions(response.data);

      // Auto-select cheapest option
      if (response.data.options && response.data.options.length > 0) {
        setSelectedShipping(response.data.options[0]);
      } else if (response.data.free_shipping) {
        setSelectedShipping(null); // Free shipping, no company needed
      } else {
        setSelectedShipping(null);
      }
    } catch (error) {
      console.error('Error fetching shipping options:', error);
      setShippingOptions(null);
      setSelectedShipping(null);
    } finally {
      setLoadingShipping(false);
    }
  };

  const fetchAddresses = async () => {
    try {
      const response = await axios.get(`${API}/addresses`, { withCredentials: true });
      setAddresses(response.data);

      // Auto-fill from default address
      const defaultAddress = response.data.find(addr => addr.is_default);
      if (defaultAddress) {
        setSelectedAddress(defaultAddress);
        setFormData(prev => ({
          ...prev,
          customer_name: defaultAddress.full_name || prev.customer_name,
          customer_phone: defaultAddress.phone || prev.customer_phone,
          shipping_address: defaultAddress.address_line || prev.shipping_address,
          wilaya: defaultAddress.wilaya || prev.wilaya,
          commune: defaultAddress.commune || prev.commune
        }));
      }

      // Show address selector if user has multiple addresses
      if (response.data.length > 1) {
        setShowAddressSelector(true);
      }
    } catch (error) {
      console.error('Error fetching addresses:', error);
    }
  };

  const handleSelectAddress = (address) => {
    setSelectedAddress(address);
    setFormData(prev => ({
      ...prev,
      customer_name: address.full_name || prev.customer_name,
      customer_phone: address.phone || prev.customer_phone,
      shipping_address: address.address_line || prev.shipping_address,
      wilaya: address.wilaya || prev.wilaya,
      commune: address.commune || prev.commune
    }));
  };

  const getShippingCost = () => {
    // Recovery offer: free shipping
    if (recoveryOffer?.free_shipping) return 0;
    if (!shippingOptions) return 0;
    if (shippingOptions.free_shipping) return 0;
    if (selectedShipping) return selectedShipping.shipping_cost;
    return 0;
  };

  const getDiscountAmount = () => {
    if (!recoveryOffer?.discount_enabled) return 0;
    const value = parseFloat(recoveryOffer.discount_value) || 0;
    if (recoveryOffer.discount_type === 'percentage') {
      return Math.round(cartTotal * value / 100);
    }
    return Math.min(value, cartTotal); // Fixed amount, capped at cart total
  };

  const getOrderTotal = () => {
    return Math.max(0, cartTotal - getDiscountAmount()) + getShippingCost();
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!formData.customer_name || !formData.customer_phone || !formData.shipping_address || !formData.wilaya) {
      toast.error(language === 'ar' ? 'يرجى ملء جميع الحقول المطلوبة' : 'Please fill all required fields');
      return;
    }

    // Build order payload
    const payload = {
      customer_name: formData.customer_name,
      customer_phone: formData.customer_phone,
      shipping_address: formData.shipping_address,
      wilaya: formData.wilaya,
      commune: formData.commune || null,
      notes: formData.notes,
      shipping_type: shippingType,
    };

    // Always include cart items in payload (needed for guest users and recovery)
    if (cart.items && cart.items.length > 0) {
      payload.items = cart.items.map(item => ({
        product_id: item.product_id,
        product_name: item.product?.name_ar || item.product?.name_fr || '',
        product_image: item.product?.images?.[0] || null,
        quantity: item.quantity || 1,
        price: item.product?.price || 0,
      }));
      payload.subtotal = cartTotal;
      payload.discount_amount = getDiscountAmount();
      payload.discount_percentage = recoveryOffer?.discount_type === 'percentage' ? parseFloat(recoveryOffer.discount_value) : 0;
      payload.total = getOrderTotal();
    }

    // Add shipping company if selected
    if (selectedShipping) {
      payload.shipping_company_id = selectedShipping.company_id;
      payload.shipping_cost = getShippingCost();
    }

    // Recovery offer: free shipping flag for server-side validation
    if (recoveryOffer?.free_shipping) {
      payload.free_shipping_offer = true;
    }

    try {
      setLoading(true);
      // Cancel any pending auto-save to prevent re-creating abandoned checkout after order
      if (abandonedSaveTimer.current) {
        clearTimeout(abandonedSaveTimer.current);
        abandonedSaveTimer.current = null;
      }
      const response = await axios.post(`${API}/orders`, payload, { withCredentials: true });
      setOrderId(response.data.order_id);
      setOrderSuccess(true);
      // Facebook Pixel: Purchase
      trackPurchase({
        orderId: response.data.order_id,
        total: getOrderTotal(),
        items: cart.items,
      });
      // Clear cart immediately (server already clears for logged-in users, this ensures frontend + browser cart)
      await clearCart();
      // Resolve abandoned checkout: mark as recovered only if this is a recovery order
      const browserId = localStorage.getItem('browser_id');
      const isRecoveryOrder = !!recoveryOffer;
      const recoveryKey = 'agroyousfi_recovery_offer';
      const recoveryData = isRecoveryOrder ? JSON.parse(localStorage.getItem(recoveryKey) || '{}') : {};
      await axios.delete(`${API}/abandoned-checkouts`, {
        data: {
          browser_id: browserId,
          order_id: response.data.order_id,
          checkout_id: isRecoveryOrder ? (recoveryData.checkout_id || null) : null,
          actual_total: getOrderTotal(),
        },
        withCredentials: true
      }).catch(() => {});
      // Clear recovery offer
      localStorage.removeItem('agroyousfi_recovery_offer');
      setRecoveryOffer(null);
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
              <Link to="/orders?tab=orders">
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

  const shippingCost = getShippingCost();
  const orderTotal = getOrderTotal();

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
              {/* Address Selector - Only for logged-in users with multiple addresses */}
              {user && showAddressSelector && addresses.length > 0 && (
                <div className="bg-card rounded-3xl p-6 border">
                  <h2 className="text-xl font-bold mb-4 flex items-center gap-2">
                    <Truck className="h-5 w-5 text-primary" />
                    {language === 'ar' ? 'اختر عنوان الشحن' : 'Select Shipping Address'}
                  </h2>
                  <div className="grid gap-3">
                    {addresses.map((addr) => (
                      <div
                        key={addr.address_id}
                        onClick={() => handleSelectAddress(addr)}
                        className={`p-4 rounded-xl border-2 cursor-pointer transition-all ${selectedAddress?.address_id === addr.address_id
                          ? 'border-primary bg-primary/5'
                          : 'border-border hover:border-primary/50'
                          }`}
                      >
                        <div className="flex items-start justify-between">
                          <div className="flex-1">
                            <div className="flex items-center gap-2 mb-2">
                              <span className="font-bold text-foreground">{addr.full_name}</span>
                              {addr.is_default && (
                                <span className="px-2 py-0.5 bg-primary/10 text-primary text-xs rounded-full font-medium">
                                  {language === 'ar' ? 'افتراضي' : 'Default'}
                                </span>
                              )}
                            </div>
                            <p className="text-sm text-muted-foreground mb-1">
                              {addr.address_line}
                            </p>
                            <p className="text-sm text-muted-foreground">
                              {addr.commune && `${addr.commune} - `}{addr.wilaya}
                            </p>
                            <p className="text-sm text-muted-foreground mt-1">
                              {addr.phone}
                            </p>
                          </div>
                          <div className={`h-5 w-5 rounded-full border-2 flex items-center justify-center ${selectedAddress?.address_id === addr.address_id
                            ? 'border-primary bg-primary'
                            : 'border-muted-foreground/30'
                            }`}>
                            {selectedAddress?.address_id === addr.address_id && (
                              <div className="h-2.5 w-2.5 rounded-full bg-white" />
                            )}
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                  <p className="text-xs text-muted-foreground mt-4">
                    {language === 'ar'
                      ? 'يمكنك تعديل التفاصيل أدناه إذا لزم الأمر'
                      : 'You can modify the details below if needed'
                    }
                  </p>
                </div>
              )}

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
                      onChange={(e) => setFormData(prev => ({ ...prev, customer_name: e.target.value }))}
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
                      value={formData.customer_phone}
                      onChange={(e) => setFormData(prev => ({ ...prev, customer_phone: e.target.value }))}
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
                      onValueChange={(value) => setFormData(prev => ({ ...prev, wilaya: value, commune: '' }))}
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

                  {/* Commune Selector */}
                  {communes.length > 0 && (
                    <div className="space-y-2">
                      <Label htmlFor="commune">
                        <MapPin className="h-3.5 w-3.5 inline me-1" />
                        {text.commune}
                      </Label>
                      <Select
                        value={formData.commune}
                        onValueChange={(value) => setFormData(prev => ({ ...prev, commune: value }))}
                      >
                        <SelectTrigger data-testid="checkout-commune">
                          <SelectValue placeholder={text.selectCommune} />
                        </SelectTrigger>
                        <SelectContent>
                          {communes.map((commune, index) => (
                            <SelectItem key={index} value={commune}>
                              {commune}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    </div>
                  )}

                  <div className="space-y-2 sm:col-span-2">
                    <Label htmlFor="address">{t('checkout.address')} *</Label>
                    <Textarea
                      id="address"
                      value={formData.shipping_address}
                      onChange={(e) => setFormData(prev => ({ ...prev, shipping_address: e.target.value }))}
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
                      onChange={(e) => setFormData(prev => ({ ...prev, notes: e.target.value }))}
                      placeholder={language === 'ar' ? 'ملاحظات إضافية...' : 'Additional notes...'}
                      rows={2}
                    />
                  </div>
                </div>
              </div>

              {/* Shipping Options */}
              <div className="bg-card rounded-3xl p-6 border">
                <h2 className="text-xl font-bold mb-6 flex items-center gap-2">
                  <div className="h-8 w-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-sm font-bold">2</div>
                  {text.shippingMethod}
                </h2>

                {/* Shipping Type Toggle */}
                <div className="flex gap-2 mb-6">
                  <Button
                    type="button"
                    variant={shippingType === 'home' ? 'default' : 'outline'}
                    onClick={() => setShippingType('home')}
                    className="rounded-full flex-1"
                  >
                    <Home className="h-4 w-4 me-2" />
                    {text.homeDelivery}
                  </Button>
                  <Button
                    type="button"
                    variant={shippingType === 'office' ? 'default' : 'outline'}
                    onClick={() => setShippingType('office')}
                    className="rounded-full flex-1"
                  >
                    <Building2 className="h-4 w-4 me-2" />
                    {text.officeDelivery}
                  </Button>
                </div>

                {/* Shipping Options Display */}
                {!formData.wilaya ? (
                  <div className="text-center py-8 text-muted-foreground">
                    <MapPin className="h-10 w-10 mx-auto mb-3 text-muted-foreground/30" />
                    <p className="text-sm">{text.selectWilayaFirst}</p>
                  </div>
                ) : loadingShipping ? (
                  <div className="text-center py-8">
                    <Loader2 className="h-8 w-8 mx-auto animate-spin text-primary mb-3" />
                    <p className="text-sm text-muted-foreground">{text.loadingShipping}</p>
                  </div>
                ) : shippingOptions?.free_shipping ? (
                  <div className="p-4 border-2 border-green-500 rounded-2xl bg-green-50 dark:bg-green-950/20">
                    <div className="flex items-center gap-3">
                      <div className="h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <Truck className="h-6 w-6 text-green-600" />
                      </div>
                      <div>
                        <p className="font-bold text-green-700 dark:text-green-400">{text.freeShipping}</p>
                        {shippingOptions.reason && (
                          <p className="text-sm text-green-600 dark:text-green-500">{shippingOptions.reason}</p>
                        )}
                      </div>
                    </div>
                  </div>
                ) : shippingOptions?.options?.length > 0 ? (
                  <div className="space-y-3">
                    {shippingOptions.options.map((option) => {
                      const companyName = option[`company_name_${language}`] || option.company_name_ar;
                      const isSelected = selectedShipping?.company_id === option.company_id;

                      return (
                        <div
                          key={option.company_id}
                          onClick={() => setSelectedShipping(option)}
                          className={`p-4 rounded-xl border-2 cursor-pointer transition-all ${
                            isSelected
                              ? 'border-primary bg-primary/5'
                              : 'border-border hover:border-primary/50'
                          }`}
                        >
                          <div className="flex items-center justify-between">
                            <div className="flex items-center gap-3">
                              <div className={`h-5 w-5 rounded-full border-2 flex items-center justify-center ${
                                isSelected ? 'border-primary bg-primary' : 'border-muted-foreground/30'
                              }`}>
                                {isSelected && <div className="h-2.5 w-2.5 rounded-full bg-white" />}
                              </div>
                              {option.company_logo ? (
                                <img src={option.company_logo} alt={companyName} className="h-8 w-8 rounded object-contain" />
                              ) : (
                                <div className="h-8 w-8 rounded bg-muted flex items-center justify-center">
                                  <Truck className="h-4 w-4 text-muted-foreground" />
                                </div>
                              )}
                              <div>
                                <p className="font-semibold text-sm">{companyName}</p>
                                <p className="text-xs text-muted-foreground">
                                  {option.min_delivery_days}-{option.max_delivery_days} {text.deliveryDays}
                                </p>
                              </div>
                            </div>
                            <p className="font-bold text-primary">
                              {formatPrice(option.shipping_cost)}
                            </p>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                ) : (
                  <div className="text-center py-8 text-muted-foreground">
                    <Package className="h-10 w-10 mx-auto mb-3 text-muted-foreground/30" />
                    <p className="text-sm">{text.noShippingOptions}</p>
                  </div>
                )}
              </div>

              {/* Payment Method */}
              <div className="bg-card rounded-3xl p-6 border">
                <h2 className="text-xl font-bold mb-6 flex items-center gap-2">
                  <div className="h-8 w-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-sm font-bold">3</div>
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

              {/* Recovery Offer Banner */}
              {recoveryOffer && (recoveryOffer.discount_enabled || recoveryOffer.free_shipping) && (
                <div className="p-3 bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800 rounded-xl mb-4">
                  <div className="flex items-center gap-2 text-green-700 dark:text-green-400">
                    <Gift className="h-4 w-4 shrink-0" />
                    <span className="text-sm font-medium">
                      {language === 'ar' ? 'عرض خاص لك!' : language === 'fr' ? 'Offre spéciale pour vous!' : 'Special offer for you!'}
                    </span>
                  </div>
                  <div className="mt-1 text-xs text-green-600 dark:text-green-500 space-y-0.5">
                    {recoveryOffer.discount_enabled && (
                      <p className="flex items-center gap-1">
                        <Percent className="h-3 w-3" />
                        {recoveryOffer.discount_type === 'percentage'
                          ? `${recoveryOffer.discount_value}% ${language === 'ar' ? 'خصم' : language === 'fr' ? 'de réduction' : 'discount'}`
                          : `${formatPrice(parseFloat(recoveryOffer.discount_value))} ${language === 'ar' ? 'خصم' : language === 'fr' ? 'de réduction' : 'discount'}`
                        }
                      </p>
                    )}
                    {recoveryOffer.free_shipping && (
                      <p className="flex items-center gap-1">
                        <Truck className="h-3 w-3" />
                        {language === 'ar' ? 'شحن مجاني' : language === 'fr' ? 'Livraison gratuite' : 'Free shipping'}
                      </p>
                    )}
                  </div>
                </div>
              )}

              {/* Totals */}
              <div className="space-y-3 pt-4 border-t">
                <div className="flex justify-between text-muted-foreground">
                  <span>{t('cart.subtotal')}</span>
                  <span>{formatPrice(cartTotal)}</span>
                </div>
                {getDiscountAmount() > 0 && (
                  <div className="flex justify-between text-green-600">
                    <span className="flex items-center gap-1">
                      <Gift className="h-3 w-3" />
                      {language === 'ar' ? 'الخصم' : language === 'fr' ? 'Remise' : 'Discount'}
                      {recoveryOffer?.discount_type === 'percentage' && (
                        <span className="text-xs">({recoveryOffer.discount_value}%)</span>
                      )}
                    </span>
                    <span className="font-medium">-{formatPrice(getDiscountAmount())}</span>
                  </div>
                )}
                <div className="flex justify-between text-muted-foreground">
                  <span>{t('cart.shipping')}</span>
                  {(shippingOptions?.free_shipping || shippingCost === 0) ? (
                    <span className="text-green-600 font-medium">{t('cart.free')}</span>
                  ) : (
                    <span className="font-medium">{formatPrice(shippingCost)}</span>
                  )}
                </div>
                <div className="flex justify-between text-lg font-bold pt-3 border-t">
                  <span>{t('cart.total')}</span>
                  <span className="text-primary">{formatPrice(orderTotal)}</span>
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
