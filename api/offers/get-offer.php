<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin(); // both admin and employee can view
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid offer ID"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT o.id, o.brand_id, o.title, o.slug, o.description, o.discount_percent,
           o.coupon_code, o.start_date, o.end_date, o.image, o.status,
           o.created_by, o.created_at, o.updated_at,
           b.name AS brand_name
    FROM offers o
    LEFT JOIN brands b ON b.id = o.brand_id
    WHERE o.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $id]);
$offer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$offer) {
    echo json_encode(["success" => false, "message" => "Offer not found"]);
    exit;
}

// --- Fetch related countries ---
$ctryStmt = $pdo->prepare("
    SELECT c.id, c.name
    FROM offer_countries oc
    INNER JOIN countries c ON c.id = oc.country_id
    WHERE oc.offer_id = :id
    ORDER BY c.name ASC
");
$ctryStmt->execute([':id' => $id]);
$offer['countries'] = $ctryStmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "data" => $offer
]);