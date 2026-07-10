<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();
header('Content-Type: application/json');

$userId = currentUserId();

$stmt = $pdo->prepare("SELECT id, name, email, phone, image, role, status, last_login, created_at FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

echo json_encode([
    "success" => true,
    "data" => $user
]);