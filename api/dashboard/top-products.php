<?php
/**
 * GET /api/dashboard/top-products.php
 * Returns the top 5 products for the "Top Products" table, ranked by
 * total page views — with featured products given a slight boost so
 * they surface even with fewer views yet.
 *
 * Tables used: products, product_views, brands
 *
 * Response: array of { name, brand, views, trend, change }
 * trend: "up" or "down" (views this week vs last week)
 */

header('Content-Type: application/json');

require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();

if (!isset($pdo)) {
    require_once __DIR__ . "/../../config/db.php"; // fallback if $pdo isn't already set
}

try {

    $LIMIT = 5;

    /* ---------- Rank products by total views, featured products get a tiebreak boost ---------- */
    $stmt = $pdo->prepare("
        SELECT
            p.id,
            p.name,
            p.featured,
            b.name AS brand_name,
            COUNT(pv.id) AS total_views
        FROM products p
        LEFT JOIN product_views pv ON pv.product_id = p.id
        LEFT JOIN brands b ON b.id = p.brand_id
        WHERE p.status = 1
        GROUP BY p.id, p.name, p.featured, b.name
        ORDER BY p.featured DESC, total_views DESC, p.created_at DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $LIMIT, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        echo json_encode([]);
        exit;
    }

    $productIds = array_column($products, 'id');
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));

    /* ---------- Views in the last 7 days, per product ---------- */
    $stmt = $pdo->prepare("
        SELECT product_id, COUNT(*) AS c
        FROM product_views
        WHERE product_id IN ($placeholders)
          AND viewed_at >= (NOW() - INTERVAL 7 DAY)
        GROUP BY product_id
    ");
    $stmt->execute($productIds);
    $thisWeek = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $thisWeek[$row['product_id']] = (int) $row['c'];
    }

    /* ---------- Views in the 7 days before that, per product ---------- */
    $stmt = $pdo->prepare("
        SELECT product_id, COUNT(*) AS c
        FROM product_views
        WHERE product_id IN ($placeholders)
          AND viewed_at >= (NOW() - INTERVAL 14 DAY)
          AND viewed_at <  (NOW() - INTERVAL 7 DAY)
        GROUP BY product_id
    ");
    $stmt->execute($productIds);
    $lastWeek = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $lastWeek[$row['product_id']] = (int) $row['c'];
    }

    /* ---------- Build response ---------- */
    $result = [];
    foreach ($products as $p) {
        $id   = $p['id'];
        $curr = isset($thisWeek[$id]) ? $thisWeek[$id] : 0;
        $prev = isset($lastWeek[$id]) ? $lastWeek[$id] : 0;

        if ($prev == 0) {
            $pct = $curr > 0 ? 100 : 0;
        } else {
            $pct = round((($curr - $prev) / $prev) * 100);
        }

        $result[] = [
            'name'   => $p['name'],
            'brand'  => $p['brand_name'] ? $p['brand_name'] : '—',
            'views'  => number_format((int) $p['total_views']),
            'trend'  => $pct >= 0 ? 'up' : 'down',
            'change' => ($pct >= 0 ? '+' : '') . $pct . '%'
        ];
    }

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load top products', 'message' => $e->getMessage()]);
}