<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']); // only admin can create brands
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$errors = [];

// --- Collect + trim inputs ---
$name              = trim($_POST['name'] ?? '');
$website           = trim($_POST['website'] ?? '');
$foundedYear       = trim($_POST['founded_year'] ?? '');
$status            = isset($_POST['status']) ? (int)$_POST['status'] : 1;
$shortDescription  = trim($_POST['short_description'] ?? '');
$history           = trim($_POST['history'] ?? ''); // rich HTML from Quill
$metaTitle         = trim($_POST['meta_title'] ?? '');
$metaDescription   = trim($_POST['meta_description'] ?? '');
$categoryIds       = isset($_POST['categories']) && is_array($_POST['categories']) ? array_map('intval', $_POST['categories']) : [];
$countryIds        = isset($_POST['countries']) && is_array($_POST['countries']) ? array_map('intval', $_POST['countries']) : [];

// --- Validation ---
if ($name === '') {
    $errors['name'] = 'Brand name is required.';
} elseif (mb_strlen($name) > 100) {
    $errors['name'] = 'Brand name must be under 100 characters.';
}

if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
    $errors['website'] = 'Please enter a valid URL (e.g. https://example.com).';
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

// --- Sanitize rich text HTML (history) ---
function sanitizeRichText($html) {
    if ($html === '') return '';
    $allowedTags = '<p><br><b><strong><i><em><u><s><strike><ol><ul><li><h1><h2><h3><a><blockquote>';
    $clean = strip_tags($html, $allowedTags);
    $clean = preg_replace('/on\w+\s*=\s*(["\']).*?\1/i', '', $clean);
    $clean = preg_replace('/href\s*=\s*(["\'])\s*javascript:.*?\1/i', 'href="#"', $clean);
    return $clean;
}
$history = sanitizeRichText($history);

// --- Slug generator ---
function generateBrandSlug($pdo, $name) {
    $baseSlug = strtolower(trim($name));
    $baseSlug = preg_replace('/[^a-z0-9]+/', '-', $baseSlug);
    $baseSlug = trim($baseSlug, '-');

    $slug = $baseSlug;
    $counter = 1;
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM brands WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        if (!$stmt->fetch()) break;
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
    return $slug;
}

// --- Generic single-image upload helper ---
function uploadSingleImage($fileKey, $subfolder, $maxSizeMB, &$errors) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fileKey];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[$fileKey] = 'Upload failed. Please try again.';
        return null;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = $maxSizeMB * 1024 * 1024;

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes, true)) {
        $errors[$fileKey] = 'Only JPG, PNG, or WEBP images are allowed.';
        return null;
    }
    if ($file['size'] > $maxSize) {
        $errors[$fileKey] = "Image must be under {$maxSizeMB}MB.";
        return null;
    }

    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mimeType];
    $uploadDir = __DIR__ . '/../../assets/img/' . $subfolder . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = $subfolder . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return 'assets/img/' . $subfolder . '/' . $filename;
    }

    $errors[$fileKey] = 'Could not save the uploaded image.';
    return null;
}

// --- Upload logo & cover image ---
$logoPath  = uploadSingleImage('logo', 'brands', 2, $errors);
$coverPath = uploadSingleImage('cover_image', 'brands', 3, $errors);

// --- Upload gallery images (multiple) ---
$galleryPaths = [];
if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
    $galleryCount = count($_FILES['gallery']['name']);

    if ($galleryCount > 10) {
        $errors['gallery'] = 'You can upload a maximum of 10 gallery images.';
    } else {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 3 * 1024 * 1024; // 3MB per gallery image

        for ($i = 0; $i < $galleryCount; $i++) {
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
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '_' . $i . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($tmpName, $destination)) {
                $galleryPaths[] = 'assets/img/brands/gallery/' . $filename;
            }
        }
    }
}

// --- Stop here if any validation/upload errors — clean up anything already saved ---
if (!empty($errors)) {
    $cleanupPaths = array_filter(array_merge([$logoPath, $coverPath], $galleryPaths));
    foreach ($cleanupPaths as $path) {
        $fullPath = __DIR__ . '/../../' . $path;
        if (file_exists($fullPath)) unlink($fullPath);
    }

    echo json_encode([
        "success" => false,
        "message" => "Please fix the errors below.",
        "errors" => $errors
    ]);
    exit;
}

// --- Insert everything in a transaction (brand + category links + country links + gallery) ---
try {
    $pdo->beginTransaction();

    $slug = generateBrandSlug($pdo, $name);
    $createdBy = currentUserId();

    $stmt = $pdo->prepare("
        INSERT INTO brands (
            name, slug, logo, cover_image, short_description, history,
            website, founded_year, status, meta_title, meta_description,
            created_by, created_at, updated_at
        ) VALUES (
            :name, :slug, :logo, :cover_image, :short_description, :history,
            :website, :founded_year, :status, :meta_title, :meta_description,
            :created_by, NOW(), NOW()
        )
    ");

    $stmt->execute([
        ':name'              => $name,
        ':slug'              => $slug,
        ':logo'              => $logoPath,
        ':cover_image'       => $coverPath,
        ':short_description' => $shortDescription !== '' ? $shortDescription : null,
        ':history'           => $history !== '' ? $history : null,
        ':website'           => $website !== '' ? $website : null,
        ':founded_year'      => $foundedYear !== '' ? (int)$foundedYear : null,
        ':status'            => $status,
        ':meta_title'        => $metaTitle !== '' ? $metaTitle : null,
        ':meta_description'  => $metaDescription !== '' ? $metaDescription : null,
        ':created_by'        => $createdBy
    ]);

    $brandId = $pdo->lastInsertId();

    // --- Insert category links ---
    if (!empty($categoryIds)) {
        $catStmt = $pdo->prepare("INSERT INTO brand_category (brand_id, category_id, created_at) VALUES (:brand_id, :category_id, NOW())");
        foreach ($categoryIds as $catId) {
            if ($catId > 0) {
                $catStmt->execute([':brand_id' => $brandId, ':category_id' => $catId]);
            }
        }
    }

    // --- Insert country links ---
    if (!empty($countryIds)) {
        $ctryStmt = $pdo->prepare("INSERT INTO brand_country (brand_id, country_id, created_at) VALUES (:brand_id, :country_id, NOW())");
        foreach ($countryIds as $ctryId) {
            if ($ctryId > 0) {
                $ctryStmt->execute([':brand_id' => $brandId, ':country_id' => $ctryId]);
            }
        }
    }

    // --- Insert gallery images ---
    if (!empty($galleryPaths)) {
        $galleryStmt = $pdo->prepare("INSERT INTO brand_gallery (brand_id, image, sort_order, created_at) VALUES (:brand_id, :image, :sort_order, NOW())");
        foreach ($galleryPaths as $index => $imgPath) {
            $galleryStmt->execute([
                ':brand_id'   => $brandId,
                ':image'      => $imgPath,
                ':sort_order' => $index
            ]);
        }
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Brand created successfully.",
        "id" => $brandId
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();

    // Clean up all uploaded files since the transaction failed
    $cleanupPaths = array_filter(array_merge([$logoPath, $coverPath], $galleryPaths));
    foreach ($cleanupPaths as $path) {
        $fullPath = __DIR__ . '/../../' . $path;
        if (file_exists($fullPath)) unlink($fullPath);
    }

    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}