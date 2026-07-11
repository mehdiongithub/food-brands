/**
 * brands.js — Loaded ONLY on brands.php (Brands listing page)
 * Handles: brand grid, filters (category, country, search, sort),
 * pagination, URL params sync, skeleton loaders, empty states
 */

(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '/food-brands';

    // Current filter state
    var state = {
        page: 1,
        per_page: 12,
        search: '',
        category_id: null,
        country_id: null,
        sort: 'newest'
    };

    // DOM references
    var $grid, $paginationWrap, $countEl, $skeletonWrap;

    $(document).ready(function () {
        $grid = $('#brands-grid');
        $paginationWrap = $('#brands-pagination');
        $countEl = $('#brands-count');
        $skeletonWrap = $('#brands-skeleton');

        // Read initial state from URL params
        readUrlParams();

        // Load filters sidebar first
        loadFilters();

        // Then load brands
        loadBrands();

        // Bind all events
        bindEvents();
    });

    // ============================================================
    // READ URL PARAMS (for SEO-friendly filter URLs)
    // ============================================================
    function readUrlParams() {
        var params = new URLSearchParams(window.location.search);

        if (params.get('page')) state.page = parseInt(params.get('page')) || 1;
        if (params.get('per_page')) state.per_page = parseInt(params.get('per_page')) || 12;
        if (params.get('search')) state.search = params.get('search');
        if (params.get('category_id')) state.category_id = parseInt(params.get('category_id')) || null;
        if (params.get('country_id')) state.country_id = parseInt(params.get('country_id')) || null;
        if (params.get('sort')) state.sort = params.get('sort');

        // Populate search input if value exists
        if (state.search) {
            var $searchInput = $('#brand-search-input');
            if ($searchInput.length) $searchInput.val(state.search);
        }

        // Populate sort dropdown
        if (state.sort) {
            var $sortSelect = $('#brand-sort-select');
            if ($sortSelect.length) $sortSelect.val(state.sort);
        }
    }

    // ============================================================
    // UPDATE URL PARAMS (without page reload)
    // ============================================================
    function updateUrlParams(resetPage) {
        if (resetPage) state.page = 1;

        var params = new URLSearchParams();

        if (state.page > 1) params.set('page', state.page);
        if (state.per_page !== 12) params.set('per_page', state.per_page);
        if (state.search) params.set('search', state.search);
        if (state.category_id) params.set('category_id', state.category_id);
        if (state.country_id) params.set('country_id', state.country_id);
        if (state.sort !== 'newest') params.set('sort', state.sort);

        var queryString = params.toString();
        var newUrl = BASE_URL + '/brands' + (queryString ? '?' + queryString : '');

        window.history.replaceState(state, '', newUrl);
    }

    // ============================================================
    // LOAD FILTERS (sidebar categories + countries)
    // ============================================================
    function loadFilters() {
        $.getJSON(BASE_URL + '/api/site/brands.php', {
            action: 'list',
            page: 1,
            per_page: 1
        }, function (res) {
            if (!res.success || !res.filters) return;

            renderCategoryFilters(res.filters.categories, res.filters.active_category);
            setActiveSort(res.filters.active_sort);

        }).fail(function () {
            console.warn('Failed to load brand filters.');
        });
    }

    function renderCategoryFilters(categories, activeId) {
        var $list = $('#filter-categories');
        if (!$list.length) return;

        if (categories.length === 0) {
            $list.html('<div style="font-size:0.82rem;color:var(--muted);">No categories available.</div>');
            return;
        }

        var html = '';
        $.each(categories, function (i, cat) {
            var checked = (activeId && activeId === cat.id) ? 'checked' : '';
            html += '<label class="filter-option">';
            html += '  <input type="checkbox" name="category_id" value="' + cat.id + '" ' + checked + '>';
            html += '  <span>' + escapeHtml(cat.name) + '</span>';
            html += '  <span class="count">' + cat.brand_count + '</span>';
            html += '</label>';
        });

        $list.html(html);
    }

    function setActiveSort(sort) {
        var $select = $('#brand-sort-select');
        if ($select.length && sort) {
            $select.val(sort);
            state.sort = sort;
        }
    }

    // ============================================================
    // LOAD BRANDS (main data fetch)
    // ============================================================
    function loadBrands() {
        // Show skeleton
        showSkeleton();

        // Build request params
        var params = {
            action: 'list',
            page: state.page,
            per_page: state.per_page,
            sort: state.sort
        };

        if (state.search) params.search = state.search;
        if (state.category_id) params.category_id = state.category_id;
        if (state.country_id) params.country_id = state.country_id;

        $.getJSON(BASE_URL + '/api/site/brands.php', params, function (res) {
            if (!res.success) {
                showError('Failed to load brands. Please try again.');
                return;
            }

            hideSkeleton();

            if (res.brands.length === 0) {
                $grid.html('');
                showEmptyState();
            } else {
                renderBrands(res.brands);
            }

            renderPagination(res.pagination);
            updateCount(res.pagination.total_items, res.pagination.current_page, res.pagination.per_page);
            updateUrlParams(false);
            window.refreshAOS();

        }).fail(function () {
            showError('Network error. Please check your connection and try again.');
        });
    }

    // ============================================================
    // RENDER BRANDS
    // ============================================================
    function renderBrands(brands) {
        var html = '';
        $.each(brands, function (i, brand) {
            html += buildBrandCard(brand, i);
        });
        $grid.html(html);
    }

    function buildBrandCard(brand, index) {
    var delay = Math.min((index + 1) * 50, 400);

    var html = '';

    html += '<div class="col-lg-4 col-md-6 col-12 mb-4">';
    html += '  <div class="product-card" data-aos="fade-up" data-aos-delay="' + delay + '">';

    // ==========================
    // Product / Cover Image
    // ==========================
    html += '      <div class="pc-image">';
    html += '          <a href="' + brand.url + '">';

    if (brand.cover_image) {
        html += '              <img src="' + brand.cover_image + '" alt="' + escapeHtml(brand.name) + '" loading="lazy">';
    } else {
        html += '              <img src="assets/img/no-image.webp" alt="' + escapeHtml(brand.name) + '" loading="lazy">';
    }

    html += '          </a>';

    // Discount Badge (Optional)
    if (brand.discount_percentage && brand.discount_percentage > 0) {
        html += '      <div class="pc-discount-badge">-' + brand.discount_percentage + '%</div>';
    }

    // Action Buttons
    html += '          <div class="pc-actions">';
    html += '              <button class="pc-action-btn" onclick="toggleFavorite(\'' + brand.slug + '\')" aria-label="Add to favorites">';
    html += '                  <i class="far fa-heart"></i>';
    html += '              </button>';

    html += '              <button class="pc-action-btn" onclick="quickView(\'' + brand.slug + '\')" aria-label="Quick View">';
    html += '                  <i class="far fa-eye"></i>';
    html += '              </button>';

    html += '              <button class="pc-action-btn" onclick="shareProduct(\'' + brand.slug + '\')" aria-label="Share">';
    html += '                  <i class="fas fa-share-alt"></i>';
    html += '              </button>';
    html += '          </div>';

    html += '      </div>';

    // ==========================
    // Card Body
    // ==========================
    html += '      <div class="pc-body">';

    // Brand Logo
    html += '          <div class="pc-brand">';

    if (brand.logo) {
        html += '              <img src="' + brand.logo + '" alt="' + escapeHtml(brand.name) + '" loading="lazy">';
    } else {
        html += '              <img src="assets/img/default-logo.webp" alt="' + escapeHtml(brand.name) + '" loading="lazy">';
    }

    html += '              <span>' + escapeHtml(brand.name) + '</span>';
    html += '          </div>';

    // Brand Name
    html += '          <div class="pc-name">';
    html += '              <a href="' + brand.url + '">' + escapeHtml(brand.name) + '</a>';
    html += '          </div>';

    // Short Description
    html += '          <div class="pc-category">';
    html +=               (brand.short_description ? escapeHtml(brand.short_description) : '');
    html += '          </div>';

    // Meta
    html += '          <div class="pc-meta">';
    html += '              <span><i class="fas fa-box"></i> ' + brand.product_count + ' Products</span>';

    if (brand.founded_year) {
        html += '              <span><i class="far fa-calendar"></i> Est. ' + brand.founded_year + '</span>';
    }

    html += '          </div>';

    // Footer
    html += '          <div class="pc-footer">';

    html += '              <div class="pc-prices">';

    if (brand.formatted_old_price) {
        html += '                  <span class="pc-original-price">' + brand.formatted_old_price + '</span>';
    }

    if (brand.formatted_min_price) {
        html += '                  <span class="pc-current-price">' + brand.formatted_min_price + '</span>';
    } else {
        html += '                  <span class="pc-current-price">Price Available</span>';
    }

    html += '              </div>';

    html += '              <a href="' + brand.url + '" class="pc-view-btn">';
    html += '                  View Menu';
    html += '              </a>';

    html += '          </div>';

    html += '      </div>';

    html += '  </div>';
    html += '</div>';

    return html;
}

    // ============================================================
    // RENDER PAGINATION
    // ============================================================
    function renderPagination(pagination) {
        if (!window.buildPagination) return;

        var html = window.buildPagination(pagination);
        $paginationWrap.html(html);

        window.bindPagination($paginationWrap, function (page) {
            state.page = page;
            loadBrands();
            scrollToTop();
        });
    }

    // ============================================================
    // UPDATE COUNT DISPLAY
    // ============================================================
    function updateCount(total, page, perPage) {
        if (!$countEl.length) return;

        if (total === 0) {
            $countEl.html('No brands found');
        } else {
            var start = (page - 1) * perPage + 1;
            var end = Math.min(page * perPage, total);
            $countEl.html('Showing <strong>' + start + '–' + end + '</strong> of <strong>' + total + '</strong> brands');
        }
    }

    // ============================================================
    // SKELETON / ERROR / EMPTY STATES
    // ============================================================
    function showSkeleton() {
        if ($skeletonWrap.length) {
            $skeletonWrap.show();
            $grid.hide();
            $paginationWrap.hide();
            $skeletonWrap.html(window.skeletonCards ? window.skeletonCards(state.per_page, 'brand') : '');
        } else {
            $grid.html(window.skeletonCards ? window.skeletonCards(state.per_page, 'brand') : '');
        }
    }

    function hideSkeleton() {
        if ($skeletonWrap.length) {
            $skeletonWrap.hide();
            $grid.show();
            $paginationWrap.show();
        }
    }

    function showError(msg) {
        hideSkeleton();
        $grid.html(
            '<div style="grid-column:1/-1;text-align:center;padding:3rem;">' +
            '<i class="fa-solid fa-exclamation-triangle" style="font-size:2.5rem;color:var(--danger);margin-bottom:1rem;display:block;"></i>' +
            '<p style="color:var(--text-secondary);margin-bottom:1rem;">' + escapeHtml(msg) + '</p>' +
            '<button onclick="location.reload()" style="padding:0.5rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;border:none;cursor:pointer;font-family:var(--font-body);font-weight:600;">Try Again</button>' +
            '</div>'
        );
        $paginationWrap.html('');
    }

    function showEmptyState() {
        var searchMsg = state.search ? ' for "<strong>' + escapeHtml(state.search) + '</strong>"' : '';

        $grid.html(
            '<div style="grid-column:1/-1;text-align:center;padding:4rem 2rem;">' +
            '<i class="fa-solid fa-building" style="font-size:3rem;color:var(--muted);opacity:0.3;margin-bottom:1.5rem;display:block;"></i>' +
            '<h3 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:0.5rem;">No brands found' + searchMsg + '</h3>' +
            '<p style="color:var(--text-secondary);max-width:400px;margin:0 auto 1.5rem;font-size:0.9rem;">Try adjusting your filters or search term to find what you\'re looking for.</p>' +
            '<button class="btn-reset-filters" style="padding:0.5rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;border:none;cursor:pointer;font-family:var(--font-body);font-weight:600;font-size:0.9rem;">Clear All Filters</button>' +
            '</div>'
        );
    }

    // ============================================================
    // BIND ALL EVENTS
    // ============================================================
    function bindEvents() {

        // --- Search input (debounced 500ms) ---
        var searchTimer = null;
        $(document).on('input', '#brand-search-input', function () {
            var val = $(this).val().trim();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                state.search = val;
                state.page = 1;
                updateUrlParams(true);
                loadBrands();
            }, 500);
        });

        // --- Search form submit ---
        $(document).on('submit', '#brand-search-form', function (e) {
            e.preventDefault();
            var val = $(this).find('#brand-search-input').val().trim();
            state.search = val;
            state.page = 1;
            updateUrlParams(true);
            loadBrands();
        });

        // --- Sort dropdown ---
        $(document).on('change', '#brand-sort-select', function () {
            state.sort = $(this).val();
            state.page = 1;
            updateUrlParams(true);
            loadBrands();
        });

        // --- Category filter checkboxes (single select) ---
        $(document).on('change', '#filter-categories input[type="checkbox"]', function () {
            var $all = $('#filter-categories input[type="checkbox"]');
            if ($(this).is(':checked')) {
                $all.not(this).prop('checked', false);
                state.category_id = parseInt($(this).val());
            } else {
                state.category_id = null;
            }
        });

        // --- Apply Filters button ---
        $(document).on('click', '#btn-apply-filters', function () {
            state.page = 1;
            updateUrlParams(true);
            loadBrands();

            // Close filter drawer if open
            $('#mobile-filter-panel').removeClass('show');
            $('#mobile-filter-overlay').removeClass('show');
            $('body').css('overflow', '');
        });

        // --- Reset Filters button ---
        $(document).on('click', '#btn-reset-filters, .btn-reset-filters', function () {
            // Reset state
            state.page = 1;
            state.search = '';
            state.category_id = null;
            state.country_id = null;
            state.sort = 'newest';

            // Reset UI
            var $searchInput = $('#brand-search-input');
            if ($searchInput.length) $searchInput.val('');

            var $sortSelect = $('#brand-sort-select');
            if ($sortSelect.length) $sortSelect.val('newest');

            $('#filter-categories input[type="checkbox"]').prop('checked', false);

            // Update URL and reload
            updateUrlParams(true);
            loadBrands();

            // Close filter drawer if open
            $('#mobile-filter-panel').removeClass('show');
            $('#mobile-filter-overlay').removeClass('show');
            $('body').css('overflow', '');
        });

        // --- Filter drawer toggle (used at every screen size) ---
        $(document).on('click', '#btn-mobile-filter', function () {
            $('#mobile-filter-panel').addClass('show');
            $('#mobile-filter-overlay').addClass('show');
            $('body').css('overflow', 'hidden');
        });

        $(document).on('click', '#btn-close-mobile-filter, #mobile-filter-overlay', function () {
            $('#mobile-filter-panel').removeClass('show');
            $('#mobile-filter-overlay').removeClass('show');
            $('body').css('overflow', '');
        });

        // --- Browser back/forward buttons ---
        $(window).on('popstate', function (e) {
            if (e.originalEvent && e.originalEvent.state) {
                var s = e.originalEvent.state;
                state.page = s.page || 1;
                state.per_page = s.per_page || 12;
                state.search = s.search || '';
                state.category_id = s.category_id || null;
                state.country_id = s.country_id || null;
                state.sort = s.sort || 'newest';

                // Update UI inputs
                var $searchInput = $('#brand-search-input');
                if ($searchInput.length) $searchInput.val(state.search);

                var $sortSelect = $('#brand-sort-select');
                if ($sortSelect.length) $sortSelect.val(state.sort);

                // Update checkbox states
                $('#filter-categories input[type="checkbox"]').each(function () {
                    var val = parseInt($(this).val());
                    $(this).prop('checked', val === state.category_id);
                });

                loadBrands();
            }
        });
    }

    // ============================================================
    // SCROLL TO TOP (after pagination)
    // ============================================================
    function scrollToTop() {
        var offset = $('#main-header').outerHeight() + 20;
        $('html, body').animate({
            scrollTop: $('#brands-toolbar').length ? $('#brands-toolbar').offset().top - offset : 0
        }, 400, 'swing');
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