<?php
/**
 * Auth Controller
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
            'name' => $data['name'] ?? null
        ]);

        // Create session
        $sessionToken = $this->auth->createSession($user['user_id']);

        jsonResponse([
            'user' => $user,
            'session_token' => $sessionToken,
            'message' => 'تم إنشاء الحساب بنجاح'
        ], 201);
    }

    // Login
    public function login() {
        $data = getJsonInput();

        if (empty($data['identifier'])) {
            errorResponse('البريد الإلكتروني أو رقم الهاتف مطلوب', 400);
        }

        if (empty($data['password'])) {
            errorResponse('كلمة المرور مطلوبة', 400);
        }

        // Find user
        $user = $this->user->findByIdentifier($data['identifier']);
        
        if (!$user || !verifyPassword($data['password'], $user['password_hash'])) {
            errorResponse('بيانات الدخول غير صحيحة', 401);
        }

        // Create session
        $sessionToken = $this->auth->createSession($user['user_id']);

        // Remove sensitive data
        unset($user['password_hash']);
        unset($user['id']);

        jsonResponse([
            'user' => $user,
            'session_token' => $sessionToken,
            'message' => 'تم تسجيل الدخول بنجاح'
        ]);
    }

    // Logout
    public function logout() {
        $this->auth->destroySession();
        jsonResponse(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    // Get current user
    public function me() {
        $user = $this->auth->getCurrentUser();
        
        if (!$user) {
            errorResponse('غير مصرح به', 401);
        }

        jsonResponse($user);
    }

    // Update profile
    public function updateProfile() {
        $user = $this->auth->requireAuth();
        $data = getJsonInput();

        $updated = $this->user->update($user['user_id'], $data);
        jsonResponse($updated);
    }

    // Forgot password
    public function forgotPassword() {
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
    }

    // Reset password
    public function resetPassword() {
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
    }
}
