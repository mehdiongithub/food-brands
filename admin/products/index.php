<?php
require_once __DIR__ . "/../../config/bootstrap.php";
requireLogin();

$categoriesForFilter = $pdo->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$brandsForFilter = $pdo->query("SELECT id, name FROM brands WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$countriesForFilter = $pdo->query("SELECT id, name FROM countries WHERE status = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodScope — Admin Products</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="../../assets/css/admin/style.css">
    <style>
        .row-toggle-btn {
            width: 30px; height: 30px; border-radius: 50%;
            border: 1px solid var(--border); background: var(--surface); color: var(--text2);
            display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all .25s ease;
        }
        .row-toggle-btn:hover { border-color: var(--accent, #E85D04); color: var(--accent, #E85D04); }
        .row-toggle-btn i { transition: transform .25s ease; }
        tr.row-expanded .row-toggle-btn { background: var(--accent, #E85D04); border-color: var(--accent, #E85D04); color: #fff; }
        tr.row-expanded .row-toggle-btn i { transform: rotate(135deg); }

        td.details-control-cell { padding: 0 !important; border-top: none !important; }
        .child-row-inner { overflow: hidden; max-height: 0; transition: max-height .35s ease; }
        .child-row-inner.open { max-height: 700px; }

        /* --- Single-line toolbar --- */
        .products-toolbar {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 4px;
            margin-bottom: 16px;
        }
        .products-toolbar::-webkit-scrollbar { height: 5px; }
        .products-toolbar::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

        .products-toolbar .fs {
            flex: 0 0 auto;
            min-width: 140px;
            white-space: nowrap;
        }
        .products-toolbar .ls {
            flex: 1 1 220px;
            min-width: 200px;
        }
        .products-toolbar #csvBtnContainer {
            flex: 0 0 auto;
        }

        @media (max-width: 768px) {
            .products-toolbar .fs { min-width: 120px; font-size: .8rem; }
        }
    </style>
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
                        <h1 class="pg-title">Products</h1>
                        <p class="pg-desc">Manage your product catalog</p>
                    </div>

                    <?php if (currentUserRole() === 'admin' || currentUserRole() === 'employee'): ?>
                    <a href="<?= BASE_URL ?>/admin/products/create.php" class="ba text-decoration-none">
                        <i class="fas fa-plus"></i> Add Product
                    </a>
                    <?php endif; ?>
                </div>

                <!-- ===== Single-line toolbar: page length, filters, search, CSV ===== -->
                <div class="products-toolbar">
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

                    <select class="fs" id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php foreach ($categoriesForFilter as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select class="fs" id="brandFilter">
                        <option value="">All Brands</option>
                        <?php foreach ($brandsForFilter as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select class="fs" id="countryFilter">
                        <option value="">All Countries</option>
                        <?php foreach ($countriesForFilter as $ctry): ?>
                            <option value="<?= $ctry['id'] ?>"><?= htmlspecialchars($ctry['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div class="ls">
                        <i class="fas fa-search"></i>
                        <input type="text" id="customSearch" placeholder="Search product name...">
                    </div>

                    <div id="csvBtnContainer"></div>
                </div>

                <div class="cd">
                    <div class="cd-b p-3">
                        <div class="tw">
                            <table id="productsTable" class="at display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Calories</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
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
                    <p style="color:var(--muted);margin-bottom:0;" id="deleteModalText">
                        This product will be permanently deleted. This cannot be undone.
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
        function toast(m, t) {
            t = t || 'suc';
            var ic = { suc: 'fa-check-circle', err: 'fa-times-circle', wrn: 'fa-exclamation-triangle', inf: 'fa-info-circle' };
            var $t = $('<div class="ti2 ' + t + '"><i class="fas ' + ic[t] + '"></i><span>' + m + '</span></div>');
            $('#tw2').append($t);
            setTimeout(function() {
                $t.fadeOut(250, function() { $(this).remove(); });
            }, 2800);
        }

        
        var table;
        var pendingDeleteId = null;
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

        $(function() {

            table = $('#productsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "../../api/products/products.php",
                    type: "POST",
                    data: function(d) {
                        d.status_filter = $('#statusFilter').val();
                        d.category_filter = $('#categoryFilter').val();
                        d.brand_filter = $('#brandFilter').val();
                        d.country_filter = $('#countryFilter').val();
                    }
                },
                columns: [
                    { data: "product", orderable: true },
                    { data: "calories", orderable: true },
                    { data: "status", orderable: true },
                    { data: "created_at", orderable: true },
                    { data: "actions", orderable: false, searchable: false },
                    { data: "details", orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[3, 'desc']],
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                pageLength: 10,
                dom: 'Brtip',
                buttons: [{
                    extend: 'csv',
                    text: '<i class="fas fa-download"></i> Export CSV',
                    className: 'ba',
                    filename: 'products_export_' + new Date().toISOString().slice(0, 10),
                    exportOptions: { columns: [0, 1, 2, 3] }
                }],
                drawCallback: function () {
                    $('#productsTable tbody tr.row-expanded').removeClass('row-expanded');
                }
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

            $('#statusFilter, #categoryFilter, #brandFilter, #countryFilter').on('change', function() {
                table.draw();
            });

            // --- Expandable row toggle ---
            // MUST be bound inside this same $(function(){...}) block, right after
            // `table` is initialized above — binding it outside/after this block
            // was the reason it previously failed to open.
            table.on('click', 'button.row-toggle-btn', function (e) {
                e.preventDefault();

                var $btn = $(this);
                var $tr = $btn.closest('tr');
                var row = table.row($tr);

                if (!row || !row.data()) {
                    console.warn('Could not resolve row data for toggle button.');
                    return;
                }

                var rowData = row.data();

                if (row.child.isShown()) {
                    var $childRow = $tr.next('tr.child-row');
                    $childRow.find('.child-row-inner').removeClass('open');

                    setTimeout(function () {
                        row.child.hide();
                        $tr.removeClass('row-expanded');
                    }, 350);

                } else {
                    var wrapperHtml = '<div class="child-row-inner">' + rowData.child_html + '</div>';

                    row.child(wrapperHtml, 'child-row').show();
                    $tr.addClass('row-expanded');

                    requestAnimationFrame(function () {
                        $tr.next('tr.child-row').find('.child-row-inner').addClass('open');
                    });
                }
            });

        });

        function deleteProduct(id, name) {
            pendingDeleteId = id;
            $('#deleteModalText').text(
                name ? 'Delete "' + name + '"? This cannot be undone.' : 'This product will be permanently deleted. This cannot be undone.'
            );
            deleteModal.show();
        }

        $('#confirmDeleteBtn').on('click', function() {
            if (!pendingDeleteId) return;

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');

            $.ajax({
                url: '../../api/products/delete-product.php',
                type: 'POST',
                data: { id: pendingDeleteId },
                dataType: 'json'
            })
            .done(function(res) {
                deleteModal.hide();
                if (res.success) {
                    toast(res.message || 'Product deleted successfully', 'suc');
                    table.ajax.reload(null, false);
                } else {
                    toast(res.message || 'Failed to delete product', 'err');
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