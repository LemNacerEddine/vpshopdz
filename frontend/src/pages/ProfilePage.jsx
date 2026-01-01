import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useAuth } from '@/contexts/AuthContext';
import { useCart } from '@/contexts/CartContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { toast } from 'sonner';
import { 
  User, 
  Package, 
  Heart,
  MapPin,
  ChevronRight, 
  ChevronLeft, 
  Loader2,
  ShoppingCart,
  RefreshCw,
  Trash2,
  Plus,
  Clock,
  CheckCircle2,
  Truck,
  PackageCheck,
  XCircle,
  Eye,
  Mail,
  Phone,
  Link as LinkIcon
} from 'lucide-react';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

export const ProfilePage = () => {
  const { t, language, isRTL, formatPrice } = useLanguage();
  const { user, updateProfile, loading: authLoading, checkAuth } = useAuth();
  const { addToCart } = useCart();
  const navigate = useNavigate();

  const [orders, setOrders] = useState([]);
  const [wishlist, setWishlist] = useState([]);
  const [addresses, setAddresses] = useState([]);
  const [wilayas, setWilayas] = useState([]);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [selectedOrder, setSelectedOrder] = useState(null);
  const [showOrderDetail, setShowOrderDetail] = useState(false);
  const [showAddAddress, setShowAddAddress] = useState(false);
  const [showLinkEmail, setShowLinkEmail] = useState(false);
  const [linkEmail, setLinkEmail] = useState('');
  const [linkingEmail, setLinkingEmail] = useState(false);
  const [newAddress, setNewAddress] = useState({ title: '', address: '', wilaya: '', phone: '', isDefault: false });
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    address: '',
    wilaya: ''
  });

  const ChevronIcon = isRTL ? ChevronLeft : ChevronRight;

  // Labels
  const labels = {
    ar: {
      profile: 'حسابي',
      personalInfo: 'المعلومات الشخصية',
      myOrders: 'طلباتي',
      wishlist: 'قائمة الأمنيات',
      addresses: 'عناويني',
      save: 'حفظ التغييرات',
      noOrders: 'لا توجد طلبات بعد',
      noWishlist: 'قائمة الأمنيات فارغة',
      noAddresses: 'لا توجد عناوين محفوظة',
      addAddress: 'إضافة عنوان',
      addressTitle: 'اسم العنوان',
      addressTitlePlaceholder: 'مثال: المنزل، العمل',
      setDefault: 'تعيين كافتراضي',
      default: 'افتراضي',
      reorder: 'إعادة الطلب',
      viewDetails: 'عرض التفاصيل',
      orderDetails: 'تفاصيل الطلب',
      orderNumber: 'رقم الطلب',
      orderDate: 'تاريخ الطلب',
      orderStatus: 'حالة الطلب',
      deliveryAddress: 'عنوان التوصيل',
      orderItems: 'المنتجات',
      orderTotal: 'المجموع',
      trackOrder: 'تتبع الطلب',
      pending: 'قيد الانتظار',
      confirmed: 'تم التأكيد',
      shipped: 'جاري الشحن',
      delivered: 'تم التوصيل',
      cancelled: 'ملغي',
      addToCart: 'أضف للسلة',
      removeFromWishlist: 'إزالة من الأمنيات',
      startShopping: 'ابدأ التسوق',
      orderPlaced: 'تم الطلب',
      orderConfirmed: 'تم التأكيد',
      orderShipped: 'جاري التوصيل',
      orderDelivered: 'تم التوصيل'
    },
    fr: {
      profile: 'Mon Compte',
      personalInfo: 'Informations Personnelles',
      myOrders: 'Mes Commandes',
      wishlist: 'Liste de Souhaits',
      addresses: 'Mes Adresses',
      save: 'Enregistrer',
      noOrders: 'Aucune commande',
      noWishlist: 'Liste de souhaits vide',
      noAddresses: 'Aucune adresse enregistrée',
      addAddress: 'Ajouter une adresse',
      addressTitle: 'Nom de l\'adresse',
      addressTitlePlaceholder: 'Ex: Maison, Travail',
      setDefault: 'Définir par défaut',
      default: 'Par défaut',
      reorder: 'Commander à nouveau',
      viewDetails: 'Voir les détails',
      orderDetails: 'Détails de la commande',
      orderNumber: 'N° de commande',
      orderDate: 'Date de commande',
      orderStatus: 'Statut',
      deliveryAddress: 'Adresse de livraison',
      orderItems: 'Articles',
      orderTotal: 'Total',
      trackOrder: 'Suivre la commande',
      pending: 'En attente',
      confirmed: 'Confirmée',
      shipped: 'Expédiée',
      delivered: 'Livrée',
      cancelled: 'Annulée',
      addToCart: 'Ajouter au panier',
      removeFromWishlist: 'Retirer des souhaits',
      startShopping: 'Commencer les achats',
      orderPlaced: 'Commandé',
      orderConfirmed: 'Confirmé',
      orderShipped: 'En livraison',
      orderDelivered: 'Livré'
    },
    en: {
      profile: 'My Account',
      personalInfo: 'Personal Information',
      myOrders: 'My Orders',
      wishlist: 'Wishlist',
      addresses: 'My Addresses',
      save: 'Save Changes',
      noOrders: 'No orders yet',
      noWishlist: 'Wishlist is empty',
      noAddresses: 'No saved addresses',
      addAddress: 'Add Address',
      addressTitle: 'Address Name',
      addressTitlePlaceholder: 'e.g., Home, Work',
      setDefault: 'Set as default',
      default: 'Default',
      reorder: 'Reorder',
      viewDetails: 'View Details',
      orderDetails: 'Order Details',
      orderNumber: 'Order Number',
      orderDate: 'Order Date',
      orderStatus: 'Status',
      deliveryAddress: 'Delivery Address',
      orderItems: 'Items',
      orderTotal: 'Total',
      trackOrder: 'Track Order',
      pending: 'Pending',
      confirmed: 'Confirmed',
      shipped: 'Shipped',
      delivered: 'Delivered',
      cancelled: 'Cancelled',
      addToCart: 'Add to Cart',
      removeFromWishlist: 'Remove from Wishlist',
      startShopping: 'Start Shopping',
      orderPlaced: 'Placed',
      orderConfirmed: 'Confirmed',
      orderShipped: 'Shipping',
      orderDelivered: 'Delivered'
    }
  };

  const l = labels[language];

  useEffect(() => {
    if (!user && !authLoading) {
      navigate('/login');
    }
  }, [user, authLoading]);

  useEffect(() => {
    if (user) {
      setFormData({
        name: user.name || '',
        phone: user.phone || '',
        address: user.address || '',
        wilaya: user.wilaya || ''
      });
      fetchOrders();
      fetchWilayas();
      fetchWishlist();
      fetchAddresses();
    }
  }, [user]);

  const fetchOrders = async () => {
    try {
      setLoading(true);
      const response = await axios.get(`${API}/orders`, { withCredentials: true });
      setOrders(response.data);
    } catch (error) {
      console.error('Error fetching orders:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchWilayas = async () => {
    try {
      const response = await axios.get(`${API}/wilayas`);
      setWilayas(response.data);
    } catch (error) {
      console.error('Error fetching wilayas:', error);
    }
  };

  const fetchWishlist = async () => {
    try {
      const response = await axios.get(`${API}/wishlist`, { withCredentials: true });
      setWishlist(response.data);
    } catch (error) {
      // Wishlist might not exist yet
      setWishlist([]);
    }
  };

  const fetchAddresses = async () => {
    try {
      const response = await axios.get(`${API}/addresses`, { withCredentials: true });
      setAddresses(response.data);
    } catch (error) {
      // Addresses might not exist yet
      setAddresses([]);
    }
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      setSaving(true);
      await updateProfile(formData);
      toast.success(t('common.success'));
    } catch (error) {
      toast.error(t('common.error'));
    } finally {
      setSaving(false);
    }
  };

  const handleReorder = async (order) => {
    try {
      for (const item of order.items) {
        await addToCart(item.product_id, item.quantity);
      }
      toast.success(language === 'ar' ? 'تمت إضافة المنتجات للسلة' : 'Products added to cart');
      navigate('/cart');
    } catch (error) {
      toast.error(t('common.error'));
    }
  };

  const handleRemoveFromWishlist = async (productId) => {
    try {
      await axios.delete(`${API}/wishlist/${productId}`, { withCredentials: true });
      setWishlist(wishlist.filter(item => item.product_id !== productId));
      toast.success(language === 'ar' ? 'تمت الإزالة من الأمنيات' : 'Removed from wishlist');
    } catch (error) {
      toast.error(t('common.error'));
    }
  };

  const handleAddToCartFromWishlist = async (product) => {
    const success = await addToCart(product.product_id, 1);
    if (success) {
      toast.success(t('products.addToCart'));
    }
  };

  const handleAddAddress = async () => {
    try {
      await axios.post(`${API}/addresses`, newAddress, { withCredentials: true });
      toast.success(language === 'ar' ? 'تمت إضافة العنوان' : 'Address added');
      setShowAddAddress(false);
      setNewAddress({ title: '', address: '', wilaya: '', phone: '', isDefault: false });
      fetchAddresses();
    } catch (error) {
      toast.error(t('common.error'));
    }
  };

  const handleDeleteAddress = async (addressId) => {
    try {
      await axios.delete(`${API}/addresses/${addressId}`, { withCredentials: true });
      toast.success(language === 'ar' ? 'تم حذف العنوان' : 'Address deleted');
      fetchAddresses();
    } catch (error) {
      toast.error(t('common.error'));
    }
  };

  const handleLinkEmail = async () => {
    if (!linkEmail) {
      toast.error(language === 'ar' ? 'يرجى إدخال البريد الإلكتروني' : 'Please enter email');
      return;
    }
    try {
      setLinkingEmail(true);
      await axios.post(`${API}/auth/link-email`, { email: linkEmail }, { withCredentials: true });
      toast.success(language === 'ar' ? 'تم ربط البريد الإلكتروني بنجاح' : 'Email linked successfully');
      setShowLinkEmail(false);
      setLinkEmail('');
      await checkAuth(); // Refresh user data
    } catch (error) {
      toast.error(error.response?.data?.detail || t('common.error'));
    } finally {
      setLinkingEmail(false);
    }
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'pending': return 'bg-yellow-100 text-yellow-800';
      case 'confirmed': return 'bg-blue-100 text-blue-800';
      case 'shipped': return 'bg-purple-100 text-purple-800';
      case 'delivered': return 'bg-green-100 text-green-800';
      case 'cancelled': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusIcon = (status) => {
    switch (status) {
      case 'pending': return Clock;
      case 'confirmed': return CheckCircle2;
      case 'shipped': return Truck;
      case 'delivered': return PackageCheck;
      case 'cancelled': return XCircle;
      default: return Clock;
    }
  };

  const getOrderTimeline = (status) => {
    const statuses = ['pending', 'confirmed', 'shipped', 'delivered'];
    const currentIndex = statuses.indexOf(status);
    
    return statuses.map((s, index) => ({
      status: s,
      label: l[s === 'pending' ? 'orderPlaced' : s === 'confirmed' ? 'orderConfirmed' : s === 'shipped' ? 'orderShipped' : 'orderDelivered'],
      completed: index <= currentIndex,
      current: index === currentIndex
    }));
  };

  if (authLoading) {
    return (
      <div className="min-h-[60vh] flex items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  if (!user) return null;

  return (
    <div className="min-h-screen bg-background" data-testid="profile-page">
      {/* Header */}
      <div className="bg-primary/5 py-8">
        <div className="container mx-auto px-4">
          <h1 className="text-3xl font-bold text-foreground mb-2">{l.profile}</h1>
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <Link to="/" className="hover:text-primary">{t('nav.home')}</Link>
            <ChevronIcon className="h-4 w-4" />
            <span className="text-foreground">{l.profile}</span>
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-8">
        <Tabs defaultValue="profile" className="space-y-8">
          <TabsList className="grid w-full max-w-2xl grid-cols-4 rounded-full p-1 bg-muted">
            <TabsTrigger value="profile" className="rounded-full data-[state=active]:bg-background text-xs sm:text-sm">
              <User className="h-4 w-4 sm:me-2" />
              <span className="hidden sm:inline">{l.personalInfo}</span>
            </TabsTrigger>
            <TabsTrigger value="orders" className="rounded-full data-[state=active]:bg-background text-xs sm:text-sm">
              <Package className="h-4 w-4 sm:me-2" />
              <span className="hidden sm:inline">{l.myOrders}</span>
            </TabsTrigger>
            <TabsTrigger value="wishlist" className="rounded-full data-[state=active]:bg-background text-xs sm:text-sm">
              <Heart className="h-4 w-4 sm:me-2" />
              <span className="hidden sm:inline">{l.wishlist}</span>
            </TabsTrigger>
            <TabsTrigger value="addresses" className="rounded-full data-[state=active]:bg-background text-xs sm:text-sm">
              <MapPin className="h-4 w-4 sm:me-2" />
              <span className="hidden sm:inline">{l.addresses}</span>
            </TabsTrigger>
          </TabsList>

          {/* Profile Tab */}
          <TabsContent value="profile">
            <div className="max-w-2xl">
              <Card className="rounded-3xl">
                <CardContent className="p-6">
                  <div className="flex items-center gap-4 mb-6 pb-6 border-b">
                    {user.picture ? (
                      <img src={user.picture} alt={user.name} className="h-16 w-16 rounded-full" />
                    ) : (
                      <div className="h-16 w-16 rounded-full bg-primary/10 flex items-center justify-center">
                        <User className="h-8 w-8 text-primary" />
                      </div>
                    )}
                    <div className="flex-1">
                      <h2 className="text-xl font-bold">{user.name}</h2>
                      <div className="flex flex-col gap-1 text-sm text-muted-foreground">
                        {user.email && (
                          <span className="flex items-center gap-1">
                            <Mail className="h-3 w-3" />
                            {user.email}
                          </span>
                        )}
                        {user.phone && (
                          <span className="flex items-center gap-1" dir="ltr">
                            <Phone className="h-3 w-3" />
                            {user.phone}
                          </span>
                        )}
                      </div>
                    </div>
                  </div>

                  <form onSubmit={handleSave} className="space-y-4">
                    <div className="grid sm:grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label htmlFor="name">{t('checkout.name')}</Label>
                        <Input
                          id="name"
                          value={formData.name}
                          onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                          data-testid="profile-name"
                        />
                      </div>
                      
                      <div className="space-y-2">
                        <Label htmlFor="phone">{t('checkout.phone')}</Label>
                        <Input
                          id="phone"
                          type="tel"
                          value={formData.phone}
                          onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                          dir="ltr"
                          data-testid="profile-phone"
                        />
                      </div>
                      
                      <div className="space-y-2">
                        <Label htmlFor="wilaya">{t('checkout.wilaya')}</Label>
                        <Select
                          value={formData.wilaya}
                          onValueChange={(value) => setFormData({ ...formData, wilaya: value })}
                        >
                          <SelectTrigger data-testid="profile-wilaya">
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
                        <Label htmlFor="address">{t('checkout.address')}</Label>
                        <Input
                          id="address"
                          value={formData.address}
                          onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                          data-testid="profile-address"
                        />
                      </div>
                    </div>

                    <Button 
                      type="submit" 
                      className="rounded-full"
                      disabled={saving}
                      data-testid="save-profile-btn"
                    >
                      {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : l.save}
                    </Button>
                  </form>

                  {/* Link Email Section - For phone-registered users */}
                  {!user.email && (
                    <div className="mt-6 pt-6 border-t">
                      <div className="flex items-center justify-between">
                        <div>
                          <h3 className="font-semibold flex items-center gap-2">
                            <Mail className="h-4 w-4" />
                            {language === 'ar' ? 'ربط البريد الإلكتروني' : language === 'fr' ? 'Lier un email' : 'Link Email'}
                          </h3>
                          <p className="text-sm text-muted-foreground">
                            {language === 'ar' 
                              ? 'أضف بريدك الإلكتروني لتسهيل تسجيل الدخول' 
                              : language === 'fr'
                              ? 'Ajoutez votre email pour faciliter la connexion'
                              : 'Add your email for easier login'
                            }
                          </p>
                        </div>
                        <Dialog open={showLinkEmail} onOpenChange={setShowLinkEmail}>
                          <DialogTrigger asChild>
                            <Button variant="outline" size="sm" className="rounded-full">
                              <LinkIcon className="h-4 w-4 me-1" />
                              {language === 'ar' ? 'ربط' : 'Link'}
                            </Button>
                          </DialogTrigger>
                          <DialogContent>
                            <DialogHeader>
                              <DialogTitle>
                                {language === 'ar' ? 'ربط البريد الإلكتروني' : 'Link Email'}
                              </DialogTitle>
                            </DialogHeader>
                            <div className="space-y-4">
                              <div className="space-y-2">
                                <Label>{language === 'ar' ? 'البريد الإلكتروني' : 'Email'}</Label>
                                <Input
                                  type="email"
                                  value={linkEmail}
                                  onChange={(e) => setLinkEmail(e.target.value)}
                                  placeholder="example@email.com"
                                  dir="ltr"
                                  data-testid="link-email-input"
                                />
                              </div>
                              <Button 
                                onClick={handleLinkEmail} 
                                className="w-full rounded-full"
                                disabled={linkingEmail}
                              >
                                {linkingEmail ? (
                                  <Loader2 className="h-4 w-4 animate-spin" />
                                ) : (
                                  language === 'ar' ? 'ربط البريد' : 'Link Email'
                                )}
                              </Button>
                            </div>
                          </DialogContent>
                        </Dialog>
                      </div>
                    </div>
                  )}
                </CardContent>
              </Card>
            </div>
          </TabsContent>

          {/* Orders Tab */}
          <TabsContent value="orders">
            <div className="space-y-4">
              {loading ? (
                <div className="flex justify-center py-12">
                  <Loader2 className="h-8 w-8 animate-spin text-primary" />
                </div>
              ) : orders.length === 0 ? (
                <Card className="rounded-3xl">
                  <CardContent className="text-center py-12">
                    <Package className="h-16 w-16 text-muted-foreground/30 mx-auto mb-4" />
                    <p className="text-muted-foreground mb-4">{l.noOrders}</p>
                    <Link to="/products">
                      <Button className="rounded-full">
                        {l.startShopping}
                      </Button>
                    </Link>
                  </CardContent>
                </Card>
              ) : (
                <div className="space-y-4">
                  {orders.map((order) => {
                    const StatusIcon = getStatusIcon(order.status);
                    
                    return (
                      <Card key={order.order_id} className="rounded-2xl" data-testid={`order-${order.order_id}`}>
                        <CardContent className="p-4 sm:p-6">
                          <div className="flex flex-wrap items-start justify-between gap-4 mb-4">
                            <div>
                              <p className="text-sm text-muted-foreground">{l.orderNumber}</p>
                              <p className="font-mono font-semibold">{order.order_id}</p>
                            </div>
                            <Badge className={getStatusColor(order.status)}>
                              <StatusIcon className="h-3 w-3 me-1" />
                              {l[order.status]}
                            </Badge>
                          </div>

                          {/* Order Timeline */}
                          {order.status !== 'cancelled' && (
                            <div className="mb-4 overflow-x-auto">
                              <div className="flex items-center justify-between min-w-[300px]">
                                {getOrderTimeline(order.status).map((step, index) => (
                                  <div key={step.status} className="flex-1 relative">
                                    <div className="flex flex-col items-center">
                                      <div className={`h-8 w-8 rounded-full flex items-center justify-center ${
                                        step.completed ? 'bg-primary text-white' : 'bg-muted text-muted-foreground'
                                      }`}>
                                        {step.completed ? <CheckCircle2 className="h-4 w-4" /> : <Clock className="h-4 w-4" />}
                                      </div>
                                      <span className={`text-xs mt-1 ${step.current ? 'font-bold text-primary' : 'text-muted-foreground'}`}>
                                        {step.label}
                                      </span>
                                    </div>
                                    {index < 3 && (
                                      <div className={`absolute top-4 ${isRTL ? 'right-1/2 left-0' : 'left-1/2 right-0'} h-0.5 ${
                                        step.completed ? 'bg-primary' : 'bg-muted'
                                      }`} style={{ width: 'calc(100% - 2rem)', transform: isRTL ? 'translateX(-50%)' : 'translateX(50%)' }} />
                                    )}
                                  </div>
                                ))}
                              </div>
                            </div>
                          )}

                          <div className="flex flex-wrap gap-2 mb-4">
                            {order.items.slice(0, 3).map((item, index) => (
                              <div key={index} className="text-sm bg-muted/50 px-3 py-1 rounded-full">
                                {item.name} x{item.quantity}
                              </div>
                            ))}
                            {order.items.length > 3 && (
                              <div className="text-sm bg-muted/50 px-3 py-1 rounded-full">
                                +{order.items.length - 3}
                              </div>
                            )}
                          </div>

                          <div className="flex flex-wrap items-center justify-between gap-4 pt-4 border-t">
                            <div className="flex items-center gap-4">
                              <div>
                                <p className="text-sm text-muted-foreground">{l.orderDate}</p>
                                <p className="font-medium">
                                  {new Date(order.created_at).toLocaleDateString(
                                    language === 'ar' ? 'ar-DZ' : language === 'fr' ? 'fr-FR' : 'en-US'
                                  )}
                                </p>
                              </div>
                              <div>
                                <p className="text-sm text-muted-foreground">{l.orderTotal}</p>
                                <p className="text-lg font-bold text-primary">{formatPrice(order.total)}</p>
                              </div>
                            </div>
                            
                            <div className="flex gap-2">
                              <Dialog>
                                <DialogTrigger asChild>
                                  <Button variant="outline" size="sm" className="rounded-full">
                                    <Eye className="h-4 w-4 me-1" />
                                    {l.viewDetails}
                                  </Button>
                                </DialogTrigger>
                                <DialogContent className="max-w-lg">
                                  <DialogHeader>
                                    <DialogTitle>{l.orderDetails}</DialogTitle>
                                  </DialogHeader>
                                  <div className="space-y-4">
                                    <div className="grid grid-cols-2 gap-4 text-sm">
                                      <div>
                                        <p className="text-muted-foreground">{l.orderNumber}</p>
                                        <p className="font-mono font-medium">{order.order_id}</p>
                                      </div>
                                      <div>
                                        <p className="text-muted-foreground">{l.orderStatus}</p>
                                        <Badge className={getStatusColor(order.status)}>{l[order.status]}</Badge>
                                      </div>
                                    </div>
                                    <div>
                                      <p className="text-sm text-muted-foreground mb-1">{l.deliveryAddress}</p>
                                      <p className="text-sm">{order.customer_name}</p>
                                      <p className="text-sm">{order.phone}</p>
                                      <p className="text-sm">{order.address}, {order.wilaya}</p>
                                    </div>
                                    <div>
                                      <p className="text-sm text-muted-foreground mb-2">{l.orderItems}</p>
                                      <div className="space-y-2">
                                        {order.items.map((item, index) => (
                                          <div key={index} className="flex justify-between text-sm">
                                            <span>{item.name} x{item.quantity}</span>
                                            <span className="font-medium">{formatPrice(item.price * item.quantity)}</span>
                                          </div>
                                        ))}
                                      </div>
                                    </div>
                                    <div className="pt-4 border-t flex justify-between font-bold">
                                      <span>{l.orderTotal}</span>
                                      <span className="text-primary">{formatPrice(order.total)}</span>
                                    </div>
                                  </div>
                                </DialogContent>
                              </Dialog>
                              
                              <Button 
                                size="sm" 
                                className="rounded-full"
                                onClick={() => handleReorder(order)}
                              >
                                <RefreshCw className="h-4 w-4 me-1" />
                                {l.reorder}
                              </Button>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    );
                  })}
                </div>
              )}
            </div>
          </TabsContent>

          {/* Wishlist Tab */}
          <TabsContent value="wishlist">
            {wishlist.length === 0 ? (
              <Card className="rounded-3xl">
                <CardContent className="text-center py-12">
                  <Heart className="h-16 w-16 text-muted-foreground/30 mx-auto mb-4" />
                  <p className="text-muted-foreground mb-4">{l.noWishlist}</p>
                  <Link to="/products">
                    <Button className="rounded-full">
                      {l.startShopping}
                    </Button>
                  </Link>
                </CardContent>
              </Card>
            ) : (
              <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                {wishlist.map((item) => (
                  <Card key={item.product_id} className="rounded-2xl overflow-hidden group">
                    <div className="aspect-square relative bg-muted">
                      <img 
                        src={item.product?.images?.[0] || 'https://via.placeholder.com/200'} 
                        alt={item.product?.[`name_${language}`] || item.product?.name_ar}
                        className="w-full h-full object-cover"
                      />
                      <Button
                        variant="destructive"
                        size="icon"
                        className="absolute top-2 right-2 h-8 w-8 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                        onClick={() => handleRemoveFromWishlist(item.product_id)}
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </div>
                    <CardContent className="p-4">
                      <Link to={`/products/${item.product_id}`}>
                        <h3 className="font-medium text-sm line-clamp-2 hover:text-primary">
                          {item.product?.[`name_${language}`] || item.product?.name_ar}
                        </h3>
                      </Link>
                      <p className="text-lg font-bold text-primary mt-2">
                        {formatPrice(item.product?.price || 0)}
                      </p>
                      <Button 
                        size="sm" 
                        className="w-full mt-3 rounded-full"
                        onClick={() => handleAddToCartFromWishlist(item.product)}
                      >
                        <ShoppingCart className="h-4 w-4 me-1" />
                        {l.addToCart}
                      </Button>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </TabsContent>

          {/* Addresses Tab */}
          <TabsContent value="addresses">
            <div className="space-y-4">
              <div className="flex justify-end">
                <Dialog open={showAddAddress} onOpenChange={setShowAddAddress}>
                  <DialogTrigger asChild>
                    <Button className="rounded-full">
                      <Plus className="h-4 w-4 me-1" />
                      {l.addAddress}
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>{l.addAddress}</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-4">
                      <div className="space-y-2">
                        <Label>{l.addressTitle}</Label>
                        <Input
                          value={newAddress.title}
                          onChange={(e) => setNewAddress({ ...newAddress, title: e.target.value })}
                          placeholder={l.addressTitlePlaceholder}
                        />
                      </div>
                      <div className="space-y-2">
                        <Label>{t('checkout.phone')}</Label>
                        <Input
                          value={newAddress.phone}
                          onChange={(e) => setNewAddress({ ...newAddress, phone: e.target.value })}
                          dir="ltr"
                        />
                      </div>
                      <div className="space-y-2">
                        <Label>{t('checkout.wilaya')}</Label>
                        <Select
                          value={newAddress.wilaya}
                          onValueChange={(value) => setNewAddress({ ...newAddress, wilaya: value })}
                        >
                          <SelectTrigger>
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
                      <div className="space-y-2">
                        <Label>{t('checkout.address')}</Label>
                        <Input
                          value={newAddress.address}
                          onChange={(e) => setNewAddress({ ...newAddress, address: e.target.value })}
                        />
                      </div>
                      <Button onClick={handleAddAddress} className="w-full rounded-full">
                        {l.addAddress}
                      </Button>
                    </div>
                  </DialogContent>
                </Dialog>
              </div>

              {addresses.length === 0 ? (
                <Card className="rounded-3xl">
                  <CardContent className="text-center py-12">
                    <MapPin className="h-16 w-16 text-muted-foreground/30 mx-auto mb-4" />
                    <p className="text-muted-foreground">{l.noAddresses}</p>
                  </CardContent>
                </Card>
              ) : (
                <div className="grid md:grid-cols-2 gap-4">
                  {addresses.map((addr) => (
                    <Card key={addr.address_id} className="rounded-2xl">
                      <CardContent className="p-4">
                        <div className="flex items-start justify-between mb-2">
                          <div className="flex items-center gap-2">
                            <MapPin className="h-5 w-5 text-primary" />
                            <span className="font-semibold">{addr.title}</span>
                            {addr.isDefault && (
                              <Badge variant="secondary">{l.default}</Badge>
                            )}
                          </div>
                          <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 text-destructive"
                            onClick={() => handleDeleteAddress(addr.address_id)}
                          >
                            <Trash2 className="h-4 w-4" />
                          </Button>
                        </div>
                        <p className="text-sm text-muted-foreground">{addr.phone}</p>
                        <p className="text-sm">{addr.address}</p>
                        <p className="text-sm">{addr.wilaya}</p>
                      </CardContent>
                    </Card>
                  ))}
                </div>
              )}
            </div>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
};

export default ProfilePage;
