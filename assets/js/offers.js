/**
 * offers.js — Loaded ONLY on offers.php (Offers listing page)
 * Handles: sidebar filters (brand checkboxes + min-discount range + show-expired),
 * offers grid with search/sort, pagination, URL params sync, mobile filter drawer
 */

(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '/food-brands';

    // Offers filter state (committed / applied)
    var state = {
        page: 1,
        per_page: 12,
        brand_ids: [],      // multi-select brand checkboxes
        min_discount: null, // discount range slider
        search: '',
        sort: 'discount_high',
        show_expired: false
    };

    // Pending sidebar selections (only committed to `state` on Apply)
    var pending = {
        brand_ids: [],
        min_discount: null
    };

    // DOM references
    var $grid, $paginationWrap, $countEl, $skeletonWrap;
    var offersData = null;
    var discountBounds = { min: 0, max: 100 };

    $(document).ready(function () {
        init();
    });

    function init() {
        $grid = $('#offers-grid');
        $paginationWrap = $('#offers-pagination');
        $countEl = $('#offers-count');
        $skeletonWrap = $('#offers-skeleton');

        readUrlParams();
        loadOffers(true);
        bindEvents();
    }

    // ============================================================
    // URL PARAMS
    // ============================================================
    function readUrlParams() {
        var params = new URLSearchParams(window.location.search);

        if (params.get('page')) state.page = parseInt(params.get('page')) || 1;
        if (params.get('per_page')) state.per_page = parseInt(params.get('per_page')) || 12;
        if (params.get('brand_ids')) {
            state.brand_ids = params.get('brand_ids').split(',').map(function (v) { return parseInt(v); }).filter(Boolean);
        }
        if (params.get('min_discount')) state.min_discount = parseFloat(params.get('min_discount'));
        if (params.get('search')) state.search = params.get('search');
        if (params.get('sort')) state.sort = params.get('sort');
        if (params.get('show_expired') === '1') state.show_expired = true;

        pending.brand_ids = state.brand_ids.slice();
        pending.min_discount = state.min_discount;

        if (state.search) {
            $('#offer-search-input').val(state.search);
        }
        if (state.sort) {
            $('#offer-sort-select, #offer-sort-select-mobile').val(state.sort);
        }
        if (state.show_expired) {
            $('#offer-expired-toggle, #offer-expired-toggle-mobile').prop('checked', true);
        }
    }

    function updateUrlParams(resetPage) {
        if (resetPage) state.page = 1;

        var params = new URLSearchParams();

        if (state.page > 1) params.set('page', state.page);
        if (state.per_page !== 12) params.set('per_page', state.per_page);
        if (state.brand_ids.length) params.set('brand_ids', state.brand_ids.join(','));
        if (state.min_discount !== null && state.min_discount !== undefined) params.set('min_discount', state.min_discount);
        if (state.search) params.set('search', state.search);
        if (state.sort !== 'discount_high') params.set('sort', state.sort);
        if (state.show_expired) params.set('show_expired', '1');

        var queryString = params.toString();
        var newUrl = BASE_URL + '/offers' + (queryString ? '?' + queryString : '');

        window.history.replaceState(state, '', newUrl);
    }

    function buildRequestParams() {
        var params = {
            action: 'list',
            page: state.page,
            per_page: state.per_page,
            sort: state.sort
        };

        if (state.brand_ids.length) params.brand_ids = state.brand_ids.join(',');
        if (state.min_discount !== null && state.min_discount !== undefined) params.min_discount = state.min_discount;
        if (state.search) params.search = state.search;
        if (state.show_expired) params.show_expired = '1';

        return params;
    }

    // ============================================================
    // LOAD OFFERS
    // ============================================================
    function loadOffers() {
        showSkeleton();
        hideError();

        $.getJSON(BASE_URL + '/api/site/offers.php', buildRequestParams(), function (res) {
            if (!res.success) {
                showError(res.message || 'Failed to load offers.');
                return;
            }

            offersData = res;

            if (res.filters) {
                discountBounds.min = res.filters.discount_min || 0;
                discountBounds.max = res.filters.discount_max || 0;
            }

            hideSkeleton();
            renderBrandFilters(res.filters ? res.filters.brands : []);
            renderDiscountRange();

            if (res.offers.length === 0) {
                $grid.html('');
                showEmpty();
            } else {
                hideEmpty();
                renderOffers(res.offers);
            }

            renderPagination(res.pagination);
            updateCount(res.pagination.total_items, res.pagination.current_page, res.pagination.per_page);
            updateUrlParams(false);
            window.refreshAOS();

        }).fail(function () {
            hideSkeleton();
            showError('Network error. Please check your connection and try again.');
        });
    }

    // ============================================================
    // RENDER SIDEBAR — BRAND CHECKBOXES (desktop + mobile)
    // ============================================================
    function renderBrandFilters(brands) {
        var html = '';

        if (!brands || brands.length === 0) {
            html = '<div style="padding:0.5rem 0;color:var(--muted);font-size:0.82rem;">No brands available.</div>';
        } else {
            $.each(brands, function (i, brand) {
                var checked = pending.brand_ids.indexOf(brand.id) !== -1 ? 'checked' : '';
                html += '<label class="filter-option">';
                html += '  <input type="checkbox" name="brand_id" value="' + brand.id + '" ' + checked + '>';
                if (brand.logo) {
                    html += '  <img src="' + brand.logo + '" alt="' + escapeHtml(brand.name) + '" style="width:18px;height:18px;object-fit:contain;border-radius:2px;">';
                }
                html += '  <span>' + escapeHtml(brand.name) + '</span>';
                html += '  <span class="count">' + brand.offer_count + '</span>';
                html += '</label>';
            });
        }

        $('#filter-brands').html(html);
        $('#filter-brands-mobile').html(html);
    }

    // ============================================================
    // RENDER SIDEBAR — MIN DISCOUNT RANGE SLIDER (desktop + mobile)
    // ============================================================
    function renderDiscountRange() {
        var min = discountBounds.min || 0;
        var max = discountBounds.max > min ? discountBounds.max : min + 1;
        var current = (pending.min_discount !== null && pending.min_discount !== undefined) ? pending.min_discount : min;

        $('#offer-filter-discount, #offer-filter-discount-mobile').attr('min', min).attr('max', max).val(current);
        $('#offer-discount-min-label, #offer-discount-min-label-mobile').text(Math.round(min) + '%');
        $('#offer-discount-max-label, #offer-discount-max-label-mobile').text(Math.round(current) + '%+');
    }

    // ============================================================
    // RENDER OFFERS GRID
    // ============================================================
    function renderOffers(offers) {
        var html = '';
        $.each(offers, function (i, offer) {
            html += buildOfferCard(offer, i);
        });
        $grid.html(html);
        $grid.show();
    }

    function buildOfferCard(offer, index) {
        var delay = Math.min(index * 40, 400);

        var html = '<div class="col-6 col-md-4 col-lg-4" data-aos="fade-up" data-aos-delay="' + delay + '">';
        html += '<div class="offer-card">';

        // Image
        html += '<div class="oc-image">';
        if (offer.image) {
            html += '<img src="' + offer.image + '" alt="' + escapeHtml(offer.title) + '" loading="lazy">';
        } else {
            html += '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--bg-alt);color:var(--muted);"><i class="fa-solid fa-tag" style="font-size:2rem;"></i></div>';
        }

        if (offer.status_label === 'expired') {
            html += '<span class="oc-status-badge oc-status-expired">Expired</span>';
        } else if (offer.status_label === 'ending_soon') {
            html += '<span class="oc-status-badge oc-status-ending">Ending Soon</span>';
        }
        html += '</div>';

        // Body
        html += '<div class="oc-body">';

        html += '<div class="oc-brand">';
        if (offer.brand && offer.brand.logo) {
            html += '<img src="' + offer.brand.logo + '" alt="' + escapeHtml(offer.brand.name) + '" loading="lazy">';
        }
        html += '<a href="' + offer.brand.url + '">' + escapeHtml(offer.brand ? offer.brand.name : '') + '</a>';
        html += '</div>';

        html += '<h4 class="oc-title"><a href="' + offer.url + '" style="color:var(--text);">' + escapeHtml(offer.title) + '</a></h4>';

        if (offer.description) {
            var plainDesc = $('<div>').html(offer.description).text();
            if (plainDesc.length > 90) plainDesc = plainDesc.substring(0, 87) + '...';
            html += '<p class="oc-desc">' + escapeHtml(plainDesc) + '</p>';
        }

        html += '<div class="oc-footer">';
        html += '<div class="oc-discount">' + offer.discount_percent + '% OFF</div>';
        if (offer.coupon_code) {
            html += '<span class="oc-code btn-copy-coupon" title="Click to copy" style="cursor:pointer;" data-code="' + escapeHtml(offer.coupon_code) + '">' + escapeHtml(offer.coupon_code) + '</span>';
        }
        html += '</div>';

        if (offer.days_remaining > 0 && offer.days_remaining <= 7 && offer.status_label !== 'expired') {
            html += '<div style="margin-top:0.6rem;font-size:0.75rem;color:var(--warning);font-weight:600;"><i class="fa-solid fa-clock"></i> ' + offer.days_remaining + ' days left</div>';
        }

        html += '<a href="' + offer.url + '" class="pc-view-btn" style="display:block;text-align:center;margin-top:0.85rem;">View Offer</a>';

        html += '</div>'; // oc-body
        html += '</div>'; // offer-card
        html += '</div>'; // col

        return html;
    }

    // Copy coupon code on click (delegated)
    $(document).on('click', '.btn-copy-coupon', function (e) {
        e.preventDefault();
        var code = $(this).data('code');
        if (code && window.copyToClipboard) {
            window.copyToClipboard(String(code), 'Coupon code copied!');
        }
    });

    // ============================================================
    // PAGINATION & COUNT
    // ============================================================
    function renderPagination(pagination) {
        if (!window.buildPagination) return;

        var html = window.buildPagination(pagination);
        $paginationWrap.html(html);
        $paginationWrap.show();

        window.bindPagination($paginationWrap, function (page) {
            state.page = page;
            loadOffers();
            scrollToTop();
        });
    }

    function updateCount(total, page, perPage) {
        if (!$countEl.length) return;

        if (total === 0) {
            $countEl.html('No offers found');
        } else {
            var start = (page - 1) * perPage + 1;
            var end = Math.min(page * perPage, total);
            $countEl.html('Showing <strong>' + start + '–' + end + '</strong> of <strong>' + total + '</strong> offers');
        }
    }

    function scrollToTop() {
        var offset = $('#main-header').outerHeight() + 20;
        var $target = $('#offers-toolbar');
        if ($target.length) {
            $('html, body').animate({ scrollTop: $target.offset().top - offset }, 400, 'swing');
        }
    }

    // ============================================================
    // SKELETON / EMPTY / ERROR STATES
    // ============================================================
    function showSkeleton() {
        if ($skeletonWrap.length) {
            $skeletonWrap.show();
            $grid.hide();
            $paginationWrap.hide();
        }
    }

    function hideSkeleton() {
        if ($skeletonWrap.length) {
            $skeletonWrap.hide();
            $grid.show();
            $paginationWrap.show();
        }
    }

    function showEmpty() {
        var searchMsg = state.search ? ' matching "<strong>' + escapeHtml(state.search) + '</strong>"' : '';
        var brandMsg = state.brand_ids.length ? ' for the selected brands' : '';

        $('#offers-empty').html(
            '<div style="grid-column:1/-1;text-align:center;padding:3rem 2rem;">' +
            '<i class="fa-solid fa-tag" style="font-size:2.5rem;color:var(--muted);opacity:0.3;margin-bottom:1rem;display:block;"></i>' +
            '<h3 style="font-family:var(--font-display);font-size:1.2rem;margin-bottom:0.5rem;">No offers found' + searchMsg + brandMsg + '</h3>' +
            '<p style="color:var(--text-secondary);font-size:0.9rem;">Try a different filter or search term.</p>' +
            '<button class="btn-reset-offer-filters filter-reset-btn" style="max-width:200px;margin:1rem auto 0;">Clear Filters</button>' +
            '</div>'
        ).show();
        $paginationWrap.hide();
    }

    function hideEmpty() {
        $('#offers-empty').hide();
    }

    function showError(msg) {
        hideSkeleton();
        $grid.hide();
        $paginationWrap.hide();
        $('#offers-empty').hide();
        $('#offers-error').html(
            '<div style="text-align:center;padding:4rem 2rem;">' +
            '<i class="fa-solid fa-exclamation-triangle" style="font-size:3rem;color:var(--danger);margin-bottom:1.5rem;display:block;"></i>' +
            '<h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:0.75rem;">Something went wrong</h2>' +
            '<p style="color:var(--text-secondary);max-width:450px;margin:0 auto 1.5rem;">' + escapeHtml(msg) + '</p>' +
            '<button class="btn-retry-offers" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;border:none;cursor:pointer;">' +
            '<i class="fa-solid fa-rotate-right" style="font-size:0.75rem;"></i> Try Again</button>' +
            '</div>'
        ).show();
    }

    function hideError() {
        $('#offers-error').hide();
    }

    // ============================================================
    // BIND EVENTS
    // ============================================================
    function bindEvents() {
        // --- Brand checkboxes (desktop + mobile, multi-select, pending only) ---
        $(document).on('change', '#filter-brands input[type="checkbox"], #filter-brands-mobile input[type="checkbox"]', function () {
            var val = parseInt($(this).val());
            var checked = $(this).is(':checked');

            if (checked && pending.brand_ids.indexOf(val) === -1) {
                pending.brand_ids.push(val);
            } else if (!checked) {
                pending.brand_ids = pending.brand_ids.filter(function (v) { return v !== val; });
            }

            $('#filter-brands input[value="' + val + '"], #filter-brands-mobile input[value="' + val + '"]').prop('checked', checked);
        });

        // --- Discount range slider (desktop + mobile, pending only) ---
        $(document).on('input', '#offer-filter-discount, #offer-filter-discount-mobile', function () {
            var val = parseFloat($(this).val());
            pending.min_discount = val;
            $('#offer-filter-discount, #offer-filter-discount-mobile').val(val);
            $('#offer-discount-max-label, #offer-discount-max-label-mobile').text(Math.round(val) + '%+');
        });

        // --- Search input (debounced) ---
        var searchTimer = null;
        $(document).on('input', '#offer-search-input', function () {
            var val = $(this).val().trim();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                state.search = val;
                state.page = 1;
                updateUrlParams(true);
                loadOffers();
            }, 500);
        });

        $(document).on('submit', '#offer-search-form', function (e) {
            e.preventDefault();
            state.search = $('#offer-search-input').val().trim();
            state.page = 1;
            updateUrlParams(true);
            loadOffers();
        });

        // --- Sort dropdown (desktop + mobile, applies immediately) ---
        $(document).on('change', '#offer-sort-select, #offer-sort-select-mobile', function () {
            state.sort = $(this).val();
            $('#offer-sort-select, #offer-sort-select-mobile').val(state.sort);
            state.page = 1;
            updateUrlParams(true);
            loadOffers();
        });

        // --- Show expired toggle (applies immediately) ---
        $(document).on('change', '#offer-expired-toggle, #offer-expired-toggle-mobile', function () {
            state.show_expired = $(this).is(':checked');
            $('#offer-expired-toggle, #offer-expired-toggle-mobile').prop('checked', state.show_expired);
            state.page = 1;
            updateUrlParams(true);
            loadOffers();
        });

        // --- Apply Filters button (desktop + mobile) ---
        $(document).on('click', '#btn-apply-offer-filters, #btn-apply-offer-filters-mobile', function () {
            state.brand_ids = pending.brand_ids.slice();
            state.min_discount = pending.min_discount;
            state.page = 1;
            updateUrlParams(true);
            loadOffers();
            closeMobileFilter();
        });

        // --- Reset Filters button (desktop + mobile + empty-state) ---
        $(document).on('click', '#btn-reset-offer-filters, #btn-reset-offer-filters-mobile, .btn-reset-offer-filters', function () {
            state.brand_ids = [];
            state.min_discount = null;
            state.search = '';
            state.sort = 'discount_high';
            state.show_expired = false;
            state.page = 1;

            pending.brand_ids = [];
            pending.min_discount = null;

            $('#filter-brands input[type="checkbox"], #filter-brands-mobile input[type="checkbox"]').prop('checked', false);
            $('#offer-search-input').val('');
            $('#offer-sort-select, #offer-sort-select-mobile').val('discount_high');
            $('#offer-expired-toggle, #offer-expired-toggle-mobile').prop('checked', false);

            updateUrlParams(true);
            loadOffers();
            closeMobileFilter();
        });

        // --- Retry on network error ---
        $(document).on('click', '.btn-retry-offers', function () {
            loadOffers();
        });

        // --- Mobile filter drawer open/close ---
        $(document).on('click', '#btn-mobile-offer-filter', function () {
            $('#offer-mobile-filter-panel').css('left', '0');
            $('#offer-mobile-filter-overlay').show();
            $('body').css('overflow', 'hidden');
        });

        $(document).on('click', '#btn-close-offer-mobile-filter, #offer-mobile-filter-overlay', function () {
            closeMobileFilter();
        });

        // --- Browser back/forward ---
        $(window).on('popstate', function (e) {
            if (e.originalEvent && e.originalEvent.state) {
                var s = e.originalEvent.state;
                state.page = s.page || 1;
                state.per_page = s.per_page || 12;
                state.brand_ids = s.brand_ids || [];
                state.min_discount = (s.min_discount !== undefined) ? s.min_discount : null;
                state.search = s.search || '';
                state.sort = s.sort || 'discount_high';
                state.show_expired = s.show_expired || false;

                pending.brand_ids = state.brand_ids.slice();
                pending.min_discount = state.min_discount;

                $('#offer-search-input').val(state.search);
                $('#offer-sort-select, #offer-sort-select-mobile').val(state.sort);
                $('#offer-expired-toggle, #offer-expired-toggle-mobile').prop('checked', state.show_expired);

                $('#filter-brands input[type="checkbox"], #filter-brands-mobile input[type="checkbox"]').each(function () {
                    var val = parseInt($(this).val());
                    $(this).prop('checked', state.brand_ids.indexOf(val) !== -1);
                });

                loadOffers();
            }
        });
    }

    function closeMobileFilter() {
        $('#offer-mobile-filter-panel').css('left', '-320px');
        $('#offer-mobile-filter-overlay').hide();
        $('body').css('overflow', '');
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
