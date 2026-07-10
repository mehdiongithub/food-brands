<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$errors = [];

// --- Collect inputs ---
$brandId          = isset($_POST['brand_id']) ? (int)$_POST['brand_id'] : 0;
$title             = trim($_POST['title'] ?? '');
$description       = trim($_POST['description'] ?? '');
$discountPercent   = trim($_POST['discount_percent'] ?? '');
$couponCode        = trim($_POST['coupon_code'] ?? '');
$startDate         = trim($_POST['start_date'] ?? '');
$endDate           = trim($_POST['end_date'] ?? '');
$status            = isset($_POST['status']) ? (int)$_POST['status'] : 1;
$countryIds        = isset($_POST['countries']) && is_array($_POST['countries']) ? array_map('intval', $_POST['countries']) : [];

// --- Validation ---
if ($brandId <= 0) {
    $errors['brand_id'] = 'Please select a brand.';
} else {
    $brandCheck = $pdo->prepare("SELECT id FROM brands WHERE id = :id LIMIT 1");
    $brandCheck->execute([':id' => $brandId]);
    if (!$brandCheck->fetch()) {
        $errors['brand_id'] = 'Selected brand does not exist.';
    }
}

if ($title === '') {
    $errors['title'] = 'Offer title is required.';
} elseif (mb_strlen($title) > 255) {
    $errors['title'] = 'Title must be under 255 characters.';
}

if ($discountPercent !== '') {
    if (!is_numeric($discountPercent) || $discountPercent < 0 || $discountPercent > 100) {
        $errors['discount_percent'] = 'Discount must be a number between 0 and 100.';
    }
}

if ($couponCode !== '' && mb_strlen($couponCode) > 50) {
    $errors['coupon_code'] = 'Coupon code must be under 50 characters.';
}

if ($startDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
    $errors['start_date'] = 'Invalid start date.';
}
if ($endDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    $errors['end_date'] = 'Invalid end date.';
}
if ($startDate !== '' && $endDate !== '' && $endDate < $startDate) {
    $errors['end_date'] = 'End date must be after start date.';
}

function sanitizeRichText($html) {
    if ($html === '') return '';
    $allowedTags = '<p><br><b><strong><i><em><u><s><strike><ol><ul><li><h1><h2><h3><a><blockquote>';
    $clean = strip_tags($html, $allowedTags);
    $clean = preg_replace('/on\w+\s*=\s*(["\']).*?\1/i', '', $clean);
    $clean = preg_replace('/href\s*=\s*(["\'])\s*javascript:.*?\1/i', 'href="#"', $clean);
    return $clean;
}
$description = sanitizeRichText($description);

// --- Generate a unique slug from the title ---
function generateUniqueSlug($pdo, $title) {
    $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-');
    if ($base === '') $base = 'offer';

    $slug = $base;
    $i = 1;
    $checkStmt = $pdo->prepare("SELECT id FROM offers WHERE slug = :slug LIMIT 1");
    while (true) {
        $checkStmt->execute([':slug' => $slug]);
        if (!$checkStmt->fetch()) break;
        $i++;
        $slug = $base . '-' . $i;
    }
    return $slug;
}

// --- Handle image upload ---
$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors['image'] = 'Upload failed. Please try again.';
    } else {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes, true)) {
            $errors['image'] = 'Only JPG, PNG, or WEBP images are allowed.';
        } elseif ($file['size'] > $maxSize) {
            $errors['image'] = 'Image must be under 2MB.';
        } else {
            $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mimeType];
            $uploadDir = __DIR__ . '/../../assets/img/offers/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $filename = 'offer_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $imagePath = 'assets/img/offers/' . $filename;
            } else {
                $errors['image'] = 'Could not save the uploaded image.';
            }
        }
    }
}

// --- Stop on errors — clean up anything just uploaded ---
if (!empty($errors)) {
    if ($imagePath) {
        $full = __DIR__ . '/../../' . $imagePath;
        if (file_exists($full)) unlink($full);
    }
    echo json_encode(["success" => false, "message" => "Please fix the errors below.", "errors" => $errors]);
    exit;
}

$slug = generateUniqueSlug($pdo, $title);
$currentUserId = currentUserId(); // assumed helper exists in bootstrap; returns logged-in user's id

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO offers (
            brand_id, title, slug, description, discount_percent, coupon_code,
            start_date, end_date, image, status, created_by, created_at
        ) VALUES (
            :brand_id, :title, :slug, :description, :discount_percent, :coupon_code,
            :start_date, :end_date, :image, :status, :created_by, NOW()
        )
    ");
    $stmt->execute([
        ':brand_id'          => $brandId,
        ':title'             => $title,
        ':slug'              => $slug,
        ':description'       => $description !== '' ? $description : null,
        ':discount_percent'  => $discountPercent !== '' ? $discountPercent : 0,
        ':coupon_code'       => $couponCode !== '' ? $couponCode : null,
        ':start_date'        => $startDate !== '' ? $startDate : null,
        ':end_date'          => $endDate !== '' ? $endDate : null,
        ':image'             => $imagePath,
        ':status'            => $status,
        ':created_by'        => $currentUserId ?: null
    ]);

    $offerId = $pdo->lastInsertId();

    // --- Insert offer_countries ---
    if (!empty($countryIds)) {
        $ctryStmt = $pdo->prepare("INSERT INTO offer_countries (offer_id, country_id) VALUES (:offer_id, :country_id)");
        foreach (array_unique($countryIds) as $ctryId) {
            if ($ctryId > 0) $ctryStmt->execute([':offer_id' => $offerId, ':country_id' => $ctryId]);
        }
    }

    $pdo->commit();

    echo json_encode(["success" => true, "message" => "Offer created successfully."]);

} catch (PDOException $e) {
    $pdo->rollBack();

    if ($imagePath) {
        $full = __DIR__ . '/../../' . $imagePath;
        if (file_exists($full)) unlink($full);
    }

    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}