<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']);

header('Content-Type: application/json');

// --- Handle delete action separately ---
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid ID"]);
    }
    exit;
}

/**
 * Get initials from a full name — first letter of first + last word.
 * "Alex Kumar" -> "AK", "Madonna" -> "M"
 */
function getInitials($name) {
    $name = trim($name);
    if ($name === '') return '?';

    $parts = preg_split('/\s+/', $name);
    if (count($parts) === 1) {
        return mb_strtoupper(mb_substr($parts[0], 0, 1));
    }
    $first = mb_substr($parts[0], 0, 1);
    $last  = mb_substr($parts[count($parts) - 1], 0, 1);
    return mb_strtoupper($first . $last);
}

/**
 * Deterministic color based on name, so the same person always
 * gets the same avatar color (not random on every page load).
 */
function initialsColor($name) {
    $colors = [
        '#E85D04', '#059669', '#0891B2', '#7C3AED',
        '#D97706', '#DC2626', '#2563EB', '#DB2777'
    ];
    $hash = 0;
    foreach (str_split($name) as $char) {
        $hash += ord($char);
    }
    return $colors[$hash % count($colors)];
}

// The user currently logged in — excluded from the list below along
// with anyone whose role is 'admin'. Adjust the session key if your
// auth flow stores the logged-in user's ID somewhere else.
$currentUserId = (int) ($_SESSION['user_id'] ?? 0);

$columns = ['id', 'name', 'email', 'role', 'status', 'created_at'];

$draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
$searchValue = $_POST['search']['value'] ?? '';
$statusFilter = $_POST['status_filter'] ?? '';

$orderColIndex = $_POST['order'][0]['column'] ?? 0;
$orderDir = strtolower($_POST['order'][0]['dir'] ?? 'desc');
$orderDir = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';
$orderCol = $columns[$orderColIndex] ?? 'created_at';

try {
    // --- Base exclusion: no admins, no the logged-in user themself ---
    // Applied to BOTH the total count and the filtered count, so it's
    // always in effect regardless of search/status filters.
    $baseWhere  = "WHERE role != :adminRole AND id != :currentUserId";
    $baseParams = [
        ':adminRole'     => 'admin',
        ':currentUserId' => $currentUserId
    ];

    $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM users $baseWhere");
    $totalStmt->execute($baseParams);
    $totalRecords = (int) $totalStmt->fetchColumn();

    // --- Build WHERE clause dynamically (base exclusion + search/status) ---
    $conditions = [
        "role != :adminRole",
        "id != :currentUserId"
    ];
    $params = $baseParams;

    if (!empty($searchValue)) {
        $conditions[] = "(name LIKE :s OR email LIKE :s OR role LIKE :s)";
        $params[':s'] = '%' . $searchValue . '%';
    }

    if ($statusFilter !== '') {
        $conditions[] = "status = :status";
        $params[':status'] = (int)$statusFilter;
    }

    $where = "WHERE " . implode(" AND ", $conditions);

    // --- Filtered count ---
    $filteredStmt = $pdo->prepare("SELECT COUNT(*) FROM users $where");
    $filteredStmt->execute($params);
    $filteredRecords = (int)$filteredStmt->fetchColumn();

    // --- Handle "All" page length (-1) ---
    $limitClause = "LIMIT :start, :length";
    if ($length == -1) {
        $limitClause = ''; // no limit — return all filtered rows
    }

    $sql = "SELECT id, name, email, phone, image, role, status, created_at
            FROM users
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
    foreach ($rows as $u) {
        $name   = htmlspecialchars($u['name']);
        $email  = htmlspecialchars($u['email']);
        $role   = htmlspecialchars($u['role']);
        $status = $u['status']
            ? '<span class="sb-badge2 active">Active</span>'
            : '<span class="sb-badge2 draft">Inactive</span>';
        $joined = date('Y-m-d', strtotime($u['created_at']));

        // --- Avatar: real image if set, otherwise initials circle ---
        if (!empty($u['image'])) {
            $avatarHtml = "<img class='ta' src='" . BASE_URL . "/" . htmlspecialchars($u['image']) . "' alt='$name'>";
        } else {
            $avatarHtml = "<div class='ta ta-init' style='background:" . initialsColor($u['name']) . "'>"
                        . getInitials($u['name'])
                        . "</div>";
        }

        $data[] = [
            "user" => "<div class='tu'>$avatarHtml
                        <div><div class='tn'>$name</div><div class='ts'>$email</div></div></div>",
            "role" => $role,
            "status" => $status,
            "joined" => $joined,
            "actions" => "
                <a class='ab text-decoration-none' href='view-user.php?id={$u['id']}' title='View'><i class='fas fa-eye'></i></a>
                <a class='ab text-decoration-none' href='edit-user.php?id={$u['id']}' title='Edit'><i class='fas fa-pen'></i></a>
                <a class='ab dng' href='#' onclick='deleteUser({$u['id']}, " . json_encode($name) . ");return false;' title='Delete'><i class='fas fa-trash'></i></a>
            "

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