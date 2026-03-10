<?php
/**
 * Main Router / API Entry Point
 * AgroYousfi E-commerce
 *
 * URL Structure: /api/index.php/{endpoint}
 * Example: /api/index.php/products
 */
/**
 * Main Router / API Entry Point
 * AgroYousfi E-commerce
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Load CORS config
require_once __DIR__ . '/config/cors.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/utils/helpers.php';
require_once __DIR__ . '/data/wilayas.php';

// Controllers
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ProductController.php';
require_once __DIR__ . '/controllers/CategoryController.php';
require_once __DIR__ . '/controllers/OrderController.php';
require_once __DIR__ . '/controllers/ReviewController.php';
require_once __DIR__ . '/controllers/WishlistController.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/controllers/CartController.php';
require_once __DIR__ . '/controllers/AddressController.php';
require_once __DIR__ . '/controllers/BrowsingHistoryController.php';
require_once __DIR__ . '/controllers/UploadController.php';
require_once __DIR__ . '/controllers/GoogleAuthController.php'; // إضافة من الملف الأول

// Get database connection
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    errorResponse('خطأ في الاتصال بقاعدة البيانات', 500);
}

// Parse URL
$requestUri = $_SERVER['REQUEST_URI'];

// Remove query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove /agro-yousfi/api and /index.php from path
$path = preg_replace('#^/agro-yousfi/api(/index\.php)?#', '', $path);
$path = trim($path, '/');

// Split path into segments
$segments = $path ? explode('/', $path) : [];
$method = $_SERVER['REQUEST_METHOD'];

// Initialize controllers
$authController = new AuthController($db);
$productController = new ProductController($db);
$categoryController = new CategoryController($db);
$orderController = new OrderController($db);
$reviewController = new ReviewController($db);
$wishlistController = new WishlistController($db);
$adminController = new AdminController($db);
$cartController = new CartController($db);
$addressController = new AddressController($db);
$historyController = new BrowsingHistoryController($db);
$uploadController = new UploadController();
$googleAuthController = new GoogleAuthController($db); // إضافة من الملف الأول

// Route handling
try {
    // Root endpoint
    if (empty($segments)) {
        jsonResponse([
            'name' => 'AgroYousfi API',
            'version' => '1.0.0',
            'status' => 'running'
        ]);
    }

    $resource = $segments[0] ?? '';
    $id = $segments[1] ?? null;
    $action = $segments[2] ?? null;

    switch ($resource) {
        // ==================== AUTH ====================
        case 'auth':
            switch ($id) {
                case 'register':
                    if ($method === 'POST') $authController->register();
                    break;
                case 'login':
                    if ($method === 'POST') $authController->login();
                    break;
                case 'logout':
                    if ($method === 'POST') $authController->logout();
                    break;
                case 'me':
                    if ($method === 'GET') $authController->me();
                    break;
                case 'profile':
                    if ($method === 'PUT') $authController->updateProfile();
                    break;
                case 'forgot-password':
                    if ($method === 'POST') $authController->forgotPassword();
                    break;
                case 'reset-password':
                    if ($method === 'POST') $authController->resetPassword();
                    break;
                case 'google':
                    // GET /auth/google - Get Google OAuth URL
                    if ($method === 'GET' && $action === null) {
                        $googleAuthController->getAuthUrl();
                        exit();
                    }
                    // GET /auth/google/callback - Handle Google OAuth callback
                    if ($method === 'GET' && $action === 'callback') {
                        $googleAuthController->handleCallback();
                        exit();
                    }
                    break;
            }
            break;

        // ==================== PRODUCTS ====================
        case 'products':
            if ($id === null) {
                if ($method === 'GET') $productController->index();
                if ($method === 'POST') $productController->store();
            } else {
                if ($method === 'GET') $productController->show($id);
                if ($method === 'PUT') $productController->update($id);
                if ($method === 'DELETE') $productController->destroy($id);
            }
            break;

        case 'products-on-sale':
            if ($method === 'GET') $productController->onSale();
            break;

        // ==================== WILAYAS ====================
        case 'wilayas':
            if ($method === 'GET') {
                jsonResponse(getWilayas());
            }
            break;

        // ==================== CATEGORIES ====================
        case 'categories':
            if ($id === null) {
                if ($method === 'GET') $categoryController->index();
                if ($method === 'POST') $categoryController->store();
            } else {
                if ($method === 'GET') $categoryController->show($id);
                if ($method === 'PUT') $categoryController->update($id);
                if ($method === 'DELETE') $categoryController->destroy($id);
            }
            break;

        // ==================== ORDERS ====================
        case 'orders':
            if ($id === null) {
                if ($method === 'GET') $orderController->index();
                if ($method === 'POST') $orderController->store();
            } elseif ($id === 'my') {
                if ($method === 'GET') $orderController->myOrders();
            } else {
                if ($method === 'GET') $orderController->show($id);
                if ($action === 'status' && $method === 'PUT') {
                    $orderController->updateStatus($id);
                }
            }
            break;

        // ==================== REVIEWS ====================
        case 'reviews':
            if ($id === null) {
                if ($method === 'POST') $reviewController->store();
            } else {
                if ($method === 'GET') $reviewController->index($id);
            }
            break;

        // ==================== WISHLIST ====================
        case 'wishlist':
            if ($id === null) {
                if ($method === 'GET') $wishlistController->index();
            } else {
                if ($method === 'POST') $wishlistController->add($id);
                if ($method === 'DELETE') $wishlistController->remove($id);
            }
            break;

        // ==================== ADMIN ====================
        case 'admin':
            switch ($id) {
                case 'dashboard':
                case 'stats': // Added alias for stats (من الملف الأول)
                    if ($method === 'GET') $adminController->dashboard();
                    break;
                case 'users':
                    if ($method === 'GET') $adminController->users();
                    break;
                case 'orders':
                    $subAction = $segments[3] ?? null; // من الملف الأول

                    if ($action === 'unprocessed') {
                        if ($method === 'GET') $adminController->unprocessedOrders();
                    } elseif ($subAction === 'status' && $method === 'PUT') {
                        // /admin/orders/{order_id}/status (من الملف الأول)
                        if ($action) $orderController->updateStatus($action);
                    } else {
                        if ($method === 'GET') $orderController->index();
                    }
                    break;
                case 'products':
                    if ($method === 'GET') $productController->index();
                    if ($method === 'POST') $productController->store();
                    break;
                case 'categories':
                    if ($method === 'GET') $categoryController->index();
                    if ($method === 'POST') $categoryController->store();
                    break;
            }
            break;

        // ==================== CART ====================
        case 'cart':
            if ($id === null) {
                if ($method === 'GET') $cartController->index();
                if ($method === 'DELETE') $cartController->clear();
            } elseif ($id === 'add') {
                // POST /cart/add
                if ($method === 'POST') $cartController->add();
            } elseif ($id === 'update') {
                // PUT /cart/update
                if ($method === 'PUT') $cartController->update();
            } elseif ($id === 'remove') {
                // DELETE /cart/remove/{product_id}
                if ($method === 'DELETE' && $action) $cartController->remove($action);
            } elseif ($id === 'clear') {
                // DELETE /cart/clear
                if ($method === 'DELETE') $cartController->clear();
            }
            break;

        // ==================== ADDRESSES ====================
        case 'addresses':
            if ($id === null) {
                if ($method === 'GET') $addressController->index();
                if ($method === 'POST') $addressController->store();
            } else {
                // Check for /addresses/{id}/default route (من الملف الأول)
                $pathParts = explode('/', trim($path, '/'));
                if (count($pathParts) === 3 && $pathParts[2] === 'default' && $method === 'PUT') {
                    $addressController->setDefault($id);
                } else {
                    if ($method === 'PUT') $addressController->update($id);
                    if ($method === 'DELETE') $addressController->destroy($id);
                }
            }
            break;

        // ==================== BROWSING HISTORY ====================
        // Support both /history and /browsing-history for compatibility
        case 'history':
        case 'browsing-history':
            if ($id === null) {
                if ($method === 'GET') $historyController->index();
                if ($method === 'DELETE') $historyController->clear();
            } else {
                if ($method === 'POST') $historyController->add($id);
            }
            break;

        // ==================== UPLOAD ====================
        case 'upload':
            if ($method === 'POST') $uploadController->uploadImage();
            break;

        case 'uploads':
            if ($id && $method === 'GET') $uploadController->serveFile($id);
            break;

        default:
            errorResponse('نقطة النهاية غير موجودة', 404);
    }

    // If we get here, route wasn't matched
    errorResponse('طريقة غير مسموحة', 405);

} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    errorResponse('حدث خطأ في الخادم', 500);
}