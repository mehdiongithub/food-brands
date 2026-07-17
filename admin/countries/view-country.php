<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin(); // both admin and employee can view

$token = $_GET['token'] ?? '';
$id = decryptId($token);

if ($id === false || $id <= 0) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

// Confirm the category actually exists — invalid/deleted IDs also redirect
$checkStmt = $pdo->prepare("SELECT id FROM countries WHERE id = :id LIMIT 1");
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
    <title>MenuCrest — View Country</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin/style.css">
</head>

<body>

    <div class="sb-bd" id="sbBd" onclick="closeMS()"></div>

    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/header.php'; ?>

    <div id="main">
        <div class="pg-content" id="pgC">

            <div class="pg-head" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
                <div>
                    <h1 class="pg-title">View Country</h1>
                    <p class="pg-desc">Country details</p>
                </div>
                <div style="display:flex;gap:10px;">
                    <?php 
                        if($isAdmin = (currentUserRole() === 'admin')){
                    ?>
                    <a href="edit-country.php?token=<?= urlencode(encryptId($id)) ?>" class="ba text-decoration-none">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <?php
                    }
                    ?>
                    <a href="index.php" class="bo text-decoration-none">
                        <i class="fas fa-arrow-left"></i> Back to Countries
                    </a>
                </div>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="viewContainer">
                    <div style="text-align:center;padding:40px;color:var(--muted);">
                        <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                        <p style="margin-top:10px;">Loading country details...</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="tw2" id="tw2"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/admin/sidebar.js"></script>
    <script>
    var countryId = <?= json_encode($id) ?>;
    var BASE_URL_JS = <?= json_encode(BASE_URL) ?>;

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return $('<div>').text(str).html();
    }

    $(function () {
        $.ajax({
            url: '../../api/countries/get-country.php',
            type: 'GET',
            data: { id: countryId },
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                renderCountry(res.data);
            } else {
                toast(res.message || 'Country not found', 'err');
                $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                    '<i class="fas fa-triangle-exclamation" style="font-size:1.4rem;"></i>' +
                    '<p style="margin-top:10px;">' + escapeHtml(res.message || 'Country not found') + '</p></div>');
            }
        })
        .fail(function () {
            toast('Failed to load country details', 'err');
            $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                '<p>Something went wrong while loading this country.</p></div>');
        });
    });

    function renderCountry(c) {
        var flagHtml;
        if (c.flag) {
            flagHtml = "<img src='" + BASE_URL_JS + "/" + c.flag + "' alt='" + escapeHtml(c.name) + "' style='width:64px;height:46px;object-fit:cover;border-radius:6px;border:1px solid var(--border);'>";
        } else if (c.code) {
            flagHtml = "<img src='https://flagcdn.com/w80/" + c.code.toLowerCase() + ".png' alt='" + escapeHtml(c.name) + "' style='width:64px;height:46px;object-fit:cover;border-radius:6px;border:1px solid var(--border);'>";
        } else {
            flagHtml = "<div style='width:64px;height:46px;border-radius:6px;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;'><i class='fas fa-globe' style='color:var(--muted);'></i></div>";
        }

        var statusBadge = (c.status == 1)
            ? '<span class="sb-badge2 active">Active</span>'
            : '<span class="sb-badge2 draft">Inactive</span>';

        var created = c.created_at || '—';
        var updated = c.updated_at || '—';

        var html = '' +
            '<div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;">' +
                flagHtml +
                '<div><div style="font-size:1.05rem;font-weight:700;">' + escapeHtml(c.name) + '</div>' +
                '<div style="color:var(--muted);font-size:.85rem;">' + escapeHtml(c.slug || '—') + '</div></div>' +
            '</div>' +

            '<div class="row g-3">' +
                field('Country Name', c.name) +
                field('Country Code', c.code) +
                field('Currency', c.currency) +
                field('Currency Symbol', c.currency_symbol) +
                field('Slug', c.slug || '—') +
                fieldHtml('Status', statusBadge) +
                field('Created At', created) +
                field('Last Updated', updated) +
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