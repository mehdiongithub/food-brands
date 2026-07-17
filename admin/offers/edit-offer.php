<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();

$token = $_GET['token'] ?? '';
$id = decryptId($token);

if ($id === false || $id <= 0) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

if (currentUserRole() !== 'admin') {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

// Confirm the offer actually exists — invalid/deleted IDs also redirect
$checkStmt = $pdo->prepare("SELECT id FROM offers WHERE id = :id LIMIT 1");
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
    <title>MenuCrest — Edit Offer</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/style.css">
    <style>
        #descriptionEditor {
            background: var(--surface, #fff);
            min-height: 140px;
            border-radius: 0 0 var(--r-md, 8px) var(--r-md, 8px);
        }
        .ql-toolbar.ql-snow {
            border-radius: var(--r-md, 8px) var(--r-md, 8px) 0 0;
            background: var(--bg, #f8f8f8);
        }

        /* --- Country add-list UI --- */
        .country-picker-row {
            display: flex;
            gap: 8px;
        }
        .country-picker-row select {
            flex: 1;
        }
        .country-picker-row button {
            white-space: nowrap;
        }
        #selectedCountriesList {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
            min-height: 20px;
        }
        .country-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--bg, #f2f2f2);
            border: 1px solid var(--border, #e5e5e5);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: .8rem;
        }
        .country-chip .remove-chip {
            cursor: pointer;
            color: #DC2626;
            font-weight: 700;
            line-height: 1;
        }
        .country-chip .remove-chip:hover {
            opacity: .7;
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
                    <h1 class="pg-title">Edit Offer</h1>
                    <p class="pg-desc">Update offer details</p>
                </div>
                <a href="index.php" class="bo text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Back to Offers
                </a>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="formContainer">
                    <div style="text-align:center;padding:40px;color:var(--muted);" id="loadingState">
                        <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                        <p style="margin-top:10px;">Loading offer details...</p>
                    </div>

                    <form id="editOfferForm" enctype="multipart/form-data" style="display:none;">
                        <input type="hidden" name="id" id="offerIdField" value="">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="fl">Brand <span style="color:red">*</span></label>
                                <select class="fss" name="brand_id" id="brand_id" required>
                                    <option value="">Loading brands...</option>
                                </select>
                                <div class="invalid-feedback" id="err_brand_id"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Title <span style="color:red">*</span></label>
                                <input type="text" class="fi" name="title" id="title" required maxlength="255" placeholder="Enter offer title">
                                <div class="invalid-feedback" id="err_title"></div>
                            </div>

                            <div class="col-md-12">
                                <label class="fl">Description</label>
                                <div id="descriptionEditor"></div>
                                <textarea name="description" id="description" style="display:none;"></textarea>
                                <div class="invalid-feedback" id="err_description"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="fl">Discount Percent</label>
                                <input type="number" class="fi" name="discount_percent" id="discount_percent" min="0" max="100" step="0.01" placeholder="0.00">
                                <div class="invalid-feedback" id="err_discount_percent"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="fl">Coupon Code</label>
                                <input type="text" class="fi" name="coupon_code" id="coupon_code" maxlength="50" placeholder="e.g. SAVE20">
                                <div class="invalid-feedback" id="err_coupon_code"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="fl">Status</label>
                                <select class="fss" name="status" id="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Start Date</label>
                                <input type="date" class="fi" name="start_date" id="start_date">
                                <div class="invalid-feedback" id="err_start_date"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">End Date</label>
                                <input type="date" class="fi" name="end_date" id="end_date">
                                <div class="invalid-feedback" id="err_end_date"></div>
                            </div>

                            <div class="col-md-12">
                                <label class="fl">Countries</label>
                                <div class="country-picker-row">
                                    <select class="fss" id="countrySelect">
                                        <option value="">Loading countries...</option>
                                    </select>
                                    <button type="button" class="bo" id="addCountryBtn">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </div>
                                <div id="selectedCountriesList"></div>
                                <div id="countriesContainer"><!-- hidden inputs injected here --></div>
                                <div class="invalid-feedback" id="err_countries"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Image</label>
                                <input type="file" class="fi" name="image" id="image" accept="image/png, image/jpeg, image/jpg, image/webp">
                                <small style="color:var(--muted);font-size:.72rem;">Leave empty to keep current image. JPG, PNG or WEBP. Max 2MB.</small>
                                <div class="invalid-feedback" id="err_image"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Image Preview</label>
                                <div>
                                    <img id="imagePreview" src="" alt="Preview"
                                         style="display:none;width:70px;height:70px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                                </div>
                            </div>

                        </div>

                        <div class="mo-f" style="padding:0;margin-top:22px;border:none;display:flex;gap:10px;">
                            <button type="button" class="bo" onclick="window.location.href='index.php'">Cancel</button>
                            <button type="submit" class="ba" id="submitBtn">
                                <i class="fas fa-save"></i> Update Offer
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>

    <div class="tw2" id="tw2"></div>
    <div id="formAlert" style="display:none;" class="alert" role="alert"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/admin/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script>

    var offerId = <?= json_encode($id) ?>;
    var BASE_URL_JS = <?= json_encode(BASE_URL) ?>;

    function toast(m, t) {
        t = t || 'suc';
        var ic = { suc: 'fa-check-circle', err: 'fa-times-circle', wrn: 'fa-exclamation-triangle', inf: 'fa-info-circle' };
        var $t = $('<div class="ti2 ' + t + '"><i class="fas ' + ic[t] + '"></i><span>' + m + '</span></div>');
        $('#tw2').append($t);
        setTimeout(function () {
            $t.fadeOut(250, function () { $(this).remove(); });
        }, 2800);
    }

    // --- Initialize Quill rich text editor (content injected after AJAX load) ---
    var quill = new Quill('#descriptionEditor', {
        theme: 'snow',
        placeholder: 'Enter offer description...',
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

    // --- Selected countries state ---
    var selectedCountries = {}; // { id: name }

    function renderSelectedCountries() {
        var $list = $('#selectedCountriesList');
        var $hiddenContainer = $('#countriesContainer');
        $list.empty();
        $hiddenContainer.empty();

        $.each(selectedCountries, function (id, name) {
            var $chip = $(
                '<span class="country-chip">' +
                    '<span>' + name + '</span>' +
                    '<span class="remove-chip" data-id="' + id + '">&times;</span>' +
                '</span>'
            );
            $list.append($chip);

            $hiddenContainer.append('<input type="hidden" name="countries[]" value="' + id + '">');
        });
    }

    $('#selectedCountriesList').on('click', '.remove-chip', function () {
        var id = $(this).data('id');
        delete selectedCountries[id];
        renderSelectedCountries();
    });

    $('#addCountryBtn').on('click', function () {
        var $sel = $('#countrySelect');
        var id = $sel.val();
        var name = $sel.find('option:selected').text();

        if (!id) {
            toast('Please select a country first', 'wrn');
            return;
        }
        if (selectedCountries[id]) {
            toast('Country already added', 'wrn');
            return;
        }

        selectedCountries[id] = name;
        renderSelectedCountries();

        $sel.val('');
    });

    // --- Live image preview (only replaces preview if a NEW file is chosen) ---
    $('#image').on('change', function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#imagePreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
        // If cleared, keep showing the existing image — no reset needed
    });

    // --- Load brands, countries, AND existing offer data ---
    $(function () {
        $.ajax({
            url: '../../api/offers/get-form-data.php',
            type: 'GET',
            dataType: 'json'
        })
        .done(function (formRes) {
            if (!formRes.success) {
                toast(formRes.message || 'Failed to load form data', 'err');
                return;
            }

            var $brandSelect = $('#brand_id');
            $brandSelect.empty().append('<option value="">Select a brand</option>');
            $.each(formRes.brands, function (i, b) {
                $brandSelect.append('<option value="' + b.id + '">' + b.name + '</option>');
            });

            var $countrySelect = $('#countrySelect');
            $countrySelect.empty().append('<option value="">Select a country</option>');
            $.each(formRes.countries, function (i, c) {
                $countrySelect.append('<option value="' + c.id + '">' + c.name + '</option>');
            });

            // Once dropdowns are ready, load the actual offer data
            loadOffer();
        })
        .fail(function () {
            toast('Failed to load form data', 'err');
        });
    });

    function loadOffer() {
        $.ajax({
            url: '../../api/offers/get-offer.php',
            type: 'GET',
            data: { id: offerId },
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                populateForm(res.data);
                $('#loadingState').hide();
                $('#editOfferForm').show();
            } else {
                toast(res.message || 'Offer not found', 'err');
                $('#loadingState').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                    '<i class="fas fa-triangle-exclamation" style="font-size:1.4rem;"></i>' +
                    '<p style="margin-top:10px;">' + (res.message || 'Offer not found') + '</p></div>');
            }
        })
        .fail(function () {
            toast('Failed to load offer details', 'err');
            $('#loadingState').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                '<p>Something went wrong while loading this offer.</p></div>');
        });
    }

    function populateForm(o) {
        $('#offerIdField').val(o.id);
        $('#brand_id').val(o.brand_id);
        $('#title').val(o.title || '');
        $('#discount_percent').val(o.discount_percent || '');
        $('#coupon_code').val(o.coupon_code || '');
        $('#status').val(o.status == 1 ? '1' : '0');
        $('#start_date').val(o.start_date || '');
        $('#end_date').val(o.end_date || '');

        // --- Description: seed Quill with existing HTML ---
        quill.root.innerHTML = o.description || '';
        $('#description').val(o.description || '');

        // --- Countries: pre-fill chips from existing relations ---
        selectedCountries = {};
        if (o.countries && o.countries.length > 0) {
            $.each(o.countries, function (i, c) {
                selectedCountries[c.id] = c.name;
            });
        }
        renderSelectedCountries();

        // --- Image preview ---
        if (o.image) {
            $('#imagePreview').attr('src', BASE_URL_JS + '/' + o.image).show();
        } else {
            $('#imagePreview').hide();
        }
    }

    $('#editOfferForm').on('submit', function (e) {
        e.preventDefault();

        $('.invalid-feedback').text('');

        $('#description').val(quill.root.innerHTML);
        var descText = quill.getText().trim();
        if (descText === '') {
            $('#description').val('');
        }

        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        if (startDate && endDate && endDate < startDate) {
            $('#err_end_date').text('End date must be after start date.');
            toast('End date must be after start date', 'err');
            return;
        }

        var formData = new FormData(this);
        var $btn = $('#submitBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '../../api/offers/update-offer.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                toast(res.message || 'Offer updated successfully', 'suc');
                setTimeout(function () {
                    window.location.href = 'index.php';
                }, 900);
            } else {
                if (res.errors) {
                    $.each(res.errors, function (field, msg) {
                        $('#err_' + field).text(msg);
                    });
                }
                toast(res.message || 'Please fix the errors below', 'err');
            }
        })
        .fail(function () {
            toast('Something went wrong. Please try again.', 'err');
        })
        .always(function () {
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Offer');
        });
    });
    </script>
</body>

</html>