<?php
/**
 * Browsing History Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/BrowsingHistory.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class BrowsingHistoryController {
    private $db;
    private $history;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->history = new BrowsingHistory($db);
        $this->auth = new Auth();
    }

    // Get browsing history
    public function index() {
        $user = $this->auth->getCurrentUser();
        $browserId = $_GET['browser_id'] ?? $_COOKIE['browser_id'] ?? null;
        
        $userId = $user ? $user['user_id'] : null;
        $limit = $_GET['limit'] ?? 20;
        
        $history = $this->history->getHistory($userId, $browserId, $limit);
        jsonResponse($history);
    }

    // Add to browsing history
    public function add($productId) {
        $user = $this->auth->getCurrentUser();
        $data = getJsonInput();
        $browserId = $data['browser_id'] ?? $_COOKIE['browser_id'] ?? null;
        
        $userId = $user ? $user['user_id'] : null;
        
        $this->history->addToHistory($productId, $userId, $browserId);
        jsonResponse(['message' => 'تمت الإضافة']);
    }

    // Clear browsing history
    public function clear() {
        $user = $this->auth->getCurrentUser();
        $data = getJsonInput();
        $browserId = $data['browser_id'] ?? $_COOKIE['browser_id'] ?? null;
        
        $userId = $user ? $user['user_id'] : null;
        
        $this->history->clearHistory($userId, $browserId);
        jsonResponse(['message' => 'تم مسح السجل']);
    }
}
