<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']); // only admin can create
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$errors = [];

$name   = trim($_POST['name'] ?? '');
$status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

// --- Validation ---
if ($name === '') {
    $errors['Name'] = 'Ingredient name is required.';
} elseif (mb_strlen($name) > 150) {
    $errors['Name'] = 'Ingredient name must be under 150 characters.';
}

// --- Check duplicate name (indexed column) ---
if (empty($errors['Name'])) {
    $checkStmt = $pdo->prepare("SELECT id FROM ingredients WHERE name = :name LIMIT 1");
    $checkStmt->execute([':name' => $name]);
    if ($checkStmt->fetch()) {
        $errors['Name'] = 'An ingredient with this name already exists.';
    }
}

if (!empty($errors)) {
    echo json_encode([
        "success" => false,
        "message" => "Please fix the errors below.",
        "errors" => $errors
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO ingredients (name, status, created_at)
        VALUES (:name, :status, NOW())
    ");
    $stmt->execute([
        ':name'   => $name,
        ':status' => $status
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Ingredient created successfully.",
        "id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}