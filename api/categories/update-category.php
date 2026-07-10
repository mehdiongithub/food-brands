<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']); // only admin can edit
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid category ID"]);
    exit;
}

// --- Confirm the category actually exists first ---
$existingStmt = $pdo->prepare("SELECT id, image FROM categories WHERE id = :id LIMIT 1");
$existingStmt->execute([':id' => $id]);
$existingCategory = $existingStmt->fetch(PDO::FETCH_ASSOC);

if (!$existingCategory) {
    echo json_encode(["success" => false, "message" => "Category not found"]);
    exit;
}

$errors = [];

// --- Collect + trim inputs ---
$name        = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$sortOrder   = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
$status      = isset($_POST['status']) ? (int)$_POST['status'] : 1;

// --- Validation ---
if ($name === '') {
    $errors['name'] = 'Category name is required.';
} elseif (mb_strlen($name) > 100) {
    $errors['name'] = 'Category name must be under 100 characters.';
}

if ($sortOrder < 0) {
    $errors['sort_order'] = 'Sort order cannot be negative.';
}

// --- Sanitize rich text HTML ---
function sanitizeRichText($html) {
    if ($html === '') return '';

    $allowedTags = '<p><br><b><strong><i><em><u><s><strike><ol><ul><li><h1><h2><h3><a>';
    $clean = strip_tags($html, $allowedTags);
    $clean = preg_replace('/on\w+\s*=\s*(["\']).*?\1/i', '', $clean);
    $clean = preg_replace('/href\s*=\s*(["\'])\s*javascript:.*?\1/i', 'href="#"', $clean);

    return $clean;
}

$description = sanitizeRichText($description);

// --- Handle image upload (optional — only replace if a new file is sent) ---
$newImagePath = null;
$imageChanged = false;
$imageIsDuplicateOfOld = false;

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

            // --- Compare against existing image content, not just filename ---
            if (!empty($existingCategory['image'])) {
                $existingFullPath = __DIR__ . '/../../' . $existingCategory['image'];
                if (file_exists($existingFullPath)) {
                    $newHash = hash_file('sha256', $file['tmp_name']);
                    $oldHash = hash_file('sha256', $existingFullPath);
                    if ($newHash === $oldHash) {
                        $imageIsDuplicateOfOld = true;
                    }
                }
            }

            if ($imageIsDuplicateOfOld) {
                // Identical image re-uploaded — no actual change needed
                $imageChanged = false;
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
                    $newImagePath = 'assets/img/categories/' . $filename;
                    $imageChanged = true;
                } else {
                    $errors['image'] = 'Could not save the uploaded image.';
                }
            }
        }
    }
}

// --- Stop here if any validation errors ---
if (!empty($errors)) {
    if ($newImagePath && file_exists(__DIR__ . '/../../' . $newImagePath)) {
        unlink(__DIR__ . '/../../' . $newImagePath);
    }
    echo json_encode([
        "success" => false,
        "message" => "Please fix the errors below.",
        "errors" => $errors
    ]);
    exit;
}

// --- Decide final image value ---
$finalImage = $imageChanged ? $newImagePath : $existingCategory['image'];

// --- Update database ---
try {
    $stmt = $pdo->prepare("
        UPDATE categories
        SET name = :name, description = :description, image = :image,
            status = :status, sort_order = :sort_order, updated_at = NOW()
        WHERE id = :id
    ");
    $stmt->execute([
        ':name'        => $name,
        ':description' => $description !== '' ? $description : null,
        ':image'       => $finalImage,
        ':status'      => $status,
        ':sort_order'  => $sortOrder,
        ':id'          => $id
    ]);

    // --- Only delete OLD image after DB update succeeds, and only if it genuinely changed ---
    if ($imageChanged && !empty($existingCategory['image'])) {
        $oldImagePath = __DIR__ . '/../../' . $existingCategory['image'];
        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Category updated successfully."
    ]);

} catch (PDOException $e) {
    if ($imageChanged && $newImagePath && file_exists(__DIR__ . '/../../' . $newImagePath)) {
        unlink(__DIR__ . '/../../' . $newImagePath);
    }

    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}