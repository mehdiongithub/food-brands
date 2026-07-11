/**
 * brand-detail.js — Loaded ONLY on brand.php (Brand detail page)
 * Handles: brand info, gallery, countries, offers, category pills,
 * products grid with filters, pagination, URL params sync
 */

(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '/food-brands';

    // Brand slug from PHP (injected via inline script in brand.php)
    var brandSlug = window.BRAND_SLUG || '';

    // Current product filter state
    var state = {
        page: 1,
        per_page: 12,
        category_id: null,
        search: '',
        sort: 'newest'
    };

    // DOM references
    var $productsGrid, $paginationWrap, $countEl, $skeletonWrap;
    var brandData = null; // Store full brand response

    $(document).ready(function () {
        $productsGrid = $('#brand-products-grid');
        $paginationWrap = $('#brand-products-pagination');
        $countEl = $('#brand-products-count');
        $skeletonWrap = $('#brand-products-skeleton');

        if (!brandSlug) {
            showError('Brand slug is missing. Please check the URL.');
            return;
        }

        // Read URL params for product filters
        readUrlParams();

        // Load brand detail + first page of products
        loadBrandDetail();
    });

    // ============================================================
    // READ URL PARAMS
    // ============================================================
    function readUrlParams() {
        var params = new URLSearchParams(window.location.search);

        if (params.get('page')) state.page = parseInt(params.get('page')) || 1;
        if (params.get('per_page')) state.per_page = parseInt(params.get('per_page')) || 12;
        if (params.get('category_id')) state.category_id = parseInt(params.get('category_id')) || null;
        if (params.get('search')) state.search = params.get('search');
        if (params.get('sort')) state.sort = params.get('sort');

        // Populate search input
        if (state.search) {
            var $input = $('#brand-product-search');
            if ($input.length) $input.val(state.search);
        }

        // Populate sort dropdown
        if (state.sort) {
            var $sort = $('#brand-product-sort');
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
        if (state.category_id) params.set('category_id', state.category_id);
        if (state.search) params.set('search', state.search);
        if (state.sort !== 'newest') params.set('sort', state.sort);

        var queryString = params.toString();
        var newUrl = BASE_URL + '/brand/' + brandSlug + (queryString ? '?' + queryString : '');

        window.history.replaceState(state, '', newUrl);
    }

    // ============================================================
    // LOAD BRAND DETAIL
    // ============================================================
    function loadBrandDetail() {
        // Show page-level skeleton
        showPageSkeleton();

        var params = {
            action: 'detail',
            slug: brandSlug,
            page: state.page,
            per_page: state.per_page,
            sort: state.sort
        };

        if (state.category_id) params.category_id = state.category_id;
        if (state.search) params.search = state.search;

        $.getJSON(BASE_URL + '/api/site/brands.php', params, function (res) {
            if (!res.success) {
                if (res.message && res.message.indexOf('not found') !== -1) {
                    show404();
                } else {
                    showError(res.message || 'Failed to load brand details.');
                }
                return;
            }

            brandData = res;

            // Render all sections
            hidePageSkeleton();
            renderBrandHeader(res.brand);
            renderGallery(res.gallery);
            renderCountryFlags(res.countries);
            renderCategories(res.categories);
            renderOffers(res.offers);
            renderHistory(res.brand);

            // Render products
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

            // Bind events after first load
            bindEvents();

            // Refresh AOS for new content
            window.refreshAOS();

        }).fail(function () {
            showError('Network error. Please check your connection and try again.');
        });
    }

    // ============================================================
    // LOAD PRODUCTS ONLY (after initial load)
    // ============================================================
    function loadProductsOnly() {
        showProductsSkeleton();

        var params = {
            action: 'detail',
            slug: brandSlug,
            page: state.page,
            per_page: state.per_page,
            sort: state.sort
        };

        if (state.category_id) params.category_id = state.category_id;
        if (state.search) params.search = state.search;

        $.getJSON(BASE_URL + '/api/site/brands.php', params, function (res) {
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
    // RENDER BRAND HEADER
    // ============================================================
    function renderBrandHeader(brand) {
        // Cover image
        var $cover = $('#brand-cover-img');
        if ($cover.length) {
            if (brand.cover_image) {
                $cover.attr('src', brand.cover_image).attr('alt', escapeHtml(brand.name));
            } else {
                $cover.parent().html(
                    '<div style="width:100%;height:100%;background:linear-gradient(135deg,var(--secondary),#2D1B4E);display:flex;align-items:center;justify-content:center;">' +
                    '<span style="font-family:var(--font-display);font-size:5rem;font-weight:900;color:rgba(255,255,255,0.1);">' + escapeHtml(brand.name.charAt(0)) + '</span>' +
                    '</div>'
                );
            }
        }

        // Logo
        var $logo = $('#brand-logo-img');
        if ($logo.length) {
            if (brand.logo) {
                $logo.attr('src', brand.logo).attr('alt', escapeHtml(brand.name));
            } else {
                $logo.parent().html(
                    '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--surface);border-radius:var(--radius-md);">' +
                    '<span style="font-family:var(--font-display);font-size:2rem;font-weight:900;color:var(--primary);">' + escapeHtml(brand.name.charAt(0)) + '</span>' +
                    '</div>'
                );
            }
        }

        // Name
        var $name = $('#brand-name');
        if ($name.length) $name.text(brand.name);

        // Description
        var $desc = $('#brand-description');
        if ($desc.length && brand.short_description) {
            $desc.html(brand.short_description);
        }

        // Stats
        var $statProducts = $('#brand-stat-products');
        if ($statProducts.length) $statProducts.text(brand.total_products || 0);

        var $statCountries = $('#brand-stat-countries');
        if ($statCountries.length) {
            // Count from countries array
            var countryCount = brandData ? brandData.countries.length : 0;
            $statCountries.text(countryCount);
        }

        var $statCategories = $('#brand-stat-categories');
        if ($statCategories.length) {
            var catCount = brandData ? brandData.categories.length : 0;
            $statCategories.text(catCount);
        }

        // Website link
        var $website = $('#brand-website');
        if ($website.length && brand.website) {
            $website.attr('href', brand.website).show();
        } else if ($website.length) {
            $website.hide();
        }

        // Founded year
        var $founded = $('#brand-founded');
        if ($founded.length && brand.founded_year) {
            $founded.text('Founded in ' + brand.founded_year).show();
        } else if ($founded.length) {
            $founded.hide();
        }
    }

    // ============================================================
    // RENDER GALLERY (Swiper)
    // ============================================================
    function renderGallery(gallery) {
        var $container = $('#brand-gallery-grid');
        if (!$container.length) return;

        if (!gallery || gallery.length === 0) {
            $container.closest('.brand-gallery-section').hide();
            return;
        }

        var html = '';
        $.each(gallery, function (i, img) {
            var delay = Math.min(i * 60, 360);
            html += '<div class="col-6 col-md-4">';
            html += '  <a href="' + img.image + '" target="_blank" rel="noopener noreferrer" class="gallery-grid-item" data-aos="fade-up" data-aos-delay="' + delay + '">';
            html += '    <img src="' + img.image + '" alt="' + escapeHtml(brandData.brand.name) + ' gallery image ' + (i + 1) + '" loading="lazy">';
            html += '  </a>';
            html += '</div>';
        });

        $container.html(html);
    }

    // ============================================================
    // RENDER COUNTRY FLAGS
    // ============================================================
    function renderCountryFlags(countries) {
        var $container = $('#brand-countries');
        if (!$container.length) return;

        if (!countries || countries.length === 0) {
            $container.closest('.brand-countries-section').hide();
            return;
        }

        var html = '';
        $.each(countries, function (i, c) {
            var activeClass = c.is_current ? 'border:2px solid var(--primary);' : 'border:2px solid var(--border);';
            html += '<div class="text-center" style="min-width:70px;" title="' + escapeHtml(c.name) + '">';
            html += '  <img src="' + c.flag_url + '" alt="' + escapeHtml(c.name) + '" style="width:40px;height:28px;object-fit:cover;border-radius:4px;' + activeClass + '" loading="lazy">';
            html += '  <div style="font-size:0.7rem;color:var(--muted);margin-top:0.3rem;white-space:nowrap;">' + escapeHtml(c.name) + '</div>';
            html += '</div>';
        });

        $container.html(html);
    }

    // ============================================================
    // RENDER CATEGORY PILLS (filter tabs)
    // ============================================================
    function renderCategories(categories) {
        var $container = $('#brand-category-pills');
        if (!$container.length) return;

        if (!categories || categories.length === 0) {
            $container.hide();
            return;
        }

        var html = '';
        // "All" option
        html += '<button class="fb-cat-pill brand-cat-pill' + (!state.category_id ? ' active' : '') + '" data-cat-id="">';
        html += 'All Categories <span style="opacity:0.6;margin-left:0.25rem;">' + getTotalProductCount() + '</span>';
        html += '</button>';

        $.each(categories, function (i, cat) {
            var isActive = (state.category_id && state.category_id === cat.id);
            html += '<button class="fb-cat-pill brand-cat-pill m-2' + (isActive ? ' active' : '') + '" data-cat-id="' + cat.id + '">';
            html += escapeHtml(cat.name);
            html += '</button>';
        });

        $container.html(html).show();
    }

    function getTotalProductCount() {
        if (brandData && brandData.pagination) {
            return brandData.pagination.total_items;
        }
        return 0;
    }

    // ============================================================
    // RENDER OFFERS
    // ============================================================
    function renderOffers(offers) {
        var $container = $('#brand-offers');
        if (!$container.length) return;

        if (!offers || offers.length === 0) {
            $container.closest('.brand-offers-section').hide();
            return;
        }

        var html = '';
        $.each(offers, function (i, offer) {
            var plainDesc = $('<div>').html(offer.description || '').text();
            if (plainDesc.length > 80) plainDesc = plainDesc.substring(0, 77) + '...';

            html += '<div class="offer-card" data-aos="fade-up" data-aos-delay="' + (i * 60) + '">';
            html += '<div class="oc-body">';
            html += '<div class="oc-title">' + escapeHtml(offer.title) + '</div>';
            if (plainDesc) {
                html += '<p class="oc-desc">' + escapeHtml(plainDesc) + '</p>';
            }
            html += '<div class="oc-footer">';
            html += '<div class="oc-discount">' + offer.discount_percent + '% OFF</div>';
            if (offer.coupon_code) {
                html += '<span class="oc-code btn-copy-coupon" data-code="' + escapeHtml(offer.coupon_code) + '" title="Click to copy" style="cursor:pointer;">' + escapeHtml(offer.coupon_code) + '</span>';
            }
            html += '</div>';
            html += '</div>';
            html += '</div>';
        });

        $container.html(html);
    }

    // Copy coupon code (delegated)
    $(document).on('click', '.btn-copy-coupon', function () {
        var code = $(this).data('code');
        if (code && window.copyToClipboard) {
            window.copyToClipboard(code, 'Coupon code "' + code + '" copied!');
        }
    });

    // ============================================================
    // RENDER HISTORY
    // ============================================================
    function renderHistory(brand) {
        var $container = $('#brand-history');
        if (!$container.length) return;

        if (!brand.history || brand.history === '<p>sakaskn</p>' || stripHtml(brand.history).trim().length < 10) {
            $container.closest('.brand-history-section').hide();
            return;
        }

        $container.html(brand.history);
    }

    function stripHtml(html) {
        var tmp = document.createElement('div');
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || '';
    }

    // ============================================================
    // RENDER PRODUCTS
    // ============================================================
    function renderProducts(products) {
        var html = '';
        $.each(products, function (i, p) {
            html += buildProductCard(p, i);
        });
        $productsGrid.html(html);
    }

    function buildProductCard(p, index) {

    var delay = Math.min((index + 1) * 50, 400);

    var html = '';

    html += '<div class="col-lg-4 col-md-6 col-sm-6 mb-4">';
    html += '    <div class="product-card" data-aos="fade-up" data-aos-delay="' + delay + '">';

    // ==========================
    // Product Image
    // ==========================

    html += '        <div class="pc-image">';

    if (p.image) {
        html += '            <a href="' + p.url + '">';
        html += '                <img src="' + p.image + '" alt="' + escapeHtml(p.name) + '" loading="lazy">';
        html += '            </a>';
    } else {
        html += '            <img src="' + BASE_URL + '/assets/img/no-image.webp" alt="' + escapeHtml(p.name) + '" loading="lazy">';
    }

    // Discount Badge

    if (p.has_discount && p.discount_percent > 0) {
        html += '        <div class="pc-discount-badge">-' + p.discount_percent + '%</div>';
    }

    // ==========================
    // Action Buttons
    // ==========================

    html += '        <div class="pc-actions">';

    html += '            <button class="pc-action-btn" onclick="toggleFavorite(\'' + p.slug + '\')" aria-label="Add to favorites">';
    html += '                <i class="far fa-heart"></i>';
    html += '            </button>';

    html += '            <button class="pc-action-btn" onclick="quickView(\'' + p.slug + '\')" aria-label="Quick View">';
    html += '                <i class="far fa-eye"></i>';
    html += '            </button>';

    html += '            <button class="pc-action-btn" onclick="shareProduct(\'' + p.slug + '\')" aria-label="Share">';
    html += '                <i class="fas fa-share-alt"></i>';
    html += '            </button>';

    html += '        </div>';

    html += '    </div>';

    // ==========================
    // Body
    // ==========================

    html += '    <div class="pc-body">';

    // Brand

    html += '        <div class="pc-brand">';

    if (p.brand_logo) {
        html += '            <img src="' + p.brand_logo + '" alt="' + escapeHtml(p.brand_name) + '" loading="lazy">';
    }

    html += '            <span>' + escapeHtml(p.brand_name) + '</span>';

    html += '        </div>';

    // Product Name

    html += '        <div class="pc-name">';
    html += '            <a href="' + p.url + '">' + escapeHtml(p.name) + '</a>';
    html += '        </div>';

    // Category

    if (p.category_name) {
        html += '        <div class="pc-category">';
        html += escapeHtml(p.category_name);
        html += '        </div>';
    }

    // Calories

    html += '        <div class="pc-meta">';

    if (p.calories) {
        html += '            <span><i class="fas fa-fire"></i> ' + p.calories + ' cal</span>';
    }

    html += '        </div>';

    // Footer

    html += '        <div class="pc-footer">';

    html += '            <div class="pc-prices">';

    if (p.has_discount && p.formatted_discount) {

        html += '                <span class="pc-original-price">' + p.formatted_regular + '</span>';
        html += '                <span class="pc-current-price">' + p.formatted_discount + '</span>';

    } else if (p.formatted_regular) {

        html += '                <span class="pc-current-price">' + p.formatted_regular + '</span>';

    } else {

        html += '                <span class="pc-current-price">N/A</span>';

    }

    html += '            </div>';

    html += '            <button class="pc-view-btn" onclick="window.location.href=\'' + p.url + '\'">';
    html += '                Details';
    html += '            </button>';

    html += '        </div>';

    html += '    </div>';

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
        var $pageSkeleton = $('#brand-page-skeleton');
        if ($pageSkeleton.length) {
            $pageSkeleton.show();
            $('#brand-detail-content').hide();
        }
    }

    function hidePageSkeleton() {
        var $pageSkeleton = $('#brand-page-skeleton');
        if ($pageSkeleton.length) {
            $pageSkeleton.hide();
            $('#brand-detail-content').show();
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

    function showError(msg) {
        hidePageSkeleton();
        var $content = $('#brand-detail-content');
        if ($content.length) {
            $content.html(
                '<div style="text-align:center;padding:4rem 2rem;">' +
                '<i class="fa-solid fa-exclamation-triangle" style="font-size:3rem;color:var(--danger);margin-bottom:1.5rem;display:block;"></i>' +
                '<h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:0.75rem;">Something went wrong</h2>' +
                '<p style="color:var(--text-secondary);max-width:450px;margin:0 auto 1.5rem;">' + escapeHtml(msg) + '</p>' +
                '<a href="' + BASE_URL + '/brands" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;">' +
                '<i class="fa-solid fa-arrow-left" style="font-size:0.75rem;"></i> Back to Brands</a>' +
                '</div>'
            );
        }
    }

    function show404() {
        hidePageSkeleton();
        var $content = $('#brand-detail-content');
        if ($content.length) {
            $content.html(
                '<div class="error-page">' +
                '<div class="error-code">404</div>' +
                '<h2 class="error-title">Brand Not Found</h2>' +
                '<p class="error-desc">The brand you\'re looking for doesn\'t exist or has been removed.</p>' +
                '<a href="' + BASE_URL + '/brands" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;">' +
                '<i class="fa-solid fa-arrow-left" style="font-size:0.75rem;"></i> Browse All Brands</a>' +
                '</div>'
            );
        }
    }

    function showEmptyProducts() {
        var searchMsg = state.search ? ' matching "<strong>' + escapeHtml(state.search) + '</strong>"' : '';
        var catMsg = state.category_id ? ' in this category' : '';

        $productsGrid.html(
            '<div style="grid-column:1/-1;text-align:center;padding:3rem 2rem;">' +
            '<i class="fa-solid fa-utensils" style="font-size:2.5rem;color:var(--muted);opacity:0.3;margin-bottom:1rem;display:block;"></i>' +
            '<h3 style="font-family:var(--font-display);font-size:1.2rem;margin-bottom:0.5rem;">No products found' + searchMsg + catMsg + '</h3>' +
            '<p style="color:var(--text-secondary);font-size:0.9rem;">Try a different filter or search term.</p>' +
            '</div>'
        );
    }

    // ============================================================
    // BIND EVENTS (called once after first load)
    // ============================================================
    var eventsBound = false;

    function bindEvents() {
        if (eventsBound) return;
        eventsBound = true;

        // --- Category filter click ---
        $(document).on('click', '.brand-cat-pill', function () {
            var catId = $(this).data('cat-id');
            state.category_id = catId ? parseInt(catId) : null;
            state.page = 1;

            $('.brand-cat-pill').removeClass('active');
            $(this).addClass('active');

            updateUrlParams(true);
            loadProductsOnly();
        });

        // --- Search input (debounced) ---
        var searchTimer = null;
        $(document).on('input', '#brand-product-search', function () {
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
        $(document).on('submit', '#brand-product-search-form', function (e) {
            e.preventDefault();
            var val = $(this).find('#brand-product-search').val().trim();
            state.search = val;
            state.page = 1;
            updateUrlParams(true);
            loadProductsOnly();
        });

        // --- Sort dropdown ---
        $(document).on('change', '#brand-product-sort', function () {
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
                state.category_id = s.category_id || null;
                state.search = s.search || '';
                state.sort = s.sort || 'newest';

                // Update UI
                var $input = $('#brand-product-search');
                if ($input.length) $input.val(state.search);

                var $sort = $('#brand-product-sort');
                if ($sort.length) $sort.val(state.sort);

                // Update category list active state
                $('.brand-cat-pill').removeClass('active');
                var $activePill = $('.brand-cat-pill[data-cat-id="' + (state.category_id || '') + '"]');
                if ($activePill.length) {
                    $activePill.addClass('active');
                } else {
                    $('.brand-cat-pill[data-cat-id=""]').addClass('active');
                }

                loadProductsOnly();
            }
        });
    }

    // ============================================================
    // SCROLL TO PRODUCTS SECTION
    // ============================================================
    function scrollToProducts() {
        var offset = $('#main-header').outerHeight() + 20;
        var $target = $('#brand-products-toolbar');
        if ($target.length) {
            $('html, body').animate({
                scrollTop: $target.offset().top - offset
            }, 400, 'swing');
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