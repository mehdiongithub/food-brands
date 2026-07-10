<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']);
header('Content-Type: application/json');

$columns = ['id', 'name', 'calories', 'featured', 'status', 'created_at'];

$draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
$searchValue = $_POST['search']['value'] ?? '';
$statusFilter = $_POST['status_filter'] ?? '';
$categoryFilter = $_POST['category_filter'] ?? '';
$brandFilter = $_POST['brand_filter'] ?? '';
$countryFilter = $_POST['country_filter'] ?? '';

$orderColIndex = $_POST['order'][0]['column'] ?? 0;
$orderDir = strtolower($_POST['order'][0]['dir'] ?? 'desc');
$orderDir = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';
$orderCol = $columns[$orderColIndex] ?? 'created_at';

$isAdmin = (currentUserRole() === 'admin');

try {
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM products");
    $totalRecords = (int)$totalStmt->fetchColumn();

    $conditions = [];
    $params = [];

    if (!empty($searchValue)) {
        $conditions[] = "p.name LIKE :s";
        $params[':s'] = '%' . $searchValue . '%';
    }

    if ($statusFilter !== '') {
        $conditions[] = "p.status = :status";
        $params[':status'] = (int)$statusFilter;
    }

    if ($categoryFilter !== '') {
        $conditions[] = "p.category_id = :category_id";
        $params[':category_id'] = (int)$categoryFilter;
    }

    if ($brandFilter !== '') {
        $conditions[] = "p.brand_id = :brand_id";
        $params[':brand_id'] = (int)$brandFilter;
    }

    if ($countryFilter !== '') {
        $conditions[] = "EXISTS (SELECT 1 FROM product_prices pcf WHERE pcf.product_id = p.id AND pcf.country_id = :country_id)";
        $params[':country_id'] = (int)$countryFilter;
    }

    $where = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : '';

    $countSql = "SELECT COUNT(*) FROM products p $where";
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $k => $v) {
        $countStmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $countStmt->execute();
    $filteredRecords = (int)$countStmt->fetchColumn();

    $limitClause = "LIMIT :start, :length";
    if ($length == -1) {
        $limitClause = '';
    }

    $sql = "SELECT p.id, p.name, p.slug, p.image, p.calories, p.featured, p.status, p.created_at,
                b.id AS brand_id, b.name AS brand_name, b.logo AS brand_logo,
                c.id AS category_id, c.name AS category_name
            FROM products p
            LEFT JOIN brands b ON b.id = p.brand_id
            LEFT JOIN categories c ON c.id = p.category_id
            $where
            ORDER BY p.$orderCol $orderDir
            $limitClause";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    if ($length != -1) {
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    }
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare reusable statements for child-row data (avoids re-preparing in the loop)
    $priceStmt = $pdo->prepare("
        SELECT pp.regular_price, pp.discount_price, pp.currency, pp.status,
               co.name AS country_name, co.code AS country_code, co.currency_symbol
        FROM product_prices pp
        JOIN countries co ON co.id = pp.country_id
        WHERE pp.product_id = :product_id
        ORDER BY co.name ASC
    ");

    $ingredientStmt = $pdo->prepare("
        SELECT i.name
        FROM product_ingredients pi
        JOIN ingredients i ON i.id = pi.ingredient_id
        WHERE pi.product_id = :product_id
        ORDER BY i.name ASC
    ");

    $data = [];
    foreach ($rows as $p) {
        $name = htmlspecialchars($p['name']);

        $imgHtml = !empty($p['image'])
            ? "<img src='" . htmlspecialchars(BASE_URL . '/' . $p['image']) . "' alt='$name' style='width:36px;height:36px;object-fit:cover;border-radius:6px;border:1px solid var(--border);'>"
            : "<div style='width:36px;height:36px;border-radius:6px;background:" . initialsColor($p['name']) . ";color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;'>" . getInitials($p['name']) . "</div>";

        $status = $p['status']
            ? '<span class="sb-badge2 active">Active</span>'
            : '<span class="sb-badge2 draft">Inactive</span>';

        $featuredBadge = $p['featured']
            ? '<span class="sb-badge2 active" style="background:rgba(217,119,6,.1);color:#D97706;"><i class="fas fa-star" style="font-size:.65rem;"></i> Featured</span>'
            : '';

        $created = date('Y-m-d', strtotime($p['created_at']));

        // --- Brand & category info for child row ---
        $brandName = $p['brand_name'] ? htmlspecialchars($p['brand_name']) : 'No brand assigned';
        $brandLogo = !empty($p['brand_logo'])
            ? "<img src='" . htmlspecialchars(BASE_URL . '/' . $p['brand_logo']) . "' style='width:24px;height:24px;object-fit:cover;border-radius:4px;margin-right:6px;vertical-align:middle;'>"
            : "";
        $categoryName = $p['category_name'] ? htmlspecialchars($p['category_name']) : 'No category assigned';

        // --- Fetch prices per country ---
        $priceStmt->execute([':product_id' => $p['id']]);
        $prices = $priceStmt->fetchAll(PDO::FETCH_ASSOC);

        $pricesHtml = '';
        if ($prices) {
            $pricesHtml .= "<div style='display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;'>";
            foreach ($prices as $pr) {
                $symbol = $pr['currency_symbol'] ?: ($pr['currency'] ?: '');
                $regular = $pr['regular_price'] !== null ? number_format((float)$pr['regular_price'], 2) : null;
                $discount = $pr['discount_price'] !== null ? number_format((float)$pr['discount_price'], 2) : null;
                $priceStatus = $pr['status']
                    ? "<span class='sb-badge2 active' style='font-size:.65rem;padding:2px 8px;'>Active</span>"
                    : "<span class='sb-badge2 draft' style='font-size:.65rem;padding:2px 8px;'>Inactive</span>";

                $priceDisplay = $discount
                    ? "<span style='text-decoration:line-through;color:var(--muted);font-size:.78rem;'>$symbol$regular</span> <strong style='color:var(--danger,#DC2626);'>$symbol$discount</strong>"
                    : "<strong>$symbol" . ($regular ?: '—') . "</strong>";

                $pricesHtml .= "
                    <div style='background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:10px 12px;'>
                        <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;'>
                            <span style='font-weight:600;font-size:.8rem;'>" . htmlspecialchars($pr['country_name']) . "</span>
                            $priceStatus
                        </div>
                        <div style='font-size:.9rem;'>$priceDisplay</div>
                    </div>
                ";
            }
            $pricesHtml .= "</div>";
        } else {
            $pricesHtml = "<span style='color:var(--muted);font-size:.8rem;'>No pricing set for any country</span>";
        }

        // --- Fetch ingredients ---
        $ingredientStmt->execute([':product_id' => $p['id']]);
        $ingredients = $ingredientStmt->fetchAll(PDO::FETCH_ASSOC);

        $ingredientsHtml = !empty($ingredients)
            ? implode('', array_map(function ($ing) {
                return "<span class='sb-badge2' style='margin:3px;display:inline-block;'>" . htmlspecialchars($ing['name']) . "</span>";
            }, $ingredients))
            : "<span style='color:var(--muted);font-size:.8rem;'>No ingredients listed</span>";

        // --- Build the full child row HTML ---
        $childHtml = "
            <div style='padding:16px 20px;background:var(--bg);border-radius:8px;'>

                <div style='display:flex;gap:24px;flex-wrap:wrap;margin-bottom:16px;'>
                    <div>
                        <div style='font-size:.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.03em;margin-bottom:4px;'>
                            <i class='fas fa-store'></i> Brand
                        </div>
                        <div style='font-size:.85rem;'>$brandLogo" . $brandName . "</div>
                    </div>
                    <div>
                        <div style='font-size:.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.03em;margin-bottom:4px;'>
                            <i class='fas fa-layer-group'></i> Category
                        </div>
                        <div style='font-size:.85rem;'>$categoryName</div>
                    </div>
                </div>

                <div style='margin-bottom:16px;'>
                    <div style='font-size:.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.03em;margin-bottom:8px;'>
                        <i class='fas fa-money-bill-wave'></i> Pricing by Country
                    </div>
                    $pricesHtml
                </div>

                <div>
                    <div style='font-size:.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.03em;margin-bottom:8px;'>
                        <i class='fas fa-carrot'></i> Ingredients
                    </div>
                    $ingredientsHtml
                </div>

            </div>
        ";

        if ($isAdmin) {
            $actions = "
                <a class='ab text-decoration-none' href='view-product.php?token=" . urlencode(encryptId($p['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
                <a class='ab text-decoration-none' href='edit-product.php?token=" . urlencode(encryptId($p['id'])) . "' title='Edit'><i class='fas fa-pen'></i></a>
                <a class='ab text-decoration-none dng' href='#' onclick='deleteProduct({$p['id']}, " . json_encode($name) . ");return false;' title='Delete'><i class='fas fa-trash'></i></a>
                ";
                } else {
                    $actions = "
                    <a class='ab text-decoration-none' href='view-product.php?token=" . urlencode(encryptId($p['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
                    <a class='ab text-decoration-none' href='edit-product.php?token=" . urlencode(encryptId($p['id'])) . "' title='Edit'><i class='fas fa-pen'></i></a>
            ";
        }

        $data[] = [
            "product" => "<div class='tu'>$imgHtml<div style='margin-left:8px;display:inline-block;vertical-align:middle;'><div class='tn'>$name</div>$featuredBadge</div></div>",
            "calories" => $p['calories'] !== null ? $p['calories'] . ' kcal' : '—',
            "status" => $status,
            "created_at" => $created,
            "actions" => $actions,
            "details" => "<button class='row-toggle-btn' type='button' aria-label='Toggle details'><i class='fas fa-plus'></i></button>",
            "child_html" => $childHtml
        ];
    }

    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $filteredRecords,
        "data" => $data
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}