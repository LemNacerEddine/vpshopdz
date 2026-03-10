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
  Plus,
  Search,
  Filter,
  MoreVertical,
  Edit,
  Trash2,
  Eye,
  Package,
  Download,
  Upload,
  RefreshCw,
  CheckCircle,
  XCircle,
  AlertCircle
} from 'lucide-react';
import { toast } from 'sonner';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const ProductsPage = () => {
  const { language, isRTL, formatPrice } = useLanguage();
  const [searchParams, setSearchParams] = useSearchParams();
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [categoryFilter, setCategoryFilter] = useState('all');
  const [statusFilter, setStatusFilter] = useState('all');
  const [selectedProducts, setSelectedProducts] = useState([]);
  const [deleteDialog, setDeleteDialog] = useState({ open: false, product: null });

  const l = {
    ar: {
      products: 'المنتجات',
      allProducts: 'جميع المنتجات',
      addProduct: 'إضافة منتج',
      search: 'بحث عن منتج...',
      category: 'التصنيف',
      allCategories: 'جميع التصنيفات',
      status: 'الحالة',
      allStatus: 'جميع الحالات',
      active: 'نشط',
      draft: 'مسودة',
      outOfStock: 'نفد المخزون',
      product: 'المنتج',
      price: 'السعر',
      stock: 'المخزون',
      actions: 'الإجراءات',
      edit: 'تعديل',
      delete: 'حذف',
      view: 'عرض',
      export: 'تصدير',
      import: 'استيراد',
      refresh: 'تحديث',
      noProducts: 'لا توجد منتجات',
      deleteConfirm: 'هل أنت متأكد من حذف هذا المنتج؟',
      deleteWarning: 'لا يمكن التراجع عن هذا الإجراء',
      cancel: 'إلغاء',
      confirm: 'تأكيد',
      productDeleted: 'تم حذف المنتج بنجاح',
      featured: 'مميز',
      rating: 'التقييم',
      selected: 'محدد',
      bulkDelete: 'حذف المحدد'
    },
    fr: {
      products: 'Produits',
      allProducts: 'Tous les produits',
      addProduct: 'Ajouter un produit',
      search: 'Rechercher un produit...',
      category: 'Catégorie',
      allCategories: 'Toutes les catégories',
      status: 'Statut',
      allStatus: 'Tous les statuts',
      active: 'Actif',
      draft: 'Brouillon',
      outOfStock: 'Rupture de stock',
      product: 'Produit',
      price: 'Prix',
      stock: 'Stock',
      actions: 'Actions',
      edit: 'Modifier',
      delete: 'Supprimer',
      view: 'Voir',
      export: 'Exporter',
      import: 'Importer',
      refresh: 'Actualiser',
      noProducts: 'Aucun produit',
      deleteConfirm: 'Êtes-vous sûr de vouloir supprimer ce produit?',
      deleteWarning: 'Cette action est irréversible',
      cancel: 'Annuler',
      confirm: 'Confirmer',
      productDeleted: 'Produit supprimé avec succès',
      featured: 'En vedette',
      rating: 'Note',
      selected: 'sélectionné',
      bulkDelete: 'Supprimer la sélection'
    },
    en: {
      products: 'Products',
      allProducts: 'All Products',
      addProduct: 'Add Product',
      search: 'Search products...',
      category: 'Category',
      allCategories: 'All Categories',
      status: 'Status',
      allStatus: 'All Status',
      active: 'Active',
      draft: 'Draft',
      outOfStock: 'Out of Stock',
      product: 'Product',
      price: 'Price',
      stock: 'Stock',
      actions: 'Actions',
      edit: 'Edit',
      delete: 'Delete',
      view: 'View',
      export: 'Export',
      import: 'Import',
      refresh: 'Refresh',
      noProducts: 'No products found',
      deleteConfirm: 'Are you sure you want to delete this product?',
      deleteWarning: 'This action cannot be undone',
      cancel: 'Cancel',
      confirm: 'Confirm',
      productDeleted: 'Product deleted successfully',
      featured: 'Featured',
      rating: 'Rating',
      selected: 'selected',
      bulkDelete: 'Delete Selected'
    }
  };

  const text = l[language] || l.ar;

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      const [productsRes, categoriesRes] = await Promise.all([
        axios.get(`${API}/products`),
        axios.get(`${API}/categories`)
      ]);
      setProducts(productsRes.data || []);
      setCategories(categoriesRes.data || []);
    } catch (error) {
      console.error('Error fetching data:', error);
      toast.error('Error loading products');
    } finally {
      setLoading(false);
    }
  };

  const filteredProducts = useMemo(() => {
    return products.filter(product => {
      // Search filter
      const searchMatch = !search || 
        product.name_ar?.toLowerCase().includes(search.toLowerCase()) ||
        product.name_fr?.toLowerCase().includes(search.toLowerCase()) ||
        product.name_en?.toLowerCase().includes(search.toLowerCase()) ||
        product.product_id?.toLowerCase().includes(search.toLowerCase());

      // Category filter
      const categoryMatch = categoryFilter === 'all' || product.category_id === categoryFilter;

      // Status filter
      let statusMatch = true;
      if (statusFilter === 'active') statusMatch = product.stock > 0;
      else if (statusFilter === 'outOfStock') statusMatch = product.stock === 0;
      else if (statusFilter === 'featured') statusMatch = product.featured;

      return searchMatch && categoryMatch && statusMatch;
    });
  }, [products, search, categoryFilter, statusFilter]);

  const handleDelete = async () => {
    if (!deleteDialog.product) return;
    
    try {
      await axios.delete(`${API}/products/${deleteDialog.product.product_id}`, {
        withCredentials: true
      });
      setProducts(prev => prev.filter(p => p.product_id !== deleteDialog.product.product_id));
      toast.success(text.productDeleted);
    } catch (error) {
      console.error('Error deleting product:', error);
      toast.error('Error deleting product');
    } finally {
      setDeleteDialog({ open: false, product: null });
    }
  };

  const toggleSelectProduct = (productId) => {
    setSelectedProducts(prev => 
      prev.includes(productId)
        ? prev.filter(id => id !== productId)
        : [...prev, productId]
    );
  };

  const toggleSelectAll = () => {
    if (selectedProducts.length === filteredProducts.length) {
      setSelectedProducts([]);
    } else {
      setSelectedProducts(filteredProducts.map(p => p.product_id));
    }
  };

  const getProductStatus = (product) => {
    if (product.stock === 0) return { label: text.outOfStock, color: 'text-red-600 bg-red-100 dark:bg-red-900/30' };
    if (product.featured) return { label: text.featured, color: 'text-green-600 bg-green-100 dark:bg-green-900/30' };
    return { label: text.active, color: 'text-blue-600 bg-blue-100 dark:bg-blue-900/30' };
  };

  const getCategoryName = (categoryId) => {
    const cat = categories.find(c => c.category_id === categoryId);
    return cat ? (cat[`name_${language}`] || cat.name_ar) : '-';
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-bold">{text.products}</h1>
          <p className="text-muted-foreground">{filteredProducts.length} {text.product}</p>
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
          <Link to="/admin/products/new">
            <Button size="sm">
              <Plus className="h-4 w-4 me-2" />
              {text.addProduct}
            </Button>
          </Link>
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

            {/* Category Filter */}
            <Select value={categoryFilter} onValueChange={setCategoryFilter}>
              <SelectTrigger className="w-full lg:w-48">
                <SelectValue placeholder={text.category} />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">{text.allCategories}</SelectItem>
                {categories.map(cat => (
                  <SelectItem key={cat.category_id} value={cat.category_id}>
                    {cat[`name_${language}`] || cat.name_ar}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>

            {/* Status Filter */}
            <Select value={statusFilter} onValueChange={setStatusFilter}>
              <SelectTrigger className="w-full lg:w-40">
                <SelectValue placeholder={text.status} />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">{text.allStatus}</SelectItem>
                <SelectItem value="active">{text.active}</SelectItem>
                <SelectItem value="featured">{text.featured}</SelectItem>
                <SelectItem value="outOfStock">{text.outOfStock}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          {/* Bulk Actions */}
          {selectedProducts.length > 0 && (
            <div className="flex items-center gap-4 mt-4 pt-4 border-t">
              <span className="text-sm text-muted-foreground">
                {selectedProducts.length} {text.selected}
              </span>
              <Button variant="destructive" size="sm">
                <Trash2 className="h-4 w-4 me-2" />
                {text.bulkDelete}
              </Button>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Products Table */}
      <Card>
        <CardContent className="p-0">
          {loading ? (
            <div className="flex items-center justify-center h-64">
              <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary" />
            </div>
          ) : filteredProducts.length === 0 ? (
            <div className="flex flex-col items-center justify-center h-64 text-muted-foreground">
              <Package className="h-12 w-12 mb-4 opacity-50" />
              <p>{text.noProducts}</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead>
                  <tr className="border-b bg-muted/50">
                    <th className="w-12 py-3 px-4">
                      <input
                        type="checkbox"
                        checked={selectedProducts.length === filteredProducts.length}
                        onChange={toggleSelectAll}
                        className="rounded border-gray-300"
                      />
                    </th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.product}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.category}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.price}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.stock}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.status}</th>
                    <th className="text-start py-3 px-4 text-sm font-medium">{text.actions}</th>
                  </tr>
                </thead>
                <tbody>
                  {filteredProducts.map(product => {
                    const status = getProductStatus(product);
                    return (
                      <tr key={product.product_id} className="border-b hover:bg-muted/30 transition-colors">
                        <td className="py-3 px-4">
                          <input
                            type="checkbox"
                            checked={selectedProducts.includes(product.product_id)}
                            onChange={() => toggleSelectProduct(product.product_id)}
                            className="rounded border-gray-300"
                          />
                        </td>
                        <td className="py-3 px-4">
                          <div className="flex items-center gap-3">
                            <img
                              src={product.images?.[0] || 'https://via.placeholder.com/40'}
                              alt=""
                              className="h-10 w-10 rounded-lg object-cover"
                            />
                            <div>
                              <p className="font-medium">
                                {product[`name_${language}`] || product.name_ar}
                              </p>
                              <p className="text-xs text-muted-foreground">
                                #{product.product_id.slice(-6)}
                              </p>
                            </div>
                          </div>
                        </td>
                        <td className="py-3 px-4 text-sm">
                          {getCategoryName(product.category_id)}
                        </td>
                        <td className="py-3 px-4">
                          <div>
                            <p className="font-medium">{formatPrice(product.price)}</p>
                            {product.old_price && (
                              <p className="text-xs text-muted-foreground line-through">
                                {formatPrice(product.old_price)}
                              </p>
                            )}
                          </div>
                        </td>
                        <td className="py-3 px-4">
                          <span className={`font-medium ${
                            product.stock === 0 ? 'text-red-600' :
                            product.stock < 20 ? 'text-yellow-600' : 'text-green-600'
                          }`}>
                            {product.stock}
                          </span>
                        </td>
                        <td className="py-3 px-4">
                          <span className={`inline-flex px-2 py-1 rounded-full text-xs font-medium ${status.color}`}>
                            {status.label}
                          </span>
                        </td>
                        <td className="py-3 px-4">
                          <div className="flex items-center gap-1">
                            <Link to={`/products/${product.product_id}`} target="_blank">
                              <Button variant="ghost" size="icon" className="h-8 w-8">
                                <Eye className="h-4 w-4" />
                              </Button>
                            </Link>
                            <Link to={`/admin/products/${product.product_id}/edit`}>
                              <Button variant="ghost" size="icon" className="h-8 w-8">
                                <Edit className="h-4 w-4" />
                              </Button>
                            </Link>
                            <Button
                              variant="ghost"
                              size="icon"
                              className="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50"
                              onClick={() => setDeleteDialog({ open: true, product })}
                            >
                              <Trash2 className="h-4 w-4" />
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

      {/* Delete Confirmation Dialog */}
      <Dialog open={deleteDialog.open} onOpenChange={(open) => setDeleteDialog({ open, product: null })}>
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
          {deleteDialog.product && (
            <div className="flex items-center gap-3 p-3 bg-muted rounded-lg">
              <img
                src={deleteDialog.product.images?.[0] || 'https://via.placeholder.com/40'}
                alt=""
                className="h-12 w-12 rounded-lg object-cover"
              />
              <div>
                <p className="font-medium">
                  {deleteDialog.product[`name_${language}`] || deleteDialog.product.name_ar}
                </p>
                <p className="text-sm text-muted-foreground">
                  {formatPrice(deleteDialog.product.price)}
                </p>
              </div>
            </div>
          )}
          <DialogFooter>
            <Button variant="outline" onClick={() => setDeleteDialog({ open: false, product: null })}>
              {text.cancel}
            </Button>
            <Button variant="destructive" onClick={handleDelete}>
              {text.confirm}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default ProductsPage;
