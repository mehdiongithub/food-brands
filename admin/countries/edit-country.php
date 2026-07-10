<?php
require_once __DIR__ . "/../../config/bootstrap.php";

$token = $_GET['token'] ?? '';
$id = decryptId($token);

if ($id === false || $id <= 0) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

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
    <title>FoodScope — Edit Country</title>
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
                    <h1 class="pg-title">Edit Country</h1>
                    <p class="pg-desc">Update country details</p>
                </div>
                <a href="index.php" class="bo text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Back to Countries
                </a>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="formWrapper" style="display:none;">

                    <form id="editCountryForm" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="countryId" value="<?= $id ?>">
                        <input type="hidden" name="existing_flag" id="existingFlag" value="">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="fl">Country Name <span style="color:red">*</span></label>
                                <input type="text" class="fi" name="name" id="name" required maxlength="100">
                                <div class="invalid-feedback" id="err_name"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Country Code <span style="color:red">*</span></label>
                                <input type="text" class="fi" name="code" id="code" required maxlength="2" style="text-transform:uppercase;">
                                <small style="color:var(--muted);font-size:.72rem;">2-letter ISO code (e.g. US, GB, PK)</small>
                                <div class="invalid-feedback" id="err_code"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Currency Code <span style="color:red">*</span></label>
                                <input type="text" class="fi" name="currency" id="currency" required maxlength="10">
                                <div class="invalid-feedback" id="err_currency"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Currency Symbol <span style="color:red">*</span></label>
                                <input type="text" class="fi" name="currency_symbol" id="currency_symbol" required maxlength="10">
                                <div class="invalid-feedback" id="err_currency_symbol"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Status</label>
                                <select class="fss" name="status" id="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Flag Image</label>
                                <input type="file" class="fi" name="flag" id="flag" accept="image/png, image/jpeg, image/jpg, image/webp, image/svg+xml">
                                <small style="color:var(--muted);font-size:.72rem;">Leave empty to keep current flag. Max 2MB.</small>
                                <div class="invalid-feedback" id="err_flag"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Current / Preview Flag</label>
                                <div>
                                    <img id="flagPreview" src="" alt="Preview"
                                         style="display:none;width:64px;height:46px;object-fit:cover;border-radius:6px;border:1px solid var(--border);">
                                    <div id="noFlagBox" style="display:none;width:64px;height:46px;border-radius:6px;background:var(--bg);border:1px solid var(--border);align-items:center;justify-content:center;">
                                        <i class="fas fa-globe" style="color:var(--muted);"></i>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="mo-f" style="padding:0;margin-top:22px;border:none;display:flex;gap:10px;">
                            <button type="button" class="bo" onclick="window.location.href='index.php'">Cancel</button>
                            <button type="submit" class="ba" id="submitBtn">
                                <i class="fas fa-save"></i> Update Country
                            </button>
                        </div>

                    </form>

                </div>

                <div id="loadingBox" style="text-align:center;padding:40px;color:var(--muted);">
                    <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                    <p style="margin-top:10px;">Loading country details...</p>
                </div>

            </div>

        </div>
    </div>

    <div class="tw2" id="tw2"></div>
    <div id="formAlert" style="display:none;" class="alert" role="alert"></div>


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
    var countryId = $('#countryId').val();
    var BASE_URL_JS = <?= json_encode(BASE_URL) ?>;

    // --- Load existing country data ---
    $(function () {
        $.ajax({
            url: '../../api/countries/get-country.php',
            type: 'GET',
            data: { id: countryId },
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                populateForm(res.data);
                $('#loadingBox').hide();
                $('#formWrapper').show();
            } else {
                toast(res.message || 'Country not found', 'err');
                $('#loadingBox').html('<p>' + (res.message || 'Country not found') + '</p>');
            }
        })
        .fail(function () {
            toast('Failed to load country details', 'err');
            $('#loadingBox').html('<p>Something went wrong while loading this country.</p>');
        });
    });

    function populateForm(c) {
        $('#name').val(c.name);
        $('#code').val(c.code);
        $('#currency').val(c.currency);
        $('#currency_symbol').val(c.currency_symbol);
        $('#status').val(c.status);
        $('#existingFlag').val(c.flag || '');

        if (c.flag) {
            $('#flagPreview').attr('src', BASE_URL_JS + '/' + c.flag).show();
            $('#noFlagBox').hide();
        } else if (c.code) {
            // fallback preview using flagcdn, matching what the list page shows
            $('#flagPreview').attr('src', 'https://flagcdn.com/w80/' + c.code.toLowerCase() + '.png').show();
            $('#noFlagBox').hide();
        } else {
            $('#flagPreview').hide();
            $('#noFlagBox').css('display', 'flex');
        }
    }

    // --- Live preview when a NEW flag is chosen ---
    $('#code').on('input', function () {
        this.value = this.value.toUpperCase();
    });

    $('#flag').on('change', function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#flagPreview').attr('src', e.target.result).show();
                $('#noFlagBox').hide();
            };
            reader.readAsDataURL(file);
        }
    });

    // --- Submit update ---
    $('#editCountryForm').on('submit', function (e) {
        e.preventDefault();

        $('.invalid-feedback').text('');

        var formData = new FormData(this);
        var $btn = $('#submitBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');

        $.ajax({
            url: '../../api/countries/update-country.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                toast(res.message || 'Country updated successfully', 'suc');
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
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Country');
        });
    });
    </script>
</body>

</html>