import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Loader2,
  ChevronLeft,
  ChevronRight,
  Search,
  ExternalLink,
  CheckCircle,
} from 'lucide-react';
import { toast } from 'sonner';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const CreateAdDialog = ({ open, onOpenChange, preselectedProductId, onCreated }) => {
  const { language, isRTL, formatPrice } = useLanguage();

  const [step, setStep] = useState(1);
  const [products, setProducts] = useState([]);
  const [loadingProducts, setLoadingProducts] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedProduct, setSelectedProduct] = useState(null);

  // Preview data from API
  const [preview, setPreview] = useState(null);
  const [loadingPreview, setLoadingPreview] = useState(false);

  // Editable ad fields
  const [adText, setAdText] = useState('');
  const [adHeadline, setAdHeadline] = useState('');
  const [landingUrl, setLandingUrl] = useState('');

  // Targeting & Budget
  const [targetCountry, setTargetCountry] = useState('DZ');
  const [targetAgeMin, setTargetAgeMin] = useState(18);
  const [targetAgeMax, setTargetAgeMax] = useState(65);
  const [dailyBudget, setDailyBudget] = useState('');
  const [durationDays, setDurationDays] = useState('7');

  const [creating, setCreating] = useState(false);

  const l = {
    ar: {
      selectProduct: 'اختر المنتج',
      adPreview: 'معاينة الإعلان',
      targeting: 'الاستهداف والميزانية',
      next: 'التالي',
      back: 'رجوع',
      createAd: 'إنشاء الإعلان',
      creating: 'جاري الإنشاء...',
      success: 'تم إنشاء الإعلان بنجاح',
      error: 'حدث خطأ أثناء إنشاء الإعلان',
      searchProducts: 'ابحث عن منتج...',
      dailyBudget: 'الميزانية اليومية (د.ج)',
      duration: 'المدة',
      country: 'الدولة',
      ageRange: 'الفئة العمرية',
      days: 'أيام',
      adTextLabel: 'نص الإعلان',
      adHeadlineLabel: 'عنوان الإعلان',
      landingPage: 'صفحة الهبوط',
      previewLanding: 'معاينة صفحة الهبوط',
      step: 'الخطوة',
      of: 'من',
      noProducts: 'لا توجد منتجات',
      selectProductDesc: 'اختر المنتج الذي تريد الإعلان عنه',
      configureAd: 'قم بتعديل نص الإعلان والعنوان',
      configureBudget: 'حدد الاستهداف والميزانية',
      summary: 'ملخص',
      min: 'الحد الأدنى',
      max: 'الحد الأقصى',
      product: 'المنتج',
      budget: 'الميزانية',
      totalBudget: 'الميزانية الإجمالية',
      loadingPreview: 'جاري تحميل المعاينة...',
    },
    fr: {
      selectProduct: 'Sélectionner le produit',
      adPreview: "Aperçu de l'annonce",
      targeting: 'Ciblage et budget',
      next: 'Suivant',
      back: 'Retour',
      createAd: "Créer l'annonce",
      creating: 'Création en cours...',
      success: 'Annonce créée avec succès',
      error: "Erreur lors de la création de l'annonce",
      searchProducts: 'Rechercher un produit...',
      dailyBudget: 'Budget quotidien (DZD)',
      duration: 'Durée',
      country: 'Pays',
      ageRange: "Tranche d'âge",
      days: 'jours',
      adTextLabel: "Texte de l'annonce",
      adHeadlineLabel: "Titre de l'annonce",
      landingPage: "Page d'atterrissage",
      previewLanding: "Aperçu de la page d'atterrissage",
      step: 'Étape',
      of: 'sur',
      noProducts: 'Aucun produit trouvé',
      selectProductDesc: 'Choisissez le produit à promouvoir',
      configureAd: "Modifiez le texte et le titre de l'annonce",
      configureBudget: 'Définissez le ciblage et le budget',
      summary: 'Résumé',
      min: 'Min',
      max: 'Max',
      product: 'Produit',
      budget: 'Budget',
      totalBudget: 'Budget total',
      loadingPreview: "Chargement de l'aperçu...",
    },
    en: {
      selectProduct: 'Select Product',
      adPreview: 'Ad Preview',
      targeting: 'Targeting & Budget',
      next: 'Next',
      back: 'Back',
      createAd: 'Create Ad',
      creating: 'Creating...',
      success: 'Ad created successfully',
      error: 'Error creating ad',
      searchProducts: 'Search products...',
      dailyBudget: 'Daily Budget (DZD)',
      duration: 'Duration',
      country: 'Country',
      ageRange: 'Age Range',
      days: 'days',
      adTextLabel: 'Ad Text',
      adHeadlineLabel: 'Ad Headline',
      landingPage: 'Landing Page',
      previewLanding: 'Preview Landing Page',
      step: 'Step',
      of: 'of',
      noProducts: 'No products found',
      selectProductDesc: 'Choose the product you want to advertise',
      configureAd: 'Edit the ad text and headline',
      configureBudget: 'Set targeting and budget',
      summary: 'Summary',
      min: 'Min',
      max: 'Max',
      product: 'Product',
      budget: 'Budget',
      totalBudget: 'Total Budget',
      loadingPreview: 'Loading preview...',
    },
  };

  const text = l[language] || l.ar;

  const countries = [
    { value: 'DZ', label: '\u0627\u0644\u062C\u0632\u0627\u0626\u0631' },
    { value: 'MA', label: '\u0627\u0644\u0645\u063A\u0631\u0628' },
    { value: 'TN', label: '\u062A\u0648\u0646\u0633' },
    { value: 'EG', label: '\u0645\u0635\u0631' },
    { value: 'SA', label: '\u0627\u0644\u0633\u0639\u0648\u062F\u064A\u0629' },
  ];

  const durationOptions = [
    { value: '3', label: `3 ${text.days}` },
    { value: '5', label: `5 ${text.days}` },
    { value: '7', label: `7 ${text.days}` },
    { value: '14', label: `14 ${text.days}` },
    { value: '30', label: `30 ${text.days}` },
  ];

  // Fetch products on open
  useEffect(() => {
    if (open) {
      fetchProducts();
      // Reset state
      setStep(1);
      setSelectedProduct(null);
      setPreview(null);
      setAdText('');
      setAdHeadline('');
      setLandingUrl('');
      setTargetCountry('DZ');
      setTargetAgeMin(18);
      setTargetAgeMax(65);
      setDailyBudget('');
      setDurationDays('7');
      setSearchQuery('');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open]);

  // Auto-select preselected product
  useEffect(() => {
    if (open && preselectedProductId && products.length > 0) {
      const product = products.find(
        (p) => p.product_id === preselectedProductId || p.id === preselectedProductId
      );
      if (product) {
        handleSelectProduct(product);
      }
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open, preselectedProductId, products]);

  const fetchProducts = async () => {
    try {
      setLoadingProducts(true);
      const res = await axios.get(`${API}/products`, { withCredentials: true });
      setProducts(res.data || []);
    } catch (error) {
      console.error('Error fetching products:', error);
      toast.error(text.error);
    } finally {
      setLoadingProducts(false);
    }
  };

  const fetchPreview = async (productId) => {
    try {
      setLoadingPreview(true);
      const res = await axios.get(`${API}/admin/facebook-ads/preview/${productId}`, {
        withCredentials: true,
      });
      const data = res.data;
      setPreview(data);
      setAdText(data.ad_text || '');
      setAdHeadline(data.ad_headline || '');
      setLandingUrl(data.landing_url || '');
    } catch (error) {
      console.error('Error fetching preview:', error);
      toast.error(text.error);
    } finally {
      setLoadingPreview(false);
    }
  };

  const handleSelectProduct = (product) => {
    setSelectedProduct(product);
    fetchPreview(product.product_id || product.id);
    setStep(2);
  };

  const handleCreate = async () => {
    try {
      setCreating(true);
      await axios.post(
        `${API}/admin/facebook-ads`,
        {
          product_id: selectedProduct.product_id || selectedProduct.id,
          ad_text: adText,
          ad_headline: adHeadline,
          daily_budget: Number(dailyBudget),
          duration_days: Number(durationDays),
          target_country: targetCountry,
          target_age_min: Number(targetAgeMin),
          target_age_max: Number(targetAgeMax),
        },
        { withCredentials: true }
      );
      toast.success(text.success);
      onCreated?.();
      onOpenChange(false);
    } catch (error) {
      console.error('Error creating ad:', error);
      toast.error(error.response?.data?.message || text.error);
    } finally {
      setCreating(false);
    }
  };

  const filteredProducts = products.filter((product) => {
    if (!searchQuery) return true;
    const q = searchQuery.toLowerCase();
    return (
      product.name_ar?.toLowerCase().includes(q) ||
      product.name_fr?.toLowerCase().includes(q) ||
      product.name_en?.toLowerCase().includes(q) ||
      product.product_id?.toLowerCase().includes(q)
    );
  });

  const getProductName = (product) => {
    return product?.[`name_${language}`] || product?.name_ar || '';
  };

  const stepTitles = [text.selectProduct, text.adPreview, text.targeting];
  const stepDescriptions = [text.selectProductDesc, text.configureAd, text.configureBudget];

  const BackIcon = isRTL ? ChevronRight : ChevronLeft;
  const NextIcon = isRTL ? ChevronLeft : ChevronRight;

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>{stepTitles[step - 1]}</DialogTitle>
          <DialogDescription>{stepDescriptions[step - 1]}</DialogDescription>
        </DialogHeader>

        {/* Step Indicator */}
        <div className="flex items-center justify-center gap-2 py-2">
          {[1, 2, 3].map((s) => (
            <div
              key={s}
              className={`h-2.5 w-2.5 rounded-full transition-colors ${
                s === step
                  ? 'bg-primary scale-125'
                  : s < step
                  ? 'bg-primary/50'
                  : 'bg-muted-foreground/30'
              }`}
            />
          ))}
        </div>

        {/* Step 1: Select Product */}
        {step === 1 && (
          <div className="space-y-4">
            <div className="relative">
              <Search
                className={`absolute top-1/2 -translate-y-1/2 ${
                  isRTL ? 'right-3' : 'left-3'
                } h-4 w-4 text-muted-foreground`}
              />
              <Input
                placeholder={text.searchProducts}
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className={isRTL ? 'pr-10' : 'pl-10'}
              />
            </div>

            {loadingProducts ? (
              <div className="flex items-center justify-center h-48">
                <Loader2 className="h-8 w-8 animate-spin text-primary" />
              </div>
            ) : filteredProducts.length === 0 ? (
              <div className="flex flex-col items-center justify-center h-48 text-muted-foreground">
                <p>{text.noProducts}</p>
              </div>
            ) : (
              <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[400px] overflow-y-auto">
                {filteredProducts.map((product) => (
                  <button
                    key={product.product_id || product.id}
                    onClick={() => handleSelectProduct(product)}
                    className={`flex flex-col items-center gap-2 p-3 rounded-xl border text-start transition-all hover:border-primary hover:shadow-md ${
                      selectedProduct?.product_id === product.product_id
                        ? 'border-primary bg-primary/5 ring-2 ring-primary/20'
                        : 'border-border'
                    }`}
                  >
                    <img
                      src={product.images?.[0] || 'https://via.placeholder.com/80'}
                      alt={getProductName(product)}
                      className="h-20 w-20 rounded-lg object-cover"
                    />
                    <div className="w-full text-center">
                      <p className="text-sm font-medium truncate">{getProductName(product)}</p>
                      <p className="text-xs text-primary font-semibold mt-0.5">
                        {formatPrice(product.price)}
                      </p>
                    </div>
                  </button>
                ))}
              </div>
            )}
          </div>
        )}

        {/* Step 2: Ad Preview & Config */}
        {step === 2 && (
          <div className="space-y-4">
            {loadingPreview ? (
              <div className="flex items-center justify-center h-48">
                <div className="flex flex-col items-center gap-2">
                  <Loader2 className="h-8 w-8 animate-spin text-primary" />
                  <p className="text-sm text-muted-foreground">{text.loadingPreview}</p>
                </div>
              </div>
            ) : (
              <>
                {/* Product Image */}
                {selectedProduct && (
                  <div className="flex items-center gap-4 p-3 bg-muted/50 rounded-xl">
                    <img
                      src={selectedProduct.images?.[0] || 'https://via.placeholder.com/60'}
                      alt={getProductName(selectedProduct)}
                      className="h-16 w-16 rounded-lg object-cover"
                    />
                    <div>
                      <p className="font-semibold">{getProductName(selectedProduct)}</p>
                      <p className="text-sm text-primary font-medium">
                        {formatPrice(selectedProduct.price)}
                      </p>
                    </div>
                  </div>
                )}

                {/* Ad Text */}
                <div className="space-y-2">
                  <Label>{text.adTextLabel}</Label>
                  <Textarea
                    value={adText}
                    onChange={(e) => setAdText(e.target.value)}
                    rows={4}
                    className="resize-none"
                  />
                </div>

                {/* Ad Headline */}
                <div className="space-y-2">
                  <Label>{text.adHeadlineLabel}</Label>
                  <Input
                    value={adHeadline}
                    onChange={(e) => setAdHeadline(e.target.value)}
                  />
                </div>

                {/* Landing URL */}
                <div className="space-y-2">
                  <Label>{text.landingPage}</Label>
                  <div className="flex items-center gap-2">
                    <Input value={landingUrl} readOnly className="flex-1 bg-muted/50" />
                    {landingUrl && (
                      <a href={landingUrl} target="_blank" rel="noopener noreferrer">
                        <Button variant="outline" size="icon" className="shrink-0">
                          <ExternalLink className="h-4 w-4" />
                        </Button>
                      </a>
                    )}
                  </div>
                </div>

                {/* Preview Landing Page Button */}
                {landingUrl && (
                  <a href={landingUrl} target="_blank" rel="noopener noreferrer" className="block">
                    <Button variant="outline" className="w-full">
                      <ExternalLink className="h-4 w-4 me-2" />
                      {text.previewLanding}
                    </Button>
                  </a>
                )}
              </>
            )}
          </div>
        )}

        {/* Step 3: Targeting & Budget */}
        {step === 3 && (
          <div className="space-y-5">
            {/* Country */}
            <div className="space-y-2">
              <Label>{text.country}</Label>
              <Select value={targetCountry} onValueChange={setTargetCountry}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {countries.map((c) => (
                    <SelectItem key={c.value} value={c.value}>
                      {c.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            {/* Age Range */}
            <div className="space-y-2">
              <Label>{text.ageRange}</Label>
              <div className="flex items-center gap-3">
                <div className="flex-1">
                  <Label className="text-xs text-muted-foreground">{text.min}</Label>
                  <Input
                    type="number"
                    min={13}
                    max={65}
                    value={targetAgeMin}
                    onChange={(e) => setTargetAgeMin(Number(e.target.value))}
                  />
                </div>
                <span className="mt-5 text-muted-foreground">-</span>
                <div className="flex-1">
                  <Label className="text-xs text-muted-foreground">{text.max}</Label>
                  <Input
                    type="number"
                    min={13}
                    max={65}
                    value={targetAgeMax}
                    onChange={(e) => setTargetAgeMax(Number(e.target.value))}
                  />
                </div>
              </div>
            </div>

            {/* Daily Budget */}
            <div className="space-y-2">
              <Label>{text.dailyBudget}</Label>
              <Input
                type="number"
                placeholder="500"
                value={dailyBudget}
                onChange={(e) => setDailyBudget(e.target.value)}
              />
            </div>

            {/* Duration */}
            <div className="space-y-2">
              <Label>{text.duration}</Label>
              <Select value={durationDays} onValueChange={setDurationDays}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {durationOptions.map((d) => (
                    <SelectItem key={d.value} value={d.value}>
                      {d.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            {/* Summary */}
            <div className="p-4 bg-muted/50 rounded-xl space-y-2">
              <h4 className="font-semibold text-sm">{text.summary}</h4>
              <div className="grid grid-cols-2 gap-2 text-sm">
                <span className="text-muted-foreground">{text.product}:</span>
                <span className="font-medium truncate">{getProductName(selectedProduct)}</span>

                <span className="text-muted-foreground">{text.country}:</span>
                <span className="font-medium">
                  {countries.find((c) => c.value === targetCountry)?.label}
                </span>

                <span className="text-muted-foreground">{text.ageRange}:</span>
                <span className="font-medium">
                  {targetAgeMin} - {targetAgeMax}
                </span>

                <span className="text-muted-foreground">{text.dailyBudget}:</span>
                <span className="font-medium">
                  {dailyBudget ? `${Number(dailyBudget).toLocaleString()} DZD` : '-'}
                </span>

                <span className="text-muted-foreground">{text.duration}:</span>
                <span className="font-medium">
                  {durationDays} {text.days}
                </span>

                <span className="text-muted-foreground">{text.totalBudget}:</span>
                <span className="font-semibold text-primary">
                  {dailyBudget
                    ? `${(Number(dailyBudget) * Number(durationDays)).toLocaleString()} DZD`
                    : '-'}
                </span>
              </div>
            </div>
          </div>
        )}

        {/* Navigation Buttons */}
        <div className="flex items-center justify-between pt-4 border-t">
          <Button
            variant="outline"
            onClick={() => setStep((s) => s - 1)}
            disabled={step === 1}
            className={step === 1 ? 'invisible' : ''}
          >
            <BackIcon className="h-4 w-4 me-1" />
            {text.back}
          </Button>

          {step < 3 ? (
            <Button
              onClick={() => setStep((s) => s + 1)}
              disabled={step === 1 && !selectedProduct}
            >
              {text.next}
              <NextIcon className="h-4 w-4 ms-1" />
            </Button>
          ) : (
            <Button
              onClick={handleCreate}
              disabled={creating || !dailyBudget}
              className="bg-green-600 hover:bg-green-700"
            >
              {creating ? (
                <>
                  <Loader2 className="h-4 w-4 me-2 animate-spin" />
                  {text.creating}
                </>
              ) : (
                <>
                  <CheckCircle className="h-4 w-4 me-2" />
                  {text.createAd}
                </>
              )}
            </Button>
          )}
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default CreateAdDialog;
