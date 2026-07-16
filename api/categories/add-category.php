<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']); // matches the create/edit = admin-only pattern from Countries
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$errors = [];

// --- Collect + trim inputs ---
$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? ''); // HTML from Quill — sanitized below
$sortOrder   = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
$status      = isset($_POST['status']) ? (int)$_POST['status'] : 1;
$parentIdRaw = trim($_POST['parent_id'] ?? '');
$parentId    = ($parentIdRaw === '') ? null : (int) $parentIdRaw;

// --- Validation ---
if ($name === '') {
    $errors['name'] = 'Category name is required.';
} elseif (mb_strlen($name) > 100) {
    $errors['name'] = 'Category name must be under 100 characters.';
}

if ($sortOrder < 0) {
    $errors['sort_order'] = 'Sort order cannot be negative.';
}

// --- Validate parent category (only top-level categories can be a parent,
//     keeping the tree to a clean 2-level structure: Parent -> Child) ---
if ($parentId !== null) {
    $parentStmt = $pdo->prepare("SELECT id, parent_id FROM categories WHERE id = :id LIMIT 1");
    $parentStmt->execute([':id' => $parentId]);
    $parentRow = $parentStmt->fetch(PDO::FETCH_ASSOC);

    if (!$parentRow) {
        $errors['parent_id'] = 'Selected parent category was not found.';
    } elseif ($parentRow['parent_id'] !== null) {
        $errors['parent_id'] = 'A child category cannot itself be used as a parent.';
    }
}

// --- Sanitize the rich text HTML before storing ---
// Strips <script>, event handlers (onclick etc.), and other dangerous tags,
// while keeping safe formatting tags Quill produces (b, i, u, ol, ul, li, a, p, h1-h3, br, strong, em).
function sanitizeRichText($html) {
    if ($html === '') return '';

    $allowedTags = '<p><br><b><strong><i><em><u><s><strike><ol><ul><li><h1><h2><h3><a>';
    $clean = strip_tags($html, $allowedTags);

    // Strip any leftover on*="..." event handler attributes and javascript: hrefs
    $clean = preg_replace('/on\w+\s*=\s*(["\']).*?\1/i', '', $clean);
    $clean = preg_replace('/href\s*=\s*(["\'])\s*javascript:.*?\1/i', 'href="#"', $clean);

    return $clean;
}

$description = sanitizeRichText($description);

// --- Generate unique slug from name ---
function generateCategorySlug($pdo, $name) {
    $baseSlug = strtolower(trim($name));
    $baseSlug = preg_replace('/[^a-z0-9]+/', '-', $baseSlug);
    $baseSlug = trim($baseSlug, '-');

    $slug = $baseSlug;
    $counter = 1;

    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        if (!$stmt->fetch()) {
            break;
        }
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }

    return $slug;
}

// --- Handle image upload (optional) ---
$imagePath = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

    $file = $_FILES['image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors['image'] = 'Image upload failed. Please try again.';
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
            $ext = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ][$mimeType];

            $uploadDir = __DIR__ . '/../../assets/img/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = 'category_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $imagePath = 'assets/img/categories/' . $filename;
            } else {
                $errors['image'] = 'Could not save the uploaded image.';
            }
        }
    }
}

// --- Stop here if any validation errors ---
if (!empty($errors)) {
    if ($imagePath && file_exists(__DIR__ . '/../../' . $imagePath)) {
        unlink(__DIR__ . '/../../' . $imagePath);
    }
    echo json_encode([
        "success" => false,
        "message" => "Please fix the errors below.",
        "errors" => $errors
    ]);
    exit;
}

// --- Insert into database ---
try {
    $slug = generateCategorySlug($pdo, $name);

    $stmt = $pdo->prepare("
        INSERT INTO categories (parent_id, name, slug, image, description, status, sort_order, created_at, updated_at)
        VALUES (:parent_id, :name, :slug, :image, :description, :status, :sort_order, NOW(), NOW())
    ");

    $stmt->execute([
        ':parent_id'   => $parentId,
        ':name'        => $name,
        ':slug'        => $slug,
        ':image'       => $imagePath,
        ':description' => $description !== '' ? $description : null,
        ':status'      => $status,
        ':sort_order'  => $sortOrder
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Category created successfully.",
        "id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    if ($imagePath && file_exists(__DIR__ . '/../../' . $imagePath)) {
        unlink(__DIR__ . '/../../' . $imagePath);
    }

    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}