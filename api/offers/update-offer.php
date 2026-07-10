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
    echo json_encode(["success" => false, "message" => "Invalid offer ID"]);
    exit;
}

$existingStmt = $pdo->prepare("SELECT id, image, slug FROM offers WHERE id = :id LIMIT 1");
$existingStmt->execute([':id' => $id]);
$existingOffer = $existingStmt->fetch(PDO::FETCH_ASSOC);

if (!$existingOffer) {
    echo json_encode(["success" => false, "message" => "Offer not found"]);
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

// --- Reusable: replace-with-hash-check upload helper (same pattern as brands/testimonials) ---
function handleImageReplace($fileKey, $existingPath, $subfolder, $maxSizeMB, &$errors) {
    $result = ['changed' => false, 'newPath' => null];

    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return $result; // no new file uploaded, keep existing
    }

    $file = $_FILES[$fileKey];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[$fileKey] = 'Upload failed. Please try again.';
        return $result;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = $maxSizeMB * 1024 * 1024;

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes, true)) {
        $errors[$fileKey] = 'Only JPG, PNG, or WEBP images are allowed.';
        return $result;
    }
    if ($file['size'] > $maxSize) {
        $errors[$fileKey] = "Image must be under {$maxSizeMB}MB.";
        return $result;
    }

    // Hash comparison — skip if identical to existing
    if (!empty($existingPath)) {
        $existingFullPath = __DIR__ . '/../../' . $existingPath;
        if (file_exists($existingFullPath)) {
            $newHash = hash_file('sha256', $file['tmp_name']);
            $oldHash = hash_file('sha256', $existingFullPath);
            if ($newHash === $oldHash) {
                return $result; // identical — no change
            }
        }
    }

    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mimeType];
    $uploadDir = __DIR__ . '/../../assets/img/' . $subfolder . '/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $filename = $subfolder . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $result['changed'] = true;
        $result['newPath'] = 'assets/img/' . $subfolder . '/' . $filename;
    } else {
        $errors[$fileKey] = 'Could not save the uploaded image.';
    }

    return $result;
}

$imageResult = handleImageReplace('image', $existingOffer['image'], 'offers', 2, $errors);

// --- Stop on errors — clean up anything just uploaded ---
if (!empty($errors)) {
    if ($imageResult['newPath']) {
        $full = __DIR__ . '/../../' . $imageResult['newPath'];
        if (file_exists($full)) unlink($full);
    }
    echo json_encode(["success" => false, "message" => "Please fix the errors below.", "errors" => $errors]);
    exit;
}

$finalImage = $imageResult['changed'] ? $imageResult['newPath'] : $existingOffer['image'];

// --- Regenerate slug only if title changed enough to warrant it; keep stable otherwise ---
function generateUniqueSlug($pdo, $title, $excludeId) {
    $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-');
    if ($base === '') $base = 'offer';

    $slug = $base;
    $i = 1;
    $checkStmt = $pdo->prepare("SELECT id FROM offers WHERE slug = :slug AND id != :id LIMIT 1");
    while (true) {
        $checkStmt->execute([':slug' => $slug, ':id' => $excludeId]);
        if (!$checkStmt->fetch()) break;
        $i++;
        $slug = $base . '-' . $i;
    }
    return $slug;
}

$currentBaseSlug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-');
$existingBaseSlug = preg_replace('/-\d+$/', '', $existingOffer['slug'] ?? '');
$slug = ($currentBaseSlug !== $existingBaseSlug)
    ? generateUniqueSlug($pdo, $title, $id)
    : $existingOffer['slug'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        UPDATE offers SET
            brand_id = :brand_id, title = :title, slug = :slug, description = :description,
            discount_percent = :discount_percent, coupon_code = :coupon_code,
            start_date = :start_date, end_date = :end_date, image = :image,
            status = :status, updated_at = NOW()
        WHERE id = :id
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
        ':image'             => $finalImage,
        ':status'            => $status,
        ':id'                => $id
    ]);

    // --- Sync offer_countries: remove all, re-insert selected (same pattern as brand_category/brand_country) ---
    $pdo->prepare("DELETE FROM offer_countries WHERE offer_id = :id")->execute([':id' => $id]);
    if (!empty($countryIds)) {
        $ctryStmt = $pdo->prepare("INSERT INTO offer_countries (offer_id, country_id) VALUES (:offer_id, :country_id)");
        foreach (array_unique($countryIds) as $ctryId) {
            if ($ctryId > 0) $ctryStmt->execute([':offer_id' => $id, ':country_id' => $ctryId]);
        }
    }

    $pdo->commit();

    // --- Only after successful commit: delete old physical file if it was replaced ---
    if ($imageResult['changed'] && !empty($existingOffer['image'])) {
        $old = __DIR__ . '/../../' . $existingOffer['image'];
        if (file_exists($old)) unlink($old);
    }

    echo json_encode(["success" => true, "message" => "Offer updated successfully."]);

} catch (PDOException $e) {
    $pdo->rollBack();

    if ($imageResult['newPath']) {
        $full = __DIR__ . '/../../' . $imageResult['newPath'];
        if (file_exists($full)) unlink($full);
    }

    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}