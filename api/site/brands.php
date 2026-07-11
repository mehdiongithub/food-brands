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
    $action = getInput('action', 'list'); // list, detail, featured, slug-check

    // ============================================================
    // ACTION: Check if slug exists (for admin or validation)
    // ============================================================
    if ($action === 'slug-check') {
        $slug = getInput('slug');
        $excludeId = (int) getInput('exclude_id', 0);

        if (empty($slug)) {
            jsonResponse(['success' => false, 'message' => 'Slug is required'], 400);
        }

        if ($excludeId > 0) {
            $stmt = $db->prepare("SELECT id FROM brands WHERE slug = ? AND id != ? AND status = 1 LIMIT 1");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $db->prepare("SELECT id FROM brands WHERE slug = ? AND status = 1 LIMIT 1");
            $stmt->execute([$slug]);
        }

        $exists = $stmt->fetch() ? true : false;
        jsonResponse(['success' => true, 'exists' => $exists]);
    }

    // ============================================================
    // ACTION: Featured brands (for home page carousel)
    // ============================================================
    if ($action === 'featured') {
        $limit = (int) getInput('limit', 6);
        if ($limit > 20) $limit = 20;
        if ($limit < 1) $limit = 6;

        // Get brands that have products with prices in current country
        $stmt = $db->prepare("
            SELECT DISTINCT b.id, b.name, b.slug, b.logo, b.cover_image, b.short_description,
                   b.founded_year, b.website
            FROM brands b
            INNER JOIN brand_country bc ON bc.brand_id = b.id
            INNER JOIN countries c ON c.id = bc.country_id AND c.status = 1
            WHERE b.status = 1 AND bc.country_id = ?
            ORDER BY b.id DESC
            LIMIT ?
        ");
        $stmt->execute([$countryId, $limit]);
        $rows = $stmt->fetchAll();

        $brands = [];
        foreach ($rows as $r) {
            // Get product count for this brand in current country
            $stmt2 = $db->prepare("
                SELECT COUNT(DISTINCT p.id) AS total
                FROM products p
                INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
                WHERE p.brand_id = ? AND p.status = 1
            ");
            $stmt2->execute([$countryId, $r['id']]);
            $productCount = (int) $stmt2->fetchColumn();

            // Get min price for this brand in current country
            $stmt3 = $db->prepare("
                SELECT MIN(pp.discount_price) AS min_discount,
                       MIN(pp.regular_price) AS min_regular
                FROM product_prices pp
                INNER JOIN products p ON p.id = pp.product_id AND p.status = 1
                WHERE pp.country_id = ? AND p.brand_id = ? AND pp.status = 1
            ");
            $stmt3->execute([$countryId, $r['id']]);
            $priceInfo = $stmt3->fetch();

            // Get categories for this brand
            $stmt4 = $db->prepare("
                SELECT c.id, c.name, c.slug
                FROM categories c
                INNER JOIN brand_category bc ON bc.category_id = c.id
                WHERE bc.brand_id = ? AND c.status = 1
                ORDER BY c.name ASC
                LIMIT 5
            ");
            $stmt4->execute([$r['id']]);
            $categories = $stmt4->fetchAll();

            // Get country flags for this brand (max 5)
            $stmt5 = $db->prepare("
                SELECT c.flag
                FROM countries c
                INNER JOIN brand_country bc ON bc.country_id = c.id
                WHERE bc.brand_id = ? AND c.status = 1
                ORDER BY c.name ASC
                LIMIT 5
            ");
            $stmt5->execute([$r['id']]);
            $countryFlags = $stmt5->fetchAll();

            $minPrice = null;
            $formattedMinPrice = null;
            if ($priceInfo['min_discount'] !== null) {
                $minPrice = (float) $priceInfo['min_discount'];
            } elseif ($priceInfo['min_regular'] !== null) {
                $minPrice = (float) $priceInfo['min_regular'];
            }

            // Get currency symbol
            $currStmt = $db->prepare("SELECT currency_symbol FROM countries WHERE id = ? LIMIT 1");
            $currStmt->execute([$countryId]);
            $currSymbol = $currStmt->fetchColumn() ?: '';

            if ($minPrice !== null) {
                $formattedMinPrice = $currSymbol . number_format($minPrice, 0);
            }

            $catList = [];
            foreach ($categories as $cat) {
                $catList[] = [
                    'id'   => (int) $cat['id'],
                    'name' => $cat['name'],
                    'slug' => $cat['slug']
                ];
            }

            $flagList = [];
            foreach ($countryFlags as $cf) {
                $flagList[] = asset_url($cf['flag']);
            }

            $brands[] = [
                'id'                  => (int) $r['id'],
                'name'                => $r['name'],
                'slug'                => $r['slug'],
                'logo'                => asset_url($r['logo']),
                'cover_image'         => asset_url($r['cover_image']),
                'short_description'   => $r['short_description'],
                'founded_year'        => $r['founded_year'] ? (int) $r['founded_year'] : null,
                'website'             => $r['website'],
                'product_count'       => $productCount,
                'categories'          => $catList,
                'country_flags'       => $flagList,
                'min_price'           => $minPrice,
                'formatted_min_price' => $formattedMinPrice,
                'url'                 => BASE_URL . '/brand/' . $r['slug']
            ];
        }

        jsonResponse([
            'success'      => true,
            'brands'       => $brands,
            'total'        => count($brands),
            'country_id'   => $countryId
        ]);
    }

    // ============================================================
    // ACTION: Brand detail (single brand by slug)
    // ============================================================
    if ($action === 'detail') {
        $slug = getInput('slug');

        if (empty($slug)) {
            jsonResponse(['success' => false, 'message' => 'Brand slug is required'], 400);
        }

        // Get brand
        $stmt = $db->prepare("
            SELECT id, name, slug, logo, cover_image, short_description, history,
                   website, founded_year, meta_title, meta_description
            FROM brands
            WHERE slug = ? AND status = 1
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $brand = $stmt->fetch();

        if (!$brand) {
            jsonResponse(['success' => false, 'message' => 'Brand not found'], 404);
        }

        $brandId = (int) $brand['id'];

        // Get gallery images
        $stmt = $db->prepare("
            SELECT id, image, sort_order
            FROM brand_gallery
            WHERE brand_id = ?
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$brandId]);
        $galleryRows = $stmt->fetchAll();

        $gallery = [];
        foreach ($galleryRows as $g) {
            $gallery[] = [
                'id'    => (int) $g['id'],
                'image' => asset_url($g['image']),
                'sort'  => (int) $g['sort_order']
            ];
        }

        // Get categories
        $stmt = $db->prepare("
            SELECT c.id, c.name, c.slug
            FROM categories c
            INNER JOIN brand_category bc ON bc.category_id = c.id
            WHERE bc.brand_id = ? AND c.status = 1
            ORDER BY c.name ASC
        ");
        $stmt->execute([$brandId]);
        $catRows = $stmt->fetchAll();

        $categories = [];
        foreach ($catRows as $c) {
            $categories[] = [
                'id'   => (int) $c['id'],
                'name' => $c['name'],
                'slug' => $c['slug'],
                'url'  => BASE_URL . '/category/' . $c['slug']
            ];
        }

        // Get countries
        $stmt = $db->prepare("
            SELECT c.id, c.name, c.code, c.currency, c.currency_symbol, c.flag, c.slug
            FROM countries c
            INNER JOIN brand_country bc ON bc.country_id = c.id
            WHERE bc.brand_id = ? AND c.status = 1
            ORDER BY c.name ASC
        ");
        $stmt->execute([$brandId]);
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

        // Get active offers for this brand in current country
        $stmt = $db->prepare("
            SELECT o.id, o.title, o.slug, o.description, o.discount_percent, o.coupon_code,
                   o.start_date, o.end_date, o.image
            FROM offers o
            INNER JOIN offer_countries oc ON oc.offer_id = o.id AND oc.country_id = ?
            WHERE o.brand_id = ? AND o.status = 1
              AND o.start_date <= CURDATE()
              AND o.end_date >= CURDATE()
            ORDER BY o.discount_percent DESC
        ");
        $stmt->execute([$countryId, $brandId]);
        $offerRows = $stmt->fetchAll();

        $offers = [];
        foreach ($offerRows as $o) {
            $offers[] = [
                'id'              => (int) $o['id'],
                'title'           => $o['title'],
                'slug'            => $o['slug'],
                'description'     => $o['description'],
                'discount_percent'=> (float) $o['discount_percent'],
                'coupon_code'     => $o['coupon_code'],
                'start_date'      => $o['start_date'],
                'end_date'        => $o['end_date'],
                'image'           => asset_url($o['image'])
            ];
        }

        // Get products with prices for current country (with pagination)
        $page = max(1, (int) getInput('page', 1));
        $per_page = max(1, min(50, (int) getInput('per_page', 12)));
        $offset = ($page - 1) * $per_page;

        // Filter by category if provided
        $catFilter = getInput('category_id');
        $catFilterSql = '';
        $catFilterParams = [];

        if ($catFilter) {
            $catFilter = (int) $catFilter;
            $catFilterSql = " AND p.category_id = ? ";
            $catFilterParams[] = $catFilter;
        }

        // Search within brand products
        $search = getInput('search');
        $searchSql = '';
        $searchParams = [];

        if ($search) {
            $searchSql = " AND (p.name LIKE ? OR p.short_description LIKE ?) ";
            $searchParams[] = '%' . $search . '%';
            $searchParams[] = '%' . $search . '%';
        }

        // Sort
        $sort = getInput('sort', 'newest');
        $sortSql = " ORDER BY p.id DESC ";
        if ($sort === 'price_low') $sortSql = " ORDER BY price ASC ";
        if ($sort === 'price_high') $sortSql = " ORDER BY price DESC ";
        if ($sort === 'name_asc') $sortSql = " ORDER BY p.name ASC ";
        if ($sort === 'name_desc') $sortSql = " ORDER BY p.name DESC ";
        if ($sort === 'calories_low') $sortSql = " ORDER BY p.calories ASC ";
        if ($sort === 'calories_high') $sortSql = " ORDER BY p.calories DESC ";

        // Count total products
        $countParams = array_merge([$countryId, $brandId], $catFilterParams, $searchParams);
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT p.id) AS total
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            WHERE p.brand_id = ? AND p.status = 1
            $catFilterSql $searchSql
        ");
        $stmt->execute($countParams);
        $totalProducts = (int) $stmt->fetchColumn();

        // Fetch products with prices
        $fetchParams = array_merge([$countryId, $brandId], $catFilterParams, $searchParams, [$per_page, $offset]);
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.image, p.short_description, p.calories, p.featured,
                   p.category_id, c.name AS category_name, c.slug AS category_slug,
                   pp.regular_price, pp.discount_price, pp.currency AS price_currency,
                   COALESCE(pp.discount_price, pp.regular_price) AS price
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.brand_id = ? AND p.status = 1
            $catFilterSql $searchSql
            $sortSql
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($fetchParams);
        $productRows = $stmt->fetchAll();

        // Get currency symbol
        $currStmt = $db->prepare("SELECT currency_symbol FROM countries WHERE id = ? LIMIT 1");
        $currStmt->execute([$countryId]);
        $currSymbol = $currStmt->fetchColumn() ?: '';

        $products = [];
        foreach ($productRows as $p) {
            $hasDiscount = ($p['discount_price'] !== null && $p['discount_price'] < $p['regular_price']);
            $discPercent = 0;
            if ($hasDiscount && $p['regular_price'] > 0) {
                $discPercent = round((($p['regular_price'] - $p['discount_price']) / $p['regular_price']) * 100);
            }

            $products[] = [
                'id'               => (int) $p['id'],
                'name'             => $p['name'],
                'slug'             => $p['slug'],
                'image'            => asset_url($p['image']),
                'short_description'=> $p['short_description'],
                'calories'         => (int) $p['calories'],
                'featured'         => (bool) $p['featured'],
                'category_id'      => (int) $p['category_id'],
                'category_name'    => $p['category_name'],
                'category_slug'    => $p['category_slug'],
                'regular_price'    => $p['regular_price'] !== null ? (float) $p['regular_price'] : null,
                'discount_price'   => $p['discount_price'] !== null ? (float) $p['discount_price'] : null,
                'has_discount'     => $hasDiscount,
                'discount_percent' => $discPercent,
                'formatted_regular'=> $p['regular_price'] !== null ? $currSymbol . number_format((float) $p['regular_price'], 0) : null,
                'formatted_discount'=> $hasDiscount ? $currSymbol . number_format((float) $p['discount_price'], 0) : null,
                'url'              => BASE_URL . '/product/' . $p['slug']
            ];
        }

        // Build Schema.org JSON-LD
        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Brand',
            'name'        => $brand['name'],
            'url'         => BASE_URL . '/brand/' . $brand['slug'],
            'logo'        => asset_url($brand['logo']),
            'description' => strip_tags($brand['short_description'] ?: ''),
        ];
        if ($brand['founded_year']) {
            $schema['foundingDate'] = $brand['founded_year'] . '-01-01';
        }
        if ($brand['website']) {
            $schema['sameAs'] = [$brand['website']];
        }

        // Total product count across ALL countries (not just current)
        $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE brand_id = ? AND status = 1");
        $stmt->execute([$brandId]);
        $allProductCount = (int) $stmt->fetchColumn();

        jsonResponse([
            'success'        => true,
            'brand'          => [
                'id'                => $brandId,
                'name'              => $brand['name'],
                'slug'              => $brand['slug'],
                'logo'              => asset_url($brand['logo']),
                'cover_image'       => asset_url($brand['cover_image']),
                'short_description' => $brand['short_description'],
                'history'           => $brand['history'],
                'website'           => $brand['website'],
                'founded_year'      => $brand['founded_year'] ? (int) $brand['founded_year'] : null,
                'meta_title'        => $brand['meta_title'],
                'meta_description'  => $brand['meta_description'],
                'total_products'    => $allProductCount,
                'url'               => BASE_URL . '/brand/' . $brand['slug']
            ],
            'gallery'        => $gallery,
            'categories'     => $categories,
            'countries'      => $countries,
            'offers'         => $offers,
            'products'       => $products,
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
    // ACTION: List all brands (default - for brands.php page)
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
        $searchSql = " AND (b.name LIKE ? OR b.short_description LIKE ?) ";
        $searchParams[] = '%' . $search . '%';
        $searchParams[] = '%' . $search . '%';
    }

    // Filter by category
    $catFilter = getInput('category_id');
    $catFilterSql = '';
    $catFilterParams = [];

    if ($catFilter) {
        $catFilter = (int) $catFilter;
        $catFilterSql = " AND b.id IN (SELECT brand_id FROM brand_category WHERE category_id = ?) ";
        $catFilterParams[] = $catFilter;
    }

    // Filter by country (only brands available in selected country)
    $countryFilter = getInput('country_id');
    $countryFilterSql = '';
    $countryFilterParams = [];

    if ($countryFilter) {
        $countryFilter = (int) $countryFilter;
    } else {
        // Default: filter by session country
        $countryFilter = $countryId;
    }
    $countryFilterSql = " AND b.id IN (SELECT brand_id FROM brand_country WHERE country_id = ?) ";
    $countryFilterParams[] = $countryFilter;

    // Sort
    $sort = getInput('sort', 'newest');
    $sortSql = " ORDER BY b.id DESC ";
    if ($sort === 'name_asc') $sortSql = " ORDER BY b.name ASC ";
    if ($sort === 'name_desc') $sortSql = " ORDER BY b.name DESC ";
    if ($sort === 'oldest') $sortSql = " ORDER BY b.id ASC ";
    if ($sort === 'products_high') $sortSql = " ORDER BY product_count DESC ";
    if ($sort === 'products_low') $sortSql = " ORDER BY product_count ASC ";

    // Count total
    $countParams = array_merge($searchParams, $catFilterParams, $countryFilterParams);
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT b.id) AS total
        FROM brands b
        WHERE b.status = 1
        $searchSql $catFilterSql $countryFilterSql
    ");
    $stmt->execute($countParams);
    $totalBrands = (int) $stmt->fetchColumn();

    // Fetch brands
    $fetchParams = array_merge($searchParams, $catFilterParams, $countryFilterParams, [$per_page, $offset]);
    $stmt = $db->prepare("
        SELECT b.id, b.name, b.slug, b.logo, b.cover_image, b.short_description, b.founded_year,
               (SELECT COUNT(DISTINCT p.id)
                FROM products p
                INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
                WHERE p.brand_id = b.id AND p.status = 1
               ) AS product_count,
               (SELECT MIN(COALESCE(pp2.discount_price, pp2.regular_price))
                FROM product_prices pp2
                INNER JOIN products p2 ON p2.id = pp2.product_id AND p2.status = 1
                WHERE pp2.country_id = ? AND p2.brand_id = b.id AND pp2.status = 1
               ) AS min_price
        FROM brands b
        WHERE b.status = 1
        $searchSql $catFilterSql $countryFilterSql
        GROUP BY b.id
        $sortSql
        LIMIT ? OFFSET ?
    ");
    // Add country_id twice for the two subqueries, then limit/offset
    $fetchParams = array_merge($searchParams, $catFilterParams, $countryFilterParams, [$countryId, $countryId, $per_page, $offset]);
    $stmt->execute($fetchParams);
    $rows = $stmt->fetchAll();

    // Get currency symbol
    $currStmt = $db->prepare("SELECT currency_symbol FROM countries WHERE id = ? LIMIT 1");
    $currStmt->execute([$countryId]);
    $currSymbol = $currStmt->fetchColumn() ?: '';

    $brands = [];
    foreach ($rows as $r) {
        // Get categories for each brand (limit 3)
        $stmt2 = $db->prepare("
            SELECT c.id, c.name, c.slug
            FROM categories c
            INNER JOIN brand_category bc ON bc.category_id = c.id
            WHERE bc.brand_id = ? AND c.status = 1
            ORDER BY c.name ASC
            LIMIT 3
        ");
        $stmt2->execute([(int) $r['id']]);
        $catRows = $stmt2->fetchAll();

        $catList = [];
        foreach ($catRows as $c) {
            $catList[] = ['id' => (int) $c['id'], 'name' => $c['name'], 'slug' => $c['slug']];
        }

        $minPrice = $r['min_price'] !== null ? (float) $r['min_price'] : null;
        $formattedMinPrice = $minPrice !== null ? $currSymbol . number_format($minPrice, 0) : null;

        $brands[] = [
            'id'                  => (int) $r['id'],
            'name'                => $r['name'],
            'slug'                => $r['slug'],
            'logo'                => asset_url($r['logo']),
            'cover_image'         => asset_url($r['cover_image']),
            'short_description'   => $r['short_description'],
            'founded_year'        => $r['founded_year'] ? (int) $r['founded_year'] : null,
            'product_count'       => (int) $r['product_count'],
            'categories'          => $catList,
            'min_price'           => $minPrice,
            'formatted_min_price' => $formattedMinPrice,
            'url'                 => BASE_URL . '/brand/' . $r['slug']
        ];
    }

    // Get available categories for filter sidebar
    $stmt = $db->query("
        SELECT c.id, c.name, c.slug,
               (SELECT COUNT(DISTINCT bc.brand_id) FROM brand_category bc WHERE bc.category_id = c.id) AS brand_count
        FROM categories c
        WHERE c.status = 1
        ORDER BY c.name ASC
    ");
    $allCategories = $stmt->fetchAll();

    $filterCategories = [];
    foreach ($allCategories as $fc) {
        $filterCategories[] = [
            'id'          => (int) $fc['id'],
            'name'        => $fc['name'],
            'slug'        => $fc['slug'],
            'brand_count' => (int) $fc['brand_count']
        ];
    }

    // Get available countries for filter sidebar
    $stmt = $db->query("SELECT id, name, currency_symbol, flag FROM countries WHERE status = 1 ORDER BY name ASC");
    $allCountries = $stmt->fetchAll();

    $filterCountries = [];
    foreach ($allCountries as $fc2) {
        $filterCountries[] = [
            'id'              => (int) $fc2['id'],
            'name'            => $fc2['name'],
            'currency_symbol' => $fc2['currency_symbol'],
            'flag_url'        => asset_url($fc2['flag']),
            'is_current'      => ((int) $fc2['id'] === $countryId) ? true : false
        ];
    }

    jsonResponse([
        'success'           => true,
        'brands'            => $brands,
        'pagination'        => [
            'current_page' => $page,
            'per_page'     => $per_page,
            'total_items'  => $totalBrands,
            'total_pages'  => ceil($totalBrands / $per_page) ?: 1,
            'has_next'     => ($page < ceil($totalBrands / $per_page)) ? true : false,
            'has_prev'     => ($page > 1) ? true : false
        ],
        'filters'           => [
            'categories'   => $filterCategories,
            'countries'    => $filterCountries,
            'active_search'=> $search,
            'active_category'=> $catFilter ? (int) $catFilter : null,
            'active_country'=> $countryFilter,
            'active_sort'  => $sort
        ],
        'country_id'        => $countryId
    ]);

} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Failed to load brands. Please try again.'
    ], 500);
}