<?php
/**
 * Setting Model (Key-Value Store)
 * AgroYousfi E-commerce
 */

class Setting {
    private $conn;
    private $table = 'store_settings';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get a single setting value
     */
    public function get($key, $default = null) {
        $query = "SELECT setting_value FROM {$this->table} WHERE setting_key = :key LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':key' => $key]);
        $row = $stmt->fetch();

        return $row ? $row['setting_value'] : $default;
    }

    /**
     * Set a single setting value (upsert)
     */
    public function set($key, $value) {
        $query = "INSERT INTO {$this->table} (setting_key, setting_value)
                  VALUES (:key, :value)
                  ON DUPLICATE KEY UPDATE setting_value = :value2";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':key' => $key,
            ':value' => $value,
            ':value2' => $value,
        ]);
    }

    /**
     * Get multiple settings by prefix
     */
    public function getByPrefix($prefix) {
        $query = "SELECT setting_key, setting_value FROM {$this->table} WHERE setting_key LIKE :prefix";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':prefix' => $prefix . '%']);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }

    /**
     * Get all settings
     */
    public function getAll() {
        $query = "SELECT setting_key, setting_value FROM {$this->table}";
        $stmt = $this->conn->query($query);
        $rows = $stmt->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }

    /**
     * Set multiple settings at once
     */
    public function setMany($settings) {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Delete a setting
     */
    public function delete($key) {
        $query = "DELETE FROM {$this->table} WHERE setting_key = :key";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':key' => $key]);
    }
}
