import React, { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import {
  Megaphone,
  Plus,
  RefreshCw,
  Pause,
  Play,
  Trash2,
  BarChart3,
  Eye,
  Loader2,
  MousePointerClick,
  DollarSign,
  Users2,
  ExternalLink,
} from 'lucide-react';
import { toast } from 'sonner';
import CreateAdDialog from '@/components/admin/CreateAdDialog';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const FacebookAdsPage = () => {
  const { language, isRTL, formatPrice } = useLanguage();
  const [searchParams, setSearchParams] = useSearchParams();

  const [ads, setAds] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshingAll, setRefreshingAll] = useState(false);
  const [refreshingId, setRefreshingId] = useState(null);
  const [pausingId, setPausingId] = useState(null);
  const [deletingId, setDeletingId] = useState(null);

  // Create dialog
  const [createOpen, setCreateOpen] = useState(false);
  const [preselectedProductId, setPreselectedProductId] = useState(null);

  const l = {
    ar: {
      facebookAds: 'إعلانات فيسبوك',
      facebookAdsDesc: 'إدارة حملاتك الإعلانية على فيسبوك',
      createAd: 'إنشاء إعلان',
      refreshMetrics: 'تحديث المقاييس',
      activeAds: 'الإعلانات النشطة',
      totalImpressions: 'إجمالي المشاهدات',
      totalClicks: 'إجمالي النقرات',
      totalSpend: 'إجمالي الإنفاق',
      product: 'المنتج',
      status: 'الحالة',
      dailyBudget: 'الميزانية اليومية',
      duration: 'المدة',
      impressions: 'المشاهدات',
      clicks: 'النقرات',
      spend: 'الإنفاق',
      actions: 'الإجراءات',
      pause: 'إيقاف',
      resume: 'استئناف',
      delete: 'حذف',
      refreshSingle: 'تحديث المقاييس',
      viewLanding: 'عرض صفحة الهبوط',
      noAds: 'لا توجد إعلانات بعد',
      noAdsDesc: 'ابدأ بإنشاء أول إعلان لمنتجاتك على فيسبوك',
      active: 'نشط',
      paused: 'متوقف',
      error: 'خطأ',
      pending: 'قيد الانتظار',
      draft: 'مسودة',
      days: 'أيام',
      deleteConfirm: 'هل أنت متأكد من حذف هذا الإعلان؟',
      adDeleted: 'تم حذف الإعلان',
      adPaused: 'تم إيقاف الإعلان',
      adResumed: 'تم استئناف الإعلان',
      metricsRefreshed: 'تم تحديث المقاييس',
      errorOccurred: 'حدث خطأ',
      da: 'د.ج',
    },
    fr: {
      facebookAds: 'Annonces Facebook',
      facebookAdsDesc: 'Gérez vos campagnes publicitaires Facebook',
      createAd: 'Créer une annonce',
      refreshMetrics: 'Actualiser les métriques',
      activeAds: 'Annonces actives',
      totalImpressions: 'Impressions totales',
      totalClicks: 'Clics totaux',
      totalSpend: 'Dépenses totales',
      product: 'Produit',
      status: 'Statut',
      dailyBudget: 'Budget quotidien',
      duration: 'Durée',
      impressions: 'Impressions',
      clicks: 'Clics',
      spend: 'Dépenses',
      actions: 'Actions',
      pause: 'Pause',
      resume: 'Reprendre',
      delete: 'Supprimer',
      refreshSingle: 'Actualiser les métriques',
      viewLanding: "Voir la page d'atterrissage",
      noAds: "Aucune annonce pour l'instant",
      noAdsDesc: 'Commencez par créer votre première annonce Facebook pour vos produits',
      active: 'Active',
      paused: 'En pause',
      error: 'Erreur',
      pending: 'En attente',
      draft: 'Brouillon',
      days: 'jours',
      deleteConfirm: 'Êtes-vous sûr de vouloir supprimer cette annonce ?',
      adDeleted: 'Annonce supprimée',
      adPaused: 'Annonce mise en pause',
      adResumed: 'Annonce reprise',
      metricsRefreshed: 'Métriques actualisées',
      errorOccurred: 'Une erreur est survenue',
      da: 'DA',
    },
    en: {
      facebookAds: 'Facebook Ads',
      facebookAdsDesc: 'Manage your Facebook advertising campaigns',
      createAd: 'Create Ad',
      refreshMetrics: 'Refresh Metrics',
      activeAds: 'Active Ads',
      totalImpressions: 'Total Impressions',
      totalClicks: 'Total Clicks',
      totalSpend: 'Total Spend',
      product: 'Product',
      status: 'Status',
      dailyBudget: 'Daily Budget',
      duration: 'Duration',
      impressions: 'Impressions',
      clicks: 'Clicks',
      spend: 'Spend',
      actions: 'Actions',
      pause: 'Pause',
      resume: 'Resume',
      delete: 'Delete',
      refreshSingle: 'Refresh Metrics',
      viewLanding: 'View Landing Page',
      noAds: 'No ads yet',
      noAdsDesc: 'Start by creating your first Facebook ad for your products',
      active: 'Active',
      paused: 'Paused',
      error: 'Error',
      pending: 'Pending',
      draft: 'Draft',
      days: 'days',
      deleteConfirm: 'Are you sure you want to delete this ad?',
      adDeleted: 'Ad deleted',
      adPaused: 'Ad paused',
      adResumed: 'Ad resumed',
      metricsRefreshed: 'Metrics refreshed',
      errorOccurred: 'An error occurred',
      da: 'DA',
    },
  };

  const text = l[language] || l.ar;

  const statusConfig = {
    active: { label: text.active, className: 'bg-green-100 text-green-700' },
    paused: { label: text.paused, className: 'bg-yellow-100 text-yellow-700' },
    error: { label: text.error, className: 'bg-red-100 text-red-700' },
    pending: { label: text.pending, className: 'bg-blue-100 text-blue-700' },
    draft: { label: text.draft, className: 'bg-gray-100 text-gray-700' },
  };

  // Check URL for auto-open create dialog
  useEffect(() => {
    const createProductId = searchParams.get('create');
    if (createProductId) {
      setPreselectedProductId(createProductId);
      setCreateOpen(true);
      // Clean up URL param
      searchParams.delete('create');
      setSearchParams(searchParams, { replace: true });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [searchParams]);

  useEffect(() => {
    fetchAds();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const fetchAds = async () => {
    try {
      setLoading(true);
      const res = await axios.get(`${API}/admin/facebook-ads`, { withCredentials: true });
      setAds(res.data || []);
    } catch (error) {
      console.error('Error fetching ads:', error);
      toast.error(text.errorOccurred);
    } finally {
      setLoading(false);
    }
  };

  const handleRefreshAll = async () => {
    try {
      setRefreshingAll(true);
      await axios.post(`${API}/admin/facebook-ads/metrics/refresh`, {}, { withCredentials: true });
      await fetchAds();
      toast.success(text.metricsRefreshed);
    } catch (error) {
      console.error('Error refreshing metrics:', error);
      toast.error(text.errorOccurred);
    } finally {
      setRefreshingAll(false);
    }
  };

  const handleRefreshSingle = async (adId) => {
    try {
      setRefreshingId(adId);
      await axios.post(`${API}/admin/facebook-ads/${adId}/metrics`, {}, { withCredentials: true });
      await fetchAds();
      toast.success(text.metricsRefreshed);
    } catch (error) {
      console.error('Error refreshing ad metrics:', error);
      toast.error(text.errorOccurred);
    } finally {
      setRefreshingId(null);
    }
  };

  const handlePause = async (adId) => {
    try {
      setPausingId(adId);
      await axios.put(`${API}/admin/facebook-ads/${adId}/pause`, {}, { withCredentials: true });
      setAds((prev) =>
        prev.map((ad) => (ad.id === adId ? { ...ad, status: 'paused' } : ad))
      );
      toast.success(text.adPaused);
    } catch (error) {
      console.error('Error pausing ad:', error);
      toast.error(text.errorOccurred);
    } finally {
      setPausingId(null);
    }
  };

  const handleResume = async (adId) => {
    try {
      setPausingId(adId);
      await axios.put(`${API}/admin/facebook-ads/${adId}/resume`, {}, { withCredentials: true });
      setAds((prev) =>
        prev.map((ad) => (ad.id === adId ? { ...ad, status: 'active' } : ad))
      );
      toast.success(text.adResumed);
    } catch (error) {
      console.error('Error resuming ad:', error);
      toast.error(text.errorOccurred);
    } finally {
      setPausingId(null);
    }
  };

  const handleDelete = async (adId) => {
    if (!window.confirm(text.deleteConfirm)) return;
    try {
      setDeletingId(adId);
      await axios.delete(`${API}/admin/facebook-ads/${adId}`, { withCredentials: true });
      setAds((prev) => prev.filter((ad) => ad.id !== adId));
      toast.success(text.adDeleted);
    } catch (error) {
      console.error('Error deleting ad:', error);
      toast.error(text.errorOccurred);
    } finally {
      setDeletingId(null);
    }
  };

  const handleAdCreated = () => {
    fetchAds();
    setPreselectedProductId(null);
  };

  // Compute stats from ads array
  const activeAdsCount = ads.filter((ad) => ad.status === 'active').length;
  const totalImpressions = ads.reduce((sum, ad) => sum + (ad.impressions || 0), 0);
  const totalClicks = ads.reduce((sum, ad) => sum + (ad.clicks || 0), 0);
  const totalSpend = ads.reduce((sum, ad) => sum + (ad.spend || 0), 0);

  const getProductName = (ad) => {
    if (ad.product) {
      return ad.product[`name_${language}`] || ad.product.name_ar || '';
    }
    return ad.product_name || '';
  };

  const getProductImage = (ad) => {
    if (ad.product?.images?.[0]) return ad.product.images[0];
    if (ad.product_image) return ad.product_image;
    return 'https://via.placeholder.com/40';
  };

  const renderStatCard = (title, value, Icon, color) => (
    <Card>
      <CardContent className="p-5">
        <div className="flex items-start justify-between">
          <div>
            <p className="text-sm font-medium text-muted-foreground">{title}</p>
            <p className="text-2xl font-bold mt-2">{value}</p>
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
        <Loader2 className="h-12 w-12 animate-spin text-primary" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold">{text.facebookAds}</h1>
          <p className="text-muted-foreground">{text.facebookAdsDesc}</p>
        </div>
        <div className="flex items-center gap-2">
          <Button
            variant="outline"
            size="sm"
            onClick={handleRefreshAll}
            disabled={refreshingAll}
          >
            <RefreshCw className={`h-4 w-4 me-2 ${refreshingAll ? 'animate-spin' : ''}`} />
            {text.refreshMetrics}
          </Button>
          <Button
            size="sm"
            onClick={() => {
              setPreselectedProductId(null);
              setCreateOpen(true);
            }}
          >
            <Plus className="h-4 w-4 me-2" />
            {text.createAd}
          </Button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {renderStatCard(text.activeAds, activeAdsCount, Megaphone, 'bg-green-500')}
        {renderStatCard(text.totalImpressions, totalImpressions.toLocaleString(), Eye, 'bg-blue-500')}
        {renderStatCard(text.totalClicks, totalClicks.toLocaleString(), MousePointerClick, 'bg-purple-500')}
        {renderStatCard(text.totalSpend, `${totalSpend.toLocaleString()} ${text.da}`, DollarSign, 'bg-orange-500')}
      </div>

      {/* Ads List */}
      {ads.length === 0 ? (
        <Card>
          <CardContent className="flex flex-col items-center justify-center py-16 text-muted-foreground">
            <Megaphone className="h-16 w-16 mb-4 opacity-40" />
            <h3 className="text-lg font-semibold mb-1">{text.noAds}</h3>
            <p className="text-sm mb-4">{text.noAdsDesc}</p>
            <Button
              onClick={() => {
                setPreselectedProductId(null);
                setCreateOpen(true);
              }}
            >
              <Plus className="h-4 w-4 me-2" />
              {text.createAd}
            </Button>
          </CardContent>
        </Card>
      ) : (
        <Card>
          <CardContent className="p-0">
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b bg-muted/50">
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.product}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.status}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.dailyBudget}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.duration}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.impressions}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.clicks}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.spend}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.actions}</th>
                  </tr>
                </thead>
                <tbody>
                  {ads.map((ad) => {
                    const status = statusConfig[ad.status] || statusConfig.draft;
                    return (
                      <tr
                        key={ad.id}
                        className="border-b hover:bg-muted/30 transition-colors"
                      >
                        {/* Product */}
                        <td className="py-3 px-4">
                          <div className="flex items-center gap-3">
                            <img
                              src={getProductImage(ad)}
                              alt=""
                              className="h-10 w-10 rounded-lg object-cover"
                            />
                            <div>
                              <p className="font-medium text-sm">{getProductName(ad)}</p>
                              {ad.ad_headline && (
                                <p className="text-xs text-muted-foreground truncate max-w-[200px]">
                                  {ad.ad_headline}
                                </p>
                              )}
                            </div>
                          </div>
                        </td>

                        {/* Status */}
                        <td className="py-3 px-4">
                          <span
                            className={`inline-flex px-2.5 py-1 rounded-full text-xs font-medium ${status.className}`}
                          >
                            {status.label}
                          </span>
                        </td>

                        {/* Daily Budget */}
                        <td className="py-3 px-4 text-sm font-medium">
                          {ad.daily_budget ? `${Number(ad.daily_budget).toLocaleString()} ${text.da}` : '-'}
                        </td>

                        {/* Duration */}
                        <td className="py-3 px-4 text-sm">
                          {ad.duration_days ? `${ad.duration_days} ${text.days}` : '-'}
                        </td>

                        {/* Impressions */}
                        <td className="py-3 px-4 text-sm">
                          {(ad.impressions || 0).toLocaleString()}
                        </td>

                        {/* Clicks */}
                        <td className="py-3 px-4 text-sm">
                          {(ad.clicks || 0).toLocaleString()}
                        </td>

                        {/* Spend */}
                        <td className="py-3 px-4 text-sm font-medium">
                          {(ad.spend || 0).toLocaleString()} {text.da}
                        </td>

                        {/* Actions */}
                        <td className="py-3 px-4">
                          <div className="flex items-center gap-1">
                            {/* Refresh Metrics */}
                            <Button
                              variant="ghost"
                              size="icon"
                              className="h-8 w-8"
                              onClick={() => handleRefreshSingle(ad.id)}
                              disabled={refreshingId === ad.id}
                              title={text.refreshSingle}
                            >
                              {refreshingId === ad.id ? (
                                <Loader2 className="h-4 w-4 animate-spin" />
                              ) : (
                                <BarChart3 className="h-4 w-4" />
                              )}
                            </Button>

                            {/* Pause / Resume */}
                            {ad.status === 'active' ? (
                              <Button
                                variant="ghost"
                                size="icon"
                                className="h-8 w-8 text-yellow-600 hover:text-yellow-700 hover:bg-yellow-50"
                                onClick={() => handlePause(ad.id)}
                                disabled={pausingId === ad.id}
                                title={text.pause}
                              >
                                {pausingId === ad.id ? (
                                  <Loader2 className="h-4 w-4 animate-spin" />
                                ) : (
                                  <Pause className="h-4 w-4" />
                                )}
                              </Button>
                            ) : ad.status === 'paused' ? (
                              <Button
                                variant="ghost"
                                size="icon"
                                className="h-8 w-8 text-green-600 hover:text-green-700 hover:bg-green-50"
                                onClick={() => handleResume(ad.id)}
                                disabled={pausingId === ad.id}
                                title={text.resume}
                              >
                                {pausingId === ad.id ? (
                                  <Loader2 className="h-4 w-4 animate-spin" />
                                ) : (
                                  <Play className="h-4 w-4" />
                                )}
                              </Button>
                            ) : null}

                            {/* View Landing Page */}
                            {ad.landing_url && (
                              <a
                                href={ad.landing_url}
                                target="_blank"
                                rel="noopener noreferrer"
                              >
                                <Button
                                  variant="ghost"
                                  size="icon"
                                  className="h-8 w-8"
                                  title={text.viewLanding}
                                >
                                  <ExternalLink className="h-4 w-4" />
                                </Button>
                              </a>
                            )}

                            {/* Delete */}
                            <Button
                              variant="ghost"
                              size="icon"
                              className="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50"
                              onClick={() => handleDelete(ad.id)}
                              disabled={deletingId === ad.id}
                              title={text.delete}
                            >
                              {deletingId === ad.id ? (
                                <Loader2 className="h-4 w-4 animate-spin" />
                              ) : (
                                <Trash2 className="h-4 w-4" />
                              )}
                            </Button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Create Ad Dialog */}
      <CreateAdDialog
        open={createOpen}
        onOpenChange={setCreateOpen}
        preselectedProductId={preselectedProductId}
        onCreated={handleAdCreated}
      />
    </div>
  );
};

export default FacebookAdsPage;
