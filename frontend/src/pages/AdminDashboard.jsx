import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { useAuth } from '@/contexts/AuthContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
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
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Switch } from '@/components/ui/switch';
import { toast } from 'sonner';
import { 
  LayoutDashboard, 
  Package, 
  ShoppingCart, 
  Users,
  DollarSign,
  Plus,
  Edit,
  Trash2,
  Loader2,
  TrendingUp,
  ChevronRight,
  ChevronLeft
} from 'lucide-react';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

export const AdminDashboard = () => {
  const { t, language, isRTL, formatPrice } = useLanguage();
  const { user, isAdmin, loading: authLoading } = useAuth();
  const navigate = useNavigate();

  const [stats, setStats] = useState(null);
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('overview');
  
  // Product form
  const [productDialogOpen, setProductDialogOpen] = useState(false);
  const [editingProduct, setEditingProduct] = useState(null);
  const [productForm, setProductForm] = useState({
    name_ar: '', name_fr: '', name_en: '',
    description_ar: '', description_fr: '', description_en: '',
    price: '', old_price: '', stock: '', category_id: '',
    images: '', featured: false, unit: 'piece'
  });

  const ChevronIcon = isRTL ? ChevronLeft : ChevronRight;

  useEffect(() => {
    if (!authLoading && (!user || !isAdmin)) {
      navigate('/');
    }
  }, [user, isAdmin, authLoading]);

  useEffect(() => {
    if (isAdmin) {
      fetchData();
    }
  }, [isAdmin]);

  const fetchData = async () => {
    try {
      setLoading(true);
      const [statsRes, productsRes, categoriesRes, ordersRes] = await Promise.all([
        axios.get(`${API}/admin/stats`, { withCredentials: true }),
        axios.get(`${API}/products?limit=100`),
        axios.get(`${API}/categories`),
        axios.get(`${API}/admin/orders`, { withCredentials: true })
      ]);
      setStats(statsRes.data);
      setProducts(productsRes.data);
      setCategories(categoriesRes.data);
      setOrders(ordersRes.data);
    } catch (error) {
      console.error('Error fetching data:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleProductSubmit = async (e) => {
    e.preventDefault();
    try {
      const data = {
        ...productForm,
        price: parseFloat(productForm.price),
        old_price: productForm.old_price ? parseFloat(productForm.old_price) : null,
        stock: parseInt(productForm.stock),
        images: productForm.images ? productForm.images.split(',').map(s => s.trim()) : []
      };

      if (editingProduct) {
        await axios.put(`${API}/products/${editingProduct.product_id}`, data, { withCredentials: true });
        toast.success(language === 'ar' ? 'تم تحديث المنتج' : 'Product updated');
      } else {
        await axios.post(`${API}/products`, data, { withCredentials: true });
        toast.success(language === 'ar' ? 'تم إضافة المنتج' : 'Product added');
      }
      
      setProductDialogOpen(false);
      setEditingProduct(null);
      resetProductForm();
      fetchData();
    } catch (error) {
      toast.error(error.response?.data?.detail || t('common.error'));
    }
  };

  const handleDeleteProduct = async (productId) => {
    if (!window.confirm(language === 'ar' ? 'هل أنت متأكد من حذف هذا المنتج؟' : 'Are you sure you want to delete this product?')) {
      return;
    }

    try {
      await axios.delete(`${API}/products/${productId}`, { withCredentials: true });
      toast.success(language === 'ar' ? 'تم حذف المنتج' : 'Product deleted');
      fetchData();
    } catch (error) {
      toast.error(t('common.error'));
    }
  };

  const handleOrderStatus = async (orderId, status) => {
    try {
      await axios.put(
        `${API}/admin/orders/${orderId}/status`,
        { status },
        { withCredentials: true }
      );
      toast.success(language === 'ar' ? 'تم تحديث حالة الطلب' : 'Order status updated');
      fetchData();
    } catch (error) {
      toast.error(t('common.error'));
    }
  };

  const openEditProduct = (product) => {
    setEditingProduct(product);
    setProductForm({
      name_ar: product.name_ar,
      name_fr: product.name_fr,
      name_en: product.name_en,
      description_ar: product.description_ar || '',
      description_fr: product.description_fr || '',
      description_en: product.description_en || '',
      price: product.price.toString(),
      old_price: product.old_price?.toString() || '',
      stock: product.stock.toString(),
      category_id: product.category_id,
      images: product.images?.join(', ') || '',
      featured: product.featured,
      unit: product.unit
    });
    setProductDialogOpen(true);
  };

  const resetProductForm = () => {
    setProductForm({
      name_ar: '', name_fr: '', name_en: '',
      description_ar: '', description_fr: '', description_en: '',
      price: '', old_price: '', stock: '', category_id: '',
      images: '', featured: false, unit: 'piece'
    });
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'pending': return 'bg-yellow-100 text-yellow-800';
      case 'confirmed': return 'bg-blue-100 text-blue-800';
      case 'shipped': return 'bg-purple-100 text-purple-800';
      case 'delivered': return 'bg-green-100 text-green-800';
      case 'cancelled': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  if (authLoading || loading) {
    return (
      <div className="min-h-[60vh] flex items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  if (!isAdmin) return null;

  return (
    <div className="min-h-screen bg-background" data-testid="admin-dashboard">
      {/* Header */}
      <div className="bg-primary text-primary-foreground py-6">
        <div className="container mx-auto px-4">
          <h1 className="text-2xl font-bold">{t('admin.dashboard')}</h1>
          <div className="flex items-center gap-2 text-sm text-primary-foreground/70 mt-1">
            <Link to="/" className="hover:text-primary-foreground">{t('nav.home')}</Link>
            <ChevronIcon className="h-4 w-4" />
            <span>{t('admin.dashboard')}</span>
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-8">
        <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
          <TabsList className="grid w-full max-w-lg grid-cols-3 rounded-full p-1 bg-muted">
            <TabsTrigger value="overview" className="rounded-full">
              <LayoutDashboard className="h-4 w-4 me-2" />
              {language === 'ar' ? 'نظرة عامة' : 'Overview'}
            </TabsTrigger>
            <TabsTrigger value="products" className="rounded-full">
              <Package className="h-4 w-4 me-2" />
              {t('admin.products')}
            </TabsTrigger>
            <TabsTrigger value="orders" className="rounded-full">
              <ShoppingCart className="h-4 w-4 me-2" />
              {t('admin.orders')}
            </TabsTrigger>
          </TabsList>

          {/* Overview Tab */}
          <TabsContent value="overview">
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
              <Card>
                <CardContent className="p-6">
                  <div className="flex items-center gap-4">
                    <div className="h-12 w-12 rounded-2xl bg-primary/10 flex items-center justify-center">
                      <DollarSign className="h-6 w-6 text-primary" />
                    </div>
                    <div>
                      <p className="text-sm text-muted-foreground">{t('admin.totalRevenue')}</p>
                      <p className="text-2xl font-bold">{formatPrice(stats?.total_revenue || 0)}</p>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardContent className="p-6">
                  <div className="flex items-center gap-4">
                    <div className="h-12 w-12 rounded-2xl bg-secondary/10 flex items-center justify-center">
                      <ShoppingCart className="h-6 w-6 text-secondary" />
                    </div>
                    <div>
                      <p className="text-sm text-muted-foreground">{t('admin.totalOrders')}</p>
                      <p className="text-2xl font-bold">{stats?.total_orders || 0}</p>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardContent className="p-6">
                  <div className="flex items-center gap-4">
                    <div className="h-12 w-12 rounded-2xl bg-accent/20 flex items-center justify-center">
                      <TrendingUp className="h-6 w-6 text-accent-foreground" />
                    </div>
                    <div>
                      <p className="text-sm text-muted-foreground">{t('admin.pendingOrders')}</p>
                      <p className="text-2xl font-bold">{stats?.pending_orders || 0}</p>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardContent className="p-6">
                  <div className="flex items-center gap-4">
                    <div className="h-12 w-12 rounded-2xl bg-muted flex items-center justify-center">
                      <Package className="h-6 w-6 text-muted-foreground" />
                    </div>
                    <div>
                      <p className="text-sm text-muted-foreground">{t('admin.totalProducts')}</p>
                      <p className="text-2xl font-bold">{stats?.total_products || 0}</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Recent Orders */}
            <Card>
              <CardHeader>
                <CardTitle>{language === 'ar' ? 'آخر الطلبات' : 'Recent Orders'}</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {orders.slice(0, 5).map((order) => (
                    <div key={order.order_id} className="flex items-center justify-between p-4 bg-muted/30 rounded-xl">
                      <div>
                        <p className="font-medium">{order.customer_name}</p>
                        <p className="text-sm text-muted-foreground">{order.order_id}</p>
                      </div>
                      <div className="text-end">
                        <p className="font-bold text-primary">{formatPrice(order.total)}</p>
                        <Badge className={getStatusColor(order.status)}>
                          {t(`orders.${order.status}`)}
                        </Badge>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Products Tab */}
          <TabsContent value="products">
            <div className="flex justify-between items-center mb-6">
              <h2 className="text-xl font-bold">{t('admin.products')}</h2>
              <Dialog open={productDialogOpen} onOpenChange={setProductDialogOpen}>
                <DialogTrigger asChild>
                  <Button 
                    className="rounded-full"
                    onClick={() => {
                      setEditingProduct(null);
                      resetProductForm();
                    }}
                    data-testid="add-product-btn"
                  >
                    <Plus className="h-4 w-4 me-2" />
                    {t('admin.addProduct')}
                  </Button>
                </DialogTrigger>
                <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
                  <DialogHeader>
                    <DialogTitle>
                      {editingProduct ? t('admin.editProduct') : t('admin.addProduct')}
                    </DialogTitle>
                  </DialogHeader>
                  <form onSubmit={handleProductSubmit} className="space-y-4">
                    <div className="grid grid-cols-3 gap-4">
                      <div className="space-y-2">
                        <Label>الاسم (عربي)</Label>
                        <Input
                          value={productForm.name_ar}
                          onChange={(e) => setProductForm({ ...productForm, name_ar: e.target.value })}
                          required
                        />
                      </div>
                      <div className="space-y-2">
                        <Label>Nom (Français)</Label>
                        <Input
                          value={productForm.name_fr}
                          onChange={(e) => setProductForm({ ...productForm, name_fr: e.target.value })}
                          required
                        />
                      </div>
                      <div className="space-y-2">
                        <Label>Name (English)</Label>
                        <Input
                          value={productForm.name_en}
                          onChange={(e) => setProductForm({ ...productForm, name_en: e.target.value })}
                          required
                        />
                      </div>
                    </div>

                    <div className="grid grid-cols-3 gap-4">
                      <div className="space-y-2">
                        <Label>الوصف (عربي)</Label>
                        <Textarea
                          value={productForm.description_ar}
                          onChange={(e) => setProductForm({ ...productForm, description_ar: e.target.value })}
                          rows={2}
                        />
                      </div>
                      <div className="space-y-2">
                        <Label>Description (Français)</Label>
                        <Textarea
                          value={productForm.description_fr}
                          onChange={(e) => setProductForm({ ...productForm, description_fr: e.target.value })}
                          rows={2}
                        />
                      </div>
                      <div className="space-y-2">
                        <Label>Description (English)</Label>
                        <Textarea
                          value={productForm.description_en}
                          onChange={(e) => setProductForm({ ...productForm, description_en: e.target.value })}
                          rows={2}
                        />
                      </div>
                    </div>

                    <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                      <div className="space-y-2">
                        <Label>{t('products.price')} (DZD)</Label>
                        <Input
                          type="number"
                          value={productForm.price}
                          onChange={(e) => setProductForm({ ...productForm, price: e.target.value })}
                          required
                        />
                      </div>
                      <div className="space-y-2">
                        <Label>{language === 'ar' ? 'السعر القديم' : 'Old Price'}</Label>
                        <Input
                          type="number"
                          value={productForm.old_price}
                          onChange={(e) => setProductForm({ ...productForm, old_price: e.target.value })}
                        />
                      </div>
                      <div className="space-y-2">
                        <Label>{language === 'ar' ? 'المخزون' : 'Stock'}</Label>
                        <Input
                          type="number"
                          value={productForm.stock}
                          onChange={(e) => setProductForm({ ...productForm, stock: e.target.value })}
                          required
                        />
                      </div>
                      <div className="space-y-2">
                        <Label>{language === 'ar' ? 'الوحدة' : 'Unit'}</Label>
                        <Select
                          value={productForm.unit}
                          onValueChange={(value) => setProductForm({ ...productForm, unit: value })}
                        >
                          <SelectTrigger>
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="piece">{language === 'ar' ? 'قطعة' : 'Piece'}</SelectItem>
                            <SelectItem value="kg">{language === 'ar' ? 'كغ' : 'Kg'}</SelectItem>
                            <SelectItem value="pack">{language === 'ar' ? 'علبة' : 'Pack'}</SelectItem>
                            <SelectItem value="liter">{language === 'ar' ? 'لتر' : 'Liter'}</SelectItem>
                            <SelectItem value="roll">{language === 'ar' ? 'لفة' : 'Roll'}</SelectItem>
                            <SelectItem value="kit">{language === 'ar' ? 'طقم' : 'Kit'}</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label>{t('nav.categories')}</Label>
                        <Select
                          value={productForm.category_id}
                          onValueChange={(value) => setProductForm({ ...productForm, category_id: value })}
                          required
                        >
                          <SelectTrigger>
                            <SelectValue placeholder={language === 'ar' ? 'اختر الفئة' : 'Select category'} />
                          </SelectTrigger>
                          <SelectContent>
                            {categories.map((cat) => (
                              <SelectItem key={cat.category_id} value={cat.category_id}>
                                {cat[`name_${language}`] || cat.name_ar}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
                      </div>
                      <div className="space-y-2">
                        <Label>{language === 'ar' ? 'روابط الصور' : 'Image URLs'}</Label>
                        <Input
                          value={productForm.images}
                          onChange={(e) => setProductForm({ ...productForm, images: e.target.value })}
                          placeholder="https://..., https://..."
                        />
                      </div>
                    </div>

                    <div className="flex items-center gap-2">
                      <Switch
                        checked={productForm.featured}
                        onCheckedChange={(checked) => setProductForm({ ...productForm, featured: checked })}
                      />
                      <Label>{t('products.featured')}</Label>
                    </div>

                    <div className="flex gap-4 pt-4">
                      <Button type="submit" className="flex-1 rounded-full">
                        {editingProduct ? t('common.save') : t('common.add')}
                      </Button>
                      <Button 
                        type="button" 
                        variant="outline" 
                        onClick={() => setProductDialogOpen(false)}
                        className="rounded-full"
                      >
                        {t('common.cancel')}
                      </Button>
                    </div>
                  </form>
                </DialogContent>
              </Dialog>
            </div>

            <Card>
              <CardContent className="p-0">
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>{language === 'ar' ? 'المنتج' : 'Product'}</TableHead>
                        <TableHead>{t('products.price')}</TableHead>
                        <TableHead>{language === 'ar' ? 'المخزون' : 'Stock'}</TableHead>
                        <TableHead>{t('nav.categories')}</TableHead>
                        <TableHead>{language === 'ar' ? 'مميز' : 'Featured'}</TableHead>
                        <TableHead></TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {products.map((product) => (
                        <TableRow key={product.product_id}>
                          <TableCell>
                            <div className="flex items-center gap-3">
                              <div className="h-10 w-10 rounded-lg overflow-hidden bg-muted">
                                <img 
                                  src={product.images?.[0] || 'https://via.placeholder.com/40'} 
                                  alt="" 
                                  className="h-full w-full object-cover"
                                />
                              </div>
                              <span className="font-medium">{product[`name_${language}`] || product.name_ar}</span>
                            </div>
                          </TableCell>
                          <TableCell className="font-medium">{formatPrice(product.price)}</TableCell>
                          <TableCell>{product.stock}</TableCell>
                          <TableCell>
                            {categories.find(c => c.category_id === product.category_id)?.[`name_${language}`] || '-'}
                          </TableCell>
                          <TableCell>
                            {product.featured && <Badge variant="secondary">{t('products.featured')}</Badge>}
                          </TableCell>
                          <TableCell>
                            <div className="flex gap-2">
                              <Button 
                                variant="ghost" 
                                size="icon"
                                onClick={() => openEditProduct(product)}
                              >
                                <Edit className="h-4 w-4" />
                              </Button>
                              <Button 
                                variant="ghost" 
                                size="icon"
                                className="text-destructive hover:text-destructive"
                                onClick={() => handleDeleteProduct(product.product_id)}
                              >
                                <Trash2 className="h-4 w-4" />
                              </Button>
                            </div>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          {/* Orders Tab */}
          <TabsContent value="orders">
            <h2 className="text-xl font-bold mb-6">{t('admin.orders')}</h2>
            
            <Card>
              <CardContent className="p-0">
                <div className="overflow-x-auto">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>{t('checkout.orderNumber')}</TableHead>
                        <TableHead>{t('checkout.customerInfo')}</TableHead>
                        <TableHead>{t('orders.total')}</TableHead>
                        <TableHead>{t('orders.status')}</TableHead>
                        <TableHead>{t('orders.orderDate')}</TableHead>
                        <TableHead></TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {orders.map((order) => (
                        <TableRow key={order.order_id}>
                          <TableCell className="font-mono">{order.order_id}</TableCell>
                          <TableCell>
                            <div>
                              <p className="font-medium">{order.customer_name}</p>
                              <p className="text-sm text-muted-foreground">{order.phone}</p>
                              <p className="text-sm text-muted-foreground">{order.wilaya}</p>
                            </div>
                          </TableCell>
                          <TableCell className="font-bold text-primary">
                            {formatPrice(order.total)}
                          </TableCell>
                          <TableCell>
                            <Badge className={getStatusColor(order.status)}>
                              {t(`orders.${order.status}`)}
                            </Badge>
                          </TableCell>
                          <TableCell>
                            {new Date(order.created_at).toLocaleDateString(
                              language === 'ar' ? 'ar-DZ' : 'fr-FR'
                            )}
                          </TableCell>
                          <TableCell>
                            <Select
                              value={order.status}
                              onValueChange={(value) => handleOrderStatus(order.order_id, value)}
                            >
                              <SelectTrigger className="w-32">
                                <SelectValue />
                              </SelectTrigger>
                              <SelectContent>
                                <SelectItem value="pending">{t('orders.pending')}</SelectItem>
                                <SelectItem value="confirmed">{t('orders.confirmed')}</SelectItem>
                                <SelectItem value="shipped">{t('orders.shipped')}</SelectItem>
                                <SelectItem value="delivered">{t('orders.delivered')}</SelectItem>
                                <SelectItem value="cancelled">{t('orders.cancelled')}</SelectItem>
                              </SelectContent>
                            </Select>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
};

export default AdminDashboard;
