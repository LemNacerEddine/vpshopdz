# VPShopDZ - منصة تجارة إلكترونية متعددة المتاجر 🇩🇿

منصة مشابهة لـ Shopify و FlexDZ مخصصة للسوق الجزائري.

## 🚀 المميزات

### للتجار:
- ✅ إنشاء متجر في ثوانٍ
- ✅ لوحة تحكم سهلة الاستخدام
- ✅ إدارة المنتجات والفئات
- ✅ إدارة الطلبات مع تتبع الحالة
- ✅ 58 ولاية جزائرية مدمجة
- ✅ Facebook/TikTok Pixel
- ✅ نظام الكوبونات
- ✅ تقارير وإحصائيات
- ✅ إشعارات WhatsApp للسلات المتروكة
- ✅ ربط شركات التوصيل

### للمنصة:
- ✅ Multi-tenancy (متاجر متعددة)
- ✅ نظام اشتراكات مرن
- ✅ لوحة تحكم Super Admin
- ✅ إدارة التجار والاشتراكات

---

## 🛠️ المتطلبات

- PHP 8.2+
- Composer 2+
- MySQL 8+ / MariaDB 10.5+
- Node.js 18+ (للـ Frontend)
- Redis (اختياري، للـ Cache)

---

## 📦 التثبيت

### 1. استنساخ المشروع
```bash
git clone https://github.com/your-username/vpshopdz-laravel.git
cd vpshopdz-laravel
```

### 2. تثبيت Dependencies
```bash
composer install
```

### 3. إعداد ملف البيئة
```bash
cp .env.example .env
php artisan key:generate
```

### 4. تعديل ملف .env
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vpshopdz
DB_USERNAME=root
DB_PASSWORD=your_password

PLATFORM_DOMAIN=vpshopdz.com
```

### 5. إنشاء قاعدة البيانات وتشغيل Migrations
```bash
php artisan migrate
php artisan db:seed
```

### 6. تشغيل السيرفر
```bash
php artisan serve
```

الموقع سيعمل على: http://localhost:8000

---

## 📁 هيكل المشروع

```
vpshopdz-laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/           # API Controllers
│   │   ├── Middleware/
│   │   └── Requests/          # Form Requests
│   ├── Models/                # Eloquent Models
│   └── Services/              # Business Logic
├── config/                    # Configuration files
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Data seeders
├── routes/
│   └── api.php               # API Routes
└── storage/                   # File storage
```

---

## 🔌 API Endpoints

### Authentication
```
POST   /api/v1/auth/register     - تسجيل تاجر جديد
POST   /api/v1/auth/login        - تسجيل الدخول
POST   /api/v1/auth/logout       - تسجيل الخروج
GET    /api/v1/auth/me           - معلومات المستخدم
```

### Store (Public)
```
GET    /api/v1/store/{slug}/products        - منتجات المتجر
GET    /api/v1/store/{slug}/categories      - فئات المتجر
POST   /api/v1/store/{slug}/orders          - إنشاء طلب
GET    /api/v1/store/{slug}/wilayas         - قائمة الولايات
```

### Dashboard (Protected)
```
GET    /api/v1/dashboard/stats              - إحصائيات
GET    /api/v1/dashboard/products           - المنتجات
POST   /api/v1/dashboard/products           - إضافة منتج
GET    /api/v1/dashboard/orders             - الطلبات
PUT    /api/v1/dashboard/orders/{id}/status - تحديث حالة الطلب
```

---

## 💰 باقات الاشتراك

| الباقة | السعر/شهر | المنتجات | الطلبات |
|--------|-----------|----------|---------|
| تجريبي | مجاني (7 أيام) | 10 | 20 |
| بداية | 800 دج | 50 | 100 |
| نمو | 1,500 دج | غير محدود | 500 |
| احترافي | 3,000 دج | غير محدود | 2,000 |
| غير محدود | 5,000 دج | غير محدود | غير محدود |

---

## 🚀 النشر (Deployment)

### على VPS (DigitalOcean/Hetzner)

1. **تثبيت المتطلبات:**
```bash
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl nginx mysql-server composer
```

2. **إعداد Nginx:**
```nginx
server {
    listen 80;
    server_name vpshopdz.com *.vpshopdz.com;
    root /var/www/vpshopdz/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

3. **إعداد SSL (مجاني):**
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d vpshopdz.com -d *.vpshopdz.com
```

---

## 📞 الدعم

- 📧 Email: support@vpshopdz.com
- 💬 WhatsApp: +213 xxx xxx xxx

---

## 📄 الترخيص

MIT License - مشروع مفتوح المصدر

---

**صنع بـ ❤️ في الجزائر 🇩🇿**
