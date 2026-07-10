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
    echo json_encode(["success" => false, "message" => "Invalid product ID"]);
    exit;
}

$existingStmt = $pdo->prepare("SELECT id, image FROM products WHERE id = :id LIMIT 1");
$existingStmt->execute([':id' => $id]);
$existingProduct = $existingStmt->fetch(PDO::FETCH_ASSOC);

if (!$existingProduct) {
    echo json_encode(["success" => false, "message" => "Product not found"]);
    exit;
}

$errors = [];

// --- Collect inputs ---
$name              = trim($_POST['name'] ?? '');
$brandId           = (int)($_POST['brand_id'] ?? 0);
$categoryId        = (int)($_POST['category_id'] ?? 0);
$shortDescription  = trim($_POST['short_description'] ?? '');
$description       = trim($_POST['description'] ?? '');
$calories          = trim($_POST['calories'] ?? '');
$featured          = isset($_POST['featured']) ? 1 : 0;
$status            = isset($_POST['status']) ? (int)$_POST['status'] : 1;
$metaTitle         = trim($_POST['meta_title'] ?? '');
$metaDescription   = trim($_POST['meta_description'] ?? '');
$ingredientIds     = isset($_POST['ingredients']) && is_array($_POST['ingredients']) ? array_map('intval', $_POST['ingredients']) : [];
$pricesInput       = isset($_POST['prices']) && is_array($_POST['prices']) ? $_POST['prices'] : [];
$mainImageId       = isset($_POST['main_image_id']) && $_POST['main_image_id'] !== '' ? (int)$_POST['main_image_id'] : null;

$removedImageIds = [];
if (!empty($_POST['removed_image_ids'])) {
    $decoded = json_decode($_POST['removed_image_ids'], true);
    if (is_array($decoded)) $removedImageIds = array_map('intval', $decoded);
}

// --- Nutrition inputs ---
$nutritionCalories = trim($_POST['nutrition_calories'] ?? '');
$protein = trim($_POST['protein'] ?? '');
$fat     = trim($_POST['fat'] ?? '');
$carbs   = trim($_POST['carbs'] ?? '');
$fiber   = trim($_POST['fiber'] ?? '');
$sugar   = trim($_POST['sugar'] ?? '');
$sodium  = trim($_POST['sodium'] ?? '');

// --- Validation: basic fields ---
if ($name === '') {
    $errors['name'] = 'Product name is required.';
} elseif (mb_strlen($name) > 200) {
    $errors['name'] = 'Product name must be under 200 characters.';
}

if ($brandId <= 0) {
    $errors['brand_id'] = 'Please select a brand.';
} else {
    $chk = $pdo->prepare("SELECT id FROM brands WHERE id = :id LIMIT 1");
    $chk->execute([':id' => $brandId]);
    if (!$chk->fetch()) $errors['brand_id'] = 'Selected brand does not exist.';
}

if ($categoryId <= 0) {
    $errors['category_id'] = 'Please select a category.';
} else {
    $chk = $pdo->prepare("SELECT id FROM categories WHERE id = :id LIMIT 1");
    $chk->execute([':id' => $categoryId]);
    if (!$chk->fetch()) $errors['category_id'] = 'Selected category does not exist.';
}

if ($calories !== '' && (!ctype_digit($calories) || (int)$calories < 0)) {
    $errors['calories'] = 'Calories must be a positive number.';
}
if (mb_strlen($shortDescription) > 500) $errors['short_description'] = 'Short description must be under 500 characters.';
if (mb_strlen($metaTitle) > 255) $errors['meta_title'] = 'Meta title must be under 255 characters.';
if (mb_strlen($metaDescription) > 500) $errors['meta_description'] = 'Meta description must be under 500 characters.';

// --- Validation: nutrition ---
function validateNutritionField($value, $label, $maxDigits = 8) {
    if ($value === '') return null;
    if (!is_numeric($value) || (float)$value < 0) return "$label must be a positive number.";
    $intPart = floor(abs((float)$value));
    if (strlen((string)$intPart) > ($maxDigits - 2)) return "$label is too large.";
    return null;
}

