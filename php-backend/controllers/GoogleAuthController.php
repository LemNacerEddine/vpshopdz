<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/google_auth_helpers.php';

class GoogleAuthController {
    private $db;
    private $setting;

    public function __construct($db) {
        $this->db = $db;
        require_once __DIR__ . '/../models/Setting.php';
        $this->setting = new Setting($db);
    }

    /**
     * Get Google OAuth config from store_settings or env
     */
    private function getGoogleConfig() {
        return [
            'client_id' => $this->setting->get('google_client_id', getenv('GOOGLE_CLIENT_ID') ?: ''),
            'client_secret' => $this->setting->get('google_client_secret', getenv('GOOGLE_CLIENT_SECRET') ?: ''),
            'redirect_uri' => $this->setting->get('google_redirect_uri', getenv('GOOGLE_REDIRECT_URI') ?: ''),
        ];
    }

    /**
     * Step 1: Generate Google OAuth URL
     */
    public function getAuthUrl() {
        $config = $this->getGoogleConfig();
        $clientId = $config['client_id'];
        $redirectUri = $config['redirect_uri'];

        if (!$clientId || !$redirectUri) {
            http_response_code(500);
            echo json_encode(['error' => 'Google OAuth not configured']);
            exit();
        }

        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'prompt' => 'select_account'
        ];

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);

        echo json_encode(['authUrl' => $authUrl]);
        exit();
    }

    /**
     * Step 2: Handle callback and exchange code for token
     */
    public function handleCallback() {
        $code = $_GET['code'] ?? null;

        if (!$code) {
            http_response_code(400);
            echo json_encode(['error' => 'Authorization code not provided']);
            exit();
        }

        // Exchange code for access token
        $tokenData = $this->exchangeCodeForToken($code);

        if (!$tokenData) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to exchange code for token']);
            exit();
        }

        // Get user info from Google
        $userInfo = $this->getUserInfo($tokenData['access_token']);

        if (!$userInfo) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to get user info']);
            exit();
        }

        // Create or update user in database
        $user = $this->createOrUpdateUser($userInfo);

        if (!$user) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create user']);
            exit();
        }

        // Create session
        $sessionId = createGoogleSession($this->db, $user['user_id']);

        if (!$sessionId) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create session']);
            exit();
        }

        // Set session cookie
        setGoogleSessionCookie($sessionId, strtotime('+7 days'));

        // Fetch wilaya from default address
        $stmt = $this->db->prepare("SELECT wilaya FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
        $stmt->execute([$user['user_id']]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Prepare user data for frontend
        $userData = [
            'user_id' => $user['user_id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? '',
            'wilaya' => $address ? $address['wilaya'] : '',
            'role' => $user['role'] ?? 'customer'
        ];

        // Encode user data as base64
        $encodedUser = base64_encode(json_encode($userData));

        // Determine redirect URL based on profile completeness (phone number)
        $frontendUrl = 'https://vpdeveloper.dz/agro-yousfi';
        
        $hasPhone = !empty($user['phone']);
        $redirectPath = $hasPhone ? '/' : '/profile';
        
        header('Location: ' . $frontendUrl . $redirectPath . '?google_auth=success&user=' . urlencode($encodedUser) . ($hasPhone ? '' : '&complete_profile=1'));
        exit();
    }

    /**
     * Exchange authorization code for access token
     */
    private function exchangeCodeForToken($code) {
        $config = $this->getGoogleConfig();
        $clientId = $config['client_id'];
        $clientSecret = $config['client_secret'];
        $redirectUri = $config['redirect_uri'];

        $tokenUrl = 'https://oauth2.googleapis.com/token';

        $postData = [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Google token exchange failed: " . $response);
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Get user info from Google
     */
    private function getUserInfo($accessToken) {
        $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';

        $ch = curl_init($userInfoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("Google userinfo failed: " . $response);
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Create or update user in database
     */
    private function createOrUpdateUser($googleUser) {
        try {
            // Check if user exists
            $stmt = $this->db->prepare("
                SELECT * FROM users 
                WHERE email = ? OR google_id = ?
            ");
            $stmt->execute([$googleUser['email'], $googleUser['id']]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingUser) {
                // Update existing user
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET google_id = ?, name = ?, updated_at = NOW()
                    WHERE user_id = ?
                ");
                $stmt->execute([
                    $googleUser['id'],
                    $googleUser['name'],
                    $existingUser['user_id']
                ]);

                return $existingUser;
            } else {
                // Create new user
                $userId = 'user_' . uniqid();

                $stmt = $this->db->prepare("
                    INSERT INTO users (user_id, name, email, google_id, role, created_at, updated_at)
                    VALUES (?, ?, ?, ?, 'customer', NOW(), NOW())
                ");
                $stmt->execute([
                    $userId,
                    $googleUser['name'],
                    $googleUser['email'],
                    $googleUser['id']
                ]);


                return [
                    'user_id' => $userId,
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'role' => 'customer'
                ];
            }
        } catch (Exception $e) {
            error_log("Failed to create/update user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create default address for new user
     */
    private function createDefaultAddress($userId, $name, $email) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO addresses (user_id, label, full_name, phone, wilaya, address_line, is_default)
                VALUES (?, 'المنزل', ?, ?, '', '', 1)
            ");
            $stmt->execute([$userId, $name, $email]);
        } catch (Exception $e) {
            error_log("Failed to create default address: " . $e->getMessage());
        }
    }
}
