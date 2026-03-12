import React, { useState, useEffect, useRef } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useStore } from '../../contexts/StoreContext';
import { useTheme } from '../../contexts/ThemeContext';
import { useLanguage } from '../../contexts/LanguageContext';
import { useCart } from '../../contexts/CartContext';
import { api } from '../../lib/api';
import { getImageUrl, getCategoryName } from '../../lib/utils';
import { useCustomerAuth } from '../../contexts/CustomerAuthContext';
import {
  Search, ShoppingCart, Menu, X, Globe, ChevronDown,
  Heart, User, Flame, Phone, LogIn, LogOut,
  Package, Star, MapPin, Tag, History, Shield, Bell, ChevronRight, ChevronLeft
} from 'lucide-react';

interface HeaderProps {
  style?: 'default' | 'centered' | 'minimal' | 'mega';
}

export const Header: React.FC<HeaderProps> = ({ style = 'default' }) => {
  const { store, storeName, storeLogo, apiBase, contactInfo } = useStore();
  const { colors } = useTheme();
  const { t, language, setLanguage, isRTL } = useLanguage();
  const { cartCount } = useCart();
  const { customer, isAuthenticated, logout } = useCustomerAuth();
  const navigate = useNavigate();

  const [searchQuery, setSearchQuery] = useState('');
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [categories, setCategories] = useState<any[]>([]);
  const [showCategories, setShowCategories] = useState(false);
  const [showLangMenu, setShowLangMenu] = useState(false);
  const [showUserMenu, setShowUserMenu] = useState(false);
  const [recentProducts, setRecentProducts] = useState<any[]>([]);
  const [isScrolled, setIsScrolled] = useState(false);
  const catRef = useRef<HTMLDivElement>(null);
  const langRef = useRef<HTMLDivElement>(null);
  const userRef = useRef<HTMLDivElement>(null);
  const ChevronDir = isRTL ? ChevronLeft : ChevronRight;

  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const res = await api.get(`${apiBase}/categories`);
        setCategories(res.data?.data || res.data || []);
      } catch { /* ignore */ }
    };
    fetchCategories();
  }, [apiBase]);

  useEffect(() => {
    const handleScroll = () => setIsScrolled(window.scrollY > 10);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (catRef.current && !catRef.current.contains(e.target as Node)) setShowCategories(false);
      if (langRef.current && !langRef.current.contains(e.target as Node)) setShowLangMenu(false);
      if (userRef.current && !userRef.current.contains(e.target as Node)) setShowUserMenu(false);
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      navigate(`/search?q=${encodeURIComponent(searchQuery)}`);
      setSearchQuery('');
      setMobileMenuOpen(false);
    }
  };

  const languages = [
    { code: 'ar', label: 'العربية', flag: '🇩🇿' },
    { code: 'fr', label: 'Français', flag: '🇫🇷' },
    { code: 'en', label: 'English', flag: '🇬🇧' },
  ];

  const logoElement = (
    <Link to="/" className="flex items-center gap-2 shrink-0">
      {storeLogo ? (
        <img
          src={getImageUrl(storeLogo)}
          alt={storeName}
          className="h-10 w-10 rounded-full object-cover shadow-sm"
        />
      ) : (
        <div
          className="h-10 w-10 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-sm"
          style={{ backgroundColor: colors.primary }}
        >
          {storeName.charAt(0)}
        </div>
      )}
      <span className="hidden sm:block font-bold text-lg" style={{ color: colors.headerText }}>
        {storeName}
      </span>
    </Link>
  );

  const searchBar = (
    <form onSubmit={handleSearch} className="hidden md:flex flex-1 max-w-xl mx-4">
      <div className="relative w-full">
        <Search
          className={`absolute ${isRTL ? 'right-4' : 'left-4'} top-1/2 -translate-y-1/2 h-5 w-5 opacity-50 pointer-events-none`}
          style={{ color: colors.mutedForeground }}
        />
        <input
          type="search"
          placeholder={t('nav.searchPlaceholder')}
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          className={`${isRTL ? 'pr-12 pl-24' : 'pl-12 pr-24'} h-11 w-full rounded-full text-sm border-2 transition-all focus:outline-none`}
          style={{
            backgroundColor: colors.muted,
            borderColor: 'transparent',
            color: colors.foreground,
          }}
          onFocus={(e) => { e.target.style.borderColor = colors.primary; e.target.style.backgroundColor = colors.background; }}
          onBlur={(e) => { e.target.style.borderColor = 'transparent'; e.target.style.backgroundColor = colors.muted; }}
        />
        <button
          type="submit"
          className={`absolute ${isRTL ? 'left-1' : 'right-1'} top-1/2 -translate-y-1/2 h-9 px-5 rounded-full text-sm font-medium text-white transition-opacity hover:opacity-90`}
          style={{ backgroundColor: colors.primary }}
        >
          {t('nav.search')}
        </button>
      </div>
    </form>
  );

  const navLinks = (
    <nav className="hidden lg:flex items-center gap-1">
      <Link
        to="/"
        className="px-3 py-2 text-sm font-medium rounded-lg transition-colors hover:opacity-80"
        style={{ color: colors.headerText }}
      >
        {t('nav.home')}
      </Link>
      <Link
        to="/products"
        className="px-3 py-2 text-sm font-medium rounded-lg transition-colors hover:opacity-80"
        style={{ color: colors.headerText }}
      >
        {t('nav.products')}
      </Link>

      {/* Categories Dropdown */}
      {categories.length > 0 && (
        <div ref={catRef} className="relative">
          <button
            onClick={() => setShowCategories(!showCategories)}
            className="flex items-center gap-1 px-3 py-2 text-sm font-medium rounded-lg transition-colors hover:opacity-80"
            style={{ color: colors.headerText }}
          >
            {t('nav.categories')}
            <ChevronDown className={`h-4 w-4 transition-transform ${showCategories ? 'rotate-180' : ''}`} />
          </button>

          {showCategories && (
            <div
              className="absolute top-full mt-2 z-50 min-w-[320px] p-4 rounded-xl shadow-xl border"
              style={{
                backgroundColor: colors.card,
                borderColor: colors.border,
                [isRTL ? 'right' : 'left']: 0,
              }}
            >
              <div className="grid grid-cols-2 gap-2">
                {categories.map((cat: any) => (
                  <Link
                    key={cat.id}
                    to={`/category/${cat.id}`}
                    onClick={() => setShowCategories(false)}
                    className="flex items-center gap-3 p-2 rounded-lg transition-colors"
                    style={{ color: colors.cardForeground }}
                    onMouseEnter={(e) => { (e.target as HTMLElement).style.backgroundColor = colors.muted; }}
                    onMouseLeave={(e) => { (e.target as HTMLElement).style.backgroundColor = 'transparent'; }}
                  >
                    {cat.image && (
                      <img
                        src={getImageUrl(cat.image)}
                        alt={getCategoryName(cat, language)}
                        className="w-10 h-10 rounded-lg object-cover"
                      />
                    )}
                    <span className="text-sm font-medium">{getCategoryName(cat, language)}</span>
                  </Link>
                ))}
              </div>
              <div className="mt-3 pt-3" style={{ borderTop: `1px solid ${colors.border}` }}>
                <Link
                  to="/products"
                  onClick={() => setShowCategories(false)}
                  className="text-sm font-medium block text-center"
                  style={{ color: colors.primary }}
                >
                  {t('nav.allCategories')}
                </Link>
              </div>
            </div>
          )}
        </div>
      )}

      <Link
        to="/products?deals=true"
        className="flex items-center gap-1 px-3 py-2 text-sm font-bold rounded-lg transition-colors"
        style={{ color: '#ef4444' }}
      >
        <Flame className="h-4 w-4" />
        {t('nav.deals')}
      </Link>
    </nav>
  );

  const actionButtons = (
    <div className="flex items-center gap-1 shrink-0">
      {/* Language Selector */}
      <div ref={langRef} className="relative">
        <button
          onClick={() => setShowLangMenu(!showLangMenu)}
          className="h-9 w-9 flex items-center justify-center rounded-lg transition-colors hover:opacity-80"
          style={{ color: colors.headerText }}
        >
          <Globe className="h-5 w-5" />
        </button>
        {showLangMenu && (
          <div
            className="absolute top-full mt-2 z-50 min-w-[140px] py-1 rounded-lg shadow-xl border"
            style={{
              backgroundColor: colors.card,
              borderColor: colors.border,
              [isRTL ? 'right' : 'left']: 0,
            }}
          >
            {languages.map((lang) => (
              <button
                key={lang.code}
                onClick={() => { setLanguage(lang.code); setShowLangMenu(false); }}
                className="w-full flex items-center gap-2 px-4 py-2 text-sm transition-colors"
                style={{
                  color: colors.cardForeground,
                  backgroundColor: language === lang.code ? colors.muted : 'transparent',
                }}
                onMouseEnter={(e) => { (e.target as HTMLElement).style.backgroundColor = colors.muted; }}
                onMouseLeave={(e) => { (e.target as HTMLElement).style.backgroundColor = language === lang.code ? colors.muted : 'transparent'; }}
              >
                <span>{lang.flag}</span>
                <span>{lang.label}</span>
              </button>
            ))}
          </div>
        )}
      </div>

      {/* Wishlist */}
      <Link
        to="/wishlist"
        className="h-9 w-9 hidden sm:flex items-center justify-center rounded-lg transition-colors hover:opacity-80"
        style={{ color: colors.headerText }}
      >
        <Heart className="h-5 w-5" />
      </Link>

      {/* Customer Auth */}
      {isAuthenticated ? (
        <div ref={userRef} className="relative">
          <button
            onClick={() => setShowUserMenu(!showUserMenu)}
            className="h-9 w-9 flex items-center justify-center rounded-lg transition-colors"
            style={{
              color: showUserMenu ? '#fff' : colors.headerText,
              backgroundColor: showUserMenu ? colors.primary : 'transparent',
            }}
          >
            <div className="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold" style={{ backgroundColor: showUserMenu ? 'rgba(255,255,255,0.25)' : colors.primary }}>
              {customer?.name?.charAt(0) || '?'}
            </div>
          </button>
          {/* Desktop dropdown (md and above) */}
          {showUserMenu && (
            <div
              className="hidden md:block absolute top-full mt-1 z-50 min-w-[200px] py-1 rounded-xl shadow-xl border"
              style={{ backgroundColor: colors.card, borderColor: colors.border, [isRTL ? 'right' : 'left']: 0 }}
            >
              <div className="px-4 py-2.5 border-b" style={{ borderColor: colors.border }}>
                <p className="font-semibold text-sm truncate" style={{ color: colors.foreground }}>{customer?.name}</p>
                <p className="text-xs truncate" style={{ color: colors.mutedForeground }}>{customer?.phone || customer?.email}</p>
              </div>
              {[
                { to: '/profile', icon: Package, label: 'طلباتك' },
                { to: '/wishlist', icon: Heart, label: 'قائمة الأمنيات' },
                { to: '/profile', icon: User, label: 'الملف الشخصي' },
              ].map((item) => (
                <Link key={item.label} to={item.to} onClick={() => setShowUserMenu(false)}
                  className="flex items-center gap-2 px-4 py-2 text-sm transition-colors hover:opacity-80"
                  style={{ color: colors.cardForeground }}>
                  <item.icon className="h-4 w-4" /> {item.label}
                </Link>
              ))}
              <div className="border-t mt-1" style={{ borderColor: colors.border }}>
                <button onClick={() => { logout(apiBase); setShowUserMenu(false); }}
                  className="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                  <LogOut className="h-4 w-4" /> تسجيل الخروج
                </button>
              </div>
            </div>
          )}
        </div>
      ) : (
        <Link to="/login" className="h-9 w-9 flex items-center justify-center rounded-lg transition-colors hover:opacity-80" style={{ color: colors.headerText }}>
          <User className="h-5 w-5" />
        </Link>
      )}

      {/* Cart */}
      <Link
        to="/cart"
        className="relative h-9 w-9 flex items-center justify-center rounded-lg transition-colors hover:opacity-80"
        style={{ color: colors.headerText }}
      >
        <ShoppingCart className="h-5 w-5" />
        {cartCount > 0 && (
          <span
            className="absolute -top-1 -right-1 h-5 min-w-[20px] flex items-center justify-center rounded-full text-[10px] font-bold text-white px-1"
            style={{ backgroundColor: colors.accent }}
          >
            {cartCount}
          </span>
        )}
      </Link>

      {/* Mobile Menu Toggle */}
      <button
        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
        className="lg:hidden h-9 w-9 flex items-center justify-center rounded-lg"
        style={{ color: colors.headerText }}
      >
        {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
      </button>
    </div>
  );

  // Render based on header style
  const renderHeader = () => {
    switch (style) {
      case 'centered':
        return (
          <>
            {/* Top bar */}
            {contactInfo.phone && (
              <div className="text-xs py-1.5 text-center" style={{ backgroundColor: colors.primary, color: '#fff' }}>
                <div className="container mx-auto px-4 flex items-center justify-center gap-2">
                  <Phone className="h-3 w-3" />
                  <span dir="ltr">{contactInfo.phone}</span>
                </div>
              </div>
            )}
            {/* Logo centered */}
            <div className="py-4 text-center" style={{ backgroundColor: colors.headerBg }}>
              <div className="container mx-auto px-4 flex flex-col items-center gap-3">
                <Link to="/" className="flex flex-col items-center gap-2">
                  {storeLogo ? (
                    <img src={getImageUrl(storeLogo)} alt={storeName} className="h-14 w-14 rounded-full object-cover shadow-sm" />
                  ) : (
                    <div className="h-14 w-14 rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-sm" style={{ backgroundColor: colors.primary }}>
                      {storeName.charAt(0)}
                    </div>
                  )}
                  <span className="font-bold text-xl" style={{ color: colors.headerText }}>{storeName}</span>
                </Link>
                {searchBar}
              </div>
            </div>
            {/* Nav bar */}
            <div
              className="border-b sticky top-0 z-50"
              style={{ backgroundColor: colors.headerBg, borderColor: colors.border }}
            >
              <div className="container mx-auto px-4 flex items-center justify-between h-12">
                {navLinks}
                {actionButtons}
              </div>
            </div>
          </>
        );

      case 'minimal':
        return (
          <header
            className={`sticky top-0 z-50 border-b transition-shadow ${isScrolled ? 'shadow-md' : ''}`}
            style={{ backgroundColor: colors.headerBg, borderColor: colors.border }}
          >
            <div className="container mx-auto px-4 flex items-center justify-between h-14">
              {logoElement}
              {actionButtons}
            </div>
          </header>
        );

      default: // 'default' and 'mega'
        return (
          <header
            className={`sticky top-0 z-50 border-b backdrop-blur-md transition-shadow ${isScrolled ? 'shadow-md' : ''}`}
            style={{
              backgroundColor: isScrolled ? colors.headerBg : `${colors.headerBg}f0`,
              borderColor: colors.border,
            }}
          >
            <div className="container mx-auto px-4">
              <div className="flex items-center gap-2 h-16">
                {logoElement}
                {navLinks}
                {searchBar}
                {actionButtons}
              </div>
            </div>
          </header>
        );
    }
  };

  return (
    <>
      {renderHeader()}

      {/* Mobile User Account Panel (md and below) */}
      {showUserMenu && isAuthenticated && (
        <div
          className="fixed inset-0 z-[60] md:hidden"
          onClick={() => setShowUserMenu(false)}
        >
          <div className="absolute inset-0 bg-black/50" />
          <div
            className={`absolute top-0 ${isRTL ? 'right-0' : 'left-0'} h-full w-72 max-w-[85vw] overflow-y-auto shadow-2xl flex flex-col`}
            style={{ backgroundColor: colors.background }}
            onClick={(e) => e.stopPropagation()}
          >
            {/* Panel Header - store name */}
            <div className="flex items-center justify-between px-4 py-3 border-b" style={{ borderColor: colors.border, backgroundColor: colors.card }}>
              <button onClick={() => setShowUserMenu(false)}>
                <ChevronDir className="h-5 w-5" style={{ color: colors.foreground }} />
              </button>
              <span className="font-bold text-base truncate mx-2" style={{ color: colors.primary }}>{storeName}</span>
            </div>

            {/* User Info */}
            <div className="flex items-center gap-3 px-4 py-4 border-b" style={{ borderColor: colors.border }}>
              <div className="w-11 h-11 rounded-full flex items-center justify-center text-white font-bold text-lg shrink-0" style={{ backgroundColor: colors.primary }}>
                {customer?.name?.charAt(0) || '?'}
              </div>
              <div className="min-w-0">
                <p className="font-bold text-sm truncate" style={{ color: colors.foreground }}>{customer?.name}</p>
                <p className="text-xs truncate" style={{ color: colors.mutedForeground }}>{customer?.phone || customer?.email}</p>
              </div>
            </div>

            {/* Section title */}
            <div className="px-4 pt-3 pb-1">
              <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: colors.mutedForeground }}>الملف الشخصي</p>
            </div>

            {/* Navigation Items */}
            <nav className="flex-1 px-2 pb-4">
              {[
                { to: '/profile', icon: Package, label: 'طلباتك' },
                { to: '/profile', icon: Star, label: 'مراجعاتك' },
                { to: '/wishlist', icon: Heart, label: 'قائمة الأمنيات' },
                { to: '/profile', icon: Tag, label: 'القسائم والعروض' },
                { to: '/', icon: History, label: 'سجل التصفح' },
                { to: '/profile', icon: MapPin, label: 'العناوين' },
                { to: '/profile', icon: Shield, label: 'أمان الحساب' },
                { to: '/profile', icon: Bell, label: 'الإشعارات' },
              ].map((item) => (
                <Link
                  key={item.label}
                  to={item.to}
                  onClick={() => setShowUserMenu(false)}
                  className="flex items-center gap-3 px-3 py-3 rounded-lg text-sm transition-colors"
                  style={{ color: colors.foreground }}
                  onTouchStart={(e) => { (e.currentTarget as HTMLElement).style.backgroundColor = colors.muted; }}
                  onTouchEnd={(e) => { (e.currentTarget as HTMLElement).style.backgroundColor = 'transparent'; }}
                >
                  <item.icon className="h-5 w-5 shrink-0" style={{ color: colors.primary }} />
                  <span className="flex-1">{item.label}</span>
                  <ChevronDir className="h-4 w-4 shrink-0 opacity-40" style={{ color: colors.mutedForeground }} />
                </Link>
              ))}
            </nav>

            {/* Logout */}
            <div className="p-4 border-t" style={{ borderColor: colors.border }}>
              <button
                onClick={() => { logout(apiBase); setShowUserMenu(false); }}
                className="w-full flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm font-semibold text-red-600 transition-colors hover:bg-red-50"
              >
                <LogOut className="h-4 w-4" /> الخروج
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Mobile Menu */}
      {mobileMenuOpen && (
        <div
          className="fixed inset-0 z-[60] lg:hidden"
          onClick={() => setMobileMenuOpen(false)}
        >
          <div className="absolute inset-0 bg-black/50" />
          <div
            className={`absolute top-0 ${isRTL ? 'right-0' : 'left-0'} h-full w-80 max-w-[85vw] overflow-y-auto shadow-xl`}
            style={{ backgroundColor: colors.background }}
            onClick={(e) => e.stopPropagation()}
          >
            {/* Mobile Header */}
            <div className="flex items-center justify-between p-4 border-b" style={{ borderColor: colors.border }}>
              {logoElement}
              <button onClick={() => setMobileMenuOpen(false)}>
                <X className="h-5 w-5" style={{ color: colors.foreground }} />
              </button>
            </div>

            {/* Mobile Search */}
            <div className="p-4">
              <form onSubmit={handleSearch} className="relative">
                <Search className={`absolute ${isRTL ? 'right-3' : 'left-3'} top-1/2 -translate-y-1/2 h-4 w-4 opacity-50`} />
                <input
                  type="search"
                  placeholder={t('nav.searchPlaceholder')}
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className={`${isRTL ? 'pr-10' : 'pl-10'} h-10 w-full rounded-lg text-sm border focus:outline-none`}
                  style={{
                    backgroundColor: colors.muted,
                    borderColor: colors.border,
                    color: colors.foreground,
                  }}
                />
              </form>
            </div>

            {/* Mobile Nav Links */}
            <nav className="px-4 space-y-1">
              {[
                { to: '/', label: t('nav.home') },
                { to: '/products', label: t('nav.products') },
                { to: '/products?deals=true', label: t('nav.deals') },
              ].map((link) => (
                <Link
                  key={link.to}
                  to={link.to}
                  onClick={() => setMobileMenuOpen(false)}
                  className="block px-4 py-3 rounded-lg text-sm font-medium transition-colors"
                  style={{ color: colors.foreground }}
                  onMouseEnter={(e) => { (e.target as HTMLElement).style.backgroundColor = colors.muted; }}
                  onMouseLeave={(e) => { (e.target as HTMLElement).style.backgroundColor = 'transparent'; }}
                >
                  {link.label}
                </Link>
              ))}

              {/* Mobile Categories */}
              {categories.length > 0 && (
                <div className="pt-2">
                  <p className="px-4 py-2 text-xs font-semibold uppercase tracking-wider" style={{ color: colors.mutedForeground }}>
                    {t('nav.categories')}
                  </p>
                  {categories.map((cat: any) => (
                    <Link
                      key={cat.id}
                      to={`/category/${cat.id}`}
                      onClick={() => setMobileMenuOpen(false)}
                      className="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-colors"
                      style={{ color: colors.foreground }}
                    >
                      {cat.image && (
                        <img src={getImageUrl(cat.image)} alt="" className="w-8 h-8 rounded object-cover" />
                      )}
                      {getCategoryName(cat, language)}
                    </Link>
                  ))}
                </div>
              )}
            </nav>

            {/* Mobile Customer Auth */}
            <div className="px-4 pt-4 pb-2 border-t" style={{ borderColor: colors.border }}>
              {isAuthenticated ? (
                <>
                  <Link to="/profile" onClick={() => setMobileMenuOpen(false)} className="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium" style={{ color: colors.foreground }}>
                    <User className="h-4 w-4" /> {customer?.name || 'حسابي'}
                  </Link>
                  <button onClick={() => { logout(apiBase); setMobileMenuOpen(false); }} className="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-red-600">
                    <LogOut className="h-4 w-4" /> تسجيل الخروج
                  </button>
                </>
              ) : (
                <Link to="/login" onClick={() => setMobileMenuOpen(false)} className="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium" style={{ color: colors.primary }}>
                  <LogIn className="h-4 w-4" /> تسجيل الدخول / إنشاء حساب
                </Link>
              )}
            </div>

            {/* Mobile Language */}
            <div className="p-4 border-t" style={{ borderColor: colors.border }}>
              <div className="flex gap-2">
                {languages.map((lang) => (
                  <button
                    key={lang.code}
                    onClick={() => { setLanguage(lang.code); setMobileMenuOpen(false); }}
                    className="flex-1 py-2 rounded-lg text-sm font-medium border transition-colors"
                    style={{
                      borderColor: language === lang.code ? colors.primary : colors.border,
                      backgroundColor: language === lang.code ? colors.primary : 'transparent',
                      color: language === lang.code ? '#fff' : colors.foreground,
                    }}
                  >
                    {lang.flag} {lang.label}
                  </button>
                ))}
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default Header;