if ($nutritionCalories !== '' && (!ctype_digit($nutritionCalories) || (int)$nutritionCalories < 0)) {
    $errors['nutrition_calories'] = 'Calories must be a positive whole number.';
}
$nutritionFieldChecks = [
    'protein' => validateNutritionField($protein, 'Protein'),
    'fat'     => validateNutritionField($fat, 'Fat'),
    'carbs'   => validateNutritionField($carbs, 'Carbohydrates'),
    'fiber'   => validateNutritionField($fiber, 'Fiber'),
    'sugar'   => validateNutritionField($sugar, 'Sugar'),
    'sodium'  => validateNutritionField($sodium, 'Sodium'),
];
foreach ($nutritionFieldChecks as $field => $msg) {
    if ($msg !== null) $errors[$field] = $msg;
}

// --- Validate prices ---
$validatedPrices = [];
foreach ($pricesInput as $countryId => $priceData) {
    $countryId = (int)$countryId;
    $regular = isset($priceData['regular_price']) && $priceData['regular_price'] !== '' ? (float)$priceData['regular_price'] : null;
    $discount = isset($priceData['discount_price']) && $priceData['discount_price'] !== '' ? (float)$priceData['discount_price'] : null;

    if ($countryId <= 0) continue;

    if ($regular === null) { $errors['prices'] = 'Please enter a regular price for every added country.'; continue; }
    if ($regular < 0 || ($discount !== null && $discount < 0)) { $errors['prices'] = 'Prices cannot be negative.'; continue; }
    if ($discount !== null && $discount >= $regular) { $errors['prices'] = 'Discount price must be less than the regular price.'; continue; }

    $validatedPrices[$countryId] = ['regular' => $regular, 'discount' => $discount];
}

// --- Sanitize rich text ---
function sanitizeRichText($html) {
    if ($html === '') return '';
    $allowedTags = '<p><br><b><strong><i><em><u><s><strike><ol><ul><li><h1><h2><h3><a>';
    $clean = strip_tags($html, $allowedTags);
    $clean = preg_replace('/on\w+\s*=\s*(["\']).*?\1/i', '', $clean);
    $clean = preg_replace('/href\s*=\s*(["\'])\s*javascript:.*?\1/i', 'href="#"', $clean);
    return $clean;
}
$description = sanitizeRichText($description);

// --- Handle new image uploads ---
$newImagePaths = [];
if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 3 * 1024 * 1024;

    for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
            $errors['images'] = 'One or more images failed to upload.';
            break;
        }

        $tmpName = $_FILES['images']['tmp_name'][$i];
        $size = $_FILES['images']['size'][$i];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes, true)) { $errors['images'] = 'Images must be JPG, PNG, or WEBP.'; break; }
        if ($size > $maxSize) { $errors['images'] = 'Each image must be under 3MB.'; break; }

        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mimeType];
        $uploadDir = __DIR__ . '/../../assets/img/products/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '_' . $i . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($tmpName, $destination)) {
            $newImagePaths[] = 'assets/img/products/' . $filename;
        }
    }
}

// --- Stop here if any validation errors ---
if (!empty($errors)) {
    foreach ($newImagePaths as $path) {
        $full = __DIR__ . '/../../' . $path;
        if (file_exists($full)) unlink($full);
    }
    echo json_encode(["success" => false, "message" => "Please fix the errors below.", "errors" => $errors]);
    exit;
}

