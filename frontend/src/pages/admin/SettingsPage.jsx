import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  Save,
  Store,
  Truck,
  CreditCard,
  Users,
  Bell,
  Shield,
  Globe,
  Loader2
} from 'lucide-react';
import { toast } from 'sonner';

const SettingsPage = () => {
  const { language, isRTL } = useLanguage();
  const [saving, setSaving] = useState(false);
  const [activeTab, setActiveTab] = useState('store');

  // Store Settings
  const [storeSettings, setStoreSettings] = useState({
    name: 'AgroYousfi',
    email: 'contact@agroyousfi.dz',
    phone: '+213 555 123 456',
    address: 'الجزائر العاصمة',
    currency: 'DZD',
    language: 'ar',
    description: 'متجر المنتجات الزراعية الأول في الجزائر'
  });

  // Shipping Settings
  const [shippingSettings, setShippingSettings] = useState({
    freeShippingThreshold: 10000,
    defaultShippingCost: 500,
    expressShippingCost: 1000,
    enableFreeShipping: true,
    shippingZones: [
      { zone: 'الجزائر العاصمة', cost: 300 },
      { zone: 'وهران', cost: 500 },
      { zone: 'قسنطينة', cost: 600 },
      { zone: 'مناطق أخرى', cost: 800 }
    ]
  });

  // Tax Settings
  const [taxSettings, setTaxSettings] = useState({
    enableTax: false,
    taxRate: 19,
    taxIncludedInPrice: true
  });

  // Notification Settings
  const [notificationSettings, setNotificationSettings] = useState({
    emailOnNewOrder: true,
    emailOnLowStock: true,
    smsOnNewOrder: false,
    lowStockThreshold: 10
  });

  const l = {
    ar: {
      settings: 'الإعدادات',
      storeSettings: 'إعدادات المتجر',
      shipping: 'الشحن',
      taxes: 'الضرائب',
      notifications: 'الإشعارات',
      users: 'المستخدمين',
      save: 'حفظ التغييرات',
      saving: 'جاري الحفظ...',
      saved: 'تم حفظ الإعدادات',
      
      // Store
      storeName: 'اسم المتجر',
      storeEmail: 'البريد الإلكتروني',
      storePhone: 'رقم الهاتف',
      storeAddress: 'العنوان',
      storeCurrency: 'العملة',
      storeLanguage: 'اللغة الافتراضية',
      storeDescription: 'وصف المتجر',
      
      // Shipping
      freeShipping: 'الشحن المجاني',
      enableFreeShipping: 'تفعيل الشحن المجاني',
      freeShippingThreshold: 'الحد الأدنى للشحن المجاني',
      defaultShippingCost: 'تكلفة الشحن الافتراضية',
      expressShipping: 'الشحن السريع',
      shippingZones: 'مناطق الشحن',
      zone: 'المنطقة',
      cost: 'التكلفة',
      
      // Tax
      enableTax: 'تفعيل الضرائب',
      taxRate: 'نسبة الضريبة (%)',
      taxIncluded: 'الضريبة مشمولة في السعر',
      
      // Notifications
      emailOnNewOrder: 'إشعار بريدي عند طلب جديد',
      emailOnLowStock: 'إشعار بريدي عند انخفاض المخزون',
      smsOnNewOrder: 'إشعار SMS عند طلب جديد',
      lowStockThreshold: 'حد المخزون المنخفض',
      
      // Users
      userRole: 'دور المستخدم',
      admin: 'مدير',
      staff: 'موظف',
      adminDesc: 'صلاحيات كاملة',
      staffDesc: 'إدارة الطلبات والمنتجات فقط'
    },
    fr: {
      settings: 'Paramètres',
      storeSettings: 'Paramètres du magasin',
      shipping: 'Livraison',
      taxes: 'Taxes',
      notifications: 'Notifications',
      users: 'Utilisateurs',
      save: 'Enregistrer',
      saving: 'Enregistrement...',
      saved: 'Paramètres enregistrés',
      storeName: 'Nom du magasin',
      storeEmail: 'Email',
      storePhone: 'Téléphone',
      storeAddress: 'Adresse',
      storeCurrency: 'Devise',
      storeLanguage: 'Langue par défaut',
      storeDescription: 'Description',
      freeShipping: 'Livraison gratuite',
      enableFreeShipping: 'Activer la livraison gratuite',
      freeShippingThreshold: 'Seuil de livraison gratuite',
      defaultShippingCost: 'Coût de livraison par défaut',
      expressShipping: 'Livraison express',
      shippingZones: 'Zones de livraison',
      zone: 'Zone',
      cost: 'Coût',
      enableTax: 'Activer les taxes',
      taxRate: 'Taux de taxe (%)',
      taxIncluded: 'Taxe incluse dans le prix',
      emailOnNewOrder: 'Email pour nouvelle commande',
      emailOnLowStock: 'Email pour stock faible',
      smsOnNewOrder: 'SMS pour nouvelle commande',
      lowStockThreshold: 'Seuil de stock faible',
      userRole: "Rôle d'utilisateur",
      admin: 'Admin',
      staff: 'Staff',
      adminDesc: 'Accès complet',
      staffDesc: 'Commandes et produits uniquement'
    },
    en: {
      settings: 'Settings',
      storeSettings: 'Store Settings',
      shipping: 'Shipping',
      taxes: 'Taxes',
      notifications: 'Notifications',
      users: 'Users',
      save: 'Save Changes',
      saving: 'Saving...',
      saved: 'Settings saved',
      storeName: 'Store Name',
      storeEmail: 'Email',
      storePhone: 'Phone',
      storeAddress: 'Address',
      storeCurrency: 'Currency',
      storeLanguage: 'Default Language',
      storeDescription: 'Description',
      freeShipping: 'Free Shipping',
      enableFreeShipping: 'Enable Free Shipping',
      freeShippingThreshold: 'Free Shipping Threshold',
      defaultShippingCost: 'Default Shipping Cost',
      expressShipping: 'Express Shipping',
      shippingZones: 'Shipping Zones',
      zone: 'Zone',
      cost: 'Cost',
      enableTax: 'Enable Tax',
      taxRate: 'Tax Rate (%)',
      taxIncluded: 'Tax included in price',
      emailOnNewOrder: 'Email on new order',
      emailOnLowStock: 'Email on low stock',
      smsOnNewOrder: 'SMS on new order',
      lowStockThreshold: 'Low stock threshold',
      userRole: 'User Role',
      admin: 'Admin',
      staff: 'Staff',
      adminDesc: 'Full access',
      staffDesc: 'Orders and products only'
    }
  };

  const text = l[language] || l.ar;

  const handleSave = async () => {
    try {
      setSaving(true);
      // In production, save to backend
      await new Promise(resolve => setTimeout(resolve, 1000));
      toast.success(text.saved);
    } catch (error) {
      toast.error('Error saving settings');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">{text.settings}</h1>
        </div>
        <Button onClick={handleSave} disabled={saving}>
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

      <Tabs value={activeTab} onValueChange={setActiveTab}>
        <TabsList className="grid grid-cols-2 lg:grid-cols-5 w-full">
          <TabsTrigger value="store" className="flex items-center gap-2">
            <Store className="h-4 w-4" />
            <span className="hidden sm:inline">{text.storeSettings}</span>
          </TabsTrigger>
          <TabsTrigger value="shipping" className="flex items-center gap-2">
            <Truck className="h-4 w-4" />
            <span className="hidden sm:inline">{text.shipping}</span>
          </TabsTrigger>
          <TabsTrigger value="taxes" className="flex items-center gap-2">
            <CreditCard className="h-4 w-4" />
            <span className="hidden sm:inline">{text.taxes}</span>
          </TabsTrigger>
          <TabsTrigger value="notifications" className="flex items-center gap-2">
            <Bell className="h-4 w-4" />
            <span className="hidden sm:inline">{text.notifications}</span>
          </TabsTrigger>
          <TabsTrigger value="users" className="flex items-center gap-2">
            <Users className="h-4 w-4" />
            <span className="hidden sm:inline">{text.users}</span>
          </TabsTrigger>
        </TabsList>

        {/* Store Settings */}
        <TabsContent value="store" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>{text.storeSettings}</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>{text.storeName}</Label>
                  <Input
                    value={storeSettings.name}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, name: e.target.value }))}
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.storeEmail}</Label>
                  <Input
                    type="email"
                    value={storeSettings.email}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, email: e.target.value }))}
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.storePhone}</Label>
                  <Input
                    value={storeSettings.phone}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, phone: e.target.value }))}
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.storeAddress}</Label>
                  <Input
                    value={storeSettings.address}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, address: e.target.value }))}
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.storeCurrency}</Label>
                  <Select value={storeSettings.currency} onValueChange={(v) => setStoreSettings(prev => ({ ...prev, currency: v }))}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="DZD">د.ج (DZD)</SelectItem>
                      <SelectItem value="EUR">€ (EUR)</SelectItem>
                      <SelectItem value="USD">$ (USD)</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label>{text.storeLanguage}</Label>
                  <Select value={storeSettings.language} onValueChange={(v) => setStoreSettings(prev => ({ ...prev, language: v }))}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="ar">العربية</SelectItem>
                      <SelectItem value="fr">Français</SelectItem>
                      <SelectItem value="en">English</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
              <div className="space-y-2">
                <Label>{text.storeDescription}</Label>
                <Textarea
                  value={storeSettings.description}
                  onChange={(e) => setStoreSettings(prev => ({ ...prev, description: e.target.value }))}
                  rows={3}
                />
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Shipping Settings */}
        <TabsContent value="shipping" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>{text.freeShipping}</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between">
                <Label>{text.enableFreeShipping}</Label>
                <Switch
                  checked={shippingSettings.enableFreeShipping}
                  onCheckedChange={(checked) => setShippingSettings(prev => ({ ...prev, enableFreeShipping: checked }))}
                />
              </div>
              {shippingSettings.enableFreeShipping && (
                <div className="space-y-2">
                  <Label>{text.freeShippingThreshold} (DZD)</Label>
                  <Input
                    type="number"
                    value={shippingSettings.freeShippingThreshold}
                    onChange={(e) => setShippingSettings(prev => ({ ...prev, freeShippingThreshold: parseInt(e.target.value) }))}
                  />
                </div>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>{text.shippingZones}</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {shippingSettings.shippingZones.map((zone, idx) => (
                  <div key={idx} className="flex items-center gap-4">
                    <Input value={zone.zone} className="flex-1" readOnly />
                    <Input
                      type="number"
                      value={zone.cost}
                      onChange={(e) => {
                        const newZones = [...shippingSettings.shippingZones];
                        newZones[idx].cost = parseInt(e.target.value);
                        setShippingSettings(prev => ({ ...prev, shippingZones: newZones }));
                      }}
                      className="w-32"
                    />
                    <span className="text-sm text-muted-foreground">DZD</span>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Tax Settings */}
        <TabsContent value="taxes" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>{text.taxes}</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between">
                <Label>{text.enableTax}</Label>
                <Switch
                  checked={taxSettings.enableTax}
                  onCheckedChange={(checked) => setTaxSettings(prev => ({ ...prev, enableTax: checked }))}
                />
              </div>
              {taxSettings.enableTax && (
                <>
                  <div className="space-y-2">
                    <Label>{text.taxRate}</Label>
                    <Input
                      type="number"
                      value={taxSettings.taxRate}
                      onChange={(e) => setTaxSettings(prev => ({ ...prev, taxRate: parseFloat(e.target.value) }))}
                    />
                  </div>
                  <div className="flex items-center justify-between">
                    <Label>{text.taxIncluded}</Label>
                    <Switch
                      checked={taxSettings.taxIncludedInPrice}
                      onCheckedChange={(checked) => setTaxSettings(prev => ({ ...prev, taxIncludedInPrice: checked }))}
                    />
                  </div>
                </>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        {/* Notification Settings */}
        <TabsContent value="notifications" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>{text.notifications}</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex items-center justify-between">
                <Label>{text.emailOnNewOrder}</Label>
                <Switch
                  checked={notificationSettings.emailOnNewOrder}
                  onCheckedChange={(checked) => setNotificationSettings(prev => ({ ...prev, emailOnNewOrder: checked }))}
                />
              </div>
              <div className="flex items-center justify-between">
                <Label>{text.emailOnLowStock}</Label>
                <Switch
                  checked={notificationSettings.emailOnLowStock}
                  onCheckedChange={(checked) => setNotificationSettings(prev => ({ ...prev, emailOnLowStock: checked }))}
                />
              </div>
              <div className="flex items-center justify-between">
                <Label>{text.smsOnNewOrder}</Label>
                <Switch
                  checked={notificationSettings.smsOnNewOrder}
                  onCheckedChange={(checked) => setNotificationSettings(prev => ({ ...prev, smsOnNewOrder: checked }))}
                />
              </div>
              <div className="space-y-2">
                <Label>{text.lowStockThreshold}</Label>
                <Input
                  type="number"
                  value={notificationSettings.lowStockThreshold}
                  onChange={(e) => setNotificationSettings(prev => ({ ...prev, lowStockThreshold: parseInt(e.target.value) }))}
                />
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Users Settings */}
        <TabsContent value="users" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>{text.users}</CardTitle>
              <CardDescription>
                {language === 'ar' ? 'إدارة صلاحيات المستخدمين' : 'Manage user permissions'}
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="p-4 border rounded-lg">
                  <div className="flex items-center gap-3 mb-3">
                    <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                      <Shield className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                      <h3 className="font-semibold">{text.admin}</h3>
                      <p className="text-sm text-muted-foreground">{text.adminDesc}</p>
                    </div>
                  </div>
                  <ul className="text-sm space-y-1 text-muted-foreground">
                    <li>• {language === 'ar' ? 'إدارة المنتجات' : 'Manage products'}</li>
                    <li>• {language === 'ar' ? 'إدارة الطلبات' : 'Manage orders'}</li>
                    <li>• {language === 'ar' ? 'إدارة العملاء' : 'Manage customers'}</li>
                    <li>• {language === 'ar' ? 'إدارة الإعدادات' : 'Manage settings'}</li>
                    <li>• {language === 'ar' ? 'عرض التقارير' : 'View reports'}</li>
                  </ul>
                </div>
                <div className="p-4 border rounded-lg">
                  <div className="flex items-center gap-3 mb-3">
                    <div className="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                      <Users className="h-5 w-5 text-blue-600" />
                    </div>
                    <div>
                      <h3 className="font-semibold">{text.staff}</h3>
                      <p className="text-sm text-muted-foreground">{text.staffDesc}</p>
                    </div>
                  </div>
                  <ul className="text-sm space-y-1 text-muted-foreground">
                    <li>• {language === 'ar' ? 'عرض المنتجات' : 'View products'}</li>
                    <li>• {language === 'ar' ? 'إدارة الطلبات' : 'Manage orders'}</li>
                    <li>• {language === 'ar' ? 'عرض العملاء' : 'View customers'}</li>
                    <li className="text-red-500">• {language === 'ar' ? 'لا يمكن تعديل الإعدادات' : 'Cannot edit settings'}</li>
                  </ul>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
};

export default SettingsPage;
