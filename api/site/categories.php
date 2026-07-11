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
    $action = getInput('action', 'list'); // list, detail, slug-check

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
            $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ? AND id != ? AND status = 1 LIMIT 1");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ? AND status = 1 LIMIT 1");
            $stmt->execute([$slug]);
        }

        $exists = $stmt->fetch() ? true : false;
        jsonResponse(['success' => true, 'exists' => $exists]);
    }

    // ============================================================
    // ACTION: Category detail (single category by slug)
    // ============================================================
    if ($action === 'detail') {
        $slug = getInput('slug');

        if (empty($slug)) {
            jsonResponse(['success' => false, 'message' => 'Category slug is required'], 400);
        }

        // Get category
        $stmt = $db->prepare("
            SELECT id, name, slug, image, description
            FROM categories
            WHERE slug = ? AND status = 1
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $category = $stmt->fetch();

        if (!$category) {
            jsonResponse(['success' => false, 'message' => 'Category not found'], 404);
        }

        $categoryId = (int) $category['id'];

        // Get brands in this category that are available in current country
        $stmt = $db->prepare("
            SELECT DISTINCT b.id, b.name, b.slug, b.logo, b.short_description
            FROM brands b
            INNER JOIN brand_category bc ON bc.brand_id = b.id AND bc.category_id = ?
            INNER JOIN brand_country bcr ON bcr.brand_id = b.id AND bcr.country_id = ?
            WHERE b.status = 1
            ORDER BY b.name ASC
        ");
        $stmt->execute([$categoryId, $countryId]);
        $brandRows = $stmt->fetchAll();

        $brands = [];
        foreach ($brandRows as $br) {
            // Product count for this brand in this category + current country
            $stmt2 = $db->prepare("
                SELECT COUNT(DISTINCT p.id) AS total
                FROM products p
                INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
                WHERE p.brand_id = ? AND p.category_id = ? AND p.status = 1
            ");
            $stmt2->execute([$countryId, (int) $br['id'], $categoryId]);
            $pCount = (int) $stmt2->fetchColumn();

            // Min price for this brand in this category + current country
            $stmt3 = $db->prepare("
                SELECT MIN(COALESCE(pp.discount_price, pp.regular_price)) AS min_price
                FROM product_prices pp
                INNER JOIN products p ON p.id = pp.product_id AND p.status = 1 AND p.category_id = ?
                WHERE pp.country_id = ? AND p.brand_id = ? AND pp.status = 1
            ");
            $stmt3->execute([$categoryId, $countryId, (int) $br['id']]);
            $priceInfo = $stmt3->fetch();

            $minPrice = $priceInfo['min_price'] !== null ? (float) $priceInfo['min_price'] : null;

            $currStmt = $db->prepare("SELECT currency_symbol FROM countries WHERE id = ? LIMIT 1");
            $currStmt->execute([$countryId]);
            $currSymbol = $currStmt->fetchColumn() ?: '';

            $brands[] = [
                'id'                  => (int) $br['id'],
                'name'                => $br['name'],
                'slug'                => $br['slug'],
                'logo'                => asset_url($br['logo']),
                'short_description'   => $br['short_description'],
                'product_count'       => $pCount,
                'min_price'           => $minPrice,
                'formatted_min_price' => $minPrice !== null ? $currSymbol . number_format($minPrice, 0) : null,
                'url'                 => BASE_URL . '/brand/' . $br['slug']
            ];
        }

        // Get products with prices in current country (with pagination)
        $page = max(1, (int) getInput('page', 1));
        $per_page = max(1, min(50, (int) getInput('per_page', 12)));
        $offset = ($page - 1) * $per_page;

        // Filter by brand within this category
        $brandFilter = getInput('brand_id');
        $brandSql = '';
        $brandParams = [];

        if ($brandFilter) {
            $brandFilter = (int) $brandFilter;
            $brandSql = " AND p.brand_id = ? ";
            $brandParams[] = $brandFilter;
        }

        // Search within this category
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
        $countParams = array_merge([$countryId, $categoryId], $brandParams, $searchParams);
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT p.id) AS total
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            WHERE p.category_id = ? AND p.status = 1
            $brandSql $searchSql
        ");
        $stmt->execute($countParams);
        $totalProducts = (int) $stmt->fetchColumn();

        // Fetch products
        $fetchParams = array_merge([$countryId, $categoryId], $brandParams, $searchParams, [$per_page, $offset]);
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.image, p.short_description, p.calories, p.featured,
                   p.brand_id,
                   b.name AS brand_name, b.slug AS brand_slug, b.logo AS brand_logo,
                   pp.regular_price, pp.discount_price, pp.currency AS price_currency,
                   COALESCE(pp.discount_price, pp.regular_price) AS price
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            INNER JOIN brands b ON b.id = p.brand_id AND b.status = 1
            WHERE p.category_id = ? AND p.status = 1
            $brandSql $searchSql
            $sortSql
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($fetchParams);
        $productRows = $stmt->fetchAll();

        // Currency symbol
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
                'id'                => (int) $p['id'],
                'name'              => $p['name'],
                'slug'              => $p['slug'],
                'image'             => asset_url($p['image']),
                'short_description' => $p['short_description'],
                'calories'          => (int) $p['calories'],
                'featured'          => (bool) $p['featured'],
                'brand_id'          => (int) $p['brand_id'],
                'brand_name'        => $p['brand_name'],
                'brand_slug'        => $p['brand_slug'],
                'brand_logo'        => asset_url($p['brand_logo']),
                'regular_price'     => $p['regular_price'] !== null ? (float) $p['regular_price'] : null,
                'discount_price'    => $p['discount_price'] !== null ? (float) $p['discount_price'] : null,
                'has_discount'      => $hasDiscount,
                'discount_percent'  => $discPercent,
                'formatted_regular' => $p['regular_price'] !== null ? $currSymbol . number_format((float) $p['regular_price'], 0) : null,
                'formatted_discount'=> $hasDiscount ? $currSymbol . number_format((float) $p['discount_price'], 0) : null,
                'url'               => BASE_URL . '/product/' . $p['slug'],
                'brand_url'         => BASE_URL . '/brand/' . $p['brand_slug']
            ];
        }

        // Total product count across ALL countries for this category
        $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status = 1");
        $stmt->execute([$categoryId]);
        $allProductCount = (int) $stmt->fetchColumn();

        // Total brand count for this category
        $stmt = $db->prepare("SELECT COUNT(DISTINCT brand_id) FROM brand_category WHERE category_id = ?");
        $stmt->execute([$categoryId]);
        $allBrandCount = (int) $stmt->fetchColumn();

        // Build Schema.org JSON-LD
        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'CollectionPage',
            'name'        => $category['name'] . ' Food Menu & Prices',
            'description' => strip_tags($category['description'] ?: 'Browse all ' . $category['name'] . ' food items with prices and nutrition info.'),
            'url'         => BASE_URL . '/category/' . $category['slug'],
        ];

        jsonResponse([
            'success'        => true,
            'category'       => [
                'id'              => $categoryId,
                'name'            => $category['name'],
                'slug'            => $category['slug'],
                'image'           => asset_url($category['image']),
                'description'     => $category['description'],
                'total_products'  => $allProductCount,
                'total_brands'    => $allBrandCount,
                'url'             => BASE_URL . '/category/' . $category['slug']
            ],
            'brands'         => $brands,
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
    // ACTION: List all categories (default - for categories.php page)
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
        $searchSql = " AND (c.name LIKE ? OR c.description LIKE ?) ";
        $searchParams[] = '%' . $search . '%';
        $searchParams[] = '%' . $search . '%';
    }

    // Sort
    $sort = getInput('sort', 'sort_order');
    $sortSql = " ORDER BY c.sort_order ASC, c.id ASC ";
    if ($sort === 'name_asc') $sortSql = " ORDER BY c.name ASC ";
    if ($sort === 'name_desc') $sortSql = " ORDER BY c.name DESC ";
    if ($sort === 'products_high') $sortSql = " ORDER BY product_count DESC ";
    if ($sort === 'products_low') $sortSql = " ORDER BY product_count ASC ";
    if ($sort === 'brands_high') $sortSql = " ORDER BY brand_count DESC ";
    if ($sort === 'newest') $sortSql = " ORDER BY c.id DESC ";

    // Count total categories
    $countParams = array_merge($searchParams);
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT c.id) AS total
        FROM categories c
        WHERE c.status = 1
        $searchSql
    ");
    $stmt->execute($countParams);
    $totalCategories = (int) $stmt->fetchColumn();

    // Fetch categories with product/brand counts for current country
    $fetchParams = array_merge($searchParams, [$countryId, $countryId, $per_page, $offset]);
    $stmt = $db->prepare("
        SELECT c.id, c.name, c.slug, c.image, c.description, c.sort_order,
               (SELECT COUNT(DISTINCT p.id)
                FROM products p
                INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
                WHERE p.category_id = c.id AND p.status = 1
               ) AS product_count,
               (SELECT COUNT(DISTINCT bc.brand_id)
                FROM brand_category bc
                INNER JOIN brand_country bcr ON bcr.brand_id = bc.brand_id AND bcr.country_id = ?
                WHERE bc.category_id = c.id
               ) AS brand_count
        FROM categories c
        WHERE c.status = 1
        $searchSql
        GROUP BY c.id
        $sortSql
        LIMIT ? OFFSET ?
    ");
    $stmt->execute($fetchParams);
    $rows = $stmt->fetchAll();

    // Currency symbol
    $currStmt = $db->prepare("SELECT currency_symbol FROM countries WHERE id = ? LIMIT 1");
    $currStmt->execute([$countryId]);
    $currSymbol = $currStmt->fetchColumn() ?: '';

    $categories = [];
    foreach ($rows as $r) {
        // Get min price across all products in this category for current country
        $stmt2 = $db->prepare("
            SELECT MIN(COALESCE(pp.discount_price, pp.regular_price)) AS min_price,
                   MAX(COALESCE(pp.discount_price, pp.regular_price)) AS max_price
            FROM product_prices pp
            INNER JOIN products p ON p.id = pp.product_id AND p.status = 1 AND p.category_id = ?
            WHERE pp.country_id = ? AND pp.status = 1
        ");
        $stmt2->execute([(int) $r['id'], $countryId]);
        $priceInfo = $stmt2->fetch();

        $minPrice = $priceInfo['min_price'] !== null ? (float) $priceInfo['min_price'] : null;
        $maxPrice = $priceInfo['max_price'] !== null ? (float) $priceInfo['max_price'] : null;

        // Get top brands in this category (limit 3)
        $stmt3 = $db->prepare("
            SELECT DISTINCT b.id, b.name, b.slug, b.logo
            FROM brands b
            INNER JOIN brand_category bc ON bc.brand_id = b.id AND bc.category_id = ?
            INNER JOIN brand_country bcr ON bcr.brand_id = b.id AND bcr.country_id = ?
            WHERE b.status = 1
            ORDER BY b.name ASC
            LIMIT 3
        ");
        $stmt3->execute([(int) $r['id'], $countryId]);
        $topBrandRows = $stmt3->fetchAll();

        $topBrands = [];
        foreach ($topBrandRows as $tb) {
            $topBrands[] = [
                'id'   => (int) $tb['id'],
                'name' => $tb['name'],
                'slug' => $tb['slug'],
                'logo' => asset_url($tb['logo']),
                'url'  => BASE_URL . '/brand/' . $tb['slug']
            ];
        }

        // Get a sample product image for the category card
        $stmt4 = $db->prepare("
            SELECT p.image
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            WHERE p.category_id = ? AND p.status = 1 AND p.image IS NOT NULL AND p.image != ''
            ORDER BY p.featured DESC, RAND()
            LIMIT 1
        ");
        $stmt4->execute([$countryId, (int) $r['id']]);
        $sampleProduct = $stmt4->fetch();

        $cardImage = asset_url($r['image']);
        if (empty($r['image']) && $sampleProduct) {
            $cardImage = asset_url($sampleProduct['image']);
        }

        $categories[] = [
            'id'                  => (int) $r['id'],
            'name'                => $r['name'],
            'slug'                => $r['slug'],
            'image'               => $cardImage,
            'description'         => $r['description'],
            'sort_order'          => (int) $r['sort_order'],
            'product_count'       => (int) $r['product_count'],
            'brand_count'         => (int) $r['brand_count'],
            'top_brands'          => $topBrands,
            'min_price'           => $minPrice,
            'max_price'           => $maxPrice,
            'formatted_min_price' => $minPrice !== null ? $currSymbol . number_format($minPrice, 0) : null,
            'formatted_max_price' => $maxPrice !== null ? $currSymbol . number_format($maxPrice, 0) : null,
            'url'                 => BASE_URL . '/category/' . $r['slug']
        ];
    }

    jsonResponse([
        'success'    => true,
        'categories' => $categories,
        'pagination' => [
            'current_page' => $page,
            'per_page'     => $per_page,
            'total_items'  => $totalCategories,
            'total_pages'  => ceil($totalCategories / $per_page) ?: 1,
            'has_next'     => ($page < ceil($totalCategories / $per_page)) ? true : false,
            'has_prev'     => ($page > 1) ? true : false
        ],
        'country_id' => $countryId
    ]);

} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Failed to load categories. Please try again.'
    ], 500);
}