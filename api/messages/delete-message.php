<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid message ID"]);
    exit;
}

// --- Confirm it exists first ---
$stmt = $pdo->prepare("SELECT id, name, subject FROM contact_messages WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    echo json_encode(["success" => false, "message" => "Message not found"]);
    exit;
}

try {
    $delStmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = :id");
    $delStmt->execute([':id' => $id]);

    if ($delStmt->rowCount() === 0) {
        // Race condition guard — already deleted by another request
        echo json_encode(["success" => false, "message" => "Message could not be deleted"]);
        exit;
    }

    $label = !empty($message['subject']) ? $message['subject'] : $message['name'];

    echo json_encode([
        "success" => true,
        "message" => "Message \"" . $label . "\" deleted successfully."
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}