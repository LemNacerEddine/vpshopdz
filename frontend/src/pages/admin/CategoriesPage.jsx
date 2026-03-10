import React, { useState, useEffect, useRef } from 'react';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
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
  Search,
  Edit,
  Trash2,
  Folder,
  RefreshCw,
  AlertCircle,
  Loader2,
  Image as ImageIcon,
  Link as LinkIcon,
  Upload,
  X
} from 'lucide-react';
import { toast } from 'sonner';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const CategoriesPage = () => {
  const { language, isRTL } = useLanguage();
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [search, setSearch] = useState('');
  const [editDialog, setEditDialog] = useState({ open: false, category: null });
  const [deleteDialog, setDeleteDialog] = useState({ open: false, category: null });
  const [imageTab, setImageTab] = useState('url');
  const fileInputRef = useRef(null);
  const [formData, setFormData] = useState({
    name_ar: '',
    name_fr: '',
    name_en: '',
    image: '',
    parent_id: ''
  });

  const l = {
    ar: {
      categories: 'التصنيفات',
      allCategories: 'جميع التصنيفات',
      addCategory: 'إضافة تصنيف',
      editCategory: 'تعديل التصنيف',
      search: 'بحث عن تصنيف...',
      nameAr: 'الاسم بالعربية',
      nameFr: 'الاسم بالفرنسية',
      nameEn: 'الاسم بالإنجليزية',
      image: 'صورة التصنيف',
      imageUrl: 'رابط الصورة',
      uploadImage: 'رفع من الجهاز',
      urlTab: 'رابط',
      uploadTab: 'رفع ملف',
      enterUrl: 'أدخل رابط الصورة',
      selectFile: 'اختر ملف',
      dragDrop: 'أو اسحب وأفلت هنا',
      supportedFormats: 'PNG, JPG, WEBP (حد أقصى 5MB)',
      products: 'المنتجات',
      save: 'حفظ',
      cancel: 'إلغاء',
      delete: 'حذف',
      deleteConfirm: 'هل أنت متأكد من حذف هذا التصنيف؟',
      deleteWarning: 'سيتم حذف جميع المنتجات المرتبطة بهذا التصنيف',
      refresh: 'تحديث',
      noCategories: 'لا توجد تصنيفات',
      categorySaved: 'تم حفظ التصنيف بنجاح',
      categoryDeleted: 'تم حذف التصنيف بنجاح',
      error: 'حدث خطأ',
      uploadSuccess: 'تم رفع الصورة بنجاح',
      uploadError: 'خطأ في رفع الصورة',
      removeImage: 'إزالة الصورة'
    },
    fr: {
      categories: 'Catégories',
      allCategories: 'Toutes les catégories',
      addCategory: 'Ajouter une catégorie',
      editCategory: 'Modifier la catégorie',
      search: 'Rechercher une catégorie...',
      nameAr: 'Nom en arabe',
      nameFr: 'Nom en français',
      nameEn: 'Nom en anglais',
      image: 'Image de la catégorie',
      imageUrl: 'URL de l\'image',
      uploadImage: 'Télécharger depuis l\'appareil',
      urlTab: 'Lien',
      uploadTab: 'Télécharger',
      enterUrl: 'Entrez l\'URL de l\'image',
      selectFile: 'Sélectionner un fichier',
      dragDrop: 'ou glisser-déposer ici',
      supportedFormats: 'PNG, JPG, WEBP (max 5MB)',
      products: 'Produits',
      save: 'Enregistrer',
      cancel: 'Annuler',
      delete: 'Supprimer',
      deleteConfirm: 'Êtes-vous sûr de vouloir supprimer cette catégorie?',
      deleteWarning: 'Tous les produits associés seront supprimés',
      refresh: 'Actualiser',
      noCategories: 'Aucune catégorie',
      categorySaved: 'Catégorie enregistrée',
      categoryDeleted: 'Catégorie supprimée',
      error: 'Erreur',
      uploadSuccess: 'Image téléchargée',
      uploadError: 'Erreur de téléchargement',
      removeImage: 'Supprimer l\'image'
    },
    en: {
      categories: 'Categories',
      allCategories: 'All Categories',
      addCategory: 'Add Category',
      editCategory: 'Edit Category',
      search: 'Search categories...',
      nameAr: 'Name in Arabic',
      nameFr: 'Name in French',
      nameEn: 'Name in English',
      image: 'Category Image',
      imageUrl: 'Image URL',
      uploadImage: 'Upload from device',
      urlTab: 'URL',
      uploadTab: 'Upload',
      enterUrl: 'Enter image URL',
      selectFile: 'Select file',
      dragDrop: 'or drag and drop here',
      supportedFormats: 'PNG, JPG, WEBP (max 5MB)',
      products: 'Products',
      save: 'Save',
      cancel: 'Cancel',
      delete: 'Delete',
      deleteConfirm: 'Are you sure you want to delete this category?',
      deleteWarning: 'All associated products will be deleted',
      refresh: 'Refresh',
      noCategories: 'No categories found',
      categorySaved: 'Category saved successfully',
      categoryDeleted: 'Category deleted successfully',
      error: 'Error occurred',
      uploadSuccess: 'Image uploaded successfully',
      uploadError: 'Error uploading image',
      removeImage: 'Remove image'
    }
  };

  const text = l[language] || l.ar;

  useEffect(() => {
    fetchCategories();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleFileUpload = async (file) => {
    if (!file) return;
    
    // Validate file type
    const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    if (!validTypes.includes(file.type)) {
      toast.error(language === 'ar' ? 'نوع الملف غير مدعوم' : 'Unsupported file type');
      return;
    }
    
    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
      toast.error(language === 'ar' ? 'حجم الملف كبير جداً (الحد الأقصى 5MB)' : 'File too large (max 5MB)');
      return;
    }

    try {
      setUploading(true);
      const formDataUpload = new FormData();
      formDataUpload.append('file', file);
      
      const response = await axios.post(`${API}/upload/image`, formDataUpload, {
        headers: { 'Content-Type': 'multipart/form-data' },
        withCredentials: true
      });
      
      setFormData(prev => ({ ...prev, image: response.data.url }));
      toast.success(text.uploadSuccess);
    } catch (error) {
      console.error('Error uploading image:', error);
      toast.error(text.uploadError);
    } finally {
      setUploading(false);
    }
  };

  const handleDrop = (e) => {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (file) handleFileUpload(file);
  };

  const handleDragOver = (e) => {
    e.preventDefault();
  };

  const fetchCategories = async () => {
    try {
      setLoading(true);
      const [categoriesRes, productsRes] = await Promise.all([
        axios.get(`${API}/categories`),
        axios.get(`${API}/products`)
      ]);
      
      // Count products per category
      const productCounts = {};
      (productsRes.data || []).forEach(product => {
        const catId = product.category_id;
        productCounts[catId] = (productCounts[catId] || 0) + 1;
      });
      
      const categoriesWithCount = (categoriesRes.data || []).map(cat => ({
        ...cat,
        productCount: productCounts[cat.category_id] || 0
      }));
      
      setCategories(categoriesWithCount);
    } catch (error) {
      console.error('Error fetching categories:', error);
      toast.error(text.error);
    } finally {
      setLoading(false);
    }
  };

  const filteredCategories = categories.filter(cat => {
    const searchMatch = !search ||
      cat.name_ar?.toLowerCase().includes(search.toLowerCase()) ||
      cat.name_fr?.toLowerCase().includes(search.toLowerCase()) ||
      cat.name_en?.toLowerCase().includes(search.toLowerCase());
    return searchMatch;
  });

  const openAddDialog = () => {
    setFormData({
      name_ar: '',
      name_fr: '',
      name_en: '',
      image: '',
      parent_id: ''
    });
    setEditDialog({ open: true, category: null });
  };

  const openEditDialog = (category) => {
    setFormData({
      name_ar: category.name_ar || '',
      name_fr: category.name_fr || '',
      name_en: category.name_en || '',
      image: category.image || '',
      parent_id: category.parent_id || ''
    });
    setEditDialog({ open: true, category });
  };

  const handleSave = async () => {
    if (!formData.name_ar && !formData.name_fr) {
      toast.error(language === 'ar' ? 'يرجى إدخال اسم التصنيف' : 'Please enter category name');
      return;
    }

    try {
      setSaving(true);
      
      if (editDialog.category) {
        // Update existing
        await axios.put(
          `${API}/categories/${editDialog.category.category_id}`,
          formData,
          { withCredentials: true }
        );
      } else {
        // Create new
        await axios.post(`${API}/categories`, formData, { withCredentials: true });
      }
      
      toast.success(text.categorySaved);
      setEditDialog({ open: false, category: null });
      fetchCategories();
    } catch (error) {
      console.error('Error saving category:', error);
      toast.error(text.error);
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async () => {
    if (!deleteDialog.category) return;
    
    try {
      await axios.delete(
        `${API}/categories/${deleteDialog.category.category_id}`,
        { withCredentials: true }
      );
      toast.success(text.categoryDeleted);
      setDeleteDialog({ open: false, category: null });
      fetchCategories();
    } catch (error) {
      console.error('Error deleting category:', error);
      toast.error(text.error);
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold">{text.categories}</h1>
          <p className="text-muted-foreground">{filteredCategories.length} {text.categories}</p>
        </div>
        <div className="flex items-center gap-2">
          <Button variant="outline" size="sm" onClick={fetchCategories}>
            <RefreshCw className="h-4 w-4 me-2" />
            {text.refresh}
          </Button>
          <Button size="sm" onClick={openAddDialog}>
            <Plus className="h-4 w-4 me-2" />
            {text.addCategory}
          </Button>
        </div>
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

      {/* Categories Grid */}
      {loading ? (
        <div className="flex items-center justify-center h-64">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary" />
        </div>
      ) : filteredCategories.length === 0 ? (
        <Card>
          <CardContent className="flex flex-col items-center justify-center h-64 text-muted-foreground">
            <Folder className="h-12 w-12 mb-4 opacity-50" />
            <p>{text.noCategories}</p>
            <Button variant="outline" className="mt-4" onClick={openAddDialog}>
              <Plus className="h-4 w-4 me-2" />
              {text.addCategory}
            </Button>
          </CardContent>
        </Card>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          {filteredCategories.map(category => (
            <Card key={category.category_id} className="overflow-hidden group">
              <div className="aspect-video bg-muted relative">
                {category.image ? (
                  <img
                    src={category.image}
                    alt={category[`name_${language}`] || category.name_ar}
                    className="w-full h-full object-cover"
                  />
                ) : (
                  <div className="w-full h-full flex items-center justify-center">
                    <Folder className="h-12 w-12 text-muted-foreground/50" />
                  </div>
                )}
                {/* Overlay with actions */}
                <div className="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                  <Button
                    variant="secondary"
                    size="icon"
                    className="h-8 w-8"
                    onClick={() => openEditDialog(category)}
                  >
                    <Edit className="h-4 w-4" />
                  </Button>
                  <Button
                    variant="destructive"
                    size="icon"
                    className="h-8 w-8"
                    onClick={() => setDeleteDialog({ open: true, category })}
                  >
                    <Trash2 className="h-4 w-4" />
                  </Button>
                </div>
              </div>
              <CardContent className="p-4">
                <h3 className="font-semibold truncate">
                  {category[`name_${language}`] || category.name_ar}
                </h3>
                <p className="text-sm text-muted-foreground mt-1">
                  {category.productCount} {text.products}
                </p>
              </CardContent>
            </Card>
          ))}
        </div>
      )}

      {/* Add/Edit Dialog */}
      <Dialog open={editDialog.open} onOpenChange={() => setEditDialog({ open: false, category: null })}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>
              {editDialog.category ? text.editCategory : text.addCategory}
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
            
            {/* Image Section with Tabs */}
            <div className="space-y-2">
              <Label className="flex items-center gap-2">
                <ImageIcon className="h-4 w-4" />
                {text.image}
              </Label>
              
              <Tabs value={imageTab} onValueChange={setImageTab} className="w-full">
                <TabsList className="grid w-full grid-cols-2">
                  <TabsTrigger value="url" className="flex items-center gap-2">
                    <LinkIcon className="h-4 w-4" />
                    {text.urlTab}
                  </TabsTrigger>
                  <TabsTrigger value="upload" className="flex items-center gap-2">
                    <Upload className="h-4 w-4" />
                    {text.uploadTab}
                  </TabsTrigger>
                </TabsList>
                
                <TabsContent value="url" className="mt-3">
                  <Input
                    value={formData.image}
                    onChange={(e) => setFormData(prev => ({ ...prev, image: e.target.value }))}
                    placeholder={text.enterUrl}
                    dir="ltr"
                  />
                </TabsContent>
                
                <TabsContent value="upload" className="mt-3">
                  <div
                    onDrop={handleDrop}
                    onDragOver={handleDragOver}
                    onClick={() => fileInputRef.current?.click()}
                    className="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer hover:border-primary hover:bg-primary/5 transition-colors"
                  >
                    <input
                      ref={fileInputRef}
                      type="file"
                      accept="image/png,image/jpeg,image/jpg,image/webp"
                      onChange={(e) => handleFileUpload(e.target.files[0])}
                      className="hidden"
                    />
                    {uploading ? (
                      <div className="flex flex-col items-center">
                        <Loader2 className="h-8 w-8 animate-spin text-primary mb-2" />
                        <p className="text-sm text-muted-foreground">
                          {language === 'ar' ? 'جاري الرفع...' : 'Uploading...'}
                        </p>
                      </div>
                    ) : (
                      <>
                        <Upload className="h-8 w-8 mx-auto text-muted-foreground mb-2" />
                        <p className="text-sm font-medium">{text.selectFile}</p>
                        <p className="text-xs text-muted-foreground mt-1">{text.dragDrop}</p>
                        <p className="text-xs text-muted-foreground mt-1">{text.supportedFormats}</p>
                      </>
                    )}
                  </div>
                </TabsContent>
              </Tabs>
              
              {/* Image Preview */}
              {formData.image && (
                <div className="relative inline-block mt-3">
                  <img
                    src={formData.image}
                    alt="Preview"
                    className="h-24 w-24 object-cover rounded-lg border"
                  />
                  <button
                    type="button"
                    onClick={() => setFormData(prev => ({ ...prev, image: '' }))}
                    className="absolute -top-2 -end-2 h-6 w-6 bg-destructive text-white rounded-full flex items-center justify-center hover:bg-destructive/90"
                    title={text.removeImage}
                  >
                    <X className="h-4 w-4" />
                  </button>
                </div>
              )}
            </div>
          </div>

          <DialogFooter>
            <Button variant="outline" onClick={() => setEditDialog({ open: false, category: null })}>
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
      <Dialog open={deleteDialog.open} onOpenChange={() => setDeleteDialog({ open: false, category: null })}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2">
              <AlertCircle className="h-5 w-5 text-red-500" />
              {text.delete}
            </DialogTitle>
            <DialogDescription>
              {text.deleteConfirm}
              <br />
              <span className="text-red-500">{text.deleteWarning}</span>
            </DialogDescription>
          </DialogHeader>
          
          {deleteDialog.category && (
            <div className="flex items-center gap-3 p-3 bg-muted rounded-lg">
              {deleteDialog.category.image ? (
                <img
                  src={deleteDialog.category.image}
                  alt=""
                  className="h-12 w-12 rounded-lg object-cover"
                />
              ) : (
                <div className="h-12 w-12 rounded-lg bg-muted-foreground/20 flex items-center justify-center">
                  <Folder className="h-6 w-6 text-muted-foreground" />
                </div>
              )}
              <div>
                <p className="font-medium">
                  {deleteDialog.category[`name_${language}`] || deleteDialog.category.name_ar}
                </p>
                <p className="text-sm text-muted-foreground">
                  {deleteDialog.category.productCount} {text.products}
                </p>
              </div>
            </div>
          )}

          <DialogFooter>
            <Button variant="outline" onClick={() => setDeleteDialog({ open: false, category: null })}>
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

export default CategoriesPage;
