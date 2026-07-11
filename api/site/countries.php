<?php
// Load config and functions
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

    // Optional filters
    $brandId = getInput('brand_id');
    $productId = getInput('product_id');
    $offerId = getInput('offer_id');

    // -------------------------------------------------------
    // CASE 1: Get countries for a specific brand
    // -------------------------------------------------------
    if ($brandId) {
        $brandId = (int) $brandId;

        // Verify brand exists
        $stmt = $db->prepare("SELECT id, name, slug FROM brands WHERE id = ? AND status = 1");
        $stmt->execute([$brandId]);
        $brand = $stmt->fetch();

        if (!$brand) {
            jsonResponse(['success' => false, 'message' => 'Brand not found'], 404);
        }

        // Get countries where this brand is available
        $stmt = $db->prepare("
            SELECT c.id, c.name, c.code, c.currency, c.currency_symbol, c.flag, c.slug
            FROM countries c
            INNER JOIN brand_country bc ON bc.country_id = c.id
            WHERE bc.brand_id = ? AND c.status = 1
            ORDER BY c.name ASC
        ");
        $stmt->execute([$brandId]);
        $rows = $stmt->fetchAll();

        $countries = [];
        foreach ($rows as $r) {
            $countries[] = [
                'id'              => (int) $r['id'],
                'name'            => $r['name'],
                'code'            => $r['code'],
                'currency'        => $r['currency'],
                'currency_symbol' => $r['currency_symbol'],
                'slug'            => $r['slug'],
                'flag_url'        => asset_url($r['flag'])
            ];
        }

        jsonResponse([
            'success'  => true,
            'brand'    => [
                'id'   => (int) $brand['id'],
                'name' => $brand['name'],
                'slug' => $brand['slug']
            ],
            'countries' => $countries,
            'total'    => count($countries)
        ]);
    }

    // -------------------------------------------------------
    // CASE 2: Get countries for a specific product (via brand)
    // -------------------------------------------------------
    if ($productId) {
        $productId = (int) $productId;

        // Verify product exists and get its brand
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.brand_id, b.name AS brand_name, b.slug AS brand_slug
            FROM products p
            INNER JOIN brands b ON b.id = p.brand_id
            WHERE p.id = ? AND p.status = 1 AND b.status = 1
        ");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
        }

        // Get countries that have pricing for this product
        $stmt = $db->prepare("
            SELECT c.id, c.name, c.code, c.currency, c.currency_symbol, c.flag, c.slug,
                   pp.regular_price, pp.discount_price, pp.currency AS price_currency
            FROM countries c
            INNER JOIN product_prices pp ON pp.country_id = c.id AND pp.product_id = ?
            WHERE c.status = 1 AND pp.status = 1
            ORDER BY c.name ASC
        ");
        $stmt->execute([$productId]);
        $rows = $stmt->fetchAll();

        $countries = [];
        foreach ($rows as $r) {
            $hasDiscount = ($r['discount_price'] !== null && $r['discount_price'] < $r['regular_price']);
            $countries[] = [
                'id'               => (int) $r['id'],
                'name'             => $r['name'],
                'code'             => $r['code'],
                'currency'         => $r['currency'],
                'currency_symbol'  => $r['currency_symbol'],
                'slug'             => $r['slug'],
                'flag_url'         => asset_url($r['flag']),
                'regular_price'    => $r['regular_price'] !== null ? (float) $r['regular_price'] : null,
                'discount_price'   => $r['discount_price'] !== null ? (float) $r['discount_price'] : null,
                'has_discount'     => $hasDiscount,
                'formatted_regular'=> $r['regular_price'] !== null ? $r['currency_symbol'] . number_format((float) $r['regular_price'], 0) : null,
                'formatted_discount'=> $hasDiscount ? $r['currency_symbol'] . number_format((float) $r['discount_price'], 0) : null
            ];
        }

        jsonResponse([
            'success'  => true,
            'product'  => [
                'id'         => (int) $product['id'],
                'name'       => $product['name'],
                'slug'       => $product['slug'],
                'brand_id'   => (int) $product['brand_id'],
                'brand_name' => $product['brand_name'],
                'brand_slug' => $product['brand_slug']
            ],
            'countries' => $countries,
            'total'     => count($countries)
        ]);
    }

    // -------------------------------------------------------
    // CASE 3: Get countries for a specific offer
    // -------------------------------------------------------
    if ($offerId) {
        $offerId = (int) $offerId;

        $stmt = $db->prepare("
            SELECT o.id, o.title, o.slug, b.name AS brand_name, b.slug AS brand_slug, b.logo
            FROM offers o
            INNER JOIN brands b ON b.id = o.brand_id
            WHERE o.id = ? AND o.status = 1
        ");
        $stmt->execute([$offerId]);
        $offer = $stmt->fetch();

        if (!$offer) {
            jsonResponse(['success' => false, 'message' => 'Offer not found'], 404);
        }

        $stmt = $db->prepare("
            SELECT c.id, c.name, c.code, c.currency, c.currency_symbol, c.flag, c.slug
            FROM countries c
            INNER JOIN offer_countries oc ON oc.country_id = c.id
            WHERE oc.offer_id = ? AND c.status = 1
            ORDER BY c.name ASC
        ");
        $stmt->execute([$offerId]);
        $rows = $stmt->fetchAll();

        $countries = [];
        foreach ($rows as $r) {
            $countries[] = [
                'id'              => (int) $r['id'],
                'name'            => $r['name'],
                'code'            => $r['code'],
                'currency'        => $r['currency'],
                'currency_symbol' => $r['currency_symbol'],
                'slug'            => $r['slug'],
                'flag_url'        => asset_url($r['flag'])
            ];
        }

        jsonResponse([
            'success'  => true,
            'offer'    => [
                'id'         => (int) $offer['id'],
                'title'      => $offer['title'],
                'slug'       => $offer['slug'],
                'brand_name' => $offer['brand_name'],
                'brand_slug' => $offer['brand_slug'],
                'brand_logo' => asset_url($offer['logo'])
            ],
            'countries' => $countries,
            'total'     => count($countries)
        ]);
    }

    // -------------------------------------------------------
    // CASE 4: Get all active countries (default - no filters)
    // -------------------------------------------------------
    $stmt = $db->query("
        SELECT id, name, code, currency, currency_symbol, flag, slug
        FROM countries
        WHERE status = 1
        ORDER BY name ASC
    ");
    $rows = $stmt->fetchAll();

    $currentCountryId = (int) $_SESSION['country_id'];
    $countries = [];

    foreach ($rows as $r) {
        $countries[] = [
            'id'              => (int) $r['id'],
            'name'            => $r['name'],
            'code'            => $r['code'],
            'currency'        => $r['currency'],
            'currency_symbol' => $r['currency_symbol'],
            'slug'            => $r['slug'],
            'flag_url'        => asset_url($r['flag']),
            'is_current'      => ((int) $r['id'] === $currentCountryId) ? true : false
        ];
    }

    jsonResponse([
        'success'        => true,
        'countries'      => $countries,
        'current_id'     => $currentCountryId,
        'total'          => count($countries)
    ]);

} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Failed to load countries. Please try again.'
    ], 500);
}