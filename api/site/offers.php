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
    $countryId = (int) $_SESSION['country_id'];
    $action = getInput('action', 'list'); // list, detail, slug-check, home-featured

    // ============================================================
    // ACTION: Check if slug exists
    // ============================================================
    if ($action === 'slug-check') {
        $slug = getInput('slug');
        $excludeId = (int) getInput('exclude_id', 0);

        if (empty($slug)) {
            jsonResponse(['success' => false, 'message' => 'Slug is required'], 400);
        }

        if ($excludeId > 0) {
            $stmt = $db->prepare("SELECT id FROM offers WHERE slug = ? AND id != ? AND status = 1 LIMIT 1");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $db->prepare("SELECT id FROM offers WHERE slug = ? AND status = 1 LIMIT 1");
            $stmt->execute([$slug]);
        }

        $exists = $stmt->fetch() ? true : false;
        jsonResponse(['success' => true, 'exists' => $exists]);
    }

    // ============================================================
    // ACTION: Home featured offers (active, current country, highest discount)
    // ============================================================
    if ($action === 'home-featured') {
        $limit = (int) getInput('limit', 6);
        if ($limit > 20) $limit = 20;
        if ($limit < 1) $limit = 6;

        $stmt = $db->prepare("
            SELECT o.id, o.title, o.slug, o.description, o.discount_percent, o.coupon_code,
                   o.start_date, o.end_date, o.image,
                   b.id AS brand_id, b.name AS brand_name, b.slug AS brand_slug, b.logo AS brand_logo
            FROM offers o
            INNER JOIN brands b ON b.id = o.brand_id AND b.status = 1
            INNER JOIN offer_countries oc ON oc.offer_id = o.id AND oc.country_id = ?
            WHERE o.status = 1
              AND o.start_date <= CURDATE()
              AND o.end_date >= CURDATE()
            ORDER BY o.discount_percent DESC, o.id DESC
            LIMIT ?
        ");
        $stmt->execute([$countryId, $limit]);
        $rows = $stmt->fetchAll();

        $offers = [];
        foreach ($rows as $r) {
            // Count products for this brand in current country
            $stmt2 = $db->prepare("
                SELECT COUNT(DISTINCT p.id) AS total
                FROM products p
                INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
                WHERE p.brand_id = ? AND p.status = 1
            ");
            $stmt2->execute([$countryId, (int) $r['brand_id']]);
            $productCount = (int) $stmt2->fetchColumn();

            // Days remaining
            $endDate = new DateTime($r['end_date']);
            $now = new DateTime('today');
            $daysLeft = $now->diff($endDate)->days;
            if ($endDate < $now) $daysLeft = 0;

            $offers[] = [
                'id'               => (int) $r['id'],
                'title'            => $r['title'],
                'slug'             => $r['slug'],
                'description'      => $r['description'],
                'discount_percent' => (float) $r['discount_percent'],
                'coupon_code'      => $r['coupon_code'],
                'start_date'       => $r['start_date'],
                'end_date'         => $r['end_date'],
                'days_remaining'   => $daysLeft,
                'image'            => asset_url($r['image']),
                'brand'            => [
                    'id'   => (int) $r['brand_id'],
                    'name' => $r['brand_name'],
                    'slug' => $r['brand_slug'],
                    'logo' => asset_url($r['brand_logo']),
                    'url'  => BASE_URL . '/brand/' . $r['brand_slug']
                ],
                'brand_product_count' => $productCount,
                'brand_url'        => BASE_URL . '/brand/' . $r['brand_slug']
            ];
        }

        jsonResponse([
            'success'    => true,
            'offers'     => $offers,
            'total'      => count($offers),
            'country_id' => $countryId
        ]);
    }

    // ============================================================
    // ACTION: Offer detail (single offer by slug)
    // ============================================================
    if ($action === 'detail') {
        $slug = getInput('slug');

        if (empty($slug)) {
            jsonResponse(['success' => false, 'message' => 'Offer slug is required'], 400);
        }

        // Get offer
        $stmt = $db->prepare("
            SELECT o.id, o.title, o.slug, o.description, o.discount_percent, o.coupon_code,
                   o.start_date, o.end_date, o.image, o.brand_id,
                   b.name AS brand_name, b.slug AS brand_slug, b.logo AS brand_logo,
                   b.cover_image AS brand_cover, b.short_description AS brand_description
            FROM offers o
            INNER JOIN brands b ON b.id = o.brand_id AND b.status = 1
            WHERE o.slug = ? AND o.status = 1
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $offer = $stmt->fetch();

        if (!$offer) {
            jsonResponse(['success' => false, 'message' => 'Offer not found'], 404);
        }

        $offerId = (int) $offer['id'];
        $brandId = (int) $offer['brand_id'];

        // Check if offer is currently active
        $isActive = (
            $offer['start_date'] <= date('Y-m-d') &&
            $offer['end_date'] >= date('Y-m-d')
        );

        // Days remaining or expired info
        $endDate = new DateTime($offer['end_date']);
        $startDate = new DateTime($offer['start_date']);
        $now = new DateTime('today');
        $daysLeft = $now->diff($endDate)->days;
        if ($endDate < $now) $daysLeft = 0;
        $totalDays = $startDate->diff($endDate)->days;

        // Countries where this offer is available
        $stmt = $db->prepare("
            SELECT c.id, c.name, c.code, c.currency, c.currency_symbol, c.flag, c.slug
            FROM countries c
            INNER JOIN offer_countries oc ON oc.country_id = c.id
            WHERE oc.offer_id = ? AND c.status = 1
            ORDER BY c.name ASC
        ");
        $stmt->execute([$offerId]);
        $countryRows = $stmt->fetchAll();

        $countries = [];
        foreach ($countryRows as $cr) {
            $countries[] = [
                'id'              => (int) $cr['id'],
                'name'            => $cr['name'],
                'code'            => $cr['code'],
                'currency'        => $cr['currency'],
                'currency_symbol' => $cr['currency_symbol'],
                'slug'            => $cr['slug'],
                'flag_url'        => asset_url($cr['flag']),
                'is_current'      => ((int) $cr['id'] === $countryId) ? true : false
            ];
        }

        // Products from this brand with prices in current country (what the offer applies to)
        $page = max(1, (int) getInput('page', 1));
        $per_page = max(1, min(50, (int) getInput('per_page', 12)));
        $offset = ($page - 1) * $per_page;

        $search = getInput('search');
        $searchSql = '';
        $searchParams = [];

        if ($search) {
            $searchSql = " AND (p.name LIKE ? OR p.short_description LIKE ?) ";
            $searchParams[] = '%' . $search . '%';
            $searchParams[] = '%' . $search . '%';
        }

        $sort = getInput('sort', 'newest');
        $sortSql = " ORDER BY p.id DESC ";
        if ($sort === 'price_low') $sortSql = " ORDER BY price ASC ";
        if ($sort === 'price_high') $sortSql = " ORDER BY price DESC ";
        if ($sort === 'name_asc') $sortSql = " ORDER BY p.name ASC ";
        if ($sort === 'name_desc') $sortSql = " ORDER BY p.name DESC ";

        // Count
        $countParams = array_merge([$countryId, $brandId], $searchParams);
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT p.id) AS total
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            WHERE p.brand_id = ? AND p.status = 1
            $searchSql
        ");
        $stmt->execute($countParams);
        $totalProducts = (int) $stmt->fetchColumn();

        // Fetch
        $currStmt = $db->prepare("SELECT currency_symbol FROM countries WHERE id = ? LIMIT 1");
        $currStmt->execute([$countryId]);
        $currSymbol = $currStmt->fetchColumn() ?: '';

        $fetchParams = array_merge([$countryId, $brandId], $searchParams, [$per_page, $offset]);
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.image, p.short_description, p.calories,
                   p.category_id, c.name AS category_name, c.slug AS category_slug,
                   pp.regular_price, pp.discount_price,
                   COALESCE(pp.discount_price, pp.regular_price) AS price
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.brand_id = ? AND p.status = 1
            $searchSql
            $sortSql
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($fetchParams);
        $productRows = $stmt->fetchAll();

        $products = [];
        foreach ($productRows as $p) {
            $hasDiscount = ($p['discount_price'] !== null && $p['discount_price'] < $p['regular_price']);
            $discPercent = 0;
            if ($hasDiscount && $p['regular_price'] > 0) {
                $discPercent = round((($p['regular_price'] - $p['discount_price']) / $p['regular_price']) * 100);
            }

            // Calculate offer price: apply offer discount on regular price if product doesn't already have a bigger discount
            $offerPrice = null;
            $offerSaved = null;
            $regularPrice = $p['regular_price'] !== null ? (float) $p['regular_price'] : null;
            $productDiscPercent = $discPercent;
            $effectiveDiscPercent = max($productDiscPercent, (float) $offer['discount_percent']);

            if ($regularPrice !== null && $regularPrice > 0) {
                $offerPrice = $regularPrice - ($regularPrice * ($effectiveDiscPercent / 100));
                $offerSaved = $regularPrice - $offerPrice;
            }

            $products[] = [
                'id'                   => (int) $p['id'],
                'name'                 => $p['name'],
                'slug'                 => $p['slug'],
                'image'                => asset_url($p['image']),
                'short_description'    => $p['short_description'],
                'calories'             => (int) $p['calories'],
                'category_name'        => $p['category_name'],
                'category_slug'        => $p['category_slug'],
                'regular_price'        => $regularPrice,
                'discount_price'       => $p['discount_price'] !== null ? (float) $p['discount_price'] : null,
                'has_discount'         => $hasDiscount,
                'product_discount'     => $discPercent,
                'offer_discount'       => (float) $offer['discount_percent'],
                'effective_discount'   => $effectiveDiscPercent,
                'offer_price'          => round($offerPrice, 2),
                'offer_saved'          => round($offerSaved, 2),
                'formatted_regular'    => $regularPrice !== null ? $currSymbol . number_format($regularPrice, 0) : null,
                'formatted_offer_price'=> $offerPrice !== null ? $currSymbol . number_format($offerPrice, 0) : null,
                'formatted_saved'      => $offerSaved > 0 ? $currSymbol . number_format($offerSaved, 0) . ' saved' : null,
                'url'                  => BASE_URL . '/product/' . $p['slug']
            ];
        }

        // Other active offers from the same brand (exclude current)
        $stmt = $db->prepare("
            SELECT o.id, o.title, o.slug, o.discount_percent, o.coupon_code,
                   o.start_date, o.end_date, o.image
            FROM offers o
            INNER JOIN offer_countries oc ON oc.offer_id = o.id AND oc.country_id = ?
            WHERE o.brand_id = ? AND o.id != ? AND o.status = 1
              AND o.start_date <= CURDATE()
              AND o.end_date >= CURDATE()
            ORDER BY o.discount_percent DESC
            LIMIT 4
        ");
        $stmt->execute([$countryId, $brandId, $offerId]);
        $otherOfferRows = $stmt->fetchAll();

        $otherOffers = [];
        foreach ($otherOfferRows as $oo) {
            $ed = new DateTime($oo['end_date']);
            $dl = $now->diff($ed)->days;
            if ($ed < $now) $dl = 0;

            $otherOffers[] = [
                'id'               => (int) $oo['id'],
                'title'            => $oo['title'],
                'slug'             => $oo['slug'],
                'discount_percent' => (float) $oo['discount_percent'],
                'coupon_code'      => $oo['coupon_code'],
                'end_date'         => $oo['end_date'],
                'days_remaining'   => $dl,
                'image'            => asset_url($oo['image'])
            ];
        }

        // Build Schema.org JSON-LD
        $schema = [
            '@context'        => 'https://schema.org',
            '@type'           => 'Offer',
            'name'            => $offer['title'],
            'description'     => strip_tags($offer['description'] ?: ''),
            'url'             => BASE_URL . '/offers/' . $offer['slug'],
            'validFrom'       => $offer['start_date'],
            'validThrough'    => $offer['end_date'],
        ];
        if ($offer['discount_percent'] > 0) {
            $schema['discount'] = $offer['discount_percent'] . '%';
        }

        jsonResponse([
            'success'        => true,
            'offer'          => [
                'id'               => $offerId,
                'title'            => $offer['title'],
                'slug'             => $offer['slug'],
                'description'      => $offer['description'],
                'discount_percent' => (float) $offer['discount_percent'],
                'coupon_code'      => $offer['coupon_code'],
                'start_date'       => $offer['start_date'],
                'end_date'         => $offer['end_date'],
                'is_active'        => $isActive,
                'days_remaining'   => $daysLeft,
                'total_days'       => $totalDays,
                'image'            => asset_url($offer['image']),
                'url'              => BASE_URL . '/offers/' . $offer['slug']
            ],
            'brand'          => [
                'id'                => $brandId,
                'name'              => $offer['brand_name'],
                'slug'              => $offer['brand_slug'],
                'logo'              => asset_url($offer['brand_logo']),
                'cover_image'       => asset_url($offer['brand_cover']),
                'short_description' => $offer['brand_description'],
                'url'               => BASE_URL . '/brand/' . $offer['brand_slug']
            ],
            'countries'      => $countries,
            'products'       => $products,
            'other_offers'   => $otherOffers,
            'pagination'     => [
                'current_page' => $page,
                'per_page'     => $per_page,
                'total_items'  => $totalProducts,
                'total_pages'  => ceil($totalProducts / $per_page) ?: 1,
                'has_next'     => ($page < ceil($totalProducts / $per_page)) ? true : false,
                'has_prev'     => ($page > 1) ? true : false
            ],
            'schema_json'    => json_encode($schema, JSON_UNESCAPED_SLASHES),
            'country_id'     => $countryId
        ]);
    }

    // ============================================================
    // ACTION: List all offers (default - for offers.php page)
    // ============================================================
    // Pagination
    $page = max(1, (int) getInput('page', 1));
    $per_page = max(1, min(50, (int) getInput('per_page', 12)));
    $offset = ($page - 1) * $per_page;

    // Search
    $search = getInput('search');
    $searchSql = '';
    $searchParams = [];

    if ($search) {
        $searchSql = " AND (o.title LIKE ? OR o.description LIKE ? OR b.name LIKE ?) ";
        $searchParams[] = '%' . $search . '%';
        $searchParams[] = '%' . $search . '%';
        $searchParams[] = '%' . $search . '%';
    }

    // Filter by brand
    $brandFilter = getInput('brand_id');
    $brandSql = '';
    $brandParams = [];

    if ($brandFilter) {
        $brandFilter = (int) $brandFilter;
        $brandSql = " AND o.brand_id = ? ";
        $brandParams[] = $brandFilter;
    }

    // Filter: show expired offers too?
    $showExpired = getInput('show_expired', '0');
    $dateSql = " AND o.start_date <= CURDATE() AND o.end_date >= CURDATE() ";
    if ($showExpired === '1' || $showExpired === 'true') {
        $dateSql = ''; // Show all
    }

    // Filter by country
    $countryFilter = getInput('country_id', $countryId);
    $countryFilter = (int) $countryFilter;

    // Sort
    $sort = getInput('sort', 'discount_high');
    $sortSql = " ORDER BY o.discount_percent DESC, o.id DESC ";
    if ($sort === 'discount_low') $sortSql = " ORDER BY o.discount_percent ASC, o.id DESC ";
    if ($sort === 'newest') $sortSql = " ORDER BY o.id DESC ";
    if ($sort === 'oldest') $sortSql = " ORDER BY o.id ASC ";
    if ($sort === 'ending_soon') $sortSql = " ORDER BY o.end_date ASC, o.discount_percent DESC ";
    if ($sort === 'brand_asc') $sortSql = " ORDER BY b.name ASC ";

    // Count total
    $countParams = array_merge($searchParams, $brandParams, [$countryFilter]);
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT o.id) AS total
        FROM offers o
        INNER JOIN brands b ON b.id = o.brand_id AND b.status = 1
        INNER JOIN offer_countries oc ON oc.offer_id = o.id AND oc.country_id = ?
        WHERE o.status = 1
        $searchSql $brandSql $dateSql
    ");
    $stmt->execute($countParams);
    $totalOffers = (int) $stmt->fetchColumn();

    // Fetch offers
    $fetchParams = array_merge($searchParams, $brandParams, [$countryFilter, $per_page, $offset]);
    $stmt = $db->prepare("
        SELECT o.id, o.title, o.slug, o.description, o.discount_percent, o.coupon_code,
               o.start_date, o.end_date, o.image,
               b.id AS brand_id, b.name AS brand_name, b.slug AS brand_slug, b.logo AS brand_logo
        FROM offers o
        INNER JOIN brands b ON b.id = o.brand_id AND b.status = 1
        INNER JOIN offer_countries oc ON oc.offer_id = o.id AND oc.country_id = ?
        WHERE o.status = 1
        $searchSql $brandSql $dateSql
        $sortSql
        LIMIT ? OFFSET ?
    ");
    $stmt->execute($fetchParams);
    $rows = $stmt->fetchAll();

    $now = new DateTime('today');
    $offers = [];

    foreach ($rows as $r) {
        // Days remaining
        $endDate = new DateTime($r['end_date']);
        $daysLeft = $now->diff($endDate)->days;
        if ($endDate < $now) $daysLeft = 0;

        // Status label
        $statusLabel = 'active';
        if ($endDate < $now) {
            $statusLabel = 'expired';
        } elseif ($daysLeft <= 3) {
            $statusLabel = 'ending_soon';
        }

        // Product count for this brand in current country
        $stmt2 = $db->prepare("
            SELECT COUNT(DISTINCT p.id) AS total
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            WHERE p.brand_id = ? AND p.status = 1
        ");
        $stmt2->execute([$countryFilter, (int) $r['brand_id']]);
        $productCount = (int) $stmt2->fetchColumn();

        $offers[] = [
            'id'                 => (int) $r['id'],
            'title'              => $r['title'],
            'slug'               => $r['slug'],
            'description'        => $r['description'],
            'discount_percent'   => (float) $r['discount_percent'],
            'coupon_code'        => $r['coupon_code'],
            'start_date'         => $r['start_date'],
            'end_date'           => $r['end_date'],
            'days_remaining'     => $daysLeft,
            'status_label'       => $statusLabel,
            'image'              => asset_url($r['image']),
            'brand'              => [
                'id'   => (int) $r['brand_id'],
                'name' => $r['brand_name'],
                'slug' => $r['brand_slug'],
                'logo' => asset_url($r['brand_logo']),
                'url'  => BASE_URL . '/brand/' . $r['brand_slug']
            ],
            'brand_product_count'=> $productCount,
            'url'                => BASE_URL . '/offers/' . $r['slug']
        ];
    }

    // Filter data for sidebar
    // Brand filter list (brands that have active offers in current country)
    $stmt = $db->prepare("
        SELECT DISTINCT b.id, b.name, b.slug, b.logo,
               (SELECT COUNT(DISTINCT o2.id)
                FROM offers o2
                INNER JOIN offer_countries oc2 ON oc2.offer_id = o2.id AND oc2.country_id = ?
                WHERE o2.brand_id = b.id AND o2.status = 1
                  AND o2.start_date <= CURDATE() AND o2.end_date >= CURDATE()
               ) AS offer_count
        FROM brands b
        INNER JOIN offers o ON o.brand_id = b.id AND o.status = 1
        INNER JOIN offer_countries oc ON oc.offer_id = o.id AND oc.country_id = ?
        WHERE b.status = 1
          AND o.start_date <= CURDATE() AND o.end_date >= CURDATE()
        ORDER BY b.name ASC
    ");
    $stmt->execute([$countryFilter, $countryFilter]);
    $brandFilterRows = $stmt->fetchAll();

    $filterBrands = [];
    foreach ($brandFilterRows as $fb) {
        $filterBrands[] = [
            'id'          => (int) $fb['id'],
            'name'        => $fb['name'],
            'slug'        => $fb['slug'],
            'logo'        => asset_url($fb['logo']),
            'offer_count' => (int) $fb['offer_count']
        ];
    }

    // Discount range for filter
    $stmt = $db->prepare("
        SELECT MIN(o.discount_percent) AS min_disc, MAX(o.discount_percent) AS max_disc
        FROM offers o
        INNER JOIN offer_countries oc ON oc.offer_id = o.id AND oc.country_id = ?
        WHERE o.status = 1
          AND o.start_date <= CURDATE() AND o.end_date >= CURDATE()
    ");
    $stmt->execute([$countryFilter]);
    $discRange = $stmt->fetch();

    jsonResponse([
        'success'    => true,
        'offers'     => $offers,
        'pagination' => [
            'current_page' => $page,
            'per_page'     => $per_page,
            'total_items'  => $totalOffers,
            'total_pages'  => ceil($totalOffers / $per_page) ?: 1,
            'has_next'     => ($page < ceil($totalOffers / $per_page)) ? true : false,
            'has_prev'     => ($page > 1) ? true : false
        ],
        'filters'    => [
            'brands'          => $filterBrands,
            'discount_min'    => $discRange['min_disc'] !== null ? (float) $discRange['min_disc'] : 0,
            'discount_max'    => $discRange['max_disc'] !== null ? (float) $discRange['max_disc'] : 0,
            'active_search'   => $search,
            'active_brand'    => $brandFilter ? (int) $brandFilter : null,
            'active_country'  => $countryFilter,
            'active_sort'     => $sort,
            'show_expired'    => ($showExpired === '1' || $showExpired === 'true') ? true : false
        ],
        'country_id' => $countryFilter
    ]);

} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Failed to load offers. Please try again.'
    ], 500);
}