import React, { useState, useEffect, useMemo } from 'react';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import {
  Search,
  Users,
  RefreshCw,
  Download,
  Eye,
  Phone,
  Mail,
  MapPin,
  Calendar,
  ShoppingBag,
  Star,
  TrendingUp
} from 'lucide-react';
import { format } from 'date-fns';
import { ar, fr, enUS } from 'date-fns/locale';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const CustomersPage = () => {
  const { language, isRTL, formatPrice } = useLanguage();
  const [customers, setCustomers] = useState([]);
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [selectedCustomer, setSelectedCustomer] = useState(null);

  const locale = language === 'ar' ? ar : language === 'fr' ? fr : enUS;

  const l = {
    ar: {
      customers: 'العملاء',
      allCustomers: 'جميع العملاء',
      search: 'بحث عن عميل...',
      refresh: 'تحديث',
      export: 'تصدير',
      customer: 'العميل',
      phone: 'الهاتف',
      email: 'البريد',
      orders: 'الطلبات',
      totalSpent: 'إجمالي المشتريات',
      joinDate: 'تاريخ الانضمام',
      actions: 'الإجراءات',
      viewDetails: 'عرض التفاصيل',
      customerDetails: 'تفاصيل العميل',
      orderHistory: 'سجل الطلبات',
      noCustomers: 'لا يوجد عملاء',
      noOrders: 'لا توجد طلبات',
      close: 'إغلاق',
      address: 'العنوان',
      wilaya: 'الولاية',
      segment: 'الفئة',
      new: 'جديد',
      loyal: 'مخلص',
      inactive: 'غير نشط',
      totalCustomers: 'إجمالي العملاء',
      newThisMonth: 'جدد هذا الشهر',
      avgOrderValue: 'متوسط قيمة الطلب'
    },
    fr: {
      customers: 'Clients',
      allCustomers: 'Tous les clients',
      search: 'Rechercher un client...',
      refresh: 'Actualiser',
      export: 'Exporter',
      customer: 'Client',
      phone: 'Téléphone',
      email: 'Email',
      orders: 'Commandes',
      totalSpent: 'Total dépensé',
      joinDate: "Date d'inscription",
      actions: 'Actions',
      viewDetails: 'Voir les détails',
      customerDetails: 'Détails du client',
      orderHistory: 'Historique des commandes',
      noCustomers: 'Aucun client',
      noOrders: 'Aucune commande',
      close: 'Fermer',
      address: 'Adresse',
      wilaya: 'Wilaya',
      segment: 'Segment',
      new: 'Nouveau',
      loyal: 'Fidèle',
      inactive: 'Inactif',
      totalCustomers: 'Total clients',
      newThisMonth: 'Nouveaux ce mois',
      avgOrderValue: 'Valeur moyenne'
    },
    en: {
      customers: 'Customers',
      allCustomers: 'All Customers',
      search: 'Search customers...',
      refresh: 'Refresh',
      export: 'Export',
      customer: 'Customer',
      phone: 'Phone',
      email: 'Email',
      orders: 'Orders',
      totalSpent: 'Total Spent',
      joinDate: 'Join Date',
      actions: 'Actions',
      viewDetails: 'View Details',
      customerDetails: 'Customer Details',
      orderHistory: 'Order History',
      noCustomers: 'No customers found',
      noOrders: 'No orders',
      close: 'Close',
      address: 'Address',
      wilaya: 'Wilaya',
      segment: 'Segment',
      new: 'New',
      loyal: 'Loyal',
      inactive: 'Inactive',
      totalCustomers: 'Total Customers',
      newThisMonth: 'New This Month',
      avgOrderValue: 'Avg Order Value'
    }
  };

  const text = l[language] || l.ar;

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const [ordersRes] = await Promise.all([
        axios.get(`${API}/admin/orders`, { withCredentials: true })
      ]);
      
      setOrders(ordersRes.data || []);
      
      // Extract unique customers from orders
      const customerMap = new Map();
      ordersRes.data?.forEach(order => {
        const key = order.phone || order.customer_name;
        if (!customerMap.has(key)) {
          customerMap.set(key, {
            id: key,
            name: order.customer_name,
            phone: order.phone,
            address: order.address,
            wilaya: order.wilaya,
            orders: [],
            totalSpent: 0,
            firstOrder: order.created_at,
            lastOrder: order.created_at
          });
        }
        const customer = customerMap.get(key);
        customer.orders.push(order);
        customer.totalSpent += order.total || 0;
        if (new Date(order.created_at) < new Date(customer.firstOrder)) {
          customer.firstOrder = order.created_at;
        }
        if (new Date(order.created_at) > new Date(customer.lastOrder)) {
          customer.lastOrder = order.created_at;
        }
      });
      
      setCustomers(Array.from(customerMap.values()));
    } catch (error) {
      console.error('Error fetching data:', error);
    } finally {
      setLoading(false);
    }
  };

  const filteredCustomers = useMemo(() => {
    return customers.filter(customer => {
      const searchMatch = !search ||
        customer.name?.toLowerCase().includes(search.toLowerCase()) ||
        customer.phone?.includes(search);
      return searchMatch;
    });
  }, [customers, search]);

  const getCustomerSegment = (customer) => {
    const orderCount = customer.orders?.length || 0;
    const daysSinceLastOrder = Math.floor((new Date() - new Date(customer.lastOrder)) / (1000 * 60 * 60 * 24));
    
    if (orderCount >= 3) return { label: text.loyal, color: 'bg-green-100 text-green-800' };
    if (daysSinceLastOrder > 60) return { label: text.inactive, color: 'bg-gray-100 text-gray-800' };
    return { label: text.new, color: 'bg-blue-100 text-blue-800' };
  };

  const stats = useMemo(() => {
    const now = new Date();
    const thisMonth = customers.filter(c => {
      const date = new Date(c.firstOrder);
      return date.getMonth() === now.getMonth() && date.getFullYear() === now.getFullYear();
    }).length;
    
    const totalSpent = customers.reduce((sum, c) => sum + c.totalSpent, 0);
    const totalOrders = customers.reduce((sum, c) => sum + c.orders.length, 0);
    
    return {
      total: customers.length,
      newThisMonth: thisMonth,
      avgOrderValue: totalOrders > 0 ? totalSpent / totalOrders : 0
    };
  }, [customers]);

  const customerOrders = useMemo(() => {
    if (!selectedCustomer) return [];
    return orders.filter(o => o.phone === selectedCustomer.phone);
  }, [selectedCustomer, orders]);

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold">{text.customers}</h1>
          <p className="text-muted-foreground">{filteredCustomers.length} {text.customer}</p>
        </div>
        <div className="flex items-center gap-2">
          <Button variant="outline" size="sm" onClick={fetchData}>
            <RefreshCw className="h-4 w-4 me-2" />
            {text.refresh}
          </Button>
          <Button variant="outline" size="sm">
            <Download className="h-4 w-4 me-2" />
            {text.export}
          </Button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-4">
              <div className="h-12 w-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                <Users className="h-6 w-6 text-blue-600" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground">{text.totalCustomers}</p>
                <p className="text-2xl font-bold">{stats.total}</p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-4">
              <div className="h-12 w-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <TrendingUp className="h-6 w-6 text-green-600" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground">{text.newThisMonth}</p>
                <p className="text-2xl font-bold">{stats.newThisMonth}</p>
              </div>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="p-6">
            <div className="flex items-center gap-4">
              <div className="h-12 w-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                <ShoppingBag className="h-6 w-6 text-purple-600" />
              </div>
              <div>
                <p className="text-sm text-muted-foreground">{text.avgOrderValue}</p>
                <p className="text-2xl font-bold">{formatPrice(stats.avgOrderValue)}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Search */}
      <Card>
        <CardContent className="p-4">
          <div className="relative">
            <Search className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'right-3' : 'left-3'} h-4 w-4 text-muted-foreground`} />
            <Input
              placeholder={text.search}
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className={isRTL ? 'pr-10' : 'pl-10'}
            />
          </div>
        </CardContent>
      </Card>

      {/* Customers Table */}
      <Card>
        <CardContent className="p-0">
          {loading ? (
            <div className="flex items-center justify-center h-64">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary" />
            </div>
          ) : filteredCustomers.length === 0 ? (
            <div className="flex flex-col items-center justify-center h-64 text-muted-foreground">
              <Users className="h-12 w-12 mb-4 opacity-50" />
              <p>{text.noCustomers}</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b bg-muted/50">
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.customer}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.phone}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.orders}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.totalSpent}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.segment}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.joinDate}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.actions}</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredCustomers.map(customer => {
                    const segment = getCustomerSegment(customer);
                    return (
                      <tr key={customer.id} className="border-b hover:bg-muted/30 transition-colors">
                        <td className="py-3 px-4">
                          <div className="flex items-center gap-3">
                            <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                              <span className="text-sm font-medium text-primary">
                                {customer.name?.charAt(0) || '?'}
                              </span>
                            </div>
                            <div>
                              <p className="font-medium">{customer.name}</p>
                              <p className="text-xs text-muted-foreground">{customer.wilaya}</p>
                            </div>
                          </div>
                        </td>
                        <td className="py-3 px-4">
                          <div className="flex items-center gap-1 text-sm">
                            <Phone className="h-3 w-3 text-muted-foreground" />
                            {customer.phone}
                          </div>
                        </td>
                        <td className="py-3 px-4">
                          <span className="font-medium">{customer.orders?.length || 0}</span>
                        </td>
                        <td className="py-3 px-4">
                          <span className="font-semibold">{formatPrice(customer.totalSpent)}</span>
                        </td>
                        <td className="py-3 px-4">
                          <span className={`inline-flex px-2 py-1 rounded-full text-xs font-medium ${segment.color}`}>
                            {segment.label}
                          </span>
                        </td>
                        <td className="py-3 px-4 text-sm">
                          {format(new Date(customer.firstOrder), 'dd/MM/yyyy', { locale })}
                        </td>
                        <td className="py-3 px-4">
                          <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8"
                            onClick={() => setSelectedCustomer(customer)}
                          >
                            <Eye className="h-4 w-4" />
                          </Button>
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

      {/* Customer Details Dialog */}
      <Dialog open={!!selectedCustomer} onOpenChange={() => setSelectedCustomer(null)}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>{text.customerDetails}</DialogTitle>
          </DialogHeader>
          
          {selectedCustomer && (
            <div className="space-y-6">
              {/* Customer Info */}
              <div className="p-4 bg-muted/50 rounded-lg">
                <div className="flex items-center gap-4 mb-4">
                  <div className="h-16 w-16 rounded-full bg-primary/10 flex items-center justify-center">
                    <span className="text-2xl font-bold text-primary">
                      {selectedCustomer.name?.charAt(0) || '?'}
                    </span>
                  </div>
                  <div>
                    <h3 className="text-lg font-semibold">{selectedCustomer.name}</h3>
                    <div className={`inline-flex px-2 py-1 rounded-full text-xs font-medium ${getCustomerSegment(selectedCustomer).color}`}>
                      {getCustomerSegment(selectedCustomer).label}
                    </div>
                  </div>
                </div>
                <div className="grid grid-cols-2 gap-4 text-sm">
                  <div className="flex items-center gap-2">
                    <Phone className="h-4 w-4 text-muted-foreground" />
                    <span>{selectedCustomer.phone}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <MapPin className="h-4 w-4 text-muted-foreground" />
                    <span>{selectedCustomer.wilaya}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <ShoppingBag className="h-4 w-4 text-muted-foreground" />
                    <span>{selectedCustomer.orders?.length} {text.orders}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Star className="h-4 w-4 text-muted-foreground" />
                    <span>{formatPrice(selectedCustomer.totalSpent)}</span>
                  </div>
                </div>
              </div>

              {/* Order History */}
              <div>
                <h3 className="font-medium mb-3">{text.orderHistory}</h3>
                {customerOrders.length > 0 ? (
                  <div className="border rounded-lg divide-y max-h-64 overflow-y-auto">
                    {customerOrders.map(order => (
                      <div key={order.order_id} className="p-3 flex items-center justify-between">
                        <div>
                          <p className="font-medium">#{order.order_id.slice(-6)}</p>
                          <p className="text-xs text-muted-foreground">
                            {format(new Date(order.created_at), 'dd/MM/yyyy HH:mm', { locale })}
                          </p>
                        </div>
                        <div className="text-end">
                          <p className="font-semibold">{formatPrice(order.total)}</p>
                          <p className="text-xs text-muted-foreground">
                            {order.items?.length} {text.orders}
                          </p>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <p className="text-muted-foreground text-center py-4">{text.noOrders}</p>
                )}
              </div>
            </div>
          )}
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default CustomersPage;
