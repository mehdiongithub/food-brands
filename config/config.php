<?php
/**
 * config/config.php
 */

// Start session on EVERY page/API before anything else runs
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ENVIRONMENT', 'development');

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Auto-detect BASE_URL the same way config/database-config.php does,
// so the admin panel keeps working no matter what your project folder
// is named.
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : '';
    $projectRoot = realpath(__DIR__ . '/..');
    $urlPrefix = '';
    if ($docRoot && $projectRoot && strpos($projectRoot, $docRoot) === 0) {
        $urlPrefix = rtrim(str_replace('\\', '/', substr($projectRoot, strlen($docRoot))), '/');
    }
    define('BASE_URL', $protocol . '://' . $host . $urlPrefix);
}

date_default_timezone_set('Asia/Karachi');

define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

define('SITE_NAME', 'FoodScope');
define('ADMIN_EMAIL', 'admin@foodscope.com');

define('ENCRYPTION_KEY', 'change-this-to-a-long-random-secret-string-1234567890');

