import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useStore } from '../../contexts/StoreContext';
import { useTheme } from '../../contexts/ThemeContext';
import { useLanguage } from '../../contexts/LanguageContext';
import { useCustomerAuth } from '../../contexts/CustomerAuthContext';
import { getImageUrl } from '../../lib/utils';
import {
  Package, Star, Heart, Tag, History, MapPin, Shield, Bell, LogOut, Trash2,
} from 'lucide-react';

interface ViewedProduct {
  id: string;
  name_ar?: string;
  name_fr?: string;
  name_en?: string;
  images?: any[];
  price: number;
  old_price?: number;
}

interface UserMenuProps {
  isOpen: boolean;
  onClose: () => void;
}

export const UserMenu: React.FC<UserMenuProps> = ({ isOpen, onClose }) => {
  const { store, apiBase } = useStore();
  const { colors } = useTheme();
  const { language, isRTL, formatPrice } = useLanguage();
  const { customer, logout } = useCustomerAuth();
  const [viewedProducts, setViewedProducts] = useState<ViewedProduct[]>([]);

  useEffect(() => {
    if (isOpen) {
      try {
        const stored = localStorage.getItem(`vp_viewed_${store.slug}`);
        setViewedProducts(stored ? JSON.parse(stored).slice(0, 6) : []);
      } catch { /* ignore */ }
    }
  }, [isOpen, store.slug]);

  const clearHistory = () => {
    localStorage.removeItem(`vp_viewed_${store.slug}`);
    setViewedProducts([]);
  };

  const getProductName = (p: ViewedProduct): string => {
    if (language === 'ar') return p.name_ar || p.name_fr || p.name_en || '';
    if (language === 'fr') return p.name_fr || p.name_ar || '';
    return p.name_en || p.name_ar || '';
  };

  const getProductImage = (p: ViewedProduct): string | null => {
    if (!p.images || p.images.length === 0) return null;
    const img = p.images[0];
    return typeof img === 'string' ? img : (img?.url || img?.path || null);
  };

  if (!isOpen) return null;

  const menuItems = [
    { to: '/profile?tab=orders', icon: Package, label: 'طلباتك', disabled: false },
    { to: '/profile?tab=reviews', icon: Star, label: 'مراجعاتك', disabled: false },
    { to: '/wishlist', icon: Heart, label: 'قائمة الأمنيات', disabled: false },
    { to: '#', icon: Tag, label: 'القسائم والعروض', disabled: true },
    { to: '/profile?tab=history', icon: History, label: 'سجل التصفح', disabled: false },
    { to: '/profile?tab=addresses', icon: MapPin, label: 'العناوين', disabled: false },
    { to: '#', icon: Shield, label: 'أمان الحساب', disabled: true },
    { to: '#', icon: Bell, label: 'الإشعارات', disabled: true },
  ];

  return (
    <div
      className="hidden md:flex absolute top-full mt-1 z-50 rounded-xl shadow-2xl border overflow-hidden"
      style={{
        backgroundColor: colors.card,
        borderColor: colors.border,
        width: '560px',
        [isRTL ? 'right' : 'left']: 0,
      }}
    >
      {/* Left column: recently viewed products */}
      <div
        className="flex-1 p-4 border-r"
        style={{ borderColor: colors.border }}
      >
        <div className="flex items-center justify-between mb-3">
          <h3 className="text-sm font-semibold" style={{ color: colors.foreground }}>
            شوهد مؤخراً
          </h3>
          {viewedProducts.length > 0 && (
            <button
              onClick={clearHistory}
              className="flex items-center gap-1 text-xs transition-opacity hover:opacity-70"
              style={{ color: colors.mutedForeground }}
            >
              <Trash2 className="h-3 w-3" />
              مسح السجل
            </button>
          )}
        </div>

        {viewedProducts.length > 0 ? (
          <div className="grid grid-cols-3 gap-2">
            {viewedProducts.map((p) => {
              const imgUrl = getProductImage(p);
              return (
                <Link
                  key={p.id}
                  to={`/products/${p.id}`}
                  onClick={onClose}
                  className="rounded-lg overflow-hidden group block"
                  style={{ backgroundColor: colors.muted }}
                >
                  <div className="aspect-square overflow-hidden">
                    {imgUrl ? (
                      <img
                        src={getImageUrl(imgUrl)}
                        alt={getProductName(p)}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                      />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center text-2xl opacity-20">
                        📷
                      </div>
                    )}
                  </div>
                  <div className="p-1.5">
                    <p className="text-[10px] leading-tight truncate" style={{ color: colors.foreground }}>
                      {getProductName(p)}
                    </p>
                    <p className="text-[11px] font-bold mt-0.5" style={{ color: colors.primary }}>
                      {formatPrice(p.price)}
                    </p>
                  </div>
                </Link>
              );
            })}
          </div>
        ) : (
          <div className="flex flex-col items-center justify-center h-36 text-center">
            <History className="h-9 w-9 mb-2 opacity-20" style={{ color: colors.mutedForeground }} />
            <p className="text-xs" style={{ color: colors.mutedForeground }}>
              لم تتصفح أي منتج بعد
            </p>
            <Link
              to="/products"
              onClick={onClose}
              className="mt-2 text-xs font-medium hover:underline"
              style={{ color: colors.primary }}
            >
              تصفح المنتجات
            </Link>
          </div>
        )}
      </div>

      {/* Right column: user info + menu */}
      <div className="w-[240px] flex flex-col shrink-0">
        {/* User info */}
        <div className="p-4 border-b" style={{ borderColor: colors.border }}>
          <div className="flex items-center gap-3">
            <div
              className="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-base shrink-0"
              style={{ backgroundColor: colors.primary }}
            >
              {customer?.name?.charAt(0)?.toUpperCase() || '?'}
            </div>
            <div className="min-w-0">
              <p className="font-bold text-sm truncate" style={{ color: colors.foreground }}>
                {customer?.name}
              </p>
              <p className="text-xs truncate" style={{ color: colors.mutedForeground }}>
                {customer?.phone || customer?.email}
              </p>
            </div>
          </div>
          <Link
            to="/profile"
            onClick={onClose}
            className="mt-3 block text-center text-xs py-1.5 rounded-lg border transition-colors hover:opacity-80"
            style={{ borderColor: colors.border, color: colors.primary }}
          >
            عرض الملف الشخصي
          </Link>
        </div>

        {/* Menu items - scrollable */}
        <div className="flex-1 overflow-y-auto py-1" style={{ maxHeight: '220px' }}>
          {menuItems.map((item) =>
            item.disabled ? (
              <div
                key={item.label}
                className="flex items-center gap-2.5 px-4 py-2.5 text-sm opacity-40 cursor-not-allowed"
                style={{ color: colors.foreground }}
              >
                <item.icon className="h-4 w-4 shrink-0" style={{ color: colors.mutedForeground }} />
                <span>{item.label}</span>
              </div>
            ) : (
              <Link
                key={item.label}
                to={item.to}
                onClick={onClose}
                className="flex items-center gap-2.5 px-4 py-2.5 text-sm transition-colors"
                style={{ color: colors.foreground }}
                onMouseEnter={(e) => { (e.currentTarget as HTMLElement).style.backgroundColor = colors.muted; }}
                onMouseLeave={(e) => { (e.currentTarget as HTMLElement).style.backgroundColor = 'transparent'; }}
              >
                <item.icon className="h-4 w-4 shrink-0" style={{ color: colors.primary }} />
                <span>{item.label}</span>
              </Link>
            )
          )}
        </div>

        {/* Logout */}
        <div className="p-3 border-t" style={{ borderColor: colors.border }}>
          <button
            onClick={() => { logout(apiBase); onClose(); }}
            className="w-full flex items-center justify-center gap-2 py-2 rounded-lg text-sm font-semibold text-red-600 transition-colors hover:bg-red-50"
          >
            <LogOut className="h-4 w-4" />
            تسجيل الخروج
          </button>
        </div>
      </div>
    </div>
  );
};

export default UserMenu;
