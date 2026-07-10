<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']); // both can VIEW the list
header('Content-Type: application/json');

if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Extra safety: block delete here too, even though delete-country.php also checks
    if (currentUserRole() !== 'admin') {
        echo json_encode(["success" => false, "message" => "You don't have permission to delete countries."]);
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM countries WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid ID"]);
    }
    exit;
}

$columns = ['id', 'name', 'slug', 'status', 'currency', 'created_at','code','flag'];

$draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
$searchValue = $_POST['search']['value'] ?? '';
$statusFilter = $_POST['status_filter'] ?? '';

$orderColIndex = $_POST['order'][0]['column'] ?? 0;
$orderDir = strtolower($_POST['order'][0]['dir'] ?? 'desc');
$orderDir = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';
$orderCol = $columns[$orderColIndex] ?? 'created_at';

// --- Determine current user's role once, used to build the actions column ---
$isAdmin = (currentUserRole() === 'admin');

try {
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM countries");
    $totalRecords = (int)$totalStmt->fetchColumn();

    $conditions = [];
    $params = [];

    if (!empty($searchValue)) {
        $conditions[] = "(name LIKE :s OR slug LIKE :s)";
        $params[':s'] = '%' . $searchValue . '%';
    }

    if ($statusFilter !== '') {
        $conditions[] = "status = :status";
        $params[':status'] = (int)$statusFilter;
    }

    $where = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : '';

    $filteredStmt = $pdo->prepare("SELECT COUNT(*) FROM countries $where");
    $filteredStmt->execute($params);
    $filteredRecords = (int)$filteredStmt->fetchColumn();

    $limitClause = "LIMIT :start, :length";
    if ($length == -1) {
        $limitClause = '';
    }

    $sql = "SELECT id, name, slug, status, created_at, currency, code, flag, currency_symbol
            FROM countries
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
    foreach ($rows as $c) {
        $name = htmlspecialchars($c['name']);
        $flagHtml = getFlagHtml($c['name'], $c['flag']);
        $code = getCountryCode($c['name']);
        $codeDisplay = $code ? strtoupper($code) : $c['code'];

        $status = $c['status']
            ? '<span class="sb-badge2 active">Active</span>'
            : '<span class="sb-badge2 draft">Inactive</span>';

        $currency = $c['currency'] ?? '—';
        $currencySymbol = getCurrencySymbol($c['code']) ?? '—';

        // --- Build actions based on role ---
        if ($isAdmin) {
            $actions = "
                <a class='ab text-decoration-none' href='view-country.php?token=" . urlencode(encryptId($c['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
                <a class='ab text-decoration-none' href='edit-country.php?token=" . urlencode(encryptId($c['id'])) . "' title='Edit'><i class='fas fa-pen'></i></a>
                <a class='ab text-decoration-none dng' href='#' onclick='deleteCountry({$c['id']}, " . json_encode($name) . ");return false;' title='Delete'><i class='fas fa-trash'></i></a>
            ";
        } else {
            // Employee — view only
            $actions = "
                <a class='ab text-decoration-none' href='view-country.php?token=" . urlencode(encryptId($c['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
            ";
        }

        $data[] = [
            "name" => "<div class='tu'><span style='margin-right:8px;'>$flagHtml</span><span class='tn'>$name</span></div>",
            "code" => "<code style='background:var(--bg);padding:2px 7px;border-radius:4px;font-size:.78rem;font-weight:600;'>" . $codeDisplay . "</code>",
            "status" => $status,
            "currency_symbol" => $currencySymbol,
            "currency" => $currency,
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