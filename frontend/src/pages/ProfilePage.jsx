import React, { useState, useEffect } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import axios from 'axios';
import { useLanguage } from '../contexts/LanguageContext';
import { useAuth } from '../contexts/AuthContext';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Badge } from '../components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../components/ui/tabs';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '../components/ui/select';
import { toast } from 'sonner';
import { User, Package, Settings, ChevronRight, ChevronLeft, Loader2 } from 'lucide-react';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

export const ProfilePage = () => {
  const { t, language, isRTL, formatPrice } = useLanguage();
  const { user, updateProfile, loading: authLoading } = useAuth();
  const navigate = useNavigate();

  const [orders, setOrders] = useState([]);
  const [wilayas, setWilayas] = useState([]);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    address: '',
    wilaya: ''
  });

  const ChevronIcon = isRTL ? ChevronLeft : ChevronRight;

  useEffect(() => {
    if (!user && !authLoading) {
      navigate('/login');
    }
  }, [user, authLoading]);

  useEffect(() => {
    if (user) {
      setFormData({
        name: user.name || '',
        phone: user.phone || '',
        address: user.address || '',
        wilaya: user.wilaya || ''
      });
      fetchOrders();
      fetchWilayas();
    }
  }, [user]);

  const fetchOrders = async () => {
    try {
      setLoading(true);
      const response = await axios.get(`${API}/orders`, { withCredentials: true });
      setOrders(response.data);
    } catch (error) {
      console.error('Error fetching orders:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchWilayas = async () => {
    try {
      const response = await axios.get(`${API}/wilayas`);
      setWilayas(response.data);
    } catch (error) {
      console.error('Error fetching wilayas:', error);
    }
  };

  const handleSave = async (e) => {
    e.preventDefault();
    try {
      setSaving(true);
      await updateProfile(formData);
      toast.success(t('common.success'));
    } catch (error) {
      toast.error(t('common.error'));
    } finally {
      setSaving(false);
    }
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

  if (authLoading) {
    return (
      <div className="min-h-[60vh] flex items-center justify-center">
        <Loader2 className="h-8 w-8 animate-spin text-primary" />
      </div>
    );
  }

  if (!user) return null;

  return (
    <div className="min-h-screen bg-background" data-testid="profile-page">
      {/* Header */}
      <div className="bg-primary/5 py-8">
        <div className="container mx-auto px-4">
          <h1 className="text-3xl font-bold text-foreground mb-2">{t('profile.title')}</h1>
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <Link to="/" className="hover:text-primary">{t('nav.home')}</Link>
            <ChevronIcon className="h-4 w-4" />
            <span className="text-foreground">{t('profile.title')}</span>
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 py-8">
        <Tabs defaultValue="profile" className="space-y-8">
          <TabsList className="grid w-full max-w-md grid-cols-2 rounded-full p-1 bg-muted">
            <TabsTrigger value="profile" className="rounded-full data-[state=active]:bg-background">
              <User className="h-4 w-4 me-2" />
              {t('profile.personalInfo')}
            </TabsTrigger>
            <TabsTrigger value="orders" className="rounded-full data-[state=active]:bg-background">
              <Package className="h-4 w-4 me-2" />
              {t('profile.orders')}
            </TabsTrigger>
          </TabsList>

          {/* Profile Tab */}
          <TabsContent value="profile">
            <div className="max-w-2xl">
              <div className="bg-card rounded-3xl p-6 border">
                <div className="flex items-center gap-4 mb-6 pb-6 border-b">
                  {user.picture ? (
                    <img src={user.picture} alt={user.name} className="h-16 w-16 rounded-full" />
                  ) : (
                    <div className="h-16 w-16 rounded-full bg-primary/10 flex items-center justify-center">
                      <User className="h-8 w-8 text-primary" />
                    </div>
                  )}
                  <div>
                    <h2 className="text-xl font-bold">{user.name}</h2>
                    <p className="text-muted-foreground">{user.email}</p>
                  </div>
                </div>

                <form onSubmit={handleSave} className="space-y-4">
                  <div className="grid sm:grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <Label htmlFor="name">{t('checkout.name')}</Label>
                      <Input
                        id="name"
                        value={formData.name}
                        onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                        data-testid="profile-name"
                      />
                    </div>
                    
                    <div className="space-y-2">
                      <Label htmlFor="phone">{t('checkout.phone')}</Label>
                      <Input
                        id="phone"
                        type="tel"
                        value={formData.phone}
                        onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                        dir="ltr"
                        data-testid="profile-phone"
                      />
                    </div>
                    
                    <div className="space-y-2">
                      <Label htmlFor="wilaya">{t('checkout.wilaya')}</Label>
                      <Select
                        value={formData.wilaya}
                        onValueChange={(value) => setFormData({ ...formData, wilaya: value })}
                      >
                        <SelectTrigger data-testid="profile-wilaya">
                          <SelectValue placeholder={t('checkout.selectWilaya')} />
                        </SelectTrigger>
                        <SelectContent>
                          {wilayas.map((wilaya, index) => (
                            <SelectItem key={index} value={wilaya}>
                              {wilaya}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    </div>
                    
                    <div className="space-y-2 sm:col-span-2">
                      <Label htmlFor="address">{t('checkout.address')}</Label>
                      <Input
                        id="address"
                        value={formData.address}
                        onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                        data-testid="profile-address"
                      />
                    </div>
                  </div>

                  <Button 
                    type="submit" 
                    className="rounded-full"
                    disabled={saving}
                    data-testid="save-profile-btn"
                  >
                    {saving ? <Loader2 className="h-4 w-4 animate-spin" /> : t('profile.save')}
                  </Button>
                </form>
              </div>
            </div>
          </TabsContent>

          {/* Orders Tab */}
          <TabsContent value="orders">
            <div className="space-y-4">
              <h2 className="text-xl font-bold">{t('profile.orderHistory')}</h2>
              
              {loading ? (
                <div className="flex justify-center py-12">
                  <Loader2 className="h-8 w-8 animate-spin text-primary" />
                </div>
              ) : orders.length === 0 ? (
                <div className="text-center py-12 bg-muted/30 rounded-3xl">
                  <Package className="h-16 w-16 text-muted-foreground/30 mx-auto mb-4" />
                  <p className="text-muted-foreground">{t('profile.noOrders')}</p>
                  <Link to="/products">
                    <Button className="mt-4 rounded-full">
                      {t('cart.continueShopping')}
                    </Button>
                  </Link>
                </div>
              ) : (
                <div className="space-y-4">
                  {orders.map((order) => (
                    <div 
                      key={order.order_id}
                      className="bg-card rounded-2xl p-4 sm:p-6 border"
                      data-testid={`order-${order.order_id}`}
                    >
                      <div className="flex flex-wrap items-start justify-between gap-4 mb-4">
                        <div>
                          <p className="text-sm text-muted-foreground">
                            {t('checkout.orderNumber')}
                          </p>
                          <p className="font-mono font-semibold">{order.order_id}</p>
                        </div>
                        <Badge className={getStatusColor(order.status)}>
                          {t(`orders.${order.status}`)}
                        </Badge>
                      </div>

                      <div className="flex flex-wrap gap-2 mb-4">
                        {order.items.slice(0, 3).map((item, index) => (
                          <div key={index} className="text-sm bg-muted/50 px-3 py-1 rounded-full">
                            {item.name} x{item.quantity}
                          </div>
                        ))}
                        {order.items.length > 3 && (
                          <div className="text-sm bg-muted/50 px-3 py-1 rounded-full">
                            +{order.items.length - 3}
                          </div>
                        )}
                      </div>

                      <div className="flex flex-wrap items-center justify-between gap-4 pt-4 border-t">
                        <div>
                          <p className="text-sm text-muted-foreground">{t('orders.orderDate')}</p>
                          <p className="font-medium">
                            {new Date(order.created_at).toLocaleDateString(
                              language === 'ar' ? 'ar-DZ' : language === 'fr' ? 'fr-FR' : 'en-US'
                            )}
                          </p>
                        </div>
                        <div className="text-end">
                          <p className="text-sm text-muted-foreground">{t('orders.total')}</p>
                          <p className="text-lg font-bold text-primary">{formatPrice(order.total)}</p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
};

export default ProfilePage;
