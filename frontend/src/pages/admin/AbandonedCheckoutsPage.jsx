import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import WhatsAppPhonePreview from '@/components/admin/WhatsAppPhonePreview';
import {
  Search,
  RefreshCw,
  Phone,
  MapPin,
  Clock,
  ShoppingCart,
  MessageCircle,
  CheckCircle,
  Trash2,
  TrendingUp,
  RotateCcw,
  XCircle,
  AlertTriangle,
  Send,
  Ban,
  ChevronDown,
  ChevronUp,
  Package,
} from 'lucide-react';
import { format } from 'date-fns';
import { ar, fr, enUS } from 'date-fns/locale';
import { toast } from 'sonner';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const AbandonedCheckoutsPage = () => {
  const { language, isRTL, formatPrice } = useLanguage();
  const [checkouts, setCheckouts] = useState([]);
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [filter, setFilter] = useState('all');
  const [selectedCheckout, setSelectedCheckout] = useState(null);
  const [expandedId, setExpandedId] = useState(null);
  const [whatsappMessage, setWhatsappMessage] = useState('');
  const [storeUrl, setStoreUrl] = useState('');
  const [offerSettings, setOfferSettings] = useState(null);

  const locale = language === 'ar' ? ar : language === 'fr' ? fr : enUS;

  const l = {
    ar: {
      title: 'الطلبات المتروكة',
      subtitle: 'استرداد العملاء الذين لم يكملوا طلباتهم عبر واتساب',
      search: 'البحث بالاسم أو رقم الهاتف...',
      noResults: 'لا توجد طلبات متروكة',
      totalAbandoned: 'إجمالي المتروكة',
      notSent: 'لم يُرسل',
      sent: 'تم الإرسال',
      recovered: 'مُسترد',
      failed: 'فشل',
      sendWhatsApp: 'إرسال واتساب',
      markRecovered: 'تم الاسترداد',
      delete: 'حذف',
      retry: 'إعادة المحاولة',
      skip: 'تخطي',
      customer: 'العميل',
      phone: 'الهاتف',
      products: 'المنتجات',
      items: 'منتج',
      filterAll: 'الكل',
      filterNotSent: 'لم يُرسل',
      filterSent: 'تم الإرسال',
      filterRecovered: 'مُسترد',
      filterFailed: 'فشل',
      refresh: 'تحديث',
      attempts: 'المحاولات',
      lastError: 'آخر خطأ',
      nextRetry: 'المحاولة التالية',
      retryConfirm: 'تم إعادة تعيين حالة الإرسال',
      skipConfirm: 'تم تخطي هذا الطلب',
      anonymous: 'اسم العميل غير متاح',
      previewTitle: 'معاينة الرسالة',
      noPreview: 'اختر طلباً لمعاينة الرسالة',
      recoveryRate: 'نسبة الاسترداد',
    },
    fr: {
      title: 'Paniers abandonnés',
      subtitle: 'Récupérez les clients via WhatsApp automatiquement',
      search: 'Rechercher par nom ou téléphone...',
      noResults: 'Aucun panier abandonné',
      totalAbandoned: 'Total abandonnés',
      notSent: 'Non envoyé',
      sent: 'Envoyé',
      recovered: 'Récupéré',
      failed: 'Échoué',
      sendWhatsApp: 'Envoyer WhatsApp',
      markRecovered: 'Marquer récupéré',
      delete: 'Supprimer',
      retry: 'Réessayer',
      skip: 'Ignorer',
      customer: 'Client',
      phone: 'Téléphone',
      products: 'Produits',
      items: 'articles',
      filterAll: 'Tous',
      filterNotSent: 'Non envoyé',
      filterSent: 'Envoyé',
      filterRecovered: 'Récupéré',
      filterFailed: 'Échoué',
      refresh: 'Actualiser',
      attempts: 'Tentatives',
      lastError: 'Dernière erreur',
      nextRetry: 'Prochaine tentative',
      retryConfirm: 'Statut d\'envoi réinitialisé',
      skipConfirm: 'Ce panier a été ignoré',
      anonymous: 'Nom du client non disponible',
      previewTitle: 'Aperçu du message',
      noPreview: 'Sélectionnez un panier pour prévisualiser',
      recoveryRate: 'Taux de récupération',
    },
    en: {
      title: 'Abandoned Checkouts',
      subtitle: 'Recover customers automatically via WhatsApp',
      search: 'Search by name or phone...',
      noResults: 'No abandoned checkouts',
      totalAbandoned: 'Total Abandoned',
      notSent: 'Not Sent',
      sent: 'Sent',
      recovered: 'Recovered',
      failed: 'Failed',
      sendWhatsApp: 'Send WhatsApp',
      markRecovered: 'Mark Recovered',
      delete: 'Delete',
      retry: 'Retry',
      skip: 'Skip',
      customer: 'Customer',
      phone: 'Phone',
      products: 'Products',
      items: 'items',
      filterAll: 'All',
      filterNotSent: 'Not Sent',
      filterSent: 'Sent',
      filterRecovered: 'Recovered',
      filterFailed: 'Failed',
      refresh: 'Refresh',
      attempts: 'Attempts',
      lastError: 'Last error',
      nextRetry: 'Next retry',
      retryConfirm: 'Send status reset',
      skipConfirm: 'Checkout skipped',
      anonymous: 'Customer name not available',
      previewTitle: 'Message Preview',
      noPreview: 'Select a checkout to preview',
      recoveryRate: 'Recovery Rate',
    }
  };
  const text = l[language] || l.ar;

  useEffect(() => {
    fetchData();
    fetchMessageTemplate();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [filter]);

  const fetchData = async () => {
    setLoading(true);
    try {
      const params = {};
      if (filter === 'pending') params.send_status = 'pending';
      if (filter === 'sent') params.send_status = 'sent';
      if (filter === 'failed') params.send_status = 'failed';
      if (filter === 'recovered') params.recovered = 'true';

      const [checkoutsRes, statsRes] = await Promise.all([
        axios.get(`${API}/admin/abandoned-checkouts`, { params, withCredentials: true }),
        axios.get(`${API}/admin/abandoned-checkouts/stats`, { withCredentials: true }),
      ]);

      setCheckouts(checkoutsRes.data);
      setStats(statsRes.data);
    } catch (error) {
      toast.error(error.response?.data?.detail || 'Error fetching data');
    } finally {
      setLoading(false);
    }
  };

  const fetchMessageTemplate = async () => {
    try {
      const [msgRes, settingsRes] = await Promise.all([
        axios.get(`${API}/admin/settings/whatsapp`, { withCredentials: true }),
        axios.get(`${API}/admin/settings`, { withCredentials: true }),
      ]);
      const key = `whatsapp_message_${language}`;
      setWhatsappMessage(msgRes.data[key] || msgRes.data.whatsapp_message_ar || '');
      if (msgRes.data.store_url) setStoreUrl(msgRes.data.store_url.replace(/\/+$/, ''));
      // Load offer settings for discount calculation
      const s = settingsRes.data || {};
      setOfferSettings({
        discount_enabled: s.offer_discount_enabled === 'true',
        discount_type: s.offer_discount_type || 'percentage',
        discount_value: parseFloat(s.offer_discount_value) || 0,
        free_shipping: s.offer_free_shipping === 'true',
      });
    } catch {
      // ignore
    }
  };

  const getDiscountedTotal = (cartTotal) => {
    if (!offerSettings?.discount_enabled || !offerSettings.discount_value) return cartTotal;
    if (offerSettings.discount_type === 'percentage') {
      return Math.max(0, cartTotal - Math.round(cartTotal * offerSettings.discount_value / 100));
    }
    return Math.max(0, cartTotal - offerSettings.discount_value);
  };

  const getDiscountText = () => {
    if (!offerSettings) return '';
    let text = '';
    if (offerSettings.discount_enabled && offerSettings.discount_value > 0) {
      text = offerSettings.discount_type === 'percentage'
        ? `${offerSettings.discount_value}%`
        : formatPrice(offerSettings.discount_value);
    }
    if (offerSettings.free_shipping) {
      text += (text ? ' + ' : '') + (language === 'ar' ? 'شحن مجاني' : language === 'fr' ? 'Livraison gratuite' : 'Free shipping');
    }
    return text;
  };

  const handleSendWhatsApp = async (checkout) => {
    if (!checkout.customer_phone) return;

    let phone = checkout.customer_phone.replace(/\s+/g, '').replace(/-/g, '');
    if (phone.startsWith('0')) {
      phone = '213' + phone.substring(1);
    } else if (phone.startsWith('+')) {
      phone = phone.substring(1);
    } else if (!phone.startsWith('213')) {
      phone = '213' + phone;
    }

    const name = checkout.customer_name || text.customer;
    const recoveryLink = (storeUrl || window.location.origin) + '/recover/' + checkout.checkout_id;
    const cartTotal = parseFloat(checkout.cart_total) || 0;
    const message = whatsappMessage
      .replace('{name}', name)
      .replace('{link}', recoveryLink)
      .replace('{checkout_id}', checkout.checkout_id)
      .replace('{total}', formatPrice(cartTotal))
      .replace('{new_total}', formatPrice(getDiscountedTotal(cartTotal)))
      .replace('{discount}', getDiscountText())
      .replace('{items_count}', String(checkout.item_count || 0));

    window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');

    try {
      await axios.put(
        `${API}/admin/abandoned-checkouts/${checkout.checkout_id}/notified`,
        {},
        { withCredentials: true }
      );
      fetchData();
    } catch {
      // Silently ignore
    }
  };

  const handleRetry = async (checkoutId) => {
    try {
      await axios.put(
        `${API}/admin/abandoned-checkouts/${checkoutId}/retry`,
        {},
        { withCredentials: true }
      );
      toast.success(text.retryConfirm);
      fetchData();
    } catch {
      toast.error('Error');
    }
  };

  const handleSkip = async (checkoutId) => {
    try {
      await axios.put(
        `${API}/admin/abandoned-checkouts/${checkoutId}/skip`,
        {},
        { withCredentials: true }
      );
      toast.success(text.skipConfirm);
      fetchData();
    } catch {
      toast.error('Error');
    }
  };

  const handleMarkRecovered = async (checkoutId) => {
    try {
      await axios.put(
        `${API}/admin/abandoned-checkouts/${checkoutId}/recovered`,
        {},
        { withCredentials: true }
      );
      toast.success(language === 'ar' ? 'تم التحديث' : 'Updated');
      fetchData();
    } catch {
      toast.error('Error');
    }
  };

  const handleDelete = async (checkoutId) => {
    try {
      await axios.delete(
        `${API}/admin/abandoned-checkouts/${checkoutId}`,
        { withCredentials: true }
      );
      toast.success(language === 'ar' ? 'تم الحذف' : 'Deleted');
      if (selectedCheckout?.checkout_id === checkoutId) {
        setSelectedCheckout(null);
      }
      fetchData();
    } catch {
      toast.error('Error');
    }
  };

  const filteredCheckouts = checkouts.filter(c => {
    if (!search) return true;
    const q = search.toLowerCase();
    return (
      (c.customer_name && c.customer_name.toLowerCase().includes(q)) ||
      (c.customer_phone && c.customer_phone.includes(q))
    );
  });

  const formatDate = (dateStr) => {
    try {
      return format(new Date(dateStr), 'dd MMM yyyy HH:mm', { locale });
    } catch {
      return dateStr;
    }
  };

  const getTimeSince = (dateStr) => {
    try {
      const diff = Date.now() - new Date(dateStr).getTime();
      const minutes = Math.floor(diff / (1000 * 60));
      const hours = Math.floor(minutes / 60);
      const days = Math.floor(hours / 24);

      if (language === 'ar') {
        if (days > 0) return `منذ ${days} يوم`;
        if (hours > 0) return `منذ ${hours} ساعة`;
        if (minutes > 0) return `منذ ${minutes} دقيقة`;
        return 'منذ قليل';
      }
      if (days > 0) return `${days}d ago`;
      if (hours > 0) return `${hours}h ago`;
      if (minutes > 0) return `${minutes}m ago`;
      return 'Just now';
    } catch {
      return '';
    }
  };

  // Status badge — WEBI-style
  const getStatusBadge = (checkout) => {
    const status = checkout.send_status || 'pending';
    const isRecovered = checkout.recovered;

    if (isRecovered) {
      return (
        <Badge className="bg-amber-500 hover:bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded">
          {text.recovered}
        </Badge>
      );
    }

    const configs = {
      pending: { bg: 'bg-red-500 hover:bg-red-500', label: text.notSent },
      processing: { bg: 'bg-blue-500 hover:bg-blue-500', label: text.notSent },
      sent: { bg: 'bg-green-500 hover:bg-green-500', label: text.sent },
      failed: { bg: 'bg-red-600 hover:bg-red-600', label: text.failed },
      skipped: { bg: 'bg-gray-500 hover:bg-gray-500', label: text.skip },
    };

    const config = configs[status] || configs.pending;

    return (
      <Badge className={`${config.bg} text-white text-[10px] font-bold px-2 py-0.5 rounded`}>
        {config.label}
      </Badge>
    );
  };

  // Get first product image from checkout items
  const getFirstImage = (checkout) => {
    const items = checkout.items || [];
    if (items.length > 0 && items[0].image) {
      return items[0].image;
    }
    return null;
  };

  // Build preview message for selected checkout
  const getPreviewMessage = (checkout) => {
    if (!checkout || !whatsappMessage) return '';
    const name = checkout.customer_name || text.customer;
    const link = (storeUrl || window.location.origin) + '/recover/' + checkout.checkout_id;
    const cartTotal = parseFloat(checkout.cart_total) || 0;
    return whatsappMessage
      .replace('{name}', name)
      .replace('{link}', link)
      .replace('{checkout_id}', checkout.checkout_id)
      .replace('{total}', formatPrice(cartTotal))
      .replace('{new_total}', formatPrice(getDiscountedTotal(cartTotal)))
      .replace('{discount}', getDiscountText())
      .replace('{items_count}', String(checkout.item_count || 0));
  };

  // Tab filter counts
  const getFilterCount = (filterKey) => {
    if (!stats) return 0;
    switch (filterKey) {
      case 'all': return Number(stats.total) || 0;
      case 'pending': return Number(stats.auto_pending) || 0;
      case 'sent': return Number(stats.auto_sent) || 0;
      case 'failed': return Number(stats.auto_failed) || 0;
      case 'recovered': return Number(stats.recovered) || 0;
      default: return 0;
    }
  };

  return (
    <div className="space-y-5">
      {/* Header */}
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold">{text.title}</h1>
          <p className="text-muted-foreground text-sm">{text.subtitle}</p>
        </div>
        <Button variant="outline" size="sm" onClick={fetchData} disabled={loading}>
          <RefreshCw className={`h-4 w-4 me-2 ${loading ? 'animate-spin' : ''}`} />
          {text.refresh}
        </Button>
      </div>

      {/* Main Layout: List + Phone Preview */}
      <div className="flex gap-6">
        {/* Left: Checkout List */}
        <div className="flex-1 min-w-0 space-y-4">
          {/* Tab Filters — WEBI style */}
          <div className="flex items-center gap-1 border-b">
            {[
              { key: 'all', label: text.filterAll },
              { key: 'pending', label: text.filterNotSent },
              { key: 'sent', label: text.filterSent },
              { key: 'failed', label: text.filterFailed },
              { key: 'recovered', label: text.filterRecovered },
            ].map(f => (
              <button
                key={f.key}
                onClick={() => setFilter(f.key)}
                className={`px-4 py-2.5 text-sm font-medium transition-colors relative ${
                  filter === f.key
                    ? 'text-primary'
                    : 'text-muted-foreground hover:text-foreground'
                }`}
              >
                {f.label}
                <span className="ms-1.5 text-xs text-muted-foreground">
                  ({getFilterCount(f.key)})
                </span>
                {filter === f.key && (
                  <span className="absolute bottom-0 inset-x-0 h-0.5 bg-primary rounded-t" />
                )}
              </button>
            ))}
          </div>

          {/* Search */}
          <div className="relative">
            <Search className={`absolute top-1/2 -translate-y-1/2 ${isRTL ? 'right-3' : 'left-3'} h-4 w-4 text-muted-foreground`} />
            <Input
              placeholder={text.search}
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className={`${isRTL ? 'pr-10' : 'pl-10'} rounded-lg`}
            />
          </div>

          {/* Stats Summary — compact */}
          {stats && (
            <div className="grid grid-cols-4 gap-3">
              <div className="bg-muted/50 rounded-lg p-3 text-center">
                <p className="text-xl font-bold">{stats.total || 0}</p>
                <p className="text-[10px] text-muted-foreground">{text.totalAbandoned}</p>
              </div>
              <div className="bg-green-50 dark:bg-green-950/20 rounded-lg p-3 text-center">
                <p className="text-xl font-bold text-green-600">{stats.auto_sent || 0}</p>
                <p className="text-[10px] text-muted-foreground">{text.sent}</p>
              </div>
              <div className="bg-red-50 dark:bg-red-950/20 rounded-lg p-3 text-center">
                <p className="text-xl font-bold text-red-600">{Number(stats.auto_pending || 0) + Number(stats.auto_failed || 0)}</p>
                <p className="text-[10px] text-muted-foreground">{text.notSent}</p>
              </div>
              <div className="bg-amber-50 dark:bg-amber-950/20 rounded-lg p-3 text-center">
                <p className="text-xl font-bold text-amber-600">{stats.recovery_rate || 0}%</p>
                <p className="text-[10px] text-muted-foreground">{text.recoveryRate}</p>
              </div>
            </div>
          )}

          {/* Checkout List — WEBI style */}
          {loading ? (
            <div className="flex items-center justify-center py-16">
              <RefreshCw className="h-8 w-8 animate-spin text-muted-foreground" />
            </div>
          ) : filteredCheckouts.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-16">
              <ShoppingCart className="h-16 w-16 text-muted-foreground/20 mb-4" />
              <p className="text-muted-foreground">{text.noResults}</p>
            </div>
          ) : (
            <div className="space-y-0 border rounded-lg overflow-hidden divide-y">
              {filteredCheckouts.map((checkout) => {
                const isSelected = selectedCheckout?.checkout_id === checkout.checkout_id;
                const isExpanded = expandedId === checkout.checkout_id;
                const firstImage = getFirstImage(checkout);

                return (
                  <div key={checkout.checkout_id}>
                    {/* Main Row */}
                    <div
                      className={`flex items-center gap-3 px-4 py-3 cursor-pointer transition-colors hover:bg-muted/50 ${
                        isSelected ? 'bg-primary/5 border-s-2 border-s-primary' : ''
                      }`}
                      onClick={() => {
                        setSelectedCheckout(checkout);
                        setExpandedId(isExpanded ? null : checkout.checkout_id);
                      }}
                    >
                      {/* Product Thumbnail */}
                      <div className="w-10 h-10 rounded-lg bg-muted flex items-center justify-center overflow-hidden shrink-0">
                        {firstImage ? (
                          <img src={firstImage} alt="" className="w-full h-full object-cover" />
                        ) : (
                          <Package className="h-5 w-5 text-muted-foreground/50" />
                        )}
                      </div>

                      {/* Checkout Info */}
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-2">
                          <span className="text-sm font-bold text-primary">
                            #{checkout.checkout_id.slice(-6).toUpperCase()}
                          </span>
                          <span className="text-xs text-muted-foreground">
                            {checkout.item_count} {text.items}
                          </span>
                        </div>
                        {checkout.customer_phone && (
                          <p className="text-xs text-muted-foreground flex items-center gap-1 mt-0.5">
                            <Phone className="h-3 w-3" />
                            <span dir="ltr">{checkout.customer_phone}</span>
                          </p>
                        )}
                        <p className="text-xs text-muted-foreground truncate mt-0.5">
                          {checkout.customer_name || text.anonymous}
                        </p>
                      </div>

                      {/* Price + Time */}
                      <div className="text-end shrink-0 me-2">
                        <p className="text-sm font-bold">{formatPrice(checkout.cart_total)}</p>
                        <p className="text-[10px] text-muted-foreground mt-0.5">
                          {getTimeSince(checkout.updated_at)}
                        </p>
                      </div>

                      {/* Status Badge */}
                      <div className="shrink-0">
                        {getStatusBadge(checkout)}
                      </div>

                      {/* Expand */}
                      <div className="shrink-0">
                        {isExpanded
                          ? <ChevronUp className="h-4 w-4 text-muted-foreground" />
                          : <ChevronDown className="h-4 w-4 text-muted-foreground" />
                        }
                      </div>
                    </div>

                    {/* Expanded Details */}
                    {isExpanded && (
                      <div className="border-t bg-muted/20 px-4 py-3 space-y-3">
                        {/* Products */}
                        <div className="space-y-2">
                          {(checkout.items || []).map((item, idx) => (
                            <div key={idx} className="flex items-center gap-2 text-xs">
                              {item.image && (
                                <img src={item.image} alt="" className="h-8 w-8 rounded object-cover" />
                              )}
                              <span className="flex-1 truncate">{item.name || item.product_id}</span>
                              <span className="text-muted-foreground">{item.quantity}x</span>
                              <span className="font-medium">{formatPrice(item.price * item.quantity)}</span>
                            </div>
                          ))}
                        </div>

                        {/* Status Info */}
                        {(checkout.send_status === 'failed' || checkout.send_attempts > 0) && (
                          <div className="bg-background p-2 rounded border text-xs space-y-1">
                            <div className="flex items-center gap-3 flex-wrap text-muted-foreground">
                              <span>{text.attempts}: <strong>{checkout.send_attempts || 0}</strong></span>
                              {checkout.next_retry_at && checkout.send_status === 'failed' && (
                                <span>{text.nextRetry}: <strong>{formatDate(checkout.next_retry_at)}</strong></span>
                              )}
                            </div>
                            {checkout.last_error && (
                              <p className="text-red-500 flex items-start gap-1">
                                <AlertTriangle className="h-3 w-3 shrink-0 mt-0.5" />
                                {checkout.last_error}
                              </p>
                            )}
                          </div>
                        )}

                        {/* Location */}
                        {checkout.wilaya && (
                          <p className="text-xs text-muted-foreground flex items-center gap-1">
                            <MapPin className="h-3 w-3" />
                            {[checkout.shipping_address, checkout.commune, checkout.wilaya].filter(Boolean).join(', ')}
                          </p>
                        )}

                        {/* Date */}
                        <p className="text-[10px] text-muted-foreground">
                          <Clock className="h-3 w-3 inline me-1" />
                          {formatDate(checkout.created_at)}
                        </p>

                        {/* Action Buttons */}
                        <div className="flex flex-wrap gap-2 pt-1">
                          {checkout.recovered ? (
                            <>
                              <Badge variant="outline" className="bg-green-50 text-green-700 border-green-200">
                                <CheckCircle className="h-3 w-3 me-1" />
                                {text.recovered}
                                {checkout.recovered_order_id && (
                                  <span className="ms-1 font-mono">#{checkout.recovered_order_id}</span>
                                )}
                              </Badge>
                            </>
                          ) : (
                            <>
                              {checkout.customer_phone && checkout.send_status !== 'sent' && (
                                <Button
                                  size="sm"
                                  className="bg-green-600 hover:bg-green-700 text-white text-xs h-7 px-3 rounded-lg"
                                  onClick={(e) => { e.stopPropagation(); handleSendWhatsApp(checkout); }}
                                >
                                  <MessageCircle className="h-3 w-3 me-1" />
                                  {text.sendWhatsApp}
                                </Button>
                              )}
                              {(checkout.send_status === 'failed' || checkout.send_status === 'skipped') && (
                                <Button
                                  size="sm" variant="outline"
                                  className="text-xs h-7 px-3 rounded-lg"
                                  onClick={(e) => { e.stopPropagation(); handleRetry(checkout.checkout_id); }}
                                >
                                  <RotateCcw className="h-3 w-3 me-1" />
                                  {text.retry}
                                </Button>
                              )}
                              {(checkout.send_status === 'pending' || checkout.send_status === 'failed') && (
                                <Button
                                  size="sm" variant="outline"
                                  className="text-xs h-7 px-3 rounded-lg"
                                  onClick={(e) => { e.stopPropagation(); handleSkip(checkout.checkout_id); }}
                                >
                                  <Ban className="h-3 w-3 me-1" />
                                  {text.skip}
                                </Button>
                              )}
                              <Button
                                size="sm" variant="outline"
                                className="text-xs h-7 px-3 rounded-lg"
                                onClick={(e) => { e.stopPropagation(); handleMarkRecovered(checkout.checkout_id); }}
                              >
                                <TrendingUp className="h-3 w-3 me-1" />
                                {text.markRecovered}
                              </Button>
                              <Button
                                size="sm" variant="ghost"
                                className="text-xs h-7 px-3 rounded-lg text-red-600 hover:text-red-700 hover:bg-red-50"
                                onClick={(e) => { e.stopPropagation(); handleDelete(checkout.checkout_id); }}
                              >
                                <Trash2 className="h-3 w-3 me-1" />
                                {text.delete}
                              </Button>
                            </>
                          )}
                        </div>
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          )}
        </div>

        {/* Right: WhatsApp Phone Preview — hidden on mobile */}
        <div className="hidden lg:block w-[310px] shrink-0 sticky top-4">
          <p className="text-sm font-medium mb-3 text-center text-muted-foreground">
            {text.previewTitle}
          </p>
          <WhatsAppPhonePreview
            message={selectedCheckout ? getPreviewMessage(selectedCheckout) : ''}
            storeName="AgroYousfi"
          />
          {!selectedCheckout && (
            <p className="text-xs text-muted-foreground text-center mt-3">
              {text.noPreview}
            </p>
          )}
        </div>
      </div>
    </div>
  );
};

export default AbandonedCheckoutsPage;
