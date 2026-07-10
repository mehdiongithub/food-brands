<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']);

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'data'    => null
];

try {

    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id <= 0) {
        $response['message'] = 'Invalid FAQ ID.';
        echo json_encode($response);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT
            id,
            question,
            answer,
            sort_order,
            status,
            created_at
        FROM faqs
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $id
    ]);

    $faq = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$faq) {
        $response['message'] = 'FAQ not found.';
        echo json_encode($response);
        exit;
    }

    $response['success'] = true;

    $response['data'] = [
        'id'         => (int)$faq['id'],
        'question'   => $faq['question'],
        'answer'     => $faq['answer'],
        'sort_order' => (int)$faq['sort_order'],
        'status'     => (int)$faq['status'],
        'created_at' => $faq['created_at'],
        'updated_at' => $faq['created_at']
    ];

} catch (PDOException $e) {

    $response['message'] = 'Database error.';
    // For debugging:
    // $response['message'] = $e->getMessage();

}

echo json_encode($response);