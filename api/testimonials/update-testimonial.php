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
    echo json_encode(["success" => false, "message" => "Invalid testimonial ID"]);
    exit;
}

$existingStmt = $pdo->prepare("SELECT id, image FROM testimonials WHERE id = :id LIMIT 1");
$existingStmt->execute([':id' => $id]);
$existingTestimonial = $existingStmt->fetch(PDO::FETCH_ASSOC);

if (!$existingTestimonial) {
    echo json_encode(["success" => false, "message" => "Testimonial not found"]);
    exit;
}

$errors = [];

// --- Collect inputs ---
$name        = trim($_POST['name'] ?? '');
$designation = trim($_POST['designation'] ?? '');
$review      = trim($_POST['review'] ?? '');
$rating      = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$status      = isset($_POST['status']) ? (int)$_POST['status'] : 1;

// --- Validation ---
if ($name === '') {
    $errors['name'] = 'Name is required.';
} elseif (mb_strlen($name) > 150) {
    $errors['name'] = 'Name must be under 150 characters.';
}

if (mb_strlen($designation) > 150) {
    $errors['designation'] = 'Designation must be under 150 characters.';
}

if ($review === '') {
    $errors['review'] = 'Review is required.';
}

if ($rating < 1 || $rating > 5) {
    $errors['rating'] = 'Please select a rating between 1 and 5 stars.';
}

function sanitizeRichText($html) {
    if ($html === '') return '';
    $allowedTags = '<p><br><b><strong><i><em><u><blockquote>';
    $clean = strip_tags($html, $allowedTags);
    $clean = preg_replace('/on\w+\s*=\s*(["\']).*?\1/i', '', $clean);
    $clean = preg_replace('/href\s*=\s*(["\'])\s*javascript:.*?\1/i', 'href="#"', $clean);
    return $clean;
}
$review = sanitizeRichText($review);

// --- Reusable: replace-with-hash-check upload helper (same pattern as brands) ---
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

$imageResult = handleImageReplace('image', $existingTestimonial['image'], 'testimonials', 2, $errors);

// --- Stop on errors — clean up anything just uploaded ---
if (!empty($errors)) {
    if ($imageResult['newPath']) {
        $full = __DIR__ . '/../../' . $imageResult['newPath'];
        if (file_exists($full)) unlink($full);
    }
    echo json_encode(["success" => false, "message" => "Please fix the errors below.", "errors" => $errors]);
    exit;
}

$finalImage = $imageResult['changed'] ? $imageResult['newPath'] : $existingTestimonial['image'];

try {
    $stmt = $pdo->prepare("
        UPDATE testimonials SET
            name = :name, designation = :designation, image = :image,
            review = :review, rating = :rating, status = :status
        WHERE id = :id
    ");
    $stmt->execute([
        ':name'        => $name,
        ':designation' => $designation !== '' ? $designation : null,
        ':image'       => $finalImage,
        ':review'      => $review,
        ':rating'      => $rating,
        ':status'      => $status,
        ':id'          => $id
    ]);

    // --- Only after successful update: delete old physical file if it was replaced ---
    if ($imageResult['changed'] && !empty($existingTestimonial['image'])) {
        $old = __DIR__ . '/../../' . $existingTestimonial['image'];
        if (file_exists($old)) unlink($old);
    }

    echo json_encode(["success" => true, "message" => "Testimonial updated successfully."]);

} catch (PDOException $e) {
    // Roll back the newly uploaded file if update fails
    if ($imageResult['newPath']) {
        $full = __DIR__ . '/../../' . $imageResult['newPath'];
        if (file_exists($full)) unlink($full);
    }
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}