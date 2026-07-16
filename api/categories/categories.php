<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']); // both can VIEW the list
header('Content-Type: application/json');

if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Extra safety: block delete here too, even though delete-category.php also checks
    if (currentUserRole() !== 'admin') {
        echo json_encode(["success" => false, "message" => "You don't have permission to delete categories."]);
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid ID"]);
    }
    exit;
}

// Must match the client-side `columns:` order in admin/categories/index.php:
// 0 = Name, 1 = Parent (not orderable), 2 = Status, 3 = Actions (not orderable)
$columns = ['name', 'parent_id', 'status', 'created_at'];

$draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
$searchValue = $_POST['search']['value'] ?? '';
$statusFilter = $_POST['status_filter'] ?? '';
$parentFilter = $_POST['parent_filter'] ?? ''; // '', 'parent' (top-level only), 'child' (children only)

$orderColIndex = $_POST['order'][0]['column'] ?? 0;
$orderDir = strtolower($_POST['order'][0]['dir'] ?? 'desc');
$orderDir = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';
$orderCol = $columns[$orderColIndex] ?? 'created_at';

// --- Determine current user's role once, used to build the actions column ---
$isAdmin = (currentUserRole() === 'admin');

try {
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM categories");
    $totalRecords = (int)$totalStmt->fetchColumn();

    $conditions = [];
    $params = [];

    if (!empty($searchValue)) {
        $conditions[] = "(c.name LIKE :s OR c.slug LIKE :s)";
        $params[':s'] = '%' . $searchValue . '%';
    }

    if ($statusFilter !== '') {
        $conditions[] = "c.status = :status";
        $params[':status'] = (int)$statusFilter;
    }

    if ($parentFilter === 'parent') {
        $conditions[] = "c.parent_id IS NULL";
    } elseif ($parentFilter === 'child') {
        $conditions[] = "c.parent_id IS NOT NULL";
    }

    $where = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : '';

    $filteredStmt = $pdo->prepare("SELECT COUNT(*) FROM categories c $where");
    $filteredStmt->execute($params);
    $filteredRecords = (int)$filteredStmt->fetchColumn();

    $limitClause = "LIMIT :start, :length";
    if ($length == -1) {
        $limitClause = '';
    }

    $sql = "SELECT c.id, c.name, c.slug, c.status, c.created_at, c.description, c.sort_order, c.image,
                   c.parent_id, p.name AS parent_name
            FROM categories c
            LEFT JOIN categories p ON p.id = c.parent_id
            $where
            ORDER BY c.$orderCol $orderDir
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
    foreach ($rows as $c) {
        $name = htmlspecialchars($c['name']);
        
        $status = $c['status']
            ? '<span class="sb-badge2 active">Active</span>'
            : '<span class="sb-badge2 draft">Inactive</span>';
        $sort_order = $c['sort_order'];

        $parentHtml = $c['parent_id']
            ? '<span class="sb-badge2">' . htmlspecialchars($c['parent_name'] ?? 'Unknown') . '</span>'
            : '<span class="sb-badge2 draft">Top Level</span>';
        if (!empty($c['image'])) {
            $avatarHtml = "<img class='ta' src='" . BASE_URL . "/" . htmlspecialchars($c['image']) . "' alt='$name'>";
        } else {
            $avatarHtml = "<div class='ta ta-init' style='background:" . initialsColor($c['name']) . "'>"
                        . getInitials($c['name'])
                        . "</div>";
        }

        // --- Build actions based on role ---
        if ($isAdmin) {
            $actions = "
                <a class='ab text-decoration-none' href='view-category.php?token=" . urlencode(encryptId($c['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
                <a class='ab text-decoration-none' href='edit-category.php?token=" . urlencode(encryptId($c['id'])) . "' title='Edit'><i class='fas fa-pen'></i></a>
                <a class='ab text-decoration-none dng' href='#' onclick='deleteCategory({$c['id']}, " . json_encode($name) . ");return false;' title='Delete'><i class='fas fa-trash'></i></a>
            ";
        } else {
            // Employee — view only
            $actions = "
                <a class='ab text-decoration-none' href='view-category.php?token=" . urlencode(encryptId($c['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
            ";
        }

        $data[] = [
            "name" => "<div class='tu'><span style='margin-right:8px;'>$avatarHtml</span><span class='tn'>$name</span></div>",
            "parent" => $parentHtml,
            "status" => $status,
            "sort_order" => $sort_order,
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