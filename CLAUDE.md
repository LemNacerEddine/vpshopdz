# CLAUDE.md — AgroYousfi E-Commerce Platform

This file provides AI assistants with a complete guide to the codebase structure, conventions, and development workflows.

---

## Project Overview

**AgroYousfi** is a full-stack e-commerce platform for agricultural products in Algeria. It supports three languages (Arabic, French, English) with RTL layout, session-based authentication, Google OAuth, an admin dashboard, shipping management, and Facebook Pixel integration.

- **Frontend**: React 18 SPA with Tailwind CSS
- **Backend**: Pure PHP 7.4+ REST API (no framework)
- **Database**: MySQL 5.7+ via PDO
- **Hosting**: Apache server at `vpdeveloper.dz`

---

## Repository Layout

```
/
├── frontend/          # React 18 application (Create React App + CRACO)
├── php-backend/       # PHP REST API
├── README.md          # Project overview (Arabic + English)
└── CLAUDE.md          # This file
```

---

## Frontend (`frontend/`)

### Stack
| Tool | Version | Purpose |
|------|---------|---------|
| React | 18.2.0 | UI framework |
| React Router DOM | 7.5.1 | Client-side routing |
| Tailwind CSS | 3.4.17 | Utility-first styling |
| Radix UI | Various | Accessible UI primitives |
| React Hook Form | 7.56.2 | Form state management |
| Zod | 3.24.4 | Schema validation |
| Axios | 1.8.4 | HTTP client (with credentials) |
| Recharts | 3.6.0 | Admin dashboard charts |
| jsPDF | 3.0.4 | Invoice PDF generation |
| Sonner | 2.0.7 | Toast notifications |
| Lucide React | 0.507.0 | Icon library |

### Project Structure

```
frontend/src/
├── components/
│   ├── admin/          # Admin-only UI components
│   ├── layout/         # Navbar, Footer, Layout wrappers
│   ├── products/       # ProductCard, ProductGallery
│   └── ui/             # Shadcn/Radix UI component library (~30 files)
├── contexts/
│   ├── AuthContext.js          # Auth state, login/logout/register
│   ├── CartContext.js          # Cart state (localStorage fallback + server sync)
│   ├── LanguageContext.js      # i18n switcher (AR/FR/EN)
│   └── StoreSettingsContext.js # Store-wide configuration
├── hooks/
│   └── use-toast.js    # Toast notification hook
├── i18n/
│   └── translations.js # All UI strings in AR, FR, EN
├── lib/
│   ├── arabicPdf.js    # Arabic RTL PDF helper
│   ├── fbPixel.js      # Facebook Pixel integration
│   ├── invoiceHtml.js  # HTML invoice template
│   └── utils.js        # General utility functions (cn, etc.)
├── pages/
│   ├── admin/          # Admin dashboard pages (13 files)
│   └── *.jsx           # Customer-facing pages (13 files)
├── App.js              # Root router + context providers
└── index.js            # React entry point
```

### Key Pages

| Page | Path | Notes |
|------|------|-------|
| `HomePage.jsx` | `/` | Hero section, featured products |
| `ProductsPage.jsx` | `/products` | Filtering, search, pagination |
| `ProductDetailPage.jsx` | `/products/:id` | Gallery, reviews, add to cart |
| `CartPage.jsx` | `/cart` | Cart management |
| `CheckoutPage.jsx` | `/checkout` | Multi-step with shipping calculation |
| `ProfilePage.jsx` | `/profile` | Order history, addresses, settings |
| `LoginPage.jsx` | `/login` | Email/password + Google OAuth |
| `AdminDashboard.jsx` | `/admin/*` | Admin area root |
| `admin/DashboardHome.jsx` | `/admin` | Revenue charts, statistics |
| `admin/ProductsPage.jsx` | `/admin/products` | Product CRUD |
| `admin/OrdersPage.jsx` | `/admin/orders` | Order management |
| `admin/SettingsPage.jsx` | `/admin/settings` | Store configuration |

### Path Aliases
Defined in `craco.config.js` and `jsconfig.json`:
```js
@/ → frontend/src/
```

Use `@/components/ui/button` instead of relative paths.

### Development Commands

```bash
cd frontend
yarn install       # Install dependencies
yarn start         # Dev server at http://localhost:3000
yarn build         # Production build → frontend/build/
yarn test          # Run tests (React Scripts)
```

### Styling Conventions
- Use Tailwind utility classes exclusively; avoid custom CSS unless necessary
- RTL layout is driven by the `dir` attribute on `<html>` (set by `LanguageContext`)
- Dark mode is configured via Tailwind's `darkMode: 'class'`
- Component variants follow the `cva` (class-variance-authority) pattern used by Shadcn/ui

### Adding a New Page
1. Create `src/pages/NewPage.jsx`
2. Add a route in `src/App.js`
3. If admin-only, place in `src/pages/admin/` and use `AdminLayout`
4. Use the `useLanguage()` hook to support all three languages

