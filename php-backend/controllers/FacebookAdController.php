<?php
/**
 * Facebook Ad Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/FacebookAd.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../services/FacebookApiService.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class FacebookAdController {
    private $db;
    private $model;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new FacebookAd($db);
        $this->auth = new Auth();
    }

    /**
     * GET /admin/facebook-ads
     */
    public function index() {
        $this->auth->requireAdmin();
        $filters = [];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['product_id'])) $filters['product_id'] = $_GET['product_id'];

        $ads = $this->model->getAll($filters);
        jsonResponse($ads);
    }

    /**
     * GET /admin/facebook-ads/{id}
     */
    public function show($id) {
        $this->auth->requireAdmin();
        $ad = $this->model->findById($id);
        if (!$ad) errorResponse('الإعلان غير موجود', 404);
        jsonResponse($ad);
    }

    /**
     * GET /admin/facebook-ads/preview/{productId}
     * Generate preview of ad text for a product
     */
    public function preview($productId) {
        $this->auth->requireAdmin();

        $productModel = new Product($this->db);
        $product = $productModel->findById($productId);
        if (!$product) errorResponse('المنتج غير موجود', 404);

        $settings = new Setting($this->db);
        $storeUrl = $settings->get('store_url', '');
        $landingUrl = $storeUrl ? rtrim($storeUrl, '/') . '/p/' . $productId : '';

        $adTexts = $this->generateAdText($product);

        jsonResponse([
            'product' => [
                'product_id' => $product['product_id'],
                'name_ar' => $product['name_ar'],
                'name_fr' => $product['name_fr'],
                'name_en' => $product['name_en'],
                'price' => $product['price'],
                'discount_percent' => $product['discount_percent'],
                'image' => $product['images'][0] ?? null,
            ],
            'ad_text' => $adTexts['primary_text'],
            'ad_headline' => $adTexts['headline'],
            'landing_url' => $landingUrl,
        ]);
    }

    /**
     * POST /admin/facebook-ads
     * Create the full ad chain: Campaign → Ad Set → Creative → Ad
     */
    public function store() {
        $this->auth->requireAdmin();
        $data = getJsonInput();

        // Validate input
        if (empty($data['product_id'])) errorResponse('معرف المنتج مطلوب', 400);
        if (empty($data['daily_budget'])) errorResponse('الميزانية اليومية مطلوبة', 400);

        $productModel = new Product($this->db);
        $product = $productModel->findById($data['product_id']);
        if (!$product) errorResponse('المنتج غير موجود', 404);

        // Load FB credentials
        $settings = new Setting($this->db);
        $accessToken = $settings->get('fb_access_token', '');
        $adAccountId = $settings->get('fb_ad_account_id', '');
        $pageId = $settings->get('fb_page_id', '');
        $storeUrl = $settings->get('store_url', '');

        if (!$accessToken || !$adAccountId || !$pageId) {
            errorResponse('إعدادات Facebook Marketing API غير مكتملة', 400);
        }

        // Prepare ad data
        $durationDays = (int)($data['duration_days'] ?? 7);
        $dailyBudgetDZD = (float)$data['daily_budget'];
        $dailyBudgetCents = (int)($dailyBudgetDZD * 100);
        $targetCountry = $data['target_country'] ?? 'DZ';
        $targetAgeMin = (int)($data['target_age_min'] ?? 18);
        $targetAgeMax = (int)($data['target_age_max'] ?? 65);

        $landingUrl = $storeUrl ? rtrim($storeUrl, '/') . '/p/' . $product['product_id'] : '';
        $adTexts = $this->generateAdText($product);
        $adText = $data['ad_text'] ?? $adTexts['primary_text'];
        $adHeadline = $data['ad_headline'] ?? $adTexts['headline'];

        $startTime = date('c');
        $endTime = date('c', strtotime("+{$durationDays} days"));

        // Create local record first
        $localAd = $this->model->create([
            'product_id' => $product['product_id'],
            'status' => 'pending',
            'daily_budget_cents' => $dailyBudgetCents,
            'duration_days' => $durationDays,
            'target_country' => $targetCountry,
            'target_age_min' => $targetAgeMin,
            'target_age_max' => $targetAgeMax,
            'ad_text' => $adText,
            'ad_headline' => $adHeadline,
            'landing_url' => $landingUrl,
            'starts_at' => date('Y-m-d H:i:s'),
            'ends_at' => date('Y-m-d H:i:s', strtotime("+{$durationDays} days")),
        ]);

        $adIdLocal = $localAd['ad_id_local'];
        $fb = new FacebookApiService($accessToken, $adAccountId, $pageId);
        $productName = $product['name_ar'] ?: $product['name_fr'] ?: $product['name_en'];

        // Step 1: Upload image
        $imageUrl = $product['images'][0] ?? null;
        $imageHash = null;
        if ($imageUrl) {
            $imgResult = $fb->uploadImage($imageUrl);
            if (!$imgResult['success']) {
                $this->model->update($adIdLocal, ['status' => 'error', 'error_message' => 'Image upload: ' . $imgResult['error']]);
                errorResponse('فشل رفع الصورة: ' . $imgResult['error'], 400);
            }
            $imageHash = $imgResult['image_hash'];
            $this->model->update($adIdLocal, ['image_hash' => $imageHash]);
        }

        // Step 2: Create Campaign (PAUSED)
        $campaignName = "AgroYousfi - {$productName}";
        $campResult = $fb->createCampaign($campaignName);
        if (!$campResult['success']) {
            $this->model->update($adIdLocal, ['status' => 'error', 'error_message' => 'Campaign: ' . $campResult['error']]);
            errorResponse('فشل إنشاء الحملة: ' . $campResult['error'], 400);
        }
        $campaignId = $campResult['campaign_id'];
        $this->model->update($adIdLocal, ['campaign_id' => $campaignId, 'campaign_name' => $campaignName]);

        // Step 3: Create Ad Set
        $targeting = [
            'geo_locations' => ['countries' => [$targetCountry]],
            'age_min' => $targetAgeMin,
            'age_max' => $targetAgeMax,
        ];
        $adsetName = "AdSet - {$productName}";
        $adsetResult = $fb->createAdSet($campaignId, $adsetName, $dailyBudgetCents, $targeting, $startTime, $endTime);
        if (!$adsetResult['success']) {
            $this->model->update($adIdLocal, ['status' => 'error', 'error_message' => 'AdSet: ' . $adsetResult['error']]);
            errorResponse('فشل إنشاء مجموعة الإعلانات: ' . $adsetResult['error'], 400);
        }
        $adsetId = $adsetResult['adset_id'];
        $this->model->update($adIdLocal, ['adset_id' => $adsetId]);

        // Step 4: Create Creative
        $creativeName = "Creative - {$productName}";
        $creativeResult = $fb->createCreative($creativeName, $imageHash, $landingUrl, $adText, $adHeadline);
        if (!$creativeResult['success']) {
            $this->model->update($adIdLocal, ['status' => 'error', 'error_message' => 'Creative: ' . $creativeResult['error']]);
            errorResponse('فشل إنشاء المحتوى الإبداعي: ' . $creativeResult['error'], 400);
        }
        $creativeId = $creativeResult['creative_id'];
        $this->model->update($adIdLocal, ['creative_id' => $creativeId]);

        // Step 5: Create Ad
        $adName = "Ad - {$productName}";
        $adResult = $fb->createAd($adName, $adsetId, $creativeId);
        if (!$adResult['success']) {
            $this->model->update($adIdLocal, ['status' => 'error', 'error_message' => 'Ad: ' . $adResult['error']]);
            errorResponse('فشل إنشاء الإعلان: ' . $adResult['error'], 400);
        }
        $fbAdId = $adResult['ad_id'];
        $this->model->update($adIdLocal, ['fb_ad_id' => $fbAdId]);

        // Step 6: Activate Campaign
        $activateResult = $fb->activateCampaign($campaignId);
        if (!$activateResult['success']) {
            $this->model->update($adIdLocal, ['status' => 'error', 'error_message' => 'Activate: ' . $activateResult['error']]);
            errorResponse('فشل تفعيل الحملة: ' . $activateResult['error'], 400);
        }

        // Success
        $finalAd = $this->model->update($adIdLocal, ['status' => 'active']);
        jsonResponse($finalAd, 201);
    }

    /**
     * PUT /admin/facebook-ads/{id}/pause
     */
    public function pause($id) {
        $this->auth->requireAdmin();
        $ad = $this->model->findById($id);
        if (!$ad) errorResponse('الإعلان غير موجود', 404);
        if (!$ad['fb_ad_id']) errorResponse('الإعلان غير مرتبط بفيسبوك', 400);

        $fb = $this->getFacebookService();
        $result = $fb->pauseAd($ad['fb_ad_id']);

        if (!$result['success']) {
            errorResponse('فشل إيقاف الإعلان: ' . $result['error'], 400);
        }

        $updated = $this->model->update($id, ['status' => 'paused']);
        jsonResponse($updated);
    }

    /**
     * PUT /admin/facebook-ads/{id}/resume
     */
    public function resume($id) {
        $this->auth->requireAdmin();
        $ad = $this->model->findById($id);
        if (!$ad) errorResponse('الإعلان غير موجود', 404);
        if (!$ad['fb_ad_id']) errorResponse('الإعلان غير مرتبط بفيسبوك', 400);

        $fb = $this->getFacebookService();
        $result = $fb->resumeAd($ad['fb_ad_id']);

        if (!$result['success']) {
            errorResponse('فشل استئناف الإعلان: ' . $result['error'], 400);
        }

        $updated = $this->model->update($id, ['status' => 'active']);
        jsonResponse($updated);
    }

    /**
     * DELETE /admin/facebook-ads/{id}
     */
    public function destroy($id) {
        $this->auth->requireAdmin();
        $ad = $this->model->findById($id);
        if (!$ad) errorResponse('الإعلان غير موجود', 404);

        // Delete from Facebook if connected
        if ($ad['fb_ad_id']) {
            $fb = $this->getFacebookService();
            $fb->deleteAd($ad['fb_ad_id']);
        }

        $this->model->delete($id);
        jsonResponse(['success' => true]);
    }

    /**
     * POST /admin/facebook-ads/{id}/metrics
     */
    public function refreshMetrics($id) {
        $this->auth->requireAdmin();
        $ad = $this->model->findById($id);
        if (!$ad) errorResponse('الإعلان غير موجود', 404);
        if (!$ad['fb_ad_id']) errorResponse('الإعلان غير مرتبط بفيسبوك', 400);

        $fb = $this->getFacebookService();
        $result = $fb->getAdInsights($ad['fb_ad_id']);

        if (!$result['success']) {
            errorResponse('فشل جلب الإحصائيات: ' . $result['error'], 400);
        }

        $updated = $this->model->updateMetrics($id, $result['metrics']);
        jsonResponse($updated);
    }

    /**
     * POST /admin/facebook-ads/metrics/refresh
     * Refresh all active ads metrics
     */
    public function refreshAllMetrics() {
        $this->auth->requireAdmin();
        $ads = $this->model->getAll(['status' => 'active']);
        $fb = $this->getFacebookService();
        $updated = 0;

        foreach ($ads as $ad) {
            if (!$ad['fb_ad_id']) continue;
            $result = $fb->getAdInsights($ad['fb_ad_id']);
            if ($result['success']) {
                $this->model->updateMetrics($ad['ad_id_local'], $result['metrics']);
                $updated++;
            }
        }

        jsonResponse(['success' => true, 'updated' => $updated]);
    }

    /**
     * POST /admin/facebook-ads/validate
     */
    public function validateCredentials() {
        $this->auth->requireAdmin();
        $fb = $this->getFacebookService();
        $result = $fb->validateCredentials();

        if ($result['success']) {
            jsonResponse(['success' => true, 'account' => $result['account']]);
        } else {
            errorResponse($result['error'] ?? 'فشل التحقق من بيانات الاعتماد', 400);
        }
    }

    /**
     * Helper: get FacebookApiService instance from settings
     */
    private function getFacebookService() {
        $settings = new Setting($this->db);
        $accessToken = $settings->get('fb_access_token', '');
        $adAccountId = $settings->get('fb_ad_account_id', '');
        $pageId = $settings->get('fb_page_id', '');

        if (!$accessToken || !$adAccountId) {
            errorResponse('إعدادات Facebook Marketing API غير مكتملة', 400);
        }

        return new FacebookApiService($accessToken, $adAccountId, $pageId);
    }

    /**
     * Auto-generate ad text from product data
     */
    private function generateAdText($product) {
        $name = $product['name_ar'] ?: ($product['name_fr'] ?: $product['name_en']);
        $price = number_format((float)$product['price'], 0);

        $hasDiscount = isDiscountActive(
            $product['discount_percent'],
            $product['discount_start'] ?? null,
            $product['discount_end'] ?? null
        );

        if ($hasDiscount && $product['discount_percent'] > 0) {
            $discountedPrice = number_format(calculateDiscountedPrice($product['price'], $product['discount_percent']), 0);
            $primaryText = "{$name} - خصم {$product['discount_percent']}%! السعر الآن {$discountedPrice} د.ج بدلاً من {$price} د.ج. اطلب الآن من AgroYousfi!";
            $headline = "{$name} - خصم {$product['discount_percent']}%";
        } else {
            $primaryText = "{$name} - {$price} د.ج. منتجات زراعية عالية الجودة من AgroYousfi. اطلب الآن!";
            $headline = "{$name} - {$price} د.ج";
        }

        return [
            'primary_text' => $primaryText,
            'headline' => $headline,
        ];
    }
}
