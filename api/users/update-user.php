<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']);



header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid user ID"]);
    exit;
}

// --- Confirm the user actually exists first ---
$existingStmt = $pdo->prepare("SELECT id, image FROM users WHERE id = :id LIMIT 1");
$existingStmt->execute([':id' => $id]);
$existingUser = $existingStmt->fetch(PDO::FETCH_ASSOC);

if (!$existingUser) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

$errors = [];

// --- Collect + trim inputs ---
$name           = trim($_POST['name'] ?? '');
$email          = trim($_POST['email'] ?? '');
$password       = trim($_POST['password'] ?? ''); // optional on edit
$phone          = trim($_POST['phone'] ?? '');
$role           = trim($_POST['role'] ?? 'guest');
$status         = isset($_POST['status']) ? (int)$_POST['status'] : 1;
$existingImage  = trim($_POST['existing_image'] ?? ''); // path currently in DB, sent back from the form

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
}

// Password is optional on update — only validate if they typed something
if ($password !== '' && strlen($password) < 6) {
    $errors['password'] = 'Password must be at least 6 characters.';
}

if ($phone !== '' && mb_strlen($phone) > 20) {
    $errors['phone'] = 'Phone must be under 20 characters.';
}

$allowedRoles = ['admin', 'employee', 'guest'];
if (!in_array($role, $allowedRoles, true)) {
    $errors['role'] = 'Invalid role selected.';
}

// --- Check duplicate email, excluding this user's own row ---
if (empty($errors['email'])) {
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1");
    $checkStmt->execute([':email' => $email, ':id' => $id]);
    if ($checkStmt->fetch()) {
        $errors['email'] = 'This email is already registered to another user.';
    }
}

// --- Handle image upload (optional — only replace if a new file is sent) ---
$newImagePath = null;   // set only if a new upload succeeds
$imageChanged = false;

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

            $uploadDir = __DIR__ . '/../../assets/img/users/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = 'user_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $newImagePath = 'assets/img/users/' . $filename;
                $imageChanged = true;
            } else {
                $errors['image'] = 'Could not save the uploaded image.';
            }
        }
    }
}

// --- Stop here if any validation errors — clean up newly uploaded file if one was saved ---
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

// --- Decide final image value to store ---
// If a new image was uploaded, use it. Otherwise keep whatever was already in the DB.
$finalImage = $imageChanged ? $newImagePath : $existingUser['image'];

// --- Build the update query dynamically depending on whether password was changed ---
try {
    if ($password !== '') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            UPDATE users
            SET name = :name, email = :email, password = :password, phone = :phone,
                image = :image, role = :role, status = :status, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':password' => $hashedPassword,
            ':phone'    => $phone !== '' ? $phone : null,
            ':image'    => $finalImage,
            ':role'     => $role,
            ':status'   => $status,
            ':id'       => $id
        ]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE users
            SET name = :name, email = :email, phone = :phone,
                image = :image, role = :role, status = :status, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':phone'    => $phone !== '' ? $phone : null,
            ':image'    => $finalImage,
            ':role'     => $role,
            ':status'   => $status,
            ':id'       => $id
        ]);
    }

    // --- Only delete the OLD image after the DB update succeeds, and only if it changed ---
    if ($imageChanged && !empty($existingUser['image'])) {
        $oldImagePath = __DIR__ . '/../../' . $existingUser['image'];
        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "User updated successfully."
    ]);

} catch (PDOException $e) {
    // If the DB update fails, remove the newly uploaded image too — nothing should be orphaned
    if ($imageChanged && $newImagePath && file_exists(__DIR__ . '/../../' . $newImagePath)) {
        unlink(__DIR__ . '/../../' . $newImagePath);
    }

    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}