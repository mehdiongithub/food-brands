/**
 * category-detail.js — Loaded on category.php ONLY
 * Handles: category header, sidebar filters (brand checkboxes + price range),
 * products grid with search/sort, pagination, URL params sync
 */

(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '/food-brands';
    var categorySlug = window.CATEGORY_SLUG || '';

    // Products filter state
    var state = {
        page: 1,
        per_page: 12,
        brand_ids: [],   // multi-select brand checkboxes
        max_price: null, // price range slider
        search: '',
        sort: 'newest'
    };

    // Pending sidebar selections (only committed to `state` on Apply)
    var pending = {
        brand_ids: [],
        max_price: null
    };

    // DOM references
    var $grid, $paginationWrap, $countEl, $skeletonWrap;
    var categoryData = null;
    var priceBounds = { min: 0, max: 999 };

    $(document).ready(function () {
        initDetailPage();
    });

    // ================================================================
    // DETAIL PAGE (category.php)
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
        if (params.get('brand_ids')) {
            state.brand_ids = params.get('brand_ids').split(',').map(function (v) { return parseInt(v); }).filter(Boolean);
        }
        if (params.get('max_price')) state.max_price = parseFloat(params.get('max_price'));
        if (params.get('search')) state.search = params.get('search');
        if (params.get('sort')) state.sort = params.get('sort');

        pending.brand_ids = state.brand_ids.slice();
        pending.max_price = state.max_price;

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
        if (state.brand_ids.length) params.set('brand_ids', state.brand_ids.join(','));
        if (state.max_price !== null && state.max_price !== undefined) params.set('max_price', state.max_price);
        if (state.search) params.set('search', state.search);
        if (state.sort !== 'newest') params.set('sort', state.sort);

        var queryString = params.toString();
        var newUrl = BASE_URL + '/category/' + categorySlug + (queryString ? '?' + queryString : '');

        window.history.replaceState(state, '', newUrl);
    }

    function buildRequestParams() {
        var params = {
            action: 'detail',
            slug: categorySlug,
            page: state.page,
            per_page: state.per_page,
            sort: state.sort
        };

        if (state.brand_ids.length) params.brand_ids = state.brand_ids.join(',');
        if (state.max_price !== null && state.max_price !== undefined) params.max_price = state.max_price;
        if (state.search) params.search = state.search;

        return params;
    }

    function loadCategoryDetail() {
        showPageSkeleton();

        $.getJSON(BASE_URL + '/api/site/categories.php', buildRequestParams(), function (res) {
            if (!res.success) {
                if (res.message && res.message.indexOf('not found') !== -1) {
                    show404();
                } else {
                    showError(res.message || 'Failed to load category details.');
                }
                return;
            }

            categoryData = res;

            if (res.price_bounds) {
                priceBounds.min = res.price_bounds.min || 0;
                priceBounds.max = res.price_bounds.max || 0;
            }

            hidePageSkeleton();
            renderCategoryHeader(res.category);
            renderBrandFilters(res.brands);
            renderPriceRange();

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
            bindDetailEvents();
            window.refreshAOS();

        }).fail(function () {
            showError('Network error. Please check your connection and try again.');
        });
    }

    function loadProductsOnly() {
        showProductsSkeleton();

        $.getJSON(BASE_URL + '/api/site/categories.php', buildRequestParams(), function (res) {
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
        var $name = $('#cat-name');
        if ($name.length) $name.text(category.name);

        var $desc = $('#cat-description');

        if ($desc.length) {
            $desc.html(
                'Discover the best <strong>' + category.name + '</strong> from top food brands. Compare prices, explore menu items, check the latest deals, and find your favorite ' +
                category.name.toLowerCase() +
                ' available in different brands.'
            );
        }

        var $statProducts = $('#cat-stat-products');
        if ($statProducts.length) $statProducts.text(category.total_products || 0);

        var $statBrands = $('#cat-stat-brands');
        if ($statBrands.length) {
            var brandCount = categoryData ? categoryData.brands.length : 0;
            $statBrands.text(brandCount);
        }

        var $heroImg = $('#cat-hero-image');
        if ($heroImg.length && category.image) {
            $heroImg.attr('src', category.image).attr('alt', escapeHtml(category.name));
        }
    }

    // ============================================================
    // RENDER SIDEBAR — BRAND CHECKBOXES (desktop + mobile)
    // ============================================================
    function renderBrandFilters(brands) {
        var html = '';

        if (!brands || brands.length === 0) {
            html = '<div style="font-size:0.82rem;color:var(--muted);">No brands available.</div>';
        } else {
            $.each(brands, function (i, brand) {
                var checked = pending.brand_ids.indexOf(brand.id) !== -1 ? 'checked' : '';
                html += '<label class="filter-option">';
                html += '  <input type="checkbox" name="brand_id" value="' + brand.id + '" ' + checked + '>';
                if (brand.logo) {
                    html += '  <img src="' + brand.logo + '" alt="' + escapeHtml(brand.name) + '" style="width:18px;height:18px;object-fit:contain;border-radius:2px;">';
                }
                html += '  <span>' + escapeHtml(brand.name) + '</span>';
                html += '  <span class="count">' + brand.product_count + '</span>';
                html += '</label>';
            });
        }

        $('#cat-filter-brands').html(html);
        $('#cat-filter-brands-mobile').html(html);
    }

    // ============================================================
    // RENDER SIDEBAR — PRICE RANGE SLIDER (desktop + mobile)
    // ============================================================
    function renderPriceRange() {
        var min = priceBounds.min || 0;
        var max = priceBounds.max > min ? priceBounds.max : min + 1;
        var current = (pending.max_price !== null && pending.max_price !== undefined) ? pending.max_price : max;

        $('#cat-filter-price, #cat-filter-price-mobile').attr('min', min).attr('max', max).val(current);
        $('#cat-price-min-label, #cat-price-min-label-mobile').text(formatPrice(min));
        $('#cat-price-max-label, #cat-price-max-label-mobile').text(formatPrice(current));
    }

    function formatPrice(val) {
        var symbol = (categoryData && categoryData.products.length && categoryData.products[0].formatted_regular)
            ? categoryData.products[0].formatted_regular.replace(/[0-9.,]/g, '')
            : '$';
        return symbol + Math.round(val);
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

        // Wrap in a Bootstrap column so cards lay out correctly in the row grid
        // (same responsive classes as the brand page: full width on mobile,
        // 2 per row on tablets, 3 per row on desktop)
        var html = '<div class="col-lg-4 col-md-6 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="' + delay + '">';
        html += '<div class="product-card">';

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

        // Brand (small logo + name)
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

        // Calories
        if (p.calories > 0) {
            html += '<div class="pc-meta"><span><i class="fa-solid fa-fire"></i> ' + p.calories + ' cal</span></div>';
        }

        // Footer: price + View Details
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
        html += '<a href="' + p.url + '" class="pc-view-btn">View Details</a>';
        html += '</div>';

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
    // RENDER PAGINATION & COUNT
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
    // SKELETON / ERROR / EMPTY STATES
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
        var $skeleton = $('#cat-page-skeleton');
        if ($skeleton.length) {
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

        // Ultimate fallback
        $('main').html(
            '<div class="error-page">' +
            '<div class="error-code">500</div>' +
            '<h2 class="error-title">Something went wrong</h2>' +
            '<p class="error-desc">' + escapeHtml(msg) + '</p>' +
            '<a href="' + BASE_URL + '/categories" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;">Back to Categories</a>' +
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
        var brandMsg = state.brand_ids.length ? ' for the selected brands' : '';

        $grid.html(
            '<div style="grid-column:1/-1;text-align:center;padding:3rem 2rem;">' +
            '<i class="fa-solid fa-utensils" style="font-size:2.5rem;color:var(--muted);opacity:0.3;margin-bottom:1rem;display:block;"></i>' +
            '<h3 style="font-family:var(--font-display);font-size:1.2rem;margin-bottom:0.5rem;">No products found' + searchMsg + brandMsg + '</h3>' +
            '<p style="color:var(--text-secondary);font-size:0.9rem;">Try a different filter or search term.</p>' +
            '<button class="btn-reset-cat-filters filter-reset-btn" style="max-width:200px;margin:1rem auto 0;">Clear Filters</button>' +
            '</div>'
        );
    }

    // ============================================================
    // BIND EVENTS
    // ============================================================
    var detailEventsBound = false;

    function bindDetailEvents() {
        if (detailEventsBound) return;
        detailEventsBound = true;

        // --- Brand checkboxes (desktop + mobile, multi-select, pending only) ---
        $(document).on('change', '#cat-filter-brands input[type="checkbox"], #cat-filter-brands-mobile input[type="checkbox"]', function () {
            var val = parseInt($(this).val());
            var checked = $(this).is(':checked');

            if (checked && pending.brand_ids.indexOf(val) === -1) {
                pending.brand_ids.push(val);
            } else if (!checked) {
                pending.brand_ids = pending.brand_ids.filter(function (v) { return v !== val; });
            }

            // Keep desktop & mobile checkbox sets in sync
            $('#cat-filter-brands input[value="' + val + '"], #cat-filter-brands-mobile input[value="' + val + '"]').prop('checked', checked);
        });

        // --- Price range slider (desktop + mobile, pending only) ---
        $(document).on('input', '#cat-filter-price, #cat-filter-price-mobile', function () {
            var val = parseFloat($(this).val());
            pending.max_price = val;
            $('#cat-filter-price, #cat-filter-price-mobile').val(val);
            $('#cat-price-max-label, #cat-price-max-label-mobile').text(formatPrice(val));
        });

        // --- Search input (debounced, applies immediately) ---
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

        $(document).on('submit', '#cat-product-search-form', function (e) {
            e.preventDefault();
            state.search = $(this).find('#cat-product-search').val().trim();
            state.page = 1;
            updateDetailUrlParams(true);
            loadProductsOnly();
        });

        // --- Sort dropdown (applies immediately) ---
        $(document).on('change', '#cat-product-sort', function () {
            state.sort = $(this).val();
            state.page = 1;
            updateDetailUrlParams(true);
            loadProductsOnly();
        });

        // --- Apply Filters button (desktop + mobile) ---
        $(document).on('click', '#btn-cat-apply-filters, #btn-cat-apply-filters-mobile', function () {
            state.brand_ids = pending.brand_ids.slice();
            state.max_price = pending.max_price;
            state.page = 1;
            updateDetailUrlParams(true);
            loadProductsOnly();
            closeMobileFilter();
        });

        // --- Reset Filters button (desktop + mobile + empty-state) ---
        $(document).on('click', '#btn-cat-reset-filters, #btn-cat-reset-filters-mobile, .btn-reset-cat-filters', function () {
            state.brand_ids = [];
            state.max_price = null;
            state.search = '';
            state.sort = 'newest';
            state.page = 1;

            pending.brand_ids = [];
            pending.max_price = null;

            $('#cat-filter-brands input[type="checkbox"], #cat-filter-brands-mobile input[type="checkbox"]').prop('checked', false);
            $('#cat-product-search').val('');
            $('#cat-product-sort').val('newest');
            renderPriceRange();

            updateDetailUrlParams(true);
            loadProductsOnly();
            closeMobileFilter();
        });

        // --- Mobile filter drawer open/close ---
        $(document).on('click', '#btn-mobile-cat-filter', function () {
            $('#cat-mobile-filter-panel').css('left', '0');
            $('#cat-mobile-filter-overlay').show();
            $('body').css('overflow', 'hidden');
        });

        $(document).on('click', '#btn-close-cat-mobile-filter, #cat-mobile-filter-overlay', function () {
            closeMobileFilter();
        });

        // --- Browser back/forward ---
        $(window).on('popstate', function (e) {
            if (e.originalEvent && e.originalEvent.state) {
                var s = e.originalEvent.state;
                state.page = s.page || 1;
                state.per_page = s.per_page || 12;
                state.brand_ids = s.brand_ids || [];
                state.max_price = (s.max_price !== undefined) ? s.max_price : null;
                state.search = s.search || '';
                state.sort = s.sort || 'newest';

                pending.brand_ids = state.brand_ids.slice();
                pending.max_price = state.max_price;

                var $input = $('#cat-product-search');
                if ($input.length) $input.val(state.search);

                var $sort = $('#cat-product-sort');
                if ($sort.length) $sort.val(state.sort);

                $('#cat-filter-brands input[type="checkbox"], #cat-filter-brands-mobile input[type="checkbox"]').each(function () {
                    var val = parseInt($(this).val());
                    $(this).prop('checked', state.brand_ids.indexOf(val) !== -1);
                });
                renderPriceRange();

                loadProductsOnly();
            }
        });
    }

    function closeMobileFilter() {
        $('#cat-mobile-filter-panel').css('left', '-320px');
        $('#cat-mobile-filter-overlay').hide();
        $('body').css('overflow', '');
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

})();