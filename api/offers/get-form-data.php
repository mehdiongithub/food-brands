<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']);
header('Content-Type: application/json');

try {
    $brands = $pdo->query("SELECT id, name FROM brands WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    $countries = $pdo->query("SELECT id, name FROM countries WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "brands" => $brands,
        "countries" => $countries
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}