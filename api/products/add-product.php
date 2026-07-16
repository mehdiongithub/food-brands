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

if (mb_strlen($shortDescription) > 500) {
    $errors['short_description'] = 'Short description must be under 500 characters.';
}
if (mb_strlen($metaTitle) > 255) {
    $errors['meta_title'] = 'Meta title must be under 255 characters.';
}
if (mb_strlen($metaDescription) > 500) {
    $errors['meta_description'] = 'Meta description must be under 500 characters.';
}

// --- Validation: nutrition fields (all optional, but must be valid if provided) ---
function validateNutritionField($value, $label, $maxDigits = 8) {
    if ($value === '') return null; // empty is fine — column is nullable
    if (!is_numeric($value) || (float)$value < 0) {
        return "$label must be a positive number.";
    }
    // decimal(8,2) allows up to 6 digits before the decimal point (8 total - 2 decimal)
    $intPart = floor(abs((float)$value));
    if (strlen((string)$intPart) > ($maxDigits - 2)) {
        return "$label is too large.";
    }
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

// --- Countries this brand is actually linked to (for the price check below) ---
$validCountryIds = [];
if ($brandId > 0 && !isset($errors['brand_id'])) {
    $bcStmt = $pdo->prepare("SELECT country_id FROM brand_country WHERE brand_id = :brand_id");
    $bcStmt->execute([':brand_id' => $brandId]);
    $validCountryIds = array_map('intval', $bcStmt->fetchAll(PDO::FETCH_COLUMN));
}

// --- Validate prices ---
$validatedPrices = [];
foreach ($pricesInput as $countryId => $priceData) {
    $countryId = (int)$countryId;
    $regular = isset($priceData['regular_price']) && $priceData['regular_price'] !== '' ? (float)$priceData['regular_price'] : null;
    $discount = isset($priceData['discount_price']) && $priceData['discount_price'] !== '' ? (float)$priceData['discount_price'] : null;

    if ($countryId <= 0) continue;

    if (!in_array($countryId, $validCountryIds, true)) {
        $errors['prices'] = 'One or more selected countries are not linked to this brand. Remove them or link the country to the brand first (Brands → Edit → Countries).';
        continue;
    }
    if ($regular === null) {
        $errors['prices'] = 'Please enter a regular price for every added country.';
        continue;
    }
    if ($regular < 0 || ($discount !== null && $discount < 0)) {
        $errors['prices'] = 'Prices cannot be negative.';
        continue;
    }
    if ($discount !== null && $discount >= $regular) {
        $errors['prices'] = 'Discount price must be less than the regular price.';
        continue;
    }

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

// --- Slug generator ---
function generateProductSlug($pdo, $name) {
    $baseSlug = strtolower(trim($name));
    $baseSlug = preg_replace('/[^a-z0-9]+/', '-', $baseSlug);
    $baseSlug = trim($baseSlug, '-');
    $slug = $baseSlug;
    $counter = 1;
    while (true) {
        $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        if (!$stmt->fetch()) break;
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
    return $slug;
}

// --- Handle multi-image upload ---
$imagePaths = [];
if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
    $count = count($_FILES['images']['name']);
    if ($count > 10) {
        $errors['images'] = 'Maximum 10 images allowed.';
    } else {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 3 * 1024 * 1024;

        for ($i = 0; $i < $count; $i++) {
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

            if (!in_array($mimeType, $allowedTypes, true)) {
                $errors['images'] = 'Images must be JPG, PNG, or WEBP.';
                break;
            }
            if ($size > $maxSize) {
                $errors['images'] = 'Each image must be under 3MB.';
                break;
            }

            $uploadDir = __DIR__ . '/../../assets/img/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $filename = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '_' . $i . '.webp';
            $destination = $uploadDir . $filename;

            if (convertToWebp($tmpName, $destination)) {
                $imagePaths[] = 'assets/img/products/' . $filename;
            }
        }
    }
}

// --- Stop here if any validation/upload errors ---
if (!empty($errors)) {
    foreach ($imagePaths as $path) {
        $full = __DIR__ . '/../../' . $path;
        if (file_exists($full)) unlink($full);
    }
    echo json_encode(["success" => false, "message" => "Please fix the errors below.", "errors" => $errors]);
    exit;
}

// --- Insert everything in a transaction ---
try {
    $pdo->beginTransaction();

    $slug = generateProductSlug($pdo, $name);
    $createdBy = currentUserId();
    $mainImage = !empty($imagePaths) ? $imagePaths[0] : null; // first uploaded image = products.image (thumbnail)

    $stmt = $pdo->prepare("
        INSERT INTO products (
            brand_id, category_id, name, slug, image, short_description, description,
            calories, featured, status, meta_title, meta_description, created_by, created_at, updated_at
        ) VALUES (
            :brand_id, :category_id, :name, :slug, :image, :short_description, :description,
            :calories, :featured, :status, :meta_title, :meta_description, :created_by, NOW(), NOW()
        )
    ");
    $stmt->execute([
        ':brand_id'          => $brandId,
        ':category_id'       => $categoryId,
        ':name'              => $name,
        ':slug'              => $slug,
        ':image'             => $mainImage,
        ':short_description' => $shortDescription !== '' ? $shortDescription : null,
        ':description'       => $description !== '' ? $description : null,
        ':calories'          => $calories !== '' ? (int)$calories : 0,
        ':featured'          => $featured,
        ':status'            => $status,
        ':meta_title'        => $metaTitle !== '' ? $metaTitle : null,
        ':meta_description'  => $metaDescription !== '' ? $metaDescription : null,
        ':created_by'        => $createdBy
    ]);

    $productId = $pdo->lastInsertId();

    // --- Product images (all of them, including the first, for the gallery table) ---
    if (!empty($imagePaths)) {
        $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image, sort_order, created_at) VALUES (:product_id, :image, :sort_order, NOW())");
        foreach ($imagePaths as $index => $imgPath) {
            $imgStmt->execute([':product_id' => $productId, ':image' => $imgPath, ':sort_order' => $index]);
        }
    }

    // --- Ingredients ---
    if (!empty($ingredientIds)) {
        $ingStmt = $pdo->prepare("INSERT INTO product_ingredients (product_id, ingredient_id, created_at) VALUES (:product_id, :ingredient_id, NOW())");
        foreach (array_unique($ingredientIds) as $ingId) {
            if ($ingId > 0) $ingStmt->execute([':product_id' => $productId, ':ingredient_id' => $ingId]);
        }
    }

    // --- Prices per country ---
    if (!empty($validatedPrices)) {
        $countryStmt = $pdo->prepare("SELECT id, currency FROM countries WHERE id = :id LIMIT 1");
        $priceStmt = $pdo->prepare("
            INSERT INTO product_prices (product_id, country_id, regular_price, discount_price, currency, status, updated_on)
            VALUES (:product_id, :country_id, :regular_price, :discount_price, :currency, 1, NOW())
        ");
        foreach ($validatedPrices as $countryId => $p) {
            $countryStmt->execute([':id' => $countryId]);
            $countryRow = $countryStmt->fetch(PDO::FETCH_ASSOC);
            $currency = $countryRow ? $countryRow['currency'] : null;

            $priceStmt->execute([
                ':product_id'     => $productId,
                ':country_id'     => $countryId,
                ':regular_price'  => $p['regular'],
                ':discount_price' => $p['discount'],
                ':currency'       => $currency
            ]);
        }
    }

    // --- Nutrition information (only insert if at least one value was provided) ---
    $hasNutritionData = ($nutritionCalories !== '' || $protein !== '' || $fat !== '' ||
                          $carbs !== '' || $fiber !== '' || $sugar !== '' || $sodium !== '');

    if ($hasNutritionData) {
        $nutritionStmt = $pdo->prepare("
            INSERT INTO product_nutrition (
                product_id, fat, carbs, protein, fiber, sugar, sodium, calories, created_at, updated_at
            ) VALUES (
                :product_id, :fat, :carbs, :protein, :fiber, :sugar, :sodium, :calories, NOW(), NOW()
            )
        ");
        $nutritionStmt->execute([
            ':product_id' => $productId,
            ':fat'        => $fat !== '' ? (float)$fat : null,
            ':carbs'      => $carbs !== '' ? (float)$carbs : null,
            ':protein'    => $protein !== '' ? (float)$protein : null,
            ':fiber'      => $fiber !== '' ? (float)$fiber : null,
            ':sugar'      => $sugar !== '' ? (float)$sugar : null,
            ':sodium'     => $sodium !== '' ? (float)$sodium : null,
            ':calories'   => $nutritionCalories !== '' ? (int)$nutritionCalories : null,
        ]);
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Product created successfully.",
        "id" => $productId
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();

    foreach ($imagePaths as $path) {
        $full = __DIR__ . '/../../' . $path;
        if (file_exists($full)) unlink($full);
    }

    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}