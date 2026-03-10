# VPShopDZ - منصة تجارة إلكترونية متعددة المتاجر 🇩🇿

## المعلومات الأساسية
- **الاسم**: VPShopDZ
- **الوصف**: منصة مشابهة لـ Shopify و FlexDZ مخصصة للسوق الجزائري
- **التاريخ**: يناير 2026
- **الحالة**: ✅ MVP جاهز للاختبار

## ما تم تنفيذه ✅

### 1. نظام المصادقة (Auth System) ✅
- تسجيل تاجر جديد + إنشاء متجر تلقائي
- تسجيل الدخول / الخروج
- صفحات Login/Register بتصميم جميل
- Session-based auth للـ Web
- API Token auth (Sanctum) للـ API

### 2. لوحة تحكم التاجر (Dashboard) ✅
- **الرئيسية**: إحصائيات المتجر، آخر الطلبات، تنبيه المخزون
- **المنتجات**: عرض قائمة المنتجات مع الفلترة والبحث
- **الطلبات**: إدارة الطلبات مع إحصائيات الحالات
- **الإعدادات**: إعدادات المتجر، التواصل، السوشيال ميديا، SEO

### 3. API Endpoints ✅
#### Public APIs:
- `POST /api/v1/auth/register` - تسجيل جديد
- `POST /api/v1/auth/login` - تسجيل دخول
- `GET /api/v1/plans` - باقات الاشتراك
- `GET /api/v1/store/{slug}` - معلومات المتجر
- `GET /api/v1/store/{slug}/products` - منتجات المتجر
- `GET /api/v1/store/{slug}/categories` - فئات المتجر
- `GET /api/v1/store/{slug}/wilayas` - الولايات
- `GET /api/v1/store/{slug}/communes/{id}` - البلديات
- `POST /api/v1/store/{slug}/orders` - إنشاء طلب
- `GET /api/v1/store/{slug}/orders/{num}/track` - تتبع طلب

#### Protected APIs (Dashboard):
- `GET /api/v1/dashboard` - إحصائيات لوحة التحكم
- `GET/POST/PUT/DELETE /api/v1/dashboard/products` - CRUD منتجات
- `GET/POST/PUT/DELETE /api/v1/dashboard/categories` - CRUD فئات
- `GET /api/v1/dashboard/orders` - الطلبات
- `PUT /api/v1/dashboard/orders/{id}/status` - تحديث حالة الطلب
- `GET/PUT /api/v1/dashboard/settings` - إعدادات المتجر

### 4. قاعدة البيانات ✅
- 58 ولاية جزائرية
- 1011 بلدية
- 60+ شركة توصيل
- 5 باقات اشتراك

### 5. الواجهات ✅
- صفحة تسجيل الدخول
- صفحة إنشاء متجر جديد
- لوحة التحكم الرئيسية
- صفحة المنتجات
- صفحة الطلبات
- صفحة الإعدادات

## المهام القادمة 🟡

### P1 - أولوية عالية:
1. ⏳ واجهة المتجر العامة (Storefront)
   - الصفحة الرئيسية للمتجر
   - عرض المنتجات
   - سلة التسوق
   - صفحة الدفع (Checkout)

2. ⏳ نموذج إضافة/تعديل المنتجات
   - رفع الصور
   - إدارة المتغيرات

3. ⏳ إدارة الطلب التفصيلية
   - تغيير الحالة
   - إضافة tracking number

### P2 - أولوية متوسطة:
- لوحة تحكم المنصة (Super Admin)
- نظام الإشعارات
- ربط API شركات التوصيل (Yalidine)

### P3 - أولوية منخفضة:
- نظام الدفع الإلكتروني
- تخصيص النطاقات
- نظام الاشتراكات والفوترة

## بنية الملفات
```
laravel-platform/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/           # API Controllers
│   │   └── Web/           # Web Controllers
│   └── Models/            # Eloquent Models
├── database/
│   ├── migrations/        # Database structure
│   └── seeders/           # Initial data
├── resources/views/
│   ├── auth/              # Login/Register
│   ├── dashboard/         # Dashboard pages
│   └── layouts/           # Blade layouts
└── routes/
    ├── api.php            # API routes
    └── web.php            # Web routes
```

## خطوات التشغيل
```bash
# 1. فك الضغط
unzip vpshopdz-laravel.zip -d vpshopdz
cd vpshopdz

# 2. تثبيت الحزم
composer install

# 3. إعداد البيئة
cp .env.example .env
php artisan key:generate

# 4. إعداد قاعدة البيانات
# عدّل .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
php artisan migrate
php artisan db:seed

# 5. تشغيل السيرفر
php artisan serve
```

## روابط الاختبار
- الصفحة الرئيسية: http://127.0.0.1:8000/
- تسجيل متجر جديد: http://127.0.0.1:8000/register
- تسجيل الدخول: http://127.0.0.1:8000/login
- لوحة التحكم: http://127.0.0.1:8000/dashboard

---
**صنع بـ ❤️ في الجزائر 🇩🇿**
