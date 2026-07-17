<?php
// Prevent direct browser access to this file
if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    // Allow direct access - this is an API endpoint
}

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database-config.php';


// Set JSON response header
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $db = getDB();

    // Fetch site settings
    $stmt = $db->query("SELECT id, site_name, logo, favicon, email, phone, address, facebook, instagram, twitter, youtube, linkedin, copyright FROM settings LIMIT 1");
    $settings = $stmt->fetch();

    if (!$settings) {
        $settings = [
            'site_name' => 'MenuCrest',
            'logo' => '',
            'favicon' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'facebook' => '',
            'instagram' => '',
            'twitter' => '',
            'youtube' => '',
            'linkedin' => '',
            'copyright' => ''
        ];
    }

    // Build asset URLs for logo and favicon
    $settings['logo_url'] = asset_url($settings['logo']);
    $settings['favicon_url'] = asset_url($settings['favicon']);

    // Remove raw paths (don't send relative paths to frontend)
    unset($settings['logo']);
    unset($settings['favicon']);

    // Fetch current country
    $countryId = (int) $_SESSION['country_id'];
    $stmt = $db->prepare("SELECT id, name, code, currency, currency_symbol, flag, slug FROM countries WHERE id = ? AND status = 1");
    $stmt->execute([$countryId]);
    $currentCountry = $stmt->fetch();

    // Fallback to first available country
    if (!$currentCountry) {
        $stmt = $db->query("SELECT id, name, code, currency, currency_symbol, flag, slug FROM countries WHERE status = 1 ORDER BY id ASC LIMIT 1");
        $currentCountry = $stmt->fetch();
        if ($currentCountry) {
            $_SESSION['country_id'] = (int) $currentCountry['id'];
        }
    }

    if ($currentCountry) {
        $currentCountry['flag_url'] = asset_url($currentCountry['flag']);
        unset($currentCountry['flag']);
    } else {
        $currentCountry = [
            'id' => 0,
            'name' => 'Unknown',
            'code' => 'XX',
            'currency' => '',
            'currency_symbol' => '',
            'slug' => '',
            'flag_url' => ''
        ];
    }

    // Fetch all active countries for the dropdown
    $stmt = $db->query("SELECT id, name, code, currency, currency_symbol, flag, slug FROM countries WHERE status = 1 ORDER BY name ASC");
    $allCountries = $stmt->fetchAll();

    // Build country list with full URLs
    $countriesList = [];
    foreach ($allCountries as $c) {
        $countriesList[] = [
            'id'            => (int) $c['id'],
            'name'          => $c['name'],
            'code'          => $c['code'],
            'currency'      => $c['currency'],
            'currency_symbol'=> $c['currency_symbol'],
            'slug'          => $c['slug'],
            'flag_url'      => asset_url($c['flag']),
            'is_current'    => ((int) $c['id'] === (int) $currentCountry['id']) ? true : false
        ];
    }

    // Fetch footer categories (limit 6)
    $stmt = $db->query("
        SELECT id, name, slug 
        FROM categories 
        WHERE status = 1 
        ORDER BY sort_order ASC, id ASC 
        LIMIT 6
    ");
    $footerCategories = $stmt->fetchAll();

    $categoriesList = [];
    foreach ($footerCategories as $cat) {
        $categoriesList[] = [
            'id'   => (int) $cat['id'],
            'name' => $cat['name'],
            'slug' => $cat['slug'],
            'url'  => BASE_URL . '/category/' . $cat['slug']
        ];
    }

    // Fetch footer brands (limit 6)
    $stmt = $db->query("
        SELECT id, name, slug, logo 
        FROM brands 
        WHERE status = 1 
        ORDER BY id ASC 
        LIMIT 6
    ");
    $footerBrands = $stmt->fetchAll();

    $brandsList = [];
    foreach ($footerBrands as $b) {
        $brandsList[] = [
            'id'    => (int) $b['id'],
            'name'  => $b['name'],
            'slug'  => $b['slug'],
            'logo'  => asset_url($b['logo']),
            'url'   => BASE_URL . '/brand/' . $b['slug']
        ];
    }

    // Fetch quick stats for home page
    $totalBrands = (int) $db->query("SELECT COUNT(*) FROM brands WHERE status = 1")->fetchColumn();
    $totalProducts = (int) $db->query("SELECT COUNT(*) FROM products WHERE status = 1")->fetchColumn();
    $totalCountries = (int) $db->query("SELECT COUNT(*) FROM countries WHERE status = 1")->fetchColumn();
    $totalCategories = (int) $db->query("SELECT COUNT(*) FROM categories WHERE status = 1")->fetchColumn();
    $totalOffers = (int) $db->query("SELECT COUNT(*) FROM offers WHERE status = 1")->fetchColumn();

    $stats = [
        'total_brands'    => $totalBrands,
        'total_products'  => $totalProducts,
        'total_countries' => $totalCountries,
        'total_categories'=> $totalCategories,
        'total_offers'    => $totalOffers
    ];

    // Build final response
    $response = [
        'success'        => true,
        'settings'       => $settings,
        'current_country'=> $currentCountry,
        'countries'      => $countriesList,
        'footer_categories' => $categoriesList,
        'footer_brands'  => $brandsList,
        'stats'          => $stats,
        'base_url'       => BASE_URL
    ];

    jsonResponse($response);

} catch (PDOException $e) {
    // Log error in production, don't expose to frontend
    // error_log("Settings API Error: " . $e->getMessage());
    jsonResponse([
        'success' => false,
        'message' => 'Failed to load settings. Please try again.'
    ], 500);
}