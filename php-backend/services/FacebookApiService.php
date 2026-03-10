<?php
/**
 * Facebook Marketing API Service
 * AgroYousfi E-commerce
 *
 * Wraps all Facebook Graph API calls for ad management.
 */

class FacebookApiService {
    private $accessToken;
    private $adAccountId;
    private $pageId;
    private $apiVersion = 'v21.0';
    private $baseUrl = 'https://graph.facebook.com';

    public function __construct($accessToken, $adAccountId, $pageId) {
        $this->accessToken = $accessToken;
        $this->adAccountId = $adAccountId;
        $this->pageId = $pageId;
    }

    /**
     * Core HTTP helper for Graph API calls
     */
    private function apiRequest($method, $endpoint, $params = [], $isMultipart = false) {
        $url = "{$this->baseUrl}/{$this->apiVersion}/{$endpoint}";

        $ch = curl_init();

        $headers = [];
        if (!$isMultipart) {
            $headers[] = 'Content-Type: application/json';
        }

        if ($method === 'GET') {
            $params['access_token'] = $this->accessToken;
            $url .= '?' . http_build_query($params);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        } else {
            if ($isMultipart) {
                $params['access_token'] = $this->accessToken;
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            } else {
                $params['access_token'] = $this->accessToken;
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        if ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => 'Connection error: ' . $error];
        }

        $data = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'data' => $data];
        }

        $errorMsg = $data['error']['message'] ?? ('Facebook API error (HTTP ' . $httpCode . ')');
        return ['success' => false, 'error' => $errorMsg, 'data' => $data];
    }

    /**
     * Validate credentials by fetching ad account info
     */
    public function validateCredentials() {
        $result = $this->apiRequest('GET', "act_{$this->adAccountId}", [
            'fields' => 'name,account_status,currency,business_name',
        ]);

        if ($result['success']) {
            return [
                'success' => true,
                'account' => $result['data'],
            ];
        }

        return $result;
    }

    /**
     * Upload an image from URL to the ad account
     */
    public function uploadImage($imageUrl) {
        // Download image to temp file
        $tmpFile = tempnam(sys_get_temp_dir(), 'fbimg_');
        $imageData = @file_get_contents($imageUrl);

        if ($imageData === false) {
            return ['success' => false, 'error' => 'Failed to download image from URL'];
        }

        file_put_contents($tmpFile, $imageData);

        $result = $this->apiRequest('POST', "act_{$this->adAccountId}/adimages", [
            'filename' => new CURLFile($tmpFile, 'image/jpeg', 'product.jpg'),
        ], true);

        @unlink($tmpFile);

        if ($result['success'] && isset($result['data']['images'])) {
            $images = $result['data']['images'];
            $firstImage = reset($images);
            return [
                'success' => true,
                'image_hash' => $firstImage['hash'] ?? null,
            ];
        }

        return ['success' => false, 'error' => $result['error'] ?? 'Failed to upload image'];
    }

    /**
     * Create a campaign (in PAUSED status)
     */
    public function createCampaign($name, $objective = 'OUTCOME_TRAFFIC') {
        $result = $this->apiRequest('POST', "act_{$this->adAccountId}/campaigns", [
            'name' => $name,
            'objective' => $objective,
            'status' => 'PAUSED',
            'special_ad_categories' => [],
        ]);

        if ($result['success'] && isset($result['data']['id'])) {
            return ['success' => true, 'campaign_id' => $result['data']['id']];
        }

        return ['success' => false, 'error' => $result['error'] ?? 'Failed to create campaign'];
    }

    /**
     * Create an ad set with targeting and budget
     */
    public function createAdSet($campaignId, $name, $dailyBudgetCents, $targeting, $startTime, $endTime) {
        $params = [
            'name' => $name,
            'campaign_id' => $campaignId,
            'daily_budget' => $dailyBudgetCents,
            'billing_event' => 'IMPRESSIONS',
            'optimization_goal' => 'LINK_CLICKS',
            'bid_strategy' => 'LOWEST_COST_WITHOUT_CAP',
            'targeting' => $targeting,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'PAUSED',
        ];

        $result = $this->apiRequest('POST', "act_{$this->adAccountId}/adsets", $params);

        if ($result['success'] && isset($result['data']['id'])) {
            return ['success' => true, 'adset_id' => $result['data']['id']];
        }

        return ['success' => false, 'error' => $result['error'] ?? 'Failed to create ad set'];
    }

    /**
     * Create an ad creative
     */
    public function createCreative($name, $imageHash, $linkUrl, $message, $headline, $description = '') {
        $params = [
            'name' => $name,
            'object_story_spec' => [
                'page_id' => $this->pageId,
                'link_data' => [
                    'image_hash' => $imageHash,
                    'link' => $linkUrl,
                    'message' => $message,
                    'name' => $headline,
                    'description' => $description,
                    'call_to_action' => [
                        'type' => 'SHOP_NOW',
                        'value' => ['link' => $linkUrl],
                    ],
                ],
            ],
        ];

        $result = $this->apiRequest('POST', "act_{$this->adAccountId}/adcreatives", $params);

        if ($result['success'] && isset($result['data']['id'])) {
            return ['success' => true, 'creative_id' => $result['data']['id']];
        }

        return ['success' => false, 'error' => $result['error'] ?? 'Failed to create creative'];
    }

    /**
     * Create an ad linking ad set + creative
     */
    public function createAd($name, $adsetId, $creativeId) {
        $params = [
            'name' => $name,
            'adset_id' => $adsetId,
            'creative' => ['creative_id' => $creativeId],
            'status' => 'ACTIVE',
        ];

        $result = $this->apiRequest('POST', "act_{$this->adAccountId}/ads", $params);

        if ($result['success'] && isset($result['data']['id'])) {
            return ['success' => true, 'ad_id' => $result['data']['id']];
        }

        return ['success' => false, 'error' => $result['error'] ?? 'Failed to create ad'];
    }

    /**
     * Activate a campaign (change from PAUSED to ACTIVE)
     */
    public function activateCampaign($campaignId) {
        $result = $this->apiRequest('POST', $campaignId, [
            'status' => 'ACTIVE',
        ]);

        return $result['success']
            ? ['success' => true]
            : ['success' => false, 'error' => $result['error'] ?? 'Failed to activate campaign'];
    }

    /**
     * Get ad insights (metrics)
     */
    public function getAdInsights($adId) {
        $result = $this->apiRequest('GET', "{$adId}/insights", [
            'fields' => 'impressions,clicks,spend,reach',
        ]);

        if ($result['success'] && isset($result['data']['data'][0])) {
            $metrics = $result['data']['data'][0];
            return [
                'success' => true,
                'metrics' => [
                    'impressions' => (int)($metrics['impressions'] ?? 0),
                    'clicks' => (int)($metrics['clicks'] ?? 0),
                    'spend_cents' => (int)(($metrics['spend'] ?? 0) * 100),
                    'reach' => (int)($metrics['reach'] ?? 0),
                ],
            ];
        }

        // No data yet is not an error
        if ($result['success']) {
            return [
                'success' => true,
                'metrics' => ['impressions' => 0, 'clicks' => 0, 'spend_cents' => 0, 'reach' => 0],
            ];
        }

        return ['success' => false, 'error' => $result['error'] ?? 'Failed to get insights'];
    }

    /**
     * Pause an ad
     */
    public function pauseAd($adId) {
        $result = $this->apiRequest('POST', $adId, ['status' => 'PAUSED']);
        return $result['success']
            ? ['success' => true]
            : ['success' => false, 'error' => $result['error'] ?? 'Failed to pause ad'];
    }

    /**
     * Delete/archive an ad
     */
    public function deleteAd($adId) {
        $result = $this->apiRequest('POST', $adId, ['status' => 'DELETED']);
        return $result['success']
            ? ['success' => true]
            : ['success' => false, 'error' => $result['error'] ?? 'Failed to delete ad'];
    }

    /**
     * Resume a paused ad
     */
    public function resumeAd($adId) {
        $result = $this->apiRequest('POST', $adId, ['status' => 'ACTIVE']);
        return $result['success']
            ? ['success' => true]
            : ['success' => false, 'error' => $result['error'] ?? 'Failed to resume ad'];
    }
}
