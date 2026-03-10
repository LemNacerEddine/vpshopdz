<?php
/**
 * Shipping Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/ShippingCompany.php';
require_once __DIR__ . '/../models/ShippingRate.php';
require_once __DIR__ . '/../models/ShippingRule.php';
require_once __DIR__ . '/../models/OrderShipping.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class ShippingController {
    private $db;
    private $company;
    private $rate;
    private $rule;
    private $orderShipping;
    private $cart;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->company = new ShippingCompany($db);
        $this->rate = new ShippingRate($db);
        $this->rule = new ShippingRule($db);
        $this->orderShipping = new OrderShipping($db);
        $this->cart = new Cart($db);
        $this->auth = new Auth();
    }

    // ==================== SHIPPING OPTIONS (PUBLIC) ====================

    // GET /shipping/options?wilaya=...&commune=...&shipping_type=home
    public function getOptions() {
        $wilaya = $_GET['wilaya'] ?? null;
        $commune = $_GET['commune'] ?? null;
        $shippingType = $_GET['shipping_type'] ?? 'home';

        if (!$wilaya) {
            errorResponse('يرجى تحديد الولاية', 400);
        }

        // Get cart items for the current user/browser
        $user = $this->auth->getCurrentUser();
        $browserId = $_COOKIE['browser_id'] ?? $_GET['browser_id'] ?? null;
        $cartData = $this->cart->getCart(
            $user ? $user['user_id'] : null,
            $browserId
        );

        $result = $this->calculateShippingOptions($cartData['items'], $wilaya, $commune, $shippingType);
        jsonResponse($result);
    }

    // Core shipping calculation algorithm
    public function calculateShippingOptions($cartItems, $wilaya, $commune = null, $shippingType = 'home') {
        // Step 1: Check free shipping rules
        $freeShipping = $this->checkFreeShippingRules($cartItems);
        if ($freeShipping['is_free']) {
            return [
                'free_shipping' => true,
                'reason' => $freeShipping['reason'],
                'options' => [],
                'shipping_cost' => 0
            ];
        }

        // Step 2: Classify products
        $standardItems = [];
        $totalFixedCost = 0;
        $totalActualWeight = 0;
        $totalVolumetricWeight = 0;

        foreach ($cartItems as $item) {
            $product = $item['product'];
            $shippingTypeProduct = $product['shipping_type'] ?? 'standard';

            if ($shippingTypeProduct === 'free') {
                // Free shipping product - still count weight
                $weight = ($product['weight'] ?? 0) * $item['quantity'];
                $totalActualWeight += $weight;
                continue;
            }

            if ($shippingTypeProduct === 'fixed_price') {
                // Fixed shipping price product
                $fixedPrice = ($product['fixed_shipping_price'] ?? 0) * $item['quantity'];
                $totalFixedCost += $fixedPrice;
                continue;
            }

            // Standard product
            $standardItems[] = $item;
            $weight = ($product['weight'] ?? 0.5) * $item['quantity'];
            $totalActualWeight += $weight;

            // Volumetric weight calculation
            $l = $product['length'] ?? 0;
            $w = $product['width'] ?? 0;
            $h = $product['height'] ?? 0;
            if ($l > 0 && $w > 0 && $h > 0) {
                // Will be divided by company's volumetric_divisor later
                $totalVolumetricWeight += ($l * $w * $h * $item['quantity']);
            }
        }

        // Step 3: Get available rates for this wilaya/commune
        $availableOptions = $this->rate->getAvailableOptions($wilaya, $commune, $shippingType);

        $options = [];
        foreach ($availableOptions as $option) {
            $company = $option['company'];
            $rate = $option['rate'];

            // Calculate volumetric weight with company's divisor
            $divisor = $company['volumetric_divisor'] ?? 5000;
            $volWeight = $divisor > 0 ? $totalVolumetricWeight / $divisor : 0;

            // Billable weight = MAX(actual, volumetric)
            $billableWeight = max($totalActualWeight, $volWeight);
            // Minimum 0.5kg
            if ($billableWeight < 0.5 && count($standardItems) > 0) {
                $billableWeight = 0.5;
            }

            // Calculate cost
            $baseCost = (float)$rate['base_price'];
            $includedWeight = (float)($company['included_weight'] ?? 5);
            $additionalPerKg = (float)($company['additional_price_per_kg'] ?? 0);

            if ($billableWeight <= $includedWeight) {
                $shippingCost = $baseCost;
            } else {
                $excessWeight = $billableWeight - $includedWeight;
                $shippingCost = $baseCost + ($excessWeight * $additionalPerKg);
            }

            // Add fixed costs
            $finalCost = $totalFixedCost + $shippingCost;

            $options[] = [
                'company_id' => $company['company_id'],
                'company_name_ar' => $company['name_ar'],
                'company_name_fr' => $company['name_fr'],
                'company_name_en' => $company['name_en'],
                'company_logo' => $company['logo'],
                'shipping_type' => $shippingType,
                'base_price' => $baseCost,
                'shipping_cost' => round($finalCost, 2),
                'total_weight' => round($totalActualWeight, 2),
                'billable_weight' => round($billableWeight, 2),
                'included_weight' => $includedWeight,
                'min_delivery_days' => (int)$rate['min_delivery_days'],
                'max_delivery_days' => (int)$rate['max_delivery_days'],
                'rate_id' => $rate['rate_id']
            ];
        }

        // Sort by cost ascending
        usort($options, function($a, $b) {
            return $a['shipping_cost'] <=> $b['shipping_cost'];
        });

        return [
            'free_shipping' => false,
            'reason' => null,
            'options' => $options,
            'shipping_cost' => !empty($options) ? $options[0]['shipping_cost'] : 0
        ];
    }

    // Check free shipping rules
    private function checkFreeShippingRules($cartItems) {
        $activeRules = $this->rule->getActiveRules();

        // Calculate cart totals
        $cartTotal = 0;
        $cartItemCount = 0;
        $categoryIds = [];
        $productIds = [];

        foreach ($cartItems as $item) {
            $product = $item['product'];
            $cartTotal += $product['price'] * $item['quantity'];
            $cartItemCount += $item['quantity'];
            if (!empty($product['category_id'])) {
                $categoryIds[] = $product['category_id'];
            }
            $productIds[] = $item['product_id'];
        }

        foreach ($activeRules as $rule) {
            switch ($rule['rule_type']) {
                case 'min_cart_total':
                    if ($cartTotal >= (float)$rule['condition_value']) {
                        return [
                            'is_free' => true,
                            'reason' => $rule['rule_name'],
                            'rule_id' => $rule['rule_id']
                        ];
                    }
                    break;

                case 'min_cart_items':
                    if ($cartItemCount >= (int)$rule['condition_value']) {
                        return [
                            'is_free' => true,
                            'reason' => $rule['rule_name'],
                            'rule_id' => $rule['rule_id']
                        ];
                    }
                    break;

                case 'free_for_category':
                    if (in_array($rule['condition_value'], $categoryIds)) {
                        return [
                            'is_free' => true,
                            'reason' => $rule['rule_name'],
                            'rule_id' => $rule['rule_id']
                        ];
                    }
                    break;

                case 'free_for_product':
                    if (in_array($rule['condition_value'], $productIds)) {
                        return [
                            'is_free' => true,
                            'reason' => $rule['rule_name'],
                            'rule_id' => $rule['rule_id']
                        ];
                    }
                    break;
            }
        }

        return ['is_free' => false, 'reason' => null];
    }

    // ==================== COMPANIES (ADMIN) ====================

    public function getCompanies() {
        $this->auth->requireAdmin();
        $companies = $this->company->getAll();
        jsonResponse($companies);
    }

    public function getCompany($companyId) {
        $this->auth->requireAdmin();
        $company = $this->company->findById($companyId);
        if (!$company) {
            errorResponse('شركة الشحن غير موجودة', 404);
        }
        jsonResponse($company);
    }

    public function createCompany() {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        if (empty($data['name_ar'])) {
            errorResponse('اسم الشركة مطلوب', 400);
        }

        $company = $this->company->create($data);
        jsonResponse($company, 201);
    }

    public function updateCompany($companyId) {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        $company = $this->company->update($companyId, $data);
        if (!$company) {
            errorResponse('شركة الشحن غير موجودة', 404);
        }
        jsonResponse($company);
    }

    public function deleteCompany($companyId) {
        $this->auth->requireAdmin();
        $this->company->delete($companyId);
        jsonResponse(['message' => 'تم حذف شركة الشحن']);
    }

    // ==================== RATES (ADMIN) ====================

    public function getCompanyRates($companyId) {
        $this->auth->requireAdmin();
        $rates = $this->rate->getByCompany($companyId);
        jsonResponse($rates);
    }

    public function upsertCompanyRates($companyId) {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        if (empty($data['rates']) || !is_array($data['rates'])) {
            errorResponse('يرجى تقديم أسعار الشحن', 400);
        }

        $results = $this->rate->bulkUpsert($companyId, $data['rates']);
        jsonResponse(['message' => 'تم تحديث الأسعار', 'count' => count($results)]);
    }

    // ==================== RULES (ADMIN) ====================

    public function getRules() {
        $this->auth->requireAdmin();
        $rules = $this->rule->getAll();
        jsonResponse($rules);
    }

    public function createRule() {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        if (empty($data['rule_name']) || empty($data['rule_type']) || !isset($data['condition_value'])) {
            errorResponse('بيانات القاعدة ناقصة', 400);
        }

        $rule = $this->rule->create($data);
        jsonResponse($rule, 201);
    }

    public function updateRule($ruleId) {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        $rule = $this->rule->update($ruleId, $data);
        if (!$rule) {
            errorResponse('القاعدة غير موجودة', 404);
        }
        jsonResponse($rule);
    }

    public function deleteRule($ruleId) {
        $this->auth->requireAdmin();
        $this->rule->delete($ruleId);
        jsonResponse(['message' => 'تم حذف القاعدة']);
    }

    // ==================== ORDER SHIPPING ====================

    public function updateTracking($orderId) {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        if (empty($data['tracking_number'])) {
            errorResponse('رقم التتبع مطلوب', 400);
        }

        $shipping = $this->orderShipping->updateTracking($orderId, $data['tracking_number']);
        if (!$shipping) {
            errorResponse('لم يتم العثور على بيانات الشحن', 404);
        }
        jsonResponse($shipping);
    }
}
