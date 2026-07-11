/**
 * categories.js — Loaded on categories.php AND category.php
 * Handles: category cards grid, category detail header, brand pills,
 * products grid with filters, pagination, URL params sync
 */

(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '/food-brands';

    // Detect which page we're on
    var isDetailPage = window.CATEGORY_SLUG ? true : false;
    var categorySlug = window.CATEGORY_SLUG || '';

    // Products filter state (used on category detail page)
    var state = {
        page: 1,
        per_page: 12,
        brand_id: null,
        search: '',
        sort: 'newest'
    };

    // Listing page state
    var listState = {
        page: 1,
        per_page: 12,
        search: '',
        sort: 'sort_order'
    };

    // DOM references
    var $grid, $paginationWrap, $countEl, $skeletonWrap;
    var categoryData = null;

    $(document).ready(function () {
        if (isDetailPage) {
            initDetailPage();
        } else {
            initListPage();
        }
    });

    // ================================================================
    // ================== LISTING PAGE (categories.php) ================
    // ================================================================

    function initListPage() {
        $grid = $('#categories-grid');
        $paginationWrap = $('#categories-pagination');
        $countEl = $('#categories-count');
        $skeletonWrap = $('#categories-skeleton');

        readListUrlParams();
        loadCategories();
        bindListEvents();
    }

    function readListUrlParams() {
        var params = new URLSearchParams(window.location.search);

        if (params.get('page')) listState.page = parseInt(params.get('page')) || 1;
        if (params.get('per_page')) listState.per_page = parseInt(params.get('per_page')) || 12;
        if (params.get('search')) listState.search = params.get('search');
        if (params.get('sort')) listState.sort = params.get('sort');

        if (listState.search) {
            var $input = $('#category-search-input');
            if ($input.length) $input.val(listState.search);
        }

        if (listState.sort) {
            var $sort = $('#category-sort-select');
            if ($sort.length) $sort.val(listState.sort);
        }
    }

    function updateListUrlParams(resetPage) {
        if (resetPage) listState.page = 1;

        var params = new URLSearchParams();

        if (listState.page > 1) params.set('page', listState.page);
        if (listState.per_page !== 12) params.set('per_page', listState.per_page);
        if (listState.search) params.set('search', listState.search);
        if (listState.sort !== 'sort_order') params.set('sort', listState.sort);

        var queryString = params.toString();
        var newUrl = BASE_URL + '/categories' + (queryString ? '?' + queryString : '');

        window.history.replaceState(listState, '', newUrl);
    }

    function loadCategories() {
        showListSkeleton();

        var params = {
            action: 'list',
            page: listState.page,
            per_page: listState.per_page,
            sort: listState.sort
        };

        if (listState.search) params.search = listState.search;

        $.getJSON(BASE_URL + '/api/site/categories.php', params, function (res) {
            if (!res.success) {
                showListError('Failed to load categories. Please try again.');
                return;
            }

            hideListSkeleton();

            if (res.categories.length === 0) {
                $grid.html('');
                showListEmpty();
            } else {
                renderCategoryCards(res.categories);
            }

            renderListPagination(res.pagination);
            updateListCount(res.pagination.total_items, res.pagination.current_page, res.pagination.per_page);
            updateListUrlParams(false);
            window.refreshAOS();

        }).fail(function () {
            showListError('Network error. Please check your connection and try again.');
        });
    }

    function renderCategoryCards(categories) {
        var html = '';
        $.each(categories, function (i, cat) {
            html += buildCategoryCard(cat, i);
        });
        $grid.html(html);
    }

    function buildCategoryCard(cat, index) {

    var delay = Math.min((index + 1) * 50, 400);

    var html = '';

    html += '<div class="col-lg-3 col-md-4 col-sm-6 col-6" data-aos="fade-up" data-aos-delay="' + delay + '">';

    html += '    <div class="category-card" onclick="window.location.href=\'' + cat.url + '\'" style="height:240px;">';

    // ==========================
    // Category Image
    // ==========================

    if (cat.image) {
        html += '        <img src="' + cat.image + '" alt="' + escapeHtml(cat.name) + '" loading="lazy">';
    } else {
        html += '        <img src="' + BASE_URL + '/assets/img/no-category.webp" alt="' + escapeHtml(cat.name) + '" loading="lazy">';
    }

    // ==========================
    // Discount / Sale Badge
    // ==========================

    if (cat.has_offer) {
        html += '        <div class="cc-discount">' + escapeHtml(cat.offer_text || 'Sale') + '</div>';
    }

    // ==========================
    // Content
    // ==========================

    html += '        <div class="cc-content">';

    html += '            <div class="cc-name">';
    html +=                  escapeHtml(cat.name);
    html += '            </div>';

    // Info

    var info = '';

    if (cat.product_count > 0) {
        info += cat.product_count + ' Products';
    }

    if (cat.formatted_min_price) {

        if (info !== '') info += ' &bull; ';

        info += 'From ' + cat.formatted_min_price;
    }

    html += '            <div class="cc-info">' + info + '</div>';

    html += '        </div>';

    html += '    </div>';

    html += '</div>';

    return html;
}

    function renderListPagination(pagination) {
        if (!window.buildPagination) return;

        var html = window.buildPagination(pagination);
        $paginationWrap.html(html);

        window.bindPagination($paginationWrap, function (page) {
            listState.page = page;
            loadCategories();
            scrollToListTop();
        });
    }

    function updateListCount(total, page, perPage) {
        if (!$countEl.length) return;

        if (total === 0) {
            $countEl.html('No categories found');
        } else {
            var start = (page - 1) * perPage + 1;
            var end = Math.min(page * perPage, total);
            $countEl.html('Showing <strong>' + start + '–' + end + '</strong> of <strong>' + total + '</strong> categories');
        }
    }

    function showListSkeleton() {
        if ($skeletonWrap.length) {
            $skeletonWrap.show();
            $grid.hide();
            $paginationWrap.hide();
            $skeletonWrap.html(window.skeletonCards ? window.skeletonCards(listState.per_page, 'category') : '');
        } else {
            $grid.html(window.skeletonCards ? window.skeletonCards(listState.per_page, 'category') : '');
        }
    }

    function hideListSkeleton() {
        if ($skeletonWrap.length) {
            $skeletonWrap.hide();
            $grid.show();
            $paginationWrap.show();
        }
    }

    function showListError(msg) {
        hideListSkeleton();
        $grid.html(
            '<div style="grid-column:1/-1;text-align:center;padding:3rem;">' +
            '<i class="fa-solid fa-exclamation-triangle" style="font-size:2.5rem;color:var(--danger);margin-bottom:1rem;display:block;"></i>' +
            '<p style="color:var(--text-secondary);margin-bottom:1rem;">' + escapeHtml(msg) + '</p>' +
            '<button onclick="location.reload()" style="padding:0.5rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;border:none;cursor:pointer;font-family:var(--font-body);font-weight:600;">Try Again</button>' +
            '</div>'
        );
        $paginationWrap.html('');
    }

    function showListEmpty() {
        var searchMsg = listState.search ? ' for "<strong>' + escapeHtml(listState.search) + '</strong>"' : '';

        $grid.html(
            '<div style="grid-column:1/-1;text-align:center;padding:4rem 2rem;">' +
            '<i class="fa-solid fa-layer-group" style="font-size:3rem;color:var(--muted);opacity:0.3;margin-bottom:1.5rem;display:block;"></i>' +
            '<h3 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:0.5rem;">No categories found' + searchMsg + '</h3>' +
            '<p style="color:var(--text-secondary);max-width:400px;margin:0 auto 1.5rem;font-size:0.9rem;">Try a different search term.</p>' +
            '<button class="btn-reset-cat-list" style="padding:0.5rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;border:none;cursor:pointer;font-family:var(--font-body);font-weight:600;font-size:0.9rem;">Clear Search</button>' +
            '</div>'
        );
    }

    function bindListEvents() {
        // Search input (debounced)
        var searchTimer = null;
        $(document).on('input', '#category-search-input', function () {
            var val = $(this).val().trim();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                listState.search = val;
                listState.page = 1;
                updateListUrlParams(true);
                loadCategories();
            }, 500);
        });

        // Search form submit
        $(document).on('submit', '#category-search-form', function (e) {
            e.preventDefault();
            listState.search = $(this).find('#category-search-input').val().trim();
            listState.page = 1;
            updateListUrlParams(true);
            loadCategories();
        });

        // Sort dropdown
        $(document).on('change', '#category-sort-select', function () {
            listState.sort = $(this).val();
            listState.page = 1;
            updateListUrlParams(true);
            loadCategories();
        });

        // Reset button (from empty state)
        $(document).on('click', '.btn-reset-cat-list', function () {
            listState.page = 1;
            listState.search = '';
            listState.sort = 'sort_order';

            var $input = $('#category-search-input');
            if ($input.length) $input.val('');

            var $sort = $('#category-sort-select');
            if ($sort.length) $sort.val('sort_order');

            updateListUrlParams(true);
            loadCategories();
        });

        // Browser back/forward
        $(window).on('popstate', function (e) {
            if (isDetailPage) return; // Let detail page handle its own popstate

            if (e.originalEvent && e.originalEvent.state) {
                var s = e.originalEvent.state;
                listState.page = s.page || 1;
                listState.per_page = s.per_page || 12;
                listState.search = s.search || '';
                listState.sort = s.sort || 'sort_order';

                var $input = $('#category-search-input');
                if ($input.length) $input.val(listState.search);

                var $sort = $('#category-sort-select');
                if ($sort.length) $sort.val(listState.sort);

                loadCategories();
            }
        });
    }

    function scrollToListTop() {
        var offset = $('#main-header').outerHeight() + 20;
        var $target = $('#categories-toolbar');
        if ($target.length) {
            $('html, body').animate({ scrollTop: $target.offset().top - offset }, 400, 'swing');
        } else {
            $('html, body').animate({ scrollTop: 0 }, 400, 'swing');
        }
    }

    // ================================================================
    // ================== DETAIL PAGE (category.php) ==================
    // ================================================================

    function initDetailPage() {
        $grid = $('#cat-products-grid');
        $paginationWrap = $('#cat-products-pagination');
        $countEl = $('#cat-products-count');
        $skeletonWrap = $('#cat-products-skeleton');

        if (!categorySlug) {
            showError('Category slug is missing. Please check the URL.');
            return;
        }

        readDetailUrlParams();
        loadCategoryDetail();
    }

    function readDetailUrlParams() {
        var params = new URLSearchParams(window.location.search);

        if (params.get('page')) state.page = parseInt(params.get('page')) || 1;
        if (params.get('per_page')) state.per_page = parseInt(params.get('per_page')) || 12;
        if (params.get('brand_id')) state.brand_id = parseInt(params.get('brand_id')) || null;
        if (params.get('search')) state.search = params.get('search');
        if (params.get('sort')) state.sort = params.get('sort');

        if (state.search) {
            var $input = $('#cat-product-search');
            if ($input.length) $input.val(state.search);
        }

        if (state.sort) {
            var $sort = $('#cat-product-sort');
            if ($sort.length) $sort.val(state.sort);
        }
    }

    function updateDetailUrlParams(resetPage) {
        if (resetPage) state.page = 1;

        var params = new URLSearchParams();

        if (state.page > 1) params.set('page', state.page);
        if (state.per_page !== 12) params.set('per_page', state.per_page);
        if (state.brand_id) params.set('brand_id', state.brand_id);
        if (state.search) params.set('search', state.search);
        if (state.sort !== 'newest') params.set('sort', state.sort);

        var queryString = params.toString();
        var newUrl = BASE_URL + '/category/' + categorySlug + (queryString ? '?' + queryString : '');

        window.history.replaceState(state, '', newUrl);
    }

    function loadCategoryDetail() {
        showPageSkeleton();

        var params = {
            action: 'detail',
            slug: categorySlug,
            page: state.page,
            per_page: state.per_page,
            sort: state.sort
        };

        if (state.brand_id) params.brand_id = state.brand_id;
        if (state.search) params.search = state.search;

        $.getJSON(BASE_URL + '/api/site/categories.php', params, function (res) {
            if (!res.success) {
                if (res.message && res.message.indexOf('not found') !== -1) {
                    show404();
                } else {
                    showError(res.message || 'Failed to load category details.');
                }
                return;
            }

            categoryData = res;

            hidePageSkeleton();
            renderCategoryHeader(res.category);
            renderBrandPills(res.brands);

            if (res.products.length === 0) {
                $grid.html('');
                showEmptyProducts();
            } else {
                renderProducts(res.products);
            }

            renderDetailPagination(res.pagination);
            updateDetailCount(res.pagination.total_items, res.pagination.current_page, res.pagination.per_page);
            updateDetailUrlParams(false);
            bindDetailEvents();
            window.refreshAOS();

        }).fail(function () {
            showError('Network error. Please check your connection and try again.');
        });
    }

    function loadProductsOnly() {
        showProductsSkeleton();

        var params = {
            action: 'detail',
            slug: categorySlug,
            page: state.page,
            per_page: state.per_page,
            sort: state.sort
        };

        if (state.brand_id) params.brand_id = state.brand_id;
        if (state.search) params.search = state.search;

        $.getJSON(BASE_URL + '/api/site/categories.php', params, function (res) {
            if (!res.success) {
                showError('Failed to load products. Please try again.');
                return;
            }

            hideProductsSkeleton();

            if (res.products.length === 0) {
                $grid.html('');
                showEmptyProducts();
            } else {
                renderProducts(res.products);
            }

            renderDetailPagination(res.pagination);
            updateDetailCount(res.pagination.total_items, res.pagination.current_page, res.pagination.per_page);
            updateDetailUrlParams(false);
            window.refreshAOS();

        }).fail(function () {
            showError('Network error while loading products.');
        });
    }

    // ============================================================
    // RENDER CATEGORY HEADER
    // ============================================================
    function renderCategoryHeader(category) {
        // Name
        var $name = $('#cat-name');
        if ($name.length) $name.text(category.name);

        // Description
        var $desc = $('#cat-description');
        if ($desc.length && category.description) {
            $desc.html(category.description);
        }

        // Stats
        var $statProducts = $('#cat-stat-products');
        if ($statProducts.length) $statProducts.text(category.total_products || 0);

        var $statBrands = $('#cat-stat-brands');
        if ($statBrands.length) {
            var brandCount = categoryData ? categoryData.brands.length : 0;
            $statBrands.text(brandCount);
        }

        // Image (if hero/banner section exists)
        var $heroImg = $('#cat-hero-image');
        if ($heroImg.length && category.image) {
            $heroImg.attr('src', category.image).attr('alt', escapeHtml(category.name));
        }
    }

    // ============================================================
    // RENDER BRAND PILLS (filter tabs)
    // ============================================================
    function renderBrandPills(brands) {
        var $container = $('#cat-brand-pills');
        if (!$container.length) return;

        if (!brands || brands.length === 0) {
            $container.hide();
            return;
        }

        var totalProducts = categoryData ? categoryData.pagination.total_items : 0;

        var html = '';
        // "All" pill
        var allActive = !state.brand_id ? ' background:var(--primary);color:#fff;border-color:var(--primary);' : '';
        html += '<button class="fb-cat-pill cat-brand-pill' + (!state.brand_id ? ' active' : '') + '" data-brand-id="" style="' + allActive + '">';
        html += 'All <span style="opacity:0.7;margin-left:0.25rem;">' + totalProducts + '</span>';
        html += '</button>';

        $.each(brands, function (i, brand) {
            var isActive = (state.brand_id && state.brand_id === brand.id);
            var activeStyle = isActive ? ' background:var(--primary);color:#fff;border-color:var(--primary);' : '';
            html += '<button class="fb-cat-pill cat-brand-pill' + (isActive ? ' active' : '') + '" data-brand-id="' + brand.id + '" style="' + activeStyle + '">';
            if (brand.logo) {
                html += '<img src="' + brand.logo + '" alt="' + escapeHtml(brand.name) + '" style="width:16px;height:16px;object-fit:contain;vertical-align:middle;margin-right:0.3rem;border-radius:2px;"> ';
            }
            html += escapeHtml(brand.name);
            html += '</button>';
        });

        $container.html(html).show();
    }

    // ============================================================
    // RENDER PRODUCTS
    // ============================================================
    function renderProducts(products) {
        var html = '';
        $.each(products, function (i, p) {
            html += buildProductCard(p, i);
        });
        $grid.html(html);
    }

    function buildProductCard(p, index) {
        var delay = Math.min(index * 40, 400);
        var html = '<div class="product-card" data-aos="fade-up" data-aos-delay="' + delay + '">';

        // Image
        html += '<div class="pc-image">';
        if (p.image) {
            html += '<img src="' + p.image + '" alt="' + escapeHtml(p.name) + '" loading="lazy">';
        } else {
            html += '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--bg-alt);color:var(--muted);"><i class="fa-solid fa-utensils" style="font-size:2rem;"></i></div>';
        }

        if (p.has_discount && p.discount_percent > 0) {
            html += '<span class="pc-discount-badge">-' + p.discount_percent + '%</span>';
        }

        html += '<div class="pc-actions">';
        html += '  <button class="pc-action-btn btn-quick-view" data-slug="' + p.slug + '" title="Quick View"><i class="fa-solid fa-eye"></i></button>';
        html += '  <button class="pc-action-btn btn-copy-link" data-url="' + p.url + '" title="Copy Link"><i class="fa-solid fa-link"></i></button>';
        html += '</div>';

        html += '</div>';

        // Body
        html += '<div class="pc-body">';

        // Brand
        html += '<div class="pc-brand">';
        if (p.brand_logo) {
            html += '<img src="' + p.brand_logo + '" alt="' + escapeHtml(p.brand_name) + '" loading="lazy">';
        }
        html += '<a href="' + p.brand_url + '">' + escapeHtml(p.brand_name) + '</a>';
        html += '</div>';

        // Name
        html += '<h4 class="pc-name"><a href="' + p.url + '" style="color:var(--text);transition:color 0.3s;">' + escapeHtml(p.name) + '</a></h4>';

        // Category
        if (p.category_name) {
            html += '<div class="pc-category">' + escapeHtml(p.category_name) + '</div>';
        }

        // Meta
        if (p.calories > 0) {
            html += '<div class="pc-meta"><span><i class="fa-solid fa-fire"></i> ' + p.calories + ' cal</span></div>';
        }

        // Short description (truncated to 80 chars) + view more arrow
        if (p.short_description) {
            html += '<p class="pc-desc">' + escapeHtml(truncateDesc(p.short_description, 80)) +
                ' <a href="' + p.url + '" class="pc-desc-more">View Details <i class="fa-solid fa-arrow-right"></i></a></p>';
        }

        // Footer
        html += '<div class="pc-footer">';
        html += '<div class="pc-prices">';
        if (p.has_discount && p.formatted_discount) {
            html += '<span class="pc-original-price">' + p.formatted_regular + '</span>';
            html += '<span class="pc-current-price">' + p.formatted_discount + '</span>';
        } else if (p.formatted_regular) {
            html += '<span class="pc-current-price">' + p.formatted_regular + '</span>';
        } else {
            html += '<span class="pc-current-price" style="font-size:0.85rem;color:var(--muted);">N/A</span>';
        }
        html += '</div>';
        html += '<a href="' + p.url + '" class="pc-view-btn">View</a>';
        html += '</div>';

        html += '</div>';
        html += '</div>';

        return html;
    }

    // Quick view & copy link delegation
    $(document).on('click', '.btn-quick-view', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var slug = $(this).data('slug');
        if (slug && window.openQuickView) {
            window.openQuickView(slug);
        }
    });

    $(document).on('click', '.btn-copy-link', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var url = $(this).data('url');
        if (url && window.copyToClipboard) {
            window.copyToClipboard(url, 'Product link copied!');
        }
    });

    // ============================================================
    // RENDER PAGINATION & COUNT (Detail page)
    // ============================================================
    function renderDetailPagination(pagination) {
        if (!window.buildPagination) return;

        var html = window.buildPagination(pagination);
        $paginationWrap.html(html);

        window.bindPagination($paginationWrap, function (page) {
            state.page = page;
            loadProductsOnly();
            scrollToProducts();
        });
    }

    function updateDetailCount(total, page, perPage) {
        if (!$countEl.length) return;

        if (total === 0) {
            $countEl.html('No products found');
        } else {
            var start = (page - 1) * perPage + 1;
            var end = Math.min(page * perPage, total);
            $countEl.html('Showing <strong>' + start + '–' + end + '</strong> of <strong>' + total + '</strong> products');
        }
    }

    // ============================================================
    // SKELETON / ERROR / EMPTY STATES (Detail page)
    // ============================================================
    function showPageSkeleton() {
        var $skeleton = $('#cat-page-skeleton');
        if ($skeleton.length) {
            $skeleton.show();
            $('#cat-detail-content').hide();
        }
    }

    function hidePageSkeleton() {
        var $skeleton = $('#cat-page-skeleton');
        if ($skeleton.length) {
            $skeleton.hide();
            $('#cat-detail-content').show();
        }
    }

    function showProductsSkeleton() {
        if ($skeletonWrap.length) {
            $skeletonWrap.show();
            $grid.hide();
            $paginationWrap.hide();
            $skeletonWrap.html(window.skeletonCards ? window.skeletonCards(state.per_page, 'product') : '');
        } else {
            $grid.html(window.skeletonCards ? window.skeletonCards(state.per_page, 'product') : '');
        }
    }

    function hideProductsSkeleton() {
        if ($skeletonWrap.length) {
            $skeletonWrap.hide();
            $grid.show();
            $paginationWrap.show();
        }
    }

    function showError(msg) {
        // Try detail page skeleton first
        var $skeleton = $('#cat-page-skeleton');
        if ($skeleton.length && isDetailPage) {
            hidePageSkeleton();
            var $content = $('#cat-detail-content');
            if ($content.length) {
                $content.html(
                    '<div style="text-align:center;padding:4rem 2rem;">' +
                    '<i class="fa-solid fa-exclamation-triangle" style="font-size:3rem;color:var(--danger);margin-bottom:1.5rem;display:block;"></i>' +
                    '<h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:0.75rem;">Something went wrong</h2>' +
                    '<p style="color:var(--text-secondary);max-width:450px;margin:0 auto 1.5rem;">' + escapeHtml(msg) + '</p>' +
                    '<a href="' + BASE_URL + '/categories" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;">' +
                    '<i class="fa-solid fa-arrow-left" style="font-size:0.75rem;"></i> Back to Categories</a>' +
                    '</div>'
                );
                return;
            }
        }

        // Fallback for list page
        if ($grid && $grid.length && !isDetailPage) {
            showListError(msg);
            return;
        }

        // Ultimate fallback
        $('main').html(
            '<div class="error-page">' +
            '<div class="error-code">500</div>' +
            '<h2 class="error-title">Something went wrong</h2>' +
            '<p class="error-desc">' + escapeHtml(msg) + '</p>' +
            '<a href="' + BASE_URL + '/categories" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;">Back to Categories</a>' +
            '</div>'
        );
    }

    function show404() {
        hidePageSkeleton();
        var $content = $('#cat-detail-content');
        if ($content.length) {
            $content.html(
                '<div class="error-page">' +
                '<div class="error-code">404</div>' +
                '<h2 class="error-title">Category Not Found</h2>' +
                '<p class="error-desc">The category you\'re looking for doesn\'t exist or has been removed.</p>' +
                '<a href="' + BASE_URL + '/categories" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;">' +
                '<i class="fa-solid fa-arrow-left" style="font-size:0.75rem;"></i> Browse All Categories</a>' +
                '</div>'
            );
        }
    }

    function showEmptyProducts() {
        var searchMsg = state.search ? ' matching "<strong>' + escapeHtml(state.search) + '</strong>"' : '';
        var brandMsg = state.brand_id ? ' from this brand' : '';

        $grid.html(
            '<div style="grid-column:1/-1;text-align:center;padding:3rem 2rem;">' +
            '<i class="fa-solid fa-utensils" style="font-size:2.5rem;color:var(--muted);opacity:0.3;margin-bottom:1rem;display:block;"></i>' +
            '<h3 style="font-family:var(--font-display);font-size:1.2rem;margin-bottom:0.5rem;">No products found' + searchMsg + brandMsg + '</h3>' +
            '<p style="color:var(--text-secondary);font-size:0.9rem;">Try a different filter or search term.</p>' +
            '</div>'
        );
    }

    // ============================================================
    // BIND DETAIL PAGE EVENTS
    // ============================================================
    var detailEventsBound = false;

    function bindDetailEvents() {
        if (detailEventsBound) return;
        detailEventsBound = true;

        // --- Brand pills click ---
        $(document).on('click', '.cat-brand-pill', function () {
            var brandId = $(this).data('brand-id');
            state.brand_id = brandId ? parseInt(brandId) : null;
            state.page = 1;

            // Update pill styles
            $('.cat-brand-pill').removeClass('active').css({
                'background': '',
                'color': '',
                'border-color': ''
            });
            $(this).addClass('active').css({
                'background': 'var(--primary)',
                'color': '#fff',
                'border-color': 'var(--primary)'
            });

            updateDetailUrlParams(true);
            loadProductsOnly();
        });

        // --- Search input (debounced) ---
        var searchTimer = null;
        $(document).on('input', '#cat-product-search', function () {
            var val = $(this).val().trim();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                state.search = val;
                state.page = 1;
                updateDetailUrlParams(true);
                loadProductsOnly();
            }, 500);
        });

        // --- Search form submit ---
        $(document).on('submit', '#cat-product-search-form', function (e) {
            e.preventDefault();
            state.search = $(this).find('#cat-product-search').val().trim();
            state.page = 1;
            updateDetailUrlParams(true);
            loadProductsOnly();
        });

        // --- Sort dropdown ---
        $(document).on('change', '#cat-product-sort', function () {
            state.sort = $(this).val();
            state.page = 1;
            updateDetailUrlParams(true);
            loadProductsOnly();
        });

        // --- Browser back/forward ---
        $(window).on('popstate', function (e) {
            if (!isDetailPage) return;

            if (e.originalEvent && e.originalEvent.state) {
                var s = e.originalEvent.state;
                state.page = s.page || 1;
                state.per_page = s.per_page || 12;
                state.brand_id = s.brand_id || null;
                state.search = s.search || '';
                state.sort = s.sort || 'newest';

                // Update UI
                var $input = $('#cat-product-search');
                if ($input.length) $input.val(state.search);

                var $sort = $('#cat-product-sort');
                if ($sort.length) $sort.val(state.sort);

                // Update brand pills
                $('.cat-brand-pill').removeClass('active').css({
                    'background': '',
                    'color': '',
                    'border-color': ''
                });
                var $activePill = $('.cat-brand-pill[data-brand-id="' + (state.brand_id || '') + '"]');
                if ($activePill.length) {
                    $activePill.addClass('active').css({
                        'background': 'var(--primary)',
                        'color': '#fff',
                        'border-color': 'var(--primary)'
                    });
                } else {
                    $('.cat-brand-pill[data-brand-id=""]').addClass('active').css({
                        'background': 'var(--primary)',
                        'color': '#fff',
                        'border-color': 'var(--primary)'
                    });
                }

                loadProductsOnly();
            }
        });
    }

    function scrollToProducts() {
        var offset = $('#main-header').outerHeight() + 20;
        var $target = $('#cat-products-toolbar');
        if ($target.length) {
            $('html, body').animate({ scrollTop: $target.offset().top - offset }, 400, 'swing');
        }
    }

    // ============================================================
    // UTILITY
    // ============================================================
    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function truncateDesc(text, maxLen) {
        if (!text) return '';
        var plain = $('<div>').html(text).text().trim();
        if (plain.length <= maxLen) return plain;
        return plain.substring(0, maxLen).trim() + '...';
    }

})();