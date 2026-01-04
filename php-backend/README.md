# AgroYousfi - PHP + MySQL Backend

## متطلبات التشغيل

- PHP 7.4+ (يفضل 8.0+)
- MySQL 5.7+ / MariaDB 10.3+
- Apache مع mod_rewrite
- PDO PHP Extension

## خطوات التثبيت

### 1. إعداد قاعدة البيانات

```bash
# تسجيل الدخول إلى MySQL
mysql -u root -p

# تنفيذ ملف الـ schema
source /path/to/database.sql;
```

أو من cPanel:
1. اذهب إلى phpMyAdmin
2. أنشئ قاعدة بيانات جديدة باسم `agroyousfi`
3. استورد ملف `database.sql`

### 2. إعداد التطبيق

```bash
# انسخ config.example.php إلى config.php
cp config.example.php config.php

# عدل الإعدادات
nano config.php
```

أو عدل `config/database.php` مباشرة:

```php
$this->host = 'localhost';
$this->db_name = 'agroyousfi';
$this->username = 'your_username';
$this->password = 'your_password';
```

### 3. رفع الملفات

ارفع مجلد `php-backend` إلى مجلد `api` في استضافتك:

```
public_html/
├── api/                 ← ملفات PHP
│   ├── config/
│   ├── controllers/
│   ├── models/
│   ├── middleware/
│   ├── utils/
│   ├── index.php
│   └── .htaccess
└── (frontend files)    ← ملفات React المبنية
```

### 4. إعداد Frontend

عدل ملف `.env` في الـ Frontend:

```
REACT_APP_BACKEND_URL=https://yourdomain.com/api
```

ثم ابنِ التطبيق:

```bash
cd frontend
npm install
npm run build
```

ارفع محتويات مجلد `build` إلى `public_html`.

## هيكل الـ API

### Authentication
| Endpoint | Method | الوصف |
|----------|--------|------|
| `/api/auth/register` | POST | تسجيل جديد |
| `/api/auth/login` | POST | تسجيل دخول |
| `/api/auth/logout` | POST | تسجيل خروج |
| `/api/auth/me` | GET | المستخدم الحالي |
| `/api/auth/profile` | PUT | تحديث الملف الشخصي |
| `/api/auth/forgot-password` | POST | طلب إعادة تعيين كلمة المرور |
| `/api/auth/reset-password` | POST | إعادة تعيين كلمة المرور |

### Products
| Endpoint | Method | الوصف |
|----------|--------|------|
| `/api/products` | GET | قائمة المنتجات |
| `/api/products/{id}` | GET | تفاصيل منتج |
| `/api/products` | POST | إضافة منتج (مدير) |
| `/api/products/{id}` | PUT | تعديل منتج (مدير) |
| `/api/products/{id}` | DELETE | حذف منتج (مدير) |
| `/api/products-on-sale` | GET | المنتجات المخفضة |

### Categories
| Endpoint | Method | الوصف |
|----------|--------|------|
| `/api/categories` | GET | قائمة الأقسام |
| `/api/categories/{id}` | GET | تفاصيل قسم |

### Orders
| Endpoint | Method | الوصف |
|----------|--------|------|
| `/api/orders` | GET | قائمة الطلبات (مدير) |
| `/api/orders/my` | GET | طلباتي |
| `/api/orders` | POST | إنشاء طلب |
| `/api/orders/{id}/status` | PUT | تحديث حالة الطلب (مدير) |

### Wishlist
| Endpoint | Method | الوصف |
|----------|--------|------|
| `/api/wishlist` | GET | قائمة المفضلة |
| `/api/wishlist/{product_id}` | POST | إضافة للمفضلة |
| `/api/wishlist/{product_id}` | DELETE | إزالة من المفضلة |

### Admin
| Endpoint | Method | الوصف |
|----------|--------|------|
| `/api/admin/dashboard` | GET | إحصائيات لوحة التحكم |
| `/api/admin/users` | GET | قائمة المستخدمين |

## بيانات الدخول الافتراضية

**المدير:**
- البريد: `admin@agroyousfi.dz`
- كلمة المرور: `admin123`

## ملاحظات مهمة

1. **الأمان**: غيّر كلمة مرور المدير الافتراضية فوراً
2. **HTTPS**: استخدم شهادة SSL
3. **الصلاحيات**: تأكد من صلاحيات الملفات (755 للمجلدات، 644 للملفات)

## الدعم

للمساعدة، تواصل عبر support@agroyousfi.dz
