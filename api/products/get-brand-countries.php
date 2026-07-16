<?php
// Suppress on-screen PHP notices/warnings for this endpoint — if one gets
// printed before json_encode() runs, it corrupts the response and the
// browser sees "HTTP 200 OK" with a body that isn't valid JSON (jQuery
// then reports a generic AJAX failure even though the request "succeeded").
// Errors are still logged server-side, just not echoed into the response.
ini_set('display_errors', '0');

require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']);
header('Content-Type: application/json');

$brandId = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;

if ($brandId <= 0) {
    echo json_encode(["success" => false, "message" => "brand_id is required", "countries" => []]);
    exit;
}

try {
    // Only countries that (a) are active AND (b) are linked to this brand
    // via brand_country — i.e. the brand is actually available/present there.
    $stmt = $pdo->prepare("
        SELECT c.id, c.name, c.code, c.currency, c.currency_symbol, c.flag
        FROM countries c
        INNER JOIN brand_country bc ON bc.country_id = c.id
        WHERE bc.brand_id = :brand_id
          AND c.status = 1
        ORDER BY c.name ASC
    ");
    $stmt->execute([':brand_id' => $brandId]);
    $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($countries as &$c) {
        $c['flag_html'] = getFlagHtml($c['name'], $c['flag']);
    }

    echo json_encode([
        "success" => true,
        "countries" => $countries
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage(), "countries" => []]);
}