# دليل الترحيل - AgroYousfi
## من Python/FastAPI إلى PHP/MySQL

---

## ✅ الميزات المكتملة في PHP Backend

### Authentication (المصادقة)
- ✅ `POST /auth/register` - تسجيل مستخدم جديد
- ✅ `POST /auth/login` - تسجيل الدخول
- ✅ `POST /auth/logout` - تسجيل الخروج
- ✅ `GET /auth/me` - معلومات المستخدم الحالي
- ✅ `PUT /auth/profile` - تحديث الملف الشخصي
- ✅ `POST /auth/forgot-password` - نسيت كلمة المرور
- ✅ `POST /auth/reset-password` - إعادة تعيين كلمة المرور

### Products (المنتجات)
- ✅ `GET /products` - قائمة المنتجات (مع فلترة حسب الفئة والبحث)
- ✅ `GET /products/{id}` - تفاصيل منتج
- ✅ `POST /products` - إضافة منتج (Admin)
- ✅ `PUT /products/{id}` - تحديث منتج (Admin)
- ✅ `DELETE /products/{id}` - حذف منتج (Admin)
- ✅ `GET /products-on-sale` - المنتجات ذات التخفيضات

### Categories (الفئات)
- ✅ `GET /categories` - قائمة الفئات
- ✅ `GET /categories/{id}` - تفاصيل فئة
- ✅ `POST /categories` - إضافة فئة (Admin)
- ✅ `PUT /categories/{id}` - تحديث فئة (Admin)
- ✅ `DELETE /categories/{id}` - حذف فئة (Admin)

### Wilayas (الولايات)
- ✅ `GET /wilayas` - قائمة الولايات الجزائرية (48 ولاية)

### Cart (سلة التسوق)
- ✅ `GET /cart` - عرض السلة
- ✅ `POST /cart` - إضافة منتج للسلة
- ✅ `PUT /cart` - تحديث كمية منتج
- ✅ `DELETE /cart` - مسح السلة
- ✅ `DELETE /cart/{product_id}` - حذف منتج من السلة

### Addresses (العناوين)
- ✅ `GET /addresses` - عناوين المستخدم
- ✅ `POST /addresses` - إضافة عنوان
- ✅ `PUT /addresses/{id}` - تحديث عنوان
- ✅ `DELETE /addresses/{id}` - حذف عنوان

### Orders (الطلبات)
- ✅ `GET /orders` - قائمة الطلبات
- ✅ `POST /orders` - إنشاء طلب جديد
- ✅ `GET /orders/{id}` - تفاصيل طلب
- ✅ `GET /orders/my` - طلباتي
- ✅ `PUT /orders/{id}/status` - تحديث حالة الطلب (Admin)

### Reviews (التقييمات)
- ✅ `GET /reviews/{product_id}` - تقييمات منتج
- ✅ `POST /reviews` - إضافة تقييم

### Wishlist (قائمة الأمنيات)
- ✅ `GET /wishlist` - قائمة الأمنيات
- ✅ `POST /wishlist/{product_id}` - إضافة للأمنيات
- ✅ `DELETE /wishlist/{product_id}` - حذف من الأمنيات

### Browsing History (سجل التصفح)
- ✅ `GET /history` - سجل التصفح
- ✅ `POST /history/{product_id}` - إضافة للسجل
- ✅ `DELETE /history` - مسح السجل

### Upload (رفع الملفات)
- ✅ `POST /upload` - رفع صورة (Admin)
- ✅ `GET /uploads/{filename}` - عرض صورة مرفوعة

### Admin Dashboard
- ✅ `GET /admin/dashboard` - إحصائيات لوحة التحكم
- ✅ `GET /admin/users` - قائمة المستخدمين
- ✅ `GET /admin/orders` - قائمة الطلبات
- ✅ `GET /admin/orders/unprocessed` - الطلبات غير المعالجة

---

## ⚠️ الميزات غير المكتملة

