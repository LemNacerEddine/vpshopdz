import React, { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { useLanguage } from '@/contexts/LanguageContext';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import {
  LayoutDashboard,
  Package,
  ShoppingCart,
  Users,
  BarChart3,
  Settings,
  Menu,
  X,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  Sun,
  Moon,
  Globe,
  Bell,
  LogOut,
  User,
  Search,
  Folder,
  DollarSign,
  FileText,
  Shield,
  Store
} from 'lucide-react';

const AdminLayout = ({ children }) => {
  const { language, setLanguage, isRTL } = useLanguage();
  const { user, logout, isAdmin } = useAuth();
  const location = useLocation();
  const navigate = useNavigate();
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [darkMode, setDarkMode] = useState(() => {
    if (typeof window !== 'undefined') {
      return localStorage.getItem('admin-dark-mode') === 'true';
    }
    return false;
  });
  
  // Initialize expanded menus from localStorage or default to products
  const [expandedMenus, setExpandedMenus] = useState(() => {
    if (typeof window !== 'undefined') {
      const saved = localStorage.getItem('admin-expanded-menus');
      if (saved) {
        try {
          return JSON.parse(saved);
        } catch {
          return ['products'];
        }
      }
    }
    return ['products'];
  });

  // Save expanded menus to localStorage
  useEffect(() => {
    localStorage.setItem('admin-expanded-menus', JSON.stringify(expandedMenus));
  }, [expandedMenus]);

  // Apply dark mode
  useEffect(() => {
    if (darkMode) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
    localStorage.setItem('admin-dark-mode', darkMode);
  }, [darkMode]);

  const l = {
    ar: {
      dashboard: 'لوحة التحكم',
      products: 'المنتجات',
      allProducts: 'جميع المنتجات',
      addProduct: 'إضافة منتج',
      categories: 'التصنيفات',
      orders: 'الطلبات',
      allOrders: 'جميع الطلبات',
      pendingOrders: 'قيد الانتظار',
      confirmedOrders: 'مؤكدة',
      processingOrders: 'قيد التجهيز',
      shippedOrders: 'تم الشحن',
      deliveredOrders: 'تم التوصيل',
      cancelledOrders: 'ملغية',
      customers: 'العملاء',
      analytics: 'التحليلات',
      reports: 'التقارير',
      settings: 'الإعدادات',
      storeSettings: 'إعدادات المتجر',
      shipping: 'الشحن',
      taxes: 'الضرائب',
      users: 'المستخدمين',
      search: 'بحث...',
      notifications: 'الإشعارات',
      profile: 'الملف الشخصي',
      logout: 'تسجيل الخروج',
      backToStore: 'العودة للمتجر',
      finance: 'المالية',
      revenue: 'الإيرادات',
      refunds: 'المرتجعات'
    },
    fr: {
      dashboard: 'Tableau de bord',
      products: 'Produits',
      allProducts: 'Tous les produits',
      addProduct: 'Ajouter un produit',
      categories: 'Catégories',
      orders: 'Commandes',
      allOrders: 'Toutes les commandes',
      pendingOrders: 'En attente',
      confirmedOrders: 'Confirmées',
      processingOrders: 'En préparation',
      shippedOrders: 'Expédiées',
      deliveredOrders: 'Livrées',
      cancelledOrders: 'Annulées',
      customers: 'Clients',
      analytics: 'Analytique',
      reports: 'Rapports',
      settings: 'Paramètres',
      storeSettings: 'Paramètres du magasin',
      shipping: 'Livraison',
      taxes: 'Taxes',
      users: 'Utilisateurs',
      search: 'Rechercher...',
      notifications: 'Notifications',
      profile: 'Profil',
      logout: 'Déconnexion',
      backToStore: 'Retour au magasin',
      finance: 'Finance',
      revenue: 'Revenus',
      refunds: 'Remboursements'
    },
    en: {
      dashboard: 'Dashboard',
      products: 'Products',
      allProducts: 'All Products',
      addProduct: 'Add Product',
      categories: 'Categories',
      orders: 'Orders',
      allOrders: 'All Orders',
      pendingOrders: 'Pending',
      confirmedOrders: 'Confirmed',
      processingOrders: 'Processing',
      shippedOrders: 'Shipped',
      deliveredOrders: 'Delivered',
      cancelledOrders: 'Cancelled',
      customers: 'Customers',
      analytics: 'Analytics',
      reports: 'Reports',
      settings: 'Settings',
      storeSettings: 'Store Settings',
      shipping: 'Shipping',
      taxes: 'Taxes',
      users: 'Users',
      search: 'Search...',
      notifications: 'Notifications',
      profile: 'Profile',
      logout: 'Logout',
      backToStore: 'Back to Store',
      finance: 'Finance',
      revenue: 'Revenue',
      refunds: 'Refunds'
    }
  };

  const text = l[language] || l.ar;
  const ChevronIcon = isRTL ? ChevronLeft : ChevronRight;

  const menuItems = [
    {
      id: 'dashboard',
      icon: LayoutDashboard,
      label: text.dashboard,
      path: '/admin'
    },
    {
      id: 'products',
      icon: Package,
      label: text.products,
      children: [
        { label: text.allProducts, path: '/admin/products' },
        { label: text.addProduct, path: '/admin/products/new' },
        { label: text.categories, path: '/admin/categories' }
      ]
    },
    {
      id: 'orders',
      icon: ShoppingCart,
      label: text.orders,
      children: [
        { label: text.allOrders, path: '/admin/orders' },
        { label: text.pendingOrders, path: '/admin/orders?status=pending' },
        { label: text.confirmedOrders, path: '/admin/orders?status=confirmed' },
        { label: text.processingOrders, path: '/admin/orders?status=processing' },
        { label: text.shippedOrders, path: '/admin/orders?status=shipped' },
        { label: text.deliveredOrders, path: '/admin/orders?status=delivered' },
        { label: text.cancelledOrders, path: '/admin/orders?status=cancelled' }
      ]
    },
    {
      id: 'customers',
      icon: Users,
      label: text.customers,
      path: '/admin/customers'
    },
    {
      id: 'finance',
      icon: DollarSign,
      label: text.finance,
      children: [
        { label: text.revenue, path: '/admin/finance/revenue' },
        { label: text.refunds, path: '/admin/finance/refunds' }
      ]
    },
    {
      id: 'analytics',
      icon: BarChart3,
      label: text.analytics,
      path: '/admin/analytics'
    },
    {
      id: 'settings',
      icon: Settings,
      label: text.settings,
      children: [
        { label: text.storeSettings, path: '/admin/settings/store' },
        { label: text.shipping, path: '/admin/settings/shipping' },
        { label: text.taxes, path: '/admin/settings/taxes' },
        { label: text.users, path: '/admin/settings/users' }
      ]
    }
  ];

  const toggleMenu = (menuId, forceExpand = false) => {
    setExpandedMenus(prev => {
      // If forceExpand is true, always expand
      if (forceExpand) {
        if (!prev.includes(menuId)) {
          return [...prev, menuId];
        }
        return prev;
      }
      // Regular toggle behavior - always allow toggle
      return prev.includes(menuId) 
        ? prev.filter(id => id !== menuId)
        : [...prev, menuId];
    });
  };

  // Auto-expand menu when navigating to a child page (only on initial load)
  useEffect(() => {
    const path = location.pathname;
    const search = location.search;
    
    // Determine which menu should be expanded based on current path
    let menuToExpand = null;
    if (path.startsWith('/admin/products') || path.startsWith('/admin/categories')) {
      menuToExpand = 'products';
    } else if (path.startsWith('/admin/orders')) {
      menuToExpand = 'orders';
    } else if (path.startsWith('/admin/finance')) {
      menuToExpand = 'finance';
    } else if (path.startsWith('/admin/settings')) {
      menuToExpand = 'settings';
    }
    
    // Only expand if not already expanded
    if (menuToExpand && !expandedMenus.includes(menuToExpand)) {
      setExpandedMenus(prev => [...prev, menuToExpand]);
    }
  }, []); // Only run once on mount

  // Check if parent menu has an active child (for styling the parent button)
  const hasActiveChild = (menuId) => {
    const path = location.pathname;
    switch (menuId) {
      case 'products':
        return path.startsWith('/admin/products') || path.startsWith('/admin/categories');
      case 'orders':
        return path.startsWith('/admin/orders');
      case 'finance':
        return path.startsWith('/admin/finance');
      case 'settings':
        return path.startsWith('/admin/settings');
      default:
        return false;
    }
  };

  // Check if a menu item is active (including query parameters for filters)
  const isActive = (itemPath) => {
    if (itemPath === '/admin') return location.pathname === '/admin';
    
    // Check if the path has query parameters
    if (itemPath.includes('?')) {
      const [basePath, queryString] = itemPath.split('?');
      const itemParams = new URLSearchParams(queryString);
      const currentParams = new URLSearchParams(location.search);
      
      // Check if base path matches and all item params match current params
      if (location.pathname !== basePath) return false;
      
      for (const [key, value] of itemParams.entries()) {
        if (currentParams.get(key) !== value) return false;
      }
      return true;
    }
    
    // For paths without query params, check exact match or no query params
    if (location.pathname === itemPath || location.pathname.startsWith(itemPath + '/')) {
      // If it's the base orders page (/admin/orders), only active if no status filter
      if (itemPath === '/admin/orders' && location.search) {
        return false;
      }
      return true;
    }
    
    return false;
  };

  const languages = [
    { code: 'ar', label: 'العربية', flag: '🇩🇿' },
    { code: 'fr', label: 'Français', flag: '🇫🇷' },
    { code: 'en', label: 'English', flag: '🇬🇧' }
  ];

  return (
    <div className={`min-h-screen bg-gray-50 dark:bg-gray-900 ${isRTL ? 'rtl' : 'ltr'}`} dir={isRTL ? 'rtl' : 'ltr'}>
      {/* Mobile Header */}
      <header className="lg:hidden fixed top-0 inset-x-0 z-50 h-16 bg-white dark:bg-gray-800 border-b dark:border-gray-700 flex items-center justify-between px-4">
        <button onClick={() => setMobileMenuOpen(true)} className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
          <Menu className="h-6 w-6" />
        </button>
        <h1 className="text-lg font-bold text-primary">AgroYousfi Admin</h1>
        <div className="flex items-center gap-2">
          <button onClick={() => setDarkMode(!darkMode)} className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            {darkMode ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
          </button>
        </div>
      </header>

      {/* Mobile Sidebar Overlay */}
      {mobileMenuOpen && (
        <div className="lg:hidden fixed inset-0 z-50">
          <div className="absolute inset-0 bg-black/50" onClick={() => setMobileMenuOpen(false)} />
          <div className={`absolute top-0 ${isRTL ? 'right-0' : 'left-0'} h-full w-72 bg-white dark:bg-gray-800 shadow-xl`}>
            <div className="flex items-center justify-between p-4 border-b dark:border-gray-700">
              <h2 className="font-bold text-lg">القائمة</h2>
              <button onClick={() => setMobileMenuOpen(false)} className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <X className="h-5 w-5" />
              </button>
            </div>
            <ScrollArea className="h-[calc(100vh-64px)]">
              <nav className="p-4 space-y-1">
                {menuItems.map(item => (
                  <div key={item.id}>
                    {item.children ? (
                      <>
                        <button
                          onClick={() => toggleMenu(item.id)}
                          className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                            expandedMenus.includes(item.id) || hasActiveChild(item.id)
                              ? 'bg-primary/10 text-primary'
                              : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                          }`}
                        >
                          <item.icon className="h-5 w-5" />
                          <span className="flex-1 text-start">{item.label}</span>
                          <ChevronDown className={`h-4 w-4 transition-transform ${expandedMenus.includes(item.id) ? 'rotate-180' : ''}`} />
                        </button>
                        {expandedMenus.includes(item.id) && (
                          <div className="mt-1 space-y-1 ps-8">
                            {item.children.map((child, idx) => (
                              <Link
                                key={idx}
                                to={child.path}
                                onClick={() => setMobileMenuOpen(false)}
                                className={`block px-3 py-2 rounded-lg text-sm transition-colors ${
                                  isActive(child.path)
                                    ? 'bg-primary text-white'
                                    : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                                }`}
                              >
                                {child.label}
                              </Link>
                            ))}
                          </div>
                        )}
                      </>
                    ) : (
                      <Link
                        to={item.path}
                        onClick={() => setMobileMenuOpen(false)}
                        className={`flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                          isActive(item.path)
                            ? 'bg-primary text-white'
                            : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                        }`}
                      >
                        <item.icon className="h-5 w-5" />
                        <span>{item.label}</span>
                      </Link>
                    )}
                  </div>
                ))}
              </nav>
            </ScrollArea>
          </div>
        </div>
      )}

      {/* Desktop Sidebar */}
      <aside className={`hidden lg:flex flex-col fixed top-0 ${isRTL ? 'right-0' : 'left-0'} h-screen bg-white dark:bg-gray-800 border-e dark:border-gray-700 transition-all duration-300 z-40 ${
        sidebarOpen ? 'w-64' : 'w-20'
      }`}>
        {/* Logo */}
        <div className="h-16 flex items-center justify-between px-4 border-b dark:border-gray-700">
          {sidebarOpen ? (
            <Link to="/admin" className="flex items-center gap-2">
              <div className="h-10 w-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <Store className="h-6 w-6 text-primary" />
              </div>
              <span className="font-bold text-lg">AgroYousfi</span>
            </Link>
          ) : (
            <div className="h-10 w-10 mx-auto rounded-xl bg-primary/10 flex items-center justify-center">
              <Store className="h-6 w-6 text-primary" />
            </div>
          )}
          <button
            onClick={() => setSidebarOpen(!sidebarOpen)}
            className={`p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 ${!sidebarOpen ? 'hidden' : ''}`}
          >
            {isRTL ? <ChevronRight className="h-5 w-5" /> : <ChevronLeft className="h-5 w-5" />}
          </button>
        </div>

        {/* Navigation */}
        <ScrollArea className="flex-1 py-4">
          <nav className="px-3 space-y-1">
            {menuItems.map(item => (
              <div key={item.id}>
                {item.children ? (
                  <>
                    <button
                      onClick={() => sidebarOpen && toggleMenu(item.id)}
                      title={!sidebarOpen ? item.label : undefined}
                      className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                        (expandedMenus.includes(item.id) || hasActiveChild(item.id)) && sidebarOpen
                          ? 'bg-primary/10 text-primary'
                          : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                      } ${!sidebarOpen ? 'justify-center' : ''}`}
                    >
                      <item.icon className="h-5 w-5 shrink-0" />
                      {sidebarOpen && (
                        <>
                          <span className="flex-1 text-start">{item.label}</span>
                          <ChevronDown className={`h-4 w-4 transition-transform ${expandedMenus.includes(item.id) ? 'rotate-180' : ''}`} />
                        </>
                      )}
                    </button>
                    {expandedMenus.includes(item.id) && sidebarOpen && (
                      <div className="mt-1 space-y-1 ps-8">
                        {item.children.map((child, idx) => (
                          <Link
                            key={idx}
                            to={child.path}
                            className={`block px-3 py-2 rounded-lg text-sm transition-colors ${
                              isActive(child.path)
                                ? 'bg-primary text-white'
                                : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300'
                            }`}
                          >
                            {child.label}
                          </Link>
                        ))}
                      </div>
                    )}
                  </>
                ) : (
                  <Link
                    to={item.path}
                    title={!sidebarOpen ? item.label : undefined}
                    className={`flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                      isActive(item.path)
                        ? 'bg-primary text-white'
                        : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                    } ${!sidebarOpen ? 'justify-center' : ''}`}
                  >
                    <item.icon className="h-5 w-5 shrink-0" />
                    {sidebarOpen && <span>{item.label}</span>}
                  </Link>
                )}
              </div>
            ))}
          </nav>
        </ScrollArea>

        {/* Bottom Actions */}
        <div className="p-3 border-t dark:border-gray-700 space-y-2">
          <Link
            to="/"
            className={`flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 ${
              !sidebarOpen ? 'justify-center' : ''
            }`}
          >
            <Store className="h-5 w-5" />
            {sidebarOpen && <span>{text.backToStore}</span>}
          </Link>
        </div>
      </aside>

      {/* Main Content */}
      <div className={`transition-all duration-300 ${sidebarOpen ? 'lg:ps-64' : 'lg:ps-20'}`}>
        {/* Top Bar */}
        <header className="hidden lg:flex h-16 items-center justify-between px-6 bg-white dark:bg-gray-800 border-b dark:border-gray-700 sticky top-0 z-30">
          <div className="flex items-center gap-4">
            <button
              onClick={() => setSidebarOpen(!sidebarOpen)}
              className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
            >
              <Menu className="h-5 w-5" />
            </button>
            <div className="relative">
              <Search className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'right-3' : 'left-3'} h-4 w-4 text-gray-400`} />
              <input
                type="text"
                placeholder={text.search}
                className={`w-64 h-10 ${isRTL ? 'pr-10 pl-4' : 'pl-10 pr-4'} rounded-lg border dark:border-gray-600 bg-gray-50 dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/20 text-sm`}
              />
            </div>
          </div>

          <div className="flex items-center gap-3">
            {/* Language Selector */}
            <div className="relative group">
              <button className="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <Globe className="h-5 w-5" />
                <span className="text-sm font-medium">{languages.find(l => l.code === language)?.flag}</span>
              </button>
              <div className="absolute top-full end-0 mt-2 w-40 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border dark:border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                {languages.map(lang => (
                  <button
                    key={lang.code}
                    onClick={() => setLanguage(lang.code)}
                    className={`w-full flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700 ${
                      language === lang.code ? 'text-primary font-medium' : ''
                    }`}
                  >
                    <span>{lang.flag}</span>
                    <span>{lang.label}</span>
                  </button>
                ))}
              </div>
            </div>

            {/* Dark Mode Toggle */}
            <button
              onClick={() => setDarkMode(!darkMode)}
              className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
            >
              {darkMode ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
            </button>

            {/* Notifications */}
            <button className="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
              <Bell className="h-5 w-5" />
              <span className="absolute top-1 end-1 h-2 w-2 bg-red-500 rounded-full" />
            </button>

            {/* User Menu */}
            <div className="relative group">
              <button className="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <div className="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                  {user?.picture ? (
                    <img src={user.picture} alt="" className="h-8 w-8 rounded-full object-cover" />
                  ) : (
                    <User className="h-4 w-4 text-primary" />
                  )}
                </div>
                <div className="text-start hidden xl:block">
                  <p className="text-sm font-medium">{user?.name}</p>
                  <p className="text-xs text-gray-500">Admin</p>
                </div>
                <ChevronDown className="h-4 w-4 hidden xl:block" />
              </button>
              <div className="absolute top-full end-0 mt-2 w-48 py-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border dark:border-gray-700 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                <Link to="/profile" className="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                  <User className="h-4 w-4" />
                  {text.profile}
                </Link>
                <button
                  onClick={logout}
                  className="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                >
                  <LogOut className="h-4 w-4" />
                  {text.logout}
                </button>
              </div>
            </div>
          </div>
        </header>

        {/* Page Content */}
        <main className="p-4 lg:p-6 mt-16 lg:mt-0 min-h-[calc(100vh-4rem)]">
          {children}
        </main>
      </div>
    </div>
  );
};

export default AdminLayout;
