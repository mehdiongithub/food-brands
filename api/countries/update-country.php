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
    echo json_encode(["success" => false, "message" => "Invalid country ID"]);
    exit;
}

// --- Confirm the country actually exists first ---
$existingStmt = $pdo->prepare("SELECT id, flag FROM countries WHERE id = :id LIMIT 1");
$existingStmt->execute([':id' => $id]);
$existingCountry = $existingStmt->fetch(PDO::FETCH_ASSOC);

if (!$existingCountry) {
    echo json_encode(["success" => false, "message" => "Country not found"]);
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

// --- Check duplicate code, excluding this country's own row ---
if (empty($errors['code'])) {
    $checkStmt = $pdo->prepare("SELECT id FROM countries WHERE code = :code AND id != :id LIMIT 1");
    $checkStmt->execute([':code' => $code, ':id' => $id]);
    if ($checkStmt->fetch()) {
        $errors['code'] = 'Another country already uses this code.';
    }
}

// --- Handle flag upload (optional — only replace if a new file is sent) ---
$newFlagPath = null;
$flagChanged = false;
$flagIsDuplicateOfOld = false;

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

            // --- Check if the newly uploaded file is identical to the existing one ---
            // Compares actual file content (hash), not just filename, so re-uploading
            // the exact same image is correctly treated as "no change".
            if (!empty($existingCountry['flag'])) {
                $existingFullPath = __DIR__ . '/../../' . $existingCountry['flag'];
                if (file_exists($existingFullPath)) {
                    $newHash = hash_file('sha256', $file['tmp_name']);
                    $oldHash = hash_file('sha256', $existingFullPath);
                    if ($newHash === $oldHash) {
                        $flagIsDuplicateOfOld = true;
                    }
                }
            }

            if ($flagIsDuplicateOfOld) {
                // Same image re-uploaded — skip saving a new file entirely, keep existing path
                $flagChanged = false;
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
                    $newFlagPath = 'assets/img/countries/' . $filename;
                    $flagChanged = true;
                } else {
                    $errors['flag'] = 'Could not save the uploaded flag image.';
                }
            }
        }
    }
}

// --- Stop here if any validation errors — clean up newly uploaded file if one was saved ---
if (!empty($errors)) {
    if ($newFlagPath && file_exists(__DIR__ . '/../../' . $newFlagPath)) {
        unlink(__DIR__ . '/../../' . $newFlagPath);
    }
    echo json_encode([
        "success" => false,
        "message" => "Please fix the errors below.",
        "errors" => $errors
    ]);
    exit;
}

// --- Decide final flag value to store ---
// New (different) image uploaded -> use it.
// Same image re-uploaded, or no file selected -> keep whatever was already in the DB.
$finalFlag = $flagChanged ? $newFlagPath : $existingCountry['flag'];

// --- Update database ---
try {
    $stmt = $pdo->prepare("
        UPDATE countries
        SET name = :name, code = :code, currency = :currency, currency_symbol = :currency_symbol,
            flag = :flag, status = :status, updated_at = NOW()
        WHERE id = :id
    ");
    $stmt->execute([
        ':name'            => $name,
        ':code'            => $code,
        ':currency'        => $currency,
        ':currency_symbol' => $currencySymbol,
        ':flag'            => $finalFlag,
        ':status'          => $status,
        ':id'              => $id
    ]);

    // --- Only delete the OLD image after DB update succeeds, and only if it truly changed ---
    if ($flagChanged && !empty($existingCountry['flag'])) {
        $oldFlagPath = __DIR__ . '/../../' . $existingCountry['flag'];
        if (file_exists($oldFlagPath)) {
            unlink($oldFlagPath);
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Country updated successfully."
    ]);

} catch (PDOException $e) {
    // If DB update fails, remove the newly uploaded image too — avoid orphaned files
    if ($flagChanged && $newFlagPath && file_exists(__DIR__ . '/../../' . $newFlagPath)) {
        unlink(__DIR__ . '/../../' . $newFlagPath);
    }

    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}