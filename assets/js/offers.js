/**
 * offer-detail.js — Loaded ONLY on offer-detail.php (Offer detail page)
 * Handles: offer hero, coupon box, validity/countdown, countries,
 * eligible products grid with filters + pagination, other offers, share
 */

(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '/food-brands';

    // Offer slug from PHP (injected via inline script in offer-detail.php)
    var offerSlug = window.OFFER_SLUG || '';

    // Current product filter state (for the "eligible products" grid)
    var state = {
        page: 1,
        per_page: 12,
        search: '',
        sort: 'newest'
    };

    // DOM references
    var $productsGrid, $paginationWrap, $countEl, $skeletonWrap;
    var offerData = null; // Store full offer response

    $(document).ready(function () {
        $productsGrid = $('#od-products-grid');
        $paginationWrap = $('#od-products-pagination');
        $countEl = $('#od-products-count');
        $skeletonWrap = $('#od-products-skeleton');

        if (!offerSlug) {
            showError('Offer slug is missing. Please check the URL.');
            return;
        }

        // Read URL params for product filters
        readUrlParams();

        // Load offer detail + first page of eligible products
        loadOfferDetail();
    });

    // ============================================================
    // READ URL PARAMS
    // ============================================================
    function readUrlParams() {
        var params = new URLSearchParams(window.location.search);

        if (params.get('page')) state.page = parseInt(params.get('page')) || 1;
        if (params.get('per_page')) state.per_page = parseInt(params.get('per_page')) || 12;
        if (params.get('search')) state.search = params.get('search');
        if (params.get('sort')) state.sort = params.get('sort');

        // Populate search input
        if (state.search) {
            var $input = $('#od-product-search');
            if ($input.length) $input.val(state.search);
        }

        // Populate sort dropdown
        if (state.sort) {
            var $sort = $('#od-product-sort');
            if ($sort.length) $sort.val(state.sort);
        }
    }

    // ============================================================
    // UPDATE URL PARAMS
    // ============================================================
    function updateUrlParams(resetPage) {
        if (resetPage) state.page = 1;

        var params = new URLSearchParams();

        if (state.page > 1) params.set('page', state.page);
        if (state.per_page !== 12) params.set('per_page', state.per_page);
        if (state.search) params.set('search', state.search);
        if (state.sort !== 'newest') params.set('sort', state.sort);

        var queryString = params.toString();
        var newUrl = BASE_URL + '/offers/' + offerSlug + (queryString ? '?' + queryString : '');

        window.history.replaceState(state, '', newUrl);
    }

    // ============================================================
    // LOAD OFFER DETAIL
    // ============================================================
    function loadOfferDetail() {
        showPageSkeleton();

        var params = {
            action: 'detail',
            slug: offerSlug,
            page: state.page,
            per_page: state.per_page,
            sort: state.sort
        };
        if (state.search) params.search = state.search;

        $.getJSON(BASE_URL + '/api/site/offers.php', params, function (res) {
            if (!res.success) {
                if (res.message && res.message.indexOf('not found') !== -1) {
                    show404();
                } else {
                    showError(res.message || 'Failed to load offer details.');
                }
                return;
            }

            offerData = res;

            hidePageSkeleton();
            renderHero(res.offer, res.brand);
            renderImage(res.offer, res.brand);
            renderCouponBox(res.offer);
            renderDiscount(res.offer);
            renderDescription(res.offer);
            renderValidity(res.offer);
            renderBrandCard(res.brand);
            renderCountries(res.countries);

            if (res.products.length === 0) {
                $productsGrid.html('');
                showEmptyProducts();
            } else {
                renderProducts(res.products);
            }
            renderPagination(res.pagination);
            updateCount(res.pagination.total_items, res.pagination.current_page, res.pagination.per_page);
            updateUrlParams(false);

            renderOtherOffers(res.other_offers);
            initShareButtons(res.offer, res.brand);

            // Inject Schema.org JSON-LD returned by the API
            if (res.schema_json) {
                $('head').append('<script type="application/ld+json">' + res.schema_json + '<' + '/script>');
            }

            bindEvents();
            window.refreshAOS();

        }).fail(function () {
            showError('Network error. Please check your connection and try again.');
        });
    }

    // ============================================================
    // LOAD PRODUCTS ONLY (after initial load — filters/pagination)
    // ============================================================
    function loadProductsOnly() {
        showProductsSkeleton();

        var params = {
            action: 'detail',
            slug: offerSlug,
            page: state.page,
            per_page: state.per_page,
            sort: state.sort
        };
        if (state.search) params.search = state.search;

        $.getJSON(BASE_URL + '/api/site/offers.php', params, function (res) {
            if (!res.success) {
                showError('Failed to load products. Please try again.');
                return;
            }

            hideProductsSkeleton();

            if (res.products.length === 0) {
                $productsGrid.html('');
                showEmptyProducts();
            } else {
                renderProducts(res.products);
            }

            renderPagination(res.pagination);
            updateCount(res.pagination.total_items, res.pagination.current_page, res.pagination.per_page);
            updateUrlParams(false);
            window.refreshAOS();

        }).fail(function () {
            showError('Network error while loading products.');
        });
    }

    // ============================================================
    // RENDER HERO (breadcrumb, status badge, title, brand row)
    // ============================================================
    function renderHero(offer, brand) {
        var $breadcrumb = $('#od-breadcrumb-title');
        if ($breadcrumb.length) $breadcrumb.text(offer.title);

        var $title = $('#od-title');
        if ($title.length) $title.text(offer.title);

        // Status badge
        var $badge = $('#od-status-badge');
        if ($badge.length) {
            var badgeHtml = '';
            if (!offer.is_active) {
                badgeHtml = '<span style="padding:0.3rem 0.85rem;border-radius:var(--radius-full);background:var(--muted);color:#fff;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;">Expired</span>';
            } else if (offer.days_remaining <= 3) {
                badgeHtml = '<span style="padding:0.3rem 0.85rem;border-radius:var(--radius-full);background:var(--warning);color:#fff;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;"><i class="fa-solid fa-clock" style="margin-right:0.3rem;"></i>Ending Soon</span>';
            } else {
                badgeHtml = '<span style="padding:0.3rem 0.85rem;border-radius:var(--radius-full);background:var(--accent);color:#fff;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;"><i class="fa-solid fa-circle-check" style="margin-right:0.3rem;"></i>Active</span>';
            }
            $badge.html(badgeHtml);
        }

        // Brand row
        var $row = $('#od-brand-row');
        if ($row.length && brand) {
            var html = '';
            if (brand.logo) {
                html += '<a href="' + brand.url + '"><img src="' + brand.logo + '" alt="' + escapeHtml(brand.name) + '" style="width:32px;height:32px;object-fit:contain;border-radius:6px;background:#fff;padding:4px;"></a>';
            }
            html += '<a href="' + brand.url + '" style="color:rgba(255,255,255,0.85);font-weight:600;font-size:0.95rem;">' + escapeHtml(brand.name) + '</a>';
            $row.html(html);
        }
    }

    // ============================================================
    // RENDER OFFER IMAGE
    // ============================================================
    function renderImage(offer, brand) {
        var $img = $('#od-image');
        if (!$img.length) return;

        var src = offer.image || (brand ? brand.cover_image : '') || (brand ? brand.logo : '');
        if (src) {
            $img.attr('src', src).attr('alt', offer.title);
        } else {
            $img.parent().html(
                '<div style="width:100%;height:320px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--secondary),#2D1B4E);">' +
                '<i class="fa-solid fa-tags" style="font-size:3rem;color:rgba(255,255,255,0.2);"></i></div>'
            );
        }
    }

    // ============================================================
    // RENDER COUPON BOX
    // ============================================================
    function renderCouponBox(offer) {
        var $box = $('#od-coupon-box');
        if (!$box.length) return;

        if (!offer.coupon_code) {
            $box.hide();
            return;
        }

        var html = '<div style="font-size:0.78rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.5rem;">Use Coupon Code</div>';
        html += '<div style="display:flex;align-items:center;justify-content:center;gap:0.75rem;">';
        html += '<span style="font-family:monospace;font-size:1.3rem;font-weight:800;color:var(--primary);letter-spacing:0.05em;">' + escapeHtml(offer.coupon_code) + '</span>';
        html += '<button class="btn-copy-coupon" data-code="' + escapeHtml(offer.coupon_code) + '" title="Copy code" style="width:36px;height:36px;border-radius:var(--radius-full);border:1px solid var(--border);background:var(--surface);color:var(--primary);cursor:pointer;">';
        html += '<i class="fa-solid fa-copy"></i></button>';
        html += '</div>';

        $box.html(html).show();
    }

    // ============================================================
    // RENDER DISCOUNT
    // ============================================================
    function renderDiscount(offer) {
        var $discount = $('#od-discount');
        if ($discount.length) {
            $discount.text(offer.discount_percent + '% OFF');
        }
    }

    // ============================================================
    // RENDER DESCRIPTION
    // ============================================================
    function renderDescription(offer) {
        var $desc = $('#od-description');
        if (!$desc.length) return;

        if (offer.description) {
            $desc.html(offer.description);
        } else {
            $desc.html('<p>Enjoy ' + offer.discount_percent + '% off at checkout on eligible items from this brand.</p>');
        }
    }

    // ============================================================
    // RENDER VALIDITY / COUNTDOWN
    // ============================================================
    function renderValidity(offer) {
        var $box = $('#od-validity');
        if (!$box.length) return;

        var progressPct = 0;
        if (offer.total_days > 0) {
            var elapsed = offer.total_days - offer.days_remaining;
            progressPct = Math.min(100, Math.max(0, Math.round((elapsed / offer.total_days) * 100)));
        }

        var html = '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.6rem;">';
        html += '<span style="font-size:0.85rem;color:var(--text-secondary);"><i class="fa-regular fa-calendar" style="margin-right:0.35rem;"></i>' + formatDate(offer.start_date) + ' – ' + formatDate(offer.end_date) + '</span>';

        if (offer.is_active) {
            html += '<span style="font-size:0.85rem;font-weight:700;color:' + (offer.days_remaining <= 3 ? 'var(--warning)' : 'var(--success)') + ';">' + offer.days_remaining + ' day' + (offer.days_remaining === 1 ? '' : 's') + ' left</span>';
        } else {
            html += '<span style="font-size:0.85rem;font-weight:700;color:var(--muted);">Offer expired</span>';
        }
        html += '</div>';

        html += '<div style="height:8px;border-radius:var(--radius-full);background:var(--border-light);overflow:hidden;">';
        html += '<div style="height:100%;width:' + progressPct + '%;background:' + (offer.is_active ? 'linear-gradient(90deg,var(--primary),var(--primary-light))' : 'var(--muted)') + ';border-radius:var(--radius-full);"></div>';
        html += '</div>';

        $box.html(html);
    }

    // ============================================================
    // RENDER BRAND CARD
    // ============================================================
    function renderBrandCard(brand) {
        var $card = $('#od-brand-card');
        if (!$card.length || !brand) return;

        var html = '';
        if (brand.logo) {
            html += '<img src="' + brand.logo + '" alt="' + escapeHtml(brand.name) + '" style="width:52px;height:52px;object-fit:contain;border-radius:var(--radius-sm);background:var(--bg-alt);padding:6px;flex-shrink:0;">';
        }
        html += '<div style="flex:1;">';
        html += '<div style="font-weight:700;font-size:1rem;margin-bottom:0.2rem;">' + escapeHtml(brand.name) + '</div>';
        if (brand.short_description) {
            var plainDesc = $('<div>').html(brand.short_description).text();
            if (plainDesc.length > 100) plainDesc = plainDesc.substring(0, 97) + '...';
            html += '<div style="font-size:0.82rem;color:var(--text-secondary);">' + escapeHtml(plainDesc) + '</div>';
        }
        html += '</div>';
        html += '<a href="' + brand.url + '" style="padding:0.5rem 1.1rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-size:0.82rem;font-weight:600;white-space:nowrap;">Visit Brand</a>';

        $card.html(html);
    }

    // ============================================================
    // RENDER COUNTRIES
    // ============================================================
    function renderCountries(countries) {
        var $container = $('#od-countries');
        if (!$container.length) return;

        if (!countries || countries.length === 0) {
            $container.closest('#od-countries-section').hide();
            return;
        }

        var html = '';
        $.each(countries, function (i, c) {
            var activeStyle = c.is_current ? 'border:2px solid var(--primary);' : 'border:2px solid var(--border);';
            html += '<div class="text-center" style="min-width:70px;" title="' + escapeHtml(c.name) + '">';
            html += '  <img src="' + c.flag_url + '" alt="' + escapeHtml(c.name) + '" style="width:40px;height:28px;object-fit:cover;border-radius:4px;' + activeStyle + '" loading="lazy">';
            html += '  <div style="font-size:0.7rem;color:var(--muted);margin-top:0.3rem;white-space:nowrap;">' + escapeHtml(c.name) + '</div>';
            html += '</div>';
        });

        $container.html(html);
    }

    // ============================================================
    // RENDER ELIGIBLE PRODUCTS
    // ============================================================
    function renderProducts(products) {
        var html = '';
        $.each(products, function (i, p) {
            html += buildProductCard(p, i);
        });
        $productsGrid.html(html);
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
        if (p.effective_discount > 0) {
            html += '<span class="pc-discount-badge">-' + p.effective_discount + '%</span>';
        }
        html += '<div class="pc-actions">';
        html += '  <button class="pc-action-btn btn-quick-view" data-slug="' + p.slug + '" title="Quick View"><i class="fa-solid fa-eye"></i></button>';
        html += '  <button class="pc-action-btn btn-copy-link" data-url="' + p.url + '" title="Copy Link"><i class="fa-solid fa-link"></i></button>';
        html += '</div>';
        html += '</div>';

        // Body
        html += '<div class="pc-body">';
        html += '<h4 class="pc-name"><a href="' + p.url + '" style="color:var(--text);transition:color 0.3s;">' + escapeHtml(p.name) + '</a></h4>';

        if (p.category_name) {
            html += '<div class="pc-category"><a href="' + BASE_URL + '/category/' + p.category_slug + '" style="color:var(--muted);">' + escapeHtml(p.category_name) + '</a></div>';
        }

        if (p.calories > 0) {
            html += '<div class="pc-meta"><span><i class="fa-solid fa-fire"></i> ' + p.calories + ' cal</span></div>';
        }

        // Footer: original price struck through + offer price
        html += '<div class="pc-footer">';
        html += '<div class="pc-prices">';
        if (p.formatted_regular) {
            html += '<span class="pc-original-price">' + p.formatted_regular + '</span>';
        }
        if (p.formatted_offer_price) {
            html += '<span class="pc-current-price">' + p.formatted_offer_price + '</span>';
        } else {
            html += '<span class="pc-current-price" style="font-size:0.85rem;color:var(--muted);">N/A</span>';
        }
        html += '</div>';
        html += '<a href="' + p.url + '" class="pc-view-btn">View</a>';
        html += '</div>';

        if (p.formatted_saved) {
            html += '<div style="font-size:0.72rem;color:var(--success);font-weight:600;margin-top:0.4rem;"><i class="fa-solid fa-tag" style="margin-right:0.25rem;"></i>' + p.formatted_saved + '</div>';
        }

        html += '</div>'; // pc-body
        html += '</div>'; // product-card

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

    // Coupon copy delegation
    $(document).on('click', '.btn-copy-coupon', function () {
        var code = $(this).data('code');
        if (code && window.copyToClipboard) {
            window.copyToClipboard(code, 'Coupon code "' + code + '" copied!');
        }
    });

    // ============================================================
    // RENDER PAGINATION
    // ============================================================
    function renderPagination(pagination) {
        if (!window.buildPagination) return;

        var html = window.buildPagination(pagination);
        $paginationWrap.html(html);

        window.bindPagination($paginationWrap, function (page) {
            state.page = page;
            loadProductsOnly();
            scrollToProducts();
        });
    }

    // ============================================================
    // UPDATE COUNT
    // ============================================================
    function updateCount(total, page, perPage) {
        if (!$countEl.length) return;

        if (total === 0) {
            $countEl.html('No eligible products found');
        } else {
            var start = (page - 1) * perPage + 1;
            var end = Math.min(page * perPage, total);
            $countEl.html('Showing <strong>' + start + '–' + end + '</strong> of <strong>' + total + '</strong> products');
        }
    }

    // ============================================================
    // RENDER OTHER OFFERS FROM SAME BRAND
    // ============================================================
    function renderOtherOffers(offers) {
        var $container = $('#od-other-offers');
        if (!$container.length) return;

        if (!offers || offers.length === 0) {
            $container.closest('#od-other-offers-section').hide();
            return;
        }

        var html = '';
        $.each(offers, function (i, offer) {
            html += '<div class="offer-card" data-aos="fade-up" data-aos-delay="' + (i * 60) + '">';
            html += '<div class="oc-body">';
            html += '<h4 class="oc-title"><a href="' + BASE_URL + '/offers/' + offer.slug + '" style="color:var(--text);">' + escapeHtml(offer.title) + '</a></h4>';
            html += '<div class="oc-footer">';
            html += '<div class="oc-discount">' + offer.discount_percent + '% OFF</div>';
            if (offer.coupon_code) {
                html += '<span class="oc-code btn-copy-coupon" data-code="' + escapeHtml(offer.coupon_code) + '" title="Click to copy" style="cursor:pointer;">' + escapeHtml(offer.coupon_code) + '</span>';
            }
            html += '</div>';
            if (offer.days_remaining > 0) {
                html += '<div style="margin-top:0.6rem;font-size:0.75rem;color:var(--muted);"><i class="fa-solid fa-clock" style="margin-right:0.25rem;"></i>' + offer.days_remaining + ' days left</div>';
            }
            html += '</div>';
            html += '</div>';
        });

        $container.html(html);
    }

    // ============================================================
    // SHARE BUTTONS
    // ============================================================
    function initShareButtons(offer, brand) {
        var pageUrl = window.location.href;
        var pageTitle = offer.title + ' — ' + (brand ? brand.name : 'FoodScope');

        $(document).on('click', '.pd-share-copy', function () {
            if (window.copyToClipboard) {
                window.copyToClipboard(pageUrl, 'Offer link copied to clipboard!');
            }
        });

        $(document).on('click', '.pd-share-facebook', function () {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(pageUrl), '_blank', 'width=600,height=400');
        });

        $(document).on('click', '.pd-share-twitter', function () {
            var text = encodeURIComponent('Check out this offer: ' + offer.title + '!');
            window.open('https://twitter.com/intent/tweet?text=' + text + '&url=' + encodeURIComponent(pageUrl), '_blank', 'width=600,height=400');
        });

        $(document).on('click', '.pd-share-whatsapp', function () {
            var text = encodeURIComponent(offer.title + ' — ' + pageUrl);
            window.open('https://wa.me/?text=' + text, '_blank');
        });

        $(document).on('click', '.pd-share-linkedin', function () {
            window.open('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(pageUrl), '_blank', 'width=600,height=400');
        });

        $(document).on('click', '.pd-share-email', function () {
            var subject = encodeURIComponent(pageTitle);
            var body = encodeURIComponent('Check out this offer: ' + pageUrl);
            window.location.href = 'mailto:?subject=' + subject + '&body=' + body;
        });
    }

    // ============================================================
    // SCROLL TO PRODUCTS SECTION
    // ============================================================
    function scrollToProducts() {
        var offset = $('#main-header').outerHeight() + 20;
        var $target = $('#od-products-toolbar');
        if ($target.length) {
            $('html, body').animate({
                scrollTop: $target.offset().top - offset
            }, 400, 'swing');
        }
    }

    // ============================================================
    // SKELETON / ERROR / 404 / EMPTY STATES
    // ============================================================
    function showPageSkeleton() {
        var $pageSkeleton = $('#offer-page-skeleton');
        if ($pageSkeleton.length) {
            $pageSkeleton.show();
            $('#offer-detail-content').hide();
        }
    }

    function hidePageSkeleton() {
        var $pageSkeleton = $('#offer-page-skeleton');
        if ($pageSkeleton.length) {
            $pageSkeleton.hide();
            $('#offer-detail-content').show();
        }
    }

    function showProductsSkeleton() {
        if ($skeletonWrap.length) {
            $skeletonWrap.show();
            $productsGrid.hide();
            $paginationWrap.hide();
            $skeletonWrap.html(window.skeletonCards ? window.skeletonCards(state.per_page, 'product') : '');
        } else {
            $productsGrid.html(window.skeletonCards ? window.skeletonCards(state.per_page, 'product') : '');
        }
    }

    function hideProductsSkeleton() {
        if ($skeletonWrap.length) {
            $skeletonWrap.hide();
            $productsGrid.show();
            $paginationWrap.show();
        }
    }

    function showEmptyProducts() {
        var searchMsg = state.search ? ' matching "<strong>' + escapeHtml(state.search) + '</strong>"' : '';

        $productsGrid.html(
            '<div style="grid-column:1/-1;text-align:center;padding:3rem 2rem;">' +
            '<i class="fa-solid fa-utensils" style="font-size:2.5rem;color:var(--muted);opacity:0.3;margin-bottom:1rem;display:block;"></i>' +
            '<h3 style="font-family:var(--font-display);font-size:1.2rem;margin-bottom:0.5rem;">No eligible products found' + searchMsg + '</h3>' +
            '<p style="color:var(--text-secondary);font-size:0.9rem;">Try a different search term.</p>' +
            '</div>'
        );
    }

    function showError(msg) {
        hidePageSkeleton();
        var $content = $('#offer-detail-content');
        if ($content.length) {
            $content.html(
                '<div style="text-align:center;padding:4rem 2rem;">' +
                '<i class="fa-solid fa-exclamation-triangle" style="font-size:3rem;color:var(--danger);margin-bottom:1.5rem;display:block;"></i>' +
                '<h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:0.75rem;">Something went wrong</h2>' +
                '<p style="color:var(--text-secondary);max-width:450px;margin:0 auto 1.5rem;">' + escapeHtml(msg) + '</p>' +
                '<a href="' + BASE_URL + '/offers" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;">' +
                '<i class="fa-solid fa-arrow-left" style="font-size:0.75rem;"></i> Back to Offers</a>' +
                '</div>'
            );
            $content.show();
        }
    }

    function show404() {
        hidePageSkeleton();
        var $content = $('#offer-detail-content');
        if ($content.length) {
            $content.html(
                '<div class="error-page">' +
                '<div class="error-code">404</div>' +
                '<h2 class="error-title">Offer Not Found</h2>' +
                '<p class="error-desc">The offer you\'re looking for doesn\'t exist, has expired, or has been removed.</p>' +
                '<a href="' + BASE_URL + '/offers" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;">' +
                '<i class="fa-solid fa-arrow-left" style="font-size:0.75rem;"></i> Browse All Offers</a>' +
                '</div>'
            );
            $content.show();
        }
    }

    // ============================================================
    // BIND EVENTS (called once after first load)
    // ============================================================
    var eventsBound = false;

    function bindEvents() {
        if (eventsBound) return;
        eventsBound = true;

        // --- Search input (debounced) ---
        var searchTimer = null;
        $(document).on('input', '#od-product-search', function () {
            var val = $(this).val().trim();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                state.search = val;
                state.page = 1;
                updateUrlParams(true);
                loadProductsOnly();
            }, 500);
        });

        // --- Search form submit ---
        $(document).on('submit', '#od-product-search-form', function (e) {
            e.preventDefault();
            var val = $(this).find('#od-product-search').val().trim();
            state.search = val;
            state.page = 1;
            updateUrlParams(true);
            loadProductsOnly();
        });

        // --- Sort dropdown ---
        $(document).on('change', '#od-product-sort', function () {
            state.sort = $(this).val();
            state.page = 1;
            updateUrlParams(true);
            loadProductsOnly();
        });

        // --- Browser back/forward ---
        $(window).on('popstate', function (e) {
            if (e.originalEvent && e.originalEvent.state) {
                var s = e.originalEvent.state;
                state.page = s.page || 1;
                state.per_page = s.per_page || 12;
                state.search = s.search || '';
                state.sort = s.sort || 'newest';

                var $input = $('#od-product-search');
                if ($input.length) $input.val(state.search);

                var $sort = $('#od-product-sort');
                if ($sort.length) $sort.val(state.sort);

                loadProductsOnly();
            }
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

    function formatDate(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr + 'T00:00:00');
        if (isNaN(d.getTime())) return dateStr;
        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear();
    }

})();