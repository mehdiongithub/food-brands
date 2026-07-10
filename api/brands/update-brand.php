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
    echo json_encode(["success" => false, "message" => "Invalid brand ID"]);
    exit;
}

$existingStmt = $pdo->prepare("SELECT id, logo, cover_image FROM brands WHERE id = :id LIMIT 1");
$existingStmt->execute([':id' => $id]);
$existingBrand = $existingStmt->fetch(PDO::FETCH_ASSOC);

if (!$existingBrand) {
    echo json_encode(["success" => false, "message" => "Brand not found"]);
    exit;
}

$errors = [];

// --- Collect inputs ---
$name              = trim($_POST['name'] ?? '');
$website           = trim($_POST['website'] ?? '');
$foundedYear       = trim($_POST['founded_year'] ?? '');
$status            = isset($_POST['status']) ? (int)$_POST['status'] : 1;
$shortDescription  = trim($_POST['short_description'] ?? '');
$history           = trim($_POST['history'] ?? '');
$metaTitle         = trim($_POST['meta_title'] ?? '');
$metaDescription   = trim($_POST['meta_description'] ?? '');
$categoryIds       = isset($_POST['categories']) && is_array($_POST['categories']) ? array_map('intval', $_POST['categories']) : [];
$countryIds        = isset($_POST['countries']) && is_array($_POST['countries']) ? array_map('intval', $_POST['countries']) : [];
$removedGalleryIds = [];
if (!empty($_POST['removed_gallery_ids'])) {
    $decoded = json_decode($_POST['removed_gallery_ids'], true);
    if (is_array($decoded)) {
        $removedGalleryIds = array_map('intval', $decoded);
    }
}

// --- Validation ---
if ($name === '') {
    $errors['name'] = 'Brand name is required.';
} elseif (mb_strlen($name) > 100) {
    $errors['name'] = 'Brand name must be under 100 characters.';
}

if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
    $errors['website'] = 'Please enter a valid URL.';
}

if ($foundedYear !== '') {
    if (!ctype_digit($foundedYear) || (int)$foundedYear < 1800 || (int)$foundedYear > (int)date('Y')) {
        $errors['founded_year'] = 'Please enter a valid founding year.';
    }
}

if (mb_strlen($shortDescription) > 500) {
    $errors['short_description'] = 'Short description must be under 500 characters.';
}
if (mb_strlen($metaTitle) > 255) {
    $errors['meta_title'] = 'Meta title must be under 255 characters.';
}
if (mb_strlen($metaDescription) > 500) {
    $errors['meta_description'] = 'Meta description must be under 500 characters.';
}

function sanitizeRichText($html) {
    if ($html === '') return '';
    $allowedTags = '<p><br><b><strong><i><em><u><s><strike><ol><ul><li><h1><h2><h3><a><blockquote>';
    $clean = strip_tags($html, $allowedTags);
    $clean = preg_replace('/on\w+\s*=\s*(["\']).*?\1/i', '', $clean);
    $clean = preg_replace('/href\s*=\s*(["\'])\s*javascript:.*?\1/i', 'href="#"', $clean);
    return $clean;
}
$history = sanitizeRichText($history);

// --- Reusable: replace-with-hash-check upload helper ---
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

$logoResult  = handleImageReplace('logo', $existingBrand['logo'], 'brands', 2, $errors);
$coverResult = handleImageReplace('cover_image', $existingBrand['cover_image'], 'brands', 3, $errors);

// --- New gallery uploads ---
$newGalleryPaths = [];
if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 3 * 1024 * 1024;

    for ($i = 0; $i < count($_FILES['gallery']['name']); $i++) {
        if ($_FILES['gallery']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        if ($_FILES['gallery']['error'][$i] !== UPLOAD_ERR_OK) {
            $errors['gallery'] = 'One or more gallery images failed to upload.';
            break;
        }

        $tmpName = $_FILES['gallery']['tmp_name'][$i];
        $size = $_FILES['gallery']['size'][$i];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes, true)) {
            $errors['gallery'] = 'Gallery images must be JPG, PNG, or WEBP.';
            break;
        }
        if ($size > $maxSize) {
            $errors['gallery'] = 'Each gallery image must be under 3MB.';
            break;
        }

        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mimeType];
        $uploadDir = __DIR__ . '/../../assets/img/brands/gallery/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '_' . $i . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($tmpName, $destination)) {
            $newGalleryPaths[] = 'assets/img/brands/gallery/' . $filename;
        }
    }
}

// --- Stop on errors — clean up anything just uploaded ---
if (!empty($errors)) {
    $cleanup = array_filter(array_merge(
        [$logoResult['newPath'], $coverResult['newPath']],
        $newGalleryPaths
    ));
    foreach ($cleanup as $path) {
        $full = __DIR__ . '/../../' . $path;
        if (file_exists($full)) unlink($full);
    }
    echo json_encode(["success" => false, "message" => "Please fix the errors below.", "errors" => $errors]);
    exit;
}

