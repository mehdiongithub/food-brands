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
    echo json_encode(["success" => false, "message" => "Invalid category ID"]);
    exit;
}

try {
    // Fetch the category first — need name (for message) and image (for cleanup)
    $stmt = $pdo->prepare("SELECT id, name, image FROM categories WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        echo json_encode(["success" => false, "message" => "Category not found"]);
        exit;
    }

    // --- Restrict delete: check every table that references this category ---
    // Each entry: [table, column, singular label, plural label]
    $dependencies = [
        ['brand_category', 'category_id', 'brand',   'brands'],
        ['products',       'category_id', 'product', 'products'],
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
        // Build a natural "A and B" list
        $last = array_pop($usage);
        $list = empty($usage) ? $last : implode(', ', $usage) . ' and ' . $last;

        echo json_encode([
            "success" => false,
            "message" => "\"" . $category['name'] . "\" cannot be deleted because it is linked to " . $list . ". Please remove or reassign these first."
        ]);
        exit;
    }

    // Delete the database row
    $deleteStmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $deleteStmt->execute([':id' => $id]);

    // Only after successful DB delete, remove the associated image file
    if (!empty($category['image'])) {
        $imagePath = __DIR__ . '/../../' . $category['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Category \"" . $category['name'] . "\" deleted successfully."
    ]);

} catch (PDOException $e) {
    // Fallback safety net: FK constraint violation (e.g. a new dependent table
    // was added later and this list wasn't updated)
    if ($e->getCode() == 23000) {
        echo json_encode([
            "success" => false,
            "message" => "This category cannot be deleted because it's linked to existing brands or products."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
}