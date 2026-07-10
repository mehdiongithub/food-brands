<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']);

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid testimonial ID"]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, designation, image, review, rating, status, created_at FROM testimonials WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$testimonial = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$testimonial) {
    echo json_encode(["success" => false, "message" => "Testimonial not found"]);
    exit;
}

echo json_encode([
    "success" => true,
    "data" => $testimonial
]);