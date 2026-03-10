<?php
/**
 * Address Controller - FIXED VERSION
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/Address.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class AddressController {
    private $db;
    private $address;
    private $user;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->address = new Address($db);
        $this->user = new User($db);
        $this->auth = new Auth();
    }

    // Get user addresses
    public function index() {
        try {
            $user = $this->auth->requireAuth();
            $addresses = $this->address->getByUser($user['user_id']);

            // If no addresses exist, create default from user profile
            if (empty($addresses) && !empty($user['name']) && !empty($user['phone'])) {
                $defaultAddress = [
                    'label' => 'المنزل',
                    'full_name' => $user['name'],
                    'phone' => $user['phone'],
                    'wilaya' => $user['wilaya'] ?? '',
                    'address_line' => $user['address'] ?? '',
                    'is_default' => true
                ];

                if (!empty($defaultAddress['wilaya']) && !empty($defaultAddress['address_line'])) {
                    $created = $this->address->create($user['user_id'], $defaultAddress);
                    $addresses = [$created];
                }
            }

            jsonResponse($addresses);
        } catch (Exception $e) {
            error_log('Get addresses error: ' . $e->getMessage());
            errorResponse('حدث خطأ أثناء جلب العناوين', 500);
        }
    }

    // Create address
    public function store() {
        try {
            $user = $this->auth->requireAuth();
            $data = getJsonInput();

            // Map frontend field names to backend
            $mappedData = [
                'label' => $data['label'] ?? $data['title'] ?? 'المنزل',
                'full_name' => $data['full_name'] ?? $user['name'],
                'phone' => $data['phone'] ?? $user['phone'],
                'wilaya' => $data['wilaya'] ?? '',
                'commune' => $data['commune'] ?? null,
                'address_line' => $data['address_line'] ?? $data['address'] ?? '',
                'is_default' => $data['is_default'] ?? $data['isDefault'] ?? false
            ];

            // Validate required fields
            if (empty($mappedData['full_name'])) {
                errorResponse('الاسم الكامل مطلوب', 400);
            }
            if (empty($mappedData['phone'])) {
                errorResponse('رقم الهاتف مطلوب', 400);
            }
            if (empty($mappedData['wilaya'])) {
                errorResponse('الولاية مطلوبة', 400);
            }
            if (empty($mappedData['address_line'])) {
                errorResponse('العنوان مطلوب', 400);
            }

            $address = $this->address->create($user['user_id'], $mappedData);
            jsonResponse($address, 201);
        } catch (Exception $e) {
            error_log('Create address error: ' . $e->getMessage());
            errorResponse('حدث خطأ أثناء إضافة العنوان: ' . $e->getMessage(), 500);
        }
    }

    // Update address
    public function update($addressId) {
        try {
            $user = $this->auth->requireAuth();
            $data = getJsonInput();

            // Map frontend field names to backend
            $mappedData = [];
            if (isset($data['title'])) $mappedData['label'] = $data['title'];
            if (isset($data['label'])) $mappedData['label'] = $data['label'];
            if (isset($data['full_name'])) $mappedData['full_name'] = $data['full_name'];
            if (isset($data['phone'])) $mappedData['phone'] = $data['phone'];
            if (isset($data['wilaya'])) $mappedData['wilaya'] = $data['wilaya'];
            if (isset($data['commune'])) $mappedData['commune'] = $data['commune'];
            if (isset($data['address'])) $mappedData['address_line'] = $data['address'];
            if (isset($data['address_line'])) $mappedData['address_line'] = $data['address_line'];
            if (isset($data['is_default'])) $mappedData['is_default'] = $data['is_default'];
            if (isset($data['isDefault'])) $mappedData['is_default'] = $data['isDefault'];

            $address = $this->address->update($addressId, $user['user_id'], $mappedData);

            if (!$address) {
                errorResponse('العنوان غير موجود', 404);
            }

            jsonResponse($address);
        } catch (Exception $e) {
            error_log('Update address error: ' . $e->getMessage());
            errorResponse('حدث خطأ أثناء تحديث العنوان', 500);
        }
    }

    // Delete address
    public function destroy($addressId) {
        try {
            $user = $this->auth->requireAuth();

            $this->address->delete($addressId, $user['user_id']);
            jsonResponse(['message' => 'تم حذف العنوان بنجاح']);
        } catch (Exception $e) {
            error_log('Delete address error: ' . $e->getMessage());
            errorResponse('حدث خطأ أثناء حذف العنوان', 500);
        }
    }

    // Set default address
    public function setDefault($addressId) {
        try {
            $user = $this->auth->requireAuth();

            $address = $this->address->update($addressId, $user['user_id'], ['is_default' => true]);

            if (!$address) {
                errorResponse('العنوان غير موجود', 404);
            }

            jsonResponse($address);
        } catch (Exception $e) {
            error_log('Set default address error: ' . $e->getMessage());
            errorResponse('حدث خطأ', 500);
        }
    }
}
