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
    <title>FoodScope — View FAQ</title>
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
                    <h1 class="pg-title">Manage FAQ</h1>
                    <p class="pg-desc">FAQ details and modifications</p>
                </div>
                 <div style="display:flex;gap:10px;">
                    <?php if (currentUserRole() === 'admin'): ?>
                    <a href="edit-faq.php?token=<?= urlencode(encryptId($id)) ?>" class="ba text-decoration-none">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <?php endif; ?>
                    <a href="index.php" class="bo text-decoration-none">
                        <i class="fas fa-arrow-left"></i> Back to Categories
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/admin/sidebar.js"></script>
    <script>
    var faqId = <?= json_encode($id) ?>;
    var BASE_URL_JS = <?= json_encode(BASE_URL) ?>;

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return $('<div>').text(str).html();
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
                    '<input readonly type="text" class="fi" name="question" id="question" required maxlength="255" value="' + escapeHtml(f.question) + '">' +
                    '<div class="invalid-feedback" id="err_question"></div>' +
                '</div>' +
                '<div class="col-md-12">' +
                    '<label class="fl">Answer <span style="color:red">*</span></label>' +
                    '<textarea readonly class="fi" name="answer" id="answer" required maxlength="255" rows="4">' + escapeHtml(f.answer) + '</textarea>' +
                    '<div class="invalid-feedback" id="err_answer"></div>' +
                '</div>' +
                '<div class="col-md-12">' +
                    '<label class="fl">Sort Order</label>' +
                    '<input readonly type="number" class="fi" name="sort_order" id="sort_order" min="0" value="' + sortOrderVal + '">' +
                    '<div class="invalid-feedback" id="err_sort_order"></div>' +
                '</div>' +
                '<div class="col-md-12">' +
                    '<label class="fl">Status</label>' +
                    '<select readonly class="fss" name="status" id="status">' +
                        '<option value="1" ' + statusActiveSelected + '>Active</option>' +
                        '<option value="0" ' + statusInactiveSelected + '>Inactive</option>' +
                    '</select>' +
                '</div>' +
            '</div>' +
            '<div class="mo-f" style="padding:0;margin-top:22px;border:none;display:flex;gap:10px;">' +
                '<button type="button" class="bo" onclick="window.location.href=\'index.php\'">Cancel</button>' +
            '</div>' +
        '</form>';

        // Inject the generated form straight into the main block container
        $('#viewContainer').html(html);
    }
    </script>
</body>

</html>