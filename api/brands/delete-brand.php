<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid brand ID"]);
    exit;
}

// --- Fetch brand + confirm it exists ---
$stmt = $pdo->prepare("SELECT id, name, logo, cover_image FROM brands WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$brand = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$brand) {
    echo json_encode(["success" => false, "message" => "Brand not found"]);
    exit;
}

// --- Restrict delete: check tables where OTHER content depends on this brand ---
// (brand_category / brand_country / brand_gallery are the brand's own metadata
// and are safe to cascade away below — offers and products are real content
// that would otherwise be silently destroyed.)
$dependencies = [
    ['offers',   'brand_id', 'offer',   'offers'],
    ['products', 'brand_id', 'product', 'products'],
];

$usage = [];
foreach ($dependencies as [$table, $col, $singular, $plural]) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE `$col` = :id");
    $countStmt->execute([':id' => $id]);
    $count = (int) $countStmt->fetchColumn();
    if ($count > 0) {
        $usage[] = $count . ' ' . ($count === 1 ? $singular : $plural);
    }
}

if (!empty($usage)) {
    $last = array_pop($usage);
    $list = empty($usage) ? $last : implode(', ', $usage) . ' and ' . $last;

    echo json_encode([
        "success" => false,
        "message" => "\"" . $brand['name'] . "\" cannot be deleted because it is linked to " . $list . ". Please remove or reassign these first."
    ]);
    exit;
}

// --- Fetch gallery image paths before deleting rows ---
$galleryStmt = $pdo->prepare("SELECT image FROM brand_gallery WHERE brand_id = :id");
$galleryStmt->execute([':id' => $id]);
$galleryImages = $galleryStmt->fetchAll(PDO::FETCH_COLUMN);

try {
    $pdo->beginTransaction();

    // --- Remove relationship rows first (in case FK constraints aren't cascading) ---
    $pdo->prepare("DELETE FROM brand_category WHERE brand_id = :id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM brand_country WHERE brand_id = :id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM brand_gallery WHERE brand_id = :id")->execute([':id' => $id]);

    // --- Finally remove the brand itself ---
    $delStmt = $pdo->prepare("DELETE FROM brands WHERE id = :id");
    $delStmt->execute([':id' => $id]);

    if ($delStmt->rowCount() === 0) {
        // Shouldn't happen since we already fetched it, but guard anyway
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Brand could not be deleted"]);
        exit;
    }

    $pdo->commit();

    // --- Only after successful commit: remove physical image files ---
    $filesToDelete = array_filter(array_merge(
        [$brand['logo'], $brand['cover_image']],
        $galleryImages
    ));

    foreach ($filesToDelete as $path) {
        $fullPath = __DIR__ . '/../../' . $path;
        if (file_exists($fullPath) && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    echo json_encode(["success" => true, "message" => "Brand deleted successfully."]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Fallback safety net: FK constraint violation (e.g. a new dependent table
    // was added later and the pre-check above wasn't updated)
    if ($e->getCode() == 23000) {
        echo json_encode([
            "success" => false,
            "message" => "This brand cannot be deleted because it's linked to existing offers or products."
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
}