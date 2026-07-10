<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']);

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'errors'  => []
];

try {

    $id         = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $question   = trim($_POST['question'] ?? '');
    $answer     = trim($_POST['answer'] ?? '');
    $sort_order = trim($_POST['sort_order'] ?? '');
    $status     = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    /*
    |--------------------------------------------------------------------------
    | Validate ID
    |--------------------------------------------------------------------------
    */

    if ($id <= 0) {
        $response['message'] = 'Invalid FAQ ID.';
        echo json_encode($response);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Check FAQ Exists
    |--------------------------------------------------------------------------
    */

    $check = $pdo->prepare("SELECT id FROM faqs WHERE id = ?");
    $check->execute([$id]);

    if (!$check->fetch()) {
        $response['message'] = 'FAQ not found.';
        echo json_encode($response);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($question == '') {
        $response['errors']['question'] = 'Question is required.';
    } elseif (strlen($question) > 255) {
        $response['errors']['question'] = 'Question cannot exceed 255 characters.';
    }

    if ($answer == '') {
        $response['errors']['answer'] = 'Answer is required.';
    }

    if ($sort_order === '') {
        $sort_order = 0;
    }

    if (!is_numeric($sort_order)) {
        $response['errors']['sort_order'] = 'Sort Order must be numeric.';
    }

    if (!in_array($status, [0, 1])) {
        $status = 1;
    }

    /*
    |--------------------------------------------------------------------------
    | Duplicate Question Check
    |--------------------------------------------------------------------------
    */

    if (empty($response['errors'])) {

        $duplicate = $pdo->prepare("
            SELECT id
            FROM faqs
            WHERE question = ?
            AND id != ?
            LIMIT 1
        ");

        $duplicate->execute([
            $question,
            $id
        ]);

        if ($duplicate->fetch()) {
            $response['errors']['question'] = 'This question already exists.';
        }

    }

    if (!empty($response['errors'])) {

        $response['message'] = 'Please correct the highlighted errors.';
        echo json_encode($response);
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Update FAQ
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE faqs
        SET
            question   = :question,
            answer     = :answer,
            sort_order = :sort_order,
            status     = :status
        WHERE id = :id
    ");

    $stmt->execute([
        ':question'   => $question,
        ':answer'     => $answer,
        ':sort_order' => (int)$sort_order,
        ':status'     => $status,
        ':id'         => $id
    ]);

    $response['success'] = true;
    $response['message'] = 'FAQ updated successfully.';

} catch (PDOException $e) {

    $response['message'] = 'Database error.';
}

echo json_encode($response);