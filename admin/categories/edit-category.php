<?php
require_once __DIR__ . "/../../config/bootstrap.php";

$token = $_GET['token'] ?? '';
$id = decryptId($token);

if ($id === false || $id <= 0) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

$checkStmt = $pdo->prepare("SELECT id FROM categories WHERE id = :id LIMIT 1");
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
    <title>FoodScope — Edit Category</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/style.css">
    <style>
        #descriptionEditor {
            background: var(--surface, #fff);
            min-height: 160px;
            border-radius: 0 0 var(--r-md, 8px) var(--r-md, 8px);
        }
        .ql-toolbar.ql-snow {
            border-radius: var(--r-md, 8px) var(--r-md, 8px) 0 0;
            background: var(--bg, #f8f8f8);
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
                    <h1 class="pg-title">Edit Category</h1>
                    <p class="pg-desc">Update category details</p>
                </div>
                <a href="index.php" class="bo text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Back to Categories
                </a>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="formWrapper" style="display:none;">

                    <form id="editCategoryForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="categoryId" value="<?= $id ?>">
                        <input type="hidden" name="existing_image" id="existingImage" value="">

                        <div class="row g-3">

                            <div class="col-md-8">
                                <label class="fl">Category Name <span style="color:red">*</span></label>
                                <input type="text" class="fi" name="name" id="name" required maxlength="100">
                                <div class="invalid-feedback" id="err_name"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="fl">Parent Category</label>
                                <select class="fss" name="parent_id" id="parent_id" style="width:100%;">
                                    <option value="">— None (Top Level) —</option>
                                </select>
                                <small id="childNotice" style="display:none;color:var(--muted);font-size:.72rem;">This category already has its own child categories, so it can't be assigned a parent.</small>
                                <div class="invalid-feedback" id="err_parent_id"></div>
                            </div>

                            <div class="col-md-12">
                                <label class="fl">Description</label>
                                <div id="descriptionEditor"></div>
                                <textarea name="description" id="description" style="display:none;"></textarea>
                                <div class="invalid-feedback" id="err_description"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Sort Order</label>
                                <input type="number" class="fi" name="sort_order" id="sort_order" min="0">
                                <div class="invalid-feedback" id="err_sort_order"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Status</label>
                                <select class="fss" name="status" id="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Image</label>
                                <input type="file" class="fi" name="image" id="image" accept="image/png, image/jpeg, image/jpg, image/webp">
                                <small style="color:var(--muted);font-size:.72rem;">Leave empty to keep current image. Max 2MB.</small>
                                <div class="invalid-feedback" id="err_image"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Current / Preview Image</label>
                                <div>
                                    <img id="imagePreview" src="" alt="Preview"
                                         style="display:none;width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                                    <div id="noImageBox" style="display:none;width:72px;height:72px;border-radius:8px;background:var(--bg);border:1px solid var(--border);align-items:center;justify-content:center;">
                                        <i class="fas fa-image" style="color:var(--muted);"></i>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="mo-f" style="padding:0;margin-top:22px;border:none;display:flex;gap:10px;">
                            <button type="button" class="bo" onclick="window.location.href='index.php'">Cancel</button>
                            <button type="submit" class="ba" id="submitBtn">
                                <i class="fas fa-save"></i> Update Category
                            </button>
                        </div>

                    </form>

                </div>

                <div id="loadingBox" style="text-align:center;padding:40px;color:var(--muted);">
                    <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                    <p style="margin-top:10px;">Loading category details...</p>
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
    var categoryId = $('#categoryId').val();
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

    // --- Initialize Quill ---
    var quill = new Quill('#descriptionEditor', {
        theme: 'snow',
        placeholder: 'Enter category description...',
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

    // --- Load parent category options (excluding this category itself) + existing category data ---
    $(function () {
        var parentOptionsReq = $.ajax({
            url: '../../api/categories/parent-categories.php',
            type: 'GET',
            data: { exclude_id: categoryId },
            dataType: 'json'
        });

        var categoryReq = $.ajax({
            url: '../../api/categories/get-category.php',
            type: 'GET',
            data: { id: categoryId },
            dataType: 'json'
        });

        $.when(parentOptionsReq, categoryReq).done(function (parentRes, catRes) {
            var parentData = parentRes[0];
            var res = catRes[0];

            var options = '<option value="">— None (Top Level) —</option>';
            if (parentData.success) {
                parentData.data.forEach(function (c) {
                    options += '<option value="' + c.id + '">' + $('<div>').text(c.name).html() + '</option>';
                });
            }
            $('#parent_id').html(options);

            if (res.success) {
                populateForm(res.data);
                $('#loadingBox').hide();
                $('#formWrapper').show();
            } else {
                toast(res.message || 'Category not found', 'err');
                $('#loadingBox').html('<p>' + (res.message || 'Category not found') + '</p>');
            }
        }).fail(function () {
            toast('Failed to load category details', 'err');
            $('#loadingBox').html('<p>Something went wrong while loading this category.</p>');
        });
    });

    function populateForm(c) {
        $('#name').val(c.name);
        $('#sort_order').val(c.sort_order);
        $('#status').val(c.status);
        $('#existingImage').val(c.image || '');

        // --- Parent category ---
        if (c.children_count > 0) {
            // This category already has its own children — it must stay top-level
            $('#parent_id').val('').prop('disabled', true);
            $('#childNotice').show();
        } else {
            $('#parent_id').val(c.parent_id || '');
        }

        // Pre-fill Quill with existing HTML content
        if (c.description) {
            quill.root.innerHTML = c.description;
        }
        $('#description').val(c.description || '');

        if (c.image) {
            $('#imagePreview').attr('src', BASE_URL_JS + '/' + c.image).show();
            $('#noImageBox').hide();
        } else {
            $('#imagePreview').hide();
            $('#noImageBox').css('display', 'flex');
        }
    }

    // --- Live preview when a NEW image is chosen ---
    $('#image').on('change', function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#imagePreview').attr('src', e.target.result).show();
                $('#noImageBox').hide();
            };
            reader.readAsDataURL(file);
        }
    });

    // --- Submit update ---
    $('#editCategoryForm').on('submit', function (e) {
        e.preventDefault();

        $('.invalid-feedback').text('');

        // Ensure latest Quill content is synced before building FormData
        $('#description').val(quill.root.innerHTML);
        if (quill.getText().trim() === '') {
            $('#description').val('');
        }

        var formData = new FormData(this);
        var $btn = $('#submitBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: '../../api/categories/update-category.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                toast(res.message || 'Category updated successfully', 'suc');
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
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Category');
        });
    });
    </script>
</body>

</html>