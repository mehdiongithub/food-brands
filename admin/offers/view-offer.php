<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin(); // both admin and employee can view

$token = $_GET['token'] ?? '';
$id = decryptId($token);

if ($id === false || $id <= 0) {
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
    <title>MenuCrest — View Offer</title>
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
        .desc-view p { margin-bottom: .6em; }
        .desc-view ul, .desc-view ol { margin-left: 1.2em; margin-bottom: .6em; }
        .desc-view:empty::before { content: '—'; color: var(--muted); }

        .country-chip-view {
            display: inline-flex;
            align-items: center;
            background: var(--bg, #f2f2f2);
            border: 1px solid var(--border, #e5e5e5);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: .78rem;
            margin: 0 6px 6px 0;
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
                    <h1 class="pg-title">View Offer</h1>
                    <p class="pg-desc">Offer details</p>
                </div>
                <div style="display:flex;gap:10px;">
                    <?php if (currentUserRole() === 'admin'): ?>
                    <a href="edit-offer.php?token=<?= urlencode(encryptId($id)) ?>" class="ba text-decoration-none">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <?php endif; ?>
                    <a href="index.php" class="bo text-decoration-none">
                        <i class="fas fa-arrow-left"></i> Back to Offers
                    </a>
                </div>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="viewContainer">
                    <div style="text-align:center;padding:40px;color:var(--muted);">
                        <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                        <p style="margin-top:10px;">Loading offer details...</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="tw2" id="tw2"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/admin/sidebar.js"></script>
    <script>
    var offerId = <?= json_encode($id) ?>;
    var BASE_URL_JS = <?= json_encode(BASE_URL) ?>;

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return $('<div>').text(str).html();
    }

    $(function () {
        $.ajax({
            url: '../../api/offers/get-offer.php',
            type: 'GET',
            data: { id: offerId },
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                renderOffer(res.data);
            } else {
                toast(res.message || 'Offer not found', 'err');
                $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                    '<i class="fas fa-triangle-exclamation" style="font-size:1.4rem;"></i>' +
                    '<p style="margin-top:10px;">' + escapeHtml(res.message || 'Offer not found') + '</p></div>');
            }
        })
        .fail(function () {
            toast('Failed to load offer details', 'err');
            $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                '<p>Something went wrong while loading this offer.</p></div>');
        });
    });

    function renderOffer(o) {
        var imageHtml;
        if (o.image) {
            imageHtml = "<img src='" + BASE_URL_JS + "/" + o.image + "' alt='" + escapeHtml(o.title) + "' style='width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid var(--border);'>";
        } else {
            imageHtml = "<div style='width:72px;height:72px;border-radius:8px;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;'><i class='fas fa-image' style='color:var(--muted);'></i></div>";
        }

        var statusBadge = (o.status == 1)
            ? '<span class="sb-badge2 active">Active</span>'
            : '<span class="sb-badge2 draft">Inactive</span>';

        var today = new Date().toISOString().slice(0, 10);
        if (o.end_date && o.end_date < today) {
            statusBadge += ' <span class="sb-badge2 draft">Expired</span>';
        }

        var discount = (o.discount_percent !== null && o.discount_percent !== '')
            ? parseFloat(o.discount_percent).toFixed(2) + '%'
            : '—';

        var coupon = o.coupon_code ? o.coupon_code : '—';

        var startDate = o.start_date || '—';
        var endDate = o.end_date || '—';

        var countriesHtml = '—';
        if (o.countries && o.countries.length > 0) {
            countriesHtml = o.countries.map(function (c) {
                return '<span class="country-chip-view">' + escapeHtml(c.name) + '</span>';
            }).join('');
        }

        var created = o.created_at || '—';
        var updated = o.updated_at || '—';

        // description is trusted, already-sanitized HTML stored by add-offer.php —
        // rendered directly so formatting (bold, lists, etc.) displays correctly
        var descriptionHtml = o.description || '';

        var html = '' +
            '<div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;">' +
                imageHtml +
                '<div><div style="font-size:1.05rem;font-weight:700;">' + escapeHtml(o.title) + '</div>' +
                '<div style="color:var(--muted);font-size:.85rem;">' + escapeHtml(o.brand_name || '—') + '</div></div>' +
            '</div>' +

            '<div class="row g-3">' +
                field('Title', o.title) +
                field('Brand', o.brand_name || '—') +
                field('Slug', o.slug || '—') +
                field('Discount', discount) +
                field('Coupon Code', coupon) +
                fieldHtml('Status', statusBadge) +
                field('Start Date', startDate) +
                field('End Date', endDate) +
                field('Created At', created) +
                field('Last Updated', updated) +
            '</div>' +

            '<div class="row g-3" style="margin-top:14px;">' +
                '<div class="col-12">' +
                    '<label class="fl">Countries</label>' +
                    '<div style="padding:8px 0;">' + countriesHtml + '</div>' +
                '</div>' +
            '</div>' +

            '<div class="row g-3" style="margin-top:14px;">' +
                '<div class="col-12">' +
                    '<label class="fl">Description</label>' +
                    '<div class="desc-view">' + descriptionHtml + '</div>' +
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