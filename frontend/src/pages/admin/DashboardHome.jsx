import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  LineChart,
  Line,
  BarChart,
  Bar,
  PieChart,
  Pie,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  Area,
  AreaChart
} from 'recharts';
import {
  TrendingUp,
  TrendingDown,
  Package,
  ShoppingCart,
  Users,
  DollarSign,
  AlertTriangle,
  Clock,
  CheckCircle,
  Truck,
  XCircle,
  ArrowRight,
  ArrowLeft,
  Eye,
  RefreshCw,
  Phone,
  MapPin,
  Copy,
  User,
  ChevronDown,
  ChevronUp,
  Loader2
} from 'lucide-react';
import { format, subDays } from 'date-fns';
import { ar, fr, enUS } from 'date-fns/locale';
import { toast } from 'sonner';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const DashboardHome = () => {
  const { language, isRTL, formatPrice } = useLanguage();
  const [stats, setStats] = useState(null);
  const [recentOrders, setRecentOrders] = useState([]);
  const [lowStockProducts, setLowStockProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [period, setPeriod] = useState('week');
  const [expandedOrder, setExpandedOrder] = useState(null);
  const [confirmingOrder, setConfirmingOrder] = useState(null);

  const ArrowIcon = isRTL ? ArrowLeft : ArrowRight;
  const locale = language === 'ar' ? ar : language === 'fr' ? fr : enUS;

  const l = {
    ar: {
      dashboard: 'لوحة التحكم',
      overview: 'نظرة عامة',
      today: 'اليوم',
      week: 'هذا الأسبوع',
      month: 'هذا الشهر',
      totalRevenue: 'إجمالي الإيرادات',
      totalOrders: 'إجمالي الطلبات',
      totalProducts: 'إجمالي المنتجات',
      totalCustomers: 'إجمالي العملاء',
      pendingOrders: 'طلبات معلقة',
      recentOrders: 'طلبات تحتاج معالجة',
      viewAll: 'عرض الكل',
      orderStatus: 'حالة الطلبات',
      salesChart: 'رسم بياني للمبيعات',
      topProducts: 'أفضل المنتجات مبيعاً',
      lowStock: 'تنبيه المخزون المنخفض',
      pending: 'قيد الانتظار',
      confirmed: 'مؤكد',
      shipped: 'تم الشحن',
      delivered: 'تم التوصيل',
      cancelled: 'ملغي',
      orderId: 'رقم الطلب',
      customer: 'العميل',
      amount: 'المبلغ',
      status: 'الحالة',
      date: 'التاريخ',
      product: 'المنتج',
      stock: 'المخزون',
      noLowStock: 'لا توجد منتجات بمخزون منخفض',
      refresh: 'تحديث',
      salesByDay: 'المبيعات اليومية',
      ordersCount: 'عدد الطلبات',
      revenue: 'الإيرادات',
      phone: 'الهاتف',
      wilaya: 'الولاية',
      address: 'العنوان',
      products: 'المنتجات',
      callCustomer: 'اتصل',
      copyPhone: 'نسخ',
      phoneCopied: 'تم نسخ رقم الهاتف',
      confirmOrder: 'تأكيد الطلب',
      orderConfirmed: 'تم تأكيد الطلب بنجاح',
      noOrdersToProcess: 'لا توجد طلبات تحتاج معالجة',
      allOrdersProcessed: 'جميع الطلبات تمت معالجتها',
      callFirst: 'اتصل بالعميل أولاً ثم أكد الطلب',
      items: 'منتجات'
    },
    fr: {
      dashboard: 'Tableau de bord',
      overview: 'Aperçu',
      today: "Aujourd'hui",
      week: 'Cette semaine',
      month: 'Ce mois',
      totalRevenue: 'Revenu total',
      totalOrders: 'Total des commandes',
      totalProducts: 'Total des produits',
      totalCustomers: 'Total des clients',
      pendingOrders: 'Commandes en attente',
      recentOrders: 'Commandes à traiter',
      viewAll: 'Voir tout',
      orderStatus: 'Statut des commandes',
      salesChart: 'Graphique des ventes',
      topProducts: 'Produits les plus vendus',
      lowStock: 'Alerte stock faible',
      pending: 'En attente',
      confirmed: 'Confirmée',
      shipped: 'Expédiée',
      delivered: 'Livrée',
      cancelled: 'Annulée',
      orderId: 'N° Commande',
      customer: 'Client',
      amount: 'Montant',
      status: 'Statut',
      date: 'Date',
      product: 'Produit',
      stock: 'Stock',
      noLowStock: 'Aucun produit à stock faible',
      refresh: 'Actualiser',
      salesByDay: 'Ventes par jour',
      ordersCount: 'Nombre de commandes',
      revenue: 'Revenus',
      phone: 'Téléphone',
      wilaya: 'Wilaya',
      address: 'Adresse',
      products: 'Produits',
      callCustomer: 'Appeler',
      copyPhone: 'Copier',
      phoneCopied: 'Numéro copié',
      confirmOrder: 'Confirmer',
      orderConfirmed: 'Commande confirmée',
      noOrdersToProcess: 'Aucune commande à traiter',
      allOrdersProcessed: 'Toutes les commandes traitées',
      callFirst: 'Appelez le client avant de confirmer',
      items: 'articles'
    },
    en: {
      dashboard: 'Dashboard',
      overview: 'Overview',
      today: 'Today',
      week: 'This Week',
      month: 'This Month',
      totalRevenue: 'Total Revenue',
      totalOrders: 'Total Orders',
      totalProducts: 'Total Products',
      totalCustomers: 'Total Customers',
      pendingOrders: 'Pending Orders',
      recentOrders: 'Orders to Process',
      viewAll: 'View All',
      orderStatus: 'Order Status',
      salesChart: 'Sales Chart',
      topProducts: 'Top Selling Products',
      lowStock: 'Low Stock Alert',
      pending: 'Pending',
      confirmed: 'Confirmed',
      shipped: 'Shipped',
      delivered: 'Delivered',
      cancelled: 'Cancelled',
      orderId: 'Order ID',
      customer: 'Customer',
      amount: 'Amount',
      status: 'Status',
      date: 'Date',
      product: 'Product',
      stock: 'Stock',
      noLowStock: 'No low stock products',
      refresh: 'Refresh',
      salesByDay: 'Sales by Day',
      ordersCount: 'Orders Count',
      revenue: 'Revenue',
      phone: 'Phone',
      wilaya: 'Wilaya',
      address: 'Address',
      products: 'Products',
      callCustomer: 'Call',
      copyPhone: 'Copy',
      phoneCopied: 'Phone copied',
      confirmOrder: 'Confirm',
      orderConfirmed: 'Order confirmed',
      noOrdersToProcess: 'No orders to process',
      allOrdersProcessed: 'All orders processed',
      callFirst: 'Call the customer before confirming',
      items: 'items'
    }
  };

  const text = l[language] || l.ar;

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    try {
      setLoading(true);
      
      const statsRes = await axios.get(`${API}/admin/stats`, { withCredentials: true });
      setStats(statsRes.data);

      const ordersRes = await axios.get(`${API}/admin/orders`, { withCredentials: true });
      const allOrders = ordersRes.data || [];
      const pendingOrders = allOrders.filter(o => o.status === 'pending');
      setRecentOrders(pendingOrders);

      const productsRes = await axios.get(`${API}/products`, { withCredentials: true });
      const lowStock = productsRes.data?.filter(p => p.stock < 20) || [];
      setLowStockProducts(lowStock.slice(0, 5));

    } catch (error) {
      console.error('Error fetching dashboard data:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleConfirmOrder = async (orderId) => {
    try {
      setConfirmingOrder(orderId);
      await axios.put(
        `${API}/admin/orders/${orderId}/status`,
        { status: 'confirmed' },
        { withCredentials: true }
      );
      setRecentOrders(prev => prev.filter(o => o.order_id !== orderId));
      toast.success(text.orderConfirmed);
    } catch (error) {
      console.error('Error confirming order:', error);
      toast.error(language === 'ar' ? 'خطأ في تأكيد الطلب' : 'Error confirming order');
    } finally {
      setConfirmingOrder(null);
    }
  };

  const copyPhone = (phone) => {
    navigator.clipboard.writeText(phone);
    toast.success(text.phoneCopied);
  };

  const callCustomer = (phone) => {
    window.location.href = `tel:${phone}`;
  };

  const generateSalesData = () => {
    const days = period === 'today' ? 24 : period === 'week' ? 7 : 30;
    return Array.from({ length: days }, (_, i) => {
      const date = period === 'today' 
        ? `${i}:00`
        : format(subDays(new Date(), days - 1 - i), 'dd/MM', { locale });
      return {
        date,
        revenue: Math.floor(Math.random() * 50000) + 10000,
        orders: Math.floor(Math.random() * 20) + 5
      };
    });
  };

  const salesData = generateSalesData();

  const orderStatusData = [
    { name: text.pending, value: stats?.pending_orders || 0, color: '#f59e0b' },
    { name: text.confirmed, value: 12, color: '#3b82f6' },
    { name: text.shipped, value: 8, color: '#8b5cf6' },
    { name: text.delivered, value: stats?.total_orders ? stats.total_orders - (stats?.pending_orders || 0) - 20 : 15, color: '#10b981' },
    { name: text.cancelled, value: 3, color: '#ef4444' }
  ];

  const topProductsData = [
    { name: language === 'ar' ? 'بذور القمح' : 'Wheat Seeds', sales: 45 },
    { name: language === 'ar' ? 'سماد NPK' : 'NPK Fertilizer', sales: 38 },
    { name: language === 'ar' ? 'نظام ري' : 'Irrigation Kit', sales: 32 },
    { name: language === 'ar' ? 'بذور طماطم' : 'Tomato Seeds', sales: 28 },
    { name: language === 'ar' ? 'مبيد حشري' : 'Insecticide', sales: 22 }
  ];

  // Render stat card
  const renderStatCard = (title, value, Icon, trend, trendValue, color) => (
    <Card className="relative overflow-hidden">
      <CardContent className="p-6">
        <div className="flex items-start justify-between">
          <div>
            <p className="text-sm font-medium text-muted-foreground">{title}</p>
            <p className="text-2xl font-bold mt-2">{value}</p>
            {trend && (
              <div className={`flex items-center gap-1 mt-2 text-sm ${
                trend === 'up' ? 'text-green-600' : 'text-red-600'
              }`}>
                {trend === 'up' ? <TrendingUp className="h-4 w-4" /> : <TrendingDown className="h-4 w-4" />}
                <span>{trendValue}</span>
              </div>
            )}
          </div>
          <div className={`h-12 w-12 rounded-xl flex items-center justify-center ${color}`}>
            <Icon className="h-6 w-6 text-white" />
          </div>
        </div>
      </CardContent>
    </Card>
  );

  // Render order card
  const renderOrderCard = (order) => {
    const isExpanded = expandedOrder === order.order_id;
    const isConfirming = confirmingOrder === order.order_id;

    return (
      <div key={order.order_id} className="border rounded-xl overflow-hidden bg-card hover:shadow-md transition-shadow">
        {/* Main Info Row */}
        <div className="p-4">
          <div className="flex flex-col lg:flex-row lg:items-center gap-4">
            {/* Order ID & Date */}
            <div className="flex items-center justify-between lg:w-32">
              <div>
                <p className="font-bold text-primary text-lg">#{order.order_id.slice(-6)}</p>
                <p className="text-xs text-muted-foreground">
                  {format(new Date(order.created_at), 'dd/MM HH:mm', { locale })}
                </p>
              </div>
              <Badge className="lg:hidden bg-yellow-100 text-yellow-800 border-yellow-300">
                <Clock className="h-3 w-3 me-1" />
                {text.pending}
              </Badge>
            </div>

            {/* Customer Info */}
            <div className="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
              {/* Name & Phone */}
              <div className="flex items-start gap-3">
                <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                  <User className="h-5 w-5 text-primary" />
                </div>
                <div className="min-w-0">
                  <p className="font-semibold">{order.customer_name}</p>
                  <div className="flex items-center gap-2 mt-1">
                    <a 
                      href={`tel:${order.phone}`}
                      className="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 rounded-full text-sm hover:bg-green-200 transition-colors"
                    >
                      <Phone className="h-3.5 w-3.5" />
                      <span className="font-medium" dir="ltr">{order.phone}</span>
                    </a>
                    <button 
                      onClick={() => copyPhone(order.phone)}
                      className="p-1 hover:bg-muted rounded"
                      title={text.copyPhone}
                    >
                      <Copy className="h-3.5 w-3.5 text-muted-foreground" />
                    </button>
                  </div>
                </div>
              </div>

              {/* Wilaya & Address */}
              <div className="flex items-start gap-3">
                <div className="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center shrink-0">
                  <MapPin className="h-5 w-5 text-orange-600" />
                </div>
                <div className="min-w-0">
                  <p className="font-semibold">{order.wilaya}</p>
                  <p className="text-sm text-muted-foreground truncate">{order.address}</p>
                </div>
              </div>
            </div>

            {/* Total & Actions */}
            <div className="flex items-center justify-between lg:justify-end gap-3">
              <div className="text-end">
                <p className="font-bold text-xl text-primary">{formatPrice(order.total)}</p>
                <p className="text-xs text-muted-foreground">
                  {order.items?.length || 0} {text.items}
                </p>
              </div>
              <div className="flex items-center gap-2">
                <Button
                  onClick={() => handleConfirmOrder(order.order_id)}
                  disabled={isConfirming}
                  className="bg-green-600 hover:bg-green-700"
                >
                  {isConfirming ? (
                    <Loader2 className="h-4 w-4 animate-spin" />
                  ) : (
                    <>
                      <CheckCircle className="h-4 w-4 me-1" />
                      {text.confirmOrder}
                    </>
                  )}
                </Button>
                <Button
                  variant="ghost"
                  size="icon"
                  onClick={() => setExpandedOrder(isExpanded ? null : order.order_id)}
                >
                  {isExpanded ? <ChevronUp className="h-5 w-5" /> : <ChevronDown className="h-5 w-5" />}
                </Button>
              </div>
            </div>
          </div>
        </div>

        {/* Expanded Products Section */}
        {isExpanded && (
          <div className="border-t bg-muted/30 p-4">
            <h4 className="font-medium mb-3 flex items-center gap-2 text-sm">
              <Package className="h-4 w-4" />
              {text.products}
            </h4>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
              {order.items?.map((item, idx) => (
                <div key={idx} className="flex items-center gap-3 p-2 bg-background rounded-lg">
                  {item.image ? (
                    <img src={item.image} alt={item.name} className="h-12 w-12 rounded-lg object-cover" />
                  ) : (
                    <div className="h-12 w-12 bg-muted rounded-lg flex items-center justify-center">
                      <Package className="h-6 w-6 text-muted-foreground" />
                    </div>
                  )}
                  <div className="flex-1 min-w-0">
                    <p className="font-medium text-sm truncate">{item.name}</p>
                    <p className="text-xs text-muted-foreground">
                      {formatPrice(item.price)} × {item.quantity}
                    </p>
                  </div>
                  <p className="font-semibold text-sm">{formatPrice(item.price * item.quantity)}</p>
                </div>
              ))}
            </div>
            {order.notes && (
              <div className="mt-3 p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                <p className="text-sm"><strong>ملاحظات:</strong> {order.notes}</p>
              </div>
            )}
          </div>
        )}
      </div>
    );
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold">{text.dashboard}</h1>
          <p className="text-muted-foreground">{text.overview}</p>
        </div>
        <div className="flex items-center gap-3">
          <div className="flex items-center bg-muted rounded-lg p-1">
            {['today', 'week', 'month'].map(p => (
              <button
                key={p}
                onClick={() => setPeriod(p)}
                className={`px-3 py-1.5 text-sm font-medium rounded-md transition-colors ${
                  period === p
                    ? 'bg-white dark:bg-gray-800 shadow'
                    : 'hover:bg-white/50 dark:hover:bg-gray-700/50'
                }`}
              >
                {text[p]}
              </button>
            ))}
          </div>
          <Button variant="outline" size="sm" onClick={fetchDashboardData}>
            <RefreshCw className="h-4 w-4 me-2" />
            {text.refresh}
          </Button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard
          title={text.totalRevenue}
          value={formatPrice(stats?.total_revenue || 0)}
          icon={DollarSign}
          trend="up"
          trendValue="+12.5%"
          color="bg-green-500"
        />
        <StatCard
          title={text.totalOrders}
          value={stats?.total_orders || 0}
          icon={ShoppingCart}
          trend="up"
          trendValue="+8.2%"
          color="bg-blue-500"
        />
        <StatCard
          title={text.totalProducts}
          value={stats?.total_products || 0}
          icon={Package}
          color="bg-purple-500"
        />
        <StatCard
          title={text.totalCustomers}
          value={stats?.total_users || 0}
          icon={Users}
          trend="up"
          trendValue="+5.1%"
          color="bg-orange-500"
        />
      </div>

      {/* Orders to Process - Full Width */}
      <Card>
        <CardHeader className="flex flex-row items-center justify-between pb-2">
          <div className="flex items-center gap-3">
            <div className="h-10 w-10 rounded-xl bg-yellow-100 flex items-center justify-center">
              <Clock className="h-5 w-5 text-yellow-600" />
            </div>
            <div>
              <CardTitle className="text-lg">{text.recentOrders}</CardTitle>
              <p className="text-sm text-muted-foreground">{text.callFirst}</p>
            </div>
          </div>
          <div className="flex items-center gap-2">
            <Badge variant="secondary" className="text-lg px-3">
              {recentOrders.length}
            </Badge>
            <Link to="/admin/orders">
              <Button variant="outline" size="sm">
                {text.viewAll}
                <ArrowIcon className="h-4 w-4 ms-1" />
              </Button>
            </Link>
          </div>
        </CardHeader>
        <CardContent>
          {recentOrders.length === 0 ? (
            <div className="text-center py-12 text-muted-foreground">
              <CheckCircle className="h-16 w-16 mx-auto mb-4 text-green-500 opacity-50" />
              <p className="text-lg font-medium">{text.allOrdersProcessed}</p>
              <p className="text-sm">{text.noOrdersToProcess}</p>
            </div>
          ) : (
            <div className="space-y-4">
              {recentOrders.map(order => (
                <OrderCard key={order.order_id} order={order} />
              ))}
            </div>
          )}
        </CardContent>
      </Card>

      {/* Charts Row */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Sales Chart */}
        <Card className="lg:col-span-2">
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-base font-medium">{text.salesChart}</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-72">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={salesData}>
                  <defs>
                    <linearGradient id="colorRevenue" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#10b981" stopOpacity={0.3}/>
                      <stop offset="95%" stopColor="#10b981" stopOpacity={0}/>
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                  <XAxis dataKey="date" className="text-xs" />
                  <YAxis className="text-xs" />
                  <Tooltip
                    contentStyle={{
                      backgroundColor: 'hsl(var(--card))',
                      border: '1px solid hsl(var(--border))',
                      borderRadius: '8px'
                    }}
                    formatter={(value) => [formatPrice(value), text.revenue]}
                  />
                  <Area
                    type="monotone"
                    dataKey="revenue"
                    stroke="#10b981"
                    strokeWidth={2}
                    fillOpacity={1}
                    fill="url(#colorRevenue)"
                  />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>

        {/* Order Status Pie Chart */}
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-base font-medium">{text.orderStatus}</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-56">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={orderStatusData}
                    cx="50%"
                    cy="50%"
                    innerRadius={50}
                    outerRadius={70}
                    paddingAngle={5}
                    dataKey="value"
                  >
                    {orderStatusData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            </div>
            <div className="grid grid-cols-2 gap-2 mt-2">
              {orderStatusData.map((item, idx) => (
                <div key={idx} className="flex items-center gap-2 text-sm">
                  <div className="h-3 w-3 rounded-full" style={{ backgroundColor: item.color }} />
                  <span className="text-muted-foreground text-xs">{item.name}</span>
                  <span className="font-medium ms-auto">{item.value}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Bottom Row */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Low Stock Alert */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-base font-medium flex items-center gap-2">
              <AlertTriangle className="h-5 w-5 text-yellow-500" />
              {text.lowStock}
            </CardTitle>
          </CardHeader>
          <CardContent>
            {lowStockProducts.length > 0 ? (
              <div className="space-y-3">
                {lowStockProducts.map(product => (
                  <div key={product.product_id} className="flex items-center gap-3 p-2 rounded-lg hover:bg-muted/50">
                    <img
                      src={product.images?.[0] || 'https://via.placeholder.com/40'}
                      alt=""
                      className="h-10 w-10 rounded-lg object-cover"
                    />
                    <div className="flex-1 min-w-0">
                      <p className="font-medium text-sm truncate">
                        {product[`name_${language}`] || product.name_ar}
                      </p>
                      <p className={`text-xs font-medium ${
                        product.stock < 10 ? 'text-red-500' : 'text-yellow-500'
                      }`}>
                        {text.stock}: {product.stock}
                      </p>
                    </div>
                    <Link to={`/admin/products/${product.product_id}`}>
                      <Button variant="ghost" size="icon" className="h-8 w-8">
                        <Eye className="h-4 w-4" />
                      </Button>
                    </Link>
                  </div>
                ))}
              </div>
            ) : (
              <div className="text-center py-8 text-muted-foreground">
                <Package className="h-12 w-12 mx-auto mb-2 opacity-50" />
                <p>{text.noLowStock}</p>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Top Products Bar Chart */}
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-base font-medium">{text.topProducts}</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-56">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={topProductsData} layout="vertical">
                  <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                  <XAxis type="number" className="text-xs" />
                  <YAxis dataKey="name" type="category" width={100} className="text-xs" />
                  <Tooltip
                    contentStyle={{
                      backgroundColor: 'hsl(var(--card))',
                      border: '1px solid hsl(var(--border))',
                      borderRadius: '8px'
                    }}
                  />
                  <Bar dataKey="sales" fill="#10b981" radius={[0, 4, 4, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default DashboardHome;
