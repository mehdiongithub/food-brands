<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodScope — Edit User</title>
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
                    <h1 class="pg-title">Edit User</h1>
                    <p class="pg-desc">Update user account details</p>
                </div>
                <a href="index.php" class="bo text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="formWrapper" style="display:none;">

                    <form id="editUserForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="userId" value="<?= $id ?>">
                        <input type="hidden" name="existing_image" id="existingImage" value="">

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
                                <label class="fl">New Password</label>
                                <input type="password" class="fi" name="password" id="password" minlength="6" placeholder="Leave blank to keep current password">
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
                                    <option value="guest">Guest</option>
                                    <option value="employee">Employee</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <div class="invalid-feedback" id="err_role"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Status</label>
                                <select class="fss" name="status" id="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Profile Image</label>
                                <input type="file" class="fi" name="image" id="image" accept="image/png, image/jpeg, image/jpg, image/webp">
                                <small style="color:var(--muted);font-size:.72rem;">Leave empty to keep current image. JPG, PNG or WEBP. Max 2MB.</small>
                                <div class="invalid-feedback" id="err_image"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Current / Preview Image</label>
                                <div>
                                    <img id="imagePreview" src="" alt="Preview"
                                         style="display:none;width:70px;height:70px;border-radius:50%;object-fit:cover;border:1px solid var(--border);">
                                    <div id="noImageBox" style="display:none;width:70px;height:70px;border-radius:50%;background:#E85D04;color:#fff;align-items:center;justify-content:center;font-weight:700;font-size:1.1rem;"></div>
                                </div>
                            </div>

                        </div>

                        <div class="mo-f" style="padding:0;margin-top:22px;border:none;display:flex;gap:10px;">
                            <button type="button" class="bo" onclick="window.location.href='index.php'">Cancel</button>
                            <button type="submit" class="ba" id="submitBtn">
                                <i class="fas fa-save"></i> Update User
                            </button>
                        </div>

                    </form>

                </div>

                <div id="loadingBox" style="text-align:center;padding:40px;color:var(--muted);">
                    <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                    <p style="margin-top:10px;">Loading user details...</p>
                </div>

            </div>

        </div>
    </div>

    <div class="tw2" id="tw2"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/admin/sidebar.js"></script>
    <script>
    function toast(m, t) {
        t = t || 'suc';
        var ic = { suc: 'fa-check-circle', err: 'fa-times-circle', wrn: 'fa-exclamation-triangle', inf: 'fa-info-circle' };
        var $t = $('<div class="ti2 ' + t + '"><i class="fas ' + ic[t] + '"></i><span>' + m + '</span></div>');
        $('#tw2').append($t);
        setTimeout(function () {
            $t.fadeOut(250, function () { $(this).remove(); });
        }, 2800);
    }

    function getInitials(name) {
        if (!name) return '?';
        var parts = name.trim().split(/\s+/);
        if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }

    var userId = $('#userId').val();

    // --- Load existing user data ---
    $(function () {
        $.ajax({
            url: '../../api/users/get-user.php',
            type: 'GET',
            data: { id: userId },
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                populateForm(res.data);
                $('#loadingBox').hide();
                $('#formWrapper').show();
            } else {
                toast(res.message || 'User not found', 'err');
                $('#loadingBox').html('<p>' + (res.message || 'User not found') + '</p>');
            }
        })
        .fail(function () {
            toast('Failed to load user details', 'err');
            $('#loadingBox').html('<p>Something went wrong while loading this user.</p>');
        });
    });

    function populateForm(u) {
        $('#name').val(u.name);
        $('#email').val(u.email);
        $('#phone').val(u.phone || '');
        $('#role').val(u.role);
        $('#status').val(u.status);
        $('#existingImage').val(u.image || '');

        if (u.image) {
            $('#imagePreview').attr('src', '../../' + u.image).show();
            $('#noImageBox').hide();
        } else {
            $('#imagePreview').hide();
            $('#noImageBox').text(getInitials(u.name)).css('display', 'flex');
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
    $('#editUserForm').on('submit', function (e) {
        e.preventDefault();

        $('.invalid-feedback').text('');

        var formData = new FormData(this);
        var $btn = $('#submitBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: '../../api/users/update-user.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                toast(res.message || 'User updated successfully', 'suc');
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
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update User');
        });
    });
    </script>
</body>

</html>