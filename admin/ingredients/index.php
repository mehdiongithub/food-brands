<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MenuCrest — Admin Ingredients</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="../../assets/css/admin/style.css">
</head>

<body>

    <div class="sb-bd" id="sbBd" onclick="closeMS()"></div>

    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/header.php'; ?>

    <div id="main">
        <div class="pg-content" id="pgC">

            <div class="ps act">
                <div class="pg-head" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
                    <div>
                        <h1 class="pg-title">Ingredients</h1>
                        <p class="pg-desc">Manage the master list of ingredients</p>
                    </div>

                    <?php if (currentUserRole() === 'admin'): ?>
                    <button type="button" class="ba" onclick="openAddIngredient()">
                        <i class="fas fa-plus"></i> Add Ingredient
                    </button>
                    <?php endif; ?>
                </div>

                <div class="lt">
                    <div class="lt-l">
                        <select class="fs" id="pageLengthSelect">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                            <option value="-1">Show All</option>
                        </select>

                        <select class="fs" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="lt-r">
                        <div class="ls">
                            <i class="fas fa-search"></i>
                            <input type="text" id="customSearch" placeholder="Search ingredients...">
                        </div>

                        <div id="csvBtnContainer"></div>
                    </div>
                </div>

                <div class="cd">
                    <div class="cd-b p-3">
                        <div class="tw">
                            <table id="ingredientsTable" class="at display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Add / Edit Ingredient Modal -->
    <div class="modal fade" id="ingredientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom:1px solid var(--border-l);">
                    <h5 class="modal-title" id="ingredientModalTitle" style="font-weight:700;">Add Ingredient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="ingredientForm">
                    <div class="modal-body" style="padding:24px;">
                        <input type="hidden" id="ingredientId" value="">

                        <div class="fg" style="margin-bottom:16px;">
                            <label class="fl">Ingredient Name <span style="color:red">*</span></label>
                            <input type="text" class="fi" id="ingredientName" required maxlength="150" placeholder="e.g. Chicken Breast">
                            <div class="invalid-feedback" id="err_ingredientName"></div>
                        </div>

                        <div class="fg">
                            <label class="fl">Status</label>
                            <select class="fss" id="ingredientStatus">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top:1px solid var(--border-l);padding:16px 24px;">
                        <button type="button" class="bo" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="ba" id="ingredientSubmitBtn">
                            <i class="fas fa-save"></i> Save
                        </button>
                    </div>
                </form>
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
                    <p style="color:var(--muted);margin-bottom:0;" id="deleteModalText">
                        This ingredient will be permanently deleted. This cannot be undone.
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
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="../../assets/js/admin/sidebar.js"></script>

    <script>
        var table;
        var pendingDeleteId = null;
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        var ingredientModal = new bootstrap.Modal(document.getElementById('ingredientModal'));

        function toast(m, t) {
            t = t || 'suc';
            var ic = { suc: 'fa-check-circle', err: 'fa-times-circle', wrn: 'fa-exclamation-triangle', inf: 'fa-info-circle' };
            var $t = $('<div class="ti2 ' + t + '"><i class="fas ' + ic[t] + '"></i><span>' + m + '</span></div>');
            $('#tw2').append($t);
            setTimeout(function() {
                $t.fadeOut(250, function() { $(this).remove(); });
            }, 2800);
        }

        $(function() {

            table = $('#ingredientsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "../../api/ingredients/ingredients.php",
                    type: "POST",
                    data: function(d) {
                        d.status_filter = $('#statusFilter').val();
                    }
                },
                columns: [
                    { data: "name", orderable: true },
                    { data: "status", orderable: true },
                    { data: "created_at", orderable: true },
                    { data: "actions", orderable: false, searchable: false }
                ],
                order: [[2, 'desc']],
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                pageLength: 10,
                dom: 'Brtip',
                buttons: [{
                    extend: 'csv',
                    text: '<i class="fas fa-download"></i> Export CSV',
                    className: 'ba',
                    filename: 'ingredients_export_' + new Date().toISOString().slice(0, 10),
                    exportOptions: { columns: [0, 1, 2] }
                }]
            });

            table.buttons().container().appendTo('#csvBtnContainer');

            var searchTimer;
            $('#customSearch').on('keyup', function() {
                clearTimeout(searchTimer);
                var val = this.value;
                searchTimer = setTimeout(function() {
                    table.search(val).draw();
                }, 300);
            });

            $('#pageLengthSelect').on('change', function() {
                table.page.len(parseInt($(this).val(), 10)).draw();
            });

            $('#statusFilter').on('change', function() {
                table.draw();
            });

        });

        // --- Open modal for adding a new ingredient ---
        function openAddIngredient() {
            $('#ingredientModalTitle').text('Add Ingredient');
            $('#ingredientId').val('');
            $('#ingredientName').val('');
            $('#ingredientStatus').val('1');
            $('.invalid-feedback').text('');
            ingredientModal.show();
        }

        // --- Open modal pre-filled for editing ---
        function openEditIngredient(id, name, status) {
            $('#ingredientModalTitle').text('Edit Ingredient');
            $('#ingredientId').val(id);
            $('#ingredientName').val(name);
            $('#ingredientStatus').val(status);
            $('.invalid-feedback').text('');
            ingredientModal.show();
        }

        // --- Submit add/edit form ---
        $('#ingredientForm').on('submit', function (e) {
            e.preventDefault();

            $('.invalid-feedback').text('');

            var id = $('#ingredientId').val();
            var name = $('#ingredientName').val().trim();
            var status = $('#ingredientStatus').val();

            if (name === '') {
                $('#err_ingredientName').text('Ingredient name is required.');
                return;
            }

            var url = id
                ? '../../api/ingredients/update-ingredient.php'
                : '../../api/ingredients/add-ingredient.php';

            var payload = { name: name, status: status };
            if (id) payload.id = id;

            var $btn = $('#ingredientSubmitBtn');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: url,
                type: 'POST',
                data: payload,
                dataType: 'json'
            })
            .done(function (res) {
                if (res.success) {
                    toast(res.message || 'Saved successfully', 'suc');
                    ingredientModal.hide();
                    table.ajax.reload(null, false);
                } else {
                    if (res.errors) {
                        $.each(res.errors, function (field, msg) {
                            $('#err_ingredient' + field.charAt(0).toUpperCase() + field.slice(1)).text(msg);
                        });
                    }
                    toast(res.message || 'Please fix the errors below', 'err');
                }
            })
            .fail(function () {
                toast('Something went wrong. Please try again.', 'err');
            })
            .always(function () {
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
            });
        });

        function deleteIngredient(id, name) {
            pendingDeleteId = id;
            $('#deleteModalText').text(
                name ? 'Delete "' + name + '"? This cannot be undone.' : 'This ingredient will be permanently deleted. This cannot be undone.'
            );
            deleteModal.show();
        }

        $('#confirmDeleteBtn').on('click', function() {
            if (!pendingDeleteId) return;

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');

            $.ajax({
                url: '../../api/ingredients/ingredients.php',
                type: 'POST',
                data: { action: 'delete', id: pendingDeleteId },
                dataType: 'json'
            })
            .done(function(res) {
                deleteModal.hide();
                if (res.success) {
                    toast(res.message || 'Ingredient deleted successfully', 'suc');
                    table.ajax.reload(null, false);
                } else {
                    toast(res.message || 'Failed to delete ingredient', 'err');
                }
            })
            .fail(function() {
                deleteModal.hide();
                toast('Something went wrong. Please try again.', 'err');
            })
            .always(function() {
                $btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Delete');
                pendingDeleteId = null;
            });
        });
    </script>
</body>

</html>