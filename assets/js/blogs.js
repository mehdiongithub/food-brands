/**
 * blogs.js — Loaded ONLY on blog.php (Blog listing page)
 * Handles: blog grid, filters (category, search, sort),
 * pagination, URL params sync, skeleton loaders, empty states
 */

(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '/food-brands';

    // Current filter state
    var state = {
        page: 1,
        per_page: 9,
        search: '',
        category: null,
        sort: 'newest'
    };

    // DOM references
    var $grid, $paginationWrap, $countEl, $skeletonWrap;

    $(document).ready(function () {
        $grid = $('#blog-grid');
        $paginationWrap = $('#blog-pagination');
        $countEl = $('#blog-count');
        $skeletonWrap = $('#blog-skeleton');

        // Read initial state from URL params
        readUrlParams();

        // Load category filters sidebar
        loadFilters();

        // Then load blog posts
        loadBlogs();

        // Bind all events
        bindEvents();
    });

    // ============================================================
    // READ URL PARAMS (for SEO-friendly filter URLs)
    // ============================================================
    function readUrlParams() {
        var params = new URLSearchParams(window.location.search);

        if (params.get('page')) state.page = parseInt(params.get('page')) || 1;
        if (params.get('per_page')) state.per_page = parseInt(params.get('per_page')) || 9;
        if (params.get('search')) state.search = params.get('search');
        if (params.get('category')) state.category = params.get('category');
        if (params.get('sort')) state.sort = params.get('sort');

        if (state.search) {
            var $searchInput = $('#blog-search-input');
            if ($searchInput.length) $searchInput.val(state.search);
        }

        if (state.sort) {
            var $sortSelect = $('#blog-sort-select');
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
        if (state.per_page !== 9) params.set('per_page', state.per_page);
        if (state.search) params.set('search', state.search);
        if (state.category) params.set('category', state.category);
        if (state.sort !== 'newest') params.set('sort', state.sort);

        var queryString = params.toString();
        var newUrl = BASE_URL + '/blog' + (queryString ? '?' + queryString : '');

        window.history.replaceState(state, '', newUrl);
    }

    // ============================================================
    // LOAD FILTERS (sidebar categories)
    // ============================================================
    function loadFilters() {
        $.getJSON(BASE_URL + '/api/site/blog.php', { action: 'categories' }, function (res) {
            if (!res.success) return;
            renderCategoryFilters(res.categories, state.category);
        }).fail(function () {
            console.warn('Failed to load blog categories.');
        });
    }

    function renderCategoryFilters(categories, activeName) {
        var $list = $('#filter-blog-categories');
        if (!$list.length) return;

        if (!categories || categories.length === 0) {
            $list.html('<div style="font-size:0.82rem;color:var(--muted);">No categories yet.</div>');
            $('#filter-blog-categories-mobile').html($list.html());
            return;
        }

        var html = '';
        $.each(categories, function (i, cat) {
            var checked = (activeName && activeName === cat.name) ? 'checked' : '';
            html += '<label class="filter-option">';
            html += '  <input type="checkbox" name="blog_category" value="' + escapeHtml(cat.name) + '" ' + checked + '>';
            html += '  <span>' + escapeHtml(cat.name) + '</span>';
            html += '  <span class="count">' + cat.post_count + '</span>';
            html += '</label>';
        });

        $list.html(html);
        $('#filter-blog-categories-mobile').html(html);
    }

    // ============================================================
    // LOAD BLOG POSTS (main data fetch)
    // ============================================================
    function loadBlogs() {
        showSkeleton();

        var params = {
            page: state.page,
            per_page: state.per_page,
            sort: state.sort
        };

        if (state.search) params.search = state.search;
        if (state.category) params.category = state.category;

        $.getJSON(BASE_URL + '/api/site/blog.php', params, function (res) {
            if (!res.success) {
                showError('Failed to load articles. Please try again.');
                return;
            }

            hideSkeleton();

            if (res.blogs.length === 0) {
                $grid.html('');
                showEmptyState();
            } else {
                renderBlogs(res.blogs);
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
    // RENDER BLOG CARDS
    // ============================================================
    function renderBlogs(blogs) {
        var html = '';
        $.each(blogs, function (i, post) {
            html += '<div class="col-6 col-md-4">' + buildBlogCard(post, i) + '</div>';
        });
        $grid.html(html);
    }

    function buildBlogCard(post, index) {
        var delay = Math.min(index * 40, 400);
        var html = '<div class="blog-card" data-aos="fade-up" data-aos-delay="' + delay + '">';

        html += '<a href="' + post.url + '">';
        html += '<img src="' + post.image + '" alt="' + escapeHtml(post.title) + '" loading="lazy">';
        html += '</a>';

        html += '<div class="blog-card-body">';
        html += '<div class="blog-card-meta">';
        html += '<span><i class="fa-solid fa-folder" style="margin-right:0.3rem;color:var(--primary);"></i>' + escapeHtml(post.category) + '</span>';
        html += '<span><i class="fa-regular fa-calendar" style="margin-right:0.3rem;"></i>' + escapeHtml(post.published_at_human) + '</span>';
        html += '<span><i class="fa-regular fa-clock" style="margin-right:0.3rem;"></i>' + post.read_time + ' min</span>';
        html += '</div>';
        html += '<h3 class="blog-card-title"><a href="' + post.url + '" style="color:var(--text);transition:color 0.3s;">' + escapeHtml(post.title) + '</a></h3>';
        html += '<p class="blog-card-excerpt">' + escapeHtml(post.excerpt) + '</p>';
        html += '<a href="' + post.url + '" style="display:inline-flex;align-items:center;gap:0.4rem;margin-top:0.9rem;font-size:0.82rem;font-weight:700;color:var(--primary);">Read More <i class="fa-solid fa-arrow-right" style="font-size:0.72rem;"></i></a>';
        html += '</div>';

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
            loadBlogs();
            scrollToTop();
        });
    }

    // ============================================================
    // UPDATE COUNT DISPLAY
    // ============================================================
    function updateCount(total, page, perPage) {
        if (!$countEl.length) return;

        if (total === 0) {
            $countEl.html('No articles found');
        } else {
            var start = (page - 1) * perPage + 1;
            var end = Math.min(page * perPage, total);
            $countEl.html('Showing <strong>' + start + '–' + end + '</strong> of <strong>' + total + '</strong> articles');
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
            $skeletonWrap.html(window.skeletonCards ? window.skeletonCards(state.per_page, 'blog') : '');
        } else {
            $grid.html(window.skeletonCards ? window.skeletonCards(state.per_page, 'blog') : '');
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
            '<i class="fa-solid fa-newspaper" style="font-size:3rem;color:var(--muted);opacity:0.3;margin-bottom:1.5rem;display:block;"></i>' +
            '<h3 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:0.5rem;">No articles found' + searchMsg + '</h3>' +
            '<p style="color:var(--text-secondary);max-width:400px;margin:0 auto 1.5rem;font-size:0.9rem;">Try adjusting your filters or search term to find what you\'re looking for.</p>' +
            '<button class="btn-reset-blog-filters-empty" style="padding:0.5rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;border:none;cursor:pointer;font-family:var(--font-body);font-weight:600;font-size:0.9rem;">Clear All Filters</button>' +
            '</div>'
        );
    }

    // ============================================================
    // BIND ALL EVENTS
    // ============================================================
    function bindEvents() {

        // --- Search input (debounced 500ms) ---
        var searchTimer = null;
        $(document).on('input', '#blog-search-input, .mobile-blog-search-sync', function () {
            var val = $(this).val().trim();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                state.search = val;
                state.page = 1;
                updateUrlParams(true);
                loadBlogs();
            }, 500);
        });

        // --- Search form submit (desktop + mobile) ---
        $(document).on('submit', '#blog-search-form, #blog-search-form-mobile', function (e) {
            e.preventDefault();
            var val = $(this).find('input').val().trim();
            state.search = val;
            state.page = 1;
            updateUrlParams(true);
            loadBlogs();
        });

        // --- Sort dropdown (desktop, mobile toolbar, mobile panel) ---
        $(document).on('change', '#blog-sort-select, #blog-sort-select-mobile, #blog-sort-select-mobile-panel', function () {
            state.sort = $(this).val();
            state.page = 1;
            $('#blog-sort-select, #blog-sort-select-mobile, #blog-sort-select-mobile-panel').val(state.sort);
            updateUrlParams(true);
            loadBlogs();
        });

        // --- Category filter checkboxes (single select, desktop + mobile) ---
        $(document).on('change', '#filter-blog-categories input[type="checkbox"], #filter-blog-categories-mobile input[type="checkbox"]', function () {
            var $all = $('#filter-blog-categories input[type="checkbox"], #filter-blog-categories-mobile input[type="checkbox"]');
            var val = $(this).val();

            if ($(this).is(':checked')) {
                $all.not(this).prop('checked', false);
                $all.filter('[value="' + val.replace(/"/g, '\\"') + '"]').prop('checked', true);
                state.category = val;
            } else {
                $all.prop('checked', false);
                state.category = null;
            }
        });

        // --- Apply Filters button (desktop + mobile) ---
        $(document).on('click', '#btn-apply-blog-filters, #btn-apply-blog-filters-mobile', function () {
            state.page = 1;
            updateUrlParams(true);
            loadBlogs();
            closeMobileFilter();
        });

        // --- Reset Filters button (desktop + mobile + empty state) ---
        $(document).on('click', '#btn-reset-blog-filters, #btn-reset-blog-filters-mobile, .btn-reset-blog-filters-empty', function () {
            state.page = 1;
            state.search = '';
            state.category = null;
            state.sort = 'newest';

            $('#blog-search-input, .mobile-blog-search-sync').val('');
            $('#blog-sort-select, #blog-sort-select-mobile, #blog-sort-select-mobile-panel').val('newest');
            $('#filter-blog-categories input[type="checkbox"], #filter-blog-categories-mobile input[type="checkbox"]').prop('checked', false);

            updateUrlParams(true);
            loadBlogs();
            closeMobileFilter();
        });

        // --- Mobile filter toggle ---
        $(document).on('click', '#btn-mobile-blog-filter', function () {
            var $panel = $('#mobile-blog-filter-panel');
            var $overlay = $('#mobile-blog-filter-overlay');
            if ($panel.length) {
                $panel.css('left', '0');
                $overlay.show();
                $('body').css('overflow', 'hidden');
            }
        });

        $(document).on('click', '#btn-close-mobile-blog-filter, #mobile-blog-filter-overlay', function () {
            closeMobileFilter();
        });

        // --- Browser back/forward buttons ---
        $(window).on('popstate', function (e) {
            if (e.originalEvent && e.originalEvent.state) {
                var s = e.originalEvent.state;
                state.page = s.page || 1;
                state.per_page = s.per_page || 9;
                state.search = s.search || '';
                state.category = s.category || null;
                state.sort = s.sort || 'newest';

                $('#blog-search-input, .mobile-blog-search-sync').val(state.search);
                $('#blog-sort-select, #blog-sort-select-mobile, #blog-sort-select-mobile-panel').val(state.sort);

                $('#filter-blog-categories input[type="checkbox"], #filter-blog-categories-mobile input[type="checkbox"]').each(function () {
                    $(this).prop('checked', $(this).val() === state.category);
                });

                loadBlogs();
            }
        });
    }

    function closeMobileFilter() {
        var $panel = $('#mobile-blog-filter-panel');
        var $overlay = $('#mobile-blog-filter-overlay');
        if ($panel.length) {
            $panel.css('left', '-320px');
            $overlay.hide();
            $('body').css('overflow', '');
        }
    }

    // ============================================================
    // SCROLL TO TOP (after pagination)
    // ============================================================
    function scrollToTop() {
        var offset = $('#main-header').outerHeight() + 20;
        $('html, body').animate({
            scrollTop: $('#blog-toolbar').length ? $('#blog-toolbar').offset().top - offset : 0
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
