<?php
/**
 * Returns the list of TOP-LEVEL categories (parent_id IS NULL) that are
 * eligible to be picked as a "Parent Category" in the Add/Edit Category
 * forms — e.g. "Burger", "Pizza". Used by create.php (always) and
 * edit-category.php (with exclude_id so a category never lists itself).
 *
 * Only top-level categories are offered as parents — a child category
 * (e.g. "Zinger Burger") can never itself be a parent, enforcing a single
 * level of nesting site-wide.
 */
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$excludeId = isset($_GET['exclude_id']) ? (int) $_GET['exclude_id'] : 0;

try {
    $sql = "SELECT id, name FROM categories WHERE parent_id IS NULL AND status = 1";
    $params = [];

    if ($excludeId > 0) {
        $sql .= " AND id != :exclude_id";
        $params[':exclude_id'] = $excludeId;
    }

    $sql .= " ORDER BY name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $categories
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
