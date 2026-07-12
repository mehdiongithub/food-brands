/**
 * brand-detail.js — Loaded ONLY on brand.php (Brand detail page)
 * Handles: brand info, gallery, countries, offers, sidebar filters
 * (category checkboxes + price range), products grid, pagination, URL sync
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
        category_ids: [], // multi-select category checkboxes
        max_price: null,  // price range slider
        search: '',
        sort: 'newest'
    };

    // Pending sidebar selections (only committed to `state` on Apply)
    var pending = {
        category_ids: [],
        max_price: null
    };

    // DOM references
    var $productsGrid, $paginationWrap, $countEl, $skeletonWrap;
    var brandData = null; // Store full brand response
    var priceBounds = { min: 0, max: 999 };

    $(document).ready(function () {
        $productsGrid = $('#brand-products-grid');
        $paginationWrap = $('#brand-products-pagination');
        $countEl = $('#brand-products-count');
        $skeletonWrap = $('#brand-products-skeleton');

        if (!brandSlug) {
            showError('Brand slug is missing. Please check the URL.');
            return;
        }

        readUrlParams();
        loadBrandDetail();
    });

    // ============================================================
    // READ URL PARAMS
    // ============================================================
    function readUrlParams() {
        var params = new URLSearchParams(window.location.search);

        if (params.get('page')) state.page = parseInt(params.get('page')) || 1;
        if (params.get('per_page')) state.per_page = parseInt(params.get('per_page')) || 12;
        if (params.get('category_ids')) {
            state.category_ids = params.get('category_ids').split(',').map(function (v) { return parseInt(v); }).filter(Boolean);
        }
        if (params.get('max_price')) state.max_price = parseFloat(params.get('max_price'));
        if (params.get('search')) state.search = params.get('search');
        if (params.get('sort')) state.sort = params.get('sort');

        pending.category_ids = state.category_ids.slice();
        pending.max_price = state.max_price;

        if (state.search) {
            var $input = $('#brand-product-search');
            if ($input.length) $input.val(state.search);
        }

        if (state.sort) {
            $('#brand-product-sort, #brand-product-sort-mobile').val(state.sort);
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
        if (state.category_ids.length) params.set('category_ids', state.category_ids.join(','));
        if (state.max_price !== null && state.max_price !== undefined) params.set('max_price', state.max_price);
        if (state.search) params.set('search', state.search);
        if (state.sort !== 'newest') params.set('sort', state.sort);

        var queryString = params.toString();
        var newUrl = BASE_URL + '/brand/' + brandSlug + (queryString ? '?' + queryString : '');

        window.history.replaceState(state, '', newUrl);
    }

    function buildRequestParams() {
        var params = {
            action: 'detail',
            slug: brandSlug,
            page: state.page,
            per_page: state.per_page,
            sort: state.sort
        };

        if (state.category_ids.length) params.category_ids = state.category_ids.join(',');
        if (state.max_price !== null && state.max_price !== undefined) params.max_price = state.max_price;
        if (state.search) params.search = state.search;

        return params;
    }

    // ============================================================
    // LOAD BRAND DETAIL
    // ============================================================
    function loadBrandDetail() {
        showPageSkeleton();

        $.getJSON(BASE_URL + '/api/site/brands.php', buildRequestParams(), function (res) {
            if (!res.success) {
                if (res.message && res.message.indexOf('not found') !== -1) {
                    show404();
                } else {
                    showError(res.message || 'Failed to load brand details.');
                }
                return;
            }

            brandData = res;

            if (res.price_bounds) {
                priceBounds.min = res.price_bounds.min || 0;
                priceBounds.max = res.price_bounds.max || 0;
            }

            hidePageSkeleton();
            renderBrandHeader(res.brand);
            renderGallery(res.gallery);
            renderCountryFlags(res.countries);
            renderCategoryFilters(res.categories);
            renderPriceRange();
            renderOffers(res.offers);
            renderHistory(res.brand);

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

            bindEvents();
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

        $.getJSON(BASE_URL + '/api/site/brands.php', buildRequestParams(), function (res) {
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

        var $logo = $('#brand-logo-img img');
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

        var $name = $('#brand-name');
        if ($name.length) $name.text(brand.name);

        var $desc = $('#brand-description');
        if ($desc.length && brand.short_description) {
            $desc.html(brand.short_description);
        }

        var $statProducts = $('#brand-stat-products');
        if ($statProducts.length) $statProducts.text(brand.total_products || 0);

        var $statCountries = $('#brand-stat-countries');
        if ($statCountries.length) {
            var countryCount = brandData ? brandData.countries.length : 0;
            $statCountries.text(countryCount);
        }

        var $statCategories = $('#brand-stat-categories');
        if ($statCategories.length) {
            var catCount = brandData ? brandData.categories.length : 0;
            $statCategories.text(catCount);
        }

        var $website = $('#brand-website');
        if ($website.length && brand.website) {
            $website.attr('href', brand.website).show();
        } else if ($website.length) {
            $website.hide();
        }

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
    // RENDER SIDEBAR — CATEGORY CHECKBOXES (desktop + mobile)
    // ============================================================
    function renderCategoryFilters(categories) {
        var html = '';

        if (!categories || categories.length === 0) {
            html = '<div style="font-size:0.82rem;color:var(--muted);">No categories available.</div>';
        } else {
            $.each(categories, function (i, cat) {
                var checked = pending.category_ids.indexOf(cat.id) !== -1 ? 'checked' : '';
                html += '<label class="filter-option">';
                html += '  <input type="checkbox" name="category_id" value="' + cat.id + '" ' + checked + '>';
                html += '  <span>' + escapeHtml(cat.name) + '</span>';
                html += '  <span class="count">' + cat.product_count + '</span>';
                html += '</label>';
            });
        }

        $('#brand-filter-categories').html(html);
        $('#brand-filter-categories-mobile').html(html);
    }

    // ============================================================
    // RENDER SIDEBAR — PRICE RANGE SLIDER (desktop + mobile)
    // ============================================================
    function renderPriceRange() {
        var min = priceBounds.min || 0;
        var max = priceBounds.max > min ? priceBounds.max : min + 1;
        var current = (pending.max_price !== null && pending.max_price !== undefined) ? pending.max_price : max;

        $('#brand-filter-price, #brand-filter-price-mobile').attr('min', min).attr('max', max).val(current);
        $('#brand-price-min-label, #brand-price-min-label-mobile').text(formatPrice(min));
        $('#brand-price-max-label, #brand-price-max-label-mobile').text(formatPrice(current));
    }

    function formatPrice(val) {
        var symbol = (brandData && brandData.products.length && brandData.products[0].formatted_regular)
            ? brandData.products[0].formatted_regular.replace(/[0-9.,]/g, '')
            : '$';
        return symbol + Math.round(val);
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

        if (!brand.history || stripHtml(brand.history).trim().length < 10) {
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

        var html = '<div class="col-lg-4 col-md-6 col-sm-6 mb-4" data-aos="fade-up" data-aos-delay="' + delay + '">';
        html += '<div class="product-card">';

        // Image
        html += '<div class="pc-image">';
        if (p.image) {
            html += '<a href="' + p.url + '"><img src="' + p.image + '" alt="' + escapeHtml(p.name) + '" loading="lazy"></a>';
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
            html += '<img src="' + p.brand_logo + '" alt="' + escapeHtml(p.brand_name || '') + '" loading="lazy">';
        }
        html += '<span>' + escapeHtml(p.brand_name || brandData.brand.name) + '</span>';
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
        var catMsg = state.category_ids.length ? ' in the selected categories' : '';

        $productsGrid.html(
            '<div style="grid-column:1/-1;text-align:center;padding:3rem 2rem;">' +
            '<i class="fa-solid fa-utensils" style="font-size:2.5rem;color:var(--muted);opacity:0.3;margin-bottom:1rem;display:block;"></i>' +
            '<h3 style="font-family:var(--font-display);font-size:1.2rem;margin-bottom:0.5rem;">No products found' + searchMsg + catMsg + '</h3>' +
            '<p style="color:var(--text-secondary);font-size:0.9rem;">Try a different filter or search term.</p>' +
            '<button class="btn-reset-brand-filters filter-reset-btn" style="max-width:200px;margin:1rem auto 0;">Clear Filters</button>' +
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

        // --- Category checkboxes (desktop + mobile, multi-select, pending only) ---
        $(document).on('change', '#brand-filter-categories input[type="checkbox"], #brand-filter-categories-mobile input[type="checkbox"]', function () {
            var val = parseInt($(this).val());
            var checked = $(this).is(':checked');

            if (checked && pending.category_ids.indexOf(val) === -1) {
                pending.category_ids.push(val);
            } else if (!checked) {
                pending.category_ids = pending.category_ids.filter(function (v) { return v !== val; });
            }

            $('#brand-filter-categories input[value="' + val + '"], #brand-filter-categories-mobile input[value="' + val + '"]').prop('checked', checked);
        });

        // --- Price range slider (desktop + mobile, pending only) ---
        $(document).on('input', '#brand-filter-price, #brand-filter-price-mobile', function () {
            var val = parseFloat($(this).val());
            pending.max_price = val;
            $('#brand-filter-price, #brand-filter-price-mobile').val(val);
            $('#brand-price-max-label, #brand-price-max-label-mobile').text(formatPrice(val));
        });

        // --- Search input (debounced, applies immediately) ---
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

        $(document).on('submit', '#brand-product-search-form', function (e) {
            e.preventDefault();
            var val = $(this).find('#brand-product-search').val().trim();
            state.search = val;
            state.page = 1;
            updateUrlParams(true);
            loadProductsOnly();
        });

        // --- Sort dropdown (desktop + mobile, applies immediately) ---
        $(document).on('change', '#brand-product-sort, #brand-product-sort-mobile', function () {
            state.sort = $(this).val();
            $('#brand-product-sort, #brand-product-sort-mobile').val(state.sort);
            state.page = 1;
            updateUrlParams(true);
            loadProductsOnly();
        });

        // --- Apply Filters button (desktop + mobile) ---
        $(document).on('click', '#btn-brand-apply-filters, #btn-brand-apply-filters-mobile', function () {
            state.category_ids = pending.category_ids.slice();
            state.max_price = pending.max_price;
            state.page = 1;
            updateUrlParams(true);
            loadProductsOnly();
            closeMobileFilter();
        });

        // --- Reset Filters button (desktop + mobile + empty-state) ---
        $(document).on('click', '#btn-brand-reset-filters, #btn-brand-reset-filters-mobile, .btn-reset-brand-filters', function () {
            state.category_ids = [];
            state.max_price = null;
            state.search = '';
            state.sort = 'newest';
            state.page = 1;

            pending.category_ids = [];
            pending.max_price = null;

            $('#brand-filter-categories input[type="checkbox"], #brand-filter-categories-mobile input[type="checkbox"]').prop('checked', false);
            $('#brand-product-search').val('');
            $('#brand-product-sort, #brand-product-sort-mobile').val('newest');
            renderPriceRange();

            updateUrlParams(true);
            loadProductsOnly();
            closeMobileFilter();
        });

        // --- Mobile filter drawer open/close ---
        $(document).on('click', '#btn-mobile-brand-filter', function () {
            $('#brand-mobile-filter-panel').css('left', '0');
            $('#brand-mobile-filter-overlay').show();
            $('body').css('overflow', 'hidden');
        });

        $(document).on('click', '#btn-close-brand-mobile-filter, #brand-mobile-filter-overlay', function () {
            closeMobileFilter();
        });

        // --- Browser back/forward ---
        $(window).on('popstate', function (e) {
            if (e.originalEvent && e.originalEvent.state) {
                var s = e.originalEvent.state;
                state.page = s.page || 1;
                state.per_page = s.per_page || 12;
                state.category_ids = s.category_ids || [];
                state.max_price = (s.max_price !== undefined) ? s.max_price : null;
                state.search = s.search || '';
                state.sort = s.sort || 'newest';

                pending.category_ids = state.category_ids.slice();
                pending.max_price = state.max_price;

                var $input = $('#brand-product-search');
                if ($input.length) $input.val(state.search);

                $('#brand-product-sort, #brand-product-sort-mobile').val(state.sort);

                $('#brand-filter-categories input[type="checkbox"], #brand-filter-categories-mobile input[type="checkbox"]').each(function () {
                    var val = parseInt($(this).val());
                    $(this).prop('checked', state.category_ids.indexOf(val) !== -1);
                });
                renderPriceRange();

                loadProductsOnly();
            }
        });
    }

    function closeMobileFilter() {
        $('#brand-mobile-filter-panel').css('left', '-320px');
        $('#brand-mobile-filter-overlay').hide();
        $('body').css('overflow', '');
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

})();