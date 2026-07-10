<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireApiRole(['admin']);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$errors = [];

// --- Collect + trim inputs ---
$name           = trim($_POST['name'] ?? '');
$code           = strtoupper(trim($_POST['code'] ?? ''));
$currency       = strtoupper(trim($_POST['currency'] ?? ''));
$currencySymbol = trim($_POST['currency_symbol'] ?? '');
$status         = isset($_POST['status']) ? (int)$_POST['status'] : 1;

// --- Validation ---
if ($name === '') {
    $errors['name'] = 'Country name is required.';
} elseif (mb_strlen($name) > 100) {
    $errors['name'] = 'Country name must be under 100 characters.';
}

if ($code === '') {
    $errors['code'] = 'Country code is required.';
} elseif (!preg_match('/^[A-Z]{2}$/', $code)) {
    $errors['code'] = 'Country code must be exactly 2 letters (e.g. US, PK).';
}

if ($currency === '') {
    $errors['currency'] = 'Currency code is required.';
} elseif (mb_strlen($currency) > 10) {
    $errors['currency'] = 'Currency code must be under 10 characters.';
}

if ($currencySymbol === '') {
    $errors['currency_symbol'] = 'Currency symbol is required.';
} elseif (mb_strlen($currencySymbol) > 10) {
    $errors['currency_symbol'] = 'Currency symbol must be under 10 characters.';
}

// --- Check duplicate code (unique index in your schema) ---
if (empty($errors['code'])) {
    $checkStmt = $pdo->prepare("SELECT id FROM countries WHERE code = :code LIMIT 1");
    $checkStmt->execute([':code' => $code]);
    if ($checkStmt->fetch()) {
        $errors['code'] = 'A country with this code already exists.';
    }
}

// --- Generate unique slug from name ---
function generateSlug($pdo, $name) {
    $baseSlug = strtolower(trim($name));
    $baseSlug = preg_replace('/[^a-z0-9]+/', '-', $baseSlug);
    $baseSlug = trim($baseSlug, '-');

    $slug = $baseSlug;
    $counter = 1;

    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM countries WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        if (!$stmt->fetch()) {
            break;
        }
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }

    return $slug;
}

// --- Handle flag image upload (optional) ---
$flagPath = null;

if (isset($_FILES['flag']) && $_FILES['flag']['error'] !== UPLOAD_ERR_NO_FILE) {

    $file = $_FILES['flag'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors['flag'] = 'Flag upload failed. Please try again.';
    } else {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes, true)) {
            $errors['flag'] = 'Only JPG, PNG, WEBP, or SVG images are allowed.';
        } elseif ($file['size'] > $maxSize) {
            $errors['flag'] = 'Flag image must be under 2MB.';
        } else {
            $ext = [
                'image/jpeg'    => 'jpg',
                'image/png'     => 'png',
                'image/webp'    => 'webp',
                'image/svg+xml' => 'svg'
            ][$mimeType];

            $uploadDir = __DIR__ . '/../../assets/img/countries/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = 'flag_' . strtolower($code !== '' ? $code : 'country') . '_' . time() . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $flagPath = 'assets/img/countries/' . $filename;
            } else {
                $errors['flag'] = 'Could not save the uploaded flag image.';
            }
        }
    }
}

// --- Stop here if any validation errors ---
if (!empty($errors)) {
    if ($flagPath && file_exists(__DIR__ . '/../../' . $flagPath)) {
        unlink(__DIR__ . '/../../' . $flagPath);
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
    $slug = generateSlug($pdo, $name);

    $stmt = $pdo->prepare("
        INSERT INTO countries (name, code, currency, currency_symbol, flag, slug, status, created_at, updated_at)
        VALUES (:name, :code, :currency, :currency_symbol, :flag, :slug, :status, NOW(), NOW())
    ");

    $stmt->execute([
        ':name'            => $name,
        ':code'            => $code,
        ':currency'        => $currency,
        ':currency_symbol' => $currencySymbol,
        ':flag'            => $flagPath,
        ':slug'            => $slug,
        ':status'          => $status
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Country created successfully.",
        "id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    if ($flagPath && file_exists(__DIR__ . '/../../' . $flagPath)) {
        unlink(__DIR__ . '/../../' . $flagPath);
    }

    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}