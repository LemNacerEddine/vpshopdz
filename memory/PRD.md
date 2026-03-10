# VPShopDZ - منصة تجارة إلكترونية متعددة المتاجر 🇩🇿

## المعلومات الأساسية
- **الاسم**: VPShopDZ
- **الوصف**: منصة مشابهة لـ Shopify و FlexDZ مخصصة للسوق الجزائري
- **التاريخ**: يناير 2026
- **الحالة**: قيد التطوير - هيكل Laravel 11 جاهز

## تاريخ المشروع
- **الأصل**: بدأ كمتجر "AgroYousfi" (Python/FastAPI + MongoDB)
- **التحول**: تم التحول إلى منصة Multi-tenant بـ Laravel 11
- **السبب**: متطلبات استضافة cPanel والحاجة لمنصة قابلة للتوسع

## الشخصيات المستهدفة (User Personas)
1. **التاجر الجزائري**: يريد إنشاء متجر إلكتروني بسهولة
2. **صاحب العمل الصغير**: يحتاج واجهة عربية ودعم شركات التوصيل المحلية
3. **البائع المحترف**: يحتاج ميزات متقدمة مثل API و تخصيص النطاق

## المتطلبات الأساسية (Core Requirements)
- ✅ بنية Multi-tenant (متاجر متعددة)
- ✅ نظام اشتراكات مرن (5 باقات)
- ✅ دعم 58 ولاية جزائرية
- ✅ دعم 1011 بلدية
- ✅ دعم 60+ شركة توصيل جزائرية
- ⏳ لوحة تحكم التاجر
- ⏳ لوحة تحكم المنصة (Super Admin)
- ⏳ واجهة المتجر العامة

## ما تم تنفيذه

### يناير 2026 - هيكل Laravel 11:

#### Database Migrations ✅
- `stores` - جدول المتاجر الرئيسي
- `users` - المستخدمين مع الأدوار
- `subscription_plans` - باقات الاشتراك
- `store_subscriptions` - اشتراكات المتاجر
- `payments` - سجل المدفوعات
- `categories` - فئات المنتجات
- `products` - المنتجات
- `product_images` - صور المنتجات
- `product_variants` - متغيرات المنتج
- `orders` - الطلبات
- `order_items` - عناصر الطلبات
- `customers` - عملاء المتاجر
- `wilayas` - الولايات (58)
- `communes` - البلديات (1011)
- `shipping_companies` - شركات التوصيل (60+)
- `shipping_rates` - أسعار التوصيل
- `store_shipping_settings` - إعدادات الشحن للمتجر
- `reviews` - التقييمات
- `coupons` - الكوبونات
- `wishlists` - قوائم الأمنيات
- `store_analytics` - إحصائيات المتجر

#### Models ✅
- Store, User, Product, Category, Order
- SubscriptionPlan, StoreSubscription
- Wilaya, Commune, ShippingCompany, ShippingRate
- StoreShippingSetting, Customer

#### Seeders ✅
- SubscriptionPlanSeeder (5 باقات)
- WilayaSeeder (58 ولاية)
- CommuneSeeder (1011 بلدية)
- ShippingCompanySeeder (60+ شركة)

#### Controllers ✅
- AuthController (placeholder)
- ProductController (placeholder)
- OrderController (placeholder)
- ShippingController (مكتمل)

#### API Routes ✅
- Authentication routes
- Public store routes
- Dashboard routes (protected)
- Admin routes (protected)

## باقات الاشتراك

| الباقة | السعر/شهر | المنتجات | الطلبات/شهر |
|--------|-----------|----------|-------------|
| تجريبي | مجاني (7 أيام) | 10 | 20 |
| بداية | 800 دج | 50 | 100 |
| نمو | 1,500 دج | غير محدود | 500 |
| احترافي | 3,000 دج | غير محدود | 2,000 |
| غير محدود | 5,000 دج | غير محدود | غير محدود |

## الملفات الرئيسية
- `/app/laravel-platform/` - مشروع Laravel 11
- `/app/vpshopdz-laravel.zip` - ملف مضغوط للنشر
- `/app/frontend/` - React Frontend (مؤجل)

## المهام المنجزة ✅
1. ✅ هيكل المشروع Laravel 11
2. ✅ تصميم قاعدة البيانات Multi-tenant
3. ✅ بيانات الولايات (58 ولاية)
4. ✅ بيانات البلديات (1011 بلدية)
5. ✅ بيانات شركات التوصيل (60+ شركة)
6. ✅ Models للشحن والتوصيل
7. ✅ ShippingController API

## المهام القادمة (P1)
1. ⏳ إكمال AuthController (تسجيل/دخول)
2. ⏳ إكمال ProductController (CRUD)
3. ⏳ إكمال OrderController (CRUD)
4. ⏳ Middleware للـ Multi-tenancy
5. ⏳ تشغيل المشروع على Laragon

## المهام المستقبلية (P2/P3)
- [ ] لوحة تحكم التاجر (Livewire/Filament)
- [ ] لوحة تحكم المنصة
- [ ] واجهة المتجر العامة
- [ ] نظام الدفع (CCP/BaridiMob)
- [ ] ربط API شركات التوصيل
- [ ] نظام الإشعارات (WhatsApp)
- [ ] تخصيص النطاقات

## ملاحظات تقنية
- **Stack**: Laravel 11 + MySQL/PostgreSQL
- **Auth**: Laravel Sanctum
- **Admin Panel**: Filament (مخطط)
- **Deployment**: VPS/cPanel
