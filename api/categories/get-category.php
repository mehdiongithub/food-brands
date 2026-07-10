<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']); // both roles can view
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid category ID"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, name, slug, image, description, status, sort_order, created_at, updated_at
        FROM categories
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        echo json_encode(["success" => false, "message" => "Category not found"]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "data" => $category
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}