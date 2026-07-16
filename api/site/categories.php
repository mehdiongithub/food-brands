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

        // Get category (including parent_id so we know if this is a
        // top-level category or a child category)
        $stmt = $db->prepare("
            SELECT id, parent_id, name, slug, image, description
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

        // If this is a top-level (parent) category, pull in its children
        // (e.g. Pizza -> Small/Medium/Large Pizza). Products get grouped
        // under a heading per child on the frontend. A child/leaf category
        // has no children of its own, so it just behaves like a normal
        // single-category page.
        $childCategories = ($category['parent_id'] === null) ? getCategoryChildren($db, $categoryId) : [];
        $scopeCategoryIds = array_merge([$categoryId], array_map(function ($c) { return (int) $c['id']; }, $childCategories));
        $scopePlaceholders = implode(',', array_fill(0, count($scopeCategoryIds), '?'));

        // Get brands in this category (+ children) that are available in current country
        $stmt = $db->prepare("
            SELECT DISTINCT b.id, b.name, b.slug, b.logo, b.short_description
            FROM brands b
            INNER JOIN brand_category bc ON bc.brand_id = b.id AND bc.category_id IN ($scopePlaceholders)
            INNER JOIN brand_country bcr ON bcr.brand_id = b.id AND bcr.country_id = ?
            WHERE b.status = 1
            ORDER BY b.name ASC
        ");
        $stmt->execute(array_merge($scopeCategoryIds, [$countryId]));
        $brandRows = $stmt->fetchAll();

        $brands = [];
        foreach ($brandRows as $br) {
            // Product count for this brand in this category (+ children) + current country
            $stmt2 = $db->prepare("
                SELECT COUNT(DISTINCT p.id) AS total
                FROM products p
                INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
                WHERE p.brand_id = ? AND p.category_id IN ($scopePlaceholders) AND p.status = 1
            ");
            $stmt2->execute(array_merge([$countryId, (int) $br['id']], $scopeCategoryIds));
            $pCount = (int) $stmt2->fetchColumn();

            // Min price for this brand in this category (+ children) + current country
            $stmt3 = $db->prepare("
                SELECT MIN(COALESCE(pp.discount_price, pp.regular_price)) AS min_price
                FROM product_prices pp
                INNER JOIN products p ON p.id = pp.product_id AND p.status = 1 AND p.category_id IN ($scopePlaceholders)
                WHERE pp.country_id = ? AND p.brand_id = ? AND pp.status = 1
            ");
            $stmt3->execute(array_merge($scopeCategoryIds, [$countryId, (int) $br['id']]));
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

        // Filter by brand(s) within this category — supports single brand_id
        // (legacy) or comma-separated brand_ids for multi-select checkboxes.
        $brandSql = '';
        $brandParams = [];

        $brandIdsInput = getInput('brand_ids');
        $brandIds = [];
        if ($brandIdsInput) {
            foreach (explode(',', $brandIdsInput) as $bid) {
                $bid = (int) trim($bid);
                if ($bid > 0) $brandIds[] = $bid;
            }
        } elseif (getInput('brand_id')) {
            $brandIds[] = (int) getInput('brand_id');
        }

        if (!empty($brandIds)) {
            $placeholders = implode(',', array_fill(0, count($brandIds), '?'));
            $brandSql = " AND p.brand_id IN ($placeholders) ";
            $brandParams = $brandIds;
        }

        // Filter by max price (from the sidebar price range slider)
        $priceSql = '';
        $priceParams = [];

        $maxPrice = getInput('max_price');
        if ($maxPrice !== null && $maxPrice !== '') {
            $priceSql = " AND COALESCE(pp.discount_price, pp.regular_price) <= ? ";
            $priceParams[] = (float) $maxPrice;
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

        // Sort. $sortColumnOnly (no "ORDER BY" keyword) is used inside the
        // products fetch query, appended AFTER grouping by category, so
        // products stay clustered under their child-category heading instead
        // of the sort field interleaving Small/Medium/Large Pizza together.
        $sort = getInput('sort', 'newest');
        $sortColumnOnly = "p.id DESC";
        if ($sort === 'price_low') $sortColumnOnly = "price ASC";
        if ($sort === 'price_high') $sortColumnOnly = "price DESC";
        if ($sort === 'name_asc') $sortColumnOnly = "p.name ASC";
        if ($sort === 'name_desc') $sortColumnOnly = "p.name DESC";
        if ($sort === 'calories_low') $sortColumnOnly = "p.calories ASC";
        if ($sort === 'calories_high') $sortColumnOnly = "p.calories DESC";

        // Price bounds across ALL products in this category + its children
        // (unfiltered) — used to size the price range slider on the frontend.
        $stmt = $db->prepare("
            SELECT MIN(COALESCE(pp.discount_price, pp.regular_price)) AS min_price,
                   MAX(COALESCE(pp.discount_price, pp.regular_price)) AS max_price
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            WHERE p.category_id IN ($scopePlaceholders) AND p.status = 1
        ");
        $stmt->execute(array_merge([$countryId], $scopeCategoryIds));
        $boundsRow = $stmt->fetch();
        $priceBounds = [
            'min' => $boundsRow && $boundsRow['min_price'] !== null ? (float) $boundsRow['min_price'] : 0,
            'max' => $boundsRow && $boundsRow['max_price'] !== null ? (float) $boundsRow['max_price'] : 0
        ];

        // Count total products (this category + its children, e.g. Pizza
        // page counts Small/Medium/Large Pizza products too)
        $countParams = array_merge([$countryId], $scopeCategoryIds, $brandParams, $priceParams, $searchParams);
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT p.id) AS total
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            WHERE p.category_id IN ($scopePlaceholders) AND p.status = 1
            $brandSql $priceSql $searchSql
        ");
        $stmt->execute($countParams);
        $totalProducts = (int) $stmt->fetchColumn();

        // Fetch products (this category + its children). c2.id/name is the
        // product's OWN (possibly child) category — used by the frontend to
        // group products under a heading per child category, e.g. visiting
        // "Pizza" shows a "Small Pizza" heading, "Medium Pizza" heading, etc.
        $fetchParams = array_merge([$countryId], $scopeCategoryIds, $brandParams, $priceParams, $searchParams, [$per_page, $offset]);
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.image, p.short_description, p.calories, p.featured,
                   p.brand_id, p.category_id AS product_category_id,
                   c2.name AS product_category_name, c2.slug AS product_category_slug,
                   b.name AS brand_name, b.slug AS brand_slug, b.logo AS brand_logo,
                   pp.regular_price, pp.discount_price, pp.currency AS price_currency,
                   COALESCE(pp.discount_price, pp.regular_price) AS price
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            INNER JOIN brands b ON b.id = p.brand_id AND b.status = 1
            INNER JOIN categories c2 ON c2.id = p.category_id
            WHERE p.category_id IN ($scopePlaceholders) AND p.status = 1
            $brandSql $priceSql $searchSql
            ORDER BY (p.category_id = $categoryId) DESC, p.category_id ASC, $sortColumnOnly
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
                'category_id'       => (int) $p['product_category_id'],
                'category_name'     => $p['product_category_name'],
                'category_slug'     => $p['product_category_slug'],
                'category_url'      => BASE_URL . '/category/' . $p['product_category_slug'],
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

        // Total product count across ALL countries for this category + children
        $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id IN ($scopePlaceholders) AND status = 1");
        $stmt->execute($scopeCategoryIds);
        $allProductCount = (int) $stmt->fetchColumn();

        // Total brand count for this category + children
        $stmt = $db->prepare("SELECT COUNT(DISTINCT brand_id) FROM brand_category WHERE category_id IN ($scopePlaceholders)");
        $stmt->execute($scopeCategoryIds);
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
                'is_parent'       => ($category['parent_id'] === null),
                'url'             => BASE_URL . '/category/' . $category['slug']
            ],
            'child_categories' => array_map(function ($c) {
                return [
                    'id'   => (int) $c['id'],
                    'name' => $c['name'],
                    'slug' => $c['slug']
                ];
            }, $childCategories),
            'brands'         => $brands,
            'price_bounds'   => [
                'min' => (int) floor($priceBounds['min']),
                'max' => (int) ceil($priceBounds['max'])
            ],
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
    // Only TOP-LEVEL (parent) categories are listed here — child categories
    // (e.g. Small Pizza) are shown grouped inside their parent's detail page
    // instead. Only count categories that actually have at least one product
    // priced/available in the CURRENT country, counting products assigned
    // directly to this category OR to any of its child categories
    // (EXISTS scoped to $countryId). This keeps pagination/"total_items"
    // consistent with what is shown.
    $countParams = array_merge($searchParams, [$countryId]);
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT c.id) AS total
        FROM categories c
        WHERE c.status = 1 AND c.parent_id IS NULL
        $searchSql
        AND EXISTS (
            SELECT 1
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            WHERE p.category_id IN (SELECT id FROM categories WHERE id = c.id OR parent_id = c.id)
              AND p.status = 1
        )
    ");
    $stmt->execute($countParams);
    $totalCategories = (int) $stmt->fetchColumn();

    // Fetch categories with product/brand counts for current country
    // (counts include products/brands under this category's children too)
    $fetchParams = array_merge($searchParams, [$countryId, $countryId, $per_page, $offset]);
    $stmt = $db->prepare("
        SELECT c.id, c.name, c.slug, c.image, c.description, c.sort_order,
               (SELECT COUNT(DISTINCT p.id)
                FROM products p
                INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
                WHERE p.category_id IN (SELECT id FROM categories WHERE id = c.id OR parent_id = c.id)
                  AND p.status = 1
               ) AS product_count,
               (SELECT COUNT(DISTINCT bc.brand_id)
                FROM brand_category bc
                INNER JOIN brand_country bcr ON bcr.brand_id = bc.brand_id AND bcr.country_id = ?
                WHERE bc.category_id IN (SELECT id FROM categories WHERE id = c.id OR parent_id = c.id)
               ) AS brand_count
        FROM categories c
        WHERE c.status = 1 AND c.parent_id IS NULL
        $searchSql
        GROUP BY c.id
        HAVING product_count > 0
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
        // This category's id plus any of its children's ids — used so a
        // parent category's card reflects prices/brands/images across all
        // of its subcategories too.
        $catIdSet = expandCategoryIdsWithChildren($db, [(int) $r['id']]);
        $catIdPlaceholders = implode(',', array_fill(0, count($catIdSet), '?'));

        // Get min price across all products in this category (+ children) for current country
        $stmt2 = $db->prepare("
            SELECT MIN(COALESCE(pp.discount_price, pp.regular_price)) AS min_price,
                   MAX(COALESCE(pp.discount_price, pp.regular_price)) AS max_price
            FROM product_prices pp
            INNER JOIN products p ON p.id = pp.product_id AND p.status = 1 AND p.category_id IN ($catIdPlaceholders)
            WHERE pp.country_id = ? AND pp.status = 1
        ");
        $stmt2->execute(array_merge($catIdSet, [$countryId]));
        $priceInfo = $stmt2->fetch();

        $minPrice = $priceInfo['min_price'] !== null ? (float) $priceInfo['min_price'] : null;
        $maxPrice = $priceInfo['max_price'] !== null ? (float) $priceInfo['max_price'] : null;

        // Get top brands in this category + children (limit 3)
        $stmt3 = $db->prepare("
            SELECT DISTINCT b.id, b.name, b.slug, b.logo
            FROM brands b
            INNER JOIN brand_category bc ON bc.brand_id = b.id AND bc.category_id IN ($catIdPlaceholders)
            INNER JOIN brand_country bcr ON bcr.brand_id = b.id AND bcr.country_id = ?
            WHERE b.status = 1
            ORDER BY b.name ASC
            LIMIT 3
        ");
        $stmt3->execute(array_merge($catIdSet, [$countryId]));
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

        // Get a sample product image for the category card (from this category or a child)
        $stmt4 = $db->prepare("
            SELECT p.image
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            WHERE p.category_id IN ($catIdPlaceholders) AND p.status = 1 AND p.image IS NOT NULL AND p.image != ''
            ORDER BY p.featured DESC, RAND()
            LIMIT 1
        ");
        $stmt4->execute(array_merge([$countryId], $catIdSet));
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