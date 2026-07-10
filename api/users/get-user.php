<?php
/**
 * GET /api/dashboard/recent-signups.php
 * Returns users who registered within a given calendar month
 * (defaults to the current month if no params are passed).
 *
 * Optional query params:
 *   ?month=7&year=2026   -> registrations for July 2026
 *   (no params)          -> registrations for the current month
 *
 * Table used: users
 */

require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']);

header('Content-Type: application/json');

// Only accept GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

/* ---------- Build initials + a stable color for users with no photo ---------- */
$AVATAR_COLORS = [
    '#E85D04', '#059669', '#0891B2', '#D97706',
    '#7C3AED', '#DC2626', '#4F46E5', '#0D9488'
];

function getInitials($name) {
    $name = trim($name);
    if ($name === '') return '?';
    $parts = preg_split('/\s+/', $name);
    if (count($parts) === 1) {
        return strtoupper(substr($parts[0], 0, 2));
    }
    return strtoupper(substr($parts[0], 0, 1) . substr($parts[count($parts) - 1], 0, 1));
}

function getAvatarColor($name, $colors) {
    $index = crc32($name) % count($colors);
    return $colors[$index];
}

try {

    /* ---------- Determine which month to show ---------- */
    $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
    $year  = isset($_GET['year'])  ? (int) $_GET['year']  : (int) date('Y');

    // Basic sanity checks so bad input can't break the date range
    if ($month < 1 || $month > 12) $month = (int) date('n');
    if ($year < 2000 || $year > 2100) $year = (int) date('Y');

    /* ---------- Fetch users registered in that month ---------- */
    // Filtering by MONTH()/YEAR() directly in SQL avoids any PHP/DB timezone mismatch.
    $stmt = $pdo->prepare("
        SELECT name, email, image, role, status, created_at
        FROM users
        WHERE MONTH(created_at) = :month
          AND YEAR(created_at) = :year
        ORDER BY created_at DESC
    ");
    $stmt->execute([
        ':month' => $month,
        ':year'  => $year
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $signups = array_map(function ($u) use ($AVATAR_COLORS) {
        $hasImage = !empty($u['image']);
        return [
            'name'          => $u['name'],
            'avatar'        => $hasImage ? $u['image'] : null,
            'initials'      => $hasImage ? null : getInitials($u['name']),
            'avatar_color'  => $hasImage ? null : getAvatarColor($u['name'], $AVATAR_COLORS),
            'role'          => ucfirst($u['role']),
            'date'          => date('Y-m-d', strtotime($u['created_at'])),
            'status'        => ((int) $u['status'] === 1) ? 'active' : 'draft'
        ];
    }, $rows);

    echo json_encode([
        "success" => true,
        "data" => [
            "month"   => $month,
            "year"    => $year,
            "count"   => count($signups),
            "signups" => $signups
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}