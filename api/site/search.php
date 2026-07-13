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
    $action = getInput('action', 'results'); // results, live, suggestions

    // ============================================================
    // ACTION: Live search (for header dropdown - fast, limited)
    // ============================================================
    if ($action === 'live') {
        $q = getInput('q');

        if (empty($q) || strlen($q) < 2) {
            jsonResponse([
                'success' => true,
                'query'   => '',
                'results' => ['brands' => [], 'products' => [], 'categories' => []],
                'total'   => 0
            ]);
        }

        $q = trim($q);
        $like = '%' . $q . '%';

        // Currency symbol
        $currStmt = $db->prepare("SELECT currency_symbol FROM countries WHERE id = ? LIMIT 1");
        $currStmt->execute([$countryId]);
        $currSymbol = $currStmt->fetchColumn() ?: '';

        // --- Search Brands (max 3) ---
        $stmt = $db->prepare("
            SELECT b.id, b.name, b.slug, b.logo, b.short_description
            FROM brands b
            INNER JOIN brand_country bc ON bc.brand_id = b.id AND bc.country_id = ?
            WHERE b.status = 1 AND b.name LIKE ?
            ORDER BY b.name ASC
            LIMIT 3
        ");
        $stmt->execute([$countryId, $like]);
        $brandRows = $stmt->fetchAll();

        $brands = [];
        foreach ($brandRows as $r) {
            $brands[] = [
                'type'  => 'brand',
                'id'    => (int) $r['id'],
                'name'  => highlightText($r['name'], $q),
                'name_raw' => $r['name'],
                'slug'  => $r['slug'],
                'logo'  => asset_url($r['logo']),
                'sub'   => truncateText(strip_tags($r['short_description']), 50),
                'url'   => BASE_URL . '/brand/' . $r['slug']
            ];
        }

        // --- Search Products (max 5) ---
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.image, p.calories,
                   p.brand_id, b.name AS brand_name, b.slug AS brand_slug, b.logo AS brand_logo,
                   pp.regular_price, pp.discount_price,
                   COALESCE(pp.discount_price, pp.regular_price) AS price
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            INNER JOIN brands b ON b.id = p.brand_id AND b.status = 1
            WHERE p.status = 1 AND p.name LIKE ?
            ORDER BY price ASC
            LIMIT 5
        ");
        $stmt->execute([$countryId, $like]);
        $productRows = $stmt->fetchAll();

        $products = [];
        foreach ($productRows as $r) {
            $hasDiscount = ($r['discount_price'] !== null && $r['discount_price'] < $r['regular_price']);
            $products[] = [
                'type'             => 'product',
                'id'               => (int) $r['id'],
                'name'             => highlightText($r['name'], $q),
                'name_raw'         => $r['name'],
                'slug'             => $r['slug'],
                'image'            => asset_url($r['image']),
                'brand_logo'       => asset_url($r['brand_logo']),
                'brand_name'       => $r['brand_name'],
                'sub'              => $r['brand_name'] . ($r['calories'] > 0 ? ' · ' . $r['calories'] . ' cal' : ''),
                'price'            => $hasDiscount 
                    ? $currSymbol . number_format((float) $r['discount_price'], 0) 
                    : ($r['regular_price'] !== null ? $currSymbol . number_format((float) $r['regular_price'], 0) : ''),
                'url'              => BASE_URL . '/product/' . $r['slug']
            ];
        }

        // --- Search Categories (max 3) ---
        $stmt = $db->prepare("
            SELECT c.id, c.name, c.slug, c.image
            FROM categories c
            WHERE c.status = 1 AND c.name LIKE ?
            ORDER BY c.name ASC
            LIMIT 3
        ");
        $stmt->execute([$like]);
        $catRows = $stmt->fetchAll();

        // Get product counts for these categories
        $categories = [];
        foreach ($catRows as $r) {
            $stmt2 = $db->prepare("
                SELECT COUNT(DISTINCT p.id) AS total
                FROM products p
                INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
                WHERE p.category_id = ? AND p.status = 1
            ");
            $stmt2->execute([$countryId, (int) $r['id']]);
            $pCount = (int) $stmt2->fetchColumn();

            $categories[] = [
                'type'  => 'category',
                'id'    => (int) $r['id'],
                'name'  => highlightText($r['name'], $q),
                'name_raw' => $r['name'],
                'slug'  => $r['slug'],
                'image' => asset_url($r['image']),
                'sub'   => $pCount . ' items',
                'url'   => BASE_URL . '/category/' . $r['slug']
            ];
        }

        $totalResults = count($brands) + count($products) + count($categories);

        // Log search keyword
        logSearch($db, $q, $countryId);

        jsonResponse([
            'success' => true,
            'query'   => $q,
            'results' => [
                'brands'    => $brands,
                'products'  => $products,
                'categories'=> $categories
            ],
            'total'   => $totalResults
        ]);
    }

    // ============================================================
    // ACTION: Search suggestions (autocomplete - minimal data)
    // ============================================================
    if ($action === 'suggestions') {
        $q = getInput('q');

        if (empty($q) || strlen($q) < 2) {
            jsonResponse(['success' => true, 'suggestions' => []]);
        }

        $q = trim($q);
        $like = '%' . $q . '%';
        $suggestions = [];

        // Brand names
        $stmt = $db->prepare("
            SELECT name, slug FROM brands
            WHERE status = 1 AND name LIKE ?
            ORDER BY name ASC LIMIT 3
        ");
        $stmt->execute([$like]);
        foreach ($stmt->fetchAll() as $r) {
            $suggestions[] = [
                'text' => $r['name'],
                'type' => 'brand',
                'url'  => BASE_URL . '/brand/' . $r['slug']
            ];
        }

        // Product names
        $stmt = $db->prepare("
            SELECT p.name, p.slug FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            WHERE p.status = 1 AND p.name LIKE ?
            ORDER BY p.name ASC LIMIT 4
        ");
        $stmt->execute([$countryId, $like]);
        foreach ($stmt->fetchAll() as $r) {
            $suggestions[] = [
                'text' => $r['name'],
                'type' => 'product',
                'url'  => BASE_URL . '/product/' . $r['slug']
            ];
        }

        // Category names
        $stmt = $db->prepare("
            SELECT name, slug FROM categories
            WHERE status = 1 AND name LIKE ?
            ORDER BY name ASC LIMIT 2
        ");
        $stmt->execute([$like]);
        foreach ($stmt->fetchAll() as $r) {
            $suggestions[] = [
                'text' => $r['name'],
                'type' => 'category',
                'url'  => BASE_URL . '/category/' . $r['slug']
            ];
        }

        jsonResponse([
            'success'     => true,
            'query'       => $q,
            'suggestions' => $suggestions
        ]);
    }

    // ============================================================
    // ACTION: Full search results (for search.php page - paginated)
    // ============================================================
    $q = getInput('q');

    if (empty($q)) {
        jsonResponse([
            'success' => true,
            'query'   => '',
            'results' => [
                'brands'    => [],
                'products'  => [],
                'categories'=> [],
                'offers'    => []
            ],
            'pagination' => [
                'current_page' => 1,
                'per_page'     => 12,
                'total_items'  => 0,
                'total_pages'  => 1,
                'has_next'     => false,
                'has_prev'     => false
            ],
            'total' => 0
        ]);
    }

    $q = trim($q);
    $like = '%' . $q . '%';

    // Pagination
    $page = max(1, (int) getInput('page', 1));
    $per_page = max(1, min(50, (int) getInput('per_page', 12)));
    $offset = ($page - 1) * $per_page;

    // Type filter: all, brands, products, categories, offers
    $type = getInput('type', 'all');

    // Sort
    $sort = getInput('sort', 'relevance');
    $productSortSql = " ORDER BY FIELD(p.name LIKE ?, 1, 0) DESC, p.id DESC ";
    $brandSortSql = " ORDER BY FIELD(b.name LIKE ?, 1, 0) DESC, b.name ASC ";
    $offerSortSql = " ORDER BY FIELD(o.title LIKE ?, 1, 0) DESC, o.discount_percent DESC ";

    if ($sort === 'newest') {
        $productSortSql = " ORDER BY p.id DESC ";
        $brandSortSql = " ORDER BY b.id DESC ";
        $offerSortSql = " ORDER BY o.id DESC ";
    }
    if ($sort === 'name_asc') {
        $productSortSql = " ORDER BY p.name ASC ";
        $brandSortSql = " ORDER BY b.name ASC ";
        $offerSortSql = " ORDER BY o.title ASC ";
    }
    if ($sort === 'price_low') {
        $productSortSql = " ORDER BY price ASC ";
    }
    if ($sort === 'price_high') {
        $productSortSql = " ORDER BY price DESC ";
    }

    // Currency symbol
    $currStmt = $db->prepare("SELECT currency_symbol FROM countries WHERE id = ? LIMIT 1");
    $currStmt->execute([$countryId]);
    $currSymbol = $currStmt->fetchColumn() ?: '';

    $allResults = [];
    $totalAll = 0;

    // ============================================================
    // Search Brands
    // ============================================================
    $brands = [];
    $brandTotal = 0;

    if ($type === 'all' || $type === 'brands') {
        // Count
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT b.id) AS total
            FROM brands b
            INNER JOIN brand_country bc ON bc.brand_id = b.id AND bc.country_id = ?
            WHERE b.status = 1 AND (b.name LIKE ? OR b.short_description LIKE ?)
        ");
        $stmt->execute([$countryId, $like, $like]);
        $brandTotal = (int) $stmt->fetchColumn();

        // Fetch
        $stmt = $db->prepare("
            SELECT b.id, b.name, b.slug, b.logo, b.cover_image, b.short_description,
                   (SELECT COUNT(DISTINCT p2.id)
                    FROM products p2
                    INNER JOIN product_prices pp2 ON pp2.product_id = p2.id AND pp2.country_id = ? AND pp2.status = 1
                    WHERE p2.brand_id = b.id AND p2.status = 1
                   ) AS product_count
            FROM brands b
            INNER JOIN brand_country bc ON bc.brand_id = b.id AND bc.country_id = ?
            WHERE b.status = 1 AND (b.name LIKE ? OR b.short_description LIKE ?)
            GROUP BY b.id
            $brandSortSql
        ");
        $brandParams = [$countryId, $countryId, $like, $like];
        if ($sort === 'relevance') {
            $brandParams[] = $like; // binds the FIELD(b.name LIKE ?, ...) placeholder in $brandSortSql
        }
        $stmt->execute($brandParams);
        $brandRows = $stmt->fetchAll();

        foreach ($brandRows as $r) {
            $brands[] = [
                'type'              => 'brand',
                'id'                => (int) $r['id'],
                'name'              => $r['name'],
                'name_highlighted'  => highlightText($r['name'], $q),
                'slug'              => $r['slug'],
                'logo'              => asset_url($r['logo']),
                'cover_image'       => asset_url($r['cover_image']),
                'short_description' => $r['short_description'],
                'product_count'     => (int) $r['product_count'],
                'url'               => BASE_URL . '/brand/' . $r['slug']
            ];
        }
    }

    // ============================================================
    // Search Products
    // ============================================================
    $products = [];
    $productTotal = 0;

    if ($type === 'all' || $type === 'products') {
        // Count
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT p.id) AS total
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            INNER JOIN brands b ON b.id = p.brand_id AND b.status = 1
            WHERE p.status = 1 AND (p.name LIKE ? OR p.short_description LIKE ?)
        ");
        $stmt->execute([$countryId, $like, $like]);
        $productTotal = (int) $stmt->fetchColumn();

        // Fetch (with pagination only when type=products, otherwise show all)
        if ($type === 'products') {
            $productSortSql .= " LIMIT $per_page OFFSET $offset ";
        }

        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.image, p.short_description, p.calories,
                   p.brand_id, b.name AS brand_name, b.slug AS brand_slug, b.logo AS brand_logo,
                   pp.regular_price, pp.discount_price,
                   COALESCE(pp.discount_price, pp.regular_price) AS price
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            INNER JOIN brands b ON b.id = p.brand_id AND b.status = 1
            WHERE p.status = 1 AND (p.name LIKE ? OR p.short_description LIKE ?)
            $productSortSql
        ");
        $productParams = [$countryId, $like, $like];
        if ($sort === 'relevance') {
            $productParams[] = $like; // binds the FIELD(p.name LIKE ?, ...) placeholder in $productSortSql
        }
        $stmt->execute($productParams);
        $productRows = $stmt->fetchAll();

        foreach ($productRows as $r) {
            $hasDiscount = ($r['discount_price'] !== null && $r['discount_price'] < $r['regular_price']);
            $discPercent = 0;
            if ($hasDiscount && $r['regular_price'] > 0) {
                $discPercent = round((($r['regular_price'] - $r['discount_price']) / $r['regular_price']) * 100);
            }

            $products[] = [
                'type'               => 'product',
                'id'                 => (int) $r['id'],
                'name'               => $r['name'],
                'name_highlighted'   => highlightText($r['name'], $q),
                'slug'               => $r['slug'],
                'image'              => asset_url($r['image']),
                'short_description'  => $r['short_description'],
                'calories'           => (int) $r['calories'],
                'brand_id'           => (int) $r['brand_id'],
                'brand_name'         => $r['brand_name'],
                'brand_slug'         => $r['brand_slug'],
                'brand_logo'         => asset_url($r['brand_logo']),
                'regular_price'      => $r['regular_price'] !== null ? (float) $r['regular_price'] : null,
                'discount_price'     => $r['discount_price'] !== null ? (float) $r['discount_price'] : null,
                'has_discount'       => $hasDiscount,
                'discount_percent'   => $discPercent,
                'formatted_regular'  => $r['regular_price'] !== null ? $currSymbol . number_format((float) $r['regular_price'], 0) : null,
                'formatted_discount' => $hasDiscount ? $currSymbol . number_format((float) $r['discount_price'], 0) : null,
                'url'                => BASE_URL . '/product/' . $r['slug'],
                'brand_url'          => BASE_URL . '/brand/' . $r['brand_slug']
            ];
        }
    }

    // ============================================================
    // Search Categories
    // ============================================================
    $categories = [];
    $categoryTotal = 0;

    if ($type === 'all' || $type === 'categories') {
        // Count
        $stmt = $db->prepare("
            SELECT COUNT(*) AS total
            FROM categories
            WHERE status = 1 AND (name LIKE ? OR description LIKE ?)
        ");
        $stmt->execute([$like, $like]);
        $categoryTotal = (int) $stmt->fetchColumn();

        // Fetch
        $stmt = $db->prepare("
            SELECT c.id, c.name, c.slug, c.image, c.description,
                   (SELECT COUNT(DISTINCT p2.id)
                    FROM products p2
                    INNER JOIN product_prices pp2 ON pp2.product_id = p2.id AND pp2.country_id = ? AND pp2.status = 1
                    WHERE p2.category_id = c.id AND p2.status = 1
                   ) AS product_count
            FROM categories c
            WHERE c.status = 1 AND (c.name LIKE ? OR c.description LIKE ?)
            ORDER BY c.name ASC
        ");
        $stmt->execute([$countryId, $like, $like]);
        $catRows = $stmt->fetchAll();

        foreach ($catRows as $r) {
            $categories[] = [
                'type'          => 'category',
                'id'            => (int) $r['id'],
                'name'          => $r['name'],
                'name_highlighted'=> highlightText($r['name'], $q),
                'slug'          => $r['slug'],
                'image'         => asset_url($r['image']),
                'description'   => $r['description'],
                'product_count' => (int) $r['product_count'],
                'url'           => BASE_URL . '/category/' . $r['slug']
            ];
        }
    }

    // ============================================================
    // Search Offers
    // ============================================================
    $offers = [];
    $offerTotal = 0;

    if ($type === 'all' || $type === 'offers') {
        // Count
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT o.id) AS total
            FROM offers o
            INNER JOIN brands b ON b.id = o.brand_id AND b.status = 1
            INNER JOIN offer_countries oc ON oc.offer_id = o.id AND oc.country_id = ?
            WHERE o.status = 1
              AND (o.title LIKE ? OR o.description LIKE ? OR b.name LIKE ?)
              AND o.start_date <= CURDATE()
              AND o.end_date >= CURDATE()
        ");
        $stmt->execute([$countryId, $like, $like, $like]);
        $offerTotal = (int) $stmt->fetchColumn();

        // Fetch
        $stmt = $db->prepare("
            SELECT o.id, o.title, o.slug, o.description, o.discount_percent, o.coupon_code,
                   o.start_date, o.end_date, o.image,
                   b.name AS brand_name, b.slug AS brand_slug, b.logo AS brand_logo
            FROM offers o
            INNER JOIN brands b ON b.id = o.brand_id AND b.status = 1
            INNER JOIN offer_countries oc ON oc.offer_id = o.id AND oc.country_id = ?
            WHERE o.status = 1
              AND (o.title LIKE ? OR o.description LIKE ? OR b.name LIKE ?)
              AND o.start_date <= CURDATE()
              AND o.end_date >= CURDATE()
            $offerSortSql
        ");
        $offerParams = [$countryId, $like, $like, $like];
        if ($sort === 'relevance') {
            $offerParams[] = $like; // binds the FIELD(o.title LIKE ?, ...) placeholder in $offerSortSql
        }
        $stmt->execute($offerParams);
        $offerRows = $stmt->fetchAll();

        $now = new DateTime('today');
        foreach ($offerRows as $r) {
            $endDate = new DateTime($r['end_date']);
            $daysLeft = $now->diff($endDate)->days;
            if ($endDate < $now) $daysLeft = 0;

            $offers[] = [
                'type'             => 'offer',
                'id'               => (int) $r['id'],
                'title'            => $r['title'],
                'title_highlighted'=> highlightText($r['title'], $q),
                'slug'             => $r['slug'],
                'description'      => $r['description'],
                'discount_percent' => (float) $r['discount_percent'],
                'coupon_code'      => $r['coupon_code'],
                'end_date'         => $r['end_date'],
                'days_remaining'   => $daysLeft,
                'image'            => asset_url($r['image']),
                'brand_name'       => $r['brand_name'],
                'brand_slug'       => $r['brand_slug'],
                'brand_logo'       => asset_url($r['brand_logo']),
                'brand_url'        => BASE_URL . '/brand/' . $r['brand_slug']
            ];
        }
    }

    // Calculate pagination (based on the selected type)
    if ($type === 'products') {
        $totalAll = $productTotal;
    } elseif ($type === 'brands') {
        $totalAll = $brandTotal;
    } elseif ($type === 'categories') {
        $totalAll = $categoryTotal;
    } elseif ($type === 'offers') {
        $totalAll = $offerTotal;
    } else {
        // "all" type: sum everything
        $totalAll = $brandTotal + $productTotal + $categoryTotal + $offerTotal;
    }

    // Log search keyword
    logSearch($db, $q, $countryId);

    jsonResponse([
        'success'    => true,
        'query'      => $q,
        'results'    => [
            'brands'    => $brands,
            'products'  => $products,
            'categories'=> $categories,
            'offers'    => $offers
        ],
        'counts'     => [
            'brands'    => $brandTotal,
            'products'  => $productTotal,
            'categories'=> $categoryTotal,
            'offers'    => $offerTotal,
            'total'     => $totalAll
        ],
        'pagination' => [
            'current_page' => $page,
            'per_page'     => $per_page,
            'total_items'  => $totalAll,
            'total_pages'  => ($type === 'products') ? (ceil($productTotal / $per_page) ?: 1) : 1,
            'has_next'     => ($type === 'products') ? ($page < ceil($productTotal / $per_page)) : false,
            'has_prev'     => ($type === 'products' && $page > 1) ? true : false
        ],
        'filters'    => [
            'active_type' => $type,
            'active_sort' => $sort
        ],
        'total'      => $totalAll,
        'country_id' => $countryId
    ]);

} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Search failed. Please try again.'
    ], 500);
}

/**
 * Highlight matching text with <mark> tags
 */
function highlightText($text, $keyword) {
    if (empty($keyword)) return $text;
    return preg_replace(
        '/(' . preg_quote($keyword, '/') . ')/i',
        '<mark>$1</mark>',
        $text
    );
}

/**
 * Truncate text to a specific length
 */
function truncateText($text, $maxLen = 100) {
    if (empty($text)) return '';
    $text = strip_tags($text);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    if (strlen($text) > $maxLen) {
        $text = substr($text, 0, $maxLen - 3) . '...';
    }
    return $text;
}

/**
 * Log search keyword to search_logs table
 */
function logSearch($db, $keyword, $countryId) {
    if (empty($keyword)) return;

    try {
        // Check if keyword already exists for this country
        $stmt = $db->prepare("
            SELECT id, total_search FROM search_logs
            WHERE keyword = ? AND country_id = ?
            LIMIT 1
        ");
        $stmt->execute([$keyword, $countryId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Increment count and update last_search
            $stmt = $db->prepare("
                UPDATE search_logs
                SET total_search = total_search + 1,
                    last_search = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$existing['id']]);
        } else {
            // Insert new log
            $stmt = $db->prepare("
                INSERT INTO search_logs (keyword, country_id, total_search, last_search)
                VALUES (?, ?, 1, NOW())
            ");
            $stmt->execute([$keyword, $countryId]);
        }
    } catch (PDOException $e) {
        // Silent fail - search logging shouldn't break the response
    }
}