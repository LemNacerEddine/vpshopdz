<?php
/**
 * Shipping Rate Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class ShippingRate {
    private $conn;
    private $table = 'shipping_rates';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByCompany($companyId) {
        $query = "SELECT * FROM {$this->table} WHERE company_id = :company_id ORDER BY wilaya ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':company_id' => $companyId]);
        $rates = $stmt->fetchAll();
        foreach ($rates as &$r) { unset($r['id']); }
        return $rates;
    }

    // Get shipping options: commune-specific first, then wilaya default
    public function getAvailableOptions($wilaya, $commune = null, $shippingType = 'home') {
        $companiesStmt = $this->conn->query("SELECT * FROM shipping_companies WHERE is_active = 1 ORDER BY sort_order ASC");
        $companies = $companiesStmt->fetchAll();
        $options = [];

        foreach ($companies as $company) {
            $rate = null;

            // Step 1: Try commune-specific rate
            if ($commune) {
                $stmt = $this->conn->prepare(
                    "SELECT * FROM {$this->table}
                     WHERE company_id = :cid AND wilaya = :w AND commune = :c AND shipping_type = :st AND is_active = 1"
                );
                $stmt->execute([':cid' => $company['company_id'], ':w' => $wilaya, ':c' => $commune, ':st' => $shippingType]);
                $rate = $stmt->fetch();
            }

            // Step 2: Fall back to wilaya default (commune IS NULL)
            if (!$rate) {
                $stmt = $this->conn->prepare(
                    "SELECT * FROM {$this->table}
                     WHERE company_id = :cid AND wilaya = :w AND commune IS NULL AND shipping_type = :st AND is_active = 1"
                );
                $stmt->execute([':cid' => $company['company_id'], ':w' => $wilaya, ':st' => $shippingType]);
                $rate = $stmt->fetch();
            }

            if ($rate) {
                unset($rate['id']); unset($company['id']);
                $options[] = ['company' => $company, 'rate' => $rate];
            }
        }
        return $options;
    }

    public function upsertRate($companyId, $wilaya, $commune, $shippingType, $data) {
        $checkQuery = "SELECT rate_id FROM {$this->table}
                       WHERE company_id = :cid AND wilaya = :w
                       AND " . ($commune ? "commune = :c" : "commune IS NULL") . "
                       AND shipping_type = :st";
        $params = [':cid' => $companyId, ':w' => $wilaya, ':st' => $shippingType];
        if ($commune) $params[':c'] = $commune;

        $stmt = $this->conn->prepare($checkQuery);
        $stmt->execute($params);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $this->conn->prepare(
                "UPDATE {$this->table} SET base_price = :bp,
                 min_delivery_days = :mind, max_delivery_days = :maxd,
                 is_active = :active WHERE rate_id = :rid"
            );
            $stmt->execute([
                ':bp' => $data['base_price'],
                ':mind' => $data['min_delivery_days'] ?? 1, ':maxd' => $data['max_delivery_days'] ?? 3,
                ':active' => $data['is_active'] ?? true, ':rid' => $existing['rate_id']
            ]);
            return $existing['rate_id'];
        } else {
            $rateId = generateId('rate_');
            $stmt = $this->conn->prepare(
                "INSERT INTO {$this->table}
                 (rate_id, company_id, wilaya, commune, shipping_type, base_price,
                  min_delivery_days, max_delivery_days, is_active)
                 VALUES (:rid, :cid, :w, :c, :st, :bp, :mind, :maxd, :active)"
            );
            $stmt->execute([
                ':rid' => $rateId, ':cid' => $companyId, ':w' => $wilaya, ':c' => $commune,
                ':st' => $shippingType, ':bp' => $data['base_price'],
                ':mind' => $data['min_delivery_days'] ?? 1, ':maxd' => $data['max_delivery_days'] ?? 3,
                ':active' => $data['is_active'] ?? true
            ]);
            return $rateId;
        }
    }

    public function bulkUpsert($companyId, $rates) {
        $this->conn->beginTransaction();
        try {
            $results = [];
            foreach ($rates as $rate) {
                $results[] = $this->upsertRate(
                    $companyId, $rate['wilaya'], $rate['commune'] ?? null,
                    $rate['shipping_type'] ?? 'home', $rate
                );
            }
            $this->conn->commit();
            return $results;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function deleteRate($rateId) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE rate_id = :rid");
        return $stmt->execute([':rid' => $rateId]);
    }
}
