<?php
/**
 * Shipping Rule Model
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/helpers.php';

class ShippingRule {
    private $conn;
    private $table = 'shipping_rules';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($onlyActive = false) {
        $query = "SELECT * FROM {$this->table}";
        if ($onlyActive) $query .= " WHERE is_active = 1";
        $query .= " ORDER BY priority DESC, created_at DESC";
        $stmt = $this->conn->query($query);
        $rules = $stmt->fetchAll();
        foreach ($rules as &$r) { unset($r['id']); }
        return $rules;
    }

    public function findById($ruleId) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE rule_id = :rid");
        $stmt->execute([':rid' => $ruleId]);
        $rule = $stmt->fetch();
        if ($rule) unset($rule['id']);
        return $rule;
    }

    public function getActiveRules() {
        $stmt = $this->conn->query(
            "SELECT * FROM {$this->table}
             WHERE is_active = 1
             AND (start_date IS NULL OR start_date <= NOW())
             AND (end_date IS NULL OR end_date >= NOW())
             ORDER BY priority DESC"
        );
        $rules = $stmt->fetchAll();
        foreach ($rules as &$r) { unset($r['id']); }
        return $rules;
    }

    public function create($data) {
        $ruleId = generateId('ship_rule_');
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table}
             (rule_id, rule_name, rule_type, condition_value, shipping_cost_override,
              is_active, start_date, end_date, priority)
             VALUES (:rid, :name, :type, :val, :cost, :active, :start, :end, :pri)"
        );
        $stmt->execute([
            ':rid' => $ruleId, ':name' => $data['rule_name'], ':type' => $data['rule_type'],
            ':val' => $data['condition_value'], ':cost' => $data['shipping_cost_override'] ?? 0,
            ':active' => $data['is_active'] ?? true,
            ':start' => $data['start_date'] ?? null, ':end' => $data['end_date'] ?? null,
            ':pri' => $data['priority'] ?? 0
        ]);
        return $this->findById($ruleId);
    }

    public function update($ruleId, $data) {
        $existing = $this->findById($ruleId);
        if (!$existing) return null;

        $fields = []; $params = [':rid' => $ruleId];
        $allowed = ['rule_name','rule_type','condition_value','shipping_cost_override','is_active','start_date','end_date','priority'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = :{$f}";
                $params[":{$f}"] = $data[$f];
            }
        }
        if (!empty($fields)) {
            $stmt = $this->conn->prepare("UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE rule_id = :rid");
            $stmt->execute($params);
        }
        return $this->findById($ruleId);
    }

    public function delete($ruleId) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE rule_id = :rid");
        return $stmt->execute([':rid' => $ruleId]);
    }
}
