<?php

/**
 * GET /api/dashboard/recent-signups.php
 *
 * Optional:
 * ?month=7&year=2026
 *
 * Response:
 * {
 *   "month":7,
 *   "year":2026,
 *   "count":2,
 *   "signups":[]
 * }
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/bootstrap.php';
requireLogin();

try {

    // Current month/year by default
    $month = isset($_GET['month']) ? (int) $_GET['month'] : (int) date('n');
    $year  = isset($_GET['year'])  ? (int) $_GET['year']  : (int) date('Y');

    // Validate month/year
    if ($month < 1 || $month > 12) {
        $month = (int) date('n');
    }

    if ($year < 2000 || $year > 2100) {
        $year = (int) date('Y');
    }

    // Month date range
    $monthStart = sprintf('%04d-%02d-01 00:00:00', $year, $month);
    $monthEnd   = date('Y-m-t 23:59:59', strtotime($monthStart));

    // Get recent users
    $stmt = $pdo->prepare("
        SELECT
            name,
            image,
            role,
            status,
            created_at
        FROM users
        WHERE created_at BETWEEN ? AND ?
        ORDER BY created_at DESC
    ");

    $stmt->execute([$monthStart, $monthEnd]);

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $signups = [];

    foreach ($users as $user) {

        $hasImage = !empty($user['image']);

        // Image URL
        $avatar = null;

        if ($hasImage) {

            if (filter_var($user['image'], FILTER_VALIDATE_URL)) {
                $avatar = $user['image'];
            } else {
                $avatar = BASE_URL . '/' . ltrim($user['image'], '/');
            }
        }

        $signups[] = [
            'name'         => $user['name'],
            'avatar'       => $avatar,
            'initials'     => $hasImage ? null : getInitials($user['name']),
            'avatar_color' => $hasImage ? null : initialsColor($user['name']),
            'role'         => ucfirst($user['role']),
            'date'         => date('Y-m-d', strtotime($user['created_at'])),
            'status'       => ((int)$user['status'] === 1) ? 'active' : 'inactive'
        ];
    }

    echo json_encode([
        'month'   => $month,
        'year'    => $year,
        'count'   => count($signups),
        'signups' => $signups
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Failed to load recent signups.',
        'error'   => $e->getMessage()
    ]);
}