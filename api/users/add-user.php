<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin', 'employee']);

header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$errors = [];

// --- Collect + trim inputs ---
$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$role     = trim($_POST['role'] ?? 'guest');
$status   = isset($_POST['status']) ? (int)$_POST['status'] : 1;

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

if ($password === '') {
    $errors['password'] = 'Password is required.';
} elseif (strlen($password) < 6) {
    $errors['password'] = 'Password must be at least 6 characters.';
}

if ($phone !== '' && mb_strlen($phone) > 20) {
    $errors['phone'] = 'Phone must be under 20 characters.';
}

$allowedRoles = ['admin', 'employee', 'guest'];
if (!in_array($role, $allowedRoles, true)) {
    $errors['role'] = 'Invalid role selected.';
}

// --- Check duplicate email BEFORE touching the file system ---
if (empty($errors['email'])) {
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $checkStmt->execute([':email' => $email]);
    if ($checkStmt->fetch()) {
        $errors['email'] = 'This email is already registered.';
    }
}

// --- Handle image upload (optional field) ---
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

            $uploadDir = __DIR__ . '/../../assets/img/users/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = 'user_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Path stored in DB, relative to site root
                $imagePath = 'assets/img/users/' . $filename;
            } else {
                $errors['image'] = 'Could not save the uploaded image.';
            }
        }
    }
}

// --- Stop here if any validation errors ---
if (!empty($errors)) {
    echo json_encode([
        "success" => false,
        "message" => "Please fix the errors below.",
        "errors" => $errors
    ]);
    exit;
}

// --- Insert into database ---
try {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, phone, image, role, status, created_at, updated_at)
        VALUES (:name, :email, :password, :phone, :image, :role, :status, NOW(), NOW())
    ");

    $stmt->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':password' => $hashedPassword,
        ':phone'    => $phone !== '' ? $phone : null,
        ':image'    => $imagePath,
        ':role'     => $role,
        ':status'   => $status
    ]);

    echo json_encode([
        "success" => true,
        "message" => "User created successfully.",
        "id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    // Clean up uploaded file if DB insert fails
    if ($imagePath && file_exists(__DIR__ . '/../' . $imagePath)) {
        unlink(__DIR__ . '/../' . $imagePath);
    }

    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}