### Internationalization
All translatable strings live in `src/i18n/translations.js`. Structure:
```js
export const translations = {
  ar: { key: "القيمة" },
  fr: { key: "valeur" },
  en: { key: "value" },
};
```
Access via `const { t, language } = useLanguage()` → `t('key')`.

### API Communication
Axios is configured in each component/page with:
```js
axios.defaults.withCredentials = true; // Session cookie auth
const API_BASE = "https://vpdeveloper.dz/agro-yousfi/api";
```
All requests must include `withCredentials: true` to send session cookies.

---

## Backend (`php-backend/`)

### Stack
- PHP 7.4+ (no framework — pure procedural PHP with OOP models)
- MySQL 5.7+ accessed via PDO
- Apache with `mod_rewrite` (`.htaccess`)
- Session-based authentication (30-day persistence in DB)

### Project Structure

```
php-backend/
├── config/
│   ├── database.php    # PDO connection (singleton)
│   ├── cors.php        # CORS headers & origin validation
│   └── env.php         # .env file loader
├── controllers/        # Request handlers (17 files)
├── models/             # Database abstraction (16 files)
├── middleware/
│   └── auth.php        # Session auth + role-based access
├── utils/
│   └── helpers.php     # Response helpers, session utilities
├── services/
│   └── FacebookApiService.php
├── data/
│   ├── wilayas.php     # 58 Algerian provinces
│   └── communes.php    # Algerian communes
├── index.php           # Router + entry point
├── database.sql        # Full DB schema (source of truth)
├── config.php          # Runtime configuration
└── config.example.php  # Config template for new setups
```

### Router Pattern
`index.php` implements a custom router. Routes are registered as:
```php
// Pattern: METHOD /path → Controller::method
// Route matching uses regex on $request_uri
```
All API requests go through `index.php` via `.htaccess` rewrite.

### Controller Conventions
```php
class ProductController {
    private $db;
    private $product;

    public function __construct($db) {
        $this->db = $db;
        $this->product = new Product($db);
    }

    public function getAll() {
        // 1. Validate input
        // 2. Call model
        // 3. Return JSON via helpers
    }
}
```
- Always inject `$db` (PDO) via constructor
- Use `helpers.php` response functions: `sendResponse()`, `sendError()`
- Validate user permissions via `$auth->requireAuth()` or `$auth->requireAdmin()`

### Model Conventions
```php
class Product {
    private $conn;
    private $table_name = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($filters) {
        // Always use prepared statements
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }
}
```
- Never use raw SQL string concatenation with user input — always use PDO prepared statements
- All models accept a `$db` PDO instance in the constructor

### Authentication
```php
require_once 'middleware/auth.php';
$auth = new Auth($db);

$auth->requireAuth();    // Throws 401 if not logged in
$auth->requireAdmin();   // Throws 403 if not admin
$user = $auth->getUser(); // Returns current user array
```

Sessions are stored in the `sessions` table. Session ID is sent as a cookie (`session_id`). Cookie lifetime: 7 days (`SESSION_LIFETIME = 604800`).

### Response Format
All API responses use JSON:
```json
// Success
{ "success": true, "data": { ... } }
{ "success": true, "message": "Created" }

// Error
{ "success": false, "message": "Error description" }
```
HTTP status codes: 200 OK, 201 Created, 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found, 500 Server Error.

### CORS Configuration
Allowed origins (in `config/cors.php`):
- `https://vpdeveloper.dz`
- `http://localhost:3000`
- `http://localhost:5173`
- `http://127.0.0.1:3000`
- `http://127.0.0.1:5173`

Always include `Access-Control-Allow-Credentials: true` — required for session cookies.

---

## Database Schema

Full schema is in `php-backend/database.sql`. Charset: `utf8mb4` (Arabic + emoji support).

### Key Tables

| Table | Primary Key | Notable Fields |
|-------|------------|---------------|
| `users` | `user_id` | `role` (customer/admin), `google_id`, `phone` |
| `sessions` | `session_id` | `user_id`, `expires_at` |
| `products` | `product_id` | `price`, `discount` (%), `stock`, `category_id` |
| `product_images` | `image_id` | `product_id`, `image_url` |
| `orders` | `order_id` | `status` (7 states), `user_id`, `total` |
| `order_items` | `order_item_id` | `order_id`, `product_id`, `quantity`, `price` |
| `categories` | `category_id` | `slug`, `parent_id` |
| `carts` | `cart_id` | `user_id`, `browser_id` (guest support) |
| `cart_items` | `cart_item_id` | `cart_id`, `product_id`, `quantity` |
| `shipping_companies` | `company_id` | `name`, `rate_type` |
| `shipping_rates` | `rate_id` | `company_id`, `wilaya`, `price` |
| `abandoned_checkouts` | `checkout_id` | `recovery_token`, `cart_data` (JSON) |
| `store_settings` | `setting_key` | `setting_value` (key-value pairs) |

### Order Status Flow
```
pending → confirmed → processing → shipped → delivered
                                          ↘ returned
                    ↘ cancelled
```

---

## API Reference

