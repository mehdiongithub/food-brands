<?php
// Prevent direct access to this file
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'foodscope');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site URL - change if your folder name is different
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
define('BASE_URL', $protocol . '://' . $host . '/food-brands');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Connection (PDO)
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection failed']);
            exit;
        }
    }
    return $pdo;
}

// Helper: JSON response
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Helper: Get input
function getInput($key, $default = null) {
    if (isset($_GET[$key])) return $_GET[$key];
    if (isset($_POST[$key])) return $_POST[$key];
    if (isset($_REQUEST[$key])) return $_REQUEST[$key];
    return $default;
}

// Helper: Sanitize
function clean($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

/**
 * Auto-detect visitor's country
 * ------------------------------------------------------------------
 * Runs once per session on the visitor's very first request. Looks up
 * their public IP against a free geolocation service, matches the
 * returned 2-letter country code against the `countries` table, and
 * caches the result in $_SESSION['country_id']. If detection fails for
 * any reason (local/dev IP, no internet, no matching country row) it
 * silently falls back to the first active country in the table, so the
 * site always has a valid country selected without ever showing a
 * manual picker.
 * ------------------------------------------------------------------
 */
function getVisitorIp() {
    $headerKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    foreach ($headerKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $candidate = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }
    }
    return null;
}

function detectCountryCodeFromIp($ip) {
    // Skip lookups for private/local/reserved IPs (e.g. localhost during development)
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return null;
    }

    $url = 'http://ip-api.com/json/' . urlencode($ip) . '?fields=status,countryCode';
    $response = false;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT         => 2,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
    } elseif (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create(['http' => ['timeout' => 2]]);
        $response = @file_get_contents($url, false, $ctx);
    }

    if (!$response) {
        return null;
    }

    $data = json_decode($response, true);
    if (!empty($data['status']) && $data['status'] === 'success' && !empty($data['countryCode'])) {
        return strtoupper($data['countryCode']);
    }
    return null;
}

function resolveVisitorCountryId() {
    $countryId = null;

    $ip = getVisitorIp();
    $code = $ip ? detectCountryCodeFromIp($ip) : null;

    try {
        $pdo = getDB();

        if ($code) {
            $stmt = $pdo->prepare("SELECT id FROM countries WHERE code = ? AND status = 1 LIMIT 1");
            $stmt->execute([$code]);
            $match = $stmt->fetch();
            if ($match) {
                $countryId = (int) $match['id'];
            }
        }

        if (!$countryId) {
            // No IP match (or detection unavailable) — use the first active country
            $stmt = $pdo->query("SELECT id FROM countries WHERE status = 1 ORDER BY id ASC LIMIT 1");
            $first = $stmt->fetch();
            $countryId = $first ? (int) $first['id'] : 1;
        }
    } catch (Exception $e) {
        $countryId = 1;
    }

    return $countryId;
}

// Resolve the visitor's country once per session
if (!isset($_SESSION['country_id'])) {
    $_SESSION['country_id'] = resolveVisitorCountryId();
}