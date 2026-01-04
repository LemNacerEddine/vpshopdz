# دليل الترحيل - Frontend Updates

## التغييرات المطلوبة في Frontend

### 1. تغيير REACT_APP_BACKEND_URL

في ملف `frontend/.env`:

```
# القديم (FastAPI)
REACT_APP_BACKEND_URL=https://your-domain.com

# الجديد (PHP)
REACT_APP_BACKEND_URL=https://your-domain.com/api
```

### 2. تغييرات في AuthContext.js (اختياري)

إذا كانت الـ session تُحفظ بطريقة مختلفة:

الـ PHP يستخدم cookies للـ session بشكل افتراضي، وهذا متوافق مع الكود الحالي.

### 3. تغييرات في بعض الـ endpoints

| القديم (FastAPI) | الجديد (PHP) |
|------------------|--------------|
| `/api/products-on-sale` | `/products-on-sale` |
| `/api/admin/orders/{id}/status` | `/orders/{id}/status` |

**ملاحظة**: معظم الـ endpoints متطابقة تماماً.

### 4. Google OAuth

⚠️ الـ PHP version لا يتضمن Google OAuth حالياً.

إذا كنت تحتاجه، ستحتاج إلى:
1. إعداد Google OAuth Credentials جديدة
2. استخدام مكتبة PHP مثل `league/oauth2-google`

### 5. رفع الصور

الـ PHP version يقبل URLs للصور فقط حالياً.
لدعم رفع الصور المحلية، ستحتاج إلى إضافة:

```php
// في controllers/UploadController.php
// كود لرفع الصور إلى السيرفر
```

## خطوات الترحيل الكاملة

### على السيرفر (cPanel):

1. **إنشاء قاعدة البيانات:**
   - اذهب إلى MySQL Databases
   - أنشئ قاعدة بيانات: `agroyousfi`
   - أنشئ مستخدم وأعطه صلاحيات كاملة

2. **استيراد البيانات:**
   - اذهب إلى phpMyAdmin
   - اختر قاعدة البيانات
   - استورد `database.sql`

3. **رفع ملفات PHP:**
   - ارفع مجلد `php-backend` إلى `public_html/api`
   - عدل `config/database.php` بمعلومات قاعدة البيانات

4. **بناء ورفع Frontend:**
   ```bash
   cd frontend
   # عدل .env
   npm run build
   # ارفع محتويات build/ إلى public_html/
   ```

5. **إعداد .htaccess للـ React Router:**
   أنشئ ملف `.htaccess` في `public_html/`:
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

## اختبار الـ API

```bash
# اختبار الـ API
curl https://your-domain.com/api/

# تسجيل الدخول
curl -X POST https://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"identifier":"admin@agroyousfi.dz","password":"admin123"}'

# جلب المنتجات
curl https://your-domain.com/api/products
```
