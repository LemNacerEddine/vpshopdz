<?php
/**
 * FacebookAd Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../utils/helpers.php';

class FacebookAd {
    private $conn;
    private $table = 'facebook_ads';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $adId = generateId('fbad_');

        $query = "INSERT INTO {$this->table}
                  (ad_id_local, product_id, status, daily_budget_cents, duration_days,
                   target_country, target_age_min, target_age_max, target_interests,
                   ad_text, ad_headline, landing_url, starts_at, ends_at)
                  VALUES
                  (:ad_id_local, :product_id, :status, :daily_budget_cents, :duration_days,
                   :target_country, :target_age_min, :target_age_max, :target_interests,
                   :ad_text, :ad_headline, :landing_url, :starts_at, :ends_at)";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':ad_id_local' => $adId,
            ':product_id' => $data['product_id'],
            ':status' => $data['status'] ?? 'draft',
            ':daily_budget_cents' => $data['daily_budget_cents'] ?? 0,
            ':duration_days' => $data['duration_days'] ?? 7,
            ':target_country' => $data['target_country'] ?? 'DZ',
            ':target_age_min' => $data['target_age_min'] ?? 18,
            ':target_age_max' => $data['target_age_max'] ?? 65,
            ':target_interests' => isset($data['target_interests']) ? json_encode($data['target_interests']) : null,
            ':ad_text' => $data['ad_text'] ?? null,
            ':ad_headline' => $data['ad_headline'] ?? null,
            ':landing_url' => $data['landing_url'] ?? null,
            ':starts_at' => $data['starts_at'] ?? null,
            ':ends_at' => $data['ends_at'] ?? null,
        ]);

        return $this->findById($adId);
    }

    public function findById($adIdLocal) {
        $query = "SELECT fa.*, p.name_ar, p.name_fr, p.name_en, p.price, p.discount_percent,
                         (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = fa.product_id ORDER BY pi.sort_order LIMIT 1) as product_image
                  FROM {$this->table} fa
                  LEFT JOIN products p ON fa.product_id = p.product_id
                  WHERE fa.ad_id_local = :ad_id_local";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':ad_id_local' => $adIdLocal]);
        $ad = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ad) {
            unset($ad['id']);
            if ($ad['target_interests']) {
                $ad['target_interests'] = json_decode($ad['target_interests'], true);
            }
        }

        return $ad;
    }

    public function getAll($filters = []) {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'fa.status = :status';
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['product_id'])) {
            $where[] = 'fa.product_id = :product_id';
            $params[':product_id'] = $filters['product_id'];
        }

        $query = "SELECT fa.*, p.name_ar, p.name_fr, p.name_en, p.price, p.discount_percent,
                         (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = fa.product_id ORDER BY pi.sort_order LIMIT 1) as product_image
                  FROM {$this->table} fa
                  LEFT JOIN products p ON fa.product_id = p.product_id
                  WHERE " . implode(' AND ', $where) . "
                  ORDER BY fa.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($ads as &$ad) {
            unset($ad['id']);
            if ($ad['target_interests']) {
                $ad['target_interests'] = json_decode($ad['target_interests'], true);
            }
        }

        return $ads;
    }

    public function update($adIdLocal, $data) {
        $fields = [];
        $params = [':ad_id_local' => $adIdLocal];

        $allowed = [
            'campaign_id', 'campaign_name', 'adset_id', 'creative_id', 'fb_ad_id',
            'status', 'error_message', 'image_hash', 'ad_text', 'ad_headline',
            'landing_url', 'starts_at', 'ends_at',
        ];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE ad_id_local = :ad_id_local";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
        }

        return $this->findById($adIdLocal);
    }

    public function updateMetrics($adIdLocal, $metrics) {
        $query = "UPDATE {$this->table} SET
                  impressions = :impressions, clicks = :clicks,
                  spend_cents = :spend_cents, reach = :reach,
                  metrics_updated_at = NOW()
                  WHERE ad_id_local = :ad_id_local";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':ad_id_local' => $adIdLocal,
            ':impressions' => $metrics['impressions'] ?? 0,
            ':clicks' => $metrics['clicks'] ?? 0,
            ':spend_cents' => $metrics['spend_cents'] ?? 0,
            ':reach' => $metrics['reach'] ?? 0,
        ]);

        return $this->findById($adIdLocal);
    }

    public function delete($adIdLocal) {
        $query = "DELETE FROM {$this->table} WHERE ad_id_local = :ad_id_local";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':ad_id_local' => $adIdLocal]);
    }

    public function count($status = null) {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        if ($status) {
            $query .= " WHERE status = :status";
            $params[':status'] = $status;
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
}
