<?php
/**
 * Address Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/Address.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class AddressController {
    private $db;
    private $address;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->address = new Address($db);
        $this->auth = new Auth();
    }

    // Get user addresses
    public function index() {
        $user = $this->auth->requireAuth();
        $addresses = $this->address->getByUser($user['user_id']);
        jsonResponse($addresses);
    }

    // Create address
    public function store() {
        $user = $this->auth->requireAuth();
        $data = getJsonInput();

        if (empty($data['full_name']) || empty($data['phone']) || empty($data['wilaya']) || empty($data['address_line'])) {
            errorResponse('الاسم ورقم الهاتف والولاية والعنوان مطلوبون', 400);
        }

        $address = $this->address->create($user['user_id'], $data);
        jsonResponse($address, 201);
    }

    // Update address
    public function update($addressId) {
        $user = $this->auth->requireAuth();
        $data = getJsonInput();

        $address = $this->address->update($addressId, $user['user_id'], $data);
        
        if (!$address) {
            errorResponse('العنوان غير موجود', 404);
        }

        jsonResponse($address);
    }

    // Delete address
    public function destroy($addressId) {
        $user = $this->auth->requireAuth();
        
        $this->address->delete($addressId, $user['user_id']);
        jsonResponse(['message' => 'تم حذف العنوان بنجاح']);
    }
}
