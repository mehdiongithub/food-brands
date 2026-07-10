<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']);
header('Content-Type: application/json');

try {
    $brands = $pdo->query("SELECT id, name FROM brands WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $categories = $pdo->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
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