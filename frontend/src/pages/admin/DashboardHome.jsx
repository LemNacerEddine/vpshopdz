import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
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
  Calendar
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
      revenue: 'الإيرادات'
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
      revenue: 'Revenus'
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
      revenue: 'Revenue'
    }
  };

  const text = l[language] || l.ar;

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    try {
      setLoading(true);
      
      // Fetch stats
      const statsRes = await axios.get(`${API}/admin/stats`, { withCredentials: true });
      setStats(statsRes.data);

      // Fetch orders - filter to show only pending/unprocessed orders
      const ordersRes = await axios.get(`${API}/admin/orders`, { withCredentials: true });
      const allOrders = ordersRes.data || [];
      // Filter only pending orders (not processed yet)
      const pendingOrders = allOrders.filter(o => o.status === 'pending');
      setRecentOrders(pendingOrders.slice(0, 5));

      // Fetch low stock products
      const productsRes = await axios.get(`${API}/products`, { withCredentials: true });
      const lowStock = productsRes.data?.filter(p => p.stock < 20) || [];
      setLowStockProducts(lowStock.slice(0, 5));

    } catch (error) {
      console.error('Error fetching dashboard data:', error);
    } finally {
      setLoading(false);
    }
  };

  // Generate mock sales data for chart
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

  // Order status data for pie chart
  const orderStatusData = [
    { name: text.pending, value: stats?.pending_orders || 0, color: '#f59e0b' },
    { name: text.confirmed, value: 12, color: '#3b82f6' },
    { name: text.shipped, value: 8, color: '#8b5cf6' },
    { name: text.delivered, value: stats?.total_orders ? stats.total_orders - (stats?.pending_orders || 0) - 20 : 15, color: '#10b981' },
    { name: text.cancelled, value: 3, color: '#ef4444' }
  ];

  // Top products data
  const topProductsData = [
    { name: language === 'ar' ? 'بذور القمح' : 'Wheat Seeds', sales: 45 },
    { name: language === 'ar' ? 'سماد NPK' : 'NPK Fertilizer', sales: 38 },
    { name: language === 'ar' ? 'نظام ري' : 'Irrigation Kit', sales: 32 },
    { name: language === 'ar' ? 'بذور طماطم' : 'Tomato Seeds', sales: 28 },
    { name: language === 'ar' ? 'مبيد حشري' : 'Insecticide', sales: 22 }
  ];

  const getStatusColor = (status) => {
    switch (status) {
      case 'pending': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
      case 'confirmed': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
      case 'shipped': return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
      case 'delivered': return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
      case 'cancelled': return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusIcon = (status) => {
    switch (status) {
      case 'pending': return Clock;
      case 'confirmed': return CheckCircle;
      case 'shipped': return Truck;
      case 'delivered': return CheckCircle;
      case 'cancelled': return XCircle;
      default: return Clock;
    }
  };

  const StatCard = ({ title, value, icon: Icon, trend, trendValue, color }) => (
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
          {/* Period Selector */}
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

      {/* Charts Row */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Sales Chart */}
        <Card className="lg:col-span-2">
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-base font-medium">{text.salesChart}</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-80">
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
            <div className="h-64">
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={orderStatusData}
                    cx="50%"
                    cy="50%"
                    innerRadius={60}
                    outerRadius={80}
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
            <div className="grid grid-cols-2 gap-2 mt-4">
              {orderStatusData.map((item, idx) => (
                <div key={idx} className="flex items-center gap-2 text-sm">
                  <div className="h-3 w-3 rounded-full" style={{ backgroundColor: item.color }} />
                  <span className="text-muted-foreground">{item.name}</span>
                  <span className="font-medium ms-auto">{item.value}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Bottom Row */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Recent Orders */}
        <Card className="lg:col-span-2">
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-base font-medium">{text.recentOrders}</CardTitle>
            <Link to="/admin/orders">
              <Button variant="ghost" size="sm">
                {text.viewAll}
                <ArrowIcon className="h-4 w-4 ms-1" />
              </Button>
            </Link>
          </CardHeader>
          <CardContent>
            {recentOrders.length === 0 ? (
              <div className="text-center py-8 text-muted-foreground">
                <CheckCircle className="h-12 w-12 mx-auto mb-2 opacity-50 text-green-500" />
                <p>{language === 'ar' ? 'لا توجد طلبات تحتاج معالجة' : 'No orders to process'}</p>
              </div>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead>
                    <tr className="border-b">
                      <th className="text-start py-3 px-2 text-sm font-medium text-muted-foreground">{text.orderId}</th>
                      <th className="text-start py-3 px-2 text-sm font-medium text-muted-foreground">{text.customer}</th>
                      <th className="text-start py-3 px-2 text-sm font-medium text-muted-foreground">{text.amount}</th>
                      <th className="text-start py-3 px-2 text-sm font-medium text-muted-foreground">{text.status}</th>
                      <th className="text-start py-3 px-2 text-sm font-medium text-muted-foreground">{language === 'ar' ? 'الإجراءات' : 'Actions'}</th>
                    </tr>
                  </thead>
                  <tbody>
                    {recentOrders.map(order => {
                      const StatusIcon = getStatusIcon(order.status);
                      return (
                        <tr key={order.order_id} className="border-b last:border-0 hover:bg-muted/50">
                          <td className="py-3 px-2">
                            <Link to={`/admin/orders/${order.order_id}`} className="font-medium text-primary hover:underline">
                              #{order.order_id.slice(-6)}
                            </Link>
                          </td>
                          <td className="py-3 px-2">{order.customer_name}</td>
                          <td className="py-3 px-2 font-medium">{formatPrice(order.total)}</td>
                          <td className="py-3 px-2">
                            <span className={`inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(order.status)}`}>
                              <StatusIcon className="h-3 w-3" />
                              {text[order.status]}
                            </span>
                          </td>
                          <td className="py-3 px-2">
                            <div className="flex items-center gap-1">
                              <Button
                                variant="outline"
                                size="sm"
                                className="h-7 text-xs"
                                onClick={() => handleConfirmOrder(order.order_id)}
                              >
                                <CheckCircle className="h-3 w-3 me-1" />
                                {language === 'ar' ? 'تأكيد' : 'Confirm'}
                              </Button>
                              <Link to={`/admin/orders?id=${order.order_id}`}>
                                <Button variant="ghost" size="sm" className="h-7 text-xs">
                                  <Eye className="h-3 w-3" />
                                </Button>
                              </Link>
                            </div>
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>
            )}
          </CardContent>
        </Card>

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
      </div>

      {/* Top Products Bar Chart */}
      <Card>
        <CardHeader className="pb-2">
          <CardTitle className="text-base font-medium">{text.topProducts}</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="h-64">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={topProductsData} layout="vertical">
                <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                <XAxis type="number" className="text-xs" />
                <YAxis dataKey="name" type="category" width={120} className="text-xs" />
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
  );
};

export default DashboardHome;
