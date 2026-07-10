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
    echo json_encode(["success" => false, "message" => "Invalid FAQ ID"]);
    exit;
}

try {
    // Fetch the FAQ first — need question (for message) and flag (for cleanup)
    $stmt = $pdo->prepare("SELECT id, question FROM faqs WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $faq = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$faq) {
        echo json_encode(["success" => false, "message" => "FAQ not found"]);
        exit;
    }

    // Delete the database row
    $deleteStmt = $pdo->prepare("DELETE FROM faqs WHERE id = :id");
    $deleteStmt->execute([':id' => $id]);

   
    echo json_encode([
        "success" => true,
        "message" => "FAQ \"" . $faq['question'] . "\" deleted successfully."
    ]);

} catch (PDOException $e) {
    // Common cause: FK constraint violation if brand_country references this country
    if ($e->getCode() == 23000) {
        echo json_encode([
            "success" => false,
            "message" => "This FAQ cannot be deleted because it's linked to existing records."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
}