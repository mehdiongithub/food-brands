<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']);

header('Content-Type: application/json');

$columns = ['id', 'question', 'sort_order', 'status', 'created_at'];

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

    // Total Records
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM faqs");
    $totalRecords = (int)$totalStmt->fetchColumn();

    $conditions = [];
    $params = [];

    // Search
    if (!empty($searchValue)) {
        $conditions[] = "(question LIKE :search OR answer LIKE :search)";
        $params[':search'] = "%{$searchValue}%";
    }

    // Status Filter
    if ($statusFilter !== '') {
        $conditions[] = "status = :status";
        $params[':status'] = (int)$statusFilter;
    }

    $where = count($conditions)
        ? "WHERE " . implode(" AND ", $conditions)
        : "";

    // Filtered Count
    $filteredStmt = $pdo->prepare("SELECT COUNT(*) FROM faqs $where");
    $filteredStmt->execute($params);
    $filteredRecords = (int)$filteredStmt->fetchColumn();

    // Pagination
    $limitClause = $length == -1 ? "" : "LIMIT :start,:length";

    $sql = "
        SELECT *
        FROM faqs
        $where
        ORDER BY $orderCol $orderDir
        $limitClause
    ";

    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue(
            $key,
            $value,
            is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
        );
    }

    if ($length != -1) {
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    }

    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];

    foreach ($rows as $faq) {

        $question = htmlspecialchars($faq['question']);
        if (strlen($question) > 100) {
            $question = substr($question, 0, 60) . "...";
        }
        $answer = strip_tags($faq['answer']);
        if (strlen($answer) > 100) {
            $answer = substr($answer, 0, 60) . "...";
        }

        $status = $faq['status']
            ? '<span class="sb-badge2 active">Active</span>'
            : '<span class="sb-badge2 draft">Inactive</span>';

        $created = date('Y-m-d', strtotime($faq['created_at']));

        $data[] = [

            "question" =>
                "<div>
                    <div class='tn'>{$question}</div>
                    <div class='ts'>{$answer}</div>
                </div>",

            "sort_order" => $faq['sort_order'],

            "status" => $status,

            "created_at" => $created,

            "actions" => "
                <a class='ab text-decoration-none' href='view-faq.php?token=" . urlencode(encryptId($faq['id'])) . "' title='View'>
                    <i class='fas fa-eye'></i>
                </a>

                <a class='ab text-decoration-none' href='edit-faq.php?token=" . urlencode(encryptId($faq['id'])) . "' title='Edit'>
                    <i class='fas fa-pen'></i>
                </a>

                <a class='ab dng' href='#'
                    onclick='deleteFaq({$faq['id']}, ".json_encode($question).");return false;'
                    title='Delete'>
                    <i class='fas fa-trash'></i>
                </a>
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

    echo json_encode([
        "error" => $e->getMessage()
    ]);
}