import React, { useState, useEffect, useRef } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useAuth } from '@/contexts/AuthContext';
import { useCart } from '@/contexts/CartContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { UserMenu } from './UserMenu';
import { useStoreSettings } from '@/contexts/StoreSettingsContext';
import {
  ShoppingCart,
  User,
  Menu,
  Search,
  Globe,
  LogOut,
  LayoutDashboard,
  Package,
  Leaf,
  Droplets,
  Wrench,
  Shield,
  Droplet,
  Home,
  ChevronDown,
  Flame
} from 'lucide-react';

const DEFAULT_LOGO = "https://customer-assets.emergentagent.com/job_cb33075f-a467-40a3-8ccf-6a7d58e2dd7b/artifacts/9ov58a7g_548325177_122096850867034427_2184721735778021830_n.jpg";

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const iconMap = {
  'Leaf': Leaf,
  'Droplets': Droplets,
  'Wrench': Wrench,
  'Shield': Shield,
  'Droplet': Droplet,
  'Home': Home
};

export const Navbar = () => {
  const { t, language, setLanguage, isRTL } = useLanguage();
  const { user, logout, isAdmin } = useAuth();
  const { cartCount } = useCart();
  const { storeInfo } = useStoreSettings();

  const logoUrl = storeInfo.store_logo || DEFAULT_LOGO;
  const storeName = storeInfo.store_name || 'AgroYousfi';
  const navigate = useNavigate();
  const [searchQuery, setSearchQuery] = useState('');
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [categories, setCategories] = useState([]);
  const [showCategoriesMenu, setShowCategoriesMenu] = useState(false);
  const [showUserMenu, setShowUserMenu] = useState(false);
  const userMenuRef = useRef(null);

  useEffect(() => {
    fetchCategories();
  }, []);

  // Close user menu when clicking outside
  useEffect(() => {
    const handleClickOutside = (event) => {
      if (userMenuRef.current && !userMenuRef.current.contains(event.target)) {
        setShowUserMenu(false);
      }
    };
    
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const fetchCategories = async () => {
    try {
      const response = await axios.get(`${API}/categories`);
      setCategories(response.data);
    } catch (error) {
      console.error('Error fetching categories:', error);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      navigate(`/products?search=${encodeURIComponent(searchQuery)}`);
      setSearchQuery('');
    }
  };

  const languages = [
    { code: 'ar', label: 'العربية', flag: '🇩🇿' },
    { code: 'fr', label: 'Français', flag: '🇫🇷' },
    { code: 'en', label: 'English', flag: '🇬🇧' }
  ];

  return (
    <header className="sticky top-0 z-50 w-full glass border-b border-border/40">
      <div className="container mx-auto px-4">
        <div className="flex h-16 items-center gap-2">
          {/* Logo */}
          <Link to="/" className="flex items-center gap-2 shrink-0" data-testid="logo-link">
            <img 
              src={logoUrl}
              alt={storeName} 
              className="h-10 w-10 rounded-full object-cover shadow-md"
            />
            <span className="hidden xl:block font-bold text-lg text-primary">
              {storeName}
            </span>
          </Link>

          {/* Desktop Navigation - Compact */}
          <nav className="hidden lg:flex items-center shrink-0">
            <Link
              to="/"
              className="px-3 py-2 text-sm text-muted-foreground hover:text-primary transition-colors font-medium rounded-lg hover:bg-muted whitespace-nowrap"
              data-testid="nav-home"
            >
              {t('nav.home')}
            </Link>
            <Link
              to="/products"
              className="px-3 py-2 text-sm text-muted-foreground hover:text-primary transition-colors font-medium rounded-lg hover:bg-muted whitespace-nowrap"
              data-testid="nav-products"
            >
              {t('nav.products')}
            </Link>
            {/* Deals Link - Highlighted */}
            <Link
              to="/deals"
              className="flex items-center gap-1 px-3 py-2 text-sm text-red-600 hover:text-red-700 transition-colors font-bold rounded-lg hover:bg-red-50 whitespace-nowrap"
              data-testid="nav-deals"
            >
              <Flame className="h-4 w-4" />
              {language === 'ar' ? 'العروض' : language === 'fr' ? 'Offres' : 'Deals'}
            </Link>
            
            {/* Categories Mega Menu */}
            <div 
              className="relative"
              onMouseEnter={() => setShowCategoriesMenu(true)}
              onMouseLeave={() => setShowCategoriesMenu(false)}
            >
              <button
                className="flex items-center gap-1 px-3 py-2 text-sm text-muted-foreground hover:text-primary transition-colors font-medium rounded-lg hover:bg-muted whitespace-nowrap"
                data-testid="nav-categories"
                onClick={() => setShowCategoriesMenu(!showCategoriesMenu)}
              >
                {t('nav.categories')}
                <ChevronDown className={`h-4 w-4 transition-transform ${showCategoriesMenu ? 'rotate-180' : ''}`} />
              </button>
              
              {/* Mega Menu Dropdown - with padding-top for hover gap */}
              {showCategoriesMenu && (
                <div 
                  className="absolute top-full pt-2 z-50"
                  style={{ [isRTL ? 'right' : 'left']: 0 }}
                >
                  <div className="bg-card rounded-2xl shadow-xl border p-4 min-w-[500px]">
                    <div className="grid grid-cols-3 gap-3">
                      {categories.map((category) => {
                        const IconComponent = iconMap[category.icon] || Leaf;
                        const name = category[`name_${language}`] || category.name_ar;
                        
                        return (
                          <Link
                            key={category.category_id}
                            to={`/products?category=${category.category_id}`}
                            className="flex items-center gap-3 p-3 rounded-xl hover:bg-muted transition-colors group"
                            onClick={() => setShowCategoriesMenu(false)}
                            data-testid={`mega-menu-${category.category_id}`}
                          >
                            <div className="relative w-12 h-12 rounded-xl overflow-hidden bg-muted shrink-0">
                              {category.image ? (
                                <img 
                                  src={category.image} 
                                  alt={name}
                                  className="w-full h-full object-cover group-hover:scale-110 transition-transform"
                                />
                              ) : (
                                <div className="w-full h-full flex items-center justify-center bg-primary/10">
                                  <IconComponent className="h-5 w-5 text-primary" />
                                </div>
                              )}
                            </div>
                            <div className="min-w-0">
                              <p className="font-medium text-sm text-foreground group-hover:text-primary truncate">
                                {name}
                              </p>
                            </div>
                          </Link>
                        );
                      })}
                    </div>
                    
                    {/* View All Link */}
                    <div className="mt-3 pt-3 border-t">
                      <Link
                        to="/categories"
                        className="flex items-center justify-center gap-2 text-sm font-medium text-primary hover:text-primary/80 transition-colors"
                        onClick={() => setShowCategoriesMenu(false)}
                      >
                        {t('categories.viewAll')}
                        <ChevronDown className={`h-4 w-4 ${isRTL ? 'rotate-90' : '-rotate-90'}`} />
                      </Link>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </nav>

          {/* Search Bar - Takes all remaining space */}
          <form onSubmit={handleSearch} className="hidden md:flex flex-1 min-w-0">
            <div className="relative w-full">
              <Search className={`absolute ${isRTL ? 'right-4' : 'left-4'} top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground pointer-events-none`} />
              <Input
                type="search"
                placeholder={language === 'ar' ? 'ابحث عن منتجات...' : language === 'fr' ? 'Rechercher des produits...' : 'Search for products...'}
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className={`${isRTL ? 'pr-12 pl-28' : 'pl-12 pr-28'} h-11 w-full bg-muted/50 border-2 border-transparent focus:border-primary focus:bg-background rounded-full text-base`}
                data-testid="search-input"
              />
              <Button 
                type="submit" 
                className={`absolute ${isRTL ? 'left-1' : 'right-1'} top-1/2 -translate-y-1/2 rounded-full h-9 px-6`}
              >
                {language === 'ar' ? 'بحث' : language === 'fr' ? 'Chercher' : 'Search'}
              </Button>
            </div>
          </form>

          {/* Actions */}
          <div className="flex items-center gap-1 shrink-0">
            {/* Language Selector */}
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" className="h-9 w-9" data-testid="language-selector">
                  <Globe className="h-5 w-5" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align={isRTL ? 'start' : 'end'}>
                {languages.map((lang) => (
                  <DropdownMenuItem
                    key={lang.code}
                    onClick={() => setLanguage(lang.code)}
                    className={language === lang.code ? 'bg-muted' : ''}
                    data-testid={`lang-${lang.code}`}
                  >
                    <span className="mr-2">{lang.flag}</span>
                    {lang.label}
                  </DropdownMenuItem>
                ))}
              </DropdownMenuContent>
            </DropdownMenu>

            {/* Cart */}
            <Link to="/cart" data-testid="cart-link">
              <Button variant="ghost" size="icon" className="relative h-9 w-9">
                <ShoppingCart className="h-5 w-5" />
                {cartCount > 0 && (
                  <span className="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-secondary text-white text-xs flex items-center justify-center font-bold">
                    {cartCount}
                  </span>
                )}
              </Button>
            </Link>

            {/* User Menu */}
            {user ? (
              <div className="relative" ref={userMenuRef}>
                <Button 
                  variant="ghost" 
                  size="icon" 
                  className="h-9 w-9" 
                  data-testid="user-menu"
                  onClick={() => setShowUserMenu(!showUserMenu)}
                  onMouseEnter={() => setShowUserMenu(true)}
                >
                  {user.picture ? (
                    <img src={user.picture} alt={user.name} className="h-8 w-8 rounded-full object-cover" />
                  ) : (
                    <User className="h-5 w-5" />
                  )}
                </Button>
                
                {/* Temu-style User Menu */}
                {showUserMenu && (
                  <UserMenu onClose={() => setShowUserMenu(false)} />
                )}
              </div>
            ) : (
              <Link to="/login" data-testid="login-link">
                <Button variant="default" size="sm" className="rounded-full h-9 px-4">
                  {t('nav.login')}
                </Button>
              </Link>
            )}

            {/* Mobile Menu */}
            <Sheet open={mobileMenuOpen} onOpenChange={setMobileMenuOpen}>
              <SheetTrigger asChild>
                <Button variant="ghost" size="icon" className="lg:hidden h-9 w-9" data-testid="mobile-menu-btn">
                  <Menu className="h-5 w-5" />
                </Button>
              </SheetTrigger>
              <SheetContent side={isRTL ? 'right' : 'left'} className="w-80">
                <div className="flex flex-col h-full">
                  <div className="flex items-center justify-between mb-8">
                    <Link to="/" className="flex items-center gap-2" onClick={() => setMobileMenuOpen(false)}>
                      <img src={logoUrl} alt="AgroYousfi" className="h-10 w-10 rounded-full" />
                      <span className="font-bold text-lg">{storeName}</span>
                    </Link>
                  </div>

                  {/* Mobile Search */}
                  <form onSubmit={handleSearch} className="mb-6">
                    <div className="relative">
                      <Search className={`absolute ${isRTL ? 'right-3' : 'left-3'} top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground`} />
                      <Input
                        type="search"
                        placeholder={t('nav.search')}
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className={`${isRTL ? 'pr-10' : 'pl-10'} rounded-full`}
                      />
                    </div>
                  </form>

                  {/* Mobile Nav Links */}
                  <nav className="flex flex-col gap-2">
                    <Link
                      to="/"
                      onClick={() => setMobileMenuOpen(false)}
                      className="px-4 py-3 rounded-xl hover:bg-muted transition-colors font-medium"
                    >
                      {t('nav.home')}
                    </Link>
                    <Link
                      to="/products"
                      onClick={() => setMobileMenuOpen(false)}
                      className="px-4 py-3 rounded-xl hover:bg-muted transition-colors font-medium"
                    >
                      {t('nav.products')}
                    </Link>
                    
                    {/* Mobile Categories */}
                    <div className="px-4 py-2">
                      <p className="text-sm text-muted-foreground mb-2">{t('nav.categories')}</p>
                      <div className="grid grid-cols-2 gap-2">
                        {categories.map((category) => {
                          const IconComponent = iconMap[category.icon] || Leaf;
                          const name = category[`name_${language}`] || category.name_ar;
                          
                          return (
                            <Link
                              key={category.category_id}
                              to={`/products?category=${category.category_id}`}
                              onClick={() => setMobileMenuOpen(false)}
                              className="flex items-center gap-2 p-2 rounded-lg bg-muted/50 hover:bg-muted transition-colors"
                            >
                              <IconComponent className="h-4 w-4 text-primary" />
                              <span className="text-xs truncate">{name}</span>
                            </Link>
                          );
                        })}
                      </div>
                    </div>
                  </nav>

                  {/* Language Selection */}
                  <div className="mt-6 pt-6 border-t">
                    <p className="text-sm text-muted-foreground mb-3">اختر اللغة / Langue</p>
                    <div className="flex gap-2">
                      {languages.map((lang) => (
                        <Button
                          key={lang.code}
                          variant={language === lang.code ? 'default' : 'outline'}
                          size="sm"
                          onClick={() => setLanguage(lang.code)}
                          className="flex-1"
                        >
                          {lang.flag} {lang.code.toUpperCase()}
                        </Button>
                      ))}
                    </div>
                  </div>
                </div>
              </SheetContent>
            </Sheet>
          </div>
        </div>
      </div>
    </header>
  );
};

export default Navbar;
