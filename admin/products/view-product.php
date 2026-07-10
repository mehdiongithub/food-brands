<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin(); // both admin and employee can view

$token = $_GET['token'] ?? '';
$id = decryptId($token);

if ($id === false || $id <= 0) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

$checkStmt = $pdo->prepare("SELECT id FROM products WHERE id = :id LIMIT 1");
$checkStmt->execute([':id' => $id]);
if (!$checkStmt->fetch()) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodScope — View Product</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin/style.css">
    <style>
        .form-section-title {
            font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
            color: var(--muted); margin: 26px 0 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border-l);
        }
        .form-section-title:first-child { margin-top: 0; }
        .desc-view {
            background: var(--bg, #f8f8f8); border: 1px solid var(--border, #e5e5e5); border-radius: var(--r-md, 8px);
            padding: 14px 16px; font-size: .88rem; line-height: 1.6; color: var(--text2, #444);
        }
        .desc-view p { margin-bottom: .6em; }
        .desc-view ul, .desc-view ol { margin-left: 1.2em; margin-bottom: .6em; }
        .desc-view:empty::before { content: '—'; color: var(--muted); }
        .badge-pill {
            display: inline-block; margin: 3px; padding: 4px 12px; background: var(--bg);
            border: 1px solid var(--border); border-radius: 99px; font-size: .78rem; font-weight: 500;
        }
        .gallery-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px;
        }
        .gallery-grid .img-wrap { position: relative; }
        .gallery-grid img {
            width: 100%; height: 100px; object-fit: cover; border-radius: 8px;
            border: 1px solid var(--border); cursor: pointer; transition: transform .2s ease;
        }
        .gallery-grid img:hover { transform: scale(1.03); }
        .gallery-grid .main-tag {
            position: absolute; top: 4px; left: 4px; background: var(--accent,#E85D04); color: #fff;
            font-size: .6rem; font-weight: 700; padding: 2px 6px; border-radius: 4px;
        }

        .price-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 10px;
            padding: 12px 14px;
        }
        .price-card .country-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
        .price-card .country-name { display: flex; align-items: center; gap: 6px; font-weight: 600; font-size: .85rem; }

        .nutrition-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px;
        }
        .nutrition-item {
            background: var(--surface); border: 1px solid var(--border); border-radius: 10px;
            padding: 12px; text-align: center;
        }
        .nutrition-item .val { font-size: 1.2rem; font-weight: 800; color: var(--accent,#E85D04); }
        .nutrition-item .lbl { font-size: .72rem; color: var(--muted); text-transform: uppercase; letter-spacing: .03em; margin-top: 2px; }
    </style>
</head>

<body>

    <div class="sb-bd" id="sbBd" onclick="closeMS()"></div>

    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/header.php'; ?>

    <div id="main">
        <div class="pg-content" id="pgC">

            <div class="pg-head" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
                <div>
                    <h1 class="pg-title">View Product</h1>
                    <p class="pg-desc">Product details</p>
                </div>
                <div style="display:flex;gap:10px;">
                    <?php if (currentUserRole() === 'admin'): ?>
                    <a href="edit-product.php?token=<?= urlencode(encryptId($id)) ?>" class="ba text-decoration-none">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <?php endif; ?>
                    <a href="index.php" class="bo text-decoration-none">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="viewContainer">
                    <div style="text-align:center;padding:40px;color:var(--muted);">
                        <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                        <p style="margin-top:10px;">Loading product details...</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="tw2" id="tw2"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/admin/sidebar.js"></script>
    <script>
    var productId = <?= json_encode($id) ?>;
    var BASE_URL_JS = <?= json_encode(BASE_URL) ?>;

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return $('<div>').text(str).html();
    }

    function getInitialsJs(name) {
        if (!name) return '?';
        var parts = name.trim().split(/\s+/);
        if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }

    $(function () {
        $.ajax({
            url: '../../api/products/get-product.php',
            type: 'GET',
            data: { id: productId },
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                renderProduct(res.data);
            } else {
                toast(res.message || 'Product not found', 'err');
                $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                    '<i class="fas fa-triangle-exclamation" style="font-size:1.4rem;"></i>' +
                    '<p style="margin-top:10px;">' + escapeHtml(res.message || 'Product not found') + '</p></div>');
            }
        })
        .fail(function () {
            toast('Failed to load product details', 'err');
            $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                '<p>Something went wrong while loading this product.</p></div>');
        });
    });

    function renderProduct(p) {
        var mainImageHtml = p.image
            ? "<img src='" + BASE_URL_JS + "/" + p.image + "' alt='" + escapeHtml(p.name) + "' style='width:100px;height:100px;object-fit:cover;border-radius:10px;border:1px solid var(--border);'>"
            : "<div style='width:100px;height:100px;border-radius:10px;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;'><i class='fas fa-image' style='color:var(--muted);font-size:1.4rem;'></i></div>";

        var statusBadge = (p.status == 1)
            ? '<span class="sb-badge2 active">Active</span>'
            : '<span class="sb-badge2 draft">Inactive</span>';

        var featuredBadge = (p.featured == 1)
            ? '<span class="sb-badge2 active" style="background:rgba(217,119,6,.1);color:#D97706;margin-left:6px;"><i class="fas fa-star" style="font-size:.65rem;"></i> Featured</span>'
            : '';

        var brandHtml = p.brand_name
            ? (p.brand_logo ? "<img src='" + BASE_URL_JS + "/" + p.brand_logo + "' style='width:20px;height:20px;object-fit:cover;border-radius:4px;margin-right:6px;vertical-align:middle;'>" : '') + escapeHtml(p.brand_name)
            : '<span style="color:var(--muted);">No brand assigned</span>';

        var categoryHtml = p.category_name ? escapeHtml(p.category_name) : '<span style="color:var(--muted);">No category assigned</span>';

        var shortDescHtml = p.short_description ? escapeHtml(p.short_description) : '—';
        var descriptionHtml = p.description || "<span style='color:var(--muted);'>No description provided</span>";

        // --- Ingredients ---
        var ingredientsHtml = (p.ingredients && p.ingredients.length)
            ? p.ingredients.map(function (i) { return "<span class='badge-pill'>" + escapeHtml(i.name) + "</span>"; }).join('')
            : "<span style='color:var(--muted);font-size:.82rem;'>No ingredients listed</span>";

        // --- Images gallery ---
        var galleryHtml = (p.images && p.images.length)
            ? "<div class='gallery-grid'>" + p.images.map(function (img, idx) {
                var url = BASE_URL_JS + '/' + img.image;
                var mainTag = idx === 0 ? "<span class='main-tag'>MAIN</span>" : '';
                return "<div class='img-wrap'>" + mainTag + "<img src='" + url + "' alt='Product image' onclick='window.open(\"" + url + "\", \"_blank\")'></div>";
            }).join('') + "</div>"
            : "<span style='color:var(--muted);font-size:.82rem;'>No images uploaded</span>";

        // --- Pricing by country ---
        var pricesHtml = '';
        if (p.prices && p.prices.length) {
            pricesHtml = "<div style='display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px;'>";
            p.prices.forEach(function (pr) {
                var symbol = pr.currency_symbol || pr.currency || '';
                var regular = pr.regular_price !== null ? parseFloat(pr.regular_price).toFixed(2) : null;
                var discount = pr.discount_price !== null ? parseFloat(pr.discount_price).toFixed(2) : null;
                var priceStatusBadge = (pr.status == 1)
                    ? "<span class='sb-badge2 active' style='font-size:.65rem;padding:2px 8px;'>Active</span>"
                    : "<span class='sb-badge2 draft' style='font-size:.65rem;padding:2px 8px;'>Inactive</span>";

                var priceDisplay = discount
                    ? "<span style='text-decoration:line-through;color:var(--muted);font-size:.8rem;'>" + symbol + regular + "</span> <strong style='color:#DC2626;font-size:1rem;'>" + symbol + discount + "</strong>"
                    : "<strong style='font-size:1rem;'>" + symbol + (regular || '—') + "</strong>";

                pricesHtml += "" +
                    "<div class='price-card'>" +
                        "<div class='country-row'>" +
                            "<div class='country-name'>" + pr.flag_html + " " + escapeHtml(pr.country_name) + "</div>" +
                            priceStatusBadge +
                        "</div>" +
                        "<div>" + priceDisplay + "</div>" +
                    "</div>";
            });
            pricesHtml += "</div>";
        } else {
            pricesHtml = "<span style='color:var(--muted);font-size:.82rem;'>No pricing set for any country</span>";
        }

        // --- Nutrition ---
        var nutritionHtml = '';
        if (p.nutrition) {
            var n = p.nutrition;
            nutritionHtml = "<div class='nutrition-grid'>" +
                nutritionItem(n.calories, 'Calories (kcal)') +
                nutritionItem(n.protein, 'Protein (g)') +
                nutritionItem(n.fat, 'Fat (g)') +
                nutritionItem(n.carbs, 'Carbs (g)') +
                nutritionItem(n.fiber, 'Fiber (g)') +
                nutritionItem(n.sugar, 'Sugar (g)') +
                nutritionItem(n.sodium, 'Sodium (mg)') +
            "</div>";
        } else {
            nutritionHtml = "<span style='color:var(--muted);font-size:.82rem;'>No nutrition information provided</span>";
        }

        var html = '' +
            // --- Header ---
            '<div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">' +
                mainImageHtml +
                '<div>' +
                    '<div style="font-size:1.2rem;font-weight:800;">' + escapeHtml(p.name) + featuredBadge + '</div>' +
                    '<div style="color:var(--muted);font-size:.85rem;margin-top:2px;">' + escapeHtml(p.slug || '—') + '</div>' +
                    '<div style="margin-top:6px;">' + statusBadge + '</div>' +
                '</div>' +
            '</div>' +

            // --- Basic Info ---
            '<div class="form-section-title"><i class="fas fa-info-circle"></i> Basic Information</div>' +
            '<div class="row g-3">' +
                fieldHtml('Brand', brandHtml) +
                fieldHtml('Category', categoryHtml) +
                field('Calories (listing)', (p.calories !== null ? p.calories + ' kcal' : '—')) +
                fieldHtml('Created By', escapeHtml(p.created_by_name || 'Unknown')) +
                field('Created At', p.created_at || '—') +
                field('Last Updated', p.updated_at || '—') +
            '</div>' +

            '<div class="form-section-title"><i class="fas fa-align-left"></i> Short Description</div>' +
            '<div class="desc-view">' + shortDescHtml + '</div>' +

            '<div class="form-section-title"><i class="fas fa-file-lines"></i> Full Description</div>' +
            '<div class="desc-view">' + descriptionHtml + '</div>' +

            '<div class="form-section-title"><i class="fas fa-carrot"></i> Ingredients</div>' +
            '<div style="margin-bottom:10px;">' + ingredientsHtml + '</div>' +

            '<div class="form-section-title"><i class="fas fa-images"></i> Product Images</div>' +
            galleryHtml +

            '<div class="form-section-title"><i class="fas fa-money-bill-wave"></i> Pricing by Country</div>' +
            pricesHtml +

            '<div class="form-section-title"><i class="fas fa-heartbeat"></i> Nutrition Information</div>' +
            nutritionHtml +

            '<div class="form-section-title"><i class="fas fa-magnifying-glass-chart"></i> SEO Settings</div>' +
            '<div class="row g-3">' +
                field('Meta Title', p.meta_title || '—') +
            '</div>' +
            '<div style="margin-top:12px;">' +
                '<label class="fl">Meta Description</label>' +
                '<div class="desc-view">' + (p.meta_description ? escapeHtml(p.meta_description) : '') + '</div>' +
            '</div>';

        $('#viewContainer').html(html);
    }

    function nutritionItem(value, label) {
        var display = (value !== null && value !== undefined) ? value : '—';
        return "<div class='nutrition-item'><div class='val'>" + display + "</div><div class='lbl'>" + label + "</div></div>";
    }

    function field(label, value) {
        return '<div class="col-md-6">' +
            '<label class="fl">' + label + '</label>' +
            '<input type="text" class="fi" value="' + escapeHtml(value) + '" disabled readonly>' +
            '</div>';
    }

    function fieldHtml(label, htmlValue) {
        return '<div class="col-md-6">' +
            '<label class="fl">' + label + '</label>' +
            '<div style="padding:10px 0;">' + htmlValue + '</div>' +
            '</div>';
    }
    </script>
</body>

</html>