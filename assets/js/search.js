/**
 * search.js — Loaded ONLY on search.php (Search Results page)
 * Handles: reading ?q= from URL, AJAX fetch from api/site/search.php,
 * type tabs (all/brands/products/categories/offers), sorting,
 * pagination (products tab), empty/error states, URL sync
 */

(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '/food-brands';

    // State
    var state = {
        q: '',
        type: 'all',
        sort: 'relevance',
        page: 1,
        per_page: 12
    };

    var lastResults = null;

    // DOM references
    var $toolbarWrap, $skeleton, $resultsWrap, $paginationWrap, $countEl, $emptyState, $errorState;

    $(document).ready(function () {
        $toolbarWrap = $('#search-toolbar-wrap');
        $skeleton = $('#search-skeleton');
        $resultsWrap = $('#search-results');
        $paginationWrap = $('#search-pagination');
        $countEl = $('#search-count');
        $emptyState = $('#search-empty');
        $errorState = $('#search-error');

        if (!$resultsWrap.length) return;

        readUrlParams();
        bindEvents();

        if (state.q) {
            runSearch();
        }
    });

    // ============================================================
    // URL PARAMS
    // ============================================================
    function readUrlParams() {
        var params = new URLSearchParams(window.location.search);
        state.q = params.get('q') || '';
        state.type = params.get('type') || 'all';
        state.sort = params.get('sort') || 'relevance';
        state.page = parseInt(params.get('page')) || 1;

        var $sortSelect = $('#search-sort-select');
        if ($sortSelect.length) $sortSelect.val(state.sort);

        setActiveTab(state.type);
    }

    function updateUrlParams(resetPage) {
        if (resetPage) state.page = 1;

        var params = new URLSearchParams();
        if (state.q) params.set('q', state.q);
        if (state.type !== 'all') params.set('type', state.type);
        if (state.sort !== 'relevance') params.set('sort', state.sort);
        if (state.page > 1) params.set('page', state.page);

        var queryString = params.toString();
        var newUrl = BASE_URL + '/search' + (queryString ? '?' + queryString : '');
        window.history.replaceState(state, '', newUrl);
    }

    // ============================================================
    // RUN SEARCH
    // ============================================================
    function runSearch() {
        if (!state.q) {
            $toolbarWrap.hide();
            $resultsWrap.html('');
            $paginationWrap.hide();
            $errorState.hide();
            $emptyState.show();
            return;
        }

        $emptyState.hide();
        $errorState.hide();
        showSkeleton();

        var params = {
            action: 'results',
            q: state.q,
            type: state.type,
            sort: state.sort,
            page: state.page,
            per_page: state.per_page
        };

        $.getJSON(BASE_URL + '/api/site/search.php', params, function (res) {
            hideSkeleton();

            if (!res.success) {
                showError('Failed to load search results. Please try again.');
                return;
            }

            lastResults = res;
            $toolbarWrap.show();
            updateCount(res.counts, state.type);
            updateDocumentTitle(res.query);

            if (res.total === 0) {
                $resultsWrap.html('');
                $paginationWrap.hide();
                showNoResults(res.query);
                return;
            }

            renderResults(res);
            updateUrlParams(false);
            window.refreshAOS();

        }).fail(function () {
            hideSkeleton();
            showError('Network error. Please check your connection and try again.');
        });
    }

    // ============================================================
    // RENDER RESULTS
    // ============================================================
    function renderResults(res) {
        var html = '';

        if (state.type === 'all') {
            // Grouped preview sections, each capped, with "View all" link
            if (res.results.brands.length > 0) {
                html += buildGroupSection('Brands', 'brands', res.counts.brands, buildBrandsGrid(res.results.brands.slice(0, 4)));
            }
            if (res.results.products.length > 0) {
                html += buildGroupSection('Products', 'products', res.counts.products, buildProductsGrid(res.results.products.slice(0, 8)));
            }
            if (res.results.categories.length > 0) {
                html += buildGroupSection('Categories', 'categories', res.counts.categories, buildCategoriesGrid(res.results.categories.slice(0, 4)));
            }
            if (res.results.offers.length > 0) {
                html += buildGroupSection('Offers', 'offers', res.counts.offers, buildOffersGrid(res.results.offers.slice(0, 3)));
            }
            $paginationWrap.hide();
        } else if (state.type === 'brands') {
            html += '<div class="row g-4">' + buildBrandsGrid(res.results.brands) + '</div>';
            $paginationWrap.hide();
        } else if (state.type === 'products') {
            html += '<div class="row g-4">' + buildProductsGrid(res.results.products) + '</div>';
            renderPagination(res.pagination);
        } else if (state.type === 'categories') {
            html += '<div class="row g-4">' + buildCategoriesGrid(res.results.categories) + '</div>';
            $paginationWrap.hide();
        } else if (state.type === 'offers') {
            html += '<div class="row g-4">' + buildOffersGrid(res.results.offers) + '</div>';
            $paginationWrap.hide();
        }

        $resultsWrap.html(html);
    }

    function buildGroupSection(title, type, count, gridHtml) {
        var html = '<div class="search-result-group" data-aos="fade-up">';
        html += '<div class="search-result-group-header">';
        html += '<h3 class="search-result-group-title">' + title + ' <span style="color:var(--muted);font-weight:400;font-size:0.9rem;">(' + count + ')</span></h3>';
        if (count > gridHtml.split('data-aos').length - 1) {
            html += '<button type="button" class="search-view-all-btn" data-type="' + type + '" style="background:none;border:none;color:var(--primary);font-weight:600;font-size:0.85rem;cursor:pointer;">View All <i class="fa-solid fa-arrow-right" style="font-size:0.75rem;margin-left:0.25rem;"></i></button>';
        }
        html += '</div>';
        html += '<div class="row g-4">' + gridHtml + '</div>';
        html += '</div>';
        return html;
    }

    // ============================================================
    // CARD BUILDERS
    // ============================================================
    function buildBrandsGrid(brands) {
        var html = '';
        $.each(brands, function (i, brand) {
            var delay = Math.min(i * 50, 300);
            html += '<div class="col-6 col-md-4 col-lg-3">';
            html += '<div class="brand-card" data-aos="fade-up" data-aos-delay="' + delay + '">';
            html += '<a href="' + brand.url + '" class="bc-cover">';
            if (brand.cover_image) {
                html += '<img src="' + brand.cover_image + '" alt="' + escapeHtml(brand.name) + '" loading="lazy">';
            } else {
                html += '<div style="width:100%;height:100%;background:linear-gradient(135deg,var(--secondary),#2D1B4E);display:flex;align-items:center;justify-content:center;"><span style="font-family:var(--font-display);font-size:2.5rem;font-weight:900;color:rgba(255,255,255,0.15);">' + escapeHtml(brand.name.charAt(0)) + '</span></div>';
            }
            html += '</a>';
            html += '<div class="bc-logo">';
            if (brand.logo) {
                html += '<img src="' + brand.logo + '" alt="' + escapeHtml(brand.name) + '" loading="lazy">';
            } else {
                html += '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;"><span style="font-family:var(--font-display);font-size:1.2rem;font-weight:900;color:var(--primary);">' + escapeHtml(brand.name.charAt(0)) + '</span></div>';
            }
            html += '</div>';
            html += '<div class="bc-body">';
            html += '<h3 class="bc-name"><a href="' + brand.url + '" style="color:var(--text);">' + (brand.name_highlighted || escapeHtml(brand.name)) + '</a></h3>';
            if (brand.short_description) {
                html += '<p class="bc-desc">' + escapeHtml(brand.short_description) + '</p>';
            }
            html += '<div class="bc-stats"><span class="bc-stat"><strong>' + brand.product_count + '</strong> Products</span></div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
        });
        return html;
    }

    function buildProductsGrid(products) {
        var html = '';
        $.each(products, function (i, p) {
            var delay = Math.min(i * 50, 300);
            html += '<div class="col-6 col-md-4 col-lg-3">';
            html += '<div class="product-card" data-aos="fade-up" data-aos-delay="' + delay + '">';
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
            html += '<div class="pc-body">';
            html += '<div class="pc-brand">';
            if (p.brand_logo) html += '<img src="' + p.brand_logo + '" alt="' + escapeHtml(p.brand_name) + '" loading="lazy">';
            html += '<a href="' + p.brand_url + '">' + escapeHtml(p.brand_name) + '</a>';
            html += '</div>';
            html += '<h4 class="pc-name"><a href="' + p.url + '" style="color:var(--text);">' + (p.name_highlighted || escapeHtml(p.name)) + '</a></h4>';
            if (p.calories > 0) {
                html += '<div class="pc-meta"><span><i class="fa-solid fa-fire"></i> ' + p.calories + ' cal</span></div>';
            }
            html += '<div class="pc-footer">';
            html += '<div class="pc-prices">';
            if (p.has_discount && p.formatted_discount) {
                html += '<span class="pc-original-price">' + p.formatted_regular + '</span>';
                html += '<span class="pc-current-price">' + p.formatted_discount + '</span>';
            } else if (p.formatted_regular) {
                html += '<span class="pc-current-price">' + p.formatted_regular + '</span>';
            } else {
                html += '<span class="pc-current-price" style="font-size:0.85rem;color:var(--muted);">Price not available</span>';
            }
            html += '</div>';
            html += '<a href="' + p.url + '" class="pc-view-btn">View</a>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
        });
        return html;
    }

    function buildCategoriesGrid(categories) {
        var html = '';
        $.each(categories, function (i, cat) {
            var delay = Math.min(i * 60, 300);
            html += '<div class="col-6 col-md-4 col-lg-3">';
            html += '<a href="' + cat.url + '" class="category-card" data-aos="fade-up" data-aos-delay="' + delay + '">';
            if (cat.image) {
                html += '<img src="' + cat.image + '" alt="' + escapeHtml(cat.name) + '" loading="lazy">';
            } else {
                html += '<div style="width:100%;height:100%;background:linear-gradient(135deg,var(--secondary),#2D1B4E);display:flex;align-items:center;justify-content:center;"><span style="font-family:var(--font-display);font-size:3rem;font-weight:900;color:rgba(255,255,255,0.15);">' + escapeHtml(cat.name.charAt(0)) + '</span></div>';
            }
            html += '<div class="cc-content">';
            html += '<h3 class="cc-name">' + (cat.name_highlighted || escapeHtml(cat.name)) + '</h3>';
            html += '<div class="cc-info">' + cat.product_count + ' Items</div>';
            html += '</div>';
            html += '</a>';
            html += '</div>';
        });
        return html;
    }

    function buildOffersGrid(offers) {
        var html = '';
        $.each(offers, function (i, offer) {
            var delay = Math.min(i * 60, 300);
            html += '<div class="col-md-6 col-lg-4">';
            html += '<div class="offer-card" data-aos="fade-up" data-aos-delay="' + delay + '">';
            html += '<div class="oc-body">';
            html += '<div class="oc-brand">';
            if (offer.brand_logo) html += '<a href="' + offer.brand_url + '"><img src="' + offer.brand_logo + '" alt="' + escapeHtml(offer.brand_name) + '" loading="lazy"></a>';
            html += '<a href="' + offer.brand_url + '" style="color:var(--text);">' + escapeHtml(offer.brand_name) + '</a>';
            html += '</div>';
            html += '<h4 class="oc-title">' + (offer.title_highlighted || escapeHtml(offer.title)) + '</h4>';
            if (offer.description) {
                var plainDesc = $('<div>').html(offer.description).text();
                if (plainDesc.length > 110) plainDesc = plainDesc.substring(0, 107) + '...';
                html += '<p class="oc-desc">' + escapeHtml(plainDesc) + '</p>';
            }
            html += '<div class="oc-footer">';
            html += '<div class="oc-discount">' + offer.discount_percent + '% OFF</div>';
            if (offer.coupon_code) {
                html += '<span class="oc-code btn-copy-coupon" data-code="' + escapeHtml(offer.coupon_code) + '" title="Click to copy" style="cursor:pointer;">' + escapeHtml(offer.coupon_code) + '</span>';
            } else {
                html += '<span style="font-size:0.78rem;color:var(--muted);font-style:italic;">Auto-applied</span>';
            }
            html += '</div>';
            if (offer.days_remaining > 0 && offer.days_remaining <= 7) {
                html += '<div style="margin-top:0.6rem;font-size:0.75rem;color:var(--warning);font-weight:600;"><i class="fa-solid fa-hourglass-half" style="margin-right:0.25rem;"></i>' + offer.days_remaining + 'd left</div>';
            }
            html += '</div>';
            html += '</div>';
            html += '</div>';
        });
        return html;
    }

    // ============================================================
    // PAGINATION (products tab only, per API)
    // ============================================================
    function renderPagination(pagination) {
        if (!window.buildPagination) return;
        var html = window.buildPagination(pagination);

        if (!html) {
            $paginationWrap.hide();
            return;
        }

        $paginationWrap.html(html).show();
        window.bindPagination($paginationWrap, function (page) {
            state.page = page;
            runSearch();
            scrollToResultsTop();
        });
    }

    function scrollToResultsTop() {
        var headerHeight = $('#main-header').outerHeight() || 72;
        var $target = $('#search-toolbar-wrap');
        if ($target.length) {
            $('html, body').animate({ scrollTop: $target.offset().top - headerHeight - 20 }, 400, 'swing');
        }
    }

    // ============================================================
    // TOOLBAR HELPERS
    // ============================================================
    function updateCount(counts, type) {
        if (!$countEl.length || !counts) return;
        var n = type === 'all' ? counts.total : counts[type];
        $countEl.html('<strong>' + (window.formatNumber ? window.formatNumber(n) : n) + '</strong> result' + (n === 1 ? '' : 's') + ' found');
    }

    function updateDocumentTitle(q) {
        if (!q) return;
        document.title = 'Search Results for "' + q + '"';
    }

    function setActiveTab(type) {
        $('.search-tab-btn').removeClass('active');
        $('.search-tab-btn[data-type="' + type + '"]').addClass('active');
    }

    // ============================================================
    // EMPTY / ERROR STATES
    // ============================================================
    function showNoResults(q) {
        $toolbarWrap.show();
        $resultsWrap.html(
            '<div style="text-align:center;padding:4rem 2rem;">' +
            '<i class="fa-solid fa-face-frown" style="font-size:3rem;color:var(--muted);opacity:0.3;margin-bottom:1.5rem;display:block;"></i>' +
            '<h3 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:0.5rem;">No results for "' + escapeHtml(q) + '"</h3>' +
            '<p style="color:var(--text-secondary);max-width:420px;margin:0 auto;font-size:0.9rem;">Try a different keyword, check your spelling, or browse our full catalog instead.</p>' +
            '<div style="margin-top:1.5rem;display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">' +
            '<a href="' + BASE_URL + '/brands" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;">Browse Brands</a>' +
            '<a href="' + BASE_URL + '/categories" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);border:1px solid var(--border);color:var(--text);font-weight:600;font-size:0.9rem;">Browse Categories</a>' +
            '</div>' +
            '</div>'
        );
    }

    function showError(msg) {
        $toolbarWrap.hide();
        $resultsWrap.html('');
        $paginationWrap.hide();
        $errorState.show().html(
            '<div style="text-align:center;padding:3rem 2rem;">' +
            '<i class="fa-solid fa-exclamation-triangle" style="font-size:2.5rem;color:var(--danger);margin-bottom:1rem;display:block;"></i>' +
            '<p style="color:var(--text-secondary);margin-bottom:1rem;">' + escapeHtml(msg) + '</p>' +
            '<button onclick="location.reload()" style="padding:0.5rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;border:none;cursor:pointer;font-family:var(--font-body);font-weight:600;">Try Again</button>' +
            '</div>'
        );
    }

    function showSkeleton() {
        $toolbarWrap.hide();
        $resultsWrap.html('');
        $paginationWrap.hide();
        $skeleton.show();
    }

    function hideSkeleton() {
        $skeleton.hide();
    }

    // ============================================================
    // BIND EVENTS
    // ============================================================
    function bindEvents() {
        // --- Page search form submit ---
        $(document).on('submit', '#page-search-form', function (e) {
            e.preventDefault();
            var val = $('#page-search-input').val().trim();
            state.q = val;
            state.type = 'all';
            state.page = 1;
            setActiveTab('all');
            updateUrlParams(true);
            runSearch();
        });

        // --- Type tabs ---
        $(document).on('click', '.search-tab-btn', function () {
            var type = $(this).data('type');
            if (type === state.type) return;
            state.type = type;
            state.page = 1;
            setActiveTab(type);
            updateUrlParams(true);
            runSearch();
        });

        // --- "View All" links inside grouped sections ---
        $(document).on('click', '.search-view-all-btn', function () {
            var type = $(this).data('type');
            state.type = type;
            state.page = 1;
            setActiveTab(type);
            updateUrlParams(true);
            runSearch();
            scrollToResultsTop();
        });

        // --- Sort dropdown ---
        $(document).on('change', '#search-sort-select', function () {
            state.sort = $(this).val();
            state.page = 1;
            updateUrlParams(true);
            runSearch();
        });

        // --- Copy coupon code ---
        $(document).on('click', '.btn-copy-coupon', function () {
            var code = $(this).data('code');
            if (code && window.copyToClipboard) {
                window.copyToClipboard(code, 'Coupon code copied!');
            }
        });

        // --- Copy product link ---
        $(document).on('click', '.btn-copy-link', function () {
            var url = $(this).data('url');
            if (url && window.copyToClipboard) {
                window.copyToClipboard(url, 'Link copied!');
            }
        });

        // --- Quick view ---
        $(document).on('click', '.btn-quick-view', function () {
            var slug = $(this).data('slug');
            if (slug && window.openQuickView) {
                window.openQuickView(slug);
            }
        });

        // --- Browser back/forward ---
        $(window).on('popstate', function () {
            readUrlParams();
            runSearch();
        });
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