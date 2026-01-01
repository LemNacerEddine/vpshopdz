import React, { useState, useEffect, useMemo } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
  Search,
  Filter,
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
  Calendar
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
  const [statusDialog, setStatusDialog] = useState({ open: false, order: null });
  const [newStatus, setNewStatus] = useState('');

  const locale = language === 'ar' ? ar : language === 'fr' ? fr : enUS;

  const l = {
    ar: {
      orders: 'الطلبات',
      allOrders: 'جميع الطلبات',
      search: 'بحث برقم الطلب أو اسم العميل...',
      status: 'الحالة',
      allStatus: 'جميع الحالات',
      pending: 'قيد الانتظار',
      confirmed: 'مؤكد',
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
      address: 'العنوان',
      items: 'المنتجات',
      total: 'الإجمالي',
      actions: 'الإجراءات',
      viewDetails: 'عرض التفاصيل',
      updateStatus: 'تحديث الحالة',
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
      save: 'حفظ',
      cancel: 'إلغاء',
      statusUpdated: 'تم تحديث حالة الطلب',
      invoice: 'فاتورة',
      wilaya: 'الولاية',
      paymentMethod: 'طريقة الدفع',
      cod: 'الدفع عند الاستلام'
    },
    fr: {
      orders: 'Commandes',
      allOrders: 'Toutes les commandes',
      search: 'Rechercher par numéro ou client...',
      status: 'Statut',
      allStatus: 'Tous les statuts',
      pending: 'En attente',
      confirmed: 'Confirmée',
      shipped: 'Expédiée',
      delivered: 'Livrée',
      cancelled: 'Annulée',
      date: 'Date',
      allDates: 'Toutes les dates',
      today: "Aujourd'hui",
      week: 'Cette semaine',
      month: 'Ce mois',
      orderId: 'N° Commande',
      customer: 'Client',
      phone: 'Téléphone',
      address: 'Adresse',
      items: 'Articles',
      total: 'Total',
      actions: 'Actions',
      viewDetails: 'Voir les détails',
      updateStatus: 'Mettre à jour',
      printInvoice: 'Imprimer la facture',
      refresh: 'Actualiser',
      export: 'Exporter',
      noOrders: 'Aucune commande',
      orderDetails: 'Détails de la commande',
      customerInfo: 'Informations client',
      orderItems: 'Articles commandés',
      product: 'Produit',
      quantity: 'Quantité',
      price: 'Prix',
      subtotal: 'Sous-total',
      notes: 'Notes',
      close: 'Fermer',
      save: 'Enregistrer',
      cancel: 'Annuler',
      statusUpdated: 'Statut mis à jour',
      invoice: 'Facture',
      wilaya: 'Wilaya',
      paymentMethod: 'Mode de paiement',
      cod: 'Paiement à la livraison'
    },
    en: {
      orders: 'Orders',
      allOrders: 'All Orders',
      search: 'Search by order ID or customer...',
      status: 'Status',
      allStatus: 'All Status',
      pending: 'Pending',
      confirmed: 'Confirmed',
      shipped: 'Shipped',
      delivered: 'Delivered',
      cancelled: 'Cancelled',
      date: 'Date',
      allDates: 'All Dates',
      today: 'Today',
      week: 'This Week',
      month: 'This Month',
      orderId: 'Order ID',
      customer: 'Customer',
      phone: 'Phone',
      address: 'Address',
      items: 'Items',
      total: 'Total',
      actions: 'Actions',
      viewDetails: 'View Details',
      updateStatus: 'Update Status',
      printInvoice: 'Print Invoice',
      refresh: 'Refresh',
      export: 'Export',
      noOrders: 'No orders found',
      orderDetails: 'Order Details',
      customerInfo: 'Customer Information',
      orderItems: 'Order Items',
      product: 'Product',
      quantity: 'Quantity',
      price: 'Price',
      subtotal: 'Subtotal',
      notes: 'Notes',
      close: 'Close',
      save: 'Save',
      cancel: 'Cancel',
      statusUpdated: 'Order status updated',
      invoice: 'Invoice',
      wilaya: 'Wilaya',
      paymentMethod: 'Payment Method',
      cod: 'Cash on Delivery'
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
      // Search filter
      const searchMatch = !search ||
        order.order_id?.toLowerCase().includes(search.toLowerCase()) ||
        order.customer_name?.toLowerCase().includes(search.toLowerCase()) ||
        order.phone?.includes(search);

      // Status filter
      const statusMatch = statusFilter === 'all' || order.status === statusFilter;

      // Date filter
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
    });
  }, [orders, search, statusFilter, dateFilter]);

  const updateOrderStatus = async () => {
    if (!statusDialog.order || !newStatus) return;

    try {
      await axios.put(
        `${API}/admin/orders/${statusDialog.order.order_id}/status`,
        { status: newStatus },
        { withCredentials: true }
      );
      setOrders(prev => prev.map(o => 
        o.order_id === statusDialog.order.order_id ? { ...o, status: newStatus } : o
      ));
      toast.success(text.statusUpdated);
    } catch (error) {
      console.error('Error updating status:', error);
      toast.error('Error updating status');
    } finally {
      setStatusDialog({ open: false, order: null });
      setNewStatus('');
    }
  };

  const generateInvoice = (order) => {
    const doc = new jsPDF();
    
    // Header
    doc.setFontSize(20);
    doc.setTextColor(34, 84, 61);
    doc.text('AgroYousfi', 105, 20, { align: 'center' });
    
    doc.setFontSize(12);
    doc.setTextColor(100);
    doc.text(text.invoice + ' #' + order.order_id.slice(-8).toUpperCase(), 105, 30, { align: 'center' });
    
    // Date
    doc.setFontSize(10);
    doc.text(format(new Date(order.created_at), 'dd/MM/yyyy HH:mm'), 105, 38, { align: 'center' });
    
    // Customer Info
    doc.setFontSize(11);
    doc.setTextColor(0);
    doc.text(text.customerInfo, 15, 55);
    doc.setFontSize(10);
    doc.setTextColor(60);
    doc.text(`${text.customer}: ${order.customer_name}`, 15, 65);
    doc.text(`${text.phone}: ${order.phone}`, 15, 72);
    doc.text(`${text.address}: ${order.address}`, 15, 79);
    doc.text(`${text.wilaya}: ${order.wilaya}`, 15, 86);
    
    // Items Table
    const tableData = order.items.map(item => [
      item.name,
      item.quantity,
      formatPrice(item.price),
      formatPrice(item.price * item.quantity)
    ]);
    
    doc.autoTable({
      startY: 100,
      head: [[text.product, text.quantity, text.price, text.subtotal]],
      body: tableData,
      theme: 'striped',
      headStyles: { fillColor: [34, 84, 61] },
      foot: [[text.total, '', '', formatPrice(order.total)]],
      footStyles: { fillColor: [240, 240, 240], textColor: [0, 0, 0], fontStyle: 'bold' }
    });
    
    // Payment Method
    const finalY = doc.lastAutoTable.finalY + 15;
    doc.setFontSize(10);
    doc.text(`${text.paymentMethod}: ${text.cod}`, 15, finalY);
    
    // Footer
    doc.setFontSize(8);
    doc.setTextColor(150);
    doc.text('AgroYousfi - agroyousfi.dz', 105, 280, { align: 'center' });
    
    doc.save(`invoice-${order.order_id.slice(-8)}.pdf`);
  };

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

  const statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

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
          <Button variant="outline" size="sm">
            <Download className="h-4 w-4 me-2" />
            {text.export}
          </Button>
        </div>
      </div>

      {/* Filters */}
      <Card>
        <CardContent className="p-4">
          <div className="flex flex-col lg:flex-row gap-4">
            {/* Search */}
            <div className="relative flex-1">
              <Search className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'right-3' : 'left-3'} h-4 w-4 text-muted-foreground`} />
              <Input
                placeholder={text.search}
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className={isRTL ? 'pr-10' : 'pl-10'}
              />
            </div>

            {/* Status Filter */}
            <Select value={statusFilter} onValueChange={setStatusFilter}>
              <SelectTrigger className="w-full lg:w-40">
                <SelectValue placeholder={text.status} />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">{text.allStatus}</SelectItem>
                {statuses.map(s => (
                  <SelectItem key={s} value={s}>{text[s]}</SelectItem>
                ))}
              </SelectContent>
            </Select>

            {/* Date Filter */}
            <Select value={dateFilter} onValueChange={setDateFilter}>
              <SelectTrigger className="w-full lg:w-40">
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

      {/* Orders Table */}
      <Card>
        <CardContent className="p-0">
          {loading ? (
            <div className="flex items-center justify-center h-64">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary" />
            </div>
          ) : filteredOrders.length === 0 ? (
            <div className="flex flex-col items-center justify-center h-64 text-muted-foreground">
              <Package className="h-12 w-12 mb-4 opacity-50" />
              <p>{text.noOrders}</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b bg-muted/50">
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.orderId}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.customer}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.items}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.total}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.status}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.date}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.actions}</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredOrders.map(order => {
                    const StatusIcon = getStatusIcon(order.status);
                    return (
                      <tr key={order.order_id} className="border-b hover:bg-muted/30 transition-colors">
                        <td className="py-3 px-4">
                          <span className="font-medium text-primary">#{order.order_id.slice(-6)}</span>
                        </td>
                        <td className="py-3 px-4">
                          <div>
                            <p className="font-medium">{order.customer_name}</p>
                            <p className="text-xs text-muted-foreground flex items-center gap-1">
                              <Phone className="h-3 w-3" />
                              {order.phone}
                            </p>
                          </div>
                        </td>
                        <td className="py-3 px-4">
                          <span className="text-sm">{order.items?.length || 0} {text.items}</span>
                        </td>
                        <td className="py-3 px-4">
                          <span className="font-semibold">{formatPrice(order.total)}</span>
                        </td>
                        <td className="py-3 px-4">
                          <span className={`inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(order.status)}`}>
                            <StatusIcon className="h-3 w-3" />
                            {text[order.status]}
                          </span>
                        </td>
                        <td className="py-3 px-4">
                          <div className="text-sm">
                            <p>{format(new Date(order.created_at), 'dd/MM/yyyy', { locale })}</p>
                            <p className="text-xs text-muted-foreground">
                              {format(new Date(order.created_at), 'HH:mm', { locale })}
                            </p>
                          </div>
                        </td>
                        <td className="py-3 px-4">
                          <div className="flex items-center gap-1">
                            <Button
                              variant="ghost"
                              size="icon"
                              className="h-8 w-8"
                              onClick={() => setSelectedOrder(order)}
                            >
                              <Eye className="h-4 w-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="icon"
                              className="h-8 w-8"
                              onClick={() => {
                                setStatusDialog({ open: true, order });
                                setNewStatus(order.status);
                              }}
                            >
                              <CheckCircle className="h-4 w-4" />
                            </Button>
                            <Button
                              variant="ghost"
                              size="icon"
                              className="h-8 w-8"
                              onClick={() => generateInvoice(order)}
                            >
                              <FileText className="h-4 w-4" />
                            </Button>
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

      {/* Order Details Dialog */}
      <Dialog open={!!selectedOrder} onOpenChange={() => setSelectedOrder(null)}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>{text.orderDetails}</DialogTitle>
            <DialogDescription>
              #{selectedOrder?.order_id.slice(-8).toUpperCase()}
            </DialogDescription>
          </DialogHeader>
          
          {selectedOrder && (
            <div className="space-y-6">
              {/* Customer Info */}
              <div className="p-4 bg-muted/50 rounded-lg space-y-2">
                <h3 className="font-medium mb-3">{text.customerInfo}</h3>
                <div className="grid grid-cols-2 gap-4 text-sm">
                  <div className="flex items-center gap-2">
                    <Package className="h-4 w-4 text-muted-foreground" />
                    <span>{selectedOrder.customer_name}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Phone className="h-4 w-4 text-muted-foreground" />
                    <span>{selectedOrder.phone}</span>
                  </div>
                  <div className="flex items-center gap-2 col-span-2">
                    <MapPin className="h-4 w-4 text-muted-foreground" />
                    <span>{selectedOrder.address}, {selectedOrder.wilaya}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Calendar className="h-4 w-4 text-muted-foreground" />
                    <span>{format(new Date(selectedOrder.created_at), 'dd/MM/yyyy HH:mm', { locale })}</span>
                  </div>
                </div>
              </div>

              {/* Order Items */}
              <div>
                <h3 className="font-medium mb-3">{text.orderItems}</h3>
                <div className="border rounded-lg divide-y">
                  {selectedOrder.items?.map((item, idx) => (
                    <div key={idx} className="flex items-center justify-between p-3">
                      <div className="flex items-center gap-3">
                        <div className="h-12 w-12 bg-muted rounded-lg flex items-center justify-center">
                          <Package className="h-6 w-6 text-muted-foreground" />
                        </div>
                        <div>
                          <p className="font-medium">{item.name}</p>
                          <p className="text-sm text-muted-foreground">
                            {formatPrice(item.price)} x {item.quantity}
                          </p>
                        </div>
                      </div>
                      <p className="font-semibold">{formatPrice(item.price * item.quantity)}</p>
                    </div>
                  ))}
                  <div className="flex items-center justify-between p-3 bg-muted/50 font-semibold">
                    <span>{text.total}</span>
                    <span className="text-lg">{formatPrice(selectedOrder.total)}</span>
                  </div>
                </div>
              </div>

              {/* Notes */}
              {selectedOrder.notes && (
                <div className="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                  <h3 className="font-medium mb-2">{text.notes}</h3>
                  <p className="text-sm">{selectedOrder.notes}</p>
                </div>
              )}
            </div>
          )}

          <DialogFooter>
            <Button variant="outline" onClick={() => setSelectedOrder(null)}>
              {text.close}
            </Button>
            <Button onClick={() => selectedOrder && generateInvoice(selectedOrder)}>
              <FileText className="h-4 w-4 me-2" />
              {text.printInvoice}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Status Update Dialog */}
      <Dialog open={statusDialog.open} onOpenChange={() => setStatusDialog({ open: false, order: null })}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{text.updateStatus}</DialogTitle>
            <DialogDescription>
              {statusDialog.order && `#${statusDialog.order.order_id.slice(-8).toUpperCase()}`}
            </DialogDescription>
          </DialogHeader>
          
          <div className="py-4">
            <Select value={newStatus} onValueChange={setNewStatus}>
              <SelectTrigger>
                <SelectValue placeholder={text.status} />
              </SelectTrigger>
              <SelectContent>
                {statuses.map(s => (
                  <SelectItem key={s} value={s}>
                    <div className="flex items-center gap-2">
                      {React.createElement(getStatusIcon(s), { className: 'h-4 w-4' })}
                      {text[s]}
                    </div>
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <DialogFooter>
            <Button variant="outline" onClick={() => setStatusDialog({ open: false, order: null })}>
              {text.cancel}
            </Button>
            <Button onClick={updateOrderStatus}>
              {text.save}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default OrdersPage;
