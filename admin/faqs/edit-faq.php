<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin(); // both admin and employee can view

$token = $_GET['token'] ?? '';
$id = decryptId($token);

if ($id === false || $id <= 0) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

// Confirm the FAQ actually exists — invalid/deleted IDs also redirect
$checkStmt = $pdo->prepare("SELECT id FROM faqs WHERE id = :id LIMIT 1");
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
    <title>MenuCrest — View FAQ</title>
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
                    <h1 class="pg-title">Manage FAQ</h1>
                    <p class="pg-desc">FAQ details and modifications</p>
                </div>
                <div style="display:flex;gap:10px;">
                    <a href="index.php" class="bo text-decoration-none">
                        <i class="fas fa-arrow-left"></i> Back to FAQs
                    </a>
                </div>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="viewContainer">
                    <div id="loadingSpinner" style="text-align:center;padding:40px;color:var(--muted);">
                        <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                        <p style="margin-top:10px;">Loading FAQ details...</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="tw2" id="tw2"></div>
    <div id="formAlert" style="display:none;" class="alert" role="alert"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/admin/sidebar.js"></script>
    <script>
    var faqId = <?= json_encode($id) ?>;
    var BASE_URL_JS = <?= json_encode(BASE_URL) ?>;

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return $('<div>').text(str).html();
    }

    function toast(m, t) {
        t = t || 'suc';
        var ic = { suc: 'fa-check-circle', err: 'fa-times-circle', wrn: 'fa-exclamation-triangle', inf: 'fa-info-circle' };
        var $t = $('<div class="ti2 ' + t + '"><i class="fas ' + ic[t] + '"></i><span>' + m + '</span></div>');
        $('#tw2').append($t);
        setTimeout(function () {
            $t.fadeOut(250, function () { $(this).remove(); });
        }, 2800);
    }

    $(function () {
        // 1. Fetch data
        $.ajax({
            url: '../../api/faqs/get-faq.php',
            type: 'GET',
            data: { id: faqId },
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                // Generate and mount the form HTML template first, then fill it
                renderFaqFormLayout(res.data);
            } else {
                toast(res.message || 'FAQ not found', 'err');
                $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                    '<i class="fas fa-triangle-exclamation" style="font-size:1.4rem;"></i>' +
                    '<p style="margin-top:10px;">' + escapeHtml(res.message) + '</p></div>');
            }
        })
        .fail(function () {
            toast('Failed to load FAQ details', 'err');
            $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                '<p>Something went wrong while loading this FAQ.</p></div>');
        });

        // 2. Handle Form Submit (Using event delegation since form is loaded dynamically)
        // 2. Handle Form Submit (Using event delegation since form is loaded dynamically)
        $(document).on('submit', '#addFaqForm', function (e) {
            e.preventDefault();
            
            $('.invalid-feedback').text('').hide();
            $('.fi, .fss').removeClass('is-invalid');
            
            var $submitBtn = $('#submitBtn');
            var originalBtnHtml = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            var formData = new FormData(this);
            formData.append('id', faqId);

            $.ajax({
                url: '../../api/faqs/update-faq.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            })
            .done(function (res) {
                if (res.success) {
                    toast(res.message || 'FAQ updated successfully', 'suc');
                    setTimeout(function () {
                        window.location.href = 'index.php';
                    }, 1200);
                } else {
                    if (res.errors) {
                        $.each(res.errors, function (field, msg) {
                            $('#err_' + field).text(msg).show(); // Added .show() to make the error visible
                            $('#' + field).addClass('is-invalid');
                        });
                    }
                    toast(res.message || 'Please fix the errors below', 'err');
                }
            })
            .fail(function () {
                toast('Something went wrong. Please try again.', 'err');
            })
            .always(function () {
                // FIX: Changed $btn to $submitBtn to match the declared variable
                $submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Update FAQ');
            });
        });
    });

    // This renders the form template directly inside viewContainer replacing the spinner
    function renderFaqFormLayout(f) {
        var statusActiveSelected = (f.status == 1) ? 'selected' : '';
        var statusInactiveSelected = (f.status == 0) ? 'selected' : '';
        var sortOrderVal = (f.sort_order !== undefined && f.sort_order !== null) ? f.sort_order : 0;

        var html = '' +
        '<form id="addFaqForm" enctype="multipart/form-data">' +
            '<div class="row g-3">' +
                '<div class="col-md-12">' +
                    '<label class="fl">Question <span style="color:red">*</span></label>' +
                    '<input type="text" class="fi" name="question" id="question" required maxlength="255" value="' + escapeHtml(f.question) + '">' +
                    '<div class="invalid-feedback" id="err_question"></div>' +
                '</div>' +
                '<div class="col-md-12">' +
                    '<label class="fl">Answer <span style="color:red">*</span></label>' +
                    '<textarea class="fi" name="answer" id="answer" required maxlength="255" rows="4">' + escapeHtml(f.answer) + '</textarea>' +
                    '<div class="invalid-feedback" id="err_answer"></div>' +
                '</div>' +
                '<div class="col-md-12">' +
                    '<label class="fl">Sort Order</label>' +
                    '<input type="number" class="fi" name="sort_order" id="sort_order" min="0" value="' + sortOrderVal + '">' +
                    '<div class="invalid-feedback" id="err_sort_order"></div>' +
                '</div>' +
                '<div class="col-md-12">' +
                    '<label class="fl">Status</label>' +
                    '<select class="fss" name="status" id="status">' +
                        '<option value="1" ' + statusActiveSelected + '>Active</option>' +
                        '<option value="0" ' + statusInactiveSelected + '>Inactive</option>' +
                    '</select>' +
                '</div>' +
            '</div>' +
            '<div class="mo-f" style="padding:0;margin-top:22px;border:none;display:flex;gap:10px;">' +
                '<button type="button" class="bo" onclick="window.location.href=\'index.php\'">Cancel</button>' +
                '<button type="submit" class="ba" id="submitBtn">' +
                    '<i class="fas fa-save"></i> Update FAQ' +
                '</button>' +
            '</div>' +
        '</form>';

        // Inject the generated form straight into the main block container
        $('#viewContainer').html(html);
    }
    </script>
</body>

</html>