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

// --- Generate a unique slug from the title ---
function generateUniqueSlug($pdo, $title) {
    $base = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($title)), '-');
    if ($base === '') $base = 'post';

    $slug = $base;
    $i = 1;
    $checkStmt = $pdo->prepare("SELECT id FROM blogs WHERE slug = :slug LIMIT 1");
    while (true) {
        $checkStmt->execute([':slug' => $slug]);
        if (!$checkStmt->fetch()) break;
        $i++;
        $slug = $base . '-' . $i;
    }
    return $slug;
}

// --- Handle featured image upload ---
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
            $uploadDir = __DIR__ . '/../../assets/img/blogs/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $filename = 'blog_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $imagePath = 'assets/img/blogs/' . $filename;
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
$authorId = currentUserId(); // logged-in user's id, same helper used across other modules

try {
    $stmt = $pdo->prepare("
        INSERT INTO blogs (
            title, slug, image, excerpt, content, category, author_id,
            views, status, meta_title, meta_description, published_at, created_at
        ) VALUES (
            :title, :slug, :image, :excerpt, :content, :category, :author_id,
            0, :status, :meta_title, :meta_description, :published_at, NOW()
        )
    ");
    $stmt->execute([
        ':title'             => $title,
        ':slug'              => $slug,
        ':image'             => $imagePath,
        ':excerpt'           => $excerpt !== '' ? $excerpt : null,
        ':content'           => $content !== '' ? $content : null,
        ':category'          => $category !== '' ? $category : null,
        ':author_id'         => $authorId ?: null,
        ':status'            => $status,
        ':meta_title'        => $metaTitle !== '' ? $metaTitle : null,
        ':meta_description'  => $metaDescription !== '' ? $metaDescription : null,
        ':published_at'      => $publishedAtSql
    ]);

    echo json_encode(["success" => true, "message" => "Blog created successfully."]);

} catch (PDOException $e) {
    if ($imagePath) {
        $full = __DIR__ . '/../../' . $imagePath;
        if (file_exists($full)) unlink($full);
    }
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}