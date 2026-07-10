<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']); // both can VIEW the list
header('Content-Type: application/json');


$columns = ['id', 'name', 'designation', 'image', 'review', 'rating', 'status', 'created_at'];

$draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
$searchValue = $_POST['search']['value'] ?? '';
$statusFilter = $_POST['status_filter'] ?? '';
$ratingFilter = $_POST['rating_filter'] ?? '';

$orderColIndex = $_POST['order'][0]['column'] ?? 0;
$orderDir = strtolower($_POST['order'][0]['dir'] ?? 'desc');
$orderDir = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';
$orderCol = $columns[$orderColIndex] ?? 'created_at';

// --- Determine current user's role once, used to build the actions column ---
$isAdmin = (currentUserRole() === 'admin');

try {
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM testimonials");
    $totalRecords = (int)$totalStmt->fetchColumn();

    $conditions = [];
    $params = [];

    if (!empty($searchValue)) {
        $conditions[] = "(name LIKE :s OR designation LIKE :s OR review LIKE :s)";
        $params[':s'] = '%' . $searchValue . '%';
    }

    if ($statusFilter !== '') {
        $conditions[] = "status = :status";
        $params[':status'] = (int)$statusFilter;
    }

    if ($ratingFilter !== '') {
        $conditions[] = "rating = :rating";
        $params[':rating'] = (int)$ratingFilter;
    }

    $where = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : '';

    $filteredStmt = $pdo->prepare("SELECT COUNT(*) FROM testimonials $where");
    $filteredStmt->execute($params);
    $filteredRecords = (int)$filteredStmt->fetchColumn();

    $limitClause = "LIMIT :start, :length";
    if ($length == -1) {
        $limitClause = '';
    }

    $sql = "SELECT id, name, designation, image, review, rating, status, created_at
            FROM testimonials
            $where
            ORDER BY $orderCol $orderDir
            $limitClause";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    if ($length != -1) {
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    }
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($rows as $t) {
        $name = htmlspecialchars($t['name'] ?? '');
        $designation = htmlspecialchars($t['designation'] ?? '');

        $status = $t['status']
            ? '<span class="sb-badge2 active">Active</span>'
            : '<span class="sb-badge2 draft">Inactive</span>';

        if (!empty($t['image'])) {
            $avatarHtml = "<img class='ta' src='" . BASE_URL . "/" . htmlspecialchars($t['image']) . "' alt='$name'>";
        } else {
            $avatarHtml = "<div class='ta ta-init' style='background:" . initialsColor($t['name'] ?? '') . "'>"
                        . getInitials($t['name'] ?? '')
                        . "</div>";
        }

        // --- Star rating display ---
        $rating = (int)($t['rating'] ?? 0);
        $starsHtml = '';
        for ($i = 1; $i <= 5; $i++) {
            $starsHtml .= $i <= $rating
                ? "<i class='fas fa-star' style='color:#F5A623;'></i>"
                : "<i class='far fa-star' style='color:#D1D5DB;'></i>";
        }

        // --- Truncated review preview ---
        $reviewPreview = htmlspecialchars(mb_strimwidth($t['review'] ?? '', 0, 80, '...'));

        $createdAt = $t['created_at'] ? date('M d, Y', strtotime($t['created_at'])) : '';

        // --- Build actions based on role ---
        if ($isAdmin) {
            $actions = "
                <a class='ab text-decoration-none' href='view-testimonial.php?token=" . urlencode(encryptId($t['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
                <a class='ab text-decoration-none' href='edit-testimonial.php?token=" . urlencode(encryptId($t['id'])) . "' title='Edit'><i class='fas fa-pen'></i></a>
                <a class='ab text-decoration-none dng' href='#' onclick='deleteTestimonial({$t['id']}, " . json_encode($name) . ");return false;' title='Delete'><i class='fas fa-trash'></i></a>
            ";
        } else {
            // Employee — view only
            $actions = "
                <a class='ab text-decoration-none' href='view-testimonial.php?token=" . urlencode(encryptId($t['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
            ";
        }

        $data[] = [
            "name" => "<div class='tu'><span style='margin-right:8px;'>$avatarHtml</span><span class='tn'>$name<br><small class='text-muted'>$designation</small></span></div>",
            "review" => $reviewPreview,
            "rating" => "<span class='stars-wrap'>$starsHtml</span>",
            "status" => $status,
            "created_at" => $createdAt,
            "actions" => $actions
        ];
    }

    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $filteredRecords,
        "data" => $data
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}