<?php
require_once __DIR__ . "/../../config/bootstrap.php";

// Fetch categories and countries for the multi-select dropdowns
$categories = $pdo->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$countries  = $pdo->query("SELECT id, name FROM countries WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodScope — Add Brand</title>
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
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--muted);
            margin: 26px 0 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border-l);
        }
        .form-section-title:first-child { margin-top: 0; }

        .gallery-preview-item {
            position: relative;
            display: inline-block;
            margin: 6px;
        }
        .gallery-preview-item img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border);
        }
        .gallery-preview-item .remove-gallery-item {
            position: absolute;
            top: -6px;
            right: -6px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #DC2626;
            color: #fff;
            border: 2px solid var(--surface);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .65rem;
            cursor: pointer;
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
                    <h1 class="pg-title">Add Brand</h1>
                    <p class="pg-desc">Create a new brand profile</p>
                </div>
                <a href="index.php" class="bo text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Back to Brands
                </a>
            </div>

            <div class="cd">
                <div class="cd-b p-4">

                    <form id="addBrandForm" enctype="multipart/form-data">

                        <!-- ===== BASIC INFO ===== -->
                        <div class="form-section-title"><i class="fas fa-info-circle"></i> Basic Information</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fl">Brand Name <span style="color:red">*</span></label>
                                <input type="text" class="fi" name="name" id="name" required maxlength="100" placeholder="e.g. KFC">
                                <div class="invalid-feedback" id="err_name"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Website URL</label>
                                <input type="url" class="fi" name="website" id="website" maxlength="255" placeholder="https://example.com">
                                <div class="invalid-feedback" id="err_website"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Founded Year</label>
                                <input type="number" class="fi" name="founded_year" id="founded_year" min="1800" max="<?= date('Y') ?>" placeholder="e.g. 1952">
                                <div class="invalid-feedback" id="err_founded_year"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Status</label>
                                <select class="fss" name="status" id="status">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="fl">Short Description</label>
                                <textarea class="fi" name="short_description" id="short_description" rows="3" maxlength="500" placeholder="A brief one-paragraph summary shown on brand cards and listings"></textarea>
                                <small style="color:var(--muted);font-size:.72rem;">Max 500 characters. Shown in brand cards/previews.</small>
                                <div class="invalid-feedback" id="err_short_description"></div>
                            </div>
                        </div>

                        <!-- ===== BRAND HISTORY (RICH TEXT) ===== -->
                        <div class="form-section-title"><i class="fas fa-book-open"></i> Brand History</div>
                        <div id="historyEditor"></div>
                        <textarea name="history" id="history" style="display:none;"></textarea>

                        <!-- ===== CATEGORIES & COUNTRIES ===== -->
                        <div class="form-section-title"><i class="fas fa-diagram-project"></i> Categories & Availability</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fl">Categories</label>
                                <select class="fss" name="categories[]" id="categories" multiple style="width:100%;">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small style="color:var(--muted);font-size:.72rem;">Select all categories this brand belongs to (e.g. Burgers, Chicken).</small>
                                <div class="invalid-feedback" id="err_categories"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Available Countries</label>
                                <select class="fss" name="countries[]" id="countries" multiple style="width:100%;">
                                    <?php foreach ($countries as $ctry): ?>
                                        <option value="<?= $ctry['id'] ?>"><?= htmlspecialchars($ctry['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small style="color:var(--muted);font-size:.72rem;">Select all countries where this brand operates.</small>
                                <div class="invalid-feedback" id="err_countries"></div>
                            </div>
                        </div>

                        <!-- ===== IMAGES ===== -->
                        <div class="form-section-title"><i class="fas fa-images"></i> Brand Images</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fl">Logo</label>
                                <input type="file" class="fi" name="logo" id="logo" accept="image/png, image/jpeg, image/jpg, image/webp">
                                <small style="color:var(--muted);font-size:.72rem;">Square icon shown in lists and cards. Recommended 400×400px. Max 2MB.</small>
                                <div class="invalid-feedback" id="err_logo"></div>
                                <div style="margin-top:10px;">
                                    <img id="logoPreview" src="" alt="Logo preview" style="display:none;width:80px;height:80px;object-fit:cover;border-radius:10px;border:1px solid var(--border);">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Cover Image</label>
                                <input type="file" class="fi" name="cover_image" id="cover_image" accept="image/png, image/jpeg, image/jpg, image/webp">
                                <small style="color:var(--muted);font-size:.72rem;">Wide banner shown at the top of the brand page. Recommended 1200×400px. Max 3MB.</small>
                                <div class="invalid-feedback" id="err_cover_image"></div>
                                <div style="margin-top:10px;">
                                    <img id="coverPreview" src="" alt="Cover preview" style="display:none;width:100%;max-width:320px;height:100px;object-fit:cover;border-radius:10px;border:1px solid var(--border);">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="fl">Gallery Images</label>
                                <input type="file" class="fi" name="gallery[]" id="gallery" accept="image/png, image/jpeg, image/jpg, image/webp" multiple>
                                <small style="color:var(--muted);font-size:.72rem;">Optional. Select multiple images (food photos, storefronts, etc.) — max 3MB each, up to 10 images.</small>
                                <div class="invalid-feedback" id="err_gallery"></div>
                                <div id="galleryPreviewContainer" style="margin-top:12px;"></div>
                            </div>
                        </div>

                        <!-- ===== SEO ===== -->
                        <div class="form-section-title"><i class="fas fa-magnifying-glass-chart"></i> SEO Settings</div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="fl">Meta Title</label>
                                <input type="text" class="fi" name="meta_title" id="meta_title" maxlength="255" placeholder="e.g. KFC Menu Prices & Locations | FoodScope">
                                <small style="color:var(--muted);font-size:.72rem;">Shown as the page title in search engine results. Recommended under 60 characters. <span id="metaTitleCount">0</span>/255</small>
                                <div class="invalid-feedback" id="err_meta_title"></div>
                            </div>

                            <div class="col-md-12">
                                <label class="fl">Meta Description</label>
                                <textarea class="fi" name="meta_description" id="meta_description" rows="3" maxlength="500" placeholder="A short summary shown under the title in search engine results"></textarea>
                                <small style="color:var(--muted);font-size:.72rem;">Recommended under 160 characters for best display in search results. <span id="metaDescCount">0</span>/500</small>
                                <div class="invalid-feedback" id="err_meta_description"></div>
                            </div>
                        </div>

                        <div class="mo-f" style="padding:0;margin-top:26px;border:none;display:flex;gap:10px;">
                            <button type="button" class="bo" onclick="window.location.href='index.php'">Cancel</button>
                            <button type="submit" class="ba" id="submitBtn">
                                <i class="fas fa-save"></i> Create Brand
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
    // --- Select2 for categories & countries ---
    $('#categories').select2({ placeholder: 'Select categories...', width: '100%' });
    $('#countries').select2({ placeholder: 'Select countries...', width: '100%' });

    // --- Quill rich text editor for History ---
    var quill = new Quill('#historyEditor', {
        theme: 'snow',
        placeholder: 'Write the brand\'s history, founding story, milestones...',
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

    // --- Character counters ---
    $('#meta_title').on('input', function () {
        $('#metaTitleCount').text(this.value.length);
    });
    $('#meta_description').on('input', function () {
        $('#metaDescCount').text(this.value.length);
    });

    // --- Logo preview ---
    $('#logo').on('change', function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) { $('#logoPreview').attr('src', e.target.result).show(); };
            reader.readAsDataURL(file);
        } else {
            $('#logoPreview').hide();
        }
    });

    // --- Cover image preview ---
    $('#cover_image').on('change', function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) { $('#coverPreview').attr('src', e.target.result).show(); };
            reader.readAsDataURL(file);
        } else {
            $('#coverPreview').hide();
        }
    });

    // --- Gallery multi-image preview ---
    var galleryFiles = []; // holds actual File objects we keep after remove operations

    $('#gallery').on('change', function () {
        var newFiles = Array.from(this.files);

        if (galleryFiles.length + newFiles.length > 10) {
            toast('You can upload a maximum of 10 gallery images.', 'err');
            return;
        }

        galleryFiles = galleryFiles.concat(newFiles);
        renderGalleryPreview();
    });

    function renderGalleryPreview() {
        var $container = $('#galleryPreviewContainer');
        $container.empty();

        galleryFiles.forEach(function (file, index) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var $item = $('<div class="gallery-preview-item">' +
                    '<img src="' + e.target.result + '" alt="Gallery image">' +
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

    // Rebuild the file input's FileList from galleryFiles right before submit,
    // since browsers don't allow directly manipulating a real <input type="file"> FileList
    function buildGalleryFormData(formData) {
        galleryFiles.forEach(function (file) {
            formData.append('gallery[]', file);
        });
    }

    // --- Submit ---
    $('#addBrandForm').on('submit', function (e) {
        e.preventDefault();

        $('.invalid-feedback').text('');

        // Sync Quill content
        $('#history').val(quill.root.innerHTML);
        if (quill.getText().trim() === '') {
            $('#history').val('');
        }

        var formData = new FormData(this);

        // Remove the browser's native gallery[] entries (from the raw <input>),
        // since we manage the actual file list ourselves via galleryFiles[]
        formData.delete('gallery[]');
        buildGalleryFormData(formData);

        var $btn = $('#submitBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '../../api/brands/add-brand.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                toast(res.message || 'Brand created successfully', 'suc');
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
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Create Brand');
        });
    });
    </script>
</body>

</html>