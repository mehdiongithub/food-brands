<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']); // only admin can edit
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid ingredient ID"]);
    exit;
}

// --- Confirm it exists ---
$existingStmt = $pdo->prepare("SELECT id FROM ingredients WHERE id = :id LIMIT 1");
$existingStmt->execute([':id' => $id]);
if (!$existingStmt->fetch()) {
    echo json_encode(["success" => false, "message" => "Ingredient not found"]);
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

// --- Check duplicate name, excluding this record's own row ---
if (empty($errors['Name'])) {
    $checkStmt = $pdo->prepare("SELECT id FROM ingredients WHERE name = :name AND id != :id LIMIT 1");
    $checkStmt->execute([':name' => $name, ':id' => $id]);
    if ($checkStmt->fetch()) {
        $errors['Name'] = 'Another ingredient already uses this name.';
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
        UPDATE ingredients
        SET name = :name, status = :status
        WHERE id = :id
    ");
    $stmt->execute([
        ':name'   => $name,
        ':status' => $status,
        ':id'     => $id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Ingredient updated successfully."
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}