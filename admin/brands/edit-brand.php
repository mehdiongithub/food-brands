<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin(); // both admin and employee can view

$token = $_GET['token'] ?? '';
$id = decryptId($token);

if ($id === false || $id <= 0) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

$checkStmt = $pdo->prepare("SELECT id FROM brands WHERE id = :id LIMIT 1");
$checkStmt->execute([':id' => $id]);
if (!$checkStmt->fetch()) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$countries  = $pdo->query("SELECT id, name FROM countries WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MenuCrest — Edit Brand</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/style.css">
    <style>
        #historyEditor { background: var(--surface, #fff); min-height: 180px; border-radius: 0 0 var(--r-md, 8px) var(--r-md, 8px); }
        .ql-toolbar.ql-snow { border-radius: var(--r-md, 8px) var(--r-md, 8px) 0 0; background: var(--bg, #f8f8f8); }

        .select2-container .select2-selection--multiple {
            min-height: 42px;
            border: 1.5px solid var(--border) !important;
            border-radius: var(--r-md, 8px) !important;
            padding: 4px 6px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: var(--accent, #E85D04);
            border: none;
            color: #fff;
            border-radius: 6px;
            padding: 3px 10px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff;
            margin-right: 6px;
        }

        .form-section-title {
            font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
            color: var(--muted); margin: 26px 0 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border-l);
        }
        .form-section-title:first-child { margin-top: 0; }

        .gallery-preview-item { position: relative; display: inline-block; margin: 6px; }
        .gallery-preview-item img { width: 90px; height: 90px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); }
        .gallery-preview-item.existing-marked-remove img { opacity: .3; }
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
                    <h1 class="pg-title">Edit Brand</h1>
                    <p class="pg-desc">Update brand details</p>
                </div>
                <a href="index.php" class="bo text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Back to Brands
                </a>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="formWrapper" style="display:none;">

                    <form id="editBrandForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="brandId" value="<?= $id ?>">
                        <input type="hidden" name="existing_logo" id="existingLogo" value="">
                        <input type="hidden" name="existing_cover_image" id="existingCover" value="">
                        <input type="hidden" name="removed_gallery_ids" id="removedGalleryIds" value="">

                        <!-- ===== BASIC INFO ===== -->
                        <div class="form-section-title"><i class="fas fa-info-circle"></i> Basic Information</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fl">Brand Name <span style="color:red">*</span></label>
                                <input type="text" class="fi" name="name" id="name" required maxlength="100">
                                <div class="invalid-feedback" id="err_name"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Website URL</label>
                                <input type="url" class="fi" name="website" id="website" maxlength="255">
                                <div class="invalid-feedback" id="err_website"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Founded Year</label>
                                <input type="number" class="fi" name="founded_year" id="founded_year" min="1800" max="<?= date('Y') ?>">
                                <div class="invalid-feedback" id="err_founded_year"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Status</label>
                                <select class="fss" name="status" id="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="fl">Short Description</label>
                                <textarea class="fi" name="short_description" id="short_description" rows="3" maxlength="500"></textarea>
                                <div class="invalid-feedback" id="err_short_description"></div>
                            </div>
                        </div>

                        <!-- ===== HISTORY ===== -->
                        <div class="form-section-title"><i class="fas fa-book-open"></i> Brand History</div>
                        <div id="historyEditor"></div>
                        <textarea name="history" id="history" style="display:none;"></textarea>

                        <!-- ===== CATEGORIES & COUNTRIES ===== -->
                        <div class="form-section-title"><i class="fas fa-diagram-project"></i> Categories & Availability</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fl" style="display:flex;align-items:center;justify-content:space-between;">
                                    <span>Categories</span>
                                    <span style="font-size:.72rem;font-weight:600;">
                                        <a href="#" class="select-all-link" data-target="#categories" style="color:var(--primary);text-decoration:none;">Select All</a>
                                        <span style="color:var(--muted);">|</span>
                                        <a href="#" class="clear-all-link" data-target="#categories" style="color:var(--muted);text-decoration:none;">Clear</a>
                                    </span>
                                </label>
                                <select class="fss" name="categories[]" id="categories" multiple style="width:100%;">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback" id="err_categories"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl" style="display:flex;align-items:center;justify-content:space-between;">
                                    <span>Available Countries</span>
                                    <span style="font-size:.72rem;font-weight:600;">
                                        <a href="#" class="select-all-link" data-target="#countries" style="color:var(--primary);text-decoration:none;">Select All</a>
                                        <span style="color:var(--muted);">|</span>
                                        <a href="#" class="clear-all-link" data-target="#countries" style="color:var(--muted);text-decoration:none;">Clear</a>
                                    </span>
                                </label>
                                <select class="fss" name="countries[]" id="countries" multiple style="width:100%;">
                                    <?php foreach ($countries as $ctry): ?>
                                        <option value="<?= $ctry['id'] ?>"><?= htmlspecialchars($ctry['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback" id="err_countries"></div>
                            </div>
                        </div>

                        <!-- ===== IMAGES ===== -->
                        <div class="form-section-title"><i class="fas fa-images"></i> Brand Images</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fl">Logo</label>
                                <input type="file" class="fi" name="logo" id="logo" accept="image/png, image/jpeg, image/jpg, image/webp">
                                <small style="color:var(--muted);font-size:.72rem;">Leave empty to keep current logo. Max 2MB.</small>
                                <div class="invalid-feedback" id="err_logo"></div>
                                <div style="margin-top:10px;">
                                    <img id="logoPreview" src="" alt="Logo preview" style="display:none;width:80px;height:80px;object-fit:cover;border-radius:10px;border:1px solid var(--border);">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Cover Image</label>
                                <input type="file" class="fi" name="cover_image" id="cover_image" accept="image/png, image/jpeg, image/jpg, image/webp">
                                <small style="color:var(--muted);font-size:.72rem;">Leave empty to keep current cover. Max 3MB.</small>
                                <div class="invalid-feedback" id="err_cover_image"></div>
                                <div style="margin-top:10px;">
                                    <img id="coverPreview" src="" alt="Cover preview" style="display:none;width:100%;max-width:320px;height:100px;object-fit:cover;border-radius:10px;border:1px solid var(--border);">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="fl">Gallery Images</label>
                                <input type="file" class="fi" name="gallery[]" id="gallery" accept="image/png, image/jpeg, image/jpg, image/webp" multiple>
                                <small style="color:var(--muted);font-size:.72rem;">Existing images shown below — click × to remove one, or add new ones. Max 10 total, 3MB each.</small>
                                <div class="invalid-feedback" id="err_gallery"></div>
                                <div id="galleryPreviewContainer" style="margin-top:12px;"></div>
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
                                <i class="fas fa-save"></i> Update Brand
                            </button>
                        </div>

                    </form>

                </div>

                <div id="loadingBox" style="text-align:center;padding:40px;color:var(--muted);">
                    <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                    <p style="margin-top:10px;">Loading brand details...</p>
                </div>

            </div>
        </div>
    </div>

    <div class="tw2" id="tw2"></div>
    <div id="formAlert" style="display:none;" class="alert" role="alert"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../../assets/js/admin/sidebar.js"></script>
    <script>
    function toast(m, t) {
            t = t || 'suc';
            var ic = { suc: 'fa-check-circle', err: 'fa-times-circle', wrn: 'fa-exclamation-triangle', inf: 'fa-info-circle' };
            var $t = $('<div class="ti2 ' + t + '"><i class="fas ' + ic[t] + '"></i><span>' + m + '</span></div>');
            $('#tw2').append($t);
            setTimeout(function () {
                $t.fadeOut(250, function () { $(this).remove(); });
            }, 2800);
    }
    var brandId = $('#brandId').val();
    var BASE_URL_JS = <?= json_encode(BASE_URL) ?>;

    $('#categories').select2({ placeholder: 'Select categories...', width: '100%' });
    $('#countries').select2({ placeholder: 'Select countries...', width: '100%' });

    // --- "Select All" / "Clear" links for the Categories & Countries multi-selects ---
    $('.select-all-link').on('click', function (e) {
        e.preventDefault();
        var $select = $($(this).data('target'));
        var allValues = $select.find('option').map(function () { return $(this).val(); }).get();
        $select.val(allValues).trigger('change');
    });

    $('.clear-all-link').on('click', function (e) {
        e.preventDefault();
        var $select = $($(this).data('target'));
        $select.val(null).trigger('change');
    });

    var quill = new Quill('#historyEditor', {
        theme: 'snow',
        placeholder: 'Write the brand\'s history...',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'link'],
                ['clean']
            ]
        }
    });
    quill.on('text-change', function () {
        $('#history').val(quill.root.innerHTML);
    });

    $('#meta_title').on('input', function () { $('#metaTitleCount').text(this.value.length); });
    $('#meta_description').on('input', function () { $('#metaDescCount').text(this.value.length); });

    // --- Gallery state management ---
    var newGalleryFiles = [];      // newly added File objects
    var existingGallery = [];      // [{id, image}] loaded from server
    var removedGalleryIds = [];    // ids marked for removal

    // --- Load existing brand data ---
    $(function () {
        $.ajax({
            url: '../../api/brands/get-brand.php',
            type: 'GET',
            data: { id: brandId },
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                populateForm(res.data);
                $('#loadingBox').hide();
                $('#formWrapper').show();
            } else {
                toast(res.message || 'Brand not found', 'err');
                $('#loadingBox').html('<p>' + (res.message || 'Brand not found') + '</p>');
            }
        })
        .fail(function () {
            toast('Failed to load brand details', 'err');
            $('#loadingBox').html('<p>Something went wrong while loading this brand.</p>');
        });
    });

    function populateForm(b) {
        $('#name').val(b.name);
        $('#website').val(b.website || '');
        $('#founded_year').val(b.founded_year || '');
        $('#status').val(b.status);
        $('#short_description').val(b.short_description || '');
        $('#meta_title').val(b.meta_title || '').trigger('input');
        $('#meta_description').val(b.meta_description || '').trigger('input');

        if (b.history) quill.root.innerHTML = b.history;
        $('#history').val(b.history || '');

        // Pre-select categories/countries in Select2
        var catIds = (b.categories || []).map(function (c) { return String(c.id); });
        var ctryIds = (b.countries || []).map(function (c) { return String(c.id); });
        $('#categories').val(catIds).trigger('change');
        $('#countries').val(ctryIds).trigger('change');

        // Logo / cover previews
        $('#existingLogo').val(b.logo || '');
        $('#existingCover').val(b.cover_image || '');

        if (b.logo) $('#logoPreview').attr('src', BASE_URL_JS + '/' + b.logo).show();
        if (b.cover_image) $('#coverPreview').attr('src', BASE_URL_JS + '/' + b.cover_image).show();

        // Existing gallery
        existingGallery = b.gallery || [];
        renderGalleryPreview();
    }

    $('#logo').on('change', function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) { $('#logoPreview').attr('src', e.target.result).show(); };
            reader.readAsDataURL(file);
        }
    });

    $('#cover_image').on('change', function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) { $('#coverPreview').attr('src', e.target.result).show(); };
            reader.readAsDataURL(file);
        }
    });

    // --- Gallery: combined render of existing (not removed) + new files ---
    function totalActiveGalleryCount() {
        var activeExisting = existingGallery.filter(function (g) { return removedGalleryIds.indexOf(g.id) === -1; }).length;
        return activeExisting + newGalleryFiles.length;
    }

    $('#gallery').on('change', function () {
        var incoming = Array.from(this.files);

        if (totalActiveGalleryCount() + incoming.length > 10) {
            toast('Maximum 10 gallery images allowed in total.', 'err');
            this.value = '';
            return;
        }

        newGalleryFiles = newGalleryFiles.concat(incoming);
        renderGalleryPreview();
        this.value = ''; // reset input so selecting the same file again still fires 'change'
    });

    function renderGalleryPreview() {
        var $container = $('#galleryPreviewContainer');
        $container.empty();

        // Existing images (from DB)
        existingGallery.forEach(function (g) {
            var isRemoved = removedGalleryIds.indexOf(g.id) !== -1;
            var $item = $('<div class="gallery-preview-item' + (isRemoved ? ' existing-marked-remove' : '') + '" data-existing-id="' + g.id + '">' +
                '<img src="' + BASE_URL_JS + '/' + g.image + '" alt="Gallery image">' +
                '<div class="remove-gallery-item" title="Remove"><i class="fas fa-times"></i></div>' +
                '<div class="undo-remove-item" title="Undo"><i class="fas fa-undo"></i></div>' +
                '</div>');
            $container.append($item);
        });

        // Newly added files (not yet uploaded)
        newGalleryFiles.forEach(function (file, index) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var $item = $('<div class="gallery-preview-item" data-new-index="' + index + '">' +
                    '<img src="' + e.target.result + '" alt="New gallery image">' +
                    '<div class="remove-gallery-item" title="Remove"><i class="fas fa-times"></i></div>' +
                    '</div>');
                $container.append($item);
            };
            reader.readAsDataURL(file);
        });
    }

    // Remove/undo clicks (delegated since items are re-rendered)
    $(document).on('click', '.gallery-preview-item .remove-gallery-item', function () {
        var $item = $(this).closest('.gallery-preview-item');

        if ($item.data('existing-id') !== undefined) {
            var existingId = $item.data('existing-id');
            if (removedGalleryIds.indexOf(existingId) === -1) {
                removedGalleryIds.push(existingId);
            }
            renderGalleryPreview();
        } else {
            var newIndex = $item.data('new-index');
            newGalleryFiles.splice(newIndex, 1);
            renderGalleryPreview();
        }
    });

    $(document).on('click', '.gallery-preview-item .undo-remove-item', function () {
        var $item = $(this).closest('.gallery-preview-item');
        var existingId = $item.data('existing-id');
        removedGalleryIds = removedGalleryIds.filter(function (id) { return id !== existingId; });
        renderGalleryPreview();
    });

    // --- Submit ---
    $('#editBrandForm').on('submit', function (e) {
        e.preventDefault();

        $('.invalid-feedback').text('');

        $('#history').val(quill.root.innerHTML);
        if (quill.getText().trim() === '') $('#history').val('');

        $('#removedGalleryIds').val(JSON.stringify(removedGalleryIds));

        var formData = new FormData(this);

        formData.delete('gallery[]');
        newGalleryFiles.forEach(function (file) {
            formData.append('gallery[]', file);
        });

        var $btn = $('#submitBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: '../../api/brands/update-brand.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                toast(res.message || 'Brand updated successfully', 'suc');
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
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Brand');
        });
    });
    </script>
</body>

</html>