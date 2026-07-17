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

// Confirm the blog actually exists — invalid/deleted IDs also redirect
$checkStmt = $pdo->prepare("SELECT id FROM blogs WHERE id = :id LIMIT 1");
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
    <title>MenuCrest — Edit Blog</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/style.css">
    <style>
        #contentEditor {
            background: var(--surface, #fff);
            min-height: 320px;
            border-radius: 0 0 var(--r-md, 8px) var(--r-md, 8px);
        }
        .ql-toolbar.ql-snow {
            border-radius: var(--r-md, 8px) var(--r-md, 8px) 0 0;
            background: var(--bg, #f8f8f8);
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

        .seo-count { font-weight: 600; }
        .seo-count.ok { color: #16A34A; }
        .seo-count.warn { color: #D97706; }
        .seo-count.over { color: #DC2626; }

        .serp-preview {
            background: var(--bg, #f8f8f8);
            border: 1px solid var(--border, #e5e5e5);
            border-radius: var(--r-md, 8px);
            padding: 14px 16px;
            margin-top: 10px;
        }
        .serp-title {
            color: #1a0dab;
            font-size: 1.05rem;
            line-height: 1.3;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .serp-url {
            color: #006621;
            font-size: .8rem;
            margin-bottom: 3px;
        }
        .serp-desc {
            color: #545454;
            font-size: .84rem;
            line-height: 1.4;
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
                    <h1 class="pg-title">Edit Blog</h1>
                    <p class="pg-desc">Update blog post details</p>
                </div>
                <a href="index.php" class="bo text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Back to Blogs
                </a>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="formContainer">
                    <div style="text-align:center;padding:40px;color:var(--muted);" id="loadingState">
                        <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                        <p style="margin-top:10px;">Loading blog details...</p>
                    </div>

                    <form id="editBlogForm" enctype="multipart/form-data" style="display:none;">
                        <input type="hidden" name="id" id="blogIdField" value="">

                        <!-- ===== BASIC INFO ===== -->
                        <div class="form-section-title"><i class="fas fa-info-circle"></i> Basic Information</div>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="fl">Title <span style="color:red">*</span></label>
                                <input type="text" class="fi" name="title" id="title" required maxlength="255" placeholder="e.g. 10 Best Fast Food Chains in 2026">
                                <div class="invalid-feedback" id="err_title"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="fl">Category</label>
                                <input type="text" class="fi" name="category" id="category" maxlength="100" placeholder="e.g. Food News, Reviews" list="categoryList">
                                <datalist id="categoryList"></datalist>
                                <div class="invalid-feedback" id="err_category"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Status</label>
                                <select class="fss" name="status" id="status">
                                    <option value="1">Published</option>
                                    <option value="0">Draft</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Publish Date</label>
                                <input type="datetime-local" class="fi" name="published_at" id="published_at">
                                <small style="color:var(--muted);font-size:.72rem;">Leave empty to publish immediately upon save.</small>
                                <div class="invalid-feedback" id="err_published_at"></div>
                            </div>

                            <div class="col-md-12">
                                <label class="fl">Excerpt</label>
                                <textarea class="fi" name="excerpt" id="excerpt" rows="3" maxlength="500" placeholder="A short summary shown on blog listing cards"></textarea>
                                <small style="color:var(--muted);font-size:.72rem;">Max 500 characters. Shown in blog cards/previews, not the full article.</small>
                                <div class="invalid-feedback" id="err_excerpt"></div>
                            </div>
                        </div>

                        <!-- ===== FEATURED IMAGE ===== -->
                        <div class="form-section-title"><i class="fas fa-image"></i> Featured Image</div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fl">Image</label>
                                <input type="file" class="fi" name="image" id="image" accept="image/png, image/jpeg, image/jpg, image/webp">
                                <small style="color:var(--muted);font-size:.72rem;">Leave empty to keep current image. Recommended 1200×630px. Max 2MB.</small>
                                <div class="invalid-feedback" id="err_image"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="fl">Preview</label>
                                <div>
                                    <img id="imagePreview" src="" alt="Preview"
                                         style="display:none;width:140px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                                </div>
                            </div>
                        </div>

                        <!-- ===== CONTENT (RICH TEXT) ===== -->
                        <div class="form-section-title"><i class="fas fa-pen-nib"></i> Blog Content</div>
                        <div id="contentEditor"></div>
                        <textarea name="content" id="content" style="display:none;"></textarea>
                        <div class="invalid-feedback" id="err_content"></div>

                        <!-- ===== SEO ===== -->
                        <div class="form-section-title"><i class="fas fa-magnifying-glass-chart"></i> SEO Settings</div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="fl">Meta Title</label>
                                <input type="text" class="fi" name="meta_title" id="meta_title" maxlength="255" placeholder="e.g. 10 Best Fast Food Chains in 2026 | MenuCrest">
                                <small style="color:var(--muted);font-size:.72rem;">
                                    <span id="metaTitleCount" class="seo-count">0</span>/60 recommended characters
                                </small>
                                <div class="invalid-feedback" id="err_meta_title"></div>
                            </div>

                            <div class="col-md-12">
                                <label class="fl">Meta Description</label>
                                <textarea class="fi" name="meta_description" id="meta_description" rows="3" maxlength="500" placeholder="A compelling 1-2 sentence summary that will appear under the title in search results"></textarea>
                                <small style="color:var(--muted);font-size:.72rem;">
                                    <span id="metaDescCount" class="seo-count">0</span>/160 recommended characters
                                </small>
                                <div class="invalid-feedback" id="err_meta_description"></div>
                            </div>

                            <div class="col-md-12">
                                <label class="fl">Search Result Preview</label>
                                <div class="serp-preview">
                                    <div class="serp-title" id="serpTitle">Your blog title will appear here</div>
                                    <div class="serp-url"><?= BASE_URL ?>/blog/<span id="serpSlug">your-post-slug</span></div>
                                    <div class="serp-desc" id="serpDesc">Your meta description will appear here.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mo-f" style="padding:0;margin-top:26px;border:none;display:flex;gap:10px;">
                            <button type="button" class="bo" onclick="window.location.href='index.php'">Cancel</button>
                            <button type="submit" class="ba" id="submitBtn">
                                <i class="fas fa-save"></i> Update Blog
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

    var blogId = <?= json_encode($id) ?>;
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

    // --- Quill rich text editor (content injected after AJAX load) ---
    var quill = new Quill('#contentEditor', {
        theme: 'snow',
        placeholder: 'Write your blog post...',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'link', 'image'],
                ['clean']
            ]
        }
    });
    quill.on('text-change', function () {
        $('#content').val(quill.root.innerHTML);
    });

    // --- SEO character counters + live SERP preview ---
    function updateSeoCounter($input, $counterEl, idealMax) {
        var len = $input.val().length;
        $counterEl.text(len);
        $counterEl.removeClass('ok warn over');
        if (len === 0) {
            // neutral
        } else if (len <= idealMax) {
            $counterEl.addClass('ok');
        } else if (len <= idealMax + 20) {
            $counterEl.addClass('warn');
        } else {
            $counterEl.addClass('over');
        }
    }

    function updateSerpPreview() {
        var title = $('#meta_title').val() || $('#title').val() || 'Your blog title will appear here';
        var desc = $('#meta_description').val() || $('#excerpt').val() || 'Your meta description will appear here.';
        var slug = ($('#title').val() || 'your-post-slug')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '') || 'your-post-slug';

        $('#serpTitle').text(title);
        $('#serpDesc').text(desc);
        $('#serpSlug').text(slug);
    }

    $('#meta_title').on('input', function () {
        updateSeoCounter($(this), $('#metaTitleCount'), 60);
        updateSerpPreview();
    });
    $('#meta_description').on('input', function () {
        updateSeoCounter($(this), $('#metaDescCount'), 160);
        updateSerpPreview();
    });
    $('#title, #excerpt').on('input', updateSerpPreview);

    // --- Featured image preview (only replaces preview if a NEW file is chosen) ---
    $('#image').on('change', function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) { $('#imagePreview').attr('src', e.target.result).show(); };
            reader.readAsDataURL(file);
        }
        // If cleared, keep showing the existing image — no reset needed
    });

    // --- Load category suggestions, then load the actual blog data ---
    $(function () {
        $.ajax({
            url: '../../api/blogs/get-form-data.php',
            type: 'GET',
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                var $list = $('#categoryList');
                $.each(res.categories, function (i, cat) {
                    $list.append('<option value="' + cat + '">');
                });
            }
        })
        .always(function () {
            loadBlog();
        });
    });

    function loadBlog() {
        $.ajax({
            url: '../../api/blogs/get-blog.php',
            type: 'GET',
            data: { id: blogId },
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                populateForm(res.data);
                $('#loadingState').hide();
                $('#editBlogForm').show();
            } else {
                toast(res.message || 'Blog not found', 'err');
                $('#loadingState').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                    '<i class="fas fa-triangle-exclamation" style="font-size:1.4rem;"></i>' +
                    '<p style="margin-top:10px;">' + (res.message || 'Blog not found') + '</p></div>');
            }
        })
        .fail(function () {
            toast('Failed to load blog details', 'err');
            $('#loadingState').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                '<p>Something went wrong while loading this blog.</p></div>');
        });
    }

    function populateForm(b) {
        $('#blogIdField').val(b.id);
        $('#title').val(b.title || '');
        $('#category').val(b.category || '');
        $('#status').val(b.status == 1 ? '1' : '0');
        $('#excerpt').val(b.excerpt || '');
        $('#meta_title').val(b.meta_title || '');
        $('#meta_description').val(b.meta_description || '');

        // --- published_at: convert "Y-m-d H:i:s" to datetime-local format "Y-m-dTH:i" ---
        if (b.published_at) {
            var dt = b.published_at.replace(' ', 'T').substring(0, 16);
            $('#published_at').val(dt);
        }

        // --- Content: seed Quill with existing HTML ---
        quill.root.innerHTML = b.content || '';
        $('#content').val(b.content || '');

        // --- Image preview ---
        if (b.image) {
            $('#imagePreview').attr('src', BASE_URL_JS + '/' + b.image).show();
        } else {
            $('#imagePreview').hide();
        }

        // --- SEO counters + SERP preview reflect loaded data ---
        updateSeoCounter($('#meta_title'), $('#metaTitleCount'), 60);
        updateSeoCounter($('#meta_description'), $('#metaDescCount'), 160);
        updateSerpPreview();
        if (b.slug) {
            $('#serpSlug').text(b.slug);
        }
    }

    $('#editBlogForm').on('submit', function (e) {
        e.preventDefault();

        $('.invalid-feedback').text('');

        $('#content').val(quill.root.innerHTML);
        if (quill.getText().trim() === '') {
            $('#content').val('');
        }

        var formData = new FormData(this);
        var $btn = $('#submitBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '../../api/blogs/update-blog.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                toast(res.message || 'Blog updated successfully', 'suc');
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
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Blog');
        });
    });
    </script>
</body>

</html>