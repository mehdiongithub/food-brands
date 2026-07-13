<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']);

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid message ID"]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, email, phone, subject, message, ip_address, status, created_at FROM contact_messages WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
    echo json_encode(["success" => false, "message" => "Message not found"]);
    exit;
}

// Auto mark as "read" the first time it is opened (only if it was still "new")
if ($message['status'] === 'new') {
    $upd = $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = :id");
    $upd->execute([':id' => $id]);
    $message['status'] = 'read';
}

echo json_encode([
    "success" => true,
    "data" => $message
]);
