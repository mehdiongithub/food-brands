<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']);
header('Content-Type: application/json');

try {
    $brands = $pdo->query("SELECT id, name FROM brands WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    // parent_id included so the product form can group categories into a
    // Category (parent) -> Subcategory (child) cascade, same as a
    // professional food menu (e.g. Pizza -> Small/Medium/Large).
    $categories = $pdo->query("SELECT id, parent_id, name FROM categories WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $ingredients = $pdo->query("SELECT id, name FROM ingredients WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $countries = $pdo->query("SELECT id, name, code, currency, currency_symbol, flag FROM countries WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Attach a ready-to-use flag HTML string per country so the JS doesn't need its own logic
    foreach ($countries as &$c) {
        $c['flag_html'] = getFlagHtml($c['name'], $c['flag']);
    }

    echo json_encode([
        "success" => true,
        "brands" => $brands,
        "categories" => $categories,
        "ingredients" => $ingredients,
        "countries" => $countries
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}