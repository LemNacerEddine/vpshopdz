<?php
/**
 * Upload Controller
 * AgroYousfi E-commerce
 */

require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/helpers.php';

class UploadController {
    private $auth;
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $maxSize = 5 * 1024 * 1024; // 5MB

    public function __construct() {
        $this->auth = new Auth();
        $this->uploadDir = __DIR__ . '/../uploads/';

        // Create upload directory if not exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    // Upload image
    public function uploadImage() {
        $this->auth->requireAdmin();

        if (empty($_FILES['file'])) {
            errorResponse('لم يتم رفع أي ملف', 400);
        }

        $file = $_FILES['file'];

        // Validate file type
        if (!in_array($file['type'], $this->allowedTypes)) {
            errorResponse('نوع الملف غير مسموح', 400);
        }

        // Validate file size
        if ($file['size'] > $this->maxSize) {
            errorResponse('حجم الملف كبير جداً (الحد الأقصى 5MB)', 400);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            errorResponse('فشل في رفع الملف', 500);
        }

        // Return URL ✨ المحسّن
        $baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'];

        // Get base path from REQUEST_URI (handles subdirectories like /agro-yousfi/)
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $basePath = '';

        // Extract base path (e.g., /agro-yousfi/) from REQUEST_URI
        if (preg_match('#^(/[^/]+)?/api/#', $requestUri, $matches)) {
            $basePath = $matches[1] ?? '';
        }

        $url = $baseUrl . $basePath . '/api/uploads/' . $filename;

        jsonResponse([
            'url' => $url,
            'filename' => $filename
        ]);
    }

    // Serve uploaded file
    public function serveFile($filename) {
        $filepath = $this->uploadDir . basename($filename);

        if (!file_exists($filepath)) {
            errorResponse('الملف غير موجود', 404);
        }

        $mimeType = mime_content_type($filepath);
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: public, max-age=31536000');
        readfile($filepath);
        exit();
    }
}