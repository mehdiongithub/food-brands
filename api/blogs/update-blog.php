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
    echo json_encode(["success" => false, "message" => "Invalid blog ID"]);
    exit;
}

$existingStmt = $pdo->prepare("SELECT id, image, slug FROM blogs WHERE id = :id LIMIT 1");
$existingStmt->execute([':id' => $id]);
$existingBlog = $existingStmt->fetch(PDO::FETCH_ASSOC);

if (!$existingBlog) {
    echo json_encode(["success" => false, "message" => "Blog not found"]);
    exit;
}

$errors = [];

// --- Collect inputs ---
$title            = trim($_POST['title'] ?? '');
$category         = trim($_POST['category'] ?? '');
$excerpt          = trim($_POST['excerpt'] ?? '');
$content          = trim($_POST['content'] ?? '');
$status           = isset($_POST['status']) ? (int)$_POST['status'] : 1;
$publishedAt      = trim($_POST['published_at'] ?? '');
$metaTitle        = trim($_POST['meta_title'] ?? '');
$metaDescription  = trim($_POST['meta_description'] ?? '');

// --- Validation ---
if ($title === '') {
    $errors['title'] = 'Title is required.';
} elseif (mb_strlen($title) > 255) {
    $errors['title'] = 'Title must be under 255 characters.';
}

if ($category !== '' && mb_strlen($category) > 100) {
    $errors['category'] = 'Category must be under 100 characters.';
}

if (mb_strlen($excerpt) > 500) {
    $errors['excerpt'] = 'Excerpt must be under 500 characters.';
}

if (mb_strlen($metaTitle) > 255) {
    $errors['meta_title'] = 'Meta title must be under 255 characters.';
}

if (mb_strlen($metaDescription) > 500) {
    $errors['meta_description'] = 'Meta description must be under 500 characters.';
}

// --- published_at: accept datetime-local (Y-m-dTH:i) or empty ---
$publishedAtSql = null;
if ($publishedAt !== '') {
    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $publishedAt);
    if (!$dt) {
        $errors['published_at'] = 'Invalid publish date/time.';
    } else {
        $publishedAtSql = $dt->format('Y-m-d H:i:s');
    }
}
// If publishing now and no date given, default to right now
if ($status === 1 && $publishedAtSql === null) {
    $publishedAtSql = date('Y-m-d H:i:s');
}

function sanitizeRichText($html) {
    if ($html === '') return '';
    $allowedTags = '<p><br><b><strong><i><em><u><s><strike><ol><ul><li><h1><h2><h3><a><blockquote><img>';
    $clean = strip_tags($html, $allowedTags);
    $clean = preg_replace('/on\w+\s*=\s*(["\']).*?\1/i', '', $clean);
    $clean = preg_replace('/href\s*=\s*(["\'])\s*javascript:.*?\1/i', 'href="#"', $clean);
    $clean = preg_replace('/src\s*=\s*(["\'])\s*javascript:.*?\1/i', 'src="#"', $clean);
    return $clean;
}
$content = sanitizeRichText($content);

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

$imageResult = handleImageReplace('image', $existingBlog['image'], 'blogs', 2, $errors);

// --- Stop on errors — clean up anything just uploaded ---
if (!empty($errors)) {
    if ($imageResult['newPath']) {
        $full = __DIR__ . '/../../' . $imageResult['newPath'];
        if (file_exists($full)) unlink($full);
    }
    echo json_encode(["success" => false, "message" => "Please fix the errors below.", "errors" => $errors]);
    exit;
}

$finalImage = $imageResult['changed'] ? $imageResult['newPath'] : $existingBlog['image'];

// --- Regenerate slug only if title changed enough to warrant it; keep stable otherwise ---
function generateUniqueSlug($pdo, $title, $excludeId) {
    $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-');
    if ($base === '') $base = 'post';

    $slug = $base;
    $i = 1;
    $checkStmt = $pdo->prepare("SELECT id FROM blogs WHERE slug = :slug AND id != :id LIMIT 1");
    while (true) {
        $checkStmt->execute([':slug' => $slug, ':id' => $excludeId]);
        if (!$checkStmt->fetch()) break;
        $i++;
        $slug = $base . '-' . $i;
    }
    return $slug;
}

$currentBaseSlug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-');
$existingBaseSlug = preg_replace('/-\d+$/', '', $existingBlog['slug'] ?? '');
$slug = ($currentBaseSlug !== $existingBaseSlug)
    ? generateUniqueSlug($pdo, $title, $id)
    : $existingBlog['slug'];

try {
    $stmt = $pdo->prepare("
        UPDATE blogs SET
            title = :title, slug = :slug, image = :image, excerpt = :excerpt,
            content = :content, category = :category, status = :status,
            meta_title = :meta_title, meta_description = :meta_description,
            published_at = :published_at, updated_at = NOW()
        WHERE id = :id
    ");
    $stmt->execute([
        ':title'             => $title,
        ':slug'              => $slug,
        ':image'             => $finalImage,
        ':excerpt'           => $excerpt !== '' ? $excerpt : null,
        ':content'           => $content !== '' ? $content : null,
        ':category'          => $category !== '' ? $category : null,
        ':status'            => $status,
        ':meta_title'        => $metaTitle !== '' ? $metaTitle : null,
        ':meta_description'  => $metaDescription !== '' ? $metaDescription : null,
        ':published_at'      => $publishedAtSql,
        ':id'                => $id
    ]);

    // --- Only after successful update: delete old physical file if it was replaced ---
    if ($imageResult['changed'] && !empty($existingBlog['image'])) {
        $old = __DIR__ . '/../../' . $existingBlog['image'];
        if (file_exists($old)) unlink($old);
    }

    echo json_encode(["success" => true, "message" => "Blog updated successfully."]);

} catch (PDOException $e) {
    if ($imageResult['newPath']) {
        $full = __DIR__ . '/../../' . $imageResult['newPath'];
        if (file_exists($full)) unlink($full);
    }
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}