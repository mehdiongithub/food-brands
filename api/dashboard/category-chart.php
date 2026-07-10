<?php
/**
 * GET /api/dashboard/category-chart.php
 * Returns product counts grouped by category, for the "By Category" doughnut chart.
 *
 * Tables used: categories, products
 *
 * Response:
 * { "labels": ["Burgers","Chicken",...], "data": [28,18,...] }
 *
 * Only the top 5 categories (by product count) are returned individually;
 * everything else is grouped into "Others" so the chart stays readable
 * even as you add more categories.
 */

header('Content-Type: application/json');

require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();

if (!isset($pdo)) {
    require_once __DIR__ . "/../../config/db.php"; // fallback if $pdo isn't already set
}

try {

    $sql = "
        SELECT c.name AS category_name, COUNT(p.id) AS product_count
        FROM categories c
        LEFT JOIN products p ON p.category_id = c.id
        WHERE c.status = 1
        GROUP BY c.id, c.name
        HAVING product_count > 0
        ORDER BY product_count DESC
    ";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $data   = [];

    $TOP_N = 5;
    $othersTotal = 0;

    foreach ($rows as $i => $row) {
        if ($i < $TOP_N) {
            $labels[] = $row['category_name'];
            $data[]   = (int) $row['product_count'];
        } else {
            $othersTotal += (int) $row['product_count'];
        }
    }

    if ($othersTotal > 0) {
        $labels[] = 'Others';
        $data[]   = $othersTotal;
    }

    // No categorized products yet — avoid sending an empty chart
    if (empty($labels)) {
        $labels = ['No products yet'];
        $data   = [1];
    }

    echo json_encode([
        'labels' => $labels,
        'data'   => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load category chart', 'message' => $e->getMessage()]);
}