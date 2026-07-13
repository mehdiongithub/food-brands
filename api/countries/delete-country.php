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

    // --- Restrict delete: check every table that references this country ---
    // Each entry: [table, column, singular label, plural label]
    $dependencies = [
        ['brand_country',   'country_id', 'brand',         'brands'],
        ['offer_countries', 'country_id', 'offer',         'offers'],
        ['product_prices',  'country_id', 'product price', 'product prices'],
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
        // Build a natural "A, B and C" list
        $last = array_pop($usage);
        $list = empty($usage) ? $last : implode(', ', $usage) . ' and ' . $last;

        echo json_encode([
            "success" => false,
            "message" => "\"" . $country['name'] . "\" cannot be deleted because it is linked to " . $list . ". Please remove or reassign these first."
        ]);
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
    // Fallback safety net: FK constraint violation (e.g. a new dependent table
    // was added later and this list wasn't updated)
    if ($e->getCode() == 23000) {
        echo json_encode([
            "success" => false,
            "message" => "This country cannot be deleted because it's linked to existing brands, offers, or products."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
}