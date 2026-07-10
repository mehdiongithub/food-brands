<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']); // both can VIEW the list
header('Content-Type: application/json');

if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Extra safety: block delete here too, even if a separate delete file also checks
    if (currentUserRole() !== 'admin') {
        echo json_encode(["success" => false, "message" => "You don't have permission to delete offers."]);
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        // Fetch image path first so we can remove the file after DB delete
        $imgStmt = $pdo->prepare("SELECT image FROM offers WHERE id = :id");
        $imgStmt->execute([':id' => $id]);
        $existing = $imgStmt->fetch(PDO::FETCH_ASSOC);

        try {
            $pdo->beginTransaction();

            // Remove relationship rows first
            $pdo->prepare("DELETE FROM offer_countries WHERE offer_id = :id")->execute([':id' => $id]);

            $stmt = $pdo->prepare("DELETE FROM offers WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $deleted = $stmt->rowCount() > 0;

            $pdo->commit();

            if ($deleted) {
                if (!empty($existing['image'])) {
                    $fullPath = __DIR__ . '/../../' . $existing['image'];
                    if (file_exists($fullPath) && is_file($fullPath)) {
                        @unlink($fullPath);
                    }
                }
                echo json_encode(["success" => true, "message" => "Offer deleted successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Offer not found."]);
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Invalid ID"]);
    }
    exit;
}

$columns = ['id', 'title', 'brand_name', 'discount_percent', 'coupon_code', 'start_date', 'end_date', 'status', 'created_at'];

$draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
$searchValue = $_POST['search']['value'] ?? '';
$statusFilter = $_POST['status_filter'] ?? '';
$brandFilter = $_POST['brand_filter'] ?? '';
$countryFilter = $_POST['country_filter'] ?? '';

$orderColIndex = $_POST['order'][0]['column'] ?? 0;
$orderDir = strtolower($_POST['order'][0]['dir'] ?? 'desc');
$orderDir = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';
$orderCol = $columns[$orderColIndex] ?? 'created_at';

// --- Determine current user's role once, used to build the actions column ---
$isAdmin = (currentUserRole() === 'admin');

try {
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM offers");
    $totalRecords = (int)$totalStmt->fetchColumn();

    $conditions = [];
    $params = [];
    $joinCountry = '';

    if (!empty($searchValue)) {
        $conditions[] = "(o.title LIKE :s OR o.coupon_code LIKE :s OR b.name LIKE :s)";
        $params[':s'] = '%' . $searchValue . '%';
    }

    if ($statusFilter !== '') {
        $conditions[] = "o.status = :status";
        $params[':status'] = (int)$statusFilter;
    }

    if ($brandFilter !== '') {
        $conditions[] = "o.brand_id = :brand_id";
        $params[':brand_id'] = (int)$brandFilter;
    }

    if ($countryFilter !== '') {
        $joinCountry = "INNER JOIN offer_countries oc ON oc.offer_id = o.id";
        $conditions[] = "oc.country_id = :country_id";
        $params[':country_id'] = (int)$countryFilter;
    }

    $where = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : '';

    $filteredStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT o.id)
        FROM offers o
        LEFT JOIN brands b ON b.id = o.brand_id
        $joinCountry
        $where
    ");
    $filteredStmt->execute($params);
    $filteredRecords = (int)$filteredStmt->fetchColumn();

    $limitClause = "LIMIT :start, :length";
    if ($length == -1) {
        $limitClause = '';
    }

    $orderColSql = ($orderCol === 'brand_name') ? 'b.name' : "o.$orderCol";

    $sql = "SELECT DISTINCT o.id, o.title, o.slug, o.image, o.discount_percent, o.coupon_code,
                   o.start_date, o.end_date, o.status, o.created_at, o.brand_id,
                   b.name AS brand_name
            FROM offers o
            LEFT JOIN brands b ON b.id = o.brand_id
            $joinCountry
            $where
            ORDER BY $orderColSql $orderDir
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

    $today = date('Y-m-d');

    $data = [];
    foreach ($rows as $o) {
        $title = htmlspecialchars($o['title']);
        $brandName = htmlspecialchars($o['brand_name'] ?? '—');

        $status = $o['status']
            ? '<span class="sb-badge2 active">Active</span>'
            : '<span class="sb-badge2 draft">Inactive</span>';

        // --- Expired badge, shown alongside status when end_date has passed ---
        if (!empty($o['end_date']) && $o['end_date'] < $today) {
            $status .= ' <span class="sb-badge2 draft" style="margin-left:4px;">Expired</span>';
        }

        if (!empty($o['image'])) {
            $avatarHtml = "<img class='ta' src='" . BASE_URL . "/" . htmlspecialchars($o['image']) . "' alt='$title'>";
        } else {
            $avatarHtml = "<div class='ta ta-init' style='background:" . initialsColor($o['title']) . "'>"
                        . getInitials($o['title'])
                        . "</div>";
        }

        $discount = $o['discount_percent'] !== null ? number_format((float)$o['discount_percent'], 2) . '%' : '—';
        $coupon = !empty($o['coupon_code']) ? "<code>" . htmlspecialchars($o['coupon_code']) . "</code>" : '—';

        $dateRange = '—';
        if (!empty($o['start_date']) || !empty($o['end_date'])) {
            $s = $o['start_date'] ? date('M d, Y', strtotime($o['start_date'])) : '—';
            $e = $o['end_date'] ? date('M d, Y', strtotime($o['end_date'])) : '—';
            $dateRange = "$s &rarr; $e";
        }

        $createdAt = $o['created_at'] ? date('M d, Y', strtotime($o['created_at'])) : '';

        // --- Build actions based on role ---
        if ($isAdmin) {
            $actions = "
                <a class='ab text-decoration-none' href='view-offer.php?token=" . urlencode(encryptId($o['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
                <a class='ab text-decoration-none' href='edit-offer.php?token=" . urlencode(encryptId($o['id'])) . "' title='Edit'><i class='fas fa-pen'></i></a>
                <a class='ab text-decoration-none dng' href='#' onclick='deleteOffer({$o['id']}, " . json_encode($title) . ");return false;' title='Delete'><i class='fas fa-trash'></i></a>
            ";
        } else {
            // Employee — view only
            $actions = "
                <a class='ab text-decoration-none' href='view-offer.php?token=" . urlencode(encryptId($o['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
            ";
        }

        $data[] = [
            "title" => "<div class='tu'><span style='margin-right:8px;'>$avatarHtml</span><span class='tn'>$title<br><small class='text-muted'>$brandName</small></span></div>",
            "discount_percent" => $discount,
            "coupon_code" => $coupon,
            "dates" => $dateRange,
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