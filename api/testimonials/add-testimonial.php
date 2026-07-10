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
            $uploadDir = __DIR__ . '/../../assets/img/testimonials/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $filename = 'testimonial_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $imagePath = 'assets/img/testimonials/' . $filename;
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

try {
    $stmt = $pdo->prepare("
        INSERT INTO testimonials (name, designation, image, review, rating, status, created_at)
        VALUES (:name, :designation, :image, :review, :rating, :status, NOW())
    ");
    $stmt->execute([
        ':name'        => $name,
        ':designation' => $designation !== '' ? $designation : null,
        ':image'       => $imagePath,
        ':review'      => $review,
        ':rating'      => $rating,
        ':status'      => $status
    ]);

    echo json_encode(["success" => true, "message" => "Testimonial created successfully."]);

} catch (PDOException $e) {
    // Roll back the uploaded file if insert fails
    if ($imagePath) {
        $full = __DIR__ . '/../../' . $imagePath;
        if (file_exists($full)) unlink($full);
    }
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}