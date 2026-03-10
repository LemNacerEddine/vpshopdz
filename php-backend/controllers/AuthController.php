<?php
/**
 * Auth Controller - FIXED VERSION
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class AuthController {
    private $db;
    private $user;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
        $this->auth = new Auth();
    }

    // Register
    public function register() {
        try {
            $data = getJsonInput();

            if (empty($data['email']) && empty($data['phone'])) {
                errorResponse('البريد الإلكتروني أو رقم الهاتف مطلوب', 400);
            }

            if (empty($data['password'])) {
                errorResponse('كلمة المرور مطلوبة', 400);
            }

            // Check if user exists
            if (!empty($data['email'])) {
                $existing = $this->user->findByEmail($data['email']);
                if ($existing) {
                    errorResponse('البريد الإلكتروني مستخدم مسبقاً', 400);
                }
            }

            // Create user
            $user = $this->user->create([
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'name' => $data['name'] ?? null,
                'wilaya' => $data['wilaya'] ?? null,
                'address' => $data['address'] ?? null
            ]);

            // Create default shipping address automatically
            if (!empty($data['wilaya'])) {
                try {
                    $addressData = [
                        'address_id' => 'addr_' . uniqid() . bin2hex(random_bytes(4)),
                        'user_id' => $user['user_id'],
                        'label' => 'المنزل',
                        'full_name' => $data['name'] ?? '',
                        'phone' => $data['phone'] ?? $data['email'] ?? '',
                        'wilaya' => $data['wilaya'],
                        'commune' => $data['commune'] ?? null,
                        'address_line' => $data['address'] ?? '',
                        'is_default' => 1
                    ];

                    $stmt = $this->db->prepare("
                        INSERT INTO addresses (address_id, user_id, label, full_name, phone, wilaya, commune, address_line, is_default, created_at)
                        VALUES (:address_id, :user_id, :label, :full_name, :phone, :wilaya, :commune, :address_line, :is_default, NOW())
                    ");
                    $stmt->execute($addressData);

                    error_log('Default address created for user: ' . $user['user_id']);
                } catch (Exception $e) {
                    error_log('Failed to create default address: ' . $e->getMessage());
                    // Don't fail registration if address creation fails
                }
            }

            // Create session
            $sessionToken = $this->auth->createSession($user['user_id']);

            jsonResponse([
                'user' => $user,
                'session_token' => $sessionToken,
                'message' => 'تم إنشاء الحساب بنجاح'
            ], 201);
        } catch (Exception $e) {
            error_log('Register error: ' . $e->getMessage());
            errorResponse('حدث خطأ أثناء إنشاء الحساب: ' . $e->getMessage(), 500);
        }
    }

    // Login - FIXED VERSION
    public function login() {
        try {
            $data = getJsonInput();

            if (empty($data['identifier'])) {
                errorResponse('البريد الإلكتروني أو رقم الهاتف مطلوب', 400);
            }

            if (empty($data['password'])) {
                errorResponse('كلمة المرور مطلوبة', 400);
            }

            // Find user
            $user = $this->user->findByIdentifier($data['identifier']);

            if (!$user) {
                errorResponse('البريد الإلكتروني أو كلمة المرور غير صحيحة', 401);
            }

            // Verify password
            if (!verifyPassword($data['password'], $user['password_hash'])) {
                errorResponse('البريد الإلكتروني أو كلمة المرور غير صحيحة', 401);
            }

            // Create session with error handling
            try {
                $sessionToken = $this->auth->createSession($user['user_id']);
            } catch (Exception $e) {
                error_log('Session creation failed: ' . $e->getMessage());
                errorResponse('فشل إنشاء الجلسة. يرجى المحاولة مرة أخرى.', 500);
            }

            // Remove sensitive data
            unset($user['password_hash']);
            unset($user['id']);

            jsonResponse([
                'user' => $user,
                'session_token' => $sessionToken,
                'message' => 'تم تسجيل الدخول بنجاح'
            ]);
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            errorResponse('حدث خطأ أثناء تسجيل الدخول: ' . $e->getMessage(), 500);
        }
    }

    // Logout
    public function logout() {
        try {
            $this->auth->destroySession();
            jsonResponse(['message' => 'تم تسجيل الخروج بنجاح']);
        } catch (Exception $e) {
            error_log('Logout error: ' . $e->getMessage());
            errorResponse('حدث خطأ أثناء تسجيل الخروج', 500);
        }
    }

    // Get current user
    public function me() {
        try {
            $user = $this->auth->getCurrentUser();

            if (!$user) {
                errorResponse('غير مصرح به', 401);
            }

            jsonResponse($user);
        } catch (Exception $e) {
            error_log('Me error: ' . $e->getMessage());
            errorResponse('حدث خطأ', 500);
        }
    }

    // Update profile
    public function updateProfile() {
        try {
            $user = $this->auth->requireAuth();
            $data = getJsonInput();

            // Update user profile
            $updated = $this->user->update($user['user_id'], $data);

            // If phone, wilaya, and address are provided, also create/update address entry
            if (!empty($data['phone']) && !empty($data['wilaya']) && !empty($data['address'])) {
                try {
                    // Check if user has any addresses
                    $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM addresses WHERE user_id = ?");
                    $stmt->execute([$user['user_id']]);
                    $result = $stmt->fetch();
                    $hasAddresses = $result['count'] > 0;

                    // Create or update default address
                    $addressData = [
                        'address_id' => 'addr_' . uniqid() . bin2hex(random_bytes(4)),
                        'user_id' => $user['user_id'],
                        'full_name' => $data['name'] ?? $user['name'],
                        'phone' => $data['phone'],
                        'wilaya' => $data['wilaya'],
                        'address_line' => $data['address'],
                        'is_default' => !$hasAddresses ? 1 : 0  // Set as default if it's the first address
                    ];

                    // Check if default address exists
                    $stmt = $this->db->prepare("SELECT address_id FROM addresses WHERE user_id = ? AND is_default = 1");
                    $stmt->execute([$user['user_id']]);
                    $defaultAddress = $stmt->fetch();

                    if ($defaultAddress) {
                        // Update existing default address
                        $stmt = $this->db->prepare("
                            UPDATE addresses
                            SET full_name = ?, phone = ?, wilaya = ?, commune = ?, address_line = ?
                            WHERE address_id = ?
                        ");
                        $stmt->execute([
                            $addressData['full_name'],
                            $addressData['phone'],
                            $addressData['wilaya'],
                            $data['commune'] ?? null,
                            $addressData['address_line'],
                            $defaultAddress['address_id']
                        ]);
                    } else {
                        // Insert new address
                        $stmt = $this->db->prepare("
                            INSERT INTO addresses (address_id, user_id, full_name, phone, wilaya, commune, address_line, is_default)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $addressData['address_id'],
                            $addressData['user_id'],
                            $addressData['full_name'],
                            $addressData['phone'],
                            $addressData['wilaya'],
                            $data['commune'] ?? null,
                            $addressData['address_line'],
                            $addressData['is_default']
                        ]);
                    }

                    error_log("Address saved for user: {$user['user_id']}");
                } catch (Exception $e) {
                    error_log('Address save error: ' . $e->getMessage());
                    // Don't fail the whole request if address save fails
                }
            }

            // Include wilaya/commune from default address in the response
            $stmt = $this->db->prepare("SELECT wilaya, commune, address_line FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
            $stmt->execute([$user['user_id']]);
            $defaultAddr = $stmt->fetch();
            if ($defaultAddr) {
                $updated['wilaya'] = $defaultAddr['wilaya'];
                $updated['commune'] = $defaultAddr['commune'];
                $updated['address'] = $defaultAddr['address_line'];
            }

            jsonResponse($updated);
        } catch (Exception $e) {
            error_log('Update profile error: ' . $e->getMessage());
            errorResponse('حدث خطأ أثناء تحديث الملف الشخصي', 500);
        }
    }

    // Forgot password
    public function forgotPassword() {
        try {
            $data = getJsonInput();

            if (empty($data['email'])) {
                errorResponse('البريد الإلكتروني مطلوب', 400);
            }

            $user = $this->user->findByEmail($data['email']);

            if ($user) {
                $token = $this->user->setResetToken($data['email']);
                // In production, send email with reset link
                // For now, just return success
            }

            // Always return success to prevent email enumeration
            jsonResponse(['message' => 'إذا كان البريد الإلكتروني مسجلاً، ستتلقى رابط إعادة تعيين كلمة المرور']);
        } catch (Exception $e) {
            error_log('Forgot password error: ' . $e->getMessage());
            errorResponse('حدث خطأ', 500);
        }
    }

    // Reset password
    public function resetPassword() {
        try {
            $data = getJsonInput();

            if (empty($data['token']) || empty($data['password'])) {
                errorResponse('الرمز وكلمة المرور الجديدة مطلوبان', 400);
            }

            $user = $this->user->verifyResetToken($data['token']);

            if (!$user) {
                errorResponse('الرمز غير صالح أو منتهي الصلاحية', 400);
            }

            $this->user->resetPassword($data['token'], $data['password']);
            jsonResponse(['message' => 'تم تغيير كلمة المرور بنجاح']);
        } catch (Exception $e) {
            error_log('Reset password error: ' . $e->getMessage());
            errorResponse('حدث خطأ', 500);
        }
    }
}
