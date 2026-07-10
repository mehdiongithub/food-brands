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

    $question   = trim($_POST['question'] ?? '');
    $answer     = trim($_POST['answer'] ?? '');
    $sort_order = trim($_POST['sort_order'] ?? '');
    $status     = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($question == '') {
        $response['errors']['question'] = 'Question is required.';
    } elseif (strlen($question) > 255) {
        $response['errors']['question'] = 'Question may not be greater than 255 characters.';
    }

    if ($answer == '') {
        $response['errors']['answer'] = 'Answer is required.';
    }

    if ($sort_order == '') {
        $sort_order = 0;
    }

    if (!is_numeric($sort_order)) {
        $response['errors']['sort_order'] = 'Sort order must be numeric.';
    }

    if (!in_array($status, [0,1])) {
        $status = 1;
    }

    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Question
    |--------------------------------------------------------------------------
    */

    if (empty($response['errors'])) {

        $check = $pdo->prepare("
            SELECT id
            FROM faqs
            WHERE question = ?
            LIMIT 1
        ");

        $check->execute([$question]);

        if ($check->fetch()) {
            $response['errors']['question'] = 'This question already exists.';
        }

    }

    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    if (!empty($response['errors'])) {

        $response['message'] = 'Please correct the highlighted errors.';

        echo json_encode($response);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO faqs
        (
            question,
            answer,
            sort_order,
            status
        )
        VALUES
        (
            :question,
            :answer,
            :sort_order,
            :status
        )
    ");

    $stmt->execute([
        ':question'   => $question,
        ':answer'     => $answer,
        ':sort_order' => (int)$sort_order,
        ':status'     => $status
    ]);

    $response['success'] = true;
    $response['message'] = 'FAQ created successfully.';

} catch (PDOException $e) {

    $response['message'] = 'Database Error.';
    // Uncomment while developing
    // $response['message'] = $e->getMessage();

}

echo json_encode($response);