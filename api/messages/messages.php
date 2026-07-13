<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']); // both can VIEW the list
header('Content-Type: application/json');

// Maps DataTable column index (as rendered in the table) to a real DB column for ORDER BY.
// Table columns are: 0 Name, 1 Email, 2 Phone, 3 Subject, 4 Status, 5 Received, 6 Actions
$columns = ['name', 'email', 'phone', 'subject', 'status', 'created_at', null];

$draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
$searchValue = $_POST['search']['value'] ?? '';
$statusFilter = $_POST['status_filter'] ?? '';

$orderColIndex = $_POST['order'][0]['column'] ?? 5;
$orderDir = strtolower($_POST['order'][0]['dir'] ?? 'desc');
$orderDir = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';
$orderCol = $columns[$orderColIndex] ?? null;
if (!$orderCol) {
    $orderCol = 'created_at'; // fallback for the non-orderable Actions column
}

// --- Determine current user's role once, used to build the actions column ---
$isAdmin = (currentUserRole() === 'admin');

try {
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM contact_messages");
    $totalRecords = (int)$totalStmt->fetchColumn();

    $conditions = [];
    $params = [];

    if (!empty($searchValue)) {
        $conditions[] = "(name LIKE :s OR email LIKE :s OR subject LIKE :s OR message LIKE :s)";
        $params[':s'] = '%' . $searchValue . '%';
    }

    if ($statusFilter !== '') {
        $conditions[] = "status = :status";
        $params[':status'] = $statusFilter;
    }

    $where = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : '';

    $filteredStmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages $where");
    $filteredStmt->execute($params);
    $filteredRecords = (int)$filteredStmt->fetchColumn();

    $limitClause = "LIMIT :start, :length";
    if ($length == -1) {
        $limitClause = '';
    }

    $sql = "SELECT id, name, email, phone, subject, message, status, created_at
            FROM contact_messages
            $where
            ORDER BY $orderCol $orderDir
            $limitClause";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, PDO::PARAM_STR);
    }
    if ($length != -1) {
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    }
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $statusBadges = [
        'new'     => "<span class='sb-badge2' style='background:rgba(37,99,235,.1);color:#2563EB;'>New</span>",
        'read'    => "<span class='sb-badge2 draft'>Read</span>",
        'replied' => "<span class='sb-badge2 active'>Replied</span>",
    ];

    $data = [];
    foreach ($rows as $m) {
        $name    = htmlspecialchars($m['name'] ?? '');
        $email   = htmlspecialchars($m['email'] ?? '');
        $subject = htmlspecialchars(mb_strimwidth($m['subject'] ?? '', 0, 60, '...'));
        $status  = $statusBadges[$m['status']] ?? $statusBadges['new'];
        $createdAt = $m['created_at'] ? date('M d, Y h:i A', strtotime($m['created_at'])) : '';

        // --- Build actions based on role ---
        if ($isAdmin) {
            $actions = "
                <a class='ab text-decoration-none' href='view-message.php?token=" . urlencode(encryptId($m['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
                <a class='ab text-decoration-none dng' href='#' onclick='deleteMessage({$m['id']}, " . json_encode($name) . ");return false;' title='Delete'><i class='fas fa-trash'></i></a>
            ";
        } else {
            // Employee — view only
            $actions = "
                <a class='ab text-decoration-none' href='view-message.php?token=" . urlencode(encryptId($m['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
            ";
        }

        $data[] = [
            "name"       => "<div class='tu'><span class='tn'>$name</span></div>",
            "email"      => $email,
            "phone"      => htmlspecialchars($m['phone'] ?? '—'),
            "subject"    => $subject,
            "status"     => $status,
            "created_at" => $createdAt,
            "actions"    => $actions
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
