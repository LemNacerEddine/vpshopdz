import React, { useState, useEffect, useMemo } from 'react';
import { useSearchParams } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
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
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion';
import {
  Search,
  Eye,
  RefreshCw,
  Clock,
  CheckCircle,
  Truck,
  XCircle,
  Package,
  Download,
  FileText,
  Phone,
  MapPin,
  Calendar,
  User,
  ChevronRight,
  AlertCircle,
  PackageCheck,
  Loader2,
  Copy
} from 'lucide-react';
import { format } from 'date-fns';
import { ar, fr, enUS } from 'date-fns/locale';
import { toast } from 'sonner';
import jsPDF from 'jspdf';
import 'jspdf-autotable';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const OrdersPage = () => {
  const { language, isRTL, formatPrice } = useLanguage();
  const [searchParams, setSearchParams] = useSearchParams();
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState(searchParams.get('status') || 'all');
  const [dateFilter, setDateFilter] = useState('all');
  const [selectedOrder, setSelectedOrder] = useState(null);
  const [updatingOrder, setUpdatingOrder] = useState(null);
  const [expandedOrders, setExpandedOrders] = useState([]);

  const locale = language === 'ar' ? ar : language === 'fr' ? fr : enUS;

  const l = {
    ar: {
      orders: 'الطلبات',
      search: 'بحث برقم الطلب، اسم العميل أو الهاتف...',
      status: 'الحالة',
      allStatus: 'جميع الحالات',
      pending: 'قيد الانتظار',
      confirmed: 'مؤكد',
      processing: 'قيد التجهيز',
      shipped: 'تم الشحن',
      delivered: 'تم التوصيل',
      cancelled: 'ملغي',
      date: 'التاريخ',
      allDates: 'جميع التواريخ',
      today: 'اليوم',
      week: 'هذا الأسبوع',
      month: 'هذا الشهر',
      orderId: 'رقم الطلب',
      customer: 'العميل',
      phone: 'الهاتف',
      wilaya: 'الولاية',
      address: 'العنوان',
      items: 'المنتجات',
      total: 'الإجمالي',
      actions: 'الإجراءات',
      viewDetails: 'عرض التفاصيل',
      printInvoice: 'طباعة الفاتورة',
      refresh: 'تحديث',
      export: 'تصدير',
      noOrders: 'لا توجد طلبات',
      orderDetails: 'تفاصيل الطلب',
      customerInfo: 'معلومات العميل',
      orderItems: 'عناصر الطلب',
      product: 'المنتج',
      quantity: 'الكمية',
      price: 'السعر',
      subtotal: 'المجموع الفرعي',
      notes: 'ملاحظات',
      close: 'إغلاق',
      confirmOrder: 'تأكيد الطلب',
      prepareOrder: 'تجهيز الطلب',
      shipOrder: 'إرسال للشحن',
      markDelivered: 'تم التوصيل',
      cancelOrder: 'إلغاء الطلب',
      statusUpdated: 'تم تحديث حالة الطلب',
      invoice: 'فاتورة',
      paymentMethod: 'طريقة الدفع',
      cod: 'الدفع عند الاستلام',
      callCustomer: 'اتصل بالعميل',
      copyPhone: 'نسخ الرقم',
      phoneCopied: 'تم نسخ رقم الهاتف',
      confirmOrderTitle: 'تأكيد الطلب',
      confirmOrderDesc: 'هل تريد تأكيد هذا الطلب بعد الاتصال بالعميل؟',
      yes: 'نعم، تأكيد',
      no: 'لا',
      orderConfirmed: 'تم تأكيد الطلب بنجاح',
      orderPreparing: 'الطلب قيد التجهيز الآن',
      orderShipped: 'تم إرسال الطلب لشركة الشحن',
      orderDelivered: 'تم تسليم الطلب للعميل',
      orderCancelled: 'تم إلغاء الطلب',
      pendingOrders: 'طلبات جديدة',
      confirmedOrders: 'طلبات مؤكدة',
      shippedOrders: 'طلبات مشحونة',
      workflow: 'سير العمل',
      step1: '1. اتصل بالعميل',
      step2: '2. أكد الطلب',
      step3: '3. جهز الطلبية',
      step4: '4. أرسل للشحن',
      shippingCompany: 'شركة الشحن'
    },
    fr: {
      orders: 'Commandes',
      search: 'Rechercher par numéro, client ou téléphone...',
      status: 'Statut',
      allStatus: 'Tous',
      pending: 'En attente',
      confirmed: 'Confirmée',
      processing: 'En préparation',
      shipped: 'Expédiée',
      delivered: 'Livrée',
      cancelled: 'Annulée',
      date: 'Date',
      allDates: 'Toutes',
      today: "Aujourd'hui",
      week: 'Cette semaine',
      month: 'Ce mois',
      orderId: 'N° Commande',
      customer: 'Client',
      phone: 'Téléphone',
      wilaya: 'Wilaya',
      address: 'Adresse',
      items: 'Articles',
      total: 'Total',
      actions: 'Actions',
      viewDetails: 'Détails',
      printInvoice: 'Facture',
      refresh: 'Actualiser',
      export: 'Exporter',
      noOrders: 'Aucune commande',
      orderDetails: 'Détails',
      customerInfo: 'Client',
      orderItems: 'Articles',
      product: 'Produit',
      quantity: 'Qté',
      price: 'Prix',
      subtotal: 'Sous-total',
      notes: 'Notes',
      close: 'Fermer',
      confirmOrder: 'Confirmer',
      prepareOrder: 'Préparer',
      shipOrder: 'Expédier',
      markDelivered: 'Livré',
      cancelOrder: 'Annuler',
      statusUpdated: 'Statut mis à jour',
      invoice: 'Facture',
      paymentMethod: 'Paiement',
      cod: 'Paiement à la livraison',
      callCustomer: 'Appeler',
      copyPhone: 'Copier',
      phoneCopied: 'Numéro copié',
      confirmOrderTitle: 'Confirmer la commande',
      confirmOrderDesc: 'Confirmer cette commande après avoir appelé le client?',
      yes: 'Oui',
      no: 'Non',
      orderConfirmed: 'Commande confirmée',
      orderPreparing: 'Commande en préparation',
      orderShipped: 'Commande expédiée',
      orderDelivered: 'Commande livrée',
      orderCancelled: 'Commande annulée',
      pendingOrders: 'Nouvelles',
      confirmedOrders: 'Confirmées',
      shippedOrders: 'Expédiées',
      workflow: 'Processus',
      step1: '1. Appeler',
      step2: '2. Confirmer',
      step3: '3. Préparer',
      step4: '4. Expédier',
      shippingCompany: 'Transporteur'
    },
    en: {
      orders: 'Orders',
      search: 'Search by order ID, customer or phone...',
      status: 'Status',
      allStatus: 'All',
      pending: 'Pending',
      confirmed: 'Confirmed',
      processing: 'Processing',
      shipped: 'Shipped',
      delivered: 'Delivered',
      cancelled: 'Cancelled',
      date: 'Date',
      allDates: 'All',
      today: 'Today',
      week: 'This Week',
      month: 'This Month',
      orderId: 'Order ID',
      customer: 'Customer',
      phone: 'Phone',
      wilaya: 'Wilaya',
      address: 'Address',
      items: 'Items',
      total: 'Total',
      actions: 'Actions',
      viewDetails: 'Details',
      printInvoice: 'Invoice',
      refresh: 'Refresh',
      export: 'Export',
      noOrders: 'No orders found',
      orderDetails: 'Order Details',
      customerInfo: 'Customer Info',
      orderItems: 'Items',
      product: 'Product',
      quantity: 'Qty',
      price: 'Price',
      subtotal: 'Subtotal',
      notes: 'Notes',
      close: 'Close',
      confirmOrder: 'Confirm',
      prepareOrder: 'Prepare',
      shipOrder: 'Ship',
      markDelivered: 'Delivered',
      cancelOrder: 'Cancel',
      statusUpdated: 'Status updated',
      invoice: 'Invoice',
      paymentMethod: 'Payment',
      cod: 'Cash on Delivery',
      callCustomer: 'Call',
      copyPhone: 'Copy',
      phoneCopied: 'Phone copied',
      confirmOrderTitle: 'Confirm Order',
      confirmOrderDesc: 'Confirm this order after calling the customer?',
      yes: 'Yes',
      no: 'No',
      orderConfirmed: 'Order confirmed',
      orderPreparing: 'Order is being prepared',
      orderShipped: 'Order shipped',
      orderDelivered: 'Order delivered',
      orderCancelled: 'Order cancelled',
      pendingOrders: 'New Orders',
      confirmedOrders: 'Confirmed',
      shippedOrders: 'Shipped',
      workflow: 'Workflow',
      step1: '1. Call',
      step2: '2. Confirm',
      step3: '3. Prepare',
      step4: '4. Ship',
      shippingCompany: 'Shipping'
    }
  };

  const text = l[language] || l.ar;

  useEffect(() => {
    fetchOrders();
  }, []);

  const fetchOrders = async () => {
    try {
      setLoading(true);
      const res = await axios.get(`${API}/admin/orders`, { withCredentials: true });
      setOrders(res.data || []);
    } catch (error) {
      console.error('Error fetching orders:', error);
      toast.error('Error loading orders');
    } finally {
      setLoading(false);
    }
  };

  const filteredOrders = useMemo(() => {
    return orders.filter(order => {
      const searchLower = search.toLowerCase();
      const searchMatch = !search ||
        order.order_id?.toLowerCase().includes(searchLower) ||
        order.customer_name?.toLowerCase().includes(searchLower) ||
        order.phone?.includes(search) ||
        order.wilaya?.toLowerCase().includes(searchLower);

      const statusMatch = statusFilter === 'all' || order.status === statusFilter;

      let dateMatch = true;
      if (dateFilter !== 'all') {
        const orderDate = new Date(order.created_at);
        const now = new Date();
        if (dateFilter === 'today') {
          dateMatch = orderDate.toDateString() === now.toDateString();
        } else if (dateFilter === 'week') {
          const weekAgo = new Date(now.setDate(now.getDate() - 7));
          dateMatch = orderDate >= weekAgo;
        } else if (dateFilter === 'month') {
          dateMatch = orderDate.getMonth() === now.getMonth() && orderDate.getFullYear() === now.getFullYear();
        }
      }

      return searchMatch && statusMatch && dateMatch;
    }).sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  }, [orders, search, statusFilter, dateFilter]);

  const updateOrderStatus = async (orderId, newStatus) => {
    try {
      setUpdatingOrder(orderId);
      await axios.put(
        `${API}/admin/orders/${orderId}/status`,
        { status: newStatus },
        { withCredentials: true }
      );
      setOrders(prev => prev.map(o => 
        o.order_id === orderId ? { ...o, status: newStatus } : o
      ));
      
      // Show appropriate success message
      const messages = {
        confirmed: text.orderConfirmed,
        processing: text.orderPreparing,
        shipped: text.orderShipped,
        delivered: text.orderDelivered,
        cancelled: text.orderCancelled
      };
      toast.success(messages[newStatus] || text.statusUpdated);
    } catch (error) {
      console.error('Error updating status:', error);
      toast.error('Error updating status');
    } finally {
      setUpdatingOrder(null);
    }
  };

  const copyPhone = (phone) => {
    navigator.clipboard.writeText(phone);
    toast.success(text.phoneCopied);
  };

  const callCustomer = (phone) => {
    window.location.href = `tel:${phone}`;
  };

  const generateInvoice = (order) => {
    try {
      const doc = new jsPDF();
      const priceStr = (price) => `${(price || 0).toLocaleString()} DZD`;
      
      doc.setFontSize(20);
      doc.setTextColor(34, 84, 61);
      doc.text('AgroYousfi', 105, 20, { align: 'center' });
      
      doc.setFontSize(12);
      doc.setTextColor(100);
      doc.text(`${text.invoice} #${(order.order_id || '').slice(-8).toUpperCase()}`, 105, 30, { align: 'center' });
      
      doc.setFontSize(10);
      const orderDate = order.created_at ? format(new Date(order.created_at), 'dd/MM/yyyy HH:mm') : '-';
      doc.text(orderDate, 105, 38, { align: 'center' });
      
      doc.setFontSize(11);
      doc.setTextColor(0);
      doc.text('Customer Information', 15, 55);
      doc.setFontSize(10);
      doc.setTextColor(60);
      doc.text(`Name: ${order.customer_name || '-'}`, 15, 65);
      doc.text(`Phone: ${order.phone || '-'}`, 15, 72);
      doc.text(`Wilaya: ${order.wilaya || '-'}`, 15, 79);
      doc.text(`Address: ${order.address || '-'}`, 15, 86);
      
      const items = order.items || [];
      const tableData = items.map(item => [
        item.name || 'Product',
        String(item.quantity || 1),
        priceStr(item.price),
        priceStr((item.price || 0) * (item.quantity || 1))
      ]);
      
      if (tableData.length === 0) {
        tableData.push(['No items', '0', '0 DZD', '0 DZD']);
      }
      
      doc.autoTable({
        startY: 100,
        head: [['Product', 'Qty', 'Price', 'Subtotal']],
        body: tableData,
        theme: 'striped',
        headStyles: { fillColor: [34, 84, 61] },
        foot: [['Total', '', '', priceStr(order.total)]],
        footStyles: { fillColor: [240, 240, 240], textColor: [0, 0, 0], fontStyle: 'bold' }
      });
      
      const finalY = (doc.lastAutoTable?.finalY || 150) + 15;
      doc.setFontSize(10);
      doc.text('Payment: Cash on Delivery', 15, finalY);
      doc.text(`Status: ${order.status || 'pending'}`, 15, finalY + 10);
      
      doc.setFontSize(8);
      doc.setTextColor(150);
      doc.text('AgroYousfi - agroyousfi.dz', 105, 280, { align: 'center' });
      
      doc.save(`invoice-${(order.order_id || 'order').slice(-8)}.pdf`);
      toast.success(language === 'ar' ? 'تم تحميل الفاتورة' : 'Invoice downloaded');
    } catch (error) {
      console.error('Error generating invoice:', error);
      toast.error(language === 'ar' ? 'خطأ في إنشاء الفاتورة' : 'Error generating invoice');
    }
  };

  const getStatusConfig = (status) => {
    const configs = {
      pending: { 
        color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border-yellow-300',
        icon: Clock,
        nextAction: 'confirm'
      },
      confirmed: { 
        color: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 border-blue-300',
        icon: CheckCircle,
        nextAction: 'process'
      },
      processing: { 
        color: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400 border-orange-300',
        icon: PackageCheck,
        nextAction: 'ship'
      },
      shipped: { 
        color: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 border-purple-300',
        icon: Truck,
        nextAction: 'deliver'
      },
      delivered: { 
        color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border-green-300',
        icon: CheckCircle,
        nextAction: null
      },
      cancelled: { 
        color: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border-red-300',
        icon: XCircle,
        nextAction: null
      }
    };
    return configs[status] || configs.pending;
  };

  const getNextActionButton = (order) => {
    const config = getStatusConfig(order.status);
    const isUpdating = updatingOrder === order.order_id;
    
    switch (config.nextAction) {
      case 'confirm':
        return (
          <Button
            size="sm"
            className="bg-blue-600 hover:bg-blue-700"
            onClick={() => updateOrderStatus(order.order_id, 'confirmed')}
            disabled={isUpdating}
          >
            {isUpdating ? <Loader2 className="h-4 w-4 animate-spin" /> : <CheckCircle className="h-4 w-4 me-1" />}
            {text.confirmOrder}
          </Button>
        );
      case 'process':
        return (
          <Button
            size="sm"
            className="bg-orange-600 hover:bg-orange-700"
            onClick={() => updateOrderStatus(order.order_id, 'processing')}
            disabled={isUpdating}
          >
            {isUpdating ? <Loader2 className="h-4 w-4 animate-spin" /> : <PackageCheck className="h-4 w-4 me-1" />}
            {text.prepareOrder}
          </Button>
        );
      case 'ship':
        return (
          <Button
            size="sm"
            className="bg-purple-600 hover:bg-purple-700"
            onClick={() => updateOrderStatus(order.order_id, 'shipped')}
            disabled={isUpdating}
          >
            {isUpdating ? <Loader2 className="h-4 w-4 animate-spin" /> : <Truck className="h-4 w-4 me-1" />}
            {text.shipOrder}
          </Button>
        );
      case 'deliver':
        return (
          <Button
            size="sm"
            className="bg-green-600 hover:bg-green-700"
            onClick={() => updateOrderStatus(order.order_id, 'delivered')}
            disabled={isUpdating}
          >
            {isUpdating ? <Loader2 className="h-4 w-4 animate-spin" /> : <CheckCircle className="h-4 w-4 me-1" />}
            {text.markDelivered}
          </Button>
        );
      default:
        return null;
    }
  };

  const statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

  // Count orders by status
  const statusCounts = useMemo(() => {
    const counts = {};
    statuses.forEach(s => counts[s] = 0);
    orders.forEach(o => {
      if (counts[o.status] !== undefined) counts[o.status]++;
    });
    return counts;
  }, [orders]);

  const toggleExpanded = (orderId) => {
    setExpandedOrders(prev => 
      prev.includes(orderId) 
        ? prev.filter(id => id !== orderId)
        : [...prev, orderId]
    );
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold">{text.orders}</h1>
          <p className="text-muted-foreground">{filteredOrders.length} {text.orders}</p>
        </div>
        <div className="flex items-center gap-2">
          <Button variant="outline" size="sm" onClick={fetchOrders}>
            <RefreshCw className="h-4 w-4 me-2" />
            {text.refresh}
          </Button>
        </div>
      </div>

      {/* Status Summary Cards */}
      <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
        {statuses.map(status => {
          const config = getStatusConfig(status);
          const StatusIcon = config.icon;
          const isActive = statusFilter === status;
          return (
            <button
              key={status}
              onClick={() => setStatusFilter(isActive ? 'all' : status)}
              className={`p-3 rounded-xl border-2 transition-all ${
                isActive 
                  ? config.color + ' border-current' 
                  : 'bg-card hover:bg-muted/50 border-transparent'
              }`}
            >
              <div className="flex items-center justify-between">
                <StatusIcon className="h-5 w-5" />
                <span className="text-xl font-bold">{statusCounts[status]}</span>
              </div>
              <p className="text-xs mt-1 text-start">{text[status]}</p>
            </button>
          );
        })}
      </div>

      {/* Filters */}
      <Card>
        <CardContent className="p-4">
          <div className="flex flex-col lg:flex-row gap-4">
            <div className="relative flex-1">
              <Search className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'right-3' : 'left-3'} h-4 w-4 text-muted-foreground`} />
              <Input
                placeholder={text.search}
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className={isRTL ? 'pr-10' : 'pl-10'}
              />
            </div>
            <Select value={dateFilter} onValueChange={setDateFilter}>
              <SelectTrigger className="w-full lg:w-40">
                <Calendar className="h-4 w-4 me-2" />
                <SelectValue placeholder={text.date} />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">{text.allDates}</SelectItem>
                <SelectItem value="today">{text.today}</SelectItem>
                <SelectItem value="week">{text.week}</SelectItem>
                <SelectItem value="month">{text.month}</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </CardContent>
      </Card>

      {/* Orders List */}
      {loading ? (
        <div className="flex items-center justify-center h-64">
          <Loader2 className="h-8 w-8 animate-spin text-primary" />
        </div>
      ) : filteredOrders.length === 0 ? (
        <Card>
          <CardContent className="flex flex-col items-center justify-center h-64 text-muted-foreground">
            <Package className="h-12 w-12 mb-4 opacity-50" />
            <p>{text.noOrders}</p>
          </CardContent>
        </Card>
      ) : (
        <div className="space-y-4">
          {filteredOrders.map(order => {
            const config = getStatusConfig(order.status);
            const StatusIcon = config.icon;
            const isExpanded = expandedOrders.includes(order.order_id);
            
            return (
              <Card key={order.order_id} className={`overflow-hidden border-s-4 ${config.color.split(' ')[0].replace('bg-', 'border-').replace('/30', '')}`}>
                <CardContent className="p-0">
                  {/* Main Order Row */}
                  <div className="p-4">
                    <div className="flex flex-col lg:flex-row lg:items-center gap-4">
                      {/* Order ID & Status */}
                      <div className="flex items-center justify-between lg:w-48">
                        <div>
                          <p className="font-bold text-primary">#{order.order_id.slice(-6)}</p>
                          <p className="text-xs text-muted-foreground">
                            {format(new Date(order.created_at), 'dd/MM HH:mm', { locale })}
                          </p>
                        </div>
                        <Badge className={`${config.color} border`}>
                          <StatusIcon className="h-3 w-3 me-1" />
                          {text[order.status]}
                        </Badge>
                      </div>

                      {/* Customer Info */}
                      <div className="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                        {/* Name & Phone */}
                        <div className="flex items-start gap-2">
                          <User className="h-4 w-4 text-muted-foreground mt-0.5" />
                          <div>
                            <p className="font-medium">{order.customer_name}</p>
                            <div className="flex items-center gap-2 mt-1">
                              <a 
                                href={`tel:${order.phone}`}
                                className="text-sm text-primary hover:underline flex items-center gap-1"
                              >
                                <Phone className="h-3 w-3" />
                                {order.phone}
                              </a>
                              <button 
                                onClick={() => copyPhone(order.phone)}
                                className="text-muted-foreground hover:text-foreground"
                              >
                                <Copy className="h-3 w-3" />
                              </button>
                            </div>
                          </div>
                        </div>

                        {/* Wilaya & Address */}
                        <div className="flex items-start gap-2 md:col-span-2">
                          <MapPin className="h-4 w-4 text-muted-foreground mt-0.5" />
                          <div>
                            <p className="font-medium">{order.wilaya}</p>
                            <p className="text-sm text-muted-foreground">{order.address}</p>
                          </div>
                        </div>
                      </div>

                      {/* Total & Actions */}
                      <div className="flex items-center justify-between lg:justify-end gap-3 lg:w-64">
                        <div className="text-end">
                          <p className="font-bold text-lg">{formatPrice(order.total)}</p>
                          <p className="text-xs text-muted-foreground">
                            {order.items?.length || 0} {text.items}
                          </p>
                        </div>
                        <div className="flex items-center gap-2">
                          {getNextActionButton(order)}
                          <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => toggleExpanded(order.order_id)}
                          >
                            <ChevronRight className={`h-4 w-4 transition-transform ${isExpanded ? 'rotate-90' : ''}`} />
                          </Button>
                        </div>
                      </div>
                    </div>
                  </div>

                  {/* Expanded Details */}
                  {isExpanded && (
                    <div className="border-t bg-muted/30 p-4">
                      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Products List */}
                        <div>
                          <h4 className="font-medium mb-3 flex items-center gap-2">
                            <Package className="h-4 w-4" />
                            {text.orderItems}
                          </h4>
                          <div className="space-y-2">
                            {order.items?.map((item, idx) => (
                              <div key={idx} className="flex items-center justify-between p-2 bg-background rounded-lg">
                                <div className="flex items-center gap-3">
                                  {item.image ? (
                                    <img src={item.image} alt={item.name} className="h-10 w-10 rounded object-cover" />
                                  ) : (
                                    <div className="h-10 w-10 bg-muted rounded flex items-center justify-center">
                                      <Package className="h-5 w-5 text-muted-foreground" />
                                    </div>
                                  )}
                                  <div>
                                    <p className="font-medium text-sm">{item.name}</p>
                                    <p className="text-xs text-muted-foreground">
                                      {formatPrice(item.price)} × {item.quantity}
                                    </p>
                                  </div>
                                </div>
                                <p className="font-semibold">{formatPrice(item.price * item.quantity)}</p>
                              </div>
                            ))}
                          </div>
                        </div>

                        {/* Quick Actions */}
                        <div>
                          <h4 className="font-medium mb-3">{text.actions}</h4>
                          <div className="grid grid-cols-2 gap-2">
                            <Button
                              variant="outline"
                              size="sm"
                              onClick={() => callCustomer(order.phone)}
                              className="justify-start"
                            >
                              <Phone className="h-4 w-4 me-2" />
                              {text.callCustomer}
                            </Button>
                            <Button
                              variant="outline"
                              size="sm"
                              onClick={() => generateInvoice(order)}
                              className="justify-start"
                            >
                              <FileText className="h-4 w-4 me-2" />
                              {text.printInvoice}
                            </Button>
                            {order.status !== 'cancelled' && order.status !== 'delivered' && (
                              <Button
                                variant="outline"
                                size="sm"
                                onClick={() => updateOrderStatus(order.order_id, 'cancelled')}
                                className="justify-start text-red-600 hover:text-red-700 hover:bg-red-50"
                                disabled={updatingOrder === order.order_id}
                              >
                                <XCircle className="h-4 w-4 me-2" />
                                {text.cancelOrder}
                              </Button>
                            )}
                          </div>

                          {/* Notes */}
                          {order.notes && (
                            <div className="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                              <p className="text-xs font-medium text-yellow-800 dark:text-yellow-400 mb-1">
                                {text.notes}
                              </p>
                              <p className="text-sm">{order.notes}</p>
                            </div>
                          )}
                        </div>
                      </div>
                    </div>
                  )}
                </CardContent>
              </Card>
            );
          })}
        </div>
      )}
    </div>
  );
};

export default OrdersPage;
