import React, { useState, useEffect } from 'react';
import { useNavigate, Link, useSearchParams } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useAuth } from '@/contexts/AuthContext';
import { useCart } from '@/contexts/CartContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
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
  Link as LinkIcon,
  Star,
  Ticket,
  Shield,
  Bell,
  Search,
  PackageX,
  RotateCcw,
  Settings,
  History,
  CreditCard
} from 'lucide-react';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

// Get browser ID for browsing history
const getBrowserId = () => {
  let browserId = localStorage.getItem('browser_id');
  if (!browserId) {
    browserId = 'browser_' + Math.random().toString(36).substring(2, 15);
    localStorage.setItem('browser_id', browserId);
  }
  return browserId;
};

export const ProfilePage = () => {
  const { t, language, isRTL, formatPrice } = useLanguage();
  const { user, updateProfile, loading: authLoading, checkAuth } = useAuth();
  const { addToCart } = useCart();
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();

  const [orders, setOrders] = useState([]);
  const [wishlist, setWishlist] = useState([]);
  const [addresses, setAddresses] = useState([]);
  const [browsingHistory, setBrowsingHistory] = useState([]);
  const [wilayas, setWilayas] = useState([]);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [selectedOrder, setSelectedOrder] = useState(null);
  const [showOrderDetail, setShowOrderDetail] = useState(false);
  const [showAddAddress, setShowAddAddress] = useState(false);
  const [showLinkEmail, setShowLinkEmail] = useState(false);
  const [linkEmail, setLinkEmail] = useState('');
  const [linkingEmail, setLinkingEmail] = useState(false);
  const [orderSearchQuery, setOrderSearchQuery] = useState('');
  const [orderStatusFilter, setOrderStatusFilter] = useState('all');
  const [newAddress, setNewAddress] = useState({ title: '', address: '', wilaya: '', phone: '', isDefault: false });
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    address: '',
    wilaya: ''
  });

  // Get active tab from URL
  const activeTab = searchParams.get('tab') || 'orders';

  const setActiveTab = (tab) => {
    setSearchParams({ tab });
  };

  const ChevronIcon = isRTL ? ChevronLeft : ChevronRight;

  useEffect(() => {
    if (!user && !authLoading) {
      navigate('/login', { state: { from: { pathname: '/profile' } } });
    }
  }, [user, authLoading, navigate]);

  useEffect(() => {
    if (user) {
      setFormData({
        name: user.name || '',
        phone: user.phone || '',
        address: user.address || '',
        wilaya: user.wilaya || ''
      });
      fetchOrders();
      fetchWishlist();
      fetchAddresses();
      fetchWilayas();
      fetchBrowsingHistory();
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

  const fetchWishlist = async () => {
    try {
      const response = await axios.get(`${API}/wishlist`, { withCredentials: true });
      setWishlist(response.data);
    } catch (error) {
      console.error('Error fetching wishlist:', error);
    }
  };

  const fetchAddresses = async () => {
    try {
      const response = await axios.get(`${API}/addresses`, { withCredentials: true });
      setAddresses(response.data);
    } catch (error) {
      console.error('Error fetching addresses:', error);
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

  const fetchBrowsingHistory = async () => {
    try {
      const response = await axios.get(`${API}/browsing-history?limit=20`, {
        withCredentials: true,
        headers: { 'X-Browser-ID': getBrowserId() }
      });
      setBrowsingHistory(response.data);
    } catch (error) {
      console.error('Error fetching browsing history:', error);
    }
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      setSaving(true);
      await updateProfile(formData);
      toast.success(language === 'ar' ? 'تم تحديث الملف الشخصي' : 'Profile updated');
    } catch (error) {
      toast.error(t('common.error'));
    } finally {
      setSaving(false);
    }
  };

  const handleAddToCartFromWishlist = async (productId) => {
    const success = await addToCart(productId, 1);
    if (success) {
      toast.success(t('products.addToCart'));
    }
  };

  const handleRemoveFromWishlist = async (productId) => {
    try {
      await axios.delete(`${API}/wishlist/${productId}`, { withCredentials: true });
      toast.success(language === 'ar' ? 'تمت الإزالة من المفضلة' : 'Removed from wishlist');
      fetchWishlist();
    } catch (error) {
      toast.error(t('common.error'));
    }
  };

  const handleAddAddress = async (e) => {
    e.preventDefault();
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
      await checkAuth();
    } catch (error) {
      toast.error(error.response?.data?.detail || t('common.error'));
    } finally {
      setLinkingEmail(false);
    }
  };

  const handleClearHistory = async () => {
    try {
      await axios.delete(`${API}/browsing-history`, {
        withCredentials: true,
        headers: { 'X-Browser-ID': getBrowserId() }
      });
      setBrowsingHistory([]);
      toast.success(language === 'ar' ? 'تم مسح السجل' : 'History cleared');
    } catch (error) {
      toast.error(t('common.error'));
    }
  };

  const l = {
    ar: {
      profile: 'الملف الشخصي',
      orders: 'طلباتك',
      allOrders: 'كل الطلبات',
      processing: 'قيد المعالجة',
      shipped: 'تم الشحن',
      delivered: 'تم التوصيل',
      returns: 'المرتجعات',
      reviews: 'تقييماتك',
      wishlist: 'قائمة الأمنيات',
      addresses: 'العناوين',
      browsingHistory: 'سجل التصفح',
      coupons: 'القسائم والعروض',
      accountSecurity: 'أمان الحساب',
      notifications: 'الإشعارات',
      paymentMethods: 'طرق الدفع',
      save: 'حفظ التغييرات',
      noOrders: 'ليس لديك أي طلبات',
      noOrdersDesc: 'ابدأ التسوق الآن واكتشف منتجاتنا الرائعة',
      shopNow: 'تسوق الآن',
      searchOrders: 'اسم المنتج / رقم الطلب',
      orderNumber: 'رقم الطلب',
      orderDate: 'تاريخ الطلب',
      orderStatus: 'حالة الطلب',
      orderTotal: 'المجموع',
      viewOrder: 'عرض التفاصيل',
      noWishlist: 'قائمة الأمنيات فارغة',
      noWishlistDesc: 'أضف منتجاتك المفضلة لمتابعتها لاحقاً',
      addToCart: 'أضف للسلة',
      remove: 'إزالة',
      noAddresses: 'لا توجد عناوين محفوظة',
      addAddress: 'إضافة عنوان',
      addressTitle: 'عنوان التسمية',
      noHistory: 'لم تشاهد أي منتجات بعد',
      noHistoryDesc: 'استعرض منتجاتنا وستظهر هنا',
      clearHistory: 'مسح السجل',
      linkEmail: 'ربط البريد الإلكتروني',
      linkEmailDesc: 'أضف بريدك الإلكتروني لتسهيل تسجيل الدخول'
    },
    fr: {
      profile: 'Profil',
      orders: 'Vos commandes',
      allOrders: 'Toutes les commandes',
      processing: 'En cours',
      shipped: 'Expédiées',
      delivered: 'Livrées',
      returns: 'Retours',
      reviews: 'Vos avis',
      wishlist: 'Liste de souhaits',
      addresses: 'Adresses',
      browsingHistory: 'Historique de navigation',
      coupons: 'Coupons et offres',
      accountSecurity: 'Sécurité du compte',
      notifications: 'Notifications',
      paymentMethods: 'Modes de paiement',
      save: 'Enregistrer',
      noOrders: 'Vous n\'avez aucune commande',
      noOrdersDesc: 'Commencez vos achats et découvrez nos produits',
      shopNow: 'Acheter maintenant',
      searchOrders: 'Nom du produit / Numéro de commande',
      orderNumber: 'N° de commande',
      orderDate: 'Date',
      orderStatus: 'Statut',
      orderTotal: 'Total',
      viewOrder: 'Voir les détails',
      noWishlist: 'Liste de souhaits vide',
      noWishlistDesc: 'Ajoutez vos produits préférés pour les suivre',
      addToCart: 'Ajouter au panier',
      remove: 'Supprimer',
      noAddresses: 'Aucune adresse enregistrée',
      addAddress: 'Ajouter une adresse',
      addressTitle: 'Titre de l\'adresse',
      noHistory: 'Aucun produit consulté',
      noHistoryDesc: 'Parcourez nos produits et ils apparaîtront ici',
      clearHistory: 'Effacer l\'historique',
      linkEmail: 'Lier un email',
      linkEmailDesc: 'Ajoutez votre email pour faciliter la connexion'
    },
    en: {
      profile: 'Profile',
      orders: 'Your Orders',
      allOrders: 'All Orders',
      processing: 'Processing',
      shipped: 'Shipped',
      delivered: 'Delivered',
      returns: 'Returns',
      reviews: 'Your Reviews',
      wishlist: 'Wishlist',
      addresses: 'Addresses',
      browsingHistory: 'Browsing History',
      coupons: 'Coupons & Offers',
      accountSecurity: 'Account Security',
      notifications: 'Notifications',
      paymentMethods: 'Payment Methods',
      save: 'Save Changes',
      noOrders: 'You have no orders',
      noOrdersDesc: 'Start shopping and discover our amazing products',
      shopNow: 'Shop Now',
      searchOrders: 'Product name / Order number',
      orderNumber: 'Order #',
      orderDate: 'Date',
      orderStatus: 'Status',
      orderTotal: 'Total',
      viewOrder: 'View Details',
      noWishlist: 'Wishlist is empty',
      noWishlistDesc: 'Add your favorite products to follow them',
      addToCart: 'Add to Cart',
      remove: 'Remove',
      noAddresses: 'No saved addresses',
      addAddress: 'Add Address',
      addressTitle: 'Address Title',
      noHistory: 'No products viewed yet',
      noHistoryDesc: 'Browse our products and they\'ll appear here',
      clearHistory: 'Clear History',
      linkEmail: 'Link Email',
      linkEmailDesc: 'Add your email for easier login'
    }
  };

  const text = l[language] || l.ar;

  const orderStatusConfig = {
    pending: { label: language === 'ar' ? 'قيد الانتظار' : 'Pending', icon: Clock, color: 'text-yellow-500' },
    confirmed: { label: language === 'ar' ? 'تم التأكيد' : 'Confirmed', icon: CheckCircle2, color: 'text-blue-500' },
    processing: { label: language === 'ar' ? 'قيد التجهيز' : 'Processing', icon: RefreshCw, color: 'text-orange-500' },
    shipped: { label: language === 'ar' ? 'تم الشحن' : 'Shipped', icon: Truck, color: 'text-purple-500' },
    delivered: { label: language === 'ar' ? 'تم التوصيل' : 'Delivered', icon: PackageCheck, color: 'text-green-500' },
    cancelled: { label: language === 'ar' ? 'ملغي' : 'Cancelled', icon: XCircle, color: 'text-red-500' }
  };

  // Sidebar menu items
  const sidebarItems = [
    { id: 'orders', icon: Package, label: text.orders, count: orders.length },
    { id: 'reviews', icon: Star, label: text.reviews, disabled: true },
    { id: 'profile', icon: User, label: text.profile },
    { id: 'wishlist', icon: Heart, label: text.wishlist, count: wishlist.length },
    { id: 'coupons', icon: Ticket, label: text.coupons, disabled: true },
    { id: 'history', icon: History, label: text.browsingHistory, count: browsingHistory.length },
    { id: 'addresses', icon: MapPin, label: text.addresses, count: addresses.length },
    { id: 'payment', icon: CreditCard, label: text.paymentMethods, disabled: true },
    { id: 'security', icon: Shield, label: text.accountSecurity, disabled: true },
    { id: 'notifications', icon: Bell, label: text.notifications, disabled: true },
  ];

  // Filter orders
  const filteredOrders = orders.filter(order => {
    const matchesSearch = orderSearchQuery === '' || 
      order.order_id?.toLowerCase().includes(orderSearchQuery.toLowerCase());
    const matchesStatus = orderStatusFilter === 'all' || order.status === orderStatusFilter;
    return matchesSearch && matchesStatus;
  });

  if (authLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  if (!user) {
    return null;
  }

  return (
    <div className="min-h-screen bg-muted/30" data-testid="profile-page">
      <div className="container mx-auto px-4 py-6">
        <div className="flex gap-6">
          {/* Sidebar - Temu Style */}
          <aside className="hidden lg:block w-64 shrink-0">
            <div className="bg-card rounded-2xl border shadow-sm overflow-hidden sticky top-20">
              {/* User Info */}
              <div className="p-4 border-b bg-gradient-to-br from-primary/5 to-primary/10">
                <div className="flex items-center gap-3">
                  {user.picture ? (
                    <img src={user.picture} alt={user.name} className="h-14 w-14 rounded-full object-cover ring-2 ring-primary/20" />
                  ) : (
                    <div className="h-14 w-14 rounded-full bg-primary/10 flex items-center justify-center">
                      <User className="h-7 w-7 text-primary" />
                    </div>
                  )}
                  <div className="min-w-0 flex-1">
                    <p className="font-bold truncate">{user.name}</p>
                    <p className="text-xs text-muted-foreground truncate">
                      {user.email || user.phone}
                    </p>
                  </div>
                </div>
              </div>

              {/* Menu Items */}
              <ScrollArea className="h-[calc(100vh-280px)]">
                <div className="p-2">
                  {sidebarItems.map((item) => (
                    <button
                      key={item.id}
                      onClick={() => !item.disabled && setActiveTab(item.id)}
                      disabled={item.disabled}
                      className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all ${
                        activeTab === item.id
                          ? 'bg-primary text-primary-foreground font-medium'
                          : item.disabled
                          ? 'opacity-50 cursor-not-allowed'
                          : 'hover:bg-muted'
                      }`}
                    >
                      <item.icon className="h-4 w-4 shrink-0" />
                      <span className="flex-1 text-start">{item.label}</span>
                      {item.count > 0 && (
                        <Badge variant={activeTab === item.id ? "secondary" : "outline"} className="text-xs">
                          {item.count}
                        </Badge>
                      )}
                      <ChevronIcon className="h-4 w-4 opacity-50" />
                    </button>
                  ))}
                </div>
              </ScrollArea>
            </div>
          </aside>

          {/* Mobile Tab Bar */}
          <div className="lg:hidden fixed bottom-0 inset-x-0 bg-card border-t z-40">
            <div className="flex justify-around py-2">
              {sidebarItems.slice(0, 5).map((item) => (
                <button
                  key={item.id}
                  onClick={() => !item.disabled && setActiveTab(item.id)}
                  disabled={item.disabled}
                  className={`flex flex-col items-center gap-1 px-3 py-1 ${
                    activeTab === item.id ? 'text-primary' : 'text-muted-foreground'
                  } ${item.disabled ? 'opacity-50' : ''}`}
                >
                  <item.icon className="h-5 w-5" />
                  <span className="text-[10px]">{item.label}</span>
                </button>
              ))}
            </div>
          </div>

          {/* Main Content */}
          <main className="flex-1 min-w-0 pb-20 lg:pb-0">
            {/* Orders Tab */}
            {activeTab === 'orders' && (
              <div className="space-y-4">
                {/* Order Status Tabs */}
                <div className="bg-card rounded-2xl border shadow-sm p-4">
                  <div className="flex flex-wrap gap-2 mb-4">
                    {[
                      { id: 'all', label: text.allOrders },
                      { id: 'processing', label: text.processing },
                      { id: 'shipped', label: text.shipped },
                      { id: 'delivered', label: text.delivered },
                      { id: 'cancelled', label: text.returns }
                    ].map((status) => (
                      <Button
                        key={status.id}
                        variant={orderStatusFilter === status.id ? "default" : "outline"}
                        size="sm"
                        className="rounded-full"
                        onClick={() => setOrderStatusFilter(status.id)}
                      >
                        {status.label}
                      </Button>
                    ))}
                  </div>

                  {/* Search Bar */}
                  <div className="relative">
                    <Search className={`absolute ${isRTL ? 'right-3' : 'left-3'} top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground`} />
                    <Input
                      placeholder={text.searchOrders}
                      value={orderSearchQuery}
                      onChange={(e) => setOrderSearchQuery(e.target.value)}
                      className={`${isRTL ? 'pr-10' : 'pl-10'} rounded-full`}
                    />
                  </div>
                </div>

                {/* Orders List */}
                {loading ? (
                  <div className="flex justify-center py-12">
                    <Loader2 className="h-8 w-8 animate-spin text-primary" />
                  </div>
                ) : filteredOrders.length > 0 ? (
                  <div className="space-y-3">
                    {filteredOrders.map((order) => {
                      const statusConfig = orderStatusConfig[order.status] || orderStatusConfig.pending;
                      const StatusIcon = statusConfig.icon;
                      
                      return (
                        <div key={order.order_id} className="bg-card rounded-2xl border shadow-sm p-4">
                          <div className="flex items-center justify-between mb-3">
                            <div className="flex items-center gap-3">
                              <Badge variant="outline" className="font-mono text-xs">
                                #{order.order_id?.slice(-8)}
                              </Badge>
                              <span className="text-xs text-muted-foreground">
                                {new Date(order.created_at).toLocaleDateString(language === 'ar' ? 'ar-DZ' : 'fr-FR')}
                              </span>
                            </div>
                            <div className={`flex items-center gap-1.5 text-sm ${statusConfig.color}`}>
                              <StatusIcon className="h-4 w-4" />
                              <span>{statusConfig.label}</span>
                            </div>
                          </div>

                          {/* Order Items Preview */}
                          <div className="flex gap-2 mb-3">
                            {order.items?.slice(0, 4).map((item, idx) => (
                              <div key={idx} className="w-16 h-16 rounded-lg overflow-hidden bg-muted shrink-0">
                                <img 
                                  src={item.product?.images?.[0] || 'https://via.placeholder.com/64'} 
                                  alt={item.product?.[`name_${language}`]}
                                  className="w-full h-full object-cover"
                                />
                              </div>
                            ))}
                            {order.items?.length > 4 && (
                              <div className="w-16 h-16 rounded-lg bg-muted flex items-center justify-center text-sm text-muted-foreground">
                                +{order.items.length - 4}
                              </div>
                            )}
                          </div>

                          <div className="flex items-center justify-between">
                            <span className="font-bold text-lg">
                              {formatPrice(order.total_amount)}
                            </span>
                            <Button 
                              variant="outline" 
                              size="sm" 
                              className="rounded-full"
                              onClick={() => {
                                setSelectedOrder(order);
                                setShowOrderDetail(true);
                              }}
                            >
                              <Eye className="h-4 w-4 me-1" />
                              {text.viewOrder}
                            </Button>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                ) : (
                  <div className="bg-card rounded-2xl border shadow-sm p-12 text-center">
                    <PackageX className="h-20 w-20 mx-auto text-muted-foreground/30 mb-4" />
                    <h3 className="text-xl font-bold mb-2">{text.noOrders}</h3>
                    <p className="text-muted-foreground mb-6">{text.noOrdersDesc}</p>
                    <Button onClick={() => navigate('/products')} className="rounded-full">
                      <ShoppingCart className="h-4 w-4 me-2" />
                      {text.shopNow}
                    </Button>
                  </div>
                )}
              </div>
            )}

            {/* Profile Tab */}
            {activeTab === 'profile' && (
              <div className="bg-card rounded-2xl border shadow-sm p-6">
                <h2 className="text-xl font-bold mb-6">{text.profile}</h2>
                
                <div className="flex items-center gap-4 mb-6 pb-6 border-b">
                  {user.picture ? (
                    <img src={user.picture} alt={user.name} className="h-20 w-20 rounded-full object-cover ring-2 ring-primary/20" />
                  ) : (
                    <div className="h-20 w-20 rounded-full bg-primary/10 flex items-center justify-center">
                      <User className="h-10 w-10 text-primary" />
                    </div>
                  )}
                  <div className="flex-1">
                    <h3 className="text-xl font-bold">{user.name}</h3>
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
                      />
                    </div>
                    
                    <div className="space-y-2">
                      <Label htmlFor="wilaya">{t('checkout.wilaya')}</Label>
                      <Select
                        value={formData.wilaya}
                        onValueChange={(value) => setFormData({ ...formData, wilaya: value })}
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
                    
                    <div className="space-y-2 sm:col-span-2">
                      <Label htmlFor="address">{t('checkout.address')}</Label>
                      <Input
                        id="address"
                        value={formData.address}
                        onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                      />
                    </div>
                  </div>

                  <Button type="submit" className="rounded-full" disabled={saving}>
                    {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : text.save}
                  </Button>
                </form>

                {/* Link Email Section */}
                {!user.email && (
                  <div className="mt-6 pt-6 border-t">
                    <div className="flex items-center justify-between">
                      <div>
                        <h3 className="font-semibold flex items-center gap-2">
                          <Mail className="h-4 w-4" />
                          {text.linkEmail}
                        </h3>
                        <p className="text-sm text-muted-foreground">{text.linkEmailDesc}</p>
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
                            <DialogTitle>{text.linkEmail}</DialogTitle>
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
                              />
                            </div>
                            <Button onClick={handleLinkEmail} className="w-full rounded-full" disabled={linkingEmail}>
                              {linkingEmail ? <Loader2 className="h-4 w-4 animate-spin" /> : text.linkEmail}
                            </Button>
                          </div>
                        </DialogContent>
                      </Dialog>
                    </div>
                  </div>
                )}
              </div>
            )}

            {/* Wishlist Tab */}
            {activeTab === 'wishlist' && (
              <div className="bg-card rounded-2xl border shadow-sm p-6">
                <h2 className="text-xl font-bold mb-6">{text.wishlist}</h2>
                
                {wishlist.length > 0 ? (
                  <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {wishlist.map((item) => (
                      <div key={item.product_id} className="border rounded-xl overflow-hidden group">
                        <Link to={`/products/${item.product_id}`} className="block aspect-square relative">
                          <img 
                            src={item.product?.images?.[0] || 'https://via.placeholder.com/200'} 
                            alt={item.product?.[`name_${language}`]}
                            className="w-full h-full object-cover group-hover:scale-105 transition-transform"
                          />
                          {item.product?.old_price && (
                            <Badge className="absolute top-2 start-2 bg-red-500">
                              -{Math.round((1 - item.product.price / item.product.old_price) * 100)}%
                            </Badge>
                          )}
                        </Link>
                        <div className="p-3">
                          <h3 className="font-medium text-sm truncate mb-1">
                            {item.product?.[`name_${language}`] || item.product?.name_ar}
                          </h3>
                          <p className="font-bold text-primary mb-3">{formatPrice(item.product?.price || 0)}</p>
                          <div className="flex gap-2">
                            <Button size="sm" className="flex-1 rounded-full" onClick={() => handleAddToCartFromWishlist(item.product_id)}>
                              <ShoppingCart className="h-4 w-4 me-1" />
                              {text.addToCart}
                            </Button>
                            <Button size="sm" variant="outline" className="rounded-full" onClick={() => handleRemoveFromWishlist(item.product_id)}>
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-12">
                    <Heart className="h-20 w-20 mx-auto text-muted-foreground/30 mb-4" />
                    <h3 className="text-xl font-bold mb-2">{text.noWishlist}</h3>
                    <p className="text-muted-foreground mb-6">{text.noWishlistDesc}</p>
                    <Button onClick={() => navigate('/products')} className="rounded-full">
                      {text.shopNow}
                    </Button>
                  </div>
                )}
              </div>
            )}

            {/* Addresses Tab */}
            {activeTab === 'addresses' && (
              <div className="bg-card rounded-2xl border shadow-sm p-6">
                <div className="flex items-center justify-between mb-6">
                  <h2 className="text-xl font-bold">{text.addresses}</h2>
                  <Dialog open={showAddAddress} onOpenChange={setShowAddAddress}>
                    <DialogTrigger asChild>
                      <Button size="sm" className="rounded-full">
                        <Plus className="h-4 w-4 me-1" />
                        {text.addAddress}
                      </Button>
                    </DialogTrigger>
                    <DialogContent>
                      <DialogHeader>
                        <DialogTitle>{text.addAddress}</DialogTitle>
                      </DialogHeader>
                      <form onSubmit={handleAddAddress} className="space-y-4">
                        <div className="space-y-2">
                          <Label>{text.addressTitle}</Label>
                          <Input
                            value={newAddress.title}
                            onChange={(e) => setNewAddress({ ...newAddress, title: e.target.value })}
                            placeholder={language === 'ar' ? 'مثال: المنزل، العمل' : 'Ex: Home, Work'}
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
                                <SelectItem key={index} value={wilaya}>{wilaya}</SelectItem>
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
                        <div className="space-y-2">
                          <Label>{t('checkout.phone')}</Label>
                          <Input
                            value={newAddress.phone}
                            onChange={(e) => setNewAddress({ ...newAddress, phone: e.target.value })}
                            dir="ltr"
                          />
                        </div>
                        <Button type="submit" className="w-full rounded-full">{text.addAddress}</Button>
                      </form>
                    </DialogContent>
                  </Dialog>
                </div>

                {addresses.length > 0 ? (
                  <div className="grid sm:grid-cols-2 gap-4">
                    {addresses.map((addr) => (
                      <div key={addr.address_id} className="border rounded-xl p-4">
                        <div className="flex items-start justify-between mb-2">
                          <div className="flex items-center gap-2">
                            <MapPin className="h-4 w-4 text-primary" />
                            <span className="font-medium">{addr.title || text.addresses}</span>
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
                        <p className="text-sm text-muted-foreground">{addr.address}</p>
                        <p className="text-sm text-muted-foreground">{addr.wilaya}</p>
                        {addr.phone && <p className="text-sm text-muted-foreground" dir="ltr">{addr.phone}</p>}
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-12">
                    <MapPin className="h-20 w-20 mx-auto text-muted-foreground/30 mb-4" />
                    <h3 className="text-xl font-bold mb-2">{text.noAddresses}</h3>
                  </div>
                )}
              </div>
            )}

            {/* Browsing History Tab */}
            {activeTab === 'history' && (
              <div className="bg-card rounded-2xl border shadow-sm p-6">
                <div className="flex items-center justify-between mb-6">
                  <h2 className="text-xl font-bold">{text.browsingHistory}</h2>
                  {browsingHistory.length > 0 && (
                    <Button variant="outline" size="sm" className="rounded-full" onClick={handleClearHistory}>
                      <Trash2 className="h-4 w-4 me-1" />
                      {text.clearHistory}
                    </Button>
                  )}
                </div>

                {browsingHistory.length > 0 ? (
                  <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    {browsingHistory.map((item, index) => (
                      <Link 
                        key={index} 
                        to={`/products/${item.product_id}`}
                        className="group border rounded-xl overflow-hidden"
                      >
                        <div className="aspect-square relative">
                          <img 
                            src={item.product?.images?.[0] || 'https://via.placeholder.com/200'} 
                            alt={item.product?.[`name_${language}`]}
                            className="w-full h-full object-cover group-hover:scale-105 transition-transform"
                          />
                          {item.product?.old_price && (
                            <Badge className="absolute top-2 start-2 bg-red-500 text-xs">
                              -{Math.round((1 - item.product.price / item.product.old_price) * 100)}%
                            </Badge>
                          )}
                        </div>
                        <div className="p-2">
                          <h3 className="text-xs truncate mb-1">
                            {item.product?.[`name_${language}`] || item.product?.name_ar}
                          </h3>
                          <p className="font-bold text-sm text-primary">{formatPrice(item.product?.price || 0)}</p>
                        </div>
                      </Link>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-12">
                    <History className="h-20 w-20 mx-auto text-muted-foreground/30 mb-4" />
                    <h3 className="text-xl font-bold mb-2">{text.noHistory}</h3>
                    <p className="text-muted-foreground mb-6">{text.noHistoryDesc}</p>
                    <Button onClick={() => navigate('/products')} className="rounded-full">
                      {text.shopNow}
                    </Button>
                  </div>
                )}
              </div>
            )}
          </main>
        </div>
      </div>

      {/* Order Detail Dialog */}
      <Dialog open={showOrderDetail} onOpenChange={setShowOrderDetail}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <Package className="h-5 w-5" />
              {text.orderNumber} #{selectedOrder?.order_id?.slice(-8)}
            </DialogTitle>
          </DialogHeader>
          {selectedOrder && (
            <div className="space-y-4">
              <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                <span className="text-sm text-muted-foreground">{text.orderStatus}</span>
                <Badge className={orderStatusConfig[selectedOrder.status]?.color}>
                  {orderStatusConfig[selectedOrder.status]?.label}
                </Badge>
              </div>
              
              <div className="space-y-3">
                {selectedOrder.items?.map((item, idx) => (
                  <div key={idx} className="flex gap-3 p-3 border rounded-lg">
                    <img 
                      src={item.product?.images?.[0] || 'https://via.placeholder.com/80'} 
                      alt={item.product?.[`name_${language}`]}
                      className="w-16 h-16 rounded-lg object-cover"
                    />
                    <div className="flex-1">
                      <h4 className="font-medium text-sm">{item.product?.[`name_${language}`] || item.product?.name_ar}</h4>
                      <p className="text-sm text-muted-foreground">x{item.quantity}</p>
                      <p className="font-bold text-primary">{formatPrice(item.product?.price * item.quantity)}</p>
                    </div>
                  </div>
                ))}
              </div>

              <div className="border-t pt-3">
                <div className="flex justify-between text-lg font-bold">
                  <span>{text.orderTotal}</span>
                  <span className="text-primary">{formatPrice(selectedOrder.total_amount)}</span>
                </div>
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default ProfilePage;