### Google OAuth
- ❌ `GET /auth/google` - تسجيل دخول Google
- ❌ `POST /auth/session` - معالجة جلسة Google

**ملاحظة:** لتفعيل Google OAuth، استخدم مكتبة `league/oauth2-google`

---

## 📦 هيكل قاعدة البيانات MySQL

### الجداول المطلوبة:
```sql
- users
- sessions
- categories
- products
- product_images
- orders
- order_items
- reviews
- wishlists
- browsing_history
- addresses
- carts
- cart_items
```

**ملاحظة:** قم باستيراد ملف `database.sql` لإنشاء جميع الجداول

---

## 🔧 إعداد السيرفر (cPanel)

### 1. إنشاء قاعدة البيانات:
```
- اذهب إلى MySQL Databases
- أنشئ قاعدة بيانات: agroyousfi
- أنشئ مستخدم وأعطه صلاحيات كاملة
```

### 2. إعداد ملف config/database.php:
```php
<?php
private $host = 'localhost';
private $db_name = 'your_database_name';
private $username = 'your_username';
private $password = 'your_password';
```

### 3. استيراد قاعدة البيانات:
```
- اذهب إلى phpMyAdmin
- اختر قاعدة البيانات
- استورد database.sql
```

### 4. رفع ملفات PHP:
```
- ارفع مجلد php-backend إلى public_html/api
```

### 5. إعداد .htaccess للـ API:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /api/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L,QSA]
</IfModule>
```

---

## 🖥️ تغييرات Frontend

### 1. تحديث .env:
```env
# القديم
REACT_APP_BACKEND_URL=https://your-domain.com

# الجديد (لا تغيير إذا كان الـ API في /api)
REACT_APP_BACKEND_URL=https://your-domain.com
```

### 2. بناء Frontend:
```bash
cd frontend
yarn build
# ارفع محتويات build/ إلى public_html/
```

### 3. إعداد .htaccess للـ React Router:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^api/ - [L]
    RewriteRule ^index\.html$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.html [L]
</IfModule>
```

---

## 🧪 اختبار الـ API

```bash
# اختبار الـ API
curl https://your-domain.com/api/

# جلب المنتجات
curl https://your-domain.com/api/products

# جلب الفئات
curl https://your-domain.com/api/categories

# جلب الولايات
curl https://your-domain.com/api/wilayas

# تسجيل الدخول
curl -X POST https://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"identifier":"admin@agroyousfi.dz","password":"admin123"}'
```

---

## 📝 بيانات الاختبار

### Admin Account:
- **Email:** admin@agroyousfi.dz
- **Password:** admin123

---

## 📁 هيكل الملفات

```
php-backend/
├── config/
│   ├── cors.php          # إعدادات CORS
│   └── database.php      # اتصال قاعدة البيانات
├── controllers/
│   ├── AuthController.php
│   ├── ProductController.php
│   ├── CategoryController.php
│   ├── OrderController.php
│   ├── ReviewController.php
│   ├── WishlistController.php
│   ├── AdminController.php
│   ├── CartController.php
│   ├── AddressController.php
│   ├── BrowsingHistoryController.php
│   └── UploadController.php
├── models/
│   ├── User.php
│   ├── Product.php
│   ├── Category.php
│   ├── Order.php
│   ├── Review.php
│   ├── Wishlist.php
│   ├── Cart.php
│   ├── Address.php
│   └── BrowsingHistory.php
├── middleware/
│   └── auth.php          # التحقق من المصادقة
├── utils/
│   └── helpers.php       # دوال مساعدة
├── data/
│   └── wilayas.php       # بيانات الولايات
├── uploads/              # مجلد الصور المرفوعة
├── .htaccess            # إعادة توجيه URLs
├── index.php            # نقطة الدخول الرئيسية
├── database.sql         # مخطط قاعدة البيانات
├── config.example.php   # مثال ملف الإعدادات
└── README.md            # توثيق
```
