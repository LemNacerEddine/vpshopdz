import React, { useState, useEffect, useRef } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useAuth } from '@/contexts/AuthContext';
import { useCart } from '@/contexts/CartContext';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import {
  User,
  Package,
  Heart,
  MapPin,
  Clock,
  Star,
  Settings,
  LogOut,
  ChevronLeft,
  ChevronRight,
  Shield,
  Ticket,
  Bell,
  Trash2,
  LayoutDashboard
} from 'lucide-react';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

// Generate unique browser ID
const getBrowserId = () => {
  let browserId = localStorage.getItem('browser_id');
  if (!browserId) {
    browserId = 'browser_' + Math.random().toString(36).substring(2, 15);
    localStorage.setItem('browser_id', browserId);
  }
  return browserId;
};

export const UserMenu = ({ onClose }) => {
  const { language, isRTL, formatPrice } = useLanguage();
  const { user, logout, isAdmin } = useAuth();
  const { resetCart } = useCart();
  const navigate = useNavigate();
  const [browsingHistory, setBrowsingHistory] = useState([]);
  const [loading, setLoading] = useState(true);
  const menuRef = useRef(null);

  const ChevronIcon = isRTL ? ChevronLeft : ChevronRight;

  useEffect(() => {
    fetchBrowsingHistory();
  }, []);

  const fetchBrowsingHistory = async () => {
    try {
      setLoading(true);
      const response = await axios.get(`${API}/browsing-history?limit=6`, {
        withCredentials: true,
        headers: { 'X-Browser-ID': getBrowserId() }
      });
      setBrowsingHistory(response.data);
    } catch (error) {
      console.error('Error fetching browsing history:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleClearHistory = async () => {
    try {
      await axios.delete(`${API}/browsing-history`, {
        withCredentials: true,
        headers: { 'X-Browser-ID': getBrowserId() }
      });
      setBrowsingHistory([]);
    } catch (error) {
      console.error('Error clearing history:', error);
    }
  };

  const handleNavigate = (path) => {
    navigate(path);
    onClose?.();
  };

  const handleLogout = () => {
    // Only reset local cart state, don't clear server cart
    resetCart();
    logout();
    navigate('/');
    onClose?.();
  };

  const l = {
    ar: {
      welcome: 'مرحباً',
      viewProfile: 'عرض ملفك الشخصي',
      yourOrders: 'طلباتك',
      yourReviews: 'مراجعاتك',
      profile: 'الملف الشخصي',
      wishlist: 'قائمة الأمنيات',
      addresses: 'العناوين',
      browsingHistory: 'سجل التصفح',
      coupons: 'القسائم والعروض',
      accountSecurity: 'أمان الحساب',
      notifications: 'الإشعارات',
      logout: 'تسجيل الخروج',
      adminDashboard: 'لوحة التحكم',
      clearHistory: 'مسح السجل',
      noHistory: 'لم تشاهد أي منتجات بعد',
      viewAll: 'عرض الكل',
      recentlyViewed: 'شوهد مؤخراً'
    },
    fr: {
      welcome: 'Bonjour',
      viewProfile: 'Voir votre profil',
      yourOrders: 'Vos commandes',
      yourReviews: 'Vos avis',
      profile: 'Profil',
      wishlist: 'Liste de souhaits',
      addresses: 'Adresses',
      browsingHistory: 'Historique de navigation',
      coupons: 'Coupons et offres',
      accountSecurity: 'Sécurité du compte',
      notifications: 'Notifications',
      logout: 'Déconnexion',
      adminDashboard: 'Tableau de bord',
      clearHistory: 'Effacer l\'historique',
      noHistory: 'Vous n\'avez pas encore consulté de produits',
      viewAll: 'Voir tout',
      recentlyViewed: 'Vu récemment'
    },
    en: {
      welcome: 'Hello',
      viewProfile: 'View your profile',
      yourOrders: 'Your orders',
      yourReviews: 'Your reviews',
      profile: 'Profile',
      wishlist: 'Wishlist',
      addresses: 'Addresses',
      browsingHistory: 'Browsing History',
      coupons: 'Coupons & Offers',
      accountSecurity: 'Account Security',
      notifications: 'Notifications',
      logout: 'Log out',
      adminDashboard: 'Admin Dashboard',
      clearHistory: 'Clear History',
      noHistory: 'You haven\'t viewed any products yet',
      viewAll: 'View All',
      recentlyViewed: 'Recently Viewed'
    }
  };

  const text = l[language] || l.ar;

  // If admin, show simplified admin menu
  if (isAdmin) {
    return (
      <div
        ref={menuRef}
        className="absolute top-full end-0 mt-2 z-50 bg-card rounded-2xl shadow-2xl border overflow-hidden"
        style={{ width: '320px' }}
      >
        <div className="p-4">
          {/* Admin Header */}
          <div className="flex items-center gap-3 pb-4 mb-4 border-b">
            <div className="h-14 w-14 rounded-full bg-primary/10 flex items-center justify-center ring-2 ring-primary/30">
              <Shield className="h-7 w-7 text-primary" />
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-xs text-muted-foreground">{text.welcome}</p>
              <p className="font-bold text-lg truncate">{user?.name || 'مدير'}</p>
              <p className="text-xs text-primary font-medium">{language === 'ar' ? 'حساب المدير' : language === 'fr' ? 'Compte Admin' : 'Admin Account'}</p>
            </div>
          </div>

          {/* Admin Dashboard - Primary Action */}
          <Link
            to="/admin"
            onClick={() => onClose?.()}
            className="flex items-center gap-3 p-4 rounded-xl bg-primary text-white hover:bg-primary/90 transition-colors mb-3"
          >
            <LayoutDashboard className="h-6 w-6" />
            <div>
              <p className="font-semibold">{text.adminDashboard}</p>
              <p className="text-xs text-white/80">{language === 'ar' ? 'إدارة المتجر والطلبات' : language === 'fr' ? 'Gérer le magasin' : 'Manage store & orders'}</p>
            </div>
            <ChevronIcon className="h-5 w-5 ms-auto" />
          </Link>

          {/* Quick Admin Links */}
          <div className="space-y-1 mb-4">
            <Link
              to="/admin/orders?status=pending"
              onClick={() => onClose?.()}
              className="flex items-center gap-3 p-3 rounded-lg hover:bg-muted transition-colors"
            >
              <Package className="h-5 w-5 text-yellow-600" />
              <span className="text-sm">{language === 'ar' ? 'طلبات جديدة' : language === 'fr' ? 'Nouvelles commandes' : 'New Orders'}</span>
              <ChevronIcon className="h-4 w-4 ms-auto text-muted-foreground" />
            </Link>
            <Link
              to="/admin/products"
              onClick={() => onClose?.()}
              className="flex items-center gap-3 p-3 rounded-lg hover:bg-muted transition-colors"
            >
              <Package className="h-5 w-5 text-blue-600" />
              <span className="text-sm">{language === 'ar' ? 'إدارة المنتجات' : language === 'fr' ? 'Gérer les produits' : 'Manage Products'}</span>
              <ChevronIcon className="h-4 w-4 ms-auto text-muted-foreground" />
            </Link>
          </div>

          {/* Logout Button */}
          <div className="pt-3 border-t">
            <button
              onClick={handleLogout}
              className="w-full flex items-center gap-3 p-3 rounded-lg text-sm text-destructive hover:bg-destructive/10 transition-colors"
              data-testid="menu-logout"
            >
              <LogOut className="h-5 w-5" />
              {text.logout}
            </button>
          </div>
        </div>
      </div>
    );
  }

  // Regular customer menu - Profile first, then other items
  const menuItems = [
    { icon: Package, label: text.yourOrders, path: '/profile?tab=orders', testId: 'menu-orders' },
    { icon: Star, label: text.yourReviews, path: '/profile?tab=reviews', testId: 'menu-reviews' },
    { icon: Heart, label: text.wishlist, path: '/profile?tab=wishlist', testId: 'menu-wishlist' },
    { icon: Ticket, label: text.coupons, path: '/coupons', testId: 'menu-coupons', disabled: true },
    { icon: Clock, label: text.browsingHistory, path: '/profile?tab=history', testId: 'menu-history' },
    { icon: MapPin, label: text.addresses, path: '/profile?tab=addresses', testId: 'menu-addresses' },
    { icon: Shield, label: text.accountSecurity, path: '/profile?tab=security', testId: 'menu-security', disabled: true },
    { icon: Bell, label: text.notifications, path: '/notifications', testId: 'menu-notifications', disabled: true },
  ];

  return (
    <div
      ref={menuRef}
      className="absolute top-full end-0 mt-2 z-50 bg-card rounded-2xl shadow-2xl border overflow-hidden"
      style={{ width: '560px' }}
    >
      <div className="flex">
        {/* Left Side - Browsing History */}
        <div className="w-1/2 bg-muted/30 p-4 border-e">
          <div className="flex items-center justify-between mb-3">
            <h3 className="font-semibold text-sm flex items-center gap-2">
              <Clock className="h-4 w-4" />
              {text.browsingHistory}
            </h3>
            {browsingHistory.length > 0 && (
              <button
                onClick={handleClearHistory}
                className="text-xs text-muted-foreground hover:text-destructive transition-colors flex items-center gap-1"
              >
                <Trash2 className="h-3 w-3" />
                {text.clearHistory}
              </button>
            )}
          </div>

          {loading ? (
            <div className="grid grid-cols-3 gap-2">
              {[1, 2, 3, 4, 5, 6].map(i => (
                <div key={i} className="aspect-square rounded-lg bg-muted animate-pulse" />
              ))}
            </div>
          ) : browsingHistory.length > 0 ? (
            <div className="grid grid-cols-3 gap-2">
              {browsingHistory.map((item, index) => (
                <Link
                  key={index}
                  to={`/products/${item.product_id}`}
                  onClick={() => onClose?.()}
                  className="group relative aspect-square rounded-lg overflow-hidden bg-muted hover:ring-2 hover:ring-primary transition-all"
                >
                  <img
                    src={item.product?.images?.[0] || 'https://via.placeholder.com/100'}
                    alt={item.product?.[`name_${language}`] || item.product?.name_ar}
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform"
                  />
                  {item.product?.old_price && (
                    <div className="absolute top-1 start-1 bg-red-500 text-white text-[10px] px-1 rounded">
                      -{Math.round((1 - item.product.price / item.product.old_price) * 100)}%
                    </div>
                  )}
                  <div className="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/70 to-transparent p-1">
                    <p className="text-[10px] text-white font-medium truncate">
                      {formatPrice(item.product?.price || 0)}
                    </p>
                  </div>
                </Link>
              ))}
            </div>
          ) : (
            <div className="text-center py-8 text-muted-foreground text-sm">
              <Clock className="h-8 w-8 mx-auto mb-2 opacity-50" />
              {text.noHistory}
            </div>
          )}

          {browsingHistory.length > 0 && (
            <Link
              to="/profile?tab=history"
              onClick={() => onClose?.()}
              className="flex items-center justify-center gap-1 mt-3 text-xs text-primary hover:text-primary/80 font-medium"
            >
              {text.viewAll}
              <ChevronIcon className="h-3 w-3" />
            </Link>
          )}
        </div>

        {/* Right Side - Menu Items */}
        <div className="w-1/2 p-4">
          {/* User Info */}
          <div className="flex items-center gap-3 pb-3 mb-3 border-b">
            {user?.picture ? (
              <img
                src={user.picture}
                alt={user.name}
                className="h-12 w-12 rounded-full object-cover ring-2 ring-primary/20"
              />
            ) : (
              <div className="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                <User className="h-6 w-6 text-primary" />
              </div>
            )}
            <div className="min-w-0 flex-1">
              <p className="text-xs text-muted-foreground">{text.welcome}</p>
              <p className="font-semibold truncate">{user?.name || 'مستخدم'}</p>
            </div>
          </div>

          {/* View Profile Link */}
          <Link
            to="/profile"
            onClick={() => onClose?.()}
            className="flex items-center justify-between p-2 -mx-2 rounded-lg hover:bg-muted transition-colors text-sm text-primary font-medium mb-2"
          >
            {text.viewProfile}
            <ChevronIcon className="h-4 w-4" />
          </Link>

          {/* Menu Items */}
          <ScrollArea className="h-[340px] -mx-2">
            <div className="px-2 space-y-0.5">
              {menuItems.map((item, index) => (
                <button
                  key={index}
                  onClick={() => !item.disabled && handleNavigate(item.path)}
                  disabled={item.disabled}
                  data-testid={item.testId}
                  className={`w-full flex items-center gap-3 p-2 rounded-lg text-sm transition-colors ${item.disabled
                    ? 'opacity-50 cursor-not-allowed'
                    : item.highlight
                      ? 'text-primary bg-primary/5 hover:bg-primary/10 font-medium'
                      : 'hover:bg-muted'
                    }`}
                >
                  <item.icon className="h-4 w-4 shrink-0" />
                  <span className="truncate">{item.label}</span>
                </button>
              ))}
            </div>
          </ScrollArea>

          {/* Logout Button */}
          <div className="pt-3 mt-3 border-t">
            <button
              onClick={handleLogout}
              className="w-full flex items-center gap-3 p-2 rounded-lg text-sm text-destructive hover:bg-destructive/10 transition-colors"
              data-testid="menu-logout"
            >
              <LogOut className="h-4 w-4" />
              {text.logout}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default UserMenu;
