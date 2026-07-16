<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid product ID"]);
    exit;
}

try {
    // --- Main product record with brand/category/creator joined in ---
    $stmt = $pdo->prepare("
        SELECT p.id, p.brand_id, p.category_id, p.name, p.slug, p.image, p.short_description,
               p.description, p.calories, p.featured, p.status, p.meta_title, p.meta_description,
               p.created_by, p.created_at, p.updated_at,
               b.name AS brand_name, b.logo AS brand_logo,
               c.name AS category_name, c.parent_id AS category_parent_id,
               u.name AS created_by_name
        FROM products p
        LEFT JOIN brands b ON b.id = p.brand_id
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN users u ON u.id = p.created_by
        WHERE p.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(["success" => false, "message" => "Product not found"]);
        exit;
    }

    // --- Product images ---
    $imgStmt = $pdo->prepare("
        SELECT id, image, sort_order
        FROM product_images
        WHERE product_id = :product_id
        ORDER BY sort_order ASC, id ASC
    ");
    $imgStmt->execute([':product_id' => $id]);
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Ingredients ---
    $ingStmt = $pdo->prepare("
        SELECT i.id, i.name
        FROM product_ingredients pi
        JOIN ingredients i ON i.id = pi.ingredient_id
        WHERE pi.product_id = :product_id
        ORDER BY i.name ASC
    ");
    $ingStmt->execute([':product_id' => $id]);
    $ingredients = $ingStmt->fetchAll(PDO::FETCH_ASSOC);

    // --- Prices per country ---
    $priceStmt = $pdo->prepare("
        SELECT pp.id, pp.country_id, pp.regular_price, pp.discount_price, pp.currency, pp.status, pp.updated_on,
               co.name AS country_name, co.code AS country_code, co.currency_symbol, co.flag
        FROM product_prices pp
        JOIN countries co ON co.id = pp.country_id
        WHERE pp.product_id = :product_id
        ORDER BY co.name ASC
    ");
    $priceStmt->execute([':product_id' => $id]);
    $prices = $priceStmt->fetchAll(PDO::FETCH_ASSOC);

    // Attach ready-to-use flag HTML to each price row
    foreach ($prices as &$pr) {
        $pr['flag_html'] = getFlagHtml($pr['country_name'], $pr['flag']);
    }
    unset($pr);

    // --- Nutrition info (single row or null) ---
    $nutritionStmt = $pdo->prepare("
        SELECT fat, carbs, protein, fiber, sugar, sodium, calories, created_at, updated_at
        FROM product_nutrition
        WHERE product_id = :product_id
        LIMIT 1
    ");
    $nutritionStmt->execute([':product_id' => $id]);
    $nutrition = $nutritionStmt->fetch(PDO::FETCH_ASSOC);

    $product['images']     = $images;
    $product['ingredients'] = $ingredients;
    $product['prices']     = $prices;
    $product['nutrition']  = $nutrition ?: null;

    echo json_encode([
        "success" => true,
        "data" => $product
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}