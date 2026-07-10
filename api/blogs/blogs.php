<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']); // both can VIEW the list
header('Content-Type: application/json');

if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Extra safety: block delete here too, even if a separate delete file also checks
    if (currentUserRole() !== 'admin') {
        echo json_encode(["success" => false, "message" => "You don't have permission to delete blogs."]);
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        // Fetch image path first so we can remove the file after DB delete
        $imgStmt = $pdo->prepare("SELECT image FROM blogs WHERE id = :id");
        $imgStmt->execute([':id' => $id]);
        $existing = $imgStmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = :id");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            if (!empty($existing['image'])) {
                $fullPath = __DIR__ . '/../../' . $existing['image'];
                if (file_exists($fullPath) && is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }
            echo json_encode(["success" => true, "message" => "Blog deleted successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Blog not found."]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Invalid ID"]);
    }
    exit;
}

$columns = ['id', 'title', 'category', 'status', 'published_at', 'created_at', 'views'];

$draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
$searchValue = $_POST['search']['value'] ?? '';
$statusFilter = $_POST['status_filter'] ?? '';
$categoryFilter = $_POST['category_filter'] ?? '';

$orderColIndex = $_POST['order'][0]['column'] ?? 0;
$orderDir = strtolower($_POST['order'][0]['dir'] ?? 'desc');
$orderDir = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';
$orderCol = $columns[$orderColIndex] ?? 'created_at';

// --- Determine current user's role once, used to build the actions column ---
$isAdmin = (currentUserRole() === 'admin');

try {
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM blogs");
    $totalRecords = (int)$totalStmt->fetchColumn();

    $conditions = [];
    $params = [];

    if (!empty($searchValue)) {
        $conditions[] = "(title LIKE :s OR category LIKE :s OR excerpt LIKE :s)";
        $params[':s'] = '%' . $searchValue . '%';
    }

    if ($statusFilter !== '') {
        $conditions[] = "status = :status";
        $params[':status'] = (int)$statusFilter;
    }

    if ($categoryFilter !== '') {
        $conditions[] = "category = :category";
        $params[':category'] = $categoryFilter;
    }

    $where = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : '';

    $filteredStmt = $pdo->prepare("SELECT COUNT(*) FROM blogs $where");
    $filteredStmt->execute($params);
    $filteredRecords = (int)$filteredStmt->fetchColumn();

    $limitClause = "LIMIT :start, :length";
    if ($length == -1) {
        $limitClause = '';
    }

    $sql = "SELECT id, title, slug, image, category, status, views, published_at, created_at
            FROM blogs
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
    foreach ($rows as $b) {
        $title = htmlspecialchars($b['title']);
        $category = htmlspecialchars($b['category'] ?? '—');

        $status = $b['status']
            ? '<span class="sb-badge2 active">Published</span>'
            : '<span class="sb-badge2 draft">Draft</span>';

        if (!empty($b['image'])) {
            $avatarHtml = "<img class='ta' src='" . BASE_URL . "/" . htmlspecialchars($b['image']) . "' alt='$title'>";
        } else {
            $avatarHtml = "<div class='ta ta-init' style='background:" . initialsColor($b['title']) . "'>"
                        . getInitials($b['title'])
                        . "</div>";
        }

        $publishedAt = $b['published_at'] ? date('M d, Y', strtotime($b['published_at'])) : '—';

        // --- Build actions based on role ---
        if ($isAdmin) {
            $actions = "
                <a class='ab text-decoration-none' href='view-blog.php?token=" . urlencode(encryptId($b['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
                <a class='ab text-decoration-none' href='edit-blog.php?token=" . urlencode(encryptId($b['id'])) . "' title='Edit'><i class='fas fa-pen'></i></a>
                <a class='ab text-decoration-none dng' href='#' onclick='deleteBlog({$b['id']}, " . json_encode($title) . ");return false;' title='Delete'><i class='fas fa-trash'></i></a>
            ";
        } else {
            // Employee — view only
            $actions = "
                <a class='ab text-decoration-none' href='view-blog.php?token=" . urlencode(encryptId($b['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
            ";
        }

        $data[] = [
            "title" => "<div class='tu'><span style='margin-right:8px;'>$avatarHtml</span><span class='tn'>$title</span></div>",
            "category" => $category,
            "status" => $status,
            "published_at" => $publishedAt,
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