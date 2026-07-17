<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MenuCrest — Account Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/admin/style.css">
    <style>
        .settings-tabs {
            display: flex;
            gap: 6px;
            border-bottom: 1px solid var(--border, #e5e5e5);
            margin-bottom: 22px;
        }
        .settings-tab-btn {
            background: none;
            border: none;
            padding: 12px 18px;
            font-weight: 600;
            font-size: .88rem;
            color: var(--muted, #888);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all .2s ease;
        }
        .settings-tab-btn.active {
            color: var(--accent, #E85D04);
            border-bottom-color: var(--accent, #E85D04);
        }
        .settings-tab-pane { display: none; }
        .settings-tab-pane.active { display: block; }

        .avatar-upload-wrap {
            display: flex;
            align-items: center;
            gap: 18px;
        }
        #avatarPreview {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid var(--border, #e5e5e5);
            background: var(--bg, #f2f2f2);
        }

        .account-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-top: 6px;
        }
        .account-meta-item label {
            display: block;
            font-size: .72rem;
            color: var(--muted, #888);
            text-transform: uppercase;
            letter-spacing: .03em;
            margin-bottom: 4px;
        }
        .account-meta-item div {
            font-size: .92rem;
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
                    <h1 class="pg-title">Account Settings</h1>
                    <p class="pg-desc">Manage your profile and security preferences</p>
                </div>
            </div>

            <div class="cd">
                <div class="cd-b p-4">

                    <div class="settings-tabs">
                        <button type="button" class="settings-tab-btn active" data-tab="profile">
                            <i class="fas fa-user"></i> Profile
                        </button>
                        <button type="button" class="settings-tab-btn" data-tab="security">
                            <i class="fas fa-lock"></i> Security
                        </button>
                        <button type="button" class="settings-tab-btn" data-tab="account">
                            <i class="fas fa-id-badge"></i> Account Info
                        </button>
                    </div>

                    <div id="loadingState" style="text-align:center;padding:40px;color:var(--muted);">
                        <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                        <p style="margin-top:10px;">Loading your settings...</p>
                    </div>

                    <div id="settingsContent" style="display:none;">

                        <!-- ===== Profile Tab ===== -->
                        <div class="settings-tab-pane active" id="tab-profile">
                            <form id="profileForm" enctype="multipart/form-data">

                                <div class="row g-3">

                                    <div class="col-md-12">
                                        <label class="fl">Profile Photo</label>
                                        <div class="avatar-upload-wrap">
                                            <img id="avatarPreview" src="" alt="Avatar">
                                            <div>
                                                <input type="file" class="fi" name="image" id="image" accept="image/png, image/jpeg, image/jpg, image/webp">
                                                <small style="color:var(--muted);font-size:.72rem;display:block;margin-top:4px;">JPG, PNG or WEBP. Max 2MB.</small>
                                                <div class="invalid-feedback" id="err_image"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fl">Full Name <span style="color:red">*</span></label>
                                        <input type="text" class="fi" name="name" id="name" required maxlength="100" placeholder="Enter your name">
                                        <div class="invalid-feedback" id="err_name"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fl">Email Address <span style="color:red">*</span></label>
                                        <input type="email" class="fi" name="email" id="email" required maxlength="150" placeholder="Enter your email">
                                        <div class="invalid-feedback" id="err_email"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fl">Phone Number</label>
                                        <input type="text" class="fi" name="phone" id="phone" maxlength="20" placeholder="Enter your phone number">
                                        <div class="invalid-feedback" id="err_phone"></div>
                                    </div>

                                </div>

                                <div class="mo-f" style="padding:0;margin-top:22px;border:none;display:flex;gap:10px;">
                                    <button type="submit" class="ba" id="profileSubmitBtn">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </div>

                            </form>
                        </div>

                        <!-- ===== Security Tab ===== -->
                        <div class="settings-tab-pane" id="tab-security">
                            <form id="passwordForm">

                                <div class="row g-3">

                                    <div class="col-md-12">
                                        <label class="fl">Current Password <span style="color:red">*</span></label>
                                        <input type="password" class="fi" name="current_password" id="current_password" required placeholder="Enter current password">
                                        <div class="invalid-feedback" id="err_current_password"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fl">New Password <span style="color:red">*</span></label>
                                        <input type="password" class="fi" name="new_password" id="new_password" required placeholder="At least 8 characters">
                                        <div class="invalid-feedback" id="err_new_password"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fl">Confirm New Password <span style="color:red">*</span></label>
                                        <input type="password" class="fi" name="confirm_password" id="confirm_password" required placeholder="Re-enter new password">
                                        <div class="invalid-feedback" id="err_confirm_password"></div>
                                    </div>

                                </div>

                                <div class="mo-f" style="padding:0;margin-top:22px;border:none;display:flex;gap:10px;">
                                    <button type="submit" class="ba" id="passwordSubmitBtn">
                                        <i class="fas fa-key"></i> Update Password
                                    </button>
                                </div>

                            </form>
                        </div>

                        <!-- ===== Account Info Tab (read-only) ===== -->
                        <div class="settings-tab-pane" id="tab-account">
                            <div class="account-meta" id="accountMetaContainer"></div>
                        </div>

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

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return $('<div>').text(str).html();
    }

    // --- Tab switching ---
    $('.settings-tab-btn').on('click', function () {
        var tab = $(this).data('tab');
        $('.settings-tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.settings-tab-pane').removeClass('active');
        $('#tab-' + tab).addClass('active');
    });

    // --- Load current profile via AJAX ---
    function loadProfile() {
        $.ajax({
            url: '../../api/settings/get-profile.php',
            type: 'GET',
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                populateProfile(res.data);
                $('#loadingState').hide();
                $('#settingsContent').show();
            } else {
                toast(res.message || 'Failed to load profile', 'err');
            }
        })
        .fail(function () {
            toast('Failed to load profile', 'err');
        });
    }

    function populateProfile(u) {
        $('#name').val(u.name || '');
        $('#email').val(u.email || '');
        $('#phone').val(u.phone || '');

        if (u.image) {
            $('#avatarPreview').attr('src', BASE_URL_JS + '/' + u.image);
        } else {
            // Simple placeholder avatar if none set
            $('#avatarPreview').attr('src', 'https://ui-avatars.com/api/?name=' + encodeURIComponent(u.name || 'U') + '&background=E85D04&color=fff');
        }

        var statusBadge = (u.status == 1)
            ? '<span class="sb-badge2 active">Active</span>'
            : '<span class="sb-badge2 draft">Inactive</span>';

        var roleLabel = u.role ? (u.role.charAt(0).toUpperCase() + u.role.slice(1)) : '—';

        var metaHtml = '' +
            metaItem('Role', roleLabel) +
            metaItemHtml('Status', statusBadge) +
            metaItem('Last Login', u.last_login || 'Never') +
            metaItem('Member Since', u.created_at || '—');

        $('#accountMetaContainer').html(metaHtml);
    }

    function metaItem(label, value) {
        return '<div class="account-meta-item"><label>' + label + '</label><div>' + escapeHtml(value) + '</div></div>';
    }
    function metaItemHtml(label, htmlValue) {
        return '<div class="account-meta-item"><label>' + label + '</label><div>' + htmlValue + '</div></div>';
    }

    $(function () {
        loadProfile();
    });

    // --- Live avatar preview ---
    $('#image').on('change', function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#avatarPreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // --- Submit profile form ---
    $('#profileForm').on('submit', function (e) {
        e.preventDefault();
        $('.invalid-feedback').text('');

        var formData = new FormData(this);
        var $btn = $('#profileSubmitBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '../../api/settings/update-profile.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                toast(res.message || 'Profile updated successfully', 'suc');
                loadProfile(); // refresh account info + avatar
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
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Changes');
        });
    });

    // --- Submit password form ---
    $('#passwordForm').on('submit', function (e) {
        e.preventDefault();
        $('.invalid-feedback').text('');

        var formData = new FormData(this);
        var $btn = $('#passwordSubmitBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: '../../api/settings/update-password.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                toast(res.message || 'Password updated successfully', 'suc');
                $('#passwordForm')[0].reset();
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
            $btn.prop('disabled', false).html('<i class="fas fa-key"></i> Update Password');
        });
    });

    // --- Open the correct tab based on ?tab= in the URL ---
function activateTabFromUrl() {
    var params = new URLSearchParams(window.location.search);
    var tab = params.get('tab');
    var validTabs = ['profile', 'security', 'account'];

    if (tab && validTabs.indexOf(tab) !== -1) {
        $('.settings-tab-btn').removeClass('active');
        $('.settings-tab-btn[data-tab="' + tab + '"]').addClass('active');
        $('.settings-tab-pane').removeClass('active');
        $('#tab-' + tab).addClass('active');
    }
}

$(function () {
    loadProfile();
    activateTabFromUrl();
});
    </script>
</body>

</html>