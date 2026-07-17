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
    <title>MenuCrest — View User</title>
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
                    <h1 class="pg-title">View User</h1>
                    <p class="pg-desc">User account details</p>
                </div>
                <div style="display:flex;gap:10px;">
                    <a href="edit-user.php?id=<?= $id ?>" class="ba text-decoration-none">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <a href="index.php" class="bo text-decoration-none">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                </div>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="viewContainer">
                    <div style="text-align:center;padding:40px;color:var(--muted);">
                        <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                        <p style="margin-top:10px;">Loading user details...</p>
                    </div>
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

    var userId = <?= json_encode($id) ?>;

    function getInitials(name) {
        if (!name) return '?';
        var parts = name.trim().split(/\s+/);
        if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
        return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return $('<div>').text(str).html();
    }

    $(function () {
        $.ajax({
            url: '../../api/users/get-user.php',
            type: 'GET',
            data: { id: userId },
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                renderUser(res.data);
            } else {
                toast(res.message || 'User not found', 'err');
                $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                    '<i class="fas fa-triangle-exclamation" style="font-size:1.4rem;"></i>' +
                    '<p style="margin-top:10px;">' + escapeHtml(res.message || 'User not found') + '</p></div>');
            }
        })
        .fail(function () {
            toast('Failed to load user details', 'err');
            $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                '<p>Something went wrong while loading this user.</p></div>');
        });
    });

    function renderUser(u) {
        var avatarHtml = u.image
            ? "<img src='../../" + u.image + "' alt='" + escapeHtml(u.name) + "' style='width:70px;height:70px;border-radius:50%;object-fit:cover;'>"
            : "<div style='width:70px;height:70px;border-radius:50%;background:#E85D04;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.1rem;'>" + getInitials(u.name) + "</div>";

        var statusBadge = (u.status == 1)
            ? '<span class="sb-badge2 active">Active</span>'
            : '<span class="sb-badge2 draft">Inactive</span>';

        var lastLogin = u.last_login ? u.last_login : 'Never logged in';
        var joined = u.created_at ? u.created_at : '—';
        var updated = u.updated_at ? u.updated_at : '—';

        var html = '' +
            '<div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;">' +
                avatarHtml +
                '<div><div style="font-size:1.05rem;font-weight:700;">' + escapeHtml(u.name) + '</div>' +
                '<div style="color:var(--muted);font-size:.85rem;">' + escapeHtml(u.email) + '</div></div>' +
            '</div>' +

            '<div class="row g-3">' +

                field('Full Name', u.name) +
                field('Email', u.email) +
                field('Phone', u.phone || '—') +
                field('Role', capitalize(u.role)) +
                fieldHtml('Status', statusBadge) +
                field('Last Login', lastLogin) +
                field('Created At', joined) +
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

    function capitalize(s) {
        if (!s) return '—';
        return s.charAt(0).toUpperCase() + s.slice(1);
    }
    </script>
</body>

</html>