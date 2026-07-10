<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$userId = currentUserId();

$currentPassword = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

$errors = [];

if ($currentPassword === '') {
    $errors['current_password'] = 'Current password is required.';
}

if ($newPassword === '') {
    $errors['new_password'] = 'New password is required.';
} elseif (mb_strlen($newPassword) < 8) {
    $errors['new_password'] = 'New password must be at least 8 characters.';
}

if ($confirmPassword === '') {
    $errors['confirm_password'] = 'Please confirm your new password.';
} elseif ($newPassword !== $confirmPassword) {
    $errors['confirm_password'] = 'Passwords do not match.';
}

if (!empty($errors)) {
    echo json_encode(["success" => false, "message" => "Please fix the errors below.", "errors" => $errors]);
    exit;
}

$stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

if (!password_verify($currentPassword, $user['password'])) {
    echo json_encode([
        "success" => false,
        "message" => "Please fix the errors below.",
        "errors" => ["current_password" => "Current password is incorrect."]
    ]);
    exit;
}

if (password_verify($newPassword, $user['password'])) {
    echo json_encode([
        "success" => false,
        "message" => "Please fix the errors below.",
        "errors" => ["new_password" => "New password must be different from your current password."]
    ]);
    exit;
}

try {
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $updateStmt = $pdo->prepare("UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id");
    $updateStmt->execute([':password' => $hashedPassword, ':id' => $userId]);

    echo json_encode(["success" => true, "message" => "Password updated successfully."]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}