$finalLogo  = $logoResult['changed']  ? $logoResult['newPath']  : $existingBrand['logo'];
$finalCover = $coverResult['changed'] ? $coverResult['newPath'] : $existingBrand['cover_image'];

// --- Everything below happens in a transaction ---
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        UPDATE brands SET
            name = :name, logo = :logo, cover_image = :cover_image,
            short_description = :short_description, history = :history,
            website = :website, founded_year = :founded_year, status = :status,
            meta_title = :meta_title, meta_description = :meta_description,
            updated_at = NOW()
        WHERE id = :id
    ");
    $stmt->execute([
        ':name'              => $name,
        ':logo'              => $finalLogo,
        ':cover_image'       => $finalCover,
        ':short_description' => $shortDescription !== '' ? $shortDescription : null,
        ':history'           => $history !== '' ? $history : null,
        ':website'           => $website !== '' ? $website : null,
        ':founded_year'      => $foundedYear !== '' ? (int)$foundedYear : null,
        ':status'            => $status,
        ':meta_title'        => $metaTitle !== '' ? $metaTitle : null,
        ':meta_description'  => $metaDescription !== '' ? $metaDescription : null,
        ':id'                => $id
    ]);

    // --- Sync brand_category: remove all, re-insert selected (simplest, correct sync method) ---
    $pdo->prepare("DELETE FROM brand_category WHERE brand_id = :id")->execute([':id' => $id]);
    if (!empty($categoryIds)) {
        $catStmt = $pdo->prepare("INSERT INTO brand_category (brand_id, category_id, created_at) VALUES (:brand_id, :category_id, NOW())");
        foreach (array_unique($categoryIds) as $catId) {
            if ($catId > 0) $catStmt->execute([':brand_id' => $id, ':category_id' => $catId]);
        }
    }

    // --- Sync brand_country: same pattern ---
    $pdo->prepare("DELETE FROM brand_country WHERE brand_id = :id")->execute([':id' => $id]);
    if (!empty($countryIds)) {
        $ctryStmt = $pdo->prepare("INSERT INTO brand_country (brand_id, country_id, created_at) VALUES (:brand_id, :country_id, NOW())");
        foreach (array_unique($countryIds) as $ctryId) {
            if ($ctryId > 0) $ctryStmt->execute([':brand_id' => $id, ':country_id' => $ctryId]);
        }
    }

    // --- Remove gallery images marked for deletion ---
    $removedImagePaths = [];
    if (!empty($removedGalleryIds)) {
        $placeholders = implode(',', array_fill(0, count($removedGalleryIds), '?'));

        // Fetch paths first so we can delete the actual files after DB commit
        $fetchStmt = $pdo->prepare("SELECT id, image FROM brand_gallery WHERE brand_id = ? AND id IN ($placeholders)");
        $fetchStmt->execute(array_merge([$id], $removedGalleryIds));
        $toRemove = $fetchStmt->fetchAll(PDO::FETCH_ASSOC);
        $removedImagePaths = array_column($toRemove, 'image');

        $delStmt = $pdo->prepare("DELETE FROM brand_gallery WHERE brand_id = ? AND id IN ($placeholders)");
        $delStmt->execute(array_merge([$id], $removedGalleryIds));
    }

    // --- Insert newly added gallery images ---
    if (!empty($newGalleryPaths)) {
        // Continue sort_order after existing max
        $maxOrderStmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), -1) FROM brand_gallery WHERE brand_id = :id");
        $maxOrderStmt->execute([':id' => $id]);
        $nextOrder = (int)$maxOrderStmt->fetchColumn() + 1;

        $galleryStmt = $pdo->prepare("INSERT INTO brand_gallery (brand_id, image, sort_order, created_at) VALUES (:brand_id, :image, :sort_order, NOW())");
        foreach ($newGalleryPaths as $imgPath) {
            $galleryStmt->execute([':brand_id' => $id, ':image' => $imgPath, ':sort_order' => $nextOrder]);
            $nextOrder++;
        }
    }

    $pdo->commit();

    // --- Only after successful commit: delete old physical files ---
    if ($logoResult['changed'] && !empty($existingBrand['logo'])) {
        $old = __DIR__ . '/../../' . $existingBrand['logo'];
        if (file_exists($old)) unlink($old);
    }
    if ($coverResult['changed'] && !empty($existingBrand['cover_image'])) {
        $old = __DIR__ . '/../../' . $existingBrand['cover_image'];
        if (file_exists($old)) unlink($old);
    }
    foreach ($removedImagePaths as $path) {
        $full = __DIR__ . '/../../' . $path;
        if (file_exists($full)) unlink($full);
    }

    echo json_encode(["success" => true, "message" => "Brand updated successfully."]);

} catch (PDOException $e) {
    $pdo->rollBack();

    // Roll back file uploads too since the transaction failed
    $cleanup = array_filter(array_merge(
        [$logoResult['newPath'], $coverResult['newPath']],
        $newGalleryPaths
    ));
    foreach ($cleanup as $path) {
        $full = __DIR__ . '/../../' . $path;
        if (file_exists($full)) unlink($full);
    }

    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}