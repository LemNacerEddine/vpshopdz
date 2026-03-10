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

// Load environment variables
require_once __DIR__ . '/config/env.php';

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
require_once __DIR__ . '/controllers/GoogleAuthController.php';
require_once __DIR__ . '/controllers/ShippingController.php';
require_once __DIR__ . '/controllers/CommuneController.php';
require_once __DIR__ . '/controllers/AbandonedCheckoutController.php';
require_once __DIR__ . '/controllers/SettingController.php';
require_once __DIR__ . '/controllers/FacebookAdController.php';
require_once __DIR__ . '/data/communes.php';

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
// Also handle /agro-yousfi/auth (Google OAuth callback comes without /api prefix)
$path = preg_replace('#^/agro-yousfi/(api(/index\.php)?)?#', '', $path);
$path = trim($path, '/');

// Split path into segments and URL-decode each one
$segments = $path ? array_map('urldecode', explode('/', $path)) : [];
$method = $_SERVER['REQUEST_METHOD'];

// Reconstruct order_id segments that contain '/' (e.g., ORD-0001/2026)
// Check for admin/orders/{order_part1}/{order_part2}/status pattern
if (count($segments) >= 5 && $segments[0] === 'admin' && $segments[1] === 'orders'
    && $segments[4] === 'status' && strpos($segments[2], 'ORD-') === 0) {
    // Merge segments[2] and segments[3] back into order_id: ORD-XXXX/YYYY
    $mergedOrderId = $segments[2] . '/' . $segments[3];
    $segments = [$segments[0], $segments[1], $mergedOrderId, 'status'];
}
// Also handle non-admin: orders/{order_part1}/{order_part2}/status
if (count($segments) >= 4 && $segments[0] === 'orders'
    && $segments[3] === 'status' && strpos($segments[1], 'ORD-') === 0) {
    $mergedOrderId = $segments[1] . '/' . $segments[2];
    $segments = [$segments[0], $mergedOrderId, 'status'];
}
// Handle GET orders/{order_part1}/{order_part2} (show order)
if (count($segments) >= 3 && $segments[0] === 'orders'
    && strpos($segments[1], 'ORD-') === 0 && is_numeric($segments[2])
    && !isset($segments[3])) {
    $mergedOrderId = $segments[1] . '/' . $segments[2];
    $segments = [$segments[0], $mergedOrderId];
}

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
$googleAuthController = new GoogleAuthController($db);
$shippingController = new ShippingController($db);
$communeController = new CommuneController();
$abandonedCheckoutController = new AbandonedCheckoutController($db);
$settingController = new SettingController($db);
$facebookAdController = new FacebookAdController($db);

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
                case 'stats': // Added alias for stats
                    if ($method === 'GET') $adminController->dashboard();
                    break;
                case 'users':
                    if ($method === 'GET') $adminController->users();
                    break;
                case 'orders':
                    $subAction = $segments[3] ?? null;
                    
                    if ($action === 'unprocessed') {
                        if ($method === 'GET') $adminController->unprocessedOrders();
                    } elseif ($subAction === 'status' && $method === 'PUT') {
                        // /admin/orders/{order_id}/status
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
                case 'abandoned-checkouts':
                    $subAction = $segments[3] ?? null;
                    if ($action === 'stats' && $method === 'GET') {
                        $abandonedCheckoutController->stats();
                    } elseif ($action === null) {
                        if ($method === 'GET') $abandonedCheckoutController->index();
                    } elseif ($action) {
                        if ($subAction === 'notified' && $method === 'PUT') {
                            $abandonedCheckoutController->markNotified($action);
                        } elseif ($subAction === 'recovered' && $method === 'PUT') {
                            $abandonedCheckoutController->markRecovered($action);
                        } elseif ($subAction === 'retry' && $method === 'PUT') {
                            $abandonedCheckoutController->retrySend($action);
                        } elseif ($subAction === 'skip' && $method === 'PUT') {
                            $abandonedCheckoutController->skipSend($action);
                        } elseif ($method === 'GET') {
                            $abandonedCheckoutController->show($action);
                        } elseif ($method === 'DELETE') {
                            $abandonedCheckoutController->destroy($action);
                        }
                    }
                    break;
                case 'facebook-ads':
                    $subAction = $segments[3] ?? null;
                    if ($action === 'validate' && $method === 'POST') {
                        $facebookAdController->validateCredentials();
                    } elseif ($action === 'preview' && $subAction && $method === 'GET') {
                        $facebookAdController->preview($subAction);
                    } elseif ($action === 'metrics' && $subAction === 'refresh' && $method === 'POST') {
                        $facebookAdController->refreshAllMetrics();
                    } elseif ($action && $subAction === 'pause' && $method === 'PUT') {
                        $facebookAdController->pause($action);
                    } elseif ($action && $subAction === 'resume' && $method === 'PUT') {
                        $facebookAdController->resume($action);
                    } elseif ($action && $subAction === 'metrics' && $method === 'POST') {
                        $facebookAdController->refreshMetrics($action);
                    } elseif ($action === null) {
                        if ($method === 'GET') $facebookAdController->index();
                        if ($method === 'POST') $facebookAdController->store();
                    } elseif ($action) {
                        if ($method === 'GET') $facebookAdController->show($action);
                        if ($method === 'DELETE') $facebookAdController->destroy($action);
                    }
                    break;
                case 'settings':
                    $subAction = $segments[3] ?? null;
                    if ($action === 'store' && $method === 'GET') {
                        $settingController->getStoreSettings();
                    } elseif ($action === 'whatsapp' && $subAction === 'test' && $method === 'POST') {
                        $settingController->testWhatsApp();
                    } elseif ($action === 'whatsapp' && $method === 'GET') {
                        $settingController->getWhatsApp();
                    } elseif ($action === null) {
                        if ($method === 'GET') $settingController->index();
                        if ($method === 'PUT') $settingController->update();
                    }
                    break;
            }
            break;

        // ==================== COMMUNES ====================
        case 'communes':
            if ($method === 'GET') $communeController->index();
            break;

        // ==================== SHIPPING ====================
        case 'shipping':
            if ($id === 'options' && $method === 'GET') {
                $shippingController->getOptions();
            } elseif ($id === 'companies') {
                if ($action === null) {
                    if ($method === 'GET') $shippingController->getCompanies();
                    if ($method === 'POST') $shippingController->createCompany();
                } else {
                    $subAction = $segments[3] ?? null;
                    if ($subAction === 'rates') {
                        if ($method === 'GET') $shippingController->getCompanyRates($action);
                        if ($method === 'POST') $shippingController->upsertCompanyRates($action);
                    } else {
                        if ($method === 'GET') $shippingController->getCompany($action);
                        if ($method === 'PUT') $shippingController->updateCompany($action);
                        if ($method === 'DELETE') $shippingController->deleteCompany($action);
                    }
                }
            } elseif ($id === 'rules') {
                if ($action === null) {
                    if ($method === 'GET') $shippingController->getRules();
                    if ($method === 'POST') $shippingController->createRule();
                } else {
                    if ($method === 'PUT') $shippingController->updateRule($action);
                    if ($method === 'DELETE') $shippingController->deleteRule($action);
                }
            } elseif ($id === 'tracking' && $action) {
                if ($method === 'PUT') $shippingController->updateTracking($action);
            }
            break;

        // ==================== PUBLIC SETTINGS ====================
        case 'settings':
            if ($id === 'public' && $method === 'GET') {
                $settingController->getPublic();
            }
            break;

        // ==================== ABANDONED CHECKOUTS ====================
        case 'abandoned-checkouts':
            if ($id === null) {
                if ($method === 'POST') $abandonedCheckoutController->save();
                if ($method === 'DELETE') $abandonedCheckoutController->resolve();
            }
            break;

        // ==================== RECOVERY LINK (PUBLIC) ====================
        case 'recover':
            if ($id && $method === 'GET') {
                $abandonedCheckoutController->recover($id);
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
                // Check for /addresses/{id}/default route
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
