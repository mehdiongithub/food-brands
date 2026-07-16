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
        SELECT id, parent_id, name, slug, image, description, status, sort_order, created_at, updated_at
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

    // How many children this category itself has — if > 0, the edit form
    // must lock the Parent Category field (a category with children can't
    // also become a child of something else).
    $childStmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = :id");
    $childStmt->execute([':id' => $id]);
    $category['children_count'] = (int) $childStmt->fetchColumn();

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