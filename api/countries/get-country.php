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
    echo json_encode(["success" => false, "message" => "Invalid country ID"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, name, code, currency, currency_symbol, flag, slug, status, created_at, updated_at
        FROM countries
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $country = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$country) {
        echo json_encode(["success" => false, "message" => "Country not found"]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "data" => $country
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}