<?php
/**
 * Order Shipping Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class OrderShipping {
    private $conn;
    private $table = 'order_shipping';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $shippingId = generateId('os_');
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table}
             (shipping_id, order_id, company_id, shipping_type, total_weight,
              billable_weight, shipping_cost, tracking_number, status)
             VALUES (:sid, :oid, :cid, :st, :tw, :bw, :sc, :tn, :status)"
        );
        $stmt->execute([
            ':sid' => $shippingId, ':oid' => $data['order_id'], ':cid' => $data['company_id'],
            ':st' => $data['shipping_type'] ?? 'home', ':tw' => $data['total_weight'] ?? 0,
            ':bw' => $data['billable_weight'] ?? 0, ':sc' => $data['shipping_cost'] ?? 0,
            ':tn' => $data['tracking_number'] ?? null, ':status' => $data['status'] ?? 'pending'
        ]);
        return $this->findByOrderId($data['order_id']);
    }

    public function findByOrderId($orderId) {
        $stmt = $this->conn->prepare(
            "SELECT os.*, sc.name_ar as company_name_ar, sc.name_fr as company_name_fr,
                    sc.name_en as company_name_en, sc.logo as company_logo,
                    sc.tracking_url_template, sc.phone as company_phone
             FROM {$this->table} os
             LEFT JOIN shipping_companies sc ON os.company_id = sc.company_id
             WHERE os.order_id = :oid"
        );
        $stmt->execute([':oid' => $orderId]);
        $shipping = $stmt->fetch();
        if ($shipping) unset($shipping['id']);
        return $shipping;
    }

    public function updateTracking($orderId, $trackingNumber) {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET tracking_number = :tn WHERE order_id = :oid");
        $stmt->execute([':tn' => $trackingNumber, ':oid' => $orderId]);
        return $this->findByOrderId($orderId);
    }

    public function updateStatus($orderId, $status) {
        $valid = ['pending','confirmed','in_transit','delivered','returned'];
        if (!in_array($status, $valid)) return false;
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET status = :s WHERE order_id = :oid");
        $stmt->execute([':s' => $status, ':oid' => $orderId]);
        return $this->findByOrderId($orderId);
    }
}
