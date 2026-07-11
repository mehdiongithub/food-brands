<?php
// Load config and functions
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database-config.php';

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed. Use POST.'], 405);
}

try {
    $db = getDB();

    // Get and validate country_id
    $countryId = getInput('country_id');

    if ($countryId === null || $countryId === '') {
        jsonResponse(['success' => false, 'message' => 'Country ID is required.'], 400);
    }

    $countryId = (int) $countryId;

    if ($countryId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Invalid Country ID.'], 400);
    }

    // Check if country exists and is active
    $stmt = $db->prepare("
        SELECT id, name, code, currency, currency_symbol, flag, slug
        FROM countries
        WHERE id = ? AND status = 1
        LIMIT 1
    ");
    $stmt->execute([$countryId]);
    $country = $stmt->fetch();

    if (!$country) {
        jsonResponse([
            'success' => false,
            'message' => 'Country not found or is currently unavailable.'
        ], 404);
    }

    // Get the old country info before switching (for comparison)
    $oldCountryId = (int) $_SESSION['country_id'];
    $isSame = ($oldCountryId === $countryId);

    // Update session country
    $_SESSION['country_id'] = $countryId;

    // Clear settings cache so footer/data refreshes with new country context
    // (prices and availability may change per country)
    unset($_SESSION['site_settings']);

    // Build response data
    $response = [
        'success'     => true,
        'message'     => $isSame ? 'Already selected this country.' : 'Country changed successfully.',
        'changed'     => !$isSame,
        'country'     => [
            'id'              => (int) $country['id'],
            'name'            => $country['name'],
            'code'            => $country['code'],
            'currency'        => $country['currency'],
            'currency_symbol' => $country['currency_symbol'],
            'slug'            => $country['slug'],
            'flag_url'        => asset_url($country['flag'])
        ],
        'old_country_id' => $oldCountryId
    ];

    // If country actually changed, provide quick summary data
    // so the frontend can update prices without a full page reload
    if (!$isSame) {
        // Count brands available in this country
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT bc.brand_id) AS total
            FROM brand_country bc
            INNER JOIN brands b ON b.id = bc.brand_id AND b.status = 1
            WHERE bc.country_id = ?
        ");
        $stmt->execute([$countryId]);
        $brandCount = (int) $stmt->fetchColumn();

        // Count products with prices in this country
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT pp.product_id) AS total
            FROM product_prices pp
            INNER JOIN products p ON p.id = pp.product_id AND p.status = 1
            WHERE pp.country_id = ? AND pp.status = 1
        ");
        $stmt->execute([$countryId]);
        $productCount = (int) $stmt->fetchColumn();

        // Count active offers in this country
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT oc.offer_id) AS total
            FROM offer_countries oc
            INNER JOIN offers o ON o.id = oc.offer_id AND o.status = 1
            WHERE oc.country_id = ?
              AND o.start_date <= CURDATE()
              AND o.end_date >= CURDATE()
        ");
        $stmt->execute([$countryId]);
        $offerCount = (int) $stmt->fetchColumn();

        $response['summary'] = [
            'brands_available'   => $brandCount,
            'products_available' => $productCount,
            'active_offers'      => $offerCount
        ];
    }

    jsonResponse($response);

} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Failed to change country. Please try again.'
    ], 500);
}