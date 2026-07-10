<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin(); // both admin and employee can view
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid blog ID"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT b.id, b.title, b.slug, b.image, b.excerpt, b.content, b.category,
           b.author_id, b.views, b.status, b.meta_title, b.meta_description,
           b.published_at, b.created_at, b.updated_at,
           u.name AS author_name
    FROM blogs b
    LEFT JOIN users u ON u.id = b.author_id
    WHERE b.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $id]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$blog) {
    echo json_encode(["success" => false, "message" => "Blog not found"]);
    exit;
}

echo json_encode([
    "success" => true,
    "data" => $blog
]);