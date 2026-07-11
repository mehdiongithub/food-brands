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
    $action = getInput('action', 'list'); // list, detail, featured, quick-view, slug-check

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
            $stmt = $db->prepare("SELECT id FROM products WHERE slug = ? AND id != ? AND status = 1 LIMIT 1");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $db->prepare("SELECT id FROM products WHERE slug = ? AND status = 1 LIMIT 1");
            $stmt->execute([$slug]);
        }

        $exists = $stmt->fetch() ? true : false;
        jsonResponse(['success' => true, 'exists' => $exists]);
    }

    // ============================================================
    // ACTION: Featured products (for home page)
    // ============================================================
    if ($action === 'featured') {
        $limit = (int) getInput('limit', 8);
        if ($limit > 30) $limit = 30;
        if ($limit < 1) $limit = 8;

        $brandId = getInput('brand_id');
        $brandSql = '';
        $brandParams = [];

        if ($brandId) {
            $brandId = (int) $brandId;
            $brandSql = " AND p.brand_id = ? ";
            $brandParams[] = $brandId;
        }

        $params = array_merge([$countryId], $brandParams, [$limit]);
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.image, p.short_description, p.calories, p.featured,
                   p.brand_id, p.category_id,
                   b.name AS brand_name, b.slug AS brand_slug, b.logo AS brand_logo,
                   c.name AS category_name, c.slug AS category_slug,
                   pp.regular_price, pp.discount_price, pp.currency AS price_currency,
                   COALESCE(pp.discount_price, pp.regular_price) AS price
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            INNER JOIN brands b ON b.id = p.brand_id AND b.status = 1
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.status = 1
            $brandSql
            ORDER BY p.featured DESC, price ASC
            LIMIT ?
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Get currency symbol
        $currStmt = $db->prepare("SELECT currency_symbol FROM countries WHERE id = ? LIMIT 1");
        $currStmt->execute([$countryId]);
        $currSymbol = $currStmt->fetchColumn() ?: '';

        $products = [];
        foreach ($rows as $r) {
            $hasDiscount = ($r['discount_price'] !== null && $r['discount_price'] < $r['regular_price']);
            $discPercent = 0;
            if ($hasDiscount && $r['regular_price'] > 0) {
                $discPercent = round((($r['regular_price'] - $r['discount_price']) / $r['regular_price']) * 100);
            }

            $products[] = [
                'id'                => (int) $r['id'],
                'name'              => $r['name'],
                'slug'              => $r['slug'],
                'image'             => asset_url($r['image']),
                'short_description' => $r['short_description'],
                'calories'          => (int) $r['calories'],
                'featured'          => (bool) $r['featured'],
                'brand_id'          => (int) $r['brand_id'],
                'brand_name'        => $r['brand_name'],
                'brand_slug'        => $r['brand_slug'],
                'brand_logo'        => asset_url($r['brand_logo']),
                'category_id'       => (int) $r['category_id'],
                'category_name'     => $r['category_name'],
                'category_slug'     => $r['category_slug'],
                'regular_price'     => $r['regular_price'] !== null ? (float) $r['regular_price'] : null,
                'discount_price'    => $r['discount_price'] !== null ? (float) $r['discount_price'] : null,
                'has_discount'      => $hasDiscount,
                'discount_percent'  => $discPercent,
                'formatted_regular' => $r['regular_price'] !== null ? $currSymbol . number_format((float) $r['regular_price'], 0) : null,
                'formatted_discount'=> $hasDiscount ? $currSymbol . number_format((float) $r['discount_price'], 0) : null,
                'url'               => BASE_URL . '/product/' . $r['slug'],
                'brand_url'         => BASE_URL . '/brand/' . $r['brand_slug']
            ];
        }

        jsonResponse([
            'success'    => true,
            'products'   => $products,
            'total'      => count($products),
            'country_id' => $countryId
        ]);
    }

    // ============================================================
    // ACTION: Quick View (minimal product data for modal)
    // ============================================================
    if ($action === 'quick-view') {
        $slug = getInput('slug');

        if (empty($slug)) {
            $id = (int) getInput('id', 0);
            if ($id <= 0) {
                jsonResponse(['success' => false, 'message' => 'Product slug or ID is required'], 400);
            }
            $stmt = $db->prepare("
                SELECT id, name, slug, image, short_description, description, calories, brand_id, category_id
                FROM products WHERE id = ? AND status = 1 LIMIT 1
            ");
            $stmt->execute([$id]);
        } else {
            $stmt = $db->prepare("
                SELECT id, name, slug, image, short_description, description, calories, brand_id, category_id
                FROM products WHERE slug = ? AND status = 1 LIMIT 1
            ");
            $stmt->execute([$slug]);
        }

        $product = $stmt->fetch();

        if (!$product) {
            jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
        }

        $productId = (int) $product['id'];

        // Brand info
        $stmt = $db->prepare("SELECT name, slug, logo FROM brands WHERE id = ? AND status = 1 LIMIT 1");
        $stmt->execute([$productId ? $product['brand_id'] : 0]);
        $brand = $stmt->fetch();

        // Category info
        $stmt = $db->prepare("SELECT name, slug FROM categories WHERE id = ? AND status = 1 LIMIT 1");
        $stmt->execute([$product['category_id']]);
        $category = $stmt->fetch();

        // Price for current country
        $stmt = $db->prepare("
            SELECT regular_price, discount_price, currency
            FROM product_prices
            WHERE product_id = ? AND country_id = ? AND status = 1
            LIMIT 1
        ");
        $stmt->execute([$productId, $countryId]);
        $price = $stmt->fetch();

        // Additional images
        $stmt = $db->prepare("
            SELECT image, sort_order
            FROM product_images
            WHERE product_id = ?
            ORDER BY sort_order ASC, id ASC
            LIMIT 5
        ");
        $stmt->execute([$productId]);
        $imageRows = $stmt->fetchAll();

        $images = [asset_url($product['image'])];
        foreach ($imageRows as $img) {
            $images[] = asset_url($img['image']);
        }
        $images = array_unique($images);

        // Ingredients
        $stmt = $db->prepare("
            SELECT i.name
            FROM ingredients i
            INNER JOIN product_ingredients pi ON pi.ingredient_id = i.id
            WHERE pi.product_id = ? AND i.status = 1
            ORDER BY i.name ASC
        ");
        $stmt->execute([$productId]);
        $ingredientRows = $stmt->fetchAll();

        $ingredients = [];
        foreach ($ingredientRows as $ing) {
            $ingredients[] = $ing['name'];
        }

        // Currency symbol
        $currStmt = $db->prepare("SELECT currency_symbol FROM countries WHERE id = ? LIMIT 1");
        $currStmt->execute([$countryId]);
        $currSymbol = $currStmt->fetchColumn() ?: '';

        $hasDiscount = ($price && $price['discount_price'] !== null && $price['discount_price'] < $price['regular_price']);
        $discPercent = 0;
        if ($hasDiscount && $price['regular_price'] > 0) {
            $discPercent = round((($price['regular_price'] - $price['discount_price']) / $price['regular_price']) * 100);
        }

        jsonResponse([
            'success'     => true,
            'product'     => [
                'id'                => $productId,
                'name'              => $product['name'],
                'slug'              => $product['slug'],
                'images'            => $images,
                'short_description' => $product['short_description'],
                'description'       => $product['description'],
                'calories'          => (int) $product['calories'],
                'brand'             => $brand ? [
                    'name' => $brand['name'],
                    'slug' => $brand['slug'],
                    'logo' => asset_url($brand['logo']),
                    'url'  => BASE_URL . '/brand/' . $brand['slug']
                ] : null,
                'category'          => $category ? [
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'url'  => BASE_URL . '/category/' . $category['slug']
                ] : null,
                'regular_price'     => $price ? ($price['regular_price'] !== null ? (float) $price['regular_price'] : null) : null,
                'discount_price'    => $price ? ($price['discount_price'] !== null ? (float) $price['discount_price'] : null) : null,
                'has_discount'      => $hasDiscount,
                'discount_percent'  => $discPercent,
                'formatted_regular' => $price && $price['regular_price'] !== null ? $currSymbol . number_format((float) $price['regular_price'], 0) : null,
                'formatted_discount'=> $hasDiscount ? $currSymbol . number_format((float) $price['discount_price'], 0) : null,
                'ingredients'       => $ingredients,
                'url'               => BASE_URL . '/product/' . $product['slug']
            ]
        ]);
    }

    // ============================================================
    // ACTION: Product detail (single product by slug)
    // ============================================================
    if ($action === 'detail') {
        $slug = getInput('slug');

        if (empty($slug)) {
            jsonResponse(['success' => false, 'message' => 'Product slug is required'], 400);
        }

        // Get product
        $stmt = $db->prepare("
            SELECT id, name, slug, image, short_description, description, calories,
                   featured, brand_id, category_id, meta_title, meta_description
            FROM products
            WHERE slug = ? AND status = 1
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $product = $stmt->fetch();

        if (!$product) {
            jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
        }

        $productId = (int) $product['id'];

        // Log product view
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt = $db->prepare("
            INSERT INTO product_views (product_id, ip_address, country_id, viewed_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$productId, $ip, $countryId]);

        // Brand info
        $stmt = $db->prepare("
            SELECT id, name, slug, logo, cover_image, short_description
            FROM brands WHERE id = ? AND status = 1 LIMIT 1
        ");
        $stmt->execute([$product['brand_id']]);
        $brand = $stmt->fetch();

        // Category info
        $stmt = $db->prepare("
            SELECT id, name, slug
            FROM categories WHERE id = ? AND status = 1 LIMIT 1
        ");
        $stmt->execute([$product['category_id']]);
        $category = $stmt->fetch();

        // Product images
        $stmt = $db->prepare("
            SELECT id, image, sort_order
            FROM product_images
            WHERE product_id = ?
            ORDER BY sort_order ASC, id ASC
        ");
        $stmt->execute([$productId]);
        $imageRows = $stmt->fetchAll();

        $allImages = [];
        $mainImage = asset_url($product['image']);
        $allImages[] = ['id' => 0, 'image' => $mainImage, 'is_main' => true];
        foreach ($imageRows as $img) {
            $imgUrl = asset_url($img['image']);
            $allImages[] = [
                'id'       => (int) $img['id'],
                'image'    => $imgUrl,
                'is_main'  => false,
                'sort'     => (int) $img['sort_order']
            ];
        }
        // Remove duplicates (main image might be in product_images too)
        $seen = [];
        $uniqueImages = [];
        foreach ($allImages as $im) {
            if (!isset($seen[$im['image']])) {
                $seen[$im['image']] = true;
                $uniqueImages[] = $im;
            }
        }

        // Price for current country
        $stmt = $db->prepare("
            SELECT regular_price, discount_price, currency
            FROM product_prices
            WHERE product_id = ? AND country_id = ? AND status = 1
            LIMIT 1
        ");
        $stmt->execute([$productId, $countryId]);
        $currentPrice = $stmt->fetch();

        // All country prices (for comparison table)
        $stmt = $db->prepare("
            SELECT pp.country_id, pp.regular_price, pp.discount_price, pp.currency,
                   c.name AS country_name, c.code AS country_code, c.currency_symbol, c.flag, c.slug AS country_slug
            FROM product_prices pp
            INNER JOIN countries c ON c.id = pp.country_id AND c.status = 1
            WHERE pp.product_id = ? AND pp.status = 1
            ORDER BY c.name ASC
        ");
        $stmt->execute([$productId]);
        $priceRows = $stmt->fetchAll();

        $countryPrices = [];
        foreach ($priceRows as $pr) {
            $hasD = ($pr['discount_price'] !== null && $pr['discount_price'] < $pr['regular_price']);
            $countryPrices[] = [
                'country_id'       => (int) $pr['country_id'],
                'country_name'     => $pr['country_name'],
                'country_code'     => $pr['country_code'],
                'country_slug'     => $pr['country_slug'],
                'currency'         => $pr['currency'],
                'currency_symbol'  => $pr['currency_symbol'],
                'flag_url'         => asset_url($pr['flag']),
                'regular_price'    => $pr['regular_price'] !== null ? (float) $pr['regular_price'] : null,
                'discount_price'   => $pr['discount_price'] !== null ? (float) $pr['discount_price'] : null,
                'has_discount'     => $hasD,
                'formatted_regular'=> $pr['regular_price'] !== null ? $pr['currency_symbol'] . number_format((float) $pr['regular_price'], 0) : null,
                'formatted_discount'=> $hasD ? $pr['currency_symbol'] . number_format((float) $pr['discount_price'], 0) : null,
                'is_current'       => ((int) $pr['country_id'] === $countryId) ? true : false
            ];
        }

        // Nutrition data
        $stmt = $db->prepare("
            SELECT fat, carbs, protein, fiber, sugar, sodium, calories
            FROM product_nutrition
            WHERE product_id = ?
            LIMIT 1
        ");
        $stmt->execute([$productId]);
        $nutrition = $stmt->fetch();

        if (!$nutrition) {
            $nutrition = [
                'fat' => null, 'carbs' => null, 'protein' => null,
                'fiber' => null, 'sugar' => null, 'sodium' => null,
                'calories' => (int) $product['calories']
            ];
        }

        // Ingredients
        $stmt = $db->prepare("
            SELECT i.id, i.name
            FROM ingredients i
            INNER JOIN product_ingredients pi ON pi.ingredient_id = i.id
            WHERE pi.product_id = ? AND i.status = 1
            ORDER BY i.name ASC
        ");
        $stmt->execute([$productId]);
        $ingredientRows = $stmt->fetchAll();

        $ingredients = [];
        foreach ($ingredientRows as $ing) {
            $ingredients[] = ['id' => (int) $ing['id'], 'name' => $ing['name']];
        }

        // Related products (same brand, different product, with price in current country)
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.image, p.short_description, p.calories,
                   pp.regular_price, pp.discount_price,
                   COALESCE(pp.discount_price, pp.regular_price) AS price
            FROM products p
            INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
            WHERE p.brand_id = ? AND p.id != ? AND p.status = 1
            ORDER BY RAND()
            LIMIT 6
        ");
        $stmt->execute([$countryId, $product['brand_id'], $productId]);
        $relatedRows = $stmt->fetchAll();

        $currStmt = $db->prepare("SELECT currency_symbol FROM countries WHERE id = ? LIMIT 1");
        $currStmt->execute([$countryId]);
        $currSymbol = $currStmt->fetchColumn() ?: '';

        $relatedProducts = [];
        foreach ($relatedRows as $rp) {
            $hasRD = ($rp['discount_price'] !== null && $rp['discount_price'] < $rp['regular_price']);
            $relatedProducts[] = [
                'id'                => (int) $rp['id'],
                'name'              => $rp['name'],
                'slug'              => $rp['slug'],
                'image'             => asset_url($rp['image']),
                'short_description' => $rp['short_description'],
                'calories'          => (int) $rp['calories'],
                'has_discount'      => $hasRD,
                'formatted_regular' => $rp['regular_price'] !== null ? $currSymbol . number_format((float) $rp['regular_price'], 0) : null,
                'formatted_discount'=> $hasRD ? $currSymbol . number_format((float) $rp['discount_price'], 0) : null,
                'url'               => BASE_URL . '/product/' . $rp['slug']
            ];
        }

        // If not enough related products, get from same category
        if (count($relatedProducts) < 4) {
            $existingIds = array_merge([$productId], array_column($relatedProducts, 'id'));
            $placeholders = implode(',', array_fill(0, count($existingIds), '?'));
            $stmt = $db->prepare("
                SELECT p.id, p.name, p.slug, p.image, p.short_description, p.calories,
                       pp.regular_price, pp.discount_price,
                       COALESCE(pp.discount_price, pp.regular_price) AS price
                FROM products p
                INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
                WHERE p.category_id = ? AND p.id NOT IN ($placeholders) AND p.status = 1
                ORDER BY RAND()
                LIMIT ?
            ");
            $catParams = array_merge([$countryId, $product['category_id']], $existingIds, [6 - count($relatedProducts)]);
            $stmt->execute($catParams);
            $catRelated = $stmt->fetchAll();

            foreach ($catRelated as $cr) {
                $hasCD = ($cr['discount_price'] !== null && $cr['discount_price'] < $cr['regular_price']);
                $relatedProducts[] = [
                    'id'                => (int) $cr['id'],
                    'name'              => $cr['name'],
                    'slug'              => $cr['slug'],
                    'image'             => asset_url($cr['image']),
                    'short_description' => $cr['short_description'],
                    'calories'          => (int) $cr['calories'],
                    'has_discount'      => $hasCD,
                    'formatted_regular' => $cr['regular_price'] !== null ? $currSymbol . number_format((float) $cr['regular_price'], 0) : null,
                    'formatted_discount'=> $hasCD ? $currSymbol . number_format((float) $cr['discount_price'], 0) : null,
                    'url'               => BASE_URL . '/product/' . $cr['slug']
                ];
            }
        }

        // Active offers for this product's brand in current country
        $stmt = $db->prepare("
            SELECT o.id, o.title, o.slug, o.discount_percent, o.coupon_code, o.start_date, o.end_date, o.image
            FROM offers o
            INNER JOIN offer_countries oc ON oc.offer_id = o.id AND oc.country_id = ?
            WHERE o.brand_id = ? AND o.status = 1
              AND o.start_date <= CURDATE()
              AND o.end_date >= CURDATE()
            ORDER BY o.discount_percent DESC
            LIMIT 3
        ");
        $stmt->execute([$countryId, $product['brand_id']]);
        $offerRows = $stmt->fetchAll();

        $offers = [];
        foreach ($offerRows as $o) {
            $offers[] = [
                'id'               => (int) $o['id'],
                'title'            => $o['title'],
                'slug'             => $o['slug'],
                'discount_percent' => (float) $o['discount_percent'],
                'coupon_code'      => $o['coupon_code'],
                'start_date'       => $o['start_date'],
                'end_date'         => $o['end_date'],
                'image'            => asset_url($o['image'])
            ];
        }

        // Calculate discount info for current country
        $hasDiscount = ($currentPrice && $currentPrice['discount_price'] !== null && $currentPrice['discount_price'] < $currentPrice['regular_price']);
        $discPercent = 0;
        $savedAmount = 0;
        if ($hasDiscount && $currentPrice['regular_price'] > 0) {
            $discPercent = round((($currentPrice['regular_price'] - $currentPrice['discount_price']) / $currentPrice['regular_price']) * 100);
            $savedAmount = (float) ($currentPrice['regular_price'] - $currentPrice['discount_price']);
        }

        // Build Schema.org JSON-LD
        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $product['name'],
            'description' => strip_tags($product['short_description'] ?: $product['description'] ?: ''),
            'image'       => $mainImage,
            'url'         => BASE_URL . '/product/' . $product['slug'],
        ];
        if ($brand) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name'  => $brand['name']
            ];
        }
        if ($currentPrice) {
            $offerPrice = $hasDiscount ? (float) $currentPrice['discount_price'] : (float) $currentPrice['regular_price'];
            $schema['offers'] = [
                '@type'         => 'Offer',
                'price'         => $offerPrice,
                'priceCurrency' => $currentPrice['currency'] ?? 'USD',
                'availability'  => 'https://schema.org/InStock'
            ];
        }
        if ($nutrition && $nutrition['calories']) {
            $schema['nutrition'] = [
                '@type'      => 'NutritionInformation',
                'calories'   => $nutrition['calories'] . ' kcal',
                'fatContent' => $nutrition['fat'] ? $nutrition['fat'] . 'g' : null,
                'carbohydrateContent' => $nutrition['carbs'] ? $nutrition['carbs'] . 'g' : null,
                'proteinContent' => $nutrition['protein'] ? $nutrition['protein'] . 'g' : null
            ];
        }

        jsonResponse([
            'success'        => true,
            'product'        => [
                'id'                => $productId,
                'name'              => $product['name'],
                'slug'              => $product['slug'],
                'short_description' => $product['short_description'],
                'description'       => $product['description'],
                'calories'          => (int) $product['calories'],
                'featured'          => (bool) $product['featured'],
                'meta_title'        => $product['meta_title'],
                'meta_description'  => $product['meta_description'],
                'url'               => BASE_URL . '/product/' . $product['slug']
            ],
            'images'         => $uniqueImages,
            'brand'          => $brand ? [
                'id'                => (int) $brand['id'],
                'name'              => $brand['name'],
                'slug'              => $brand['slug'],
                'logo'              => asset_url($brand['logo']),
                'cover_image'       => asset_url($brand['cover_image']),
                'short_description' => $brand['short_description'],
                'url'               => BASE_URL . '/brand/' . $brand['slug']
            ] : null,
            'category'       => $category ? [
                'id'   => (int) $category['id'],
                'name' => $category['name'],
                'slug' => $category['slug'],
                'url'  => BASE_URL . '/category/' . $category['slug']
            ] : null,
            'current_price'  => [
                'regular_price'     => $currentPrice ? ($currentPrice['regular_price'] !== null ? (float) $currentPrice['regular_price'] : null) : null,
                'discount_price'    => $currentPrice ? ($currentPrice['discount_price'] !== null ? (float) $currentPrice['discount_price'] : null) : null,
                'has_discount'      => $hasDiscount,
                'discount_percent'  => $discPercent,
                'saved_amount'      => $savedAmount,
                'currency'          => $currentPrice ? $currentPrice['currency'] : null,
                'formatted_regular' => $currentPrice && $currentPrice['regular_price'] !== null ? $currSymbol . number_format((float) $currentPrice['regular_price'], 0) : null,
                'formatted_discount'=> $hasDiscount ? $currSymbol . number_format((float) $currentPrice['discount_price'], 0) : null,
                'formatted_saved'   => $savedAmount > 0 ? $currSymbol . number_format($savedAmount, 0) . ' saved' : null
            ],
            'country_prices' => $countryPrices,
            'nutrition'      => [
                'calories' => $nutrition['calories'] !== null ? (int) $nutrition['calories'] : null,
                'fat'      => $nutrition['fat'] !== null ? (float) $nutrition['fat'] : null,
                'carbs'    => $nutrition['carbs'] !== null ? (float) $nutrition['carbs'] : null,
                'protein'  => $nutrition['protein'] !== null ? (float) $nutrition['protein'] : null,
                'fiber'    => $nutrition['fiber'] !== null ? (float) $nutrition['fiber'] : null,
                'sugar'    => $nutrition['sugar'] !== null ? (float) $nutrition['sugar'] : null,
                'sodium'   => $nutrition['sodium'] !== null ? (float) $nutrition['sodium'] : null
            ],
            'ingredients'    => $ingredients,
            'offers'         => $offers,
            'related_products'=> $relatedProducts,
            'schema_json'    => json_encode($schema, JSON_UNESCAPED_SLASHES),
            'country_id'     => $countryId
        ]);
    }

    // ============================================================
    // ACTION: List products (default - for home, category pages, etc.)
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
        $searchSql = " AND (p.name LIKE ? OR p.short_description LIKE ?) ";
        $searchParams[] = '%' . $search . '%';
        $searchParams[] = '%' . $search . '%';
    }

    // Filter by brand
    $brandFilter = getInput('brand_id');
    $brandSql = '';
    $brandParams = [];

    if ($brandFilter) {
        $brandFilter = (int) $brandFilter;
        $brandSql = " AND p.brand_id = ? ";
        $brandParams[] = $brandFilter;
    }

    // Filter by category
    $catFilter = getInput('category_id');
    $catSql = '';
    $catParams = [];

    if ($catFilter) {
        $catFilter = (int) $catFilter;
        $catSql = " AND p.category_id = ? ";
        $catParams[] = $catFilter;
    }

    // Filter by country (default: session country)
    $countryFilter = getInput('country_id', $countryId);
    $countryFilter = (int) $countryFilter;

    // Featured only?
    $featuredOnly = getInput('featured');
    $featuredSql = '';
    if ($featuredOnly === '1' || $featuredOnly === 'true') {
        $featuredSql = " AND p.featured = 1 ";
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

    // Count total
    $countParams = array_merge($searchParams, $brandParams, $catParams, [$countryFilter]);
    $stmt = $db->prepare("
        SELECT COUNT(DISTINCT p.id) AS total
        FROM products p
        INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
        WHERE p.status = 1
        $searchSql $brandSql $catSql $featuredSql
    ");
    $stmt->execute($countParams);
    $totalProducts = (int) $stmt->fetchColumn();

    // Fetch products
    $fetchParams = array_merge($searchParams, $brandParams, $catParams, [$countryFilter, $per_page, $offset]);
    $stmt = $db->prepare("
        SELECT p.id, p.name, p.slug, p.image, p.short_description, p.calories, p.featured,
               p.brand_id, p.category_id,
               b.name AS brand_name, b.slug AS brand_slug, b.logo AS brand_logo,
               c.name AS category_name, c.slug AS category_slug,
               pp.regular_price, pp.discount_price, pp.currency AS price_currency,
               COALESCE(pp.discount_price, pp.regular_price) AS price
        FROM products p
        INNER JOIN product_prices pp ON pp.product_id = p.id AND pp.country_id = ? AND pp.status = 1
        INNER JOIN brands b ON b.id = p.brand_id AND b.status = 1
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE p.status = 1
        $searchSql $brandSql $catSql $featuredSql
        $sortSql
        LIMIT ? OFFSET ?
    ");
    $stmt->execute($fetchParams);
    $rows = $stmt->fetchAll();

    // Currency symbol
    $currStmt = $db->prepare("SELECT currency_symbol FROM countries WHERE id = ? LIMIT 1");
    $currStmt->execute([$countryFilter]);
    $currSymbol = $currStmt->fetchColumn() ?: '';

    $products = [];
    foreach ($rows as $r) {
        $hasDiscount = ($r['discount_price'] !== null && $r['discount_price'] < $r['regular_price']);
        $discPercent = 0;
        if ($hasDiscount && $r['regular_price'] > 0) {
            $discPercent = round((($r['regular_price'] - $r['discount_price']) / $r['regular_price']) * 100);
        }

        $products[] = [
            'id'                => (int) $r['id'],
            'name'              => $r['name'],
            'slug'              => $r['slug'],
            'image'             => asset_url($r['image']),
            'short_description' => $r['short_description'],
            'calories'          => (int) $r['calories'],
            'featured'          => (bool) $r['featured'],
            'brand_id'          => (int) $r['brand_id'],
            'brand_name'        => $r['brand_name'],
            'brand_slug'        => $r['brand_slug'],
            'brand_logo'        => asset_url($r['brand_logo']),
            'category_id'       => (int) $r['category_id'],
            'category_name'     => $r['category_name'],
            'category_slug'     => $r['category_slug'],
            'regular_price'     => $r['regular_price'] !== null ? (float) $r['regular_price'] : null,
            'discount_price'    => $r['discount_price'] !== null ? (float) $r['discount_price'] : null,
            'has_discount'      => $hasDiscount,
            'discount_percent'  => $discPercent,
            'formatted_regular' => $r['regular_price'] !== null ? $currSymbol . number_format((float) $r['regular_price'], 0) : null,
            'formatted_discount'=> $hasDiscount ? $currSymbol . number_format((float) $r['discount_price'], 0) : null,
            'url'               => BASE_URL . '/product/' . $r['slug'],
            'brand_url'         => BASE_URL . '/brand/' . $r['brand_slug']
        ];
    }

    // Filter data for sidebar (if requested)
    $withFilters = getInput('with_filters', '0');

    $filters = null;
    if ($withFilters === '1' || $withFilters === 'true') {
        // Brand filter list
        $stmt = $db->query("
            SELECT b.id, b.name, b.slug, b.logo,
                   (SELECT COUNT(DISTINCT p2.id)
                    FROM products p2
                    INNER JOIN product_prices pp2 ON pp2.product_id = p2.id AND pp2.country_id = $countryFilter AND pp2.status = 1
                    WHERE p2.brand_id = b.id AND p2.status = 1
                   ) AS product_count
            FROM brands b
            WHERE b.status = 1
            HAVING product_count > 0
            ORDER BY b.name ASC
        ");
        $brandFilterList = $stmt->fetchAll();

        $filterBrands = [];
        foreach ($brandFilterList as $fb) {
            $filterBrands[] = [
                'id'            => (int) $fb['id'],
                'name'          => $fb['name'],
                'slug'          => $fb['slug'],
                'logo'          => asset_url($fb['logo']),
                'product_count' => (int) $fb['product_count']
            ];
        }

        // Category filter list
        $stmt = $db->query("
            SELECT c.id, c.name, c.slug,
                   (SELECT COUNT(DISTINCT p2.id)
                    FROM products p2
                    INNER JOIN product_prices pp2 ON pp2.product_id = p2.id AND pp2.country_id = $countryFilter AND pp2.status = 1
                    WHERE p2.category_id = c.id AND p2.status = 1
                   ) AS product_count
            FROM categories c
            WHERE c.status = 1
            HAVING product_count > 0
            ORDER BY c.name ASC
        ");
        $catFilterList = $stmt->fetchAll();

        $filterCategories = [];
        foreach ($catFilterList as $fc) {
            $filterCategories[] = [
                'id'            => (int) $fc['id'],
                'name'          => $fc['name'],
                'slug'          => $fc['slug'],
                'product_count' => (int) $fc['product_count']
            ];
        }

        // Price range
        $stmt = $db->prepare("
            SELECT MIN(COALESCE(pp.discount_price, pp.regular_price)) AS min_price,
                   MAX(COALESCE(pp.discount_price, pp.regular_price)) AS max_price
            FROM product_prices pp
            INNER JOIN products p ON p.id = pp.product_id AND p.status = 1
            WHERE pp.country_id = ? AND pp.status = 1
        ");
        $stmt->execute([$countryFilter]);
        $priceRange = $stmt->fetch();

        $filters = [
            'brands'          => $filterBrands,
            'categories'      => $filterCategories,
            'price_min'       => $priceRange['min_price'] !== null ? (float) $priceRange['min_price'] : 0,
            'price_max'       => $priceRange['max_price'] !== null ? (float) $priceRange['max_price'] : 0,
            'active_search'   => $search,
            'active_brand'    => $brandFilter ? (int) $brandFilter : null,
            'active_category' => $catFilter ? (int) $catFilter : null,
            'active_country'  => $countryFilter,
            'active_sort'     => $sort
        ];
    }

    $response = [
        'success'    => true,
        'products'   => $products,
        'pagination' => [
            'current_page' => $page,
            'per_page'     => $per_page,
            'total_items'  => $totalProducts,
            'total_pages'  => ceil($totalProducts / $per_page) ?: 1,
            'has_next'     => ($page < ceil($totalProducts / $per_page)) ? true : false,
            'has_prev'     => ($page > 1) ? true : false
        ],
        'country_id' => $countryFilter
    ];

    if ($filters !== null) {
        $response['filters'] = $filters;
    }

    jsonResponse($response);

} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Failed to load products. Please try again.'
    ], 500);
}