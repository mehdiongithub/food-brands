<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin(); // both admin and employee can view

$token = $_GET['token'] ?? '';
$id = decryptId($token);

if ($id === false || $id <= 0) {
    header("Location: " . BASE_URL . "/admin/messages/index.php");
    exit;
}

// Confirm the message actually exists — invalid/deleted IDs also redirect
$checkStmt = $pdo->prepare("SELECT id FROM contact_messages WHERE id = :id LIMIT 1");
$checkStmt->execute([':id' => $id]);
if (!$checkStmt->fetch()) {
    header("Location: " . BASE_URL . "/admin/messages/index.php");
    exit;
}

$isAdmin = (currentUserRole() === 'admin');
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodScope — View Message</title>
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
            white-space: pre-wrap;
        }
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
                    <h1 class="pg-title">View Message</h1>
                    <p class="pg-desc">Contact message details</p>
                </div>
                <div style="display:flex;gap:10px;">
                    <?php if ($isAdmin): ?>
                    <a href="#" id="deleteBtn" class="text-decoration-none" style="background:#DC2626;color:#fff;border:none;padding:9px 18px;border-radius:8px;font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                    <?php endif; ?>
                    <a href="index.php" class="bo text-decoration-none">
                        <i class="fas fa-arrow-left"></i> Back to Messages
                    </a>
                </div>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="viewContainer">
                    <div style="text-align:center;padding:40px;color:var(--muted);">
                        <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                        <p style="margin-top:10px;">Loading message details...</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center" style="padding:32px 28px;">
                    <div style="width:56px;height:56px;border-radius:50%;background:rgba(220,38,38,.1);color:#DC2626;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.4rem;">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <h5 style="font-weight:700;margin-bottom:8px;">Are you sure?</h5>
                    <p style="color:var(--muted);margin-bottom:0;">
                        This message will be permanently deleted. This cannot be undone.
                    </p>
                </div>
                <div class="modal-footer" style="border-top:none;padding:0 28px 28px;justify-content:center;gap:10px;">
                    <button type="button" class="bo" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="bdn" id="confirmDeleteBtn" style="background:#DC2626;color:#fff;border:none;padding:9px 18px;border-radius:8px;font-weight:600;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="tw2" id="tw2"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin/sidebar.js"></script>
    <script>
    var messageId = <?= json_encode($id) ?>;
    var isAdmin = <?= json_encode($isAdmin) ?>;

    function toast(m, t) {
        t = t || 'suc';
        var ic = { suc: 'fa-check-circle', err: 'fa-times-circle', wrn: 'fa-exclamation-triangle', inf: 'fa-info-circle' };
        var $t = $('<div class="ti2 ' + t + '"><i class="fas ' + ic[t] + '"></i><span>' + m + '</span></div>');
        $('#tw2').append($t);
        setTimeout(function() {
            $t.fadeOut(250, function() { $(this).remove(); });
        }, 2800);
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return $('<div>').text(str).html();
    }

    $(function () {
        $.ajax({
            url: '../../api/messages/get-message.php',
            type: 'GET',
            data: { id: messageId },
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                renderMessage(res.data);
            } else {
                toast(res.message || 'Message not found', 'err');
                $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                    '<i class="fas fa-triangle-exclamation" style="font-size:1.4rem;"></i>' +
                    '<p style="margin-top:10px;">' + escapeHtml(res.message || 'Message not found') + '</p></div>');
            }
        })
        .fail(function () {
            toast('Failed to load message details', 'err');
            $('#viewContainer').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                '<p>Something went wrong while loading this message.</p></div>');
        });
    });

    function renderMessage(m) {
        var statusBadges = {
            'new':     '<span class="sb-badge2" style="background:rgba(37,99,235,.1);color:#2563EB;">New</span>',
            'read':    '<span class="sb-badge2 draft">Read</span>',
            'replied': '<span class="sb-badge2 active">Replied</span>'
        };
        var statusBadge = statusBadges[m.status] || statusBadges['new'];

        var created = m.created_at || '—';

        var html = '' +
            '<div class="row g-3">' +
                field('Name', m.name) +
                field('Email', m.email) +
                field('Phone', m.phone || '—') +
                field('Subject', m.subject) +
                fieldHtml('Status', statusBadge) +
                field('Received At', created) +
                field('IP Address', m.ip_address || '—') +
            '</div>' +

            '<div class="row g-3" style="margin-top:14px;">' +
                '<div class="col-12">' +
                    '<label class="fl">Message</label>' +
                    '<div class="desc-view">' + escapeHtml(m.message) + '</div>' +
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

    // --- Delete handling ---
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

    $('#deleteBtn').on('click', function(e) {
        e.preventDefault();
        deleteModal.show();
    });

    $('#confirmDeleteBtn').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');

        $.ajax({
            url: '../../api/messages/delete-message.php',
            type: 'POST',
            data: { id: messageId },
            dataType: 'json'
        })
        .done(function(res) {
            deleteModal.hide();
            if (res.success) {
                toast(res.message || 'Message deleted successfully', 'suc');
                setTimeout(function() {
                    window.location.href = 'index.php';
                }, 800);
            } else {
                toast(res.message || 'Failed to delete message', 'err');
            }
        })
        .fail(function() {
            deleteModal.hide();
            toast('Something went wrong. Please try again.', 'err');
        })
        .always(function() {
            $btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Delete');
        });
    });
    </script>
</body>

</html>
