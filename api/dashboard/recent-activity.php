<?php
/**
 * GET /api/dashboard/recent-activity.php
 * Returns a merged, time-sorted feed of recent platform activity:
 *   - new user registrations   (users)
 *   - new products added       (products, joined to brands)
 *   - new offers created       (offers, joined to brands)
 *   - blog posts published     (blogs)
 *
 * Response: array of { color, text, time }
 * color: g = green (user), b = blue (product/blog), o = orange (offer)
 */

header('Content-Type: application/json');

require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();

if (!isset($pdo)) {
    require_once __DIR__ . "/../../config/db.php"; // fallback if $pdo isn't already set
}

try {

    $LIMIT_PER_SOURCE = 5;
    $LIMIT_TOTAL      = 8;

    $items = [];

    /* ---------- New users ---------- */
    $stmt = $pdo->prepare("SELECT name, created_at FROM users ORDER BY created_at DESC LIMIT ?");
    $stmt->bindValue(1, $LIMIT_PER_SOURCE, PDO::PARAM_INT);
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $items[] = [
            'color'      => 'g',
            'text'       => '<strong>' . htmlspecialchars($row['name']) . '</strong> registered as a new user',
            'created_at' => $row['created_at']
        ];
    }

    /* ---------- New products ---------- */
    $stmt = $pdo->prepare("
        SELECT p.name, b.name AS brand_name, p.created_at
        FROM products p
        LEFT JOIN brands b ON b.id = p.brand_id
        ORDER BY p.created_at DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $LIMIT_PER_SOURCE, PDO::PARAM_INT);
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $brand = $row['brand_name'] ? $row['brand_name'] : 'a brand';
        $items[] = [
            'color'      => 'b',
            'text'       => 'New product <strong>' . htmlspecialchars($row['name']) . '</strong> added to ' . htmlspecialchars($brand),
            'created_at' => $row['created_at']
        ];
    }

    /* ---------- New offers ---------- */
    $stmt = $pdo->prepare("
        SELECT o.title, b.name AS brand_name, o.created_at
        FROM offers o
        LEFT JOIN brands b ON b.id = o.brand_id
        ORDER BY o.created_at DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $LIMIT_PER_SOURCE, PDO::PARAM_INT);
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $brand = $row['brand_name'] ? $row['brand_name'] : 'a brand';
        $items[] = [
            'color'      => 'o',
            'text'       => 'Offer <strong>' . htmlspecialchars($row['title']) . '</strong> created for ' . htmlspecialchars($brand),
            'created_at' => $row['created_at']
        ];
    }

    /* ---------- Blog posts ---------- */
    $stmt = $pdo->prepare("SELECT title, created_at FROM blogs ORDER BY created_at DESC LIMIT ?");
    $stmt->bindValue(1, $LIMIT_PER_SOURCE, PDO::PARAM_INT);
    $stmt->execute();
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $items[] = [
            'color'      => 'b',
            'text'       => 'Blog post <strong>' . htmlspecialchars($row['title']) . '</strong> published',
            'created_at' => $row['created_at']
        ];
    }

    /* ---------- Merge, sort by newest first, trim ---------- */
    usort($items, function ($a, $b) {
        return strtotime($b['created_at']) <=> strtotime($a['created_at']);
    });
    $items = array_slice($items, 0, $LIMIT_TOTAL);

    /* ---------- Convert timestamps to "x ago" and strip created_at ---------- */
    function timeAgo($datetime) {
        $diff = time() - strtotime($datetime);
        if ($diff < 60)   return 'just now';
        if ($diff < 3600) return floor($diff / 60) . ' min ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hour' . (floor($diff / 3600) == 1 ? '' : 's') . ' ago';
        if ($diff < 172800) return 'Yesterday';
        if ($diff < 604800) return floor($diff / 86400) . ' days ago';
        return date('M j, Y', strtotime($datetime));
    }

    $result = array_map(function ($item) {
        return [
            'color' => $item['color'],
            'text'  => $item['text'],
            'time'  => timeAgo($item['created_at'])
        ];
    }, $items);

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load recent activity', 'message' => $e->getMessage()]);
}