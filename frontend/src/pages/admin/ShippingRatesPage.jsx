import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle,
} from '@/components/ui/dialog';
import { ArrowLeft, ArrowRight, Save, Loader2, ChevronDown, ChevronUp } from 'lucide-react';
import { toast } from 'sonner';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const ShippingRatesPage = () => {
  const { companyId } = useParams();
  const navigate = useNavigate();
  const { language, isRTL } = useLanguage();
  const ArrowIcon = isRTL ? ArrowRight : ArrowLeft;

  const [company, setCompany] = useState(null);
  const [wilayas, setWilayas] = useState([]);
  const [rates, setRates] = useState({});
  const [shippingType, setShippingType] = useState('home');
  const [saving, setSaving] = useState(false);
  const [loading, setLoading] = useState(true);
  const [bulkValues, setBulkValues] = useState({
    base_price: '500', min_delivery_days: '1', max_delivery_days: '3'
  });
  // Commune customization
  const [communeDialogOpen, setCommuneDialogOpen] = useState(false);
  const [selectedWilaya, setSelectedWilaya] = useState(null);
  const [communes, setCommunes] = useState([]);
  const [communeRates, setCommuneRates] = useState({});

  const l = {
    ar: {
      title: 'أسعار الشحن',
      back: 'رجوع',
      home: 'توصيل للمنزل',
      office: 'توصيل للمكتب',
      wilaya: 'الولاية',
      basePrice: 'السعر الأساسي (دج)',
      minDays: 'أقل مدة',
      maxDays: 'أقصى مدة',
      setAll: 'تعيين الكل',
      apply: 'تطبيق على الكل',
      save: 'حفظ الأسعار',
      saving: 'جاري الحفظ...',
      saved: 'تم حفظ الأسعار بنجاح',
      preview: 'معاينة',
      customizeCommunes: 'تخصيص بلديات',
      communeRates: 'أسعار البلديات',
      commune: 'البلدية',
      useWilayaDefault: 'سعر الولاية الافتراضي',
      saveCommuneRates: 'حفظ أسعار البلديات',
    },
    fr: {
      title: 'Tarifs de livraison',
      back: 'Retour',
      home: 'Livraison à domicile',
      office: 'Livraison au bureau',
      wilaya: 'Wilaya',
      basePrice: 'Prix de base (DA)',
      minDays: 'Min jours',
      maxDays: 'Max jours',
      setAll: 'Définir tout',
      apply: 'Appliquer à tous',
      save: 'Enregistrer les tarifs',
      saving: 'Enregistrement...',
      saved: 'Tarifs enregistrés',
      preview: 'Aperçu',
      customizeCommunes: 'Personnaliser communes',
      communeRates: 'Tarifs des communes',
      commune: 'Commune',
      useWilayaDefault: 'Tarif wilaya par défaut',
      saveCommuneRates: 'Enregistrer tarifs communes',
    },
    en: {
      title: 'Shipping Rates',
      back: 'Back',
      home: 'Home Delivery',
      office: 'Office Delivery',
      wilaya: 'Wilaya',
      basePrice: 'Base Price (DZD)',
      minDays: 'Min Days',
      maxDays: 'Max Days',
      setAll: 'Set All',
      apply: 'Apply to All',
      save: 'Save Rates',
      saving: 'Saving...',
      saved: 'Rates saved successfully',
      preview: 'Preview',
      customizeCommunes: 'Customize Communes',
      communeRates: 'Commune Rates',
      commune: 'Commune',
      useWilayaDefault: 'Wilaya default rate',
      saveCommuneRates: 'Save Commune Rates',
    }
  };
  const text = l[language] || l.ar;

  useEffect(() => {
    fetchData();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [companyId]);

  const fetchData = async () => {
    try {
      setLoading(true);
      const [companyRes, wilayasRes, ratesRes] = await Promise.all([
        axios.get(`${API}/shipping/companies/${companyId}`, { withCredentials: true }),
        axios.get(`${API}/wilayas`),
        axios.get(`${API}/shipping/companies/${companyId}/rates`, { withCredentials: true })
      ]);
      setCompany(companyRes.data);
      setWilayas(wilayasRes.data);

      // Index rates by wilaya+type key
      const rateMap = {};
      ratesRes.data.forEach(r => {
        const key = `${r.wilaya}__${r.shipping_type}`;
        if (r.commune) {
          // Commune-specific rates stored separately
          const cKey = `${r.wilaya}__${r.commune}__${r.shipping_type}`;
          rateMap[cKey] = r;
        } else {
          rateMap[key] = r;
        }
      });
      setRates(rateMap);
    } catch (error) {
      console.error('Error:', error);
      toast.error(language === 'ar' ? 'خطأ في تحميل البيانات' : 'Error loading data');
    } finally {
      setLoading(false);
    }
  };

  const getRateValue = (wilaya, field) => {
    const key = `${wilaya}__${shippingType}`;
    return rates[key]?.[field] ?? '';
  };

  const setRateValue = (wilaya, field, value) => {
    const key = `${wilaya}__${shippingType}`;
    setRates(prev => ({
      ...prev,
      [key]: { ...(prev[key] || {}), wilaya, shipping_type: shippingType, [field]: value }
    }));
  };

  const applyBulk = () => {
    const newRates = { ...rates };
    wilayas.forEach(w => {
      const key = `${w}__${shippingType}`;
      newRates[key] = {
        ...(newRates[key] || {}),
        wilaya: w,
        shipping_type: shippingType,
        base_price: bulkValues.base_price !== '' ? bulkValues.base_price : (newRates[key]?.base_price || ''),
        min_delivery_days: bulkValues.min_delivery_days !== '' ? bulkValues.min_delivery_days : (newRates[key]?.min_delivery_days || '1'),
        max_delivery_days: bulkValues.max_delivery_days !== '' ? bulkValues.max_delivery_days : (newRates[key]?.max_delivery_days || '3'),
      };
    });
    setRates(newRates);
    toast.success(language === 'ar' ? 'تم تطبيق القيم' : 'Values applied');
  };

  const handleSave = async () => {
    try {
      setSaving(true);
      const ratesList = [];

      Object.values(rates).forEach(r => {
        if (r.base_price && parseFloat(r.base_price) > 0) {
          ratesList.push({
            wilaya: r.wilaya,
            commune: r.commune || null,
            shipping_type: r.shipping_type || shippingType,
            base_price: parseFloat(r.base_price),
            min_delivery_days: parseInt(r.min_delivery_days) || 1,
            max_delivery_days: parseInt(r.max_delivery_days) || 3,
            is_active: true
          });
        }
      });

      await axios.post(`${API}/shipping/companies/${companyId}/rates`,
        { rates: ratesList },
        { withCredentials: true }
      );
      toast.success(text.saved);
    } catch (error) {
      toast.error(language === 'ar' ? 'خطأ' : 'Error');
    } finally {
      setSaving(false);
    }
  };

  // Commune customization
  const openCommuneDialog = async (wilaya) => {
    setSelectedWilaya(wilaya);
    try {
      const code = parseInt(wilaya);
      if (code > 0) {
        const res = await axios.get(`${API}/communes?wilaya=${code}`);
        setCommunes(res.data);
        // Load existing commune rates for this wilaya
        const existingCommuneRates = {};
        Object.entries(rates).forEach(([key, val]) => {
          if (val.wilaya === wilaya && val.commune) {
            existingCommuneRates[val.commune] = val;
          }
        });
        setCommuneRates(existingCommuneRates);
        setCommuneDialogOpen(true);
      }
    } catch (error) {
      console.error('Error fetching communes:', error);
    }
  };

  const setCommuneRateValue = (commune, field, value) => {
    setCommuneRates(prev => ({
      ...prev,
      [commune]: {
        ...(prev[commune] || {}),
        wilaya: selectedWilaya,
        commune,
        shipping_type: shippingType,
        [field]: value
      }
    }));
  };

  const saveCommuneRates = () => {
    const newRates = { ...rates };
    Object.entries(communeRates).forEach(([commune, rateData]) => {
      if (rateData.base_price && parseFloat(rateData.base_price) > 0) {
        const key = `${selectedWilaya}__${commune}__${shippingType}`;
        newRates[key] = { ...rateData, wilaya: selectedWilaya, commune, shipping_type: shippingType };
      }
    });
    setRates(newRates);
    setCommuneDialogOpen(false);
    toast.success(language === 'ar' ? 'تم حفظ أسعار البلديات' : 'Commune rates saved');
  };

  // Preview calculation - uses company-level weight settings
  const calcPreview = (wilaya, weight) => {
    const r = rates[`${wilaya}__${shippingType}`];
    if (!r || !r.base_price) return '-';
    const bp = parseFloat(r.base_price);
    const iw = parseFloat(company?.included_weight) || 5;
    const apk = parseFloat(company?.additional_price_per_kg) || 0;
    if (weight <= iw) return bp.toLocaleString();
    return (bp + (weight - iw) * apk).toLocaleString();
  };

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
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-4">
          <Button variant="ghost" size="icon" onClick={() => navigate('/admin/shipping')}>
            <ArrowIcon className="h-5 w-5" />
          </Button>
          <div>
            <h1 className="text-2xl font-bold">{text.title}</h1>
            <p className="text-sm text-muted-foreground">
              {company?.[`name_${language}`] || company?.name_ar}
            </p>
          </div>
        </div>
        <Button onClick={handleSave} disabled={saving}>
          {saving ? <Loader2 className="h-4 w-4 me-2 animate-spin" /> : <Save className="h-4 w-4 me-2" />}
          {saving ? text.saving : text.save}
        </Button>
      </div>

      {/* Shipping Type Tabs */}
      <div className="flex gap-2">
        <Button
          variant={shippingType === 'home' ? 'default' : 'outline'}
          onClick={() => setShippingType('home')}
          className="rounded-full"
        >
          {text.home}
        </Button>
        <Button
          variant={shippingType === 'office' ? 'default' : 'outline'}
          onClick={() => setShippingType('office')}
          className="rounded-full"
        >
          {text.office}
        </Button>
      </div>

      {/* Bulk Set */}
      <Card>
        <CardHeader className="pb-3">
          <CardTitle className="text-base">{text.setAll}</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div className="space-y-1">
              <Label className="text-xs">{text.basePrice}</Label>
              <Input type="number" min="0" value={bulkValues.base_price}
                onChange={e => setBulkValues(p => ({...p, base_price: e.target.value}))} placeholder="500" />
            </div>
            <div className="space-y-1">
              <Label className="text-xs">{text.minDays}</Label>
              <Input type="number" min="1" value={bulkValues.min_delivery_days}
                onChange={e => setBulkValues(p => ({...p, min_delivery_days: e.target.value}))} />
            </div>
            <div className="space-y-1">
              <Label className="text-xs">{text.maxDays}</Label>
              <Input type="number" min="1" value={bulkValues.max_delivery_days}
                onChange={e => setBulkValues(p => ({...p, max_delivery_days: e.target.value}))} />
            </div>
            <div className="flex items-end">
              <Button onClick={applyBulk} className="w-full">{text.apply}</Button>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Rates Table */}
      <Card>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b bg-muted/50">
                  <th className="p-3 text-start font-medium">{text.wilaya}</th>
                  <th className="p-3 text-center font-medium">{text.basePrice}</th>
                  <th className="p-3 text-center font-medium">{text.minDays}-{text.maxDays}</th>
                  <th className="p-3 text-center font-medium">3kg / 5kg / 10kg</th>
                  <th className="p-3 text-center font-medium">{text.customizeCommunes}</th>
                </tr>
              </thead>
              <tbody>
                {wilayas.map((w, i) => (
                  <tr key={i} className="border-b hover:bg-muted/30">
                    <td className="p-2 font-medium text-xs whitespace-nowrap max-w-[150px] truncate">{w}</td>
                    <td className="p-2">
                      <Input type="number" min="0" className="h-8 text-center w-24 mx-auto"
                        value={getRateValue(w, 'base_price')}
                        onChange={e => setRateValue(w, 'base_price', e.target.value)} />
                    </td>
                    <td className="p-2">
                      <div className="flex items-center gap-1 justify-center">
                        <Input type="number" min="1" className="h-8 text-center w-14"
                          value={getRateValue(w, 'min_delivery_days') || '1'}
                          onChange={e => setRateValue(w, 'min_delivery_days', e.target.value)} />
                        <span>-</span>
                        <Input type="number" min="1" className="h-8 text-center w-14"
                          value={getRateValue(w, 'max_delivery_days') || '3'}
                          onChange={e => setRateValue(w, 'max_delivery_days', e.target.value)} />
                      </div>
                    </td>
                    <td className="p-2 text-center text-xs text-muted-foreground whitespace-nowrap">
                      {calcPreview(w, 3)} / {calcPreview(w, 5)} / {calcPreview(w, 10)}
                    </td>
                    <td className="p-2 text-center">
                      <Button variant="ghost" size="sm" className="text-xs"
                        onClick={() => openCommuneDialog(w)}>
                        {text.customizeCommunes}
                      </Button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      {/* Commune Rates Dialog */}
      <Dialog open={communeDialogOpen} onOpenChange={setCommuneDialogOpen}>
        <DialogContent className="max-w-2xl max-h-[80vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>{text.communeRates} - {selectedWilaya}</DialogTitle>
          </DialogHeader>
          <p className="text-sm text-muted-foreground mb-4">{text.useWilayaDefault}</p>
          <div className="space-y-2">
            {communes.map((commune, i) => (
              <div key={i} className="grid grid-cols-2 gap-2 items-center border-b pb-2">
                <span className="text-sm font-medium truncate">{commune}</span>
                <Input type="number" min="0" className="h-8" placeholder={text.basePrice}
                  value={communeRates[commune]?.base_price || ''}
                  onChange={e => setCommuneRateValue(commune, 'base_price', e.target.value)} />
              </div>
            ))}
          </div>
          <Button onClick={saveCommuneRates} className="w-full mt-4">{text.saveCommuneRates}</Button>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default ShippingRatesPage;