// --- Everything below in a transaction ---
try {
    $pdo->beginTransaction();

    // --- Remove images marked for deletion (collect paths first, delete files after commit) ---
    $removedImagePaths = [];
    if (!empty($removedImageIds)) {
        $placeholders = implode(',', array_fill(0, count($removedImageIds), '?'));

        $fetchStmt = $pdo->prepare("SELECT id, image FROM product_images WHERE product_id = ? AND id IN ($placeholders)");
        $fetchStmt->execute(array_merge([$id], $removedImageIds));
        $toRemove = $fetchStmt->fetchAll(PDO::FETCH_ASSOC);
        $removedImagePaths = array_column($toRemove, 'image');

        $delStmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ? AND id IN ($placeholders)");
        $delStmt->execute(array_merge([$id], $removedImageIds));
    }

    // --- Insert newly added images ---
    $insertedNewImageIds = [];
    if (!empty($newImagePaths)) {
        $maxOrderStmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), -1) FROM product_images WHERE product_id = :id");
        $maxOrderStmt->execute([':id' => $id]);
        $nextOrder = (int)$maxOrderStmt->fetchColumn() + 1;

        $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image, sort_order, created_at) VALUES (:product_id, :image, :sort_order, NOW())");
        foreach ($newImagePaths as $imgPath) {
            $imgStmt->execute([':product_id' => $id, ':image' => $imgPath, ':sort_order' => $nextOrder]);
            $insertedNewImageIds[] = $pdo->lastInsertId();
            $nextOrder++;
        }
    }

    // --- Determine the final main image (products.image) ---
    // Priority: explicitly chosen existing image > first newly uploaded image > first remaining existing image > null
    $finalMainImagePath = null;

    if ($mainImageId !== null && !in_array($mainImageId, $removedImageIds, true)) {
        $mainStmt = $pdo->prepare("SELECT image FROM product_images WHERE id = :id AND product_id = :pid LIMIT 1");
        $mainStmt->execute([':id' => $mainImageId, ':pid' => $id]);
        $mainRow = $mainStmt->fetch(PDO::FETCH_ASSOC);
        if ($mainRow) $finalMainImagePath = $mainRow['image'];
    }

    if ($finalMainImagePath === null && !empty($newImagePaths)) {
        $finalMainImagePath = $newImagePaths[0];
    }

    if ($finalMainImagePath === null) {
        // Fall back to whatever remains in product_images after removals
        $fallbackStmt = $pdo->prepare("SELECT image FROM product_images WHERE product_id = :id ORDER BY sort_order ASC, id ASC LIMIT 1");
        $fallbackStmt->execute([':id' => $id]);
        $fallbackRow = $fallbackStmt->fetch(PDO::FETCH_ASSOC);
        if ($fallbackRow) $finalMainImagePath = $fallbackRow['image'];
    }

    // --- Update product ---
    $stmt = $pdo->prepare("
        UPDATE products SET
            brand_id = :brand_id, category_id = :category_id, name = :name, image = :image,
            short_description = :short_description, description = :description, calories = :calories,
            featured = :featured, status = :status, meta_title = :meta_title, meta_description = :meta_description,
            updated_at = NOW()
        WHERE id = :id
    ");
    $stmt->execute([
        ':brand_id'          => $brandId,
        ':category_id'       => $categoryId,
        ':name'              => $name,
        ':image'             => $finalMainImagePath,
        ':short_description' => $shortDescription !== '' ? $shortDescription : null,
        ':description'       => $description !== '' ? $description : null,
        ':calories'          => $calories !== '' ? (int)$calories : 0,
        ':featured'          => $featured,
        ':status'            => $status,
        ':meta_title'        => $metaTitle !== '' ? $metaTitle : null,
        ':meta_description'  => $metaDescription !== '' ? $metaDescription : null,
        ':id'                => $id
    ]);

    // --- Sync ingredients: delete-all-then-reinsert ---
    $pdo->prepare("DELETE FROM product_ingredients WHERE product_id = :id")->execute([':id' => $id]);
    if (!empty($ingredientIds)) {
        $ingStmt = $pdo->prepare("INSERT INTO product_ingredients (product_id, ingredient_id, created_at) VALUES (:product_id, :ingredient_id, NOW())");
        foreach (array_unique($ingredientIds) as $ingId) {
            if ($ingId > 0) $ingStmt->execute([':product_id' => $id, ':ingredient_id' => $ingId]);
        }
    }

    // --- Sync prices: delete-all-then-reinsert ---
    $pdo->prepare("DELETE FROM product_prices WHERE product_id = :id")->execute([':id' => $id]);
    if (!empty($validatedPrices)) {
        $countryStmt = $pdo->prepare("SELECT currency FROM countries WHERE id = :id LIMIT 1");
        $priceStmt = $pdo->prepare("
            INSERT INTO product_prices (product_id, country_id, regular_price, discount_price, currency, status, updated_on)
            VALUES (:product_id, :country_id, :regular_price, :discount_price, :currency, 1, NOW())
        ");
        foreach ($validatedPrices as $countryId => $p) {
            $countryStmt->execute([':id' => $countryId]);
            $countryRow = $countryStmt->fetch(PDO::FETCH_ASSOC);
            $currency = $countryRow ? $countryRow['currency'] : null;

            $priceStmt->execute([
                ':product_id'     => $id,
                ':country_id'     => $countryId,
                ':regular_price'  => $p['regular'],
                ':discount_price' => $p['discount'],
                ':currency'       => $currency
            ]);
        }
    }

    // --- Nutrition: update if exists, insert if not, delete if all fields now empty ---
    $hasNutritionData = ($nutritionCalories !== '' || $protein !== '' || $fat !== '' ||
                          $carbs !== '' || $fiber !== '' || $sugar !== '' || $sodium !== '');

    $nutritionExistsStmt = $pdo->prepare("SELECT id FROM product_nutrition WHERE product_id = :id LIMIT 1");
    $nutritionExistsStmt->execute([':id' => $id]);
    $nutritionExists = $nutritionExistsStmt->fetch();

    if ($hasNutritionData) {
        if ($nutritionExists) {
            $updNutrition = $pdo->prepare("
                UPDATE product_nutrition
                SET fat = :fat, carbs = :carbs, protein = :protein, fiber = :fiber,
                    sugar = :sugar, sodium = :sodium, calories = :calories, updated_at = NOW()
                WHERE product_id = :product_id
            ");
            $updNutrition->execute([
                ':fat' => $fat !== '' ? (float)$fat : null,
                ':carbs' => $carbs !== '' ? (float)$carbs : null,
                ':protein' => $protein !== '' ? (float)$protein : null,
                ':fiber' => $fiber !== '' ? (float)$fiber : null,
                ':sugar' => $sugar !== '' ? (float)$sugar : null,
                ':sodium' => $sodium !== '' ? (float)$sodium : null,
                ':calories' => $nutritionCalories !== '' ? (int)$nutritionCalories : null,
                ':product_id' => $id
            ]);
        } else {
            $insNutrition = $pdo->prepare("
                INSERT INTO product_nutrition (product_id, fat, carbs, protein, fiber, sugar, sodium, calories, created_at, updated_at)
                VALUES (:product_id, :fat, :carbs, :protein, :fiber, :sugar, :sodium, :calories, NOW(), NOW())
            ");
            $insNutrition->execute([
                ':product_id' => $id,
                ':fat' => $fat !== '' ? (float)$fat : null,
                ':carbs' => $carbs !== '' ? (float)$carbs : null,
                ':protein' => $protein !== '' ? (float)$protein : null,
                ':fiber' => $fiber !== '' ? (float)$fiber : null,
                ':sugar' => $sugar !== '' ? (float)$sugar : null,
                ':sodium' => $sodium !== '' ? (float)$sodium : null,
                ':calories' => $nutritionCalories !== '' ? (int)$nutritionCalories : null,
            ]);
        }
    } elseif ($nutritionExists) {
        // All fields cleared out — remove the now-empty nutrition row
        $pdo->prepare("DELETE FROM product_nutrition WHERE product_id = :id")->execute([':id' => $id]);
    }

    $pdo->commit();

    // --- Only after successful commit: delete removed image files from disk ---
    foreach ($removedImagePaths as $path) {
        $full = __DIR__ . '/../../' . $path;
        if (file_exists($full)) unlink($full);
    }

    echo json_encode(["success" => true, "message" => "Product updated successfully."]);

} catch (PDOException $e) {
    $pdo->rollBack();

    // Roll back new file uploads since the transaction failed
    foreach ($newImagePaths as $path) {
        $full = __DIR__ . '/../../' . $path;
        if (file_exists($full)) unlink($full);
    }

    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}