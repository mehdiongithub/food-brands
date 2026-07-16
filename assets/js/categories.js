/**
 * categories.js — Loaded on categories.php ONLY
 * Handles: category cards grid, search, sort, pagination, URL params sync
 */

(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '/food-brands';

    // Listing page state
    var listState = {
        page: 1,
        per_page: 12,
        search: '',
        sort: 'sort_order'
    };

    // DOM references
    var $grid, $paginationWrap, $countEl, $skeletonWrap;

    $(document).ready(function () {
        initListPage();
    });

    // ================================================================
    // LISTING PAGE (categories.php)
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

        html += '    <div class="category-card" onclick="window.location.href=\'' + cat.url + '\'">';

        // Category Image
        if (cat.image) {
            html += '        <img src="' + cat.image + '" alt="' + escapeHtml(cat.name) + '" loading="lazy">';
        } else {
            html += '        <img src="' + BASE_URL + '/assets/img/no-category.webp" alt="' + escapeHtml(cat.name) + '" loading="lazy">';
        }

        // Discount / Sale Badge
        if (cat.has_offer) {
            html += '        <div class="cc-discount">' + escapeHtml(cat.offer_text || 'Sale') + '</div>';
        }

        // Content
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