Base URL: `https://vpdeveloper.dz/agro-yousfi/api`
Local Dev: `http://localhost/agro-yousfi/api`

### Auth Endpoints
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | `/auth/register` | Public | Register new user |
| POST | `/auth/login` | Public | Email/password login |
| POST | `/auth/logout` | Required | Logout |
| GET | `/auth/me` | Required | Current user info |
| PUT | `/auth/profile` | Required | Update profile |
| POST | `/auth/forgot-password` | Public | Send reset email |
| POST | `/auth/reset-password` | Public | Reset with token |

### Products
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/products` | Public | List (search, filter, paginate) |
| GET | `/products/:id` | Public | Single product detail |
| POST | `/products` | Admin | Create product |
| PUT | `/products/:id` | Admin | Update product |
| DELETE | `/products/:id` | Admin | Delete product |
| GET | `/products-on-sale` | Public | Discounted products |

### Orders
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/orders` | Admin | All orders |
| GET | `/orders/my` | Required | User's orders |
| GET | `/orders/:id` | Required | Order details |
| POST | `/orders` | Required | Create order |
| PUT | `/orders/:id/status` | Admin | Update status |

### Other Key Endpoints
- `GET /categories` — All categories
- `GET /cart?browser_id=...` — Cart (supports guest via `browser_id`)
- `POST /cart/add` — Add to cart
- `GET /wishlist` — User wishlist
- `GET /wilayas` — 58 Algerian provinces
- `GET /communes?wilaya_id=...` — Communes by province
- `POST /upload` — Image upload (multipart/form-data)
- `GET /admin/dashboard` — Statistics (admin only)

---

## Development Workflow

### Setting Up Locally
```bash
# Clone and start frontend
cd frontend
yarn install
yarn start    # http://localhost:3000

# Backend: Point Apache/XAMPP to php-backend/
# Or use PHP's built-in server:
cd php-backend
php -S localhost:8000
```

### Making Changes

**Frontend component change:**
1. Find the file in `src/components/` or `src/pages/`
2. All UI uses Tailwind — avoid adding custom CSS
3. For new text/labels, add to `src/i18n/translations.js` in all 3 languages
4. Test in both LTR (FR/EN) and RTL (AR) layout

**Backend API change:**
1. Add/update route in `php-backend/index.php`
2. Create/update controller in `php-backend/controllers/`
3. Create/update model in `php-backend/models/`
4. Always use prepared statements — never concatenate user input into SQL
5. Apply `requireAuth()` or `requireAdmin()` as appropriate

**Database change:**
1. Update `php-backend/database.sql` to reflect the final schema
2. Write the `ALTER TABLE` or `CREATE TABLE` statement
3. Document in `php-backend/MIGRATION.md`

### Git Conventions
```bash
# Feature branches
git checkout -b feature/short-description

# Commit messages (English, imperative)
git commit -m "Add product variant support"
git commit -m "Fix cart sync on page reload"
git commit -m "Update shipping rate calculation"
```

---

## Configuration

### Database (`php-backend/config/database.php`)
```php
$host = "sdb-o.hosting.stackcp.net";
$db_name = "agro_store-3139370a4b";
$username = "NacerUser";
```
> Do not commit real credentials. Use `config.example.php` as a template.

### Frontend API URL
Defined per-file in components/pages as a constant:
```js
const API_BASE = "https://vpdeveloper.dz/agro-yousfi/api";
```
For local development, switch to `http://localhost/agro-yousfi/api`.

### Default Admin Account
- Email: `admin@agroyousfi.dz`
- Password: `admin123`
> Change this immediately in production.

---

## Known Issues & Constraints

1. **Admin Products submenu** — Dropdown not expanding in AdminLayout sidebar
2. **Session timeout in Admin** — Sessions expire during extended navigation
3. **PDF invoices** — Arabic RTL rendering needs real-world validation
4. **No automated tests** — Backend has manual test results (83/83 passed); no test suite exists
5. **Hardcoded API URLs** — API base URL is repeated across many files; consider centralizing in `src/lib/api.js`
6. **No CI/CD** — Deployment is manual FTP/SSH

---

## Important Conventions for AI Assistants

### Do
- Use `@/` path aliases in frontend imports (e.g., `@/components/ui/button`)
- Use PDO prepared statements for ALL database queries in PHP
- Add translations for all 3 languages (AR/FR/EN) when adding UI text
- Follow the existing controller/model pattern for new API endpoints
- Test CORS by ensuring `withCredentials: true` is set on all Axios calls
- Respect RTL layout — Arabic uses `dir="rtl"` and may need mirrored UI elements

### Don't
- Don't concatenate user input directly into SQL strings
- Don't hardcode Arabic, French, or English strings in JSX — use `t('key')`
- Don't remove `withCredentials: true` from Axios — session auth breaks without it
- Don't add new npm packages without checking if Radix UI/Shadcn already has the component
- Don't modify `database.sql` without also writing a migration statement
- Don't use `any` type annotations or bypass Zod validation on form inputs
