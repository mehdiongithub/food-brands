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
    echo json_encode(["success" => false, "message" => "Invalid brand ID"]);
    exit;
}

try {
    // --- Main brand record, with created-by user's name joined in ---
    $stmt = $pdo->prepare("
        SELECT b.id, b.name, b.slug, b.logo, b.cover_image, b.short_description, b.history,
               b.website, b.founded_year, b.status, b.meta_title, b.meta_description,
               b.created_by, b.created_at, b.updated_at,
               u.name AS created_by_name
        FROM brands b
        LEFT JOIN users u ON u.id = b.created_by
        WHERE b.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $brand = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$brand) {
        echo json_encode(["success" => false, "message" => "Brand not found"]);
        exit;
    }

    // --- Linked categories ---
    $catStmt = $pdo->prepare("
        SELECT c.id, c.name
        FROM brand_category bc
        JOIN categories c ON c.id = bc.category_id
        WHERE bc.brand_id = :brand_id
        ORDER BY c.name ASC
    ");
    $catStmt->execute([':brand_id' => $id]);
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Linked countries ---
    $ctryStmt = $pdo->prepare("
        SELECT co.id, co.name, co.code
        FROM brand_country bcy
        JOIN countries co ON co.id = bcy.country_id
        WHERE bcy.brand_id = :brand_id
        ORDER BY co.name ASC
    ");
    $ctryStmt->execute([':brand_id' => $id]);
    $countries = $ctryStmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Gallery images ---
    $galleryStmt = $pdo->prepare("
        SELECT id, image, sort_order
        FROM brand_gallery
        WHERE brand_id = :brand_id
        ORDER BY sort_order ASC, id ASC
    ");
    $galleryStmt->execute([':brand_id' => $id]);
    $gallery = $galleryStmt->fetchAll(PDO::FETCH_ASSOC);

    $brand['categories'] = $categories;
    $brand['countries']  = $countries;
    $brand['gallery']    = $gallery;

    echo json_encode([
        "success" => true,
        "data" => $brand
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}