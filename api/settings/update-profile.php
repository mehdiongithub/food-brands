<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$userId = currentUserId();

$existingStmt = $pdo->prepare("SELECT id, image FROM users WHERE id = :id LIMIT 1");
$existingStmt->execute([':id' => $userId]);
$existingUser = $existingStmt->fetch(PDO::FETCH_ASSOC);

if (!$existingUser) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

$errors = [];

// --- Collect inputs ---
$name  = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');

// --- Validation ---
if ($name === '') {
    $errors['name'] = 'Name is required.';
} elseif (mb_strlen($name) > 100) {
    $errors['name'] = 'Name must be under 100 characters.';
}

if ($email === '') {
    $errors['email'] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
} elseif (mb_strlen($email) > 150) {
    $errors['email'] = 'Email must be under 150 characters.';
} else {
    // Ensure email isn't already used by a DIFFERENT user
    $emailCheck = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
    $emailCheck->execute([':email' => $email, ':id' => $userId]);
    if ($emailCheck->fetch()) {
        $errors['email'] = 'This email is already in use by another account.';
    }
}

if ($phone !== '' && mb_strlen($phone) > 20) {
    $errors['phone'] = 'Phone number must be under 20 characters.';
}

// --- Reusable: replace-with-hash-check upload helper (same pattern as brands/testimonials/offers) ---
function handleImageReplace($fileKey, $existingPath, $subfolder, $maxSizeMB, &$errors) {
    $result = ['changed' => false, 'newPath' => null];

    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return $result;
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
                return $result;
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

$imageResult = handleImageReplace('image', $existingUser['image'], 'users', 2, $errors);

// --- Stop on errors — clean up anything just uploaded ---
if (!empty($errors)) {
    if ($imageResult['newPath']) {
        $full = __DIR__ . '/../../' . $imageResult['newPath'];
        if (file_exists($full)) unlink($full);
    }
    echo json_encode(["success" => false, "message" => "Please fix the errors below.", "errors" => $errors]);
    exit;
}

$finalImage = $imageResult['changed'] ? $imageResult['newPath'] : $existingUser['image'];

try {
    $stmt = $pdo->prepare("
        UPDATE users SET
            name = :name, email = :email, phone = :phone, image = :image, updated_at = NOW()
        WHERE id = :id
    ");
    $stmt->execute([
        ':name'  => $name,
        ':email' => $email,
        ':phone' => $phone !== '' ? $phone : null,
        ':image' => $finalImage,
        ':id'    => $userId
    ]);

    // --- Only after successful update: delete old physical file if it was replaced ---
    if ($imageResult['changed'] && !empty($existingUser['image'])) {
        $old = __DIR__ . '/../../' . $existingUser['image'];
        if (file_exists($old)) unlink($old);
    }

    // Keep session display values in sync if your app reads them from $_SESSION
    if (isset($_SESSION)) {
        $_SESSION['user_name']  = $name;
        $_SESSION['user_email'] = $email;
        if ($finalImage) $_SESSION['user_image'] = $finalImage;
    }

    echo json_encode(["success" => true, "message" => "Profile updated successfully."]);

} catch (PDOException $e) {
    if ($imageResult['newPath']) {
        $full = __DIR__ . '/../../' . $imageResult['newPath'];
        if (file_exists($full)) unlink($full);
    }
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}