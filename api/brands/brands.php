<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']);
header('Content-Type: application/json');

$columns = ['id', 'name', 'status', 'founded_year', 'created_at'];

$draw   = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start  = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
$searchValue = $_POST['search']['value'] ?? '';
$statusFilter = $_POST['status_filter'] ?? '';
$countryFilter = $_POST['country_filter'] ?? '';

$orderColIndex = $_POST['order'][0]['column'] ?? 0;
$orderDir = strtolower($_POST['order'][0]['dir'] ?? 'desc');
$orderDir = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'desc';
$orderCol = $columns[$orderColIndex] ?? 'created_at';

$isAdmin = (currentUserRole() === 'admin');

try {
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM brands");
    $totalRecords = (int)$totalStmt->fetchColumn();

    $conditions = [];
    $params = [];

    if (!empty($searchValue)) {
        $conditions[] = "b.name LIKE :s";
        $params[':s'] = '%' . $searchValue . '%';
    }

    if ($statusFilter !== '') {
        $conditions[] = "b.status = :status";
        $params[':status'] = (int)$statusFilter;
    }

    if ($countryFilter !== '') {
        $conditions[] = "EXISTS (SELECT 1 FROM brand_country bcf WHERE bcf.brand_id = b.id AND bcf.country_id = :country_id)";
        $params[':country_id'] = (int)$countryFilter;
    }

    $where = count($conditions) ? "WHERE " . implode(" AND ", $conditions) : '';

    $countSql = "SELECT COUNT(*) FROM brands b $where";
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

    $sql = "SELECT b.id, b.name, b.slug, b.logo, b.status, b.founded_year, b.created_at,
                (SELECT GROUP_CONCAT(c.name SEPARATOR ', ')
                    FROM brand_category bc
                    JOIN categories c ON c.id = bc.category_id
                    WHERE bc.brand_id = b.id) AS categories,
                (SELECT GROUP_CONCAT(co.name SEPARATOR ', ')
                    FROM brand_country bcy
                    JOIN countries co ON co.id = bcy.country_id
                    WHERE bcy.brand_id = b.id) AS countries
            FROM brands b
            $where
            ORDER BY b.$orderCol $orderDir
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

    $data = [];
    foreach ($rows as $b) {
        $name = htmlspecialchars($b['name']);

        $logoHtml = !empty($b['logo'])
            ? "<img src='" . htmlspecialchars(BASE_URL . '/' . $b['logo']) . "' alt='$name' style='width:36px;height:36px;object-fit:cover;border-radius:6px;border:1px solid var(--border);'>"
            : "<div style='width:36px;height:36px;border-radius:6px;background:" . initialsColor($b['name']) . ";color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;'>" . getInitials($b['name']) . "</div>";

        $status = $b['status']
            ? '<span class="sb-badge2 active">Active</span>'
            : '<span class="sb-badge2 draft">Inactive</span>';

        $founded = $b['founded_year'] ?: '—';
        $created = date('Y-m-d', strtotime($b['created_at']));

        // Build the badge lists used inside the expanded child row
        $categoryList = !empty($b['categories'])
            ? implode('', array_map(function ($cat) {
                return "<span class='sb-badge2' style='margin:3px;display:inline-block;'>" . htmlspecialchars(trim($cat)) . "</span>";
            }, explode(',', $b['categories'])))
            : "<span style='color:var(--muted);font-size:.8rem;'>No categories assigned</span>";

        $countryList = !empty($b['countries'])
            ? implode('', array_map(function ($ctry) {
                return "<span class='sb-badge2' style='margin:3px;display:inline-block;'>" . htmlspecialchars(trim($ctry)) . "</span>";
            }, explode(',', $b['countries'])))
            : "<span style='color:var(--muted);font-size:.8rem;'>No countries assigned</span>";

        // Full child-row HTML template (hidden field, rendered client-side on expand)
        $childHtml = "
            <div style='padding:16px 20px;background:var(--bg);border-radius:8px;'>
                <div style='margin-bottom:14px;'>
                    <div style='font-size:.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.03em;margin-bottom:8px;'>
                        <i class='fas fa-layer-group'></i> Categories
                    </div>
                    <div>$categoryList</div>
                </div>
                <div>
                    <div style='font-size:.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.03em;margin-bottom:8px;'>
                        <i class='fas fa-globe-americas'></i> Countries
                    </div>
                    <div>$countryList</div>
                </div>
            </div>
        ";

        if ($isAdmin) {
            $actions = "
                <a class='ab text-decoration-none' href='view-brand.php?token=" . urlencode(encryptId($b['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
                <a class='ab text-decoration-none' href='edit-brand.php?token=" . urlencode(encryptId($b['id'])) . "' title='Edit'><i class='fas fa-pen'></i></a>
                <a class='ab text-decoration-none dng' href='#' onclick='deleteBrand({$b['id']}, " . json_encode($name) . ");return false;' title='Delete'><i class='fas fa-trash'></i></a>
            ";
        } else {
            $actions = "
                <a class='ab text-decoration-none' href='view-brand.php?token=" . urlencode(encryptId($b['id'])) . "' title='View'><i class='fas fa-eye'></i></a>
            ";
        }

        $data[] = [
            "brand" => "<div class='tu'>$logoHtml<div class='tn' style='margin-left:8px;display:inline-block;vertical-align:middle;'>$name</div></div>",
            "founded_year" => $founded,
            "status" => $status,
            "created_at" => $created,
            "actions" => $actions,
            "details" => "<button class='row-toggle-btn' type='button' aria-label='Toggle details'><i class='fas fa-plus'></i></button>",
            "child_html" => $childHtml // not bound to a visible column, used by the toggle JS
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