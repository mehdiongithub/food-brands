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
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodScope — View Brand</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin/style.css">
    <style>
        .brand-cover {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: var(--r-md, 12px);
            border: 1px solid var(--border);
            margin-bottom: -50px;
        }
        .brand-logo-overlay {
            width: 96px;
            height: 96px;
            border-radius: 16px;
            object-fit: cover;
            border: 4px solid var(--surface);
            box-shadow: 0 4px 14px rgba(0,0,0,.1);
            background: var(--surface);
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
        .desc-view {
            background: var(--bg, #f8f8f8);
            border: 1px solid var(--border, #e5e5e5);
            border-radius: var(--r-md, 8px);
            padding: 14px 16px;
            font-size: .88rem;
            line-height: 1.6;
            color: var(--text2, #444);
        }
        .desc-view p { margin-bottom: .6em; }
        .desc-view ul, .desc-view ol { margin-left: 1.2em; margin-bottom: .6em; }
        .desc-view:empty::before { content: '—'; color: var(--muted); }
        .badge-pill {
            display: inline-block;
            margin: 3px;
            padding: 4px 12px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 99px;
            font-size: .78rem;
            font-weight: 500;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
        }
        .gallery-grid img {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border);
            cursor: pointer;
            transition: transform .2s ease;
        }
        .gallery-grid img:hover { transform: scale(1.03); }
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
                    <h1 class="pg-title">View Brand</h1>
                    <p class="pg-desc">Brand details</p>
                </div>
                <div style="display:flex;gap:10px;">
                    <?php if (currentUserRole() === 'admin'): ?>
                    <a href="edit-brand.php?token=<?= urlencode(encryptId($id)) ?>" class="ba text-decoration-none">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <?php endif; ?>
                    <a href="index.php" class="bo text-decoration-none">
                        <i class="fas fa-arrow-left"></i> Back to Brands
                    </a>
                </div>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="viewContainer">
                    <div style="text-align:center;padding:40px;color:var(--muted);">
                        <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                        <p style="margin-top:10px;">Loading brand details...</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="tw2" id="tw2"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/admin/sidebar.js"></script>
    <script>
    var brandId = <?= json_encode($id) ?>;
    var BASE_URL_JS = <?= json_encode(BASE_URL) ?>;

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return $('<div>').text(str).html();
    }

    $(function () {
        $.ajax({
            url: '../../api/brands/get-brand.php',
            type: 'GET',
            data: { id: brandId },
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                renderBrand(res.data);
            } else {
                toast(res.message || 'Brand not found', 'err');
                $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                    '<i class="fas fa-triangle-exclamation" style="font-size:1.4rem;"></i>' +
                    '<p style="margin-top:10px;">' + escapeHtml(res.message || 'Brand not found') + '</p></div>');
            }
        })
        .fail(function () {
            toast('Failed to load brand details', 'err');
            $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                '<p>Something went wrong while loading this brand.</p></div>');
        });
    });

    function renderBrand(b) {
        var coverHtml = b.cover_image
            ? "<img src='" + BASE_URL_JS + "/" + b.cover_image + "' class='brand-cover' alt='Cover'>"
            : "<div class='brand-cover' style='background:var(--bg);display:flex;align-items:center;justify-content:center;'><i class='fas fa-image' style='color:var(--muted);font-size:1.6rem;'></i></div>";

        var logoHtml = b.logo
            ? "<img src='" + BASE_URL_JS + "/" + b.logo + "' class='brand-logo-overlay' alt='" + escapeHtml(b.name) + "'>"
            : "<div class='brand-logo-overlay' style='display:flex;align-items:center;justify-content:center;background:#E85D04;color:#fff;font-weight:700;font-size:1.4rem;'>" + getInitialsJs(b.name) + "</div>";

        var statusBadge = (b.status == 1)
            ? '<span class="sb-badge2 active">Active</span>'
            : '<span class="sb-badge2 draft">Inactive</span>';

        var website = b.website
            ? "<a href='" + escapeHtml(b.website) + "' target='_blank' rel='noopener'>" + escapeHtml(b.website) + " <i class='fas fa-external-link-alt' style='font-size:.7rem;'></i></a>"
            : '—';

        var categoriesHtml = (b.categories && b.categories.length)
            ? b.categories.map(function (c) { return "<span class='badge-pill'>" + escapeHtml(c.name) + "</span>"; }).join('')
            : "<span style='color:var(--muted);font-size:.82rem;'>No categories assigned</span>";

        var countriesHtml = (b.countries && b.countries.length)
            ? b.countries.map(function (c) { return "<span class='badge-pill'>" + escapeHtml(c.name) + (c.code ? ' (' + c.code + ')' : '') + "</span>"; }).join('')
            : "<span style='color:var(--muted);font-size:.82rem;'>No countries assigned</span>";

        var galleryHtml = (b.gallery && b.gallery.length)
            ? "<div class='gallery-grid'>" + b.gallery.map(function (g) {
                var url = BASE_URL_JS + '/' + g.image;
                return "<img src='" + url + "' alt='Gallery image' onclick='window.open(\"" + url + "\", \"_blank\")'>";
            }).join('') + "</div>"
            : "<span style='color:var(--muted);font-size:.82rem;'>No gallery images uploaded</span>";

        var historyHtml = b.history || "<span style='color:var(--muted);'>No history provided</span>";
        var shortDescHtml = b.short_description ? escapeHtml(b.short_description) : '—';

        var html = '' +
            // --- Cover + Logo header ---
            coverHtml +
            '<div style="padding-left:14px;margin-bottom:20px;">' + logoHtml + '</div>' +

            '<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:6px;">' +
                '<div style="font-size:1.3rem;font-weight:800;">' + escapeHtml(b.name) + '</div>' +
                statusBadge +
            '</div>' +
            '<div style="color:var(--muted);font-size:.85rem;margin-bottom:20px;">' + escapeHtml(b.slug || '—') + '</div>' +

            // --- Basic Info ---
            '<div class="form-section-title"><i class="fas fa-info-circle"></i> Basic Information</div>' +
            '<div class="row g-3">' +
                field('Founded Year', b.founded_year || '—') +
                fieldHtml('Website', website) +
                fieldHtml('Created By', escapeHtml(b.created_by_name || 'Unknown')) +
                field('Created At', b.created_at || '—') +
                field('Last Updated', b.updated_at || '—') +
            '</div>' +

            '<div class="form-section-title"><i class="fas fa-align-left"></i> Short Description</div>' +
            '<div class="desc-view">' + shortDescHtml + '</div>' +

            '<div class="form-section-title"><i class="fas fa-book-open"></i> Brand History</div>' +
            '<div class="desc-view">' + historyHtml + '</div>' +

            // --- Categories & Countries ---
            '<div class="form-section-title"><i class="fas fa-diagram-project"></i> Categories</div>' +
            '<div style="margin-bottom:10px;">' + categoriesHtml + '</div>' +

            '<div class="form-section-title"><i class="fas fa-globe-americas"></i> Available Countries</div>' +
            '<div style="margin-bottom:10px;">' + countriesHtml + '</div>' +

            // --- Gallery ---
            '<div class="form-section-title"><i class="fas fa-images"></i> Gallery</div>' +
            galleryHtml +

            // --- SEO ---
            '<div class="form-section-title"><i class="fas fa-magnifying-glass-chart"></i> SEO Settings</div>' +
            '<div class="row g-3">' +
                field('Meta Title', b.meta_title || '—') +
            '</div>' +
            '<div style="margin-top:12px;">' +
                '<label class="fl">Meta Description</label>' +
                '<div class="desc-view">' + (b.meta_description ? escapeHtml(b.meta_description) : '') + '</div>' +
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

    function getInitialsJs(name) {
        if (!name) return '?';
        var parts = name.trim().split(/\s+/);
        if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }
    </script>
</body>

</html>