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
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}