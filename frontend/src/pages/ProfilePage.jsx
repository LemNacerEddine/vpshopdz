import React, { useState, useEffect } from 'react';
import { useNavigate, Link, useSearchParams } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useAuth } from '@/contexts/AuthContext';
import { useCart } from '@/contexts/CartContext';
import { useStoreSettings } from '@/contexts/StoreSettingsContext';
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
import { generateHtmlInvoice } from '@/lib/invoiceHtml';
import {
  User,
  Package,
  Heart,
  MapPin,
  ChevronRight,
  ChevronLeft,
  ChevronDown,
  ChevronUp,
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
  CreditCard,
  Calendar,
  Printer,
  Pencil
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
  const { storeInfo } = useStoreSettings();
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
  // ✅ التعديل 1: إضافة full_name
  const [newAddress, setNewAddress] = useState({ title: '', full_name: '', address: '', wilaya: '', commune: '', phone: '', isDefault: false });
  const [communes, setCommunes] = useState([]);
  const [newAddressCommunes, setNewAddressCommunes] = useState([]);
  const [showEditAddress, setShowEditAddress] = useState(false);
  const [editAddress, setEditAddress] = useState(null);
  const [editAddressCommunes, setEditAddressCommunes] = useState([]);
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    address: '',
    wilaya: '',
    commune: ''
  });

  // Get active tab from URL - default to 'profile' instead of 'orders'
  const activeTab = searchParams.get('tab') || 'profile';

  const setActiveTab = (tab) => {
    setSearchParams({ tab });
  };

  const [ordersExpanded, setOrdersExpanded] = useState(true);

  const ChevronIcon = isRTL ? ChevronLeft : ChevronRight;

  useEffect(() => {
    if (!user && !authLoading) {
      navigate('/login', { state: { from: { pathname: '/profile' } } });
    }
  }, [user, authLoading, navigate]);

  useEffect(() => {
    if (user) {
      // First set user data
      setFormData({
        name: user.name || '',
        phone: user.phone || '',
        address: user.address || '',
        wilaya: user.wilaya || '',
        commune: user.commune || ''
      });
      fetchOrders();
      fetchWishlist();
      fetchWilayas();
      fetchBrowsingHistory();

      // Then fetch addresses and update formData with default address
      fetchAddresses().then(() => {
        // This will be handled after addresses are fetched
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
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

  // ✅ التعديل 4: إضافة console.log
  const fetchAddresses = async () => {
    try {
      const response = await axios.get(`${API}/addresses`, { withCredentials: true });
      console.log('Fetched addresses:', response.data);
      setAddresses(response.data);

      // Auto-fill profile address fields from default address
      const defaultAddress = response.data.find(addr => addr.is_default);
      if (defaultAddress) {
        setFormData(prev => ({
          ...prev,
          address: defaultAddress.address_line || prev.address,
          wilaya: defaultAddress.wilaya || prev.wilaya,
          commune: defaultAddress.commune || prev.commune
        }));
        console.log('Profile fields updated from default address:', defaultAddress);
      }
    } catch (error) {
      console.error('Error fetching addresses:', error);
      if (error.response?.status !== 404) {
        toast.error(t('common.error'));
      }
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

  const fetchCommunesForWilaya = async (wilaya, setter) => {
    try {
      const code = parseInt(wilaya);
      if (code > 0) {
        const response = await axios.get(`${API}/communes?wilaya=${code}`);
        setter(response.data);
        return response.data;
      } else {
        const response = await axios.get(`${API}/communes?wilaya_name=${encodeURIComponent(wilaya)}`);
        setter(response.data);
        return response.data;
      }
    } catch (error) {
      console.error('Error fetching communes:', error);
      setter([]);
      return [];
    }
  };

  // Fetch communes when profile wilaya changes
  useEffect(() => {
    if (formData.wilaya) {
      fetchCommunesForWilaya(formData.wilaya, setCommunes);
    } else {
      setCommunes([]);
    }
  }, [formData.wilaya]);

  // Fetch communes when new address wilaya changes
  useEffect(() => {
    if (newAddress.wilaya) {
      fetchCommunesForWilaya(newAddress.wilaya, setNewAddressCommunes);
    } else {
      setNewAddressCommunes([]);
    }
  }, [newAddress.wilaya]);

  // Fetch communes when edit address wilaya changes
  useEffect(() => {
    if (editAddress?.wilaya) {
      fetchCommunesForWilaya(editAddress.wilaya, setEditAddressCommunes);
    } else {
      setEditAddressCommunes([]);
    }
  }, [editAddress?.wilaya]);

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

  // ✅ التعديل 2: استبدال handleAddAddress كاملة
  const handleAddAddress = async (e) => {
    e.preventDefault();
    try {
      // Map frontend fields to backend expected fields
      const addressData = {
        label: newAddress.title || 'المنزل',
        full_name: newAddress.full_name || user?.name || '',
        phone: newAddress.phone || user?.phone || '',
        wilaya: newAddress.wilaya,
        commune: newAddress.commune || null,
        address_line: newAddress.address,
        is_default: newAddress.isDefault || false
      };

      console.log('Sending address data:', addressData);

      const response = await axios.post(`${API}/addresses`, addressData, {
        withCredentials: true,
        headers: {
          'Content-Type': 'application/json'
        }
      });

      console.log('Address created:', response.data);

      toast.success(language === 'ar' ? 'تمت إضافة العنوان' : 'Address added');
      setShowAddAddress(false);
      setNewAddress({ title: '', full_name: '', address: '', wilaya: '', commune: '', phone: '', isDefault: false });
      fetchAddresses();
    } catch (error) {
      console.error('Add address error:', error);
      const errorMessage = error.response?.data?.detail || error.response?.data?.message || t('common.error');
      toast.error(errorMessage);
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

  // ✅ التعديل 3: إضافة handleSetDefaultAddress
  const handleSetDefaultAddress = async (addressId) => {
    try {
      await axios.put(`${API}/addresses/${addressId}/default`, {}, { withCredentials: true });
      toast.success(language === 'ar' ? 'تم تعيين العنوان الافتراضي' : 'Default address set');
      fetchAddresses();
    } catch (error) {
      console.error('Set default address error:', error);
      toast.error(t('common.error'));
    }
  };

  const handleOpenEditAddress = (addr) => {
    setEditAddress({
      address_id: addr.address_id,
      title: addr.label || '',
      full_name: addr.full_name || '',
      wilaya: addr.wilaya || '',
      commune: addr.commune || '',
      address: addr.address_line || '',
      phone: addr.phone || '',
      isDefault: addr.is_default || false
    });
    setShowEditAddress(true);
  };

  const handleEditAddress = async (e) => {
    e.preventDefault();
    try {
      const addressData = {
        label: editAddress.title || 'المنزل',
        full_name: editAddress.full_name,
        phone: editAddress.phone,
        wilaya: editAddress.wilaya,
        commune: editAddress.commune || null,
        address_line: editAddress.address,
        is_default: editAddress.isDefault || false
      };

      await axios.put(`${API}/addresses/${editAddress.address_id}`, addressData, {
        withCredentials: true,
        headers: { 'Content-Type': 'application/json' }
      });

      toast.success(language === 'ar' ? 'تم تحديث العنوان' : 'Address updated');
      setShowEditAddress(false);
      setEditAddress(null);
      fetchAddresses();
    } catch (error) {
      console.error('Edit address error:', error);
      const errorMessage = error.response?.data?.detail || error.response?.data?.message || t('common.error');
      toast.error(errorMessage);
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

  // Sidebar menu items - Orders will be rendered separately above these
  const sidebarItems = [
    { id: 'profile', icon: User, label: text.profile },
    { id: 'reviews', icon: Star, label: text.reviews, disabled: true },
    { id: 'wishlist', icon: Heart, label: text.wishlist, count: wishlist.length },
    { id: 'coupons', icon: Ticket, label: text.coupons, disabled: true },
    { id: 'history', icon: History, label: text.browsingHistory, count: browsingHistory.length },
    { id: 'addresses', icon: MapPin, label: text.addresses, count: addresses.length },
    { id: 'payment', icon: CreditCard, label: text.paymentMethods, disabled: true },
    { id: 'security', icon: Shield, label: text.accountSecurity, disabled: true },
    { id: 'notifications', icon: Bell, label: text.notifications, disabled: true },
  ];

  // Order status sub-menu items
  const orderSubItems = [
    { id: 'all', label: text.allOrders, filter: 'all' },
    { id: 'pending', label: text.processing, filter: 'pending' },
    { id: 'shipped', label: text.shipped, filter: 'shipped' },
    { id: 'delivered', label: text.delivered, filter: 'delivered' },
    { id: 'cancelled', label: text.returns, filter: 'cancelled' },
  ];

  // Filter orders
  const filteredOrders = orders.filter(order => {
    const matchesSearch = orderSearchQuery === '' ||
      order.order_id?.toLowerCase().includes(orderSearchQuery.toLowerCase());
    const matchesStatus = orderStatusFilter === 'all' || order.status === orderStatusFilter;
    return matchesSearch && matchesStatus;
  });

  const generateReceipt = (order) => {
    try {
      // Build receipt translations matching invoice format
      const receiptTranslations = {
        ...text,
        invoice: language === 'ar' ? 'وصل الطلب' : language === 'fr' ? 'Reçu de commande' : 'Order Receipt',
        pending: language === 'ar' ? 'قيد الانتظار' : language === 'fr' ? 'En attente' : 'Pending',
        confirmed: language === 'ar' ? 'مؤكد' : language === 'fr' ? 'Confirmé' : 'Confirmed',
        processing: language === 'ar' ? 'قيد التحضير' : language === 'fr' ? 'En préparation' : 'Processing',
        shipped: language === 'ar' ? 'تم الشحن' : language === 'fr' ? 'Expédié' : 'Shipped',
        delivered: language === 'ar' ? 'تم التوصيل' : language === 'fr' ? 'Livré' : 'Delivered',
        cancelled: language === 'ar' ? 'ملغي' : language === 'fr' ? 'Annulé' : 'Cancelled',
      };
      generateHtmlInvoice(order, storeInfo, language, receiptTranslations);
      toast.success(language === 'ar' ? 'تم فتح الوصل' : language === 'fr' ? 'Reçu ouvert' : 'Receipt opened');
    } catch (error) {
      console.error('Error generating receipt:', error);
      toast.error(language === 'ar' ? 'خطأ في إنشاء الوصل' : 'Error generating receipt');
    }
  };

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
                  {/* Profile - First Item */}
                  <button
                    onClick={() => setActiveTab('profile')}
                    className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all mb-1 ${activeTab === 'profile'
                      ? 'bg-primary text-primary-foreground font-medium'
                      : 'hover:bg-muted'
                      }`}
                  >
                    <User className="h-4 w-4 shrink-0" />
                    <span className="flex-1 text-start">{text.profile}</span>
                    <ChevronIcon className="h-4 w-4 opacity-50" />
                  </button>

                  {/* Orders with Expandable Sub-menu */}
                  <div className="mb-1">
                    <button
                      onClick={() => {
                        setOrdersExpanded(!ordersExpanded);
                        setActiveTab('orders');
                      }}
                      className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all ${activeTab === 'orders'
                        ? 'bg-primary text-primary-foreground font-medium'
                        : 'hover:bg-muted'
                        }`}
                    >
                      <Package className="h-4 w-4 shrink-0" />
                      <span className="flex-1 text-start">{text.orders}</span>
                      {orders.length > 0 && (
                        <Badge variant={activeTab === 'orders' ? "secondary" : "outline"} className="text-xs">
                          {orders.length}
                        </Badge>
                      )}
                      {ordersExpanded ? (
                        <ChevronUp className="h-4 w-4 opacity-50" />
                      ) : (
                        <ChevronDown className="h-4 w-4 opacity-50" />
                      )}
                    </button>

                    {/* Orders Sub-menu */}
                    {ordersExpanded && (
                      <div className={`${isRTL ? 'mr-6' : 'ml-6'} mt-1 space-y-0.5 border-s-2 border-muted ps-2`}>
                        {orderSubItems.map((subItem) => {
                          const count = subItem.filter === 'all'
                            ? orders.length
                            : orders.filter(o => o.status === subItem.filter).length;
                          return (
                            <button
                              key={subItem.id}
                              onClick={() => {
                                setActiveTab('orders');
                                setOrderStatusFilter(subItem.filter);
                              }}
                              className={`w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all ${activeTab === 'orders' && orderStatusFilter === subItem.filter
                                ? 'bg-primary/10 text-primary font-medium'
                                : 'hover:bg-muted text-muted-foreground'
                                }`}
                            >
                              <span className="flex-1 text-start">{subItem.label}</span>
                              {count > 0 && (
                                <span className="text-[10px] bg-muted px-1.5 py-0.5 rounded-full">
                                  {count}
                                </span>
                              )}
                            </button>
                          );
                        })}
                      </div>
                    )}
                  </div>

                  {/* Other Menu Items (excluding profile since it's already rendered) */}
                  {sidebarItems.filter(item => item.id !== 'profile').map((item) => (
                    <button
                      key={item.id}
                      onClick={() => !item.disabled && setActiveTab(item.id)}
                      disabled={item.disabled}
                      className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all ${activeTab === item.id
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
                  className={`flex flex-col items-center gap-1 px-3 py-1 ${activeTab === item.id ? 'text-primary' : 'text-muted-foreground'
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
                        <div key={order.order_id} className="bg-card rounded-2xl border shadow-sm overflow-hidden">
                          {/* Order Header */}
                          <div className="bg-muted/30 px-4 py-3 border-b flex items-center justify-between">
                            <div className="flex items-center gap-4">
                              <div>
                                <p className="text-xs text-muted-foreground mb-0.5">{text.orderNumber}</p>
                                <p className="font-bold text-lg font-mono">{order.order_id}</p>
                              </div>
                              <div className="h-8 w-px bg-border" />
                              <div>
                                <p className="text-xs text-muted-foreground mb-0.5">{text.orderDate}</p>
                                <p className="font-medium">
                                  {new Date(order.created_at).toLocaleDateString(language === 'ar' ? 'ar-DZ' : 'fr-FR', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric'
                                  })}
                                </p>
                              </div>
                            </div>
                            <div className={`flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium ${order.status === 'delivered' ? 'bg-green-100 text-green-700' :
                              order.status === 'shipped' ? 'bg-purple-100 text-purple-700' :
                                order.status === 'cancelled' ? 'bg-red-100 text-red-700' :
                                  'bg-yellow-100 text-yellow-700'
                              }`}>
                              <StatusIcon className="h-4 w-4" />
                              <span>{statusConfig.label}</span>
                            </div>
                          </div>

                          {/* Order Items */}
                          <div className="p-4">
                            <div className="flex gap-3 mb-4 overflow-x-auto pb-1">
                              {order.items?.map((item, idx) => (
                                <div key={idx} className="shrink-0">
                                  <div className="w-20 h-20 rounded-xl overflow-hidden bg-muted border">
                                    <img
                                      src={item.product_image || `https://via.placeholder.com/80/22c55e/ffffff?text=${encodeURIComponent(item.product_name?.charAt(0) || 'P')}`}
                                      alt={item.product_name}
                                      className="w-full h-full object-cover"
                                      onError={(e) => {
                                        e.target.src = `https://via.placeholder.com/80/22c55e/ffffff?text=${encodeURIComponent(item.product_name?.charAt(0) || 'P')}`;
                                      }}
                                    />
                                  </div>
                                  <p className="text-xs text-center mt-1 text-muted-foreground">x{item.quantity}</p>
                                </div>
                              ))}
                            </div>

                            {/* Order Footer */}
                            <div className="flex items-center justify-between pt-3 border-t">
                              <div>
                                <span className="text-sm text-muted-foreground">
                                  {order.items?.length} {language === 'ar' ? 'منتج' : language === 'fr' ? 'produit(s)' : 'product(s)'}
                                </span>
                                <span className="mx-2 text-muted-foreground">•</span>
                                <span className="font-bold text-xl text-primary">
                                  {formatPrice(order.total || 0)}
                                </span>
                              </div>
                              <Button
                                variant="default"
                                size="sm"
                                className="rounded-full"
                                onClick={() => {
                                  setSelectedOrder(order);
                                  setShowOrderDetail(true);
                                }}
                              >
                                <Eye className="h-4 w-4 me-2" />
                                {text.viewOrder}
                              </Button>
                            </div>
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

                {/* Google OAuth completion prompt */}
                {searchParams.get('complete_profile') === '1' && (!user.phone || !user.wilaya || !user.address) && (
                  <div className="mb-6 p-4 bg-primary/10 border border-primary/20 rounded-lg">
                    <div className="flex items-start gap-3">
                      <div className="h-10 w-10 rounded-full bg-primary/20 flex items-center justify-center flex-shrink-0">
                        <User className="h-5 w-5 text-primary" />
                      </div>
                      <div className="flex-1">
                        <h4 className="font-semibold text-primary mb-1">
                          {language === 'ar' ? 'مرحباً بك! أكمل معلوماتك' : 'Welcome! Complete Your Profile'}
                        </h4>
                        <p className="text-sm text-muted-foreground">
                          {language === 'ar'
                            ? 'يرجى إكمال رقم الهاتف والولاية والعنوان لإتمام عملية التسجيل'
                            : 'Please complete your phone number, wilaya, and address to finish registration'}
                        </p>
                      </div>
                    </div>
                  </div>
                )}

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
                        onValueChange={(value) => setFormData({ ...formData, wilaya: value, commune: '' })}
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

                    {communes.length > 0 && (
                      <div className="space-y-2">
                        <Label htmlFor="commune">{t('checkout.commune')}</Label>
                        <Select
                          value={formData.commune}
                          onValueChange={(value) => setFormData({ ...formData, commune: value })}
                        >
                          <SelectTrigger>
                            <SelectValue placeholder={t('checkout.selectCommune')} />
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
                        {/* ✅ التعديل 5: Address Title */}
                        <div className="space-y-2">
                          <Label>{text.addressTitle}</Label>
                          <Input
                            value={newAddress.title}
                            onChange={(e) => setNewAddress({ ...newAddress, title: e.target.value })}
                            placeholder={language === 'ar' ? 'مثال: المنزل، العمل' : 'Ex: Home, Work'}
                          />
                        </div>

                        {/* ✅ التعديل 5: Full Name Field */}
                        <div className="space-y-2">
                          <Label>{language === 'ar' ? 'الاسم الكامل' : 'Full Name'}</Label>
                          <Input
                            value={newAddress.full_name || user?.name || ''}
                            onChange={(e) => setNewAddress({ ...newAddress, full_name: e.target.value })}
                            placeholder={user?.name || ''}
                            required
                          />
                        </div>

                        <div className="space-y-2">
                          <Label>{t('checkout.wilaya')}</Label>
                          <Select
                            value={newAddress.wilaya}
                            onValueChange={(value) => setNewAddress({ ...newAddress, wilaya: value, commune: '' })}
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

                        {newAddressCommunes.length > 0 && (
                          <div className="space-y-2">
                            <Label>{t('checkout.commune')}</Label>
                            <Select
                              value={newAddress.commune}
                              onValueChange={(value) => setNewAddress({ ...newAddress, commune: value })}
                            >
                              <SelectTrigger>
                                <SelectValue placeholder={t('checkout.selectCommune')} />
                              </SelectTrigger>
                              <SelectContent>
                                {newAddressCommunes.map((commune, index) => (
                                  <SelectItem key={index} value={commune}>
                                    {commune}
                                  </SelectItem>
                                ))}
                              </SelectContent>
                            </Select>
                          </div>
                        )}

                        <div className="space-y-2">
                          <Label>{t('checkout.address')}</Label>
                          <Input
                            value={newAddress.address}
                            onChange={(e) => setNewAddress({ ...newAddress, address: e.target.value })}
                          />
                        </div>

                        {/* ✅ التعديل 6: Phone Field Update */}
                        <div className="space-y-2">
                          <Label>{t('checkout.phone')}</Label>
                          <Input
                            value={newAddress.phone || user?.phone || ''}
                            onChange={(e) => setNewAddress({ ...newAddress, phone: e.target.value })}
                            placeholder={user?.phone || '0XXX XX XX XX'}
                            dir="ltr"
                            required
                          />
                        </div>

                        {/* ✅ التعديل 7: Default Address Checkbox */}
                        <div className="flex items-center space-x-2 space-x-reverse">
                          <input
                            type="checkbox"
                            id="isDefault"
                            checked={newAddress.isDefault || false}
                            onChange={(e) => setNewAddress({ ...newAddress, isDefault: e.target.checked })}
                            className="rounded"
                          />
                          <Label htmlFor="isDefault" className="cursor-pointer">
                            {language === 'ar' ? 'تعيين كعنوان افتراضي' : 'Set as default address'}
                          </Label>
                        </div>

                        <Button type="submit" className="w-full rounded-full">{text.addAddress}</Button>
                      </form>
                    </DialogContent>
                  </Dialog>

                  {/* Edit Address Dialog */}
                  <Dialog open={showEditAddress} onOpenChange={(open) => { setShowEditAddress(open); if (!open) setEditAddress(null); }}>
                    <DialogContent>
                      <DialogHeader>
                        <DialogTitle>{language === 'ar' ? 'تعديل العنوان' : 'Edit Address'}</DialogTitle>
                      </DialogHeader>
                      {editAddress && (
                        <form onSubmit={handleEditAddress} className="space-y-4">
                          <div className="space-y-2">
                            <Label>{text.addressTitle}</Label>
                            <Input
                              value={editAddress.title}
                              onChange={(e) => setEditAddress({ ...editAddress, title: e.target.value })}
                              placeholder={language === 'ar' ? 'مثال: المنزل، العمل' : 'Ex: Home, Work'}
                            />
                          </div>

                          <div className="space-y-2">
                            <Label>{language === 'ar' ? 'الاسم الكامل' : 'Full Name'}</Label>
                            <Input
                              value={editAddress.full_name}
                              onChange={(e) => setEditAddress({ ...editAddress, full_name: e.target.value })}
                              required
                            />
                          </div>

                          <div className="space-y-2">
                            <Label>{t('checkout.wilaya')}</Label>
                            <Select
                              value={editAddress.wilaya}
                              onValueChange={(value) => setEditAddress({ ...editAddress, wilaya: value, commune: '' })}
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

                          {editAddressCommunes.length > 0 && (
                            <div className="space-y-2">
                              <Label>{t('checkout.commune')}</Label>
                              <Select
                                value={editAddress.commune}
                                onValueChange={(value) => setEditAddress({ ...editAddress, commune: value })}
                              >
                                <SelectTrigger>
                                  <SelectValue placeholder={t('checkout.selectCommune')} />
                                </SelectTrigger>
                                <SelectContent>
                                  {editAddressCommunes.map((commune, index) => (
                                    <SelectItem key={index} value={commune}>
                                      {commune}
                                    </SelectItem>
                                  ))}
                                </SelectContent>
                              </Select>
                            </div>
                          )}

                          <div className="space-y-2">
                            <Label>{t('checkout.address')}</Label>
                            <Input
                              value={editAddress.address}
                              onChange={(e) => setEditAddress({ ...editAddress, address: e.target.value })}
                            />
                          </div>

                          <div className="space-y-2">
                            <Label>{t('checkout.phone')}</Label>
                            <Input
                              value={editAddress.phone}
                              onChange={(e) => setEditAddress({ ...editAddress, phone: e.target.value })}
                              placeholder="0XXX XX XX XX"
                              dir="ltr"
                              required
                            />
                          </div>

                          <div className="flex items-center space-x-2 space-x-reverse">
                            <input
                              type="checkbox"
                              id="editIsDefault"
                              checked={editAddress.isDefault || false}
                              onChange={(e) => setEditAddress({ ...editAddress, isDefault: e.target.checked })}
                              className="rounded"
                            />
                            <Label htmlFor="editIsDefault" className="cursor-pointer">
                              {language === 'ar' ? 'تعيين كعنوان افتراضي' : 'Set as default address'}
                            </Label>
                          </div>

                          <Button type="submit" className="w-full rounded-full">
                            {language === 'ar' ? 'حفظ التغييرات' : 'Save Changes'}
                          </Button>
                        </form>
                      )}
                    </DialogContent>
                  </Dialog>
                </div>

                {addresses.length > 0 ? (
                  <div className="grid sm:grid-cols-2 gap-4">
                    {addresses.map((addr) => (
                      <div key={addr.address_id} className="border rounded-xl p-4 relative cursor-pointer hover:border-primary/50 transition-colors" onClick={() => handleOpenEditAddress(addr)}>
                        <div className="flex items-start justify-between mb-3">
                          <div className="flex items-center gap-2">
                            <MapPin className="h-4 w-4 text-primary" />
                            <span className="font-medium">{addr.label || 'المنزل'}</span>
                            {addr.is_default && (
                              <Badge variant="secondary" className="text-xs">
                                {language === 'ar' ? 'افتراضي' : 'Default'}
                              </Badge>
                            )}
                          </div>
                          <div className="flex items-center gap-1">
                            <Button
                              variant="ghost"
                              size="icon"
                              className="h-8 w-8 text-muted-foreground hover:text-primary"
                              onClick={(e) => { e.stopPropagation(); handleOpenEditAddress(addr); }}
                            >
                              <Pencil className="h-4 w-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="icon"
                              className="h-8 w-8 text-destructive"
                              onClick={(e) => { e.stopPropagation(); handleDeleteAddress(addr.address_id); }}
                            >
                              <Trash2 className="h-4 w-4" />
                            </Button>
                          </div>
                        </div>

                        <div className="space-y-1">
                          <p className="text-sm font-medium">{addr.full_name}</p>
                          <p className="text-sm text-muted-foreground">{addr.address_line}</p>
                          <p className="text-sm text-muted-foreground">{addr.commune && `${addr.commune} - `}{addr.wilaya}</p>
                          {addr.phone && (
                            <p className="text-sm text-muted-foreground" dir="ltr">
                              📞 {addr.phone}
                            </p>
                          )}
                        </div>

                        {!addr.is_default && (
                          <Button
                            variant="outline"
                            size="sm"
                            className="w-full mt-3 rounded-full"
                            onClick={(e) => { e.stopPropagation(); handleSetDefaultAddress(addr.address_id); }}
                          >
                            {language === 'ar' ? 'تعيين كافتراضي' : 'Set as Default'}
                          </Button>
                        )}
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
        <DialogContent className="max-w-3xl">
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2 text-xl">
              <Package className="h-6 w-6 text-primary" />
              {text.orderNumber} #{selectedOrder?.order_id}
            </DialogTitle>
          </DialogHeader>

          {selectedOrder && (
            <div className="space-y-6">
              {/* Header Info */}
              <div className="flex flex-wrap gap-4 items-center justify-between p-4 bg-muted/30 rounded-xl border">
                <div className="flex items-center gap-2">
                  <Calendar className="h-4 w-4 text-muted-foreground" />
                  <span className="text-sm font-medium">
                    {new Date(selectedOrder.created_at).toLocaleDateString(language === 'ar' ? 'ar-DZ' : 'en-US', {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    })}
                  </span>
                </div>
                {orderStatusConfig[selectedOrder.status] && (
                  <Badge className={`${orderStatusConfig[selectedOrder.status].color} px-3 py-1 text-sm`}>
                    {orderStatusConfig[selectedOrder.status].label}
                  </Badge>
                )}
              </div>

              <div className="grid md:grid-cols-2 gap-6">
                {/* Order Info */}
                <div className="space-y-4">
                  <h3 className="font-bold flex items-center gap-2 text-lg">
                    <User className="h-4 w-4" />
                    {language === 'ar' ? 'معلومات العميل' : 'Customer Info'}
                  </h3>
                  <div className="space-y-3 p-4 border rounded-xl bg-card">
                    <div className="flex items-center gap-3">
                      <div className="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                        <User className="h-4 w-4 text-primary" />
                      </div>
                      <div>
                        <p className="text-sm text-muted-foreground">{language === 'ar' ? 'الاسم' : 'Name'}</p>
                        <p className="font-medium">{selectedOrder.customer_name}</p>
                      </div>
                    </div>
                    <div className="flex items-center gap-3">
                      <div className="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                        <Phone className="h-4 w-4 text-primary" />
                      </div>
                      <div>
                        <p className="text-sm text-muted-foreground">{language === 'ar' ? 'الهاتف' : 'Phone'}</p>
                        <p className="font-medium" dir="ltr">{selectedOrder.customer_phone}</p>
                      </div>
                    </div>
                  </div>

                  <h3 className="font-bold flex items-center gap-2 text-lg mt-4">
                    <MapPin className="h-4 w-4" />
                    {language === 'ar' ? 'عنوان التوصيل' : 'Shipping Address'}
                  </h3>
                  <div className="p-4 border rounded-xl bg-card">
                    <div className="flex items-start gap-3">
                      <div className="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                        <MapPin className="h-4 w-4 text-primary" />
                      </div>
                      <div>
                        <p className="font-medium">{selectedOrder.wilaya}</p>
                        <p className="text-sm text-muted-foreground">{selectedOrder.shipping_address}</p>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Order Summary & Payment */}
                <div className="space-y-4">
                  <h3 className="font-bold flex items-center gap-2 text-lg">
                    <CreditCard className="h-4 w-4" />
                    {language === 'ar' ? 'الدفع' : language === 'fr' ? 'Paiement' : 'Payment'}
                  </h3>
                  <div className="p-4 border rounded-xl bg-card">
                    <div className="flex items-center justify-between mb-4">
                      <span className="text-muted-foreground">{text.paymentMethod}</span>
                      <Badge variant="outline" className="flex items-center gap-1">
                        {selectedOrder.payment_method === 'cod' ? (
                          <>
                            <Truck className="h-3 w-3" />
                            {language === 'ar' ? 'الدفع عند الاستلام' : language === 'fr' ? 'Paiement à la livraison' : 'Cash on Delivery'}
                          </>
                        ) : selectedOrder.payment_method}
                      </Badge>
                    </div>
                    <div className="pt-4 border-t space-y-2">
                      <div className="flex justify-between text-sm">
                        <span className="text-muted-foreground">{language === 'ar' ? 'المنتجات' : language === 'fr' ? 'Sous-total' : 'Subtotal'}</span>
                        <span>{formatPrice(selectedOrder.subtotal || (selectedOrder.total + parseFloat(selectedOrder.discount_amount || 0) - (selectedOrder.shipping_cost || 0)))}</span>
                      </div>
                      {parseFloat(selectedOrder.discount_amount) > 0 && (
                        <div className="flex justify-between text-sm text-green-600">
                          <span>
                            {language === 'ar' ? 'الخصم' : language === 'fr' ? 'Remise' : 'Discount'}
                            {parseFloat(selectedOrder.discount_percentage) > 0 && (
                              <span className="text-xs ms-1">({selectedOrder.discount_percentage}%)</span>
                            )}
                          </span>
                          <span className="font-medium">-{formatPrice(selectedOrder.discount_amount)}</span>
                        </div>
                      )}
                      <div className="flex justify-between text-sm">
                        <span className="text-muted-foreground">{language === 'ar' ? 'التوصيل' : language === 'fr' ? 'Livraison' : 'Shipping'}</span>
                        {parseFloat(selectedOrder.shipping_cost) > 0 ? (
                          <span className="font-medium">{formatPrice(selectedOrder.shipping_cost)}</span>
                        ) : (
                          <span className="text-green-600 font-medium">{language === 'ar' ? 'مجاني' : language === 'fr' ? 'Gratuit' : 'Free'}</span>
                        )}
                      </div>
                      <div className="flex justify-between text-xl font-bold pt-2 border-t mt-2">
                        <span>{language === 'ar' ? 'المجموع' : language === 'fr' ? 'Total' : 'Total'}</span>
                        <span className="text-primary">{formatPrice(selectedOrder.total)}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {/* Items List */}
              <div className="space-y-4">
                <h3 className="font-bold flex items-center gap-2 text-lg">
                  <Package className="h-4 w-4" />
                  {language === 'ar' ? 'المنتجات' : 'Items'} ({selectedOrder.items?.length})
                </h3>
                <div className="space-y-3 max-h-60 overflow-y-auto custom-scrollbar p-1">
                  {selectedOrder.items?.map((item, idx) => (
                    <div key={idx} className="flex gap-4 p-3 border rounded-xl bg-card hover:border-primary/50 transition-colors">
                      <div className="h-16 w-16 rounded-lg overflow-hidden bg-muted border shrink-0">
                        <img
                          src={item.product_image || `https://via.placeholder.com/80/22c55e/ffffff?text=${encodeURIComponent(item.product_name?.charAt(0) || 'P')}`}
                          alt={item.product_name}
                          className="w-full h-full object-cover"
                          onError={(e) => {
                            e.target.src = `https://via.placeholder.com/80/22c55e/ffffff?text=${encodeURIComponent(item.product_name?.charAt(0) || 'P')}`;
                          }}
                        />
                      </div>
                      <div className="flex-1 min-w-0 flex flex-col justify-center">
                        <h4 className="font-medium text-sm truncate mb-1">{item.product_name}</h4>
                        <p className="text-xs text-muted-foreground">
                          {formatPrice(item.price)} x {item.quantity}
                        </p>
                      </div>
                      <div className="flex flex-col justify-center items-end">
                        <p className="font-bold text-primary">{formatPrice(item.price * item.quantity)}</p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              {/* Print Receipt Button */}
              <Button
                variant="outline"
                className="w-full rounded-full"
                onClick={() => generateReceipt(selectedOrder)}
              >
                <Printer className="h-4 w-4 me-2" />
                {language === 'ar' ? 'طباعة الوصل' : language === 'fr' ? 'Imprimer le reçu' : 'Print Receipt'}
              </Button>

            </div>
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default ProfilePage;