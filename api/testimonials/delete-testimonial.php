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
    echo json_encode(["success" => false, "message" => "Invalid testimonial ID"]);
    exit;
}

// --- Fetch testimonial + confirm it exists ---
$stmt = $pdo->prepare("SELECT id, name, image FROM testimonials WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$testimonial = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$testimonial) {
    echo json_encode(["success" => false, "message" => "Testimonial not found"]);
    exit;
}

try {
    $delStmt = $pdo->prepare("DELETE FROM testimonials WHERE id = :id");
    $delStmt->execute([':id' => $id]);

    if ($delStmt->rowCount() === 0) {
        // Race condition guard — already deleted by another request
        echo json_encode(["success" => false, "message" => "Testimonial could not be deleted"]);
        exit;
    }

    // --- Only after successful DB delete: remove the physical image file ---
    if (!empty($testimonial['image'])) {
        $fullPath = __DIR__ . '/../../' . $testimonial['image'];
        if (file_exists($fullPath) && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    echo json_encode(["success" => true, "message" => "Testimonial from \"" . $testimonial['name'] . "\" deleted successfully."]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}