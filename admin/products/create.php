<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MenuCrest — Add Product</title>
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
        .form-section-sub {
            color: var(--muted); font-size: .78rem; margin-top: -10px; margin-bottom: 14px;
        }

        .price-row {
            display: grid; grid-template-columns: 200px 1fr 1fr 90px auto;
            gap: 10px; align-items: center; padding: 10px; background: var(--bg);
            border-radius: 8px; margin-bottom: 8px;
        }
        .price-row .country-label { display: flex; align-items: center; gap: 6px; font-size: .82rem; font-weight: 600; }
        .price-row .remove-price-row { color: #DC2626; cursor: pointer; width: 30px; height: 30px; display:flex; align-items:center; justify-content:center; }

        .gallery-preview-item { position: relative; display: inline-block; margin: 6px; }
        .gallery-preview-item img { width: 90px; height: 90px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); }
        .gallery-preview-item .remove-gallery-item {
            position: absolute; top: -6px; right: -6px; width: 22px; height: 22px; border-radius: 50%;
            background: #DC2626; color: #fff; border: 2px solid var(--surface);
            display: flex; align-items: center; justify-content: center; font-size: .65rem; cursor: pointer;
        }
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
                    <h1 class="pg-title">Add Product</h1>
                    <p class="pg-desc">Create a new product</p>
                </div>
                <a href="index.php" class="bo text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="formWrapper">

                    <form id="addProductForm" enctype="multipart/form-data">

                        <!-- ===== BASIC INFO ===== -->
                        <div class="form-section-title"><i class="fas fa-info-circle"></i> Basic Information</div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="fl">Product Name <span style="color:red">*</span></label>
                                <input type="text" class="fi" name="name" id="name" required maxlength="200" placeholder="e.g. Zinger Burger">
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
                                <select class="fss" id="category_parent_select" style="width:100%;" required>
                                    <option value="">Loading categories...</option>
                                </select>
                                <div class="invalid-feedback" id="err_category_id"></div>
                            </div>

                            <div class="col-md-6" id="subcategoryWrap" style="display:none;">
                                <label class="fl">Subcategory <span style="color:red">*</span></label>
                                <select class="fss" id="category_child_select" style="width:100%;">
                                    <option value="">Select a subcategory...</option>
                                </select>
                                <div class="invalid-feedback" id="err_subcategory"></div>
                            </div>
                            <input type="hidden" name="category_id" id="category_id" value="">

                            <div class="col-md-6">
                                <label class="fl">Calories (shown in listings)</label>
                                <input type="number" class="fi" name="calories" id="calories" min="0" placeholder="e.g. 550">
                                <div class="invalid-feedback" id="err_calories"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Status</label>
                                <select class="fss" name="status" id="status">
                                    <option value="1" selected>Active</option>
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
                                <textarea class="fi" name="short_description" id="short_description" rows="2" maxlength="500" placeholder="A one-line summary shown on product cards and listings"></textarea>
                                <small style="color:var(--muted);font-size:.72rem;">Max 500 characters. Shown in product listings/cards.</small>
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
                        <small style="color:var(--muted);font-size:.72rem;">Select all ingredients used in this product.</small>

                        <!-- ===== IMAGES ===== -->
                        <div class="form-section-title"><i class="fas fa-images"></i> Product Images</div>
                        <label class="fl">Upload Images</label>
                        <input type="file" class="fi" name="images[]" id="images" accept="image/png, image/jpeg, image/jpg, image/webp" multiple>
                        <small style="color:var(--muted);font-size:.72rem;">First image is used as the main thumbnail. Max 10 images, 3MB each.</small>
                        <div class="invalid-feedback" id="err_images"></div>
                        <div id="galleryPreviewContainer" style="margin-top:12px;"></div>

                        <!-- ===== PRICING BY COUNTRY ===== -->
                        <div class="form-section-title"><i class="fas fa-money-bill-wave"></i> Pricing by Country</div>

                        <div style="margin-bottom:10px;">
                            <select class="fss" id="countryPicker" style="width:280px;display:inline-block;" disabled>
                                <option value="">Select a brand first...</option>
                            </select>
                            <button type="button" class="bo" id="addCountryPriceBtn" style="margin-left:8px;" disabled>
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                        <small style="color:var(--muted);font-size:.72rem;display:block;margin-bottom:8px;" id="countryPickerHint">
                            Select a brand above — only countries where that brand is present will be listed here.
                        </small>

                        <div id="priceRowsContainer">
                            <div style="color:var(--muted);font-size:.82rem;padding:10px 0;" id="noPricesMsg">
                                No countries added yet. Select a country above to set its price.
                            </div>
                        </div>
                        <div class="invalid-feedback" id="err_prices"></div>

                        <!-- ===== NUTRITION INFORMATION ===== -->
                        <div class="form-section-title"><i class="fas fa-heartbeat"></i> Nutrition Information</div>
                        <p class="form-section-sub">
                            Optional, but recommended — accurate nutrition data improves search visibility and lets the product page show a nutrition facts panel.
                        </p>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fl">Calories (kcal)</label>
                                <input type="number" class="fi" name="nutrition_calories" id="nutrition_calories" min="0" step="1" placeholder="e.g. 550">
                                <div class="invalid-feedback" id="err_nutrition_calories"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="fl">Protein (g)</label>
                                <input type="number" class="fi" name="protein" id="protein" min="0" step="0.01" placeholder="e.g. 28.50">
                                <div class="invalid-feedback" id="err_protein"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="fl">Fat (g)</label>
                                <input type="number" class="fi" name="fat" id="fat" min="0" step="0.01" placeholder="e.g. 24.00">
                                <div class="invalid-feedback" id="err_fat"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="fl">Carbohydrates (g)</label>
                                <input type="number" class="fi" name="carbs" id="carbs" min="0" step="0.01" placeholder="e.g. 45.00">
                                <div class="invalid-feedback" id="err_carbs"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="fl">Fiber (g)</label>
                                <input type="number" class="fi" name="fiber" id="fiber" min="0" step="0.01" placeholder="e.g. 2.50">
                                <div class="invalid-feedback" id="err_fiber"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="fl">Sugar (g)</label>
                                <input type="number" class="fi" name="sugar" id="sugar" min="0" step="0.01" placeholder="e.g. 8.00">
                                <div class="invalid-feedback" id="err_sugar"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="fl">Sodium (mg)</label>
                                <input type="number" class="fi" name="sodium" id="sodium" min="0" step="0.01" placeholder="e.g. 980.00">
                                <div class="invalid-feedback" id="err_sodium"></div>
                            </div>
                        </div>

                        <!-- ===== SEO ===== -->
                        <div class="form-section-title"><i class="fas fa-magnifying-glass-chart"></i> SEO Settings</div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="fl">Meta Title</label>
                                <input type="text" class="fi" name="meta_title" id="meta_title" maxlength="255" placeholder="e.g. Zinger Burger - Price & Nutrition | MenuCrest">
                                <small style="color:var(--muted);font-size:.72rem;">Ideally under 60 characters for full display in search results. <span id="metaTitleCount">0</span>/255</small>
                                <div class="invalid-feedback" id="err_meta_title"></div>
                            </div>

                            <div class="col-md-12">
                                <label class="fl">Meta Description</label>
                                <textarea class="fi" name="meta_description" id="meta_description" rows="3" maxlength="500" placeholder="A short summary shown under the title in search results"></textarea>
                                <small style="color:var(--muted);font-size:.72rem;">Ideally under 160 characters. <span id="metaDescCount">0</span>/500</small>
                                <div class="invalid-feedback" id="err_meta_description"></div>
                            </div>
                        </div>

                        <div class="mo-f" style="padding:0;margin-top:26px;border:none;display:flex;gap:10px;">
                            <button type="button" class="bo" onclick="window.location.href='index.php'">Cancel</button>
                            <button type="submit" class="ba" id="submitBtn">
                                <i class="fas fa-save"></i> Create Product
                            </button>
                        </div>

                    </form>

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
    var BASE_URL_JS = <?= json_encode(BASE_URL) ?>;
    var countriesData = {}; // id -> country object, populated after AJAX load
    var childrenByParent = {}; // parent category id -> array of child category objects
    var selectedCountryIds = [];

    // --- Quill ---
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
    quill.on('text-change', function () {
        $('#description').val(quill.root.innerHTML);
    });

    $('#meta_title').on('input', function () { $('#metaTitleCount').text(this.value.length); });
    $('#meta_description').on('input', function () { $('#metaDescCount').text(this.value.length); });

    // --- Load dropdown data ---
    $(function () {
        $.ajax({
            url: '../../api/products/get-form-data.php',
            type: 'GET',
            dataType: 'json'
        })
        .done(function (res) {
            if (!res.success) {
                toast(res.message || 'Failed to load form data', 'err');
                return;
            }

            // Brand (single select)
            var brandOptions = '<option value="">Select a brand...</option>';
            res.brands.forEach(function (b) {
                brandOptions += '<option value="' + b.id + '">' + escapeHtml(b.name) + '</option>';
            });
            $('#brand_id').html(brandOptions).select2({ placeholder: 'Select a brand...', width: '100%' });

            // Category / Subcategory cascade — parents (parent_id null) go in the
            // main "Category" select; children are grouped by parent_id and only
            // revealed in the "Subcategory" select once a parent with children
            // is chosen (e.g. Pizza -> Small/Medium/Large Pizza).
            childrenByParent = {};
            res.categories.forEach(function (c) {
                if (c.parent_id) {
                    if (!childrenByParent[c.parent_id]) childrenByParent[c.parent_id] = [];
                    childrenByParent[c.parent_id].push(c);
                }
            });

            var catOptions = '<option value="">Select a category...</option>';
            res.categories.forEach(function (c) {
                if (!c.parent_id) {
                    catOptions += '<option value="' + c.id + '">' + escapeHtml(c.name) + '</option>';
                }
            });
            $('#category_parent_select').html(catOptions).select2({ placeholder: 'Select a category...', width: '100%' });

            // Ingredients (multi select)
            var ingOptions = '';
            res.ingredients.forEach(function (i) {
                ingOptions += '<option value="' + i.id + '">' + escapeHtml(i.name) + '</option>';
            });
            $('#ingredients').html(ingOptions).select2({ placeholder: 'Select ingredients...', width: '100%' });

            // Countries are NOT loaded here — the picker only fills once a
            // brand is chosen (see brand_id change handler below), and only
            // shows countries where that brand is actually present.
            $('#countryPicker').select2({ placeholder: 'Select a brand first...', width: '280px' });
        })
        .fail(function () {
            toast('Failed to load form data. Please refresh the page.', 'err');
        });
    });

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return $('<div>').text(str).html();
    }

    // --- Country picker: scoped to the selected brand's countries only ---
    function loadCountriesForBrand(brandId) {
        return $.ajax({
            url: '../../api/products/get-brand-countries.php',
            type: 'GET',
            data: { brand_id: brandId },
            dataType: 'json'
        });
    }

    // Turns a failed jqXHR into a readable message. Also logs the raw response
    // to the console — a 200 status with a parse error usually means the
    // "JSON" wasn't actually JSON (an HTML fallback page, a PHP warning
    // printed before the real output, etc.), which is easiest to see raw.
    function describeAjaxFailure(jqXHR, fallback) {
        console.error('Request to ' + (jqXHR.responseURL || 'endpoint') + ' failed. Raw response:', jqXHR.responseText);

        if (jqXHR.responseJSON && jqXHR.responseJSON.message) return jqXHR.responseJSON.message;
        if (jqXHR.status === 0) return 'Network error — could not reach the server.';

        if (jqXHR.status >= 200 && jqXHR.status < 300) {
            // Request succeeded but the body wasn't valid JSON — almost always
            // an HTML page (wrong route / missing file caught by a fallback)
            // or a PHP warning printed before the JSON. See console for the raw response.
            return (fallback || 'Request failed') + ' — server returned HTTP ' + jqXHR.status + ' but the response wasn\'t valid JSON (see browser console for the raw output).';
        }
        return (fallback || 'Request failed') + ' (HTTP ' + jqXHR.status + ' ' + (jqXHR.statusText || '') + ')';
    }

    function resetCountryPicker(placeholderText) {
        countriesData = {};
        selectedCountryIds = [];
        $('#priceRowsContainer').empty().append(
            '<div style="color:var(--muted);font-size:.82rem;padding:10px 0;" id="noPricesMsg">' + placeholderText + '</div>'
        );
        $('#countryPicker').html('<option value="">' + placeholderText + '</option>').val('').trigger('change.select2');
        $('#countryPicker').prop('disabled', true);
        $('#addCountryPriceBtn').prop('disabled', true);
    }

    $('#brand_id').on('change', function () {
        var brandId = $(this).val();

        if (!brandId) {
            resetCountryPicker('Select a brand first...');
            $('#countryPickerHint').text('Select a brand above — only countries where that brand is present will be listed here.');
            return;
        }

        resetCountryPicker('Loading countries...');
        $('#countryPickerHint').text('Loading countries for this brand...');

        loadCountriesForBrand(brandId).done(function (res) {
            if (!res.success) {
                toast(res.message || 'Failed to load countries for this brand.', 'err');
                resetCountryPicker('Failed to load countries');
                return;
            }

            var countryOptions = '<option value="">Select a country to add pricing...</option>';
            res.countries.forEach(function (c) {
                countriesData[c.id] = c;
                countryOptions += '<option value="' + c.id + '">' + escapeHtml(c.name) + ' (' + c.code + ')</option>';
            });
            $('#countryPicker').html(countryOptions).val('').trigger('change.select2');

            if (res.countries.length === 0) {
                $('#countryPicker').prop('disabled', true);
                $('#addCountryPriceBtn').prop('disabled', true);
                $('#countryPickerHint').html('<span style="color:#DC2626;">This brand is not linked to any countries yet. Add countries for this brand first (Brands &rarr; Edit &rarr; Countries) before setting prices.</span>');
            } else {
                $('#countryPicker').prop('disabled', false);
                $('#addCountryPriceBtn').prop('disabled', false);
                $('#countryPickerHint').text('Only countries where this brand is present are listed.');
            }
        }).fail(function (jqXHR) {
            toast(describeAjaxFailure(jqXHR, 'Failed to load countries for this brand.'), 'err');
            resetCountryPicker('Failed to load countries');
        });
    });

    // --- Add a country pricing row ---
    $('#addCountryPriceBtn').on('click', function () {
        var countryId = $('#countryPicker').val();
        if (!countryId) {
            toast('Please select a country first.', 'err');
            return;
        }
        if (selectedCountryIds.indexOf(countryId) !== -1) {
            toast('This country has already been added.', 'err');
            return;
        }

        var country = countriesData[countryId];
        selectedCountryIds.push(countryId);

        $('#noPricesMsg').hide();

        var rowHtml = '' +
            '<div class="price-row" data-country-id="' + countryId + '">' +
                '<div class="country-label">' + country.flag_html + ' ' + escapeHtml(country.name) + '</div>' +
                '<div>' +
                    '<input type="number" step="0.01" min="0" class="fi" placeholder="Regular price" name="prices[' + countryId + '][regular_price]" required>' +
                '</div>' +
                '<div>' +
                    '<input type="number" step="0.01" min="0" class="fi" placeholder="Discount price (optional)" name="prices[' + countryId + '][discount_price]">' +
                '</div>' +
                '<div style="font-size:.8rem;text-align:center;color:var(--muted);">' + escapeHtml(country.currency) + '</div>' +
                '<div class="remove-price-row" title="Remove"><i class="fas fa-times"></i></div>' +
            '</div>';

        $('#priceRowsContainer').append(rowHtml);
        $('#countryPicker').val('').trigger('change');
    });

    $(document).on('click', '.remove-price-row', function () {
        var $row = $(this).closest('.price-row');
        var countryId = $row.data('country-id').toString();
        selectedCountryIds = selectedCountryIds.filter(function (id) { return id !== countryId; });
        $row.remove();

        if (selectedCountryIds.length === 0) {
            $('#noPricesMsg').show();
        }
    });

    // --- Category / Subcategory cascade ---
    function updateSubcategoryOptions(parentId, preselectChildId) {
        var children = childrenByParent[parentId] || [];

        if (children.length > 0) {
            var opts = '<option value="">Select a subcategory...</option>';
            children.forEach(function (c) {
                opts += '<option value="' + c.id + '">' + escapeHtml(c.name) + '</option>';
            });
            $('#category_child_select').html(opts).val(preselectChildId || '').trigger('change.select2');
            if (!$('#category_child_select').data('select2')) {
                $('#category_child_select').select2({ placeholder: 'Select a subcategory...', width: '100%' });
            }
            $('#subcategoryWrap').show();
            $('#category_id').val(preselectChildId || '');
        } else {
            $('#subcategoryWrap').hide();
            $('#category_child_select').html('').val('');
            $('#category_id').val(parentId || '');
        }
    }

    $('#category_parent_select').on('change', function () {
        var parentId = $(this).val();
        updateSubcategoryOptions(parentId, '');
    });

    $(document).on('change', '#category_child_select', function () {
        $('#category_id').val($(this).val() || '');
    });

    // --- Gallery images ---
    var galleryFiles = [];

    $('#images').on('change', function () {
        var incoming = Array.from(this.files);
        if (galleryFiles.length + incoming.length > 10) {
            toast('Maximum 10 images allowed.', 'err');
            this.value = '';
            return;
        }
        galleryFiles = galleryFiles.concat(incoming);
        renderGalleryPreview();
        this.value = '';
    });

    function renderGalleryPreview() {
        var $container = $('#galleryPreviewContainer');
        $container.empty();

        galleryFiles.forEach(function (file, index) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var label = index === 0 ? '<div style="font-size:.65rem;color:var(--accent);font-weight:700;text-align:center;">MAIN</div>' : '';
                var $item = $('<div class="gallery-preview-item">' +
                    '<img src="' + e.target.result + '" alt="Product image">' +
                    label +
                    '<div class="remove-gallery-item" data-index="' + index + '"><i class="fas fa-times"></i></div>' +
                    '</div>');
                $container.append($item);
            };
            reader.readAsDataURL(file);
        });
    }

    $(document).on('click', '.remove-gallery-item', function () {
        var index = $(this).data('index');
        galleryFiles.splice(index, 1);
        renderGalleryPreview();
    });

    // --- Submit ---
    $('#addProductForm').on('submit', function (e) {
        e.preventDefault();

        $('.invalid-feedback').text('');

        // --- Category / Subcategory validation ---
        var parentVal = $('#category_parent_select').val();
        if (!parentVal) {
            $('#err_category_id').text('Please select a category.');
            return;
        }
        var hasChildren = (childrenByParent[parentVal] || []).length > 0;
        if (hasChildren && !$('#category_child_select').val()) {
            $('#err_subcategory').text('Please select a subcategory.');
            return;
        }
        if (!$('#category_id').val()) {
            $('#err_category_id').text('Please select a category.');
            return;
        }

        $('#description').val(quill.root.innerHTML);
        if (quill.getText().trim() === '') $('#description').val('');

        var formData = new FormData(this);

        formData.delete('images[]');
        galleryFiles.forEach(function (file) {
            formData.append('images[]', file);
        });

        var $btn = $('#submitBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '../../api/products/add-product.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                toast(res.message || 'Product created successfully', 'suc');
                setTimeout(function () { window.location.href = 'index.php'; }, 900);
            } else {
                if (res.errors) {
                    $.each(res.errors, function (field, msg) { $('#err_' + field).text(msg); });
                }
                toast(res.message || 'Please fix the errors below', 'err');
            }
        })
        .fail(function () {
            toast('Something went wrong. Please try again.', 'err');
        })
        .always(function () {
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Create Product');
        });
    });
    </script>
</body>

</html>