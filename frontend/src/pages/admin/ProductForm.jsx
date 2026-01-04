import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  ArrowLeft,
  ArrowRight,
  Save,
  X,
  Upload,
  Image as ImageIcon,
  Trash2,
  Plus,
  Video,
  Loader2,
  Tag
} from 'lucide-react';
import { toast } from 'sonner';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const ProductForm = () => {
  const { productId } = useParams();
  const navigate = useNavigate();
  const { language, isRTL } = useLanguage();
  const isEdit = !!productId && productId !== 'new';
  const ArrowIcon = isRTL ? ArrowRight : ArrowLeft;

  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [categories, setCategories] = useState([]);
  const [formData, setFormData] = useState({
    name_ar: '',
    name_fr: '',
    name_en: '',
    description_ar: '',
    description_fr: '',
    description_en: '',
    price: '',
    old_price: '',
    stock: '',
    category_id: '',
    images: [],
    video: '',
    featured: false,
    unit: 'piece',
    // Discount fields
    discount_percent: '',
    discount_start: '',
    discount_end: ''
  });
  const [imageUrl, setImageUrl] = useState('');
  const [discountEnabled, setDiscountEnabled] = useState(false);

  const l = {
    ar: {
      addProduct: 'إضافة منتج جديد',
      editProduct: 'تعديل المنتج',
      back: 'رجوع',
      save: 'حفظ',
      saving: 'جاري الحفظ...',
      basicInfo: 'المعلومات الأساسية',
      nameAr: 'الاسم بالعربية',
      nameFr: 'الاسم بالفرنسية',
      nameEn: 'الاسم بالإنجليزية',
      descriptionAr: 'الوصف بالعربية',
      descriptionFr: 'الوصف بالفرنسية',
      descriptionEn: 'الوصف بالإنجليزية',
      pricing: 'التسعير',
      price: 'السعر',
      oldPrice: 'السعر القديم (اختياري)',
      inventory: 'المخزون',
      stock: 'الكمية',
      unit: 'الوحدة',
      piece: 'قطعة',
      kg: 'كيلوجرام',
      pack: 'عبوة',
      liter: 'لتر',
      category: 'التصنيف',
      selectCategory: 'اختر التصنيف',
      featured: 'منتج مميز',
      images: 'الصور',
      addImage: 'إضافة صورة',
      imageUrl: 'رابط الصورة',
      video: 'رابط الفيديو (اختياري)',
      productSaved: 'تم حفظ المنتج بنجاح',
      error: 'حدث خطأ',
      // Discount translations
      discount: 'الخصم والعروض',
      enableDiscount: 'تفعيل الخصم',
      discountPercent: 'نسبة الخصم (%)',
      discountStart: 'تاريخ بداية الخصم',
      discountEnd: 'تاريخ نهاية الخصم',
      priceAfterDiscount: 'السعر بعد الخصم',
      discountNote: 'سيتم تطبيق الخصم تلقائياً في الفترة المحددة'
    },
    fr: {
      addProduct: 'Ajouter un produit',
      editProduct: 'Modifier le produit',
      back: 'Retour',
      save: 'Enregistrer',
      saving: 'Enregistrement...',
      basicInfo: 'Informations de base',
      nameAr: 'Nom en arabe',
      nameFr: 'Nom en français',
      nameEn: 'Nom en anglais',
      descriptionAr: 'Description en arabe',
      descriptionFr: 'Description en français',
      descriptionEn: 'Description en anglais',
      pricing: 'Tarification',
      price: 'Prix',
      oldPrice: 'Ancien prix (optionnel)',
      inventory: 'Inventaire',
      stock: 'Quantité',
      unit: 'Unité',
      piece: 'Pièce',
      kg: 'Kilogramme',
      pack: 'Pack',
      liter: 'Litre',
      category: 'Catégorie',
      selectCategory: 'Sélectionner',
      featured: 'Produit vedette',
      images: 'Images',
      addImage: 'Ajouter une image',
      imageUrl: 'URL de l\'image',
      video: 'URL vidéo (optionnel)',
      productSaved: 'Produit enregistré',
      error: 'Erreur',
      // Discount translations
      discount: 'Remise et offres',
      enableDiscount: 'Activer la remise',
      discountPercent: 'Pourcentage de remise (%)',
      discountStart: 'Date de début',
      discountEnd: 'Date de fin',
      priceAfterDiscount: 'Prix après remise',
      discountNote: 'La remise sera appliquée automatiquement pendant la période'
    },
    en: {
      addProduct: 'Add New Product',
      editProduct: 'Edit Product',
      back: 'Back',
      save: 'Save',
      saving: 'Saving...',
      basicInfo: 'Basic Information',
      nameAr: 'Name in Arabic',
      nameFr: 'Name in French',
      nameEn: 'Name in English',
      descriptionAr: 'Description in Arabic',
      descriptionFr: 'Description in French',
      descriptionEn: 'Description in English',
      pricing: 'Pricing',
      price: 'Price',
      oldPrice: 'Old Price (optional)',
      inventory: 'Inventory',
      stock: 'Quantity',
      unit: 'Unit',
      piece: 'Piece',
      kg: 'Kilogram',
      pack: 'Pack',
      liter: 'Liter',
      category: 'Category',
      selectCategory: 'Select category',
      featured: 'Featured Product',
      images: 'Images',
      addImage: 'Add Image',
      imageUrl: 'Image URL',
      video: 'Video URL (optional)',
      productSaved: 'Product saved successfully',
      error: 'Error occurred',
      // Discount translations
      discount: 'Discount & Offers',
      enableDiscount: 'Enable Discount',
      discountPercent: 'Discount Percentage (%)',
      discountStart: 'Start Date',
      discountEnd: 'End Date',
      priceAfterDiscount: 'Price After Discount',
      discountNote: 'Discount will be applied automatically during the period'
    }
  };

  const text = l[language] || l.ar;

  useEffect(() => {
    fetchCategories();
    if (isEdit) {
      fetchProduct();
    }
  }, [productId]);

  const fetchCategories = async () => {
    try {
      const res = await axios.get(`${API}/categories`);
      setCategories(res.data || []);
    } catch (error) {
      console.error('Error fetching categories:', error);
    }
  };

  const fetchProduct = async () => {
    try {
      setLoading(true);
      const res = await axios.get(`${API}/products/${productId}`);
      const product = res.data;
      setFormData({
        name_ar: product.name_ar || '',
        name_fr: product.name_fr || '',
        name_en: product.name_en || '',
        description_ar: product.description_ar || '',
        description_fr: product.description_fr || '',
        description_en: product.description_en || '',
        price: product.price?.toString() || '',
        old_price: product.old_price?.toString() || '',
        stock: product.stock?.toString() || '',
        category_id: product.category_id || '',
        images: product.images || [],
        video: product.video || '',
        featured: product.featured || false,
        unit: product.unit || 'piece',
        discount_percent: product.discount_percent?.toString() || '',
        discount_start: product.discount_start ? product.discount_start.split('T')[0] : '',
        discount_end: product.discount_end ? product.discount_end.split('T')[0] : ''
      });
      // Enable discount toggle if product has discount
      if (product.discount_percent && product.discount_percent > 0) {
        setDiscountEnabled(true);
      }
    } catch (error) {
      console.error('Error fetching product:', error);
      toast.error(text.error);
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (field, value) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const addImage = () => {
    if (imageUrl && imageUrl.trim()) {
      setFormData(prev => ({
        ...prev,
        images: [...prev.images, imageUrl.trim()]
      }));
      setImageUrl('');
    }
  };

  const handleFileUpload = async (event) => {
    const files = event.target.files;
    if (!files || files.length === 0) return;

    setUploading(true);
    
    try {
      for (const file of files) {
        const formDataFile = new FormData();
        formDataFile.append('file', file);
        
        const response = await axios.post(`${API}/upload/image`, formDataFile, {
          withCredentials: true,
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        if (response.data?.url) {
          // Convert relative URL to full URL
          const fullUrl = response.data.url.startsWith('http') 
            ? response.data.url 
            : `${process.env.REACT_APP_BACKEND_URL}${response.data.url}`;
          
          setFormData(prev => ({
            ...prev,
            images: [...prev.images, fullUrl]
          }));
        }
      }
      toast.success(language === 'ar' ? 'تم رفع الصورة بنجاح' : 'Image uploaded successfully');
    } catch (error) {
      console.error('Error uploading image:', error);
      toast.error(language === 'ar' ? 'خطأ في رفع الصورة' : 'Error uploading image');
    } finally {
      setUploading(false);
      // Reset file input
      event.target.value = '';
    }
  };

  const removeImage = (index) => {
    setFormData(prev => ({
      ...prev,
      images: prev.images.filter((_, i) => i !== index)
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    try {
      setSaving(true);
      
      const payload = {
        ...formData,
        price: parseFloat(formData.price) || 0,
        old_price: formData.old_price ? parseFloat(formData.old_price) : null,
        stock: parseInt(formData.stock) || 0,
        // Discount fields
        discount_percent: discountEnabled && formData.discount_percent ? parseInt(formData.discount_percent) : null,
        discount_start: discountEnabled && formData.discount_start ? formData.discount_start + 'T00:00:00Z' : null,
        discount_end: discountEnabled && formData.discount_end ? formData.discount_end + 'T23:59:59Z' : null
      };

      if (isEdit) {
        await axios.put(`${API}/products/${productId}`, payload, { withCredentials: true });
      } else {
        await axios.post(`${API}/products`, payload, { withCredentials: true });
      }

      toast.success(text.productSaved);
      navigate('/admin/products');
    } catch (error) {
      console.error('Error saving product:', error);
      toast.error(text.error);
    } finally {
      setSaving(false);
    }
  };

  // Calculate price after discount
  const priceAfterDiscount = formData.price && formData.discount_percent
    ? (parseFloat(formData.price) * (1 - parseInt(formData.discount_percent) / 100)).toFixed(2)
    : null;

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
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-4">
          <Button variant="ghost" size="icon" onClick={() => navigate('/admin/products')}>
            <ArrowIcon className="h-5 w-5" />
          </Button>
          <div>
            <h1 className="text-2xl font-bold">
              {isEdit ? text.editProduct : text.addProduct}
            </h1>
          </div>
        </div>
        <Button onClick={handleSubmit} disabled={saving}>
          {saving ? (
            <>
              <Loader2 className="h-4 w-4 me-2 animate-spin" />
              {text.saving}
            </>
          ) : (
            <>
              <Save className="h-4 w-4 me-2" />
              {text.save}
            </>
          )}
        </Button>
      </div>

      <form onSubmit={handleSubmit} className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Main Content */}
        <div className="lg:col-span-2 space-y-6">
          {/* Basic Info */}
          <Card>
            <CardHeader>
              <CardTitle>{text.basicInfo}</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="space-y-2">
                  <Label>{text.nameAr}</Label>
                  <Input
                    value={formData.name_ar}
                    onChange={(e) => handleChange('name_ar', e.target.value)}
                    dir="rtl"
                    required
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.nameFr}</Label>
                  <Input
                    value={formData.name_fr}
                    onChange={(e) => handleChange('name_fr', e.target.value)}
                    required
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.nameEn}</Label>
                  <Input
                    value={formData.name_en}
                    onChange={(e) => handleChange('name_en', e.target.value)}
                    required
                  />
                </div>
              </div>

              <div className="space-y-2">
                <Label>{text.descriptionAr}</Label>
                <Textarea
                  value={formData.description_ar}
                  onChange={(e) => handleChange('description_ar', e.target.value)}
                  rows={3}
                  dir="rtl"
                />
              </div>
              <div className="space-y-2">
                <Label>{text.descriptionFr}</Label>
                <Textarea
                  value={formData.description_fr}
                  onChange={(e) => handleChange('description_fr', e.target.value)}
                  rows={3}
                />
              </div>
              <div className="space-y-2">
                <Label>{text.descriptionEn}</Label>
                <Textarea
                  value={formData.description_en}
                  onChange={(e) => handleChange('description_en', e.target.value)}
                  rows={3}
                />
              </div>
            </CardContent>
          </Card>

          {/* Images */}
          <Card>
            <CardHeader>
              <CardTitle>{text.images}</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {/* Image List */}
              <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                {formData.images.map((img, idx) => (
                  <div key={idx} className="relative group aspect-square rounded-lg overflow-hidden border">
                    <img src={img} alt="" className="w-full h-full object-cover" />
                    <button
                      type="button"
                      onClick={() => removeImage(idx)}
                      className="absolute top-2 end-2 h-6 w-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <X className="h-4 w-4" />
                    </button>
                    {idx === 0 && (
                      <span className="absolute bottom-2 start-2 px-2 py-0.5 bg-primary text-white text-xs rounded">
                        Main
                      </span>
                    )}
                  </div>
                ))}
              </div>

              {/* Add Image */}
              <div className="space-y-3">
                {/* Upload from Desktop */}
                <div className="border-2 border-dashed rounded-lg p-4 text-center hover:border-primary/50 transition-colors">
                  <input
                    type="file"
                    accept="image/*"
                    multiple
                    onChange={handleFileUpload}
                    className="hidden"
                    id="image-upload"
                    disabled={uploading}
                  />
                  <label htmlFor="image-upload" className="cursor-pointer">
                    {uploading ? (
                      <div className="flex flex-col items-center gap-2">
                        <Loader2 className="h-8 w-8 animate-spin text-primary" />
                        <span className="text-sm text-muted-foreground">
                          {language === 'ar' ? 'جاري الرفع...' : 'Uploading...'}
                        </span>
                      </div>
                    ) : (
                      <div className="flex flex-col items-center gap-2">
                        <Upload className="h-8 w-8 text-muted-foreground" />
                        <span className="text-sm font-medium">
                          {language === 'ar' ? 'اضغط لرفع الصور من جهازك' : 'Click to upload images'}
                        </span>
                        <span className="text-xs text-muted-foreground">
                          PNG, JPG, WebP (Max 5MB)
                        </span>
                      </div>
                    )}
                  </label>
                </div>

                {/* Or add by URL */}
                <div className="flex items-center gap-4">
                  <div className="flex-1 h-px bg-border" />
                  <span className="text-xs text-muted-foreground">
                    {language === 'ar' ? 'أو أضف رابط' : 'or add URL'}
                  </span>
                  <div className="flex-1 h-px bg-border" />
                </div>

                <div className="flex gap-2">
                  <Input
                    placeholder={text.imageUrl}
                    value={imageUrl}
                    onChange={(e) => setImageUrl(e.target.value)}
                    className="flex-1"
                  />
                  <Button type="button" variant="outline" onClick={addImage}>
                    <Plus className="h-4 w-4 me-2" />
                    {text.addImage}
                  </Button>
                </div>
              </div>

              {/* Video URL */}
              <div className="space-y-2">
                <Label className="flex items-center gap-2">
                  <Video className="h-4 w-4" />
                  {text.video}
                </Label>
                <Input
                  value={formData.video}
                  onChange={(e) => handleChange('video', e.target.value)}
                  placeholder="https://..."
                />
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          {/* Pricing */}
          <Card>
            <CardHeader>
              <CardTitle>{text.pricing}</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label>{text.price} (DZD)</Label>
                <Input
                  type="number"
                  min="0"
                  step="0.01"
                  value={formData.price}
                  onChange={(e) => handleChange('price', e.target.value)}
                  required
                />
              </div>
              <div className="space-y-2">
                <Label>{text.oldPrice} (DZD)</Label>
                <Input
                  type="number"
                  min="0"
                  step="0.01"
                  value={formData.old_price}
                  onChange={(e) => handleChange('old_price', e.target.value)}
                />
              </div>
            </CardContent>
          </Card>

          {/* Discount & Offers */}
          <Card className="border-orange-200 dark:border-orange-800">
            <CardHeader className="pb-3">
              <div className="flex items-center justify-between">
                <CardTitle className="flex items-center gap-2 text-orange-600">
                  <Tag className="h-5 w-5" />
                  {text.discount}
                </CardTitle>
                <Switch
                  checked={discountEnabled}
                  onCheckedChange={setDiscountEnabled}
                />
              </div>
            </CardHeader>
            {discountEnabled && (
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label>{text.discountPercent}</Label>
                  <div className="relative">
                    <Input
                      type="number"
                      min="1"
                      max="99"
                      value={formData.discount_percent}
                      onChange={(e) => handleChange('discount_percent', e.target.value)}
                      className="pe-12"
                      placeholder="20"
                    />
                    <span className="absolute end-3 top-1/2 -translate-y-1/2 text-muted-foreground font-medium">
                      %
                    </span>
                  </div>
                </div>
                
                <div className="grid grid-cols-2 gap-3">
                  <div className="space-y-2">
                    <Label className="text-xs">{text.discountStart}</Label>
                    <Input
                      type="date"
                      value={formData.discount_start}
                      onChange={(e) => handleChange('discount_start', e.target.value)}
                    />
                  </div>
                  <div className="space-y-2">
                    <Label className="text-xs">{text.discountEnd}</Label>
                    <Input
                      type="date"
                      value={formData.discount_end}
                      onChange={(e) => handleChange('discount_end', e.target.value)}
                    />
                  </div>
                </div>

                {/* Price Preview */}
                {priceAfterDiscount && formData.price && (
                  <div className="p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg border border-orange-200 dark:border-orange-800">
                    <p className="text-xs text-muted-foreground mb-1">{text.priceAfterDiscount}</p>
                    <div className="flex items-center gap-2">
                      <span className="text-lg font-bold text-orange-600">
                        {parseFloat(priceAfterDiscount).toLocaleString()} DZD
                      </span>
                      <span className="text-sm text-muted-foreground line-through">
                        {parseFloat(formData.price).toLocaleString()} DZD
                      </span>
                      <span className="px-2 py-0.5 bg-orange-600 text-white text-xs font-bold rounded">
                        -{formData.discount_percent}%
                      </span>
                    </div>
                  </div>
                )}

                <p className="text-xs text-muted-foreground">
                  {text.discountNote}
                </p>
              </CardContent>
            )}
          </Card>

          {/* Inventory */}
          <Card>
            <CardHeader>
              <CardTitle>{text.inventory}</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label>{text.stock}</Label>
                <Input
                  type="number"
                  min="0"
                  value={formData.stock}
                  onChange={(e) => handleChange('stock', e.target.value)}
                  required
                />
              </div>
              <div className="space-y-2">
                <Label>{text.unit}</Label>
                <Select value={formData.unit} onValueChange={(v) => handleChange('unit', v)}>
                  <SelectTrigger>
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="piece">{text.piece}</SelectItem>
                    <SelectItem value="kg">{text.kg}</SelectItem>
                    <SelectItem value="pack">{text.pack}</SelectItem>
                    <SelectItem value="liter">{text.liter}</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </CardContent>
          </Card>

          {/* Category */}
          <Card>
            <CardHeader>
              <CardTitle>{text.category}</CardTitle>
            </CardHeader>
            <CardContent>
              <Select value={formData.category_id} onValueChange={(v) => handleChange('category_id', v)}>
                <SelectTrigger>
                  <SelectValue placeholder={text.selectCategory} />
                </SelectTrigger>
                <SelectContent>
                  {categories.map(cat => (
                    <SelectItem key={cat.category_id} value={cat.category_id}>
                      {cat[`name_${language}`] || cat.name_ar}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </CardContent>
          </Card>

          {/* Featured */}
          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <Label>{text.featured}</Label>
                <Switch
                  checked={formData.featured}
                  onCheckedChange={(checked) => handleChange('featured', checked)}
                />
              </div>
            </CardContent>
          </Card>
        </div>
      </form>
    </div>
  );
};

export default ProductForm;
