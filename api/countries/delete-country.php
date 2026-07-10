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
    echo json_encode(["success" => false, "message" => "Invalid country ID"]);
    exit;
}

try {
    // Fetch the country first — need name (for message) and flag (for cleanup)
    $stmt = $pdo->prepare("SELECT id, name, flag FROM countries WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $country = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$country) {
        echo json_encode(["success" => false, "message" => "Country not found"]);
        exit;
    }

    // Delete the database row
    $deleteStmt = $pdo->prepare("DELETE FROM countries WHERE id = :id");
    $deleteStmt->execute([':id' => $id]);

    // Only after successful DB delete, remove the associated flag image file
    if (!empty($country['flag'])) {
        $flagPath = __DIR__ . '/../../' . $country['flag'];
        if (file_exists($flagPath)) {
            unlink($flagPath);
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Country \"" . $country['name'] . "\" deleted successfully."
    ]);

} catch (PDOException $e) {
    // Common cause: FK constraint violation if brand_country references this country
    if ($e->getCode() == 23000) {
        echo json_encode([
            "success" => false,
            "message" => "This country cannot be deleted because it's linked to existing brands or other records."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
}