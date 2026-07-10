<?php
/**
 * GET /api/dashboard/stats.php
 * Returns the 4 top stat cards for the dashboard:
 *   Total Products, Active Brands, Registered Users, Active Offers
 *
 * Tables used: products, brands, users, offers
 */

header('Content-Type: application/json');

require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();

// $pdo must be a PDO instance created in config/bootstrap.php (or config/db.php).
// Adjust this if your project uses a different variable/connection name.
if (!isset($pdo)) {
    require_once __DIR__ . "/../../config/db.php"; // fallback if $pdo isn't already set
}

try {

    /* ---------- helper: count rows with an optional WHERE clause ---------- */
    function countRows(PDO $pdo, $table, $where = '1=1', $params = []) {
        $sql = "SELECT COUNT(*) FROM `$table` WHERE $where";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /* ---------- helper: month-over-month % change based on created_at ---------- */
    function monthChange(PDO $pdo, $table, $extraWhere = '1=1', $params = []) {
        $thisMonthStart = date('Y-m-01 00:00:00');
        $lastMonthStart = date('Y-m-01 00:00:00', strtotime('first day of last month'));
        $lastMonthEnd   = date('Y-m-t 23:59:59', strtotime('last month'));

        $thisCount = countRows(
            $pdo, $table,
            "$extraWhere AND created_at >= ?",
            array_merge($params, [$thisMonthStart])
        );

        $lastCount = countRows(
            $pdo, $table,
            "$extraWhere AND created_at BETWEEN ? AND ?",
            array_merge($params, [$lastMonthStart, $lastMonthEnd])
        );

        if ($lastCount == 0) {
            $pct = $thisCount > 0 ? 100 : 0;
        } else {
            $pct = round((($thisCount - $lastCount) / $lastCount) * 100);
        }

        return [
            'trend'   => $pct >= 0 ? 'up' : 'down',
            'text'    => ($pct >= 0 ? '+' : '') . $pct . '%',
            'new'     => $thisCount
        ];
    }

    /* ---------- Total Products ---------- */
    $totalProducts   = countRows($pdo, 'products');
    $productsChange  = monthChange($pdo, 'products');

    /* ---------- Active Brands ---------- */
    $activeBrands    = countRows($pdo, 'brands', 'status = 1');
    $brandsChange    = monthChange($pdo, 'brands', 'status = 1');

    /* ---------- Registered Users ---------- */
    $registeredUsers = countRows($pdo, 'users');
    $usersChange     = monthChange($pdo, 'users');

    /* ---------- Active Offers (status = 1 and not expired) ---------- */
    $activeOffers    = countRows($pdo, 'offers', 'status = 1 AND (end_date IS NULL OR end_date >= CURDATE())');
    $expiredOffers   = countRows($pdo, 'offers', '(status = 0) OR (end_date IS NOT NULL AND end_date < CURDATE())');

    /* ---------- Summary line ---------- */
    $summary = "You have {$activeOffers} active offer" . ($activeOffers == 1 ? '' : 's') .
               " and {$usersChange['new']} new user" . ($usersChange['new'] == 1 ? '' : 's') .
               " this month to review.";

    echo json_encode([
        'total_products'           => $totalProducts,
        'total_products_change'    => $productsChange['text'],
        'total_products_trend'     => $productsChange['trend'],

        'active_brands'            => $activeBrands,
        'active_brands_change'     => '+' . $brandsChange['new'] . ' new',
        'active_brands_trend'      => 'up',

        'registered_users'         => $registeredUsers,
        'registered_users_change'  => $usersChange['text'],
        'registered_users_trend'   => $usersChange['trend'],

        'active_offers'            => $activeOffers,
        'active_offers_change'     => '-' . $expiredOffers . ' expired',
        'active_offers_trend'      => 'down',

        'summary'                  => $summary
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load stats', 'message' => $e->getMessage()]);
}