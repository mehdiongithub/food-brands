<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodScope — Admin Add FAQ</title>
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
                    <h1 class="pg-title">Add FAQ</h1>
                    <p class="pg-desc">Create a new FAQ</p>
                </div>
                <a href="index.php" class="bo text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Back to FAQs
                </a>
            </div>

            <div class="cd">
                <div class="cd-b p-4">

                    <div id="formAlert" style="display:none;" class="alert" role="alert"></div>

                    <form id="addFaqForm" enctype="multipart/form-data">

                        <div class="row g-3">

                            <div class="col-md-12">
                                <label class="fl">Question <span style="color:red">*</span></label>
                                <input type="text" class="fi" name="question" id="question" required maxlength="255">
                                <div class="invalid-feedback" id="err_question"></div>
                            </div>

                            <div class="col-md-12">
                                <label class="fl">Answer <span style="color:red">*</span></label>
                                <textarea class="fi" name="answer" id="answer" required maxlength="255" rows="4"></textarea>
                                <div class="invalid-feedback" id="err_answer"></div>
                            </div>
                            <div class="col-md-12">
                                <label class="fl">Sort Order</label>
                                <input type="number" class="fi" name="sort_order" id="sort_order" min="0">
                                <div class="invalid-feedback" id="err_sort_order"></div>
                            </div>
                           

                           
                            <div class="col-md-12">
                                <label class="fl">Status</label>
                                <select class="fss" name="status" id="status">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                           
                        </div>

                        <div class="mo-f" style="padding:0;margin-top:22px;border:none;display:flex;gap:10px;">
                            <button type="button" class="bo" onclick="window.location.href='index.php'">Cancel</button>
                            <button type="submit" class="ba" id="submitBtn">
                                <i class="fas fa-save"></i> Create FAQ
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
<script>
// --- Toast helper (matches your dashboard's original toast system) ---
function toast(m, t) {
    t = t || 'suc';
    var ic = { suc: 'fa-check-circle', err: 'fa-times-circle', wrn: 'fa-exclamation-triangle', inf: 'fa-info-circle' };
    var $t = $('<div class="ti2 ' + t + '"><i class="fas ' + ic[t] + '"></i><span>' + m + '</span></div>');
    $('#tw2').append($t);
    setTimeout(function () {
        $t.fadeOut(250, function () { $(this).remove(); });
    }, 2800);
}

// Live image preview before upload
$('#image').on('change', function () {
    var file = this.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#imagePreview').attr('src', e.target.result).show();
        };
        reader.readAsDataURL(file);
    } else {
        $('#imagePreview').hide();
    }
});

$('#addFaqForm').on('submit', function (e) {
    e.preventDefault();

    // Clear previous field errors
    $('.invalid-feedback').text('');

    var formData = new FormData(this);
    var $btn = $('#submitBtn');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    $.ajax({
        url: '../../api/faqs/add-faq.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json'
    })
    .done(function (res) {
        if (res.success) {
            toast(res.message || 'FAQ created successfully', 'suc');
            setTimeout(function () {
                window.location.href = 'index.php';
            }, 2000);
        } else {
            // Field-specific validation errors stay inline
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
        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Create FAQ');
    });
});
</script>
</body>

</html>