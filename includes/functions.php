<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/..');
}
require_once BASE_PATH . '/config/database-config.php';

/**
 * Build absolute asset URL from relative database path
 */
function asset_url($path) {
    if (empty($path)) return BASE_URL . '/assets/img/placeholder/placeholder.webp';
    if (strpos($path, 'http') === 0) return $path;
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Get site settings (cached in session for performance)
 */
function getSettings() {
    if (isset($_SESSION['site_settings'])) {
        return $_SESSION['site_settings'];
    }
    $db = getDB();
    $stmt = $db->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch() ?: [];
    $_SESSION['site_settings'] = $settings;
    return $settings;
}

/**
 * Get current country info.
 *
 * $_SESSION['country_id'] is already resolved before this ever runs:
 * config/database-config.php auto-detects it from the visitor's IP on
 * their first request of the session (see resolveVisitorCountryId()
 * there), and the header country dropdown updates it on manual switch.
 * This just reads whatever is currently in the session, with a safe
 * fallback if the stored id is somehow no longer a valid active country.
 */
function getCurrentCountry() {
    $db = getDB();
    $id = (int) ($_SESSION['country_id'] ?? 0);

    $stmt = $db->prepare("SELECT * FROM countries WHERE id = ? AND status = 1");
    $stmt->execute([$id]);
    $country = $stmt->fetch();

    if (!$country) {
        // Fallback to first active country
        $stmt = $db->query("SELECT * FROM countries WHERE status = 1 ORDER BY id ASC LIMIT 1");
        $country = $stmt->fetch();
        if ($country) {
            $_SESSION['country_id'] = $country['id'];
        }
    }

    return $country ?: ['id' => 1, 'name' => 'Pakistan', 'code' => 'PK', 'currency' => 'RUPEE', 'currency_symbol' => 'RS', 'flag' => ''];
}

/**
 * Format price with currency symbol
 */
function formatPrice($amount, $country) {
    if ($amount === null || $amount === '') return 'N/A';
    $symbol = $country['currency_symbol'] ?? '';
    return $symbol . number_format((float) $amount, 0);
}

/**
 * Calculate discount percentage
 */
function discountPercent($regular, $discount) {
    if (!$regular || !$discount || $discount >= $regular) return 0;
    return round((($regular - $discount) / $regular) * 100);
}

/**
 * Strip HTML tags for meta descriptions
 */
function stripMeta($html, $maxLen = 160) {
    $text = strip_tags($html);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    if (strlen($text) > $maxLen) {
        $text = substr($text, 0, $maxLen - 3) . '...';
    }
    return $text;
}

/**
 * Generate page title
 */
function pageTitle($custom = '', $suffix = true) {
    $settings = getSettings();
    $siteName = $settings['site_name'] ?? 'FoodScope';
    if ($custom) {
        return $suffix ? $custom . ' — ' . $siteName : $custom;
    }
    return $siteName;
}