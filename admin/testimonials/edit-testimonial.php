<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();

$token = $_GET['token'] ?? '';
$id = decryptId($token);

if ($id === false || $id <= 0) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

// Only admin can edit
if (currentUserRole() !== 'admin') {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

// Confirm the testimonial actually exists — invalid/deleted IDs also redirect
$checkStmt = $pdo->prepare("SELECT id FROM testimonials WHERE id = :id LIMIT 1");
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
    <title>FoodScope — Edit Testimonial</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin/style.css">
    <style>
        #reviewEditor {
            background: var(--surface, #fff);
            min-height: 140px;
            border-radius: 0 0 var(--r-md, 8px) var(--r-md, 8px);
        }
        .ql-toolbar.ql-snow {
            border-radius: var(--r-md, 8px) var(--r-md, 8px) 0 0;
            background: var(--bg, #f8f8f8);
        }

        /* --- Star rating (radio-based) --- */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 4px;
            font-size: 1.5rem;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            cursor: pointer;
            color: #D1D5DB;
            transition: color .15s ease;
            margin: 0;
        }
        .star-rating label i {
            pointer-events: none;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #F5A623;
        }
        .star-rating input:checked ~ label {
            color: #F5A623;
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
                    <h1 class="pg-title">Edit Testimonial</h1>
                    <p class="pg-desc">Update testimonial details</p>
                </div>
                <a href="index.php" class="bo text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Back to Testimonials
                </a>
            </div>

            <div class="cd">
                <div class="cd-b p-4" id="formContainer">
                    <div style="text-align:center;padding:40px;color:var(--muted);" id="loadingState">
                        <i class="fas fa-spinner fa-spin" style="font-size:1.4rem;"></i>
                        <p style="margin-top:10px;">Loading testimonial details...</p>
                    </div>

                    <form id="editTestimonialForm" enctype="multipart/form-data" style="display:none;">
                        <input type="hidden" name="id" id="testimonialIdField" value="">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="fl">Name <span style="color:red">*</span></label>
                                <input type="text" class="fi" name="name" id="name" required maxlength="150" placeholder="Enter customer name">
                                <div class="invalid-feedback" id="err_name"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Designation</label>
                                <input type="text" class="fi" name="designation" id="designation" maxlength="150" placeholder="e.g. Food Blogger, CEO of XYZ">
                                <div class="invalid-feedback" id="err_designation"></div>
                            </div>

                            <div class="col-md-12 mb-5">
                                <label class="fl">Review <span style="color:red">*</span></label>
                                <div id="reviewEditor"></div>
                                <textarea name="review" id="review" style="display:none;"></textarea>
                                <div class="invalid-feedback" id="err_review"></div>
                            </div>

                            <div class="col-md-6 mt-5">
                                <label class="fl">Rating <span style="color:red">*</span></label>
                                <div class="star-rating" id="starRating">
                                    <input type="radio" name="rating" id="star5" value="5"><label for="star5"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="star4" value="4"><label for="star4"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="star3" value="3"><label for="star3"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="star2" value="2"><label for="star2"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="star1" value="1"><label for="star1"><i class="fas fa-star"></i></label>
                                </div>
                                <div class="invalid-feedback" id="err_rating"></div>
                            </div>

                            <div class="col-md-6 mt-5">
                                <label class="fl">Status</label>
                                <select class="fss" name="status" id="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Image</label>
                                <input type="file" class="fi" name="image" id="image" accept="image/png, image/jpeg, image/jpg, image/webp">
                                <small style="color:var(--muted);font-size:.72rem;">Leave empty to keep current image. JPG, PNG or WEBP. Max 2MB.</small>
                                <div class="invalid-feedback" id="err_image"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="fl">Image Preview</label>
                                <div>
                                    <img id="imagePreview" src="" alt="Preview"
                                         style="display:none;width:70px;height:70px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                                </div>
                            </div>

                        </div>

                        <div class="mo-f" style="padding:0;margin-top:22px;border:none;display:flex;gap:10px;">
                            <button type="button" class="bo" onclick="window.location.href='index.php'">Cancel</button>
                            <button type="submit" class="ba" id="submitBtn">
                                <i class="fas fa-save"></i> Update Testimonial
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
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script>

    var testimonialId = <?= json_encode($id) ?>;
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

    // --- Initialize Quill rich text editor (content injected after AJAX load) ---
    var quill = new Quill('#reviewEditor', {
        theme: 'snow',
        placeholder: 'Enter customer review...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                ['blockquote'],
                ['clean']
            ]
        }
    });

    quill.on('text-change', function () {
        $('#review').val(quill.root.innerHTML);
    });

    // --- Load existing testimonial data via AJAX ---
    $(function () {
        $.ajax({
            url: '../../api/testimonials/get-testimonial.php',
            type: 'GET',
            data: { id: testimonialId },
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                populateForm(res.data);
                $('#loadingState').hide();
                $('#editTestimonialForm').show();
            } else {
                toast(res.message || 'Testimonial not found', 'err');
                $('#loadingState').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                    '<i class="fas fa-triangle-exclamation" style="font-size:1.4rem;"></i>' +
                    '<p style="margin-top:10px;">' + (res.message || 'Testimonial not found') + '</p></div>');
            }
        })
        .fail(function () {
            toast('Failed to load testimonial details', 'err');
            $('#loadingState').html('<div style="text-align:center;padding:40px;color:var(--muted);">' +
                '<p>Something went wrong while loading this testimonial.</p></div>');
        });
    });

    function populateForm(t) {
        $('#testimonialIdField').val(t.id);
        $('#name').val(t.name || '');
        $('#designation').val(t.designation || '');
        $('#status').val(t.status == 1 ? '1' : '0');

        // --- Review: seed Quill with existing HTML ---
        quill.root.innerHTML = t.review || '';
        $('#review').val(t.review || '');

        // --- Rating: check the matching radio ---
        var rating = parseInt(t.rating, 10) || 0;
        if (rating >= 1 && rating <= 5) {
            $('#star' + rating).prop('checked', true);
        }

        // --- Image preview ---
        if (t.image) {
            $('#imagePreview').attr('src', BASE_URL_JS + '/' + t.image).show();
        } else {
            $('#imagePreview').hide();
        }
    }

    // --- Live image preview (only replaces preview if a NEW file is chosen) ---
    $('#image').on('change', function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#imagePreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
        // If cleared, we simply keep showing the existing image — no reset needed
    });

    $('#editTestimonialForm').on('submit', function (e) {
        e.preventDefault();

        $('.invalid-feedback').text('');

        if (!$('input[name="rating"]:checked').val()) {
            $('#err_rating').text('Please select a rating between 1 and 5 stars.');
            toast('Please select a rating', 'err');
            return;
        }

        $('#review').val(quill.root.innerHTML);

        var reviewText = quill.getText().trim();
        if (reviewText === '') {
            $('#review').val('');
        }

        var formData = new FormData(this);
        var $btn = $('#submitBtn');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '../../api/testimonials/update-testimonial.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json'
        })
        .done(function (res) {
            if (res.success) {
                toast(res.message || 'Testimonial updated successfully', 'suc');
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
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Update Testimonial');
        });
    });
    </script>
</body>

</html>