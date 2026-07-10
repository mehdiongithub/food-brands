<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodScope — Admin Add User</title>
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
                    <h1 class="pg-title">Add User</h1>
                    <p class="pg-desc">Create a new user account</p>
                </div>
                <a href="index.php" class="bo text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>

            <div class="cd">
                <div class="cd-b p-4">

                    <div id="formAlert" style="display:none;" class="alert" role="alert"></div>

                    <form id="addUserForm" enctype="multipart/form-data">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="fl">Full Name <span style="color:red">*</span></label>
                                <input type="text" class="fi" name="name" id="name" required maxlength="100">
                                <div class="invalid-feedback" id="err_name"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Email <span style="color:red">*</span></label>
                                <input type="email" class="fi" name="email" id="email" required maxlength="150">
                                <div class="invalid-feedback" id="err_email"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Password <span style="color:red">*</span></label>
                                <input type="password" class="fi" name="password" id="password" required minlength="6">
                                <div class="invalid-feedback" id="err_password"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Phone</label>
                                <input type="text" class="fi" name="phone" id="phone" maxlength="20">
                                <div class="invalid-feedback" id="err_phone"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Role <span style="color:red">*</span></label>
                                <select class="fss" name="role" id="role" required>
                                    <option value="guest" selected>Guest</option>
                                    <option value="employee">Employee</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <div class="invalid-feedback" id="err_role"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Status</label>
                                <select class="fss" name="status" id="status">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Profile Image</label>
                                <input type="file" class="fi" name="image" id="image" accept="image/png, image/jpeg, image/jpg, image/webp">
                                <small style="color:var(--muted);font-size:.72rem;">JPG, PNG or WEBP. Max 2MB.</small>
                                <div class="invalid-feedback" id="err_image"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Image Preview</label>
                                <div>
                                    <img id="imagePreview" src="" alt="Preview"
                                         style="display:none;width:70px;height:70px;border-radius:50%;object-fit:cover;border:1px solid var(--border);">
                                </div>
                            </div>

                        </div>

                        <div class="mo-f" style="padding:0;margin-top:22px;border:none;display:flex;gap:10px;">
                            <button type="button" class="bo" onclick="window.location.href='index.php'">Cancel</button>
                            <button type="submit" class="ba" id="submitBtn">
                                <i class="fas fa-save"></i> Create User
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

$('#addUserForm').on('submit', function (e) {
    e.preventDefault();

    // Clear previous field errors
    $('.invalid-feedback').text('');

    var formData = new FormData(this);
    var $btn = $('#submitBtn');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

    $.ajax({
        url: '../../api/users/add-user.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json'
    })
    .done(function (res) {
        if (res.success) {
            toast(res.message || 'User created successfully', 'suc');
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
        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Create User');
    });
});
</script>
</body>

</html>