<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();

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
    <title>FoodScope — Edit Product</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/style.css">
    <style>
        #descriptionEditor { background: var(--surface, #fff); min-height: 180px; border-radius: 0 0 var(--r-md, 8px) var(--r-md, 8px); }
        .ql-toolbar.ql-snow { border-radius: var(--r-md, 8px) var(--r-md, 8px) 0 0; background: var(--bg, #f8f8f8); }

        .select2-container .select2-selection--single { height: 42px !important; border: 1.5px solid var(--border) !important; border-radius: var(--r-md,8px) !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 42px !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px !important; }

        .select2-container .select2-selection--multiple {
            min-height: 42px; border: 1.5px solid var(--border) !important; border-radius: var(--r-md,8px) !important; padding: 4px 6px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: var(--accent,#E85D04); border: none; color: #fff; border-radius: 6px; padding: 3px 10px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove { color:#fff; margin-right:6px; }

        .form-section-title {
            font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
            color: var(--muted); margin: 26px 0 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border-l);
        }
        .form-section-title:first-child { margin-top: 0; }
        .form-section-sub { color: var(--muted); font-size: .78rem; margin-top: -10px; margin-bottom: 14px; }

        .price-row {
            display: grid; grid-template-columns: 200px 1fr 1fr 90px auto;
            gap: 10px; align-items: center; padding: 10px; background: var(--bg);
            border-radius: 8px; margin-bottom: 8px;
        }
        .price-row .country-label { display: flex; align-items: center; gap: 6px; font-size: .82rem; font-weight: 600; }
        .price-row .remove-price-row { color: #DC2626; cursor: pointer; width: 30px; height: 30px; display:flex; align-items:center; justify-content:center; }

        .gallery-preview-item { position: relative; display: inline-block; margin: 6px; }
        .gallery-preview-item img { width: 90px; height: 90px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); }
        .gallery-preview-item.existing-marked-remove img { opacity: .3; }
        .gallery-preview-item .main-tag {
            position: absolute; top: -6px; left: -6px; background: var(--accent,#E85D04); color: #fff;
            font-size: .58rem; font-weight: 700; padding: 2px 6px; border-radius: 4px;
        }
        .gallery-preview-item .remove-gallery-item {
            position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; border-radius: 50%;
            background: #DC2626; color: #fff; border: 2px solid var(--surface);
            display: flex; align-items: center; justify-content: center; font-size: .65rem; cursor: pointer;
        }
        .gallery-preview-item .undo-remove-item {
            position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; border-radius: 50%;
            background: #059669; color: #fff; border: 2px solid var(--surface);
            display: none; align-items: center; justify-content: center; font-size: .65rem; cursor: pointer;
        }
        .gallery-preview-item.existing-marked-remove .undo-remove-item { display: flex; }
        .gallery-preview-item.existing-marked-remove .remove-gallery-item { display: none; }
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
                    <h1 class="pg-title">Edit Product</h1>
                    <p class="pg-desc">Update product details</p>
                </div>
                <a href="index.php" class="bo text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="formWrapper" style="display:none;">

                    <form id="editProductForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="productId" value="<?= $id ?>">
                        <input type="hidden" name="removed_image_ids" id="removedImageIds" value="">
                        <input type="hidden" name="main_image_id" id="mainImageId" value="">

                        <!-- ===== BASIC INFO ===== -->
                        <div class="form-section-title"><i class="fas fa-info-circle"></i> Basic Information</div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="fl">Product Name <span style="color:red">*</span></label>
                                <input type="text" class="fi" name="name" id="name" required maxlength="200">
                                <div class="invalid-feedback" id="err_name"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Brand <span style="color:red">*</span></label>
                                <select class="fss" name="brand_id" id="brand_id" style="width:100%;" required>
                                    <option value="">Loading brands...</option>
                                </select>
                                <div class="invalid-feedback" id="err_brand_id"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Category <span style="color:red">*</span></label>
                                <select class="fss" name="category_id" id="category_id" style="width:100%;" required>
                                    <option value="">Loading categories...</option>
                                </select>
                                <div class="invalid-feedback" id="err_category_id"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Calories (shown in listings)</label>
                                <input type="number" class="fi" name="calories" id="calories" min="0">
                                <div class="invalid-feedback" id="err_calories"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Status</label>
                                <select class="fss" name="status" id="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="fl fsw" style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                                    <input type="checkbox" name="featured" id="featured" value="1" style="width:16px;height:16px;">
                                    Mark as Featured Product
                                </label>
                            </div>

                            <div class="col-md-12">
                                <label class="fl">Short Description</label>
                                <textarea class="fi" name="short_description" id="short_description" rows="2" maxlength="500"></textarea>
                                <div class="invalid-feedback" id="err_short_description"></div>
                            </div>
                        </div>

                        <!-- ===== FULL DESCRIPTION ===== -->
                        <div class="form-section-title"><i class="fas fa-align-left"></i> Full Description</div>
                        <div id="descriptionEditor"></div>
                        <textarea name="description" id="description" style="display:none;"></textarea>

                        <!-- ===== INGREDIENTS ===== -->
                        <div class="form-section-title"><i class="fas fa-carrot"></i> Ingredients</div>
                        <select class="fss" name="ingredients[]" id="ingredients" multiple style="width:100%;">
                            <option value="">Loading ingredients...</option>
                        </select>

                        <!-- ===== IMAGES ===== -->
                        <div class="form-section-title"><i class="fas fa-images"></i> Product Images</div>
                        <label class="fl">Add New Images</label>
                        <input type="file" class="fi" name="images[]" id="images" accept="image/png, image/jpeg, image/jpg, image/webp" multiple>
                        <small style="color:var(--muted);font-size:.72rem;">Existing images shown below — click × to remove, click a thumbnail to set it as the main image. Max 10 total, 3MB each.</small>
                        <div class="invalid-feedback" id="err_images"></div>
                        <div id="galleryPreviewContainer" style="margin-top:12px;"></div>

                        <!-- ===== PRICING BY COUNTRY ===== -->
                        <div class="form-section-title"><i class="fas fa-money-bill-wave"></i> Pricing by Country</div>

                        <div style="margin-bottom:10px;">
                            <select class="fss" id="countryPicker" style="width:280px;display:inline-block;">
                                <option value="">Select a country to add pricing...</option>
                            </select>
                            <button type="button" class="bo" id="addCountryPriceBtn" style="margin-left:8px;">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>

                        <div id="priceRowsContainer">
                            <div style="color:var(--muted);font-size:.82rem;padding:10px 0;" id="noPricesMsg">
                                No countries added yet.
                            </div>
                        </div>
                        <div class="invalid-feedback" id="err_prices"></div>

                        <!-- ===== NUTRITION ===== -->
                        <div class="form-section-title"><i class="fas fa-heartbeat"></i> Nutrition Information</div>
                        <p class="form-section-sub">Optional, but recommended for SEO and nutrition-panel display.</p>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fl">Calories (kcal)</label>
                                <input type="number" class="fi" name="nutrition_calories" id="nutrition_calories" min="0" step="1">
                                <div class="invalid-feedback" id="err_nutrition_calories"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="fl">Protein (g)</label>
                                <input type="number" class="fi" name="protein" id="protein" min="0" step="0.01">
                                <div class="invalid-feedback" id="err_protein"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="fl">Fat (g)</label>
                                <input type="number" class="fi" name="fat" id="fat" min="0" step="0.01">
                                <div class="invalid-feedback" id="err_fat"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="fl">Carbohydrates (g)</label>
                                <input type="number" class="fi" name="carbs" id="carbs" min="0" step="0.01">
                                <div class="invalid-feedback" id="err_carbs"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="fl">Fiber (g)</label>
                                <input type="number" class="fi" name="fiber" id="fiber" min="0" step="0.01">
                                <div class="invalid-feedback" id="err_fiber"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="fl">Sugar (g)</label>
                                <input type="number" class="fi" name="sugar" id="sugar" min="0" step="0.01">
                                <div class="invalid-feedback" id="err_sugar"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="fl">Sodium (mg)</label>
                                <input type="number" class="fi" name="sodium" id="sodium" min="0" step="0.01">
                                <div class="invalid-feedback" id="err_sodium"></div>
                            </div>
                        </div>

                        <!-- ===== SEO ===== -->
                        <div class="form-section-title"><i class="fas fa-magnifying-glass-chart"></i> SEO Settings</div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="fl">Meta Title</label>
                                <input type="text" class="fi" name="meta_title" id="meta_title" maxlength="255">
                                <small style="color:var(--muted);font-size:.72rem;"><span id="metaTitleCount">0</span>/255</small>
                                <div class="invalid-feedback" id="err_meta_title"></div>
                            </div>
                            <div class="col-md-12">
                                <label class="fl">Meta Description</label>
                                <textarea class="fi" name="meta_description" id="meta_description" rows="3" maxlength="500"></textarea>
                                <small style="color:var(--muted);font-size:.72rem;"><span id="metaDescCount">0</span>/500</small>
                                <div class="invalid-feedback" id="err_meta_description"></div>
                            </div>
                        </div>

                        <div class="mo-f" style="padding:0;margin-top:26px;border:none;display:flex;gap:10px;">
                            <button type="button" class="bo" onclick="window.location.href='index.php'">Cancel</button>
                            <button type="submit" class="ba" id="submitBtn">
                                <i class="fas fa-save"></i> Update Product
                            </button>
                        </div>

                    </form>

                </div>

                <div id="loadingBox" style="text-align:center;padding:40px;color:var(--muted);">
                    <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                    <p style="margin-top:10px;">Loading product details...</p>
                </div>

            </div>
        </div>
    </div>

    <div class="tw2" id="tw2"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/admin/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>

          function toast(m, t) {
            t = t || 'suc';
            var ic = { suc: 'fa-check-circle', err: 'fa-times-circle', wrn: 'fa-exclamation-triangle', inf: 'fa-info-circle' };
            var $t = $('<div class="ti2 ' + t + '"><i class="fas ' + ic[t] + '"></i><span>' + m + '</span></div>');
            $('#tw2').append($t);
            setTimeout(function() {
                $t.fadeOut(250, function() { $(this).remove(); });
            }, 2800);
        }
        
    var productId = $('#productId').val();
    var BASE_URL_JS = <?= json_encode(BASE_URL) ?>;
    var countriesData = {};
    var selectedCountryIds = [];

    var existingImages = [];   // [{id, image, sort_order}]
    var removedImageIds = [];
    var newImageFiles = [];
    var currentMainImageId = null; // id of an EXISTING image chosen as main (null if a new upload will be main)

    var quill = new Quill('#descriptionEditor', {
        theme: 'snow',
        placeholder: 'Write the full product description...',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });
    quill.on('text-change', function () { $('#description').val(quill.root.innerHTML); });

    $('#meta_title').on('input', function () { $('#metaTitleCount').text(this.value.length); });
    $('#meta_description').on('input', function () { $('#metaDescCount').text(this.value.length); });

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return $('<div>').text(str).html();
    }

    // --- Load dropdown data + existing product data together ---
    $(function () {
        $.when(
            $.ajax({ url: '../../api/products/get-form-data.php', type: 'GET', dataType: 'json' }),
            $.ajax({ url: '../../api/products/get-product.php', type: 'GET', data: { id: productId }, dataType: 'json' })
        ).done(function (formRes, productRes) {
            var formData = formRes[0];
            var productData = productRes[0];

            if (!formData.success) {
                toast(formData.message || 'Failed to load form data', 'err');
                return;
            }
            if (!productData.success) {
                toast(productData.message || 'Product not found', 'err');
                $('#loadingBox').html('<p>' + (productData.message || 'Product not found') + '</p>');
                return;
            }

            populateDropdowns(formData);
            populateForm(productData.data);

            $('#loadingBox').hide();
            $('#formWrapper').show();
        }).fail(function () {
            toast('Failed to load product details', 'err');
            $('#loadingBox').html('<p>Something went wrong while loading this product.</p>');
        });
    });

    function populateDropdowns(res) {
        var brandOptions = '<option value="">Select a brand...</option>';
        res.brands.forEach(function (b) { brandOptions += '<option value="' + b.id + '">' + escapeHtml(b.name) + '</option>'; });
        $('#brand_id').html(brandOptions).select2({ placeholder: 'Select a brand...', width: '100%' });

        var catOptions = '<option value="">Select a category...</option>';
        res.categories.forEach(function (c) { catOptions += '<option value="' + c.id + '">' + escapeHtml(c.name) + '</option>'; });
        $('#category_id').html(catOptions).select2({ placeholder: 'Select a category...', width: '100%' });

        var ingOptions = '';
        res.ingredients.forEach(function (i) { ingOptions += '<option value="' + i.id + '">' + escapeHtml(i.name) + '</option>'; });
        $('#ingredients').html(ingOptions).select2({ placeholder: 'Select ingredients...', width: '100%' });

        var countryOptions = '<option value="">Select a country to add pricing...</option>';
        res.countries.forEach(function (c) {
            countriesData[c.id] = c;
            countryOptions += '<option value="' + c.id + '">' + escapeHtml(c.name) + ' (' + c.code + ')</option>';
        });
        $('#countryPicker').html(countryOptions).select2({ placeholder: 'Select a country...', width: '280px' });
    }

    function populateForm(p) {
        $('#name').val(p.name);
        $('#calories').val(p.calories);
        $('#status').val(p.status);
        $('#featured').prop('checked', p.featured == 1);
        $('#short_description').val(p.short_description || '');
        $('#meta_title').val(p.meta_title || '').trigger('input');
        $('#meta_description').val(p.meta_description || '').trigger('input');

        $('#brand_id').val(p.brand_id).trigger('change');
        $('#category_id').val(p.category_id).trigger('change');

        if (p.description) quill.root.innerHTML = p.description;
        $('#description').val(p.description || '');

        var ingIds = (p.ingredients || []).map(function (i) { return String(i.id); });
        $('#ingredients').val(ingIds).trigger('change');

        // --- Images ---
        existingImages = p.images || [];
        if (existingImages.length > 0) {
            currentMainImageId = existingImages[0].id; // first by sort_order is the current main
        }
        renderGalleryPreview();

        // --- Prices ---
        (p.prices || []).forEach(function (pr) {
            addPriceRow(pr.country_id, pr.regular_price, pr.discount_price);
        });

        // --- Nutrition ---
        if (p.nutrition) {
            $('#nutrition_calories').val(p.nutrition.calories);
            $('#protein').val(p.nutrition.protein);
            $('#fat').val(p.nutrition.fat);
            $('#carbs').val(p.nutrition.carbs);
            $('#fiber').val(p.nutrition.fiber);
            $('#sugar').val(p.nutrition.sugar);
            $('#sodium').val(p.nutrition.sodium);
        }
    }

    // --- Price rows ---
    function addPriceRow(countryId, regularPrice, discountPrice) {
        countryId = String(countryId);
        if (selectedCountryIds.indexOf(countryId) !== -1) return;

        var country = countriesData[countryId];
        if (!country) return;

        selectedCountryIds.push(countryId);
        $('#noPricesMsg').hide();

        var rowHtml = '' +
            '<div class="price-row" data-country-id="' + countryId + '">' +
                '<div class="country-label">' + country.flag_html + ' ' + escapeHtml(country.name) + '</div>' +
                '<div><input type="number" step="0.01" min="0" class="fi" placeholder="Regular price" name="prices[' + countryId + '][regular_price]" value="' + (regularPrice !== undefined && regularPrice !== null ? regularPrice : '') + '" required></div>' +
                '<div><input type="number" step="0.01" min="0" class="fi" placeholder="Discount price (optional)" name="prices[' + countryId + '][discount_price]" value="' + (discountPrice !== undefined && discountPrice !== null ? discountPrice : '') + '"></div>' +
                '<div style="font-size:.8rem;text-align:center;color:var(--muted);">' + escapeHtml(country.currency) + '</div>' +
                '<div class="remove-price-row" title="Remove"><i class="fas fa-times"></i></div>' +
            '</div>';

        $('#priceRowsContainer').append(rowHtml);
    }

    $('#addCountryPriceBtn').on('click', function () {
        var countryId = $('#countryPicker').val();
        if (!countryId) { toast('Please select a country first.', 'err'); return; }
        if (selectedCountryIds.indexOf(countryId) !== -1) { toast('This country has already been added.', 'err'); return; }
        addPriceRow(countryId, '', '');
        $('#countryPicker').val('').trigger('change');
    });

    $(document).on('click', '.remove-price-row', function () {
        var $row = $(this).closest('.price-row');
        var countryId = $row.data('country-id').toString();
        selectedCountryIds = selectedCountryIds.filter(function (id) { return id !== countryId; });
        $row.remove();
        if (selectedCountryIds.length === 0) $('#noPricesMsg').show();
    });

    // --- Gallery: existing + new, with main-image selection ---
    function totalActiveImageCount() {
        var activeExisting = existingImages.filter(function (g) { return removedImageIds.indexOf(g.id) === -1; }).length;
        return activeExisting + newImageFiles.length;
    }

    $('#images').on('change', function () {
        var incoming = Array.from(this.files);
        if (totalActiveImageCount() + incoming.length > 10) {
            toast('Maximum 10 images allowed in total.', 'err');
            this.value = '';
            return;
        }
        newImageFiles = newImageFiles.concat(incoming);
        renderGalleryPreview();
        this.value = '';
    });

    function renderGalleryPreview() {
        var $container = $('#galleryPreviewContainer');
        $container.empty();

        existingImages.forEach(function (img) {
            var isRemoved = removedImageIds.indexOf(img.id) !== -1;
            var isMain = (currentMainImageId === img.id) && !isRemoved;
            var mainTag = isMain ? "<div class='main-tag'>MAIN</div>" : '';

            var $item = $('<div class="gallery-preview-item' + (isRemoved ? ' existing-marked-remove' : '') + '" data-existing-id="' + img.id + '" title="Click to set as main image">' +
                mainTag +
                '<img src="' + BASE_URL_JS + '/' + img.image + '" alt="Product image">' +
                '<div class="remove-gallery-item" title="Remove"><i class="fas fa-times"></i></div>' +
                '<div class="undo-remove-item" title="Undo"><i class="fas fa-undo"></i></div>' +
                '</div>');
            $container.append($item);
        });

        newImageFiles.forEach(function (file, index) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var isMain = (currentMainImageId === null) && index === 0; // only "auto-main" if no existing image is main
                var mainTag = isMain ? "<div class='main-tag'>MAIN</div>" : '';
                var $item = $('<div class="gallery-preview-item" data-new-index="' + index + '">' +
                    mainTag +
                    '<img src="' + e.target.result + '" alt="New image">' +
                    '<div class="remove-gallery-item" title="Remove"><i class="fas fa-times"></i></div>' +
                    '</div>');
                $container.append($item);
            };
            reader.readAsDataURL(file);
        });

        $('#mainImageId').val(currentMainImageId !== null ? currentMainImageId : '');
    }

    // Click a thumbnail (not the × or undo) to set it as main
    $(document).on('click', '.gallery-preview-item img', function () {
        var $item = $(this).closest('.gallery-preview-item');
        if ($item.hasClass('existing-marked-remove')) return; // can't set a removed image as main

        var existingId = $item.data('existing-id');
        if (existingId !== undefined) {
            currentMainImageId = existingId;
            renderGalleryPreview();
        } else {
            toast('Newly added images are automatically ordered — remove existing images to promote a new one, or save first.', 'inf');
        }
    });

    $(document).on('click', '.remove-gallery-item', function () {
        var $item = $(this).closest('.gallery-preview-item');

        if ($item.data('existing-id') !== undefined) {
            var existingId = $item.data('existing-id');
            if (removedImageIds.indexOf(existingId) === -1) removedImageIds.push(existingId);
            if (currentMainImageId === existingId) currentMainImageId = null; // main image was removed
            renderGalleryPreview();
        } else {
            var newIndex = $item.data('new-index');
            newImageFiles.splice(newIndex, 1);
            renderGalleryPreview();
        }
    });

    $(document).on('click', '.undo-remove-item', function () {
        var $item = $(this).closest('.gallery-preview-item');
        var existingId = $item.data('existing-id');
        removedImageIds = removedImageIds.filter(function (id) { return id !== existingId; });
        renderGalleryPreview();
    });

    // --- Submit ---
    $('#editProductForm').on('submit', function (e) {
        e.preventDefault();

        $('.invalid-feedback').text('');

        $('#description').val(quill.root.innerHTML);
        if (quill.getText().trim() === '') $('#description').val('');

        $('#removedImageIds').val(JSON.stringify(removedImageIds));

        var formData = new FormData(this);
        formData.delete('images[]');
        newImageFiles.forEach(function (file) { formData.append('images[]', file); });

        var $btn = $('#submitBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: '../../api/products/update-product.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                toast(res.message || 'Product updated successfully', 'suc');
                setTimeout(function () { window.location.href = 'index.php'; }, 900);
            } else {
                if (res.errors) $.each(res.errors, function (field, msg) { $('#err_' + field).text(msg); });
                toast(res.message || 'Please fix the errors below', 'err');
            }
        })
        .fail(function () {
            toast('Something went wrong. Please try again.', 'err');
        })
        .always(function () {
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Product');
        });
    });
    </script>
</body>

</html>