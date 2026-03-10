import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useNavigate } from 'react-router-dom';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import {
  Plus,
  Pencil,
  Trash2,
  Truck,
  DollarSign,
  Loader2
} from 'lucide-react';
import { toast } from 'sonner';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const ShippingCompaniesPage = () => {
  const { language, isRTL } = useLanguage();
  const navigate = useNavigate();
  const [companies, setCompanies] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [editDialog, setEditDialog] = useState({ open: false, company: null });
  const [deleteDialog, setDeleteDialog] = useState({ open: false, company: null });
  const [formData, setFormData] = useState({
    name_ar: '',
    name_fr: '',
    name_en: '',
    logo: '',
    phone: '',
    email: '',
    website: '',
    tracking_url_template: '',
    volumetric_divisor: 5000,
    included_weight: 5,
    additional_price_per_kg: 0,
    is_active: true
  });

  const l = {
    ar: {
      shippingCompanies: 'شركات الشحن',
      allCompanies: 'جميع شركات الشحن',
      addCompany: 'إضافة شركة شحن',
      editCompany: 'تعديل شركة الشحن',
      nameAr: 'الاسم بالعربية',
      nameFr: 'الاسم بالفرنسية',
      nameEn: 'الاسم بالإنجليزية',
      logo: 'شعار الشركة',
      logoUrl: 'رابط الشعار',
      phone: 'الهاتف',
      email: 'البريد الإلكتروني',
      website: 'الموقع الإلكتروني',
      trackingUrlTemplate: 'رابط تتبع الشحنة',
      volumetricDivisor: 'معامل الحجم',
      includedWeight: 'الوزن المشمول (كغ)',
      extraPerKg: 'سعر الكيلو الإضافي (دج)',
      isActive: 'نشطة',
      active: 'نشطة',
      inactive: 'غير نشطة',
      manageRates: 'إدارة الأسعار',
      save: 'حفظ',
      cancel: 'إلغاء',
      delete: 'حذف',
      deleteConfirm: 'هل أنت متأكد من حذف شركة الشحن هذه؟',
      deleteWarning: 'سيتم حذف جميع الأسعار المرتبطة بهذه الشركة',
      noCompanies: 'لا توجد شركات شحن',
      companySaved: 'تم حفظ شركة الشحن بنجاح',
      companyDeleted: 'تم حذف شركة الشحن بنجاح',
      error: 'حدث خطأ'
    },
    fr: {
      shippingCompanies: 'Societes de livraison',
      allCompanies: 'Toutes les societes',
      addCompany: 'Ajouter une societe',
      editCompany: 'Modifier la societe',
      nameAr: 'Nom en arabe',
      nameFr: 'Nom en francais',
      nameEn: 'Nom en anglais',
      logo: 'Logo de la societe',
      logoUrl: "URL du logo",
      phone: 'Telephone',
      email: 'Email',
      website: 'Site web',
      trackingUrlTemplate: 'URL de suivi',
      volumetricDivisor: 'Diviseur volumetrique',
      includedWeight: 'Poids inclus (kg)',
      extraPerKg: 'Prix/kg supplémentaire (DA)',
      isActive: 'Active',
      active: 'Active',
      inactive: 'Inactive',
      manageRates: 'Gerer les tarifs',
      save: 'Enregistrer',
      cancel: 'Annuler',
      delete: 'Supprimer',
      deleteConfirm: 'Etes-vous sur de vouloir supprimer cette societe?',
      deleteWarning: 'Tous les tarifs associes seront supprimes',
      noCompanies: 'Aucune societe de livraison',
      companySaved: 'Societe enregistree',
      companyDeleted: 'Societe supprimee',
      error: 'Erreur'
    },
    en: {
      shippingCompanies: 'Shipping Companies',
      allCompanies: 'All Companies',
      addCompany: 'Add Company',
      editCompany: 'Edit Company',
      nameAr: 'Name in Arabic',
      nameFr: 'Name in French',
      nameEn: 'Name in English',
      logo: 'Company Logo',
      logoUrl: 'Logo URL',
      phone: 'Phone',
      email: 'Email',
      website: 'Website',
      trackingUrlTemplate: 'Tracking URL Template',
      volumetricDivisor: 'Volumetric Divisor',
      includedWeight: 'Included Weight (kg)',
      extraPerKg: 'Extra per KG (DZD)',
      isActive: 'Active',
      active: 'Active',
      inactive: 'Inactive',
      manageRates: 'Manage Rates',
      save: 'Save',
      cancel: 'Cancel',
      delete: 'Delete',
      deleteConfirm: 'Are you sure you want to delete this shipping company?',
      deleteWarning: 'All associated rates will be deleted',
      noCompanies: 'No shipping companies found',
      companySaved: 'Shipping company saved successfully',
      companyDeleted: 'Shipping company deleted successfully',
      error: 'Error occurred'
    }
  };

  const text = l[language] || l.ar;

  useEffect(() => {
    fetchCompanies();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const fetchCompanies = async () => {
    try {
      setLoading(true);
      const res = await axios.get(`${API}/shipping/companies`, { withCredentials: true });
      setCompanies(res.data || []);
    } catch (error) {
      console.error('Error fetching shipping companies:', error);
      toast.error(text.error);
    } finally {
      setLoading(false);
    }
  };

  const openAddDialog = () => {
    setFormData({
      name_ar: '',
      name_fr: '',
      name_en: '',
      logo: '',
      phone: '',
      email: '',
      website: '',
      tracking_url_template: '',
      volumetric_divisor: 5000,
      included_weight: 5,
      additional_price_per_kg: 0,
      is_active: true
    });
    setEditDialog({ open: true, company: null });
  };

  const openEditDialog = (company) => {
    setFormData({
      name_ar: company.name_ar || '',
      name_fr: company.name_fr || '',
      name_en: company.name_en || '',
      logo: company.logo || '',
      phone: company.phone || '',
      email: company.email || '',
      website: company.website || '',
      tracking_url_template: company.tracking_url_template || '',
      volumetric_divisor: company.volumetric_divisor || 5000,
      included_weight: company.included_weight ?? 5,
      additional_price_per_kg: company.additional_price_per_kg ?? 0,
      is_active: company.is_active ?? true
    });
    setEditDialog({ open: true, company });
  };

  const handleSave = async () => {
    if (!formData.name_ar && !formData.name_fr) {
      toast.error(language === 'ar' ? 'يرجى إدخال اسم الشركة' : 'Please enter company name');
      return;
    }

    try {
      setSaving(true);

      if (editDialog.company) {
        await axios.put(
          `${API}/shipping/companies/${editDialog.company.company_id}`,
          formData,
          { withCredentials: true }
        );
      } else {
        await axios.post(`${API}/shipping/companies`, formData, { withCredentials: true });
      }

      toast.success(text.companySaved);
      setEditDialog({ open: false, company: null });
      fetchCompanies();
    } catch (error) {
      console.error('Error saving shipping company:', error);
      toast.error(text.error);
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async () => {
    if (!deleteDialog.company) return;

    try {
      await axios.delete(
        `${API}/shipping/companies/${deleteDialog.company.company_id}`,
        { withCredentials: true }
      );
      toast.success(text.companyDeleted);
      setDeleteDialog({ open: false, company: null });
      fetchCompanies();
    } catch (error) {
      console.error('Error deleting shipping company:', error);
      toast.error(text.error);
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold">{text.shippingCompanies}</h1>
          <p className="text-muted-foreground">{companies.length} {text.allCompanies}</p>
        </div>
        <div className="flex items-center gap-2">
          <Button size="sm" onClick={openAddDialog}>
            <Plus className="h-4 w-4 me-2" />
            {text.addCompany}
          </Button>
        </div>
      </div>

      {/* Companies Grid */}
      {loading ? (
        <div className="flex items-center justify-center h-64">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary" />
        </div>
      ) : companies.length === 0 ? (
        <Card>
          <CardContent className="flex flex-col items-center justify-center h-64 text-muted-foreground">
            <Truck className="h-12 w-12 mb-4 opacity-50" />
            <p>{text.noCompanies}</p>
            <Button variant="outline" className="mt-4" onClick={openAddDialog}>
              <Plus className="h-4 w-4 me-2" />
              {text.addCompany}
            </Button>
          </CardContent>
        </Card>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          {companies.map(company => (
            <Card key={company.company_id} className="overflow-hidden">
              <CardHeader className="pb-3">
                <div className="flex items-start justify-between">
                  <div className="flex items-center gap-3">
                    {company.logo ? (
                      <img
                        src={company.logo}
                        alt={company[`name_${language}`] || company.name_ar}
                        className="h-12 w-12 rounded-lg object-contain border"
                      />
                    ) : (
                      <div className="h-12 w-12 rounded-lg bg-muted flex items-center justify-center">
                        <Truck className="h-6 w-6 text-muted-foreground" />
                      </div>
                    )}
                    <div>
                      <CardTitle className="text-base">
                        {company[`name_${language}`] || company.name_ar}
                      </CardTitle>
                      {company.phone && (
                        <p className="text-sm text-muted-foreground mt-1">{company.phone}</p>
                      )}
                    </div>
                  </div>
                  <span className={`inline-flex px-2 py-1 rounded-full text-xs font-medium ${
                    company.is_active
                      ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                      : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'
                  }`}>
                    {company.is_active ? text.active : text.inactive}
                  </span>
                </div>
              </CardHeader>
              <CardContent className="pt-0 space-y-3">
                <div className="text-sm text-muted-foreground space-y-1">
                  <div>{text.includedWeight}: <span className="font-medium text-foreground">{company.included_weight ?? 5} kg</span></div>
                  <div>{text.extraPerKg}: <span className="font-medium text-foreground">{company.additional_price_per_kg ?? 0} DZD</span></div>
                </div>

                <div className="flex items-center gap-2 pt-2 border-t">
                  <Button
                    variant="outline"
                    size="sm"
                    className="flex-1"
                    onClick={() => navigate(`/admin/shipping/${company.company_id}/rates`)}
                  >
                    <DollarSign className="h-4 w-4 me-1" />
                    {text.manageRates}
                  </Button>
                  <Button
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8"
                    onClick={() => openEditDialog(company)}
                  >
                    <Pencil className="h-4 w-4" />
                  </Button>
                  <Button
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8 text-destructive hover:text-destructive"
                    onClick={() => setDeleteDialog({ open: true, company })}
                  >
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}

      {/* Add/Edit Dialog */}
      <Dialog open={editDialog.open} onOpenChange={() => setEditDialog({ open: false, company: null })}>
        <DialogContent className="max-w-lg max-h-[90vh] overflow-y-auto">
          <DialogHeader>
            <DialogTitle>
              {editDialog.company ? text.editCompany : text.addCompany}
            </DialogTitle>
          </DialogHeader>

          <div className="space-y-4 py-4">
            <div className="space-y-2">
              <Label>{text.nameAr}</Label>
              <Input
                value={formData.name_ar}
                onChange={(e) => setFormData(prev => ({ ...prev, name_ar: e.target.value }))}
                dir="rtl"
              />
            </div>
            <div className="space-y-2">
              <Label>{text.nameFr}</Label>
              <Input
                value={formData.name_fr}
                onChange={(e) => setFormData(prev => ({ ...prev, name_fr: e.target.value }))}
              />
            </div>
            <div className="space-y-2">
              <Label>{text.nameEn}</Label>
              <Input
                value={formData.name_en}
                onChange={(e) => setFormData(prev => ({ ...prev, name_en: e.target.value }))}
              />
            </div>
            <div className="space-y-2">
              <Label>{text.logo}</Label>
              <Input
                value={formData.logo}
                onChange={(e) => setFormData(prev => ({ ...prev, logo: e.target.value }))}
                placeholder={text.logoUrl}
                dir="ltr"
              />
              {formData.logo && (
                <img
                  src={formData.logo}
                  alt="Logo preview"
                  className="h-16 w-16 object-contain rounded-lg border mt-2"
                />
              )}
            </div>
            <div className="space-y-2">
              <Label>{text.phone}</Label>
              <Input
                value={formData.phone}
                onChange={(e) => setFormData(prev => ({ ...prev, phone: e.target.value }))}
                dir="ltr"
              />
            </div>
            <div className="space-y-2">
              <Label>{text.email}</Label>
              <Input
                type="email"
                value={formData.email}
                onChange={(e) => setFormData(prev => ({ ...prev, email: e.target.value }))}
                dir="ltr"
              />
            </div>
            <div className="space-y-2">
              <Label>{text.website}</Label>
              <Input
                value={formData.website}
                onChange={(e) => setFormData(prev => ({ ...prev, website: e.target.value }))}
                placeholder="https://"
                dir="ltr"
              />
            </div>
            <div className="space-y-2">
              <Label>{text.trackingUrlTemplate}</Label>
              <Input
                value={formData.tracking_url_template}
                onChange={(e) => setFormData(prev => ({ ...prev, tracking_url_template: e.target.value }))}
                placeholder="https://track.example.com/{tracking_number}"
                dir="ltr"
              />
            </div>
            <div className="space-y-2">
              <Label>{text.volumetricDivisor}</Label>
              <Input
                type="number"
                value={formData.volumetric_divisor}
                onChange={(e) => setFormData(prev => ({ ...prev, volumetric_divisor: Number(e.target.value) }))}
                dir="ltr"
              />
            </div>
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label>{text.includedWeight}</Label>
                <Input
                  type="number"
                  min="0"
                  step="0.5"
                  value={formData.included_weight}
                  onChange={(e) => setFormData(prev => ({ ...prev, included_weight: Number(e.target.value) }))}
                  dir="ltr"
                />
              </div>
              <div className="space-y-2">
                <Label>{text.extraPerKg}</Label>
                <Input
                  type="number"
                  min="0"
                  value={formData.additional_price_per_kg}
                  onChange={(e) => setFormData(prev => ({ ...prev, additional_price_per_kg: Number(e.target.value) }))}
                  dir="ltr"
                />
              </div>
            </div>
            <div className="flex items-center justify-between">
              <Label>{text.isActive}</Label>
              <Switch
                checked={formData.is_active}
                onCheckedChange={(checked) => setFormData(prev => ({ ...prev, is_active: checked }))}
              />
            </div>
          </div>

          <DialogFooter>
            <Button variant="outline" onClick={() => setEditDialog({ open: false, company: null })}>
              {text.cancel}
            </Button>
            <Button onClick={handleSave} disabled={saving}>
              {saving ? (
                <>
                  <Loader2 className="h-4 w-4 me-2 animate-spin" />
                  {language === 'ar' ? 'جاري الحفظ...' : 'Saving...'}
                </>
              ) : (
                text.save
              )}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Delete Confirmation Dialog */}
      <Dialog open={deleteDialog.open} onOpenChange={() => setDeleteDialog({ open: false, company: null })}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <Trash2 className="h-5 w-5 text-red-500" />
              {text.delete}
            </DialogTitle>
            <DialogDescription>
              {text.deleteConfirm}
              <br />
              <span className="text-red-500">{text.deleteWarning}</span>
            </DialogDescription>
          </DialogHeader>

          {deleteDialog.company && (
            <div className="flex items-center gap-3 p-3 bg-muted rounded-lg">
              {deleteDialog.company.logo ? (
                <img
                  src={deleteDialog.company.logo}
                  alt=""
                  className="h-12 w-12 rounded-lg object-contain border"
                />
              ) : (
                <div className="h-12 w-12 rounded-lg bg-muted-foreground/20 flex items-center justify-center">
                  <Truck className="h-6 w-6 text-muted-foreground" />
                </div>
              )}
              <div>
                <p className="font-medium">
                  {deleteDialog.company[`name_${language}`] || deleteDialog.company.name_ar}
                </p>
                {deleteDialog.company.phone && (
                  <p className="text-sm text-muted-foreground">{deleteDialog.company.phone}</p>
                )}
              </div>
            </div>
          )}

          <DialogFooter>
            <Button variant="outline" onClick={() => setDeleteDialog({ open: false, company: null })}>
              {text.cancel}
            </Button>
            <Button variant="destructive" onClick={handleDelete}>
              {text.delete}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default ShippingCompaniesPage;
