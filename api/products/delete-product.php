<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']); // only admin can delete
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid product ID"]);
    exit;
}

try {
    // --- Fetch the product first — need name (for message) ---
    $stmt = $pdo->prepare("SELECT id, name FROM products WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Product not found"]);
        exit;
    }

    // --- Fetch all gallery image paths before deleting rows, so we can remove the files after commit ---
    $imgStmt = $pdo->prepare("SELECT image FROM product_images WHERE product_id = :id");
    $imgStmt->execute([':id' => $id]);
    $imagePaths = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

    $pdo->beginTransaction();

    // --- Delete child records first ---
    // Note: if your schema already has ON DELETE CASCADE on these foreign keys,
    // these deletes are harmless no-ops (rows are already gone by the time we get here)
    // but keeping them explicit makes the code correct even if a constraint is ever
    // changed to RESTRICT/NO ACTION.
    $pdo->prepare("DELETE FROM product_images WHERE product_id = :id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM product_ingredients WHERE product_id = :id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM product_prices WHERE product_id = :id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM product_nutrition WHERE product_id = :id")->execute([':id' => $id]);

    // --- Delete the product itself ---
    $pdo->prepare("DELETE FROM products WHERE id = :id")->execute([':id' => $id]);

    $pdo->commit();

    // --- Only after successful commit: remove the physical image files from disk ---
    foreach ($imagePaths as $path) {
        if (!empty($path)) {
            $fullPath = __DIR__ . '/../../' . $path;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Product \"" . $product['name'] . "\" deleted successfully."
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // FK constraint violation — e.g. if some other table references this product
    // that we're not aware of, or a constraint is RESTRICT instead of CASCADE
    if ($e->getCode() == 23000) {
        echo json_encode([
            "success" => false,
            "message" => "This product cannot be deleted because it's linked to other existing records."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
}