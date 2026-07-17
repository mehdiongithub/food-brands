<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin(); // both admin and employee can view

$token = $_GET['token'] ?? '';
$id = decryptId($token);

if ($id === false || $id <= 0) {
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
    <title>MenuCrest — View Blog</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin/style.css">
    <style>
        .desc-view {
            background: var(--bg, #f8f8f8);
            border: 1px solid var(--border, #e5e5e5);
            border-radius: var(--r-md, 8px);
            padding: 14px 16px;
            font-size: .88rem;
            line-height: 1.6;
            color: var(--text2, #444);
        }
        .desc-view p { margin-bottom: .8em; }
        .desc-view ul, .desc-view ol { margin-left: 1.2em; margin-bottom: .8em; }
        .desc-view img { max-width: 100%; border-radius: 8px; margin: 8px 0; }
        .desc-view:empty::before { content: '—'; color: var(--muted); }

        .featured-image-view {
            width: 100%;
            max-width: 480px;
            height: 220px;
            object-fit: cover;
            border-radius: var(--r-md, 8px);
            border: 1px solid var(--border);
        }

        .serp-preview {
            background: var(--bg, #f8f8f8);
            border: 1px solid var(--border, #e5e5e5);
            border-radius: var(--r-md, 8px);
            padding: 14px 16px;
        }
        .serp-title {
            color: #1a0dab;
            font-size: 1.05rem;
            line-height: 1.3;
            margin-bottom: 3px;
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

        .stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--bg, #f2f2f2);
            border: 1px solid var(--border, #e5e5e5);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
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
                    <h1 class="pg-title">View Blog</h1>
                    <p class="pg-desc">Blog post details</p>
                </div>
                <div style="display:flex;gap:10px;">
                    <?php if (currentUserRole() === 'admin'): ?>
                    <a href="edit-blog.php?token=<?= urlencode(encryptId($id)) ?>" class="ba text-decoration-none">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <?php endif; ?>
                    <a href="index.php" class="bo text-decoration-none">
                        <i class="fas fa-arrow-left"></i> Back to Blogs
                    </a>
                </div>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="viewContainer">
                    <div style="text-align:center;padding:40px;color:var(--muted);">
                        <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                        <p style="margin-top:10px;">Loading blog details...</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="tw2" id="tw2"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/admin/sidebar.js"></script>
    <script>
    var blogId = <?= json_encode($id) ?>;
    var BASE_URL_JS = <?= json_encode(BASE_URL) ?>;

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return $('<div>').text(str).html();
    }

    $(function () {
        $.ajax({
            url: '../../api/blogs/get-blog.php',
            type: 'GET',
            data: { id: blogId },
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                renderBlog(res.data);
            } else {
                toast(res.message || 'Blog not found', 'err');
                $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                    '<i class="fas fa-triangle-exclamation" style="font-size:1.4rem;"></i>' +
                    '<p style="margin-top:10px;">' + escapeHtml(res.message || 'Blog not found') + '</p></div>');
            }
        })
        .fail(function () {
            toast('Failed to load blog details', 'err');
            $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                '<p>Something went wrong while loading this blog.</p></div>');
        });
    });

    function renderBlog(b) {
        var imageHtml;
        if (b.image) {
            imageHtml = "<img src='" + BASE_URL_JS + "/" + b.image + "' alt='" + escapeHtml(b.title) + "' class='featured-image-view'>";
        } else {
            imageHtml = "<div class='featured-image-view' style='background:var(--bg);display:flex;align-items:center;justify-content:center;'><i class='fas fa-image' style='color:var(--muted);font-size:1.8rem;'></i></div>";
        }

        var statusBadge = (b.status == 1)
            ? '<span class="sb-badge2 active">Published</span>'
            : '<span class="sb-badge2 draft">Draft</span>';

        var publishedAt = b.published_at || 'Not scheduled';
        var created = b.created_at || '—';
        var updated = b.updated_at || '—';
        var category = b.category || '—';
        var author = b.author_name || 'Unknown';

        // content is trusted, already-sanitized HTML stored by add-blog.php —
        // rendered directly so formatting (headings, lists, images, etc.) displays correctly
        var contentHtml = b.content || '';
        var excerptText = b.excerpt || '';

        var metaTitle = b.meta_title || b.title;
        var metaDesc = b.meta_description || b.excerpt || '';

        var html = '' +
            '<div style="margin-bottom:24px;">' + imageHtml + '</div>' +

            '<div style="margin-bottom:20px;">' +
                '<div style="font-size:1.2rem;font-weight:700;margin-bottom:6px;">' + escapeHtml(b.title) + '</div>' +
                '<div style="display:flex;flex-wrap:wrap;gap:8px;">' +
                    '<span class="stat-badge"><i class="fas fa-folder"></i> ' + escapeHtml(category) + '</span>' +
                    '<span class="stat-badge"><i class="fas fa-user"></i> ' + escapeHtml(author) + '</span>' +
                    '<span class="stat-badge"><i class="fas fa-eye"></i> ' + (b.views || 0) + ' views</span>' +
                '</div>' +
            '</div>' +

            '<div class="row g-3">' +
                field('Title', b.title) +
                field('Slug', b.slug || '—') +
                field('Category', category) +
                fieldHtml('Status', statusBadge) +
                field('Published At', publishedAt) +
                field('Author', author) +
                field('Views', b.views || 0) +
                field('Created At', created) +
                field('Last Updated', updated) +
            '</div>' +

            '<div class="row g-3" style="margin-top:14px;">' +
                '<div class="col-12">' +
                    '<label class="fl">Excerpt</label>' +
                    '<div class="desc-view">' + (excerptText ? escapeHtml(excerptText) : '') + '</div>' +
                '</div>' +
            '</div>' +

            '<div class="row g-3" style="margin-top:14px;">' +
                '<div class="col-12">' +
                    '<label class="fl">Content</label>' +
                    '<div class="desc-view">' + contentHtml + '</div>' +
                '</div>' +
            '</div>' +

            '<div class="row g-3" style="margin-top:14px;">' +
                '<div class="col-12">' +
                    '<label class="fl">Search Result Preview</label>' +
                    '<div class="serp-preview">' +
                        '<div class="serp-title">' + escapeHtml(metaTitle) + '</div>' +
                        '<div class="serp-url">' + BASE_URL_JS + '/blog/' + escapeHtml(b.slug || '') + '</div>' +
                        '<div class="serp-desc">' + escapeHtml(metaDesc) + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

        $('#viewContainer').html(html);
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