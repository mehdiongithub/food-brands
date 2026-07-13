/**
 * product-detail.js — Loaded ONLY on product.php (Product detail page)
 * Handles: gallery with zoom, pricing, nutrition, ingredients,
 * country price comparison, offers, related products, share
 */

(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '/food-brands';

    // Product slug from PHP (injected via inline script in product.php)
    var productSlug = window.PRODUCT_SLUG || '';

    // Store loaded data
    var productData = null;

    $(document).ready(function () {
        if (!productSlug) {
            showError('Product slug is missing. Please check the URL.');
            return;
        }

        loadProductDetail();
    });

    // ============================================================
    // LOAD PRODUCT DETAIL
    // ============================================================
    function loadProductDetail() {
        // Show skeleton
        showPageSkeleton();

        $.getJSON(BASE_URL + '/api/site/products.php', {
            action: 'detail',
            slug: productSlug
        }, function (res) {
            if (!res.success) {
                if (res.message && res.message.indexOf('not found') !== -1) {
                    show404();
                } else {
                    showError(res.message || 'Failed to load product details.');
                }
                return;
            }

            productData = res;

            hidePageSkeleton();
            renderGallery(res.images);
            renderBrandInfo(res.brand);
            renderProductInfo(res.product);
            renderPricing(res.current_price);
            renderDetailsGrid(res.product, res.current_price);
            renderNutrition(res.nutrition);
            renderIngredients(res.ingredients);
            renderCountryPrices(res.country_prices);
            renderOffers(res.offers);
            renderRelatedProducts(res.related_products);
            initShareButtons(res.product);

            window.refreshAOS();

        }).fail(function () {
            showError('Network error. Please check your connection and try again.');
        });
    }

    // ============================================================
    // RENDER GALLERY (Main Image + Thumbnails + Zoom)
    // ============================================================
    function renderGallery(images) {
        var $mainImg = $('.pd-main-image img');
        var $thumbs = $('#pd-thumbs');
        var $gallery = $('.pd-gallery');

        if (!images || images.length === 0) {
            // Show placeholder
            if ($mainImg.length) {
                $mainImg.attr('src', '').parent().html(
                    '<div style="width:100%;height:420px;background:var(--bg-alt);display:flex;align-items:center;justify-content:center;color:var(--muted);">' +
                    '<i class="fa-solid fa-utensils" style="font-size:3rem;"></i></div>'
                );
            }
            if ($thumbs.length) $thumbs.hide();
            return;
        }

        // Set main image
        if ($mainImg.length) {
            $mainImg.attr('src', images[0].image).attr('alt', productData.product.name);
        }

        // Render thumbnails
        if ($thumbs.length && images.length > 1) {
            var html = '';
            $.each(images, function (i, img) {
                var activeClass = i === 0 ? ' active' : '';
                html += '<div class="pd-thumb' + activeClass + '" data-src="' + img.image + '" data-index="' + i + '">';
                html += '  <img src="' + img.image + '" alt="Thumbnail ' + (i + 1) + '" loading="lazy">';
                html += '</div>';
            });
            $thumbs.html(html).show();
        } else if ($thumbs.length) {
            $thumbs.hide();
        }

        // Thumbnail click — change main image
        $thumbs.find('.pd-thumb').on('click', function () {
            var src = $(this).data('src');
            var idx = $(this).data('index');

            // Update main image
            $mainImg.attr('src', src);

            // Update active thumb
            $thumbs.find('.pd-thumb').removeClass('active');
            $(this).addClass('active');
        });

        // Main image zoom on hover
        if ($gallery.length) {
            var $mainContainer = $gallery.find('.pd-main-image');
            $mainContainer.on('mousemove', function (e) {
                var rect = this.getBoundingClientRect();
                var x = ((e.clientX - rect.left) / rect.width) * 100;
                var y = ((e.clientY - rect.top) / rect.height) * 100;
                $mainImg.css('transform-origin', x + '% ' + y + '%');
                $mainImg.css('transform', 'scale(2)');
            });

            $mainContainer.on('mouseleave', function () {
                $mainImg.css('transform-origin', 'center center');
                $mainImg.css('transform', 'scale(1)');
            });
        }
    }

    // ============================================================
    // RENDER BRAND INFO
    // ============================================================
    function renderBrandInfo(brand) {
        if (!brand) return;

        var $row = $('#pd-brand-row');
        if (!$row.length) return;

        var html = '';
        if (brand.logo) {
            html += '<a href="' + brand.url + '"><img src="' + brand.logo + '" alt="' + escapeHtml(brand.name) + '" style="width:36px;height:36px;object-fit:contain;"></a>';
        }
        html += '<a href="' + brand.url + '" style="font-size:0.9rem;color:var(--primary);font-weight:600;">' + escapeHtml(brand.name) + '</a>';

        $row.html(html);
    }

    // ============================================================
    // RENDER PRODUCT INFO
    // ============================================================
    function renderProductInfo(product) {
        var $name = $('#pd-name');
        if ($name.length) $name.text(product.name);

        // Category tag
        var $cat = $('#pd-category-tag');
        if ($cat.length && productData.category) {
            $cat.html('<a href="' + productData.category.url + '" style="color:var(--text-secondary);">' + escapeHtml(productData.category.name) + '</a>');
        }

        // Description
        var $desc = $('#pd-description');
        if ($desc.length && product.description) {
            $desc.html(product.description);
        }
    }

    // ============================================================
    // RENDER PRICING
    // ============================================================
    function renderPricing(price) {
        var $container = $('#pd-pricing');
        if (!$container.length) return;

        if (!price || (!price.regular_price && !price.discount_price)) {
            $container.html('<div style="padding:1rem;color:var(--muted);font-size:0.9rem;">Price not available for your selected country.</div>');
            return;
        }

        var html = '<div class="pd-pricing-row">';

        // Original price
        if (price.has_discount && price.formatted_regular) {
            html += '<span class="pd-original">' + price.formatted_regular + '</span>';
        }

        // Current price
        if (price.formatted_discount) {
            html += '<span class="pd-current">' + price.formatted_discount + '</span>';
        } else if (price.formatted_regular) {
            html += '<span class="pd-current">' + price.formatted_regular + '</span>';
        }

        html += '</div>';

        // Savings info
        if (price.has_discount) {
            html += '<div style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;margin-top:0.5rem;">';
            if (price.discount_percent > 0) {
                html += '<span style="padding:0.2rem 0.6rem;border-radius:var(--radius-full);background:rgba(239,68,68,0.1);color:var(--danger);font-size:0.78rem;font-weight:700;">-' + price.discount_percent + '% OFF</span>';
            }
            if (price.formatted_saved) {
                html += '<span class="pd-save"><i class="fa-solid fa-tag" style="margin-right:0.3rem;"></i>' + price.formatted_saved + '</span>';
            }
            html += '</div>';
        }

        $container.html(html);
    }

    // ============================================================
    // RENDER DETAILS GRID
    // ============================================================
    function renderDetailsGrid(product, price) {
        var $grid = $('#pd-details-grid');
        if (!$grid.length) return;

        var html = '';

        // Calories
        if (product.calories > 0) {
            html += buildDetailItem('fa-fire', 'Calories', product.calories + ' kcal');
        }

        // Currency
        if (price && price.currency) {
            html += buildDetailItem('fa-coins', 'Currency', price.currency);
        }

        // Brand
        if (productData.brand) {
            html += buildDetailItem('fa-building', 'Brand', '<a href="' + productData.brand.url + '" style="color:var(--primary);">' + escapeHtml(productData.brand.name) + '</a>');
        }

        // Category
        if (productData.category) {
            html += buildDetailItem('fa-layer-group', 'Category', '<a href="' + productData.category.url + '" style="color:var(--primary);">' + escapeHtml(productData.category.name) + '</a>');
        }

        // Availability
        if (price && (price.regular_price || price.discount_price)) {
            html += buildDetailItem('fa-check-circle', 'Availability', '<span style="color:var(--success);font-weight:600;">Available</span>');
        } else {
            html += buildDetailItem('fa-times-circle', 'Availability', '<span style="color:var(--danger);font-weight:600;">Not available</span>');
        }

        $grid.html(html);
    }

    function buildDetailItem(icon, label, value) {
        var html = '<div class="pd-detail-item">';
        html += '  <i class="fa-solid ' + icon + '"></i>';
        html += '  <span class="label">' + escapeHtml(label) + '</span>';
        html += '  <span class="value">' + value + '</span>';
        html += '</div>';
        return html;
    }

    // ============================================================
    // RENDER NUTRITION TABLE
    // ============================================================
    function renderNutrition(nutrition) {
        var $container = $('#pd-nutrition');
        if (!$container.length) return;

        if (!nutrition || (!nutrition.calories && !nutrition.fat && !nutrition.carbs && !nutrition.protein)) {
            $container.closest('.pd-nutrition-section').hide();
            return;
        }

        var html = '<table class="nutrition-table">';
        html += '<thead><tr><th>Nutrient</th><th>Amount</th></tr></thead>';
        html += '<tbody>';

        if (nutrition.calories !== null) {
            html += '<tr><td><i class="fa-solid fa-fire" style="color:var(--primary);margin-right:0.5rem;"></i>Calories</td><td>' + nutrition.calories + ' kcal</td></tr>';
        }
        if (nutrition.fat !== null) {
            html += '<tr><td><i class="fa-solid fa-droplet" style="color:#F59E0B;margin-right:0.5rem;"></i>Total Fat</td><td>' + nutrition.fat + 'g</td></tr>';
        }
        if (nutrition.carbs !== null) {
            html += '<tr><td><i class="fa-solid fa-wheat-awn" style="color:#22C55E;margin-right:0.5rem;"></i>Carbohydrates</td><td>' + nutrition.carbs + 'g</td></tr>';
        }
        if (nutrition.protein !== null) {
            html += '<tr><td><i class="fa-solid fa-dumbbell" style="color:#3B82F6;margin-right:0.5rem;"></i>Protein</td><td>' + nutrition.protein + 'g</td></tr>';
        }
        if (nutrition.fiber !== null) {
            html += '<tr><td><i class="fa-solid fa-leaf" style="color:#06D6A0;margin-right:0.5rem;"></i>Dietary Fiber</td><td>' + nutrition.fiber + 'g</td></tr>';
        }
        if (nutrition.sugar !== null) {
            html += '<tr><td><i class="fa-solid fa-cube" style="color:#EC4899;margin-right:0.5rem;"></i>Sugar</td><td>' + nutrition.sugar + 'g</td></tr>';
        }
        if (nutrition.sodium !== null) {
            html += '<tr><td><i class="fa-solid fa-flask" style="color:#8B5CF6;margin-right:0.5rem;"></i>Sodium</td><td>' + nutrition.sodium + 'mg</td></tr>';
        }

        html += '</tbody></table>';
        $container.html(html);
    }

    // ============================================================
    // RENDER INGREDIENTS
    // ============================================================
    function renderIngredients(ingredients) {
        var $container = $('#pd-ingredients');
        if (!$container.length) return;

        if (!ingredients || ingredients.length === 0) {
            $container.closest('.pd-ingredients-section').hide();
            return;
        }

        var html = '<div style="display:flex;flex-wrap:wrap;gap:0.5rem;">';
        $.each(ingredients, function (i, ing) {
            html += '<span style="padding:0.35rem 0.85rem;border-radius:var(--radius-full);background:var(--bg-alt);border:1px solid var(--border);font-size:0.82rem;color:var(--text-secondary);display:inline-flex;align-items:center;gap:0.35rem;">';
            html += '<i class="fa-solid fa-circle" style="font-size:0.3rem;color:var(--primary);"></i>';
            html += escapeHtml(ing.name);
            html += '</span>';
        });
        html += '</div>';

        $container.html(html);
    }

    // ============================================================
    // RENDER COUNTRY PRICE COMPARISON TABLE
    // ============================================================
    function renderCountryPrices(prices) {
        var $container = $('#pd-country-prices');
        if (!$container.length) return;

        if (!prices || prices.length === 0) {
            $container.closest('.pd-country-prices-section').hide();
            return;
        }

        var html = '<div style="overflow-x:auto;">';
        html += '<table class="nutrition-table">';

        // Header
        html += '<thead><tr>';
        html += '<th>Country</th>';
        html += '<th>Regular Price</th>';
        html += '<th>Discount Price</th>';
        html += '<th>You Save</th>';
        html += '</tr></thead>';

        html += '<tbody>';
        $.each(prices, function (i, cp) {
            var rowStyle = cp.is_current ? 'background:rgba(232,93,4,0.05);font-weight:600;' : '';
            var currentBadge = cp.is_current ? ' <span style="font-size:0.65rem;padding:0.1rem 0.4rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;vertical-align:middle;margin-left:0.3rem;">Current</span>' : '';

            var savedHtml = '<span style="color:var(--muted);">—</span>';
            if (cp.has_discount && cp.regular_price && cp.discount_price) {
                var saved = cp.regular_price - cp.discount_price;
                var savedPercent = Math.round((saved / cp.regular_price) * 100);
                savedHtml = '<span style="color:var(--success);font-weight:600;">' + cp.currency_symbol + numberFormat(saved) + ' (' + savedPercent + '%)</span>';
            }

            html += '<tr style="' + rowStyle + '">';
            html += '<td>';
            html += '  <div style="display:flex;align-items:center;gap:0.5rem;">';
            html += '    <img src="' + cp.flag_url + '" alt="' + escapeHtml(cp.country_name) + '" style="width:24px;height:16px;object-fit:cover;border-radius:2px;">';
            html += '    <span>' + escapeHtml(cp.country_name) + currentBadge + '</span>';
            html += '  </div>';
            html += '</td>';
            html += '<td>' + (cp.formatted_regular || '<span style="color:var(--muted);">—</span>') + '</td>';
            html += '<td>' + (cp.formatted_discount || '<span style="color:var(--muted);">—</span>') + '</td>';
            html += '<td>' + savedHtml + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table></div>';

        $container.html(html);
    }

    // ============================================================
    // RENDER OFFERS
    // ============================================================
    function renderOffers(offers) {
        var $container = $('#pd-offers');
        if (!$container.length) return;

        if (!offers || offers.length === 0) {
            $container.closest('.pd-offers-section').hide();
            return;
        }

        var html = '';
        $.each(offers, function (i, offer) {
            var plainDesc = $('<div>').html(offer.description || '').text();
            if (plainDesc.length > 100) plainDesc = plainDesc.substring(0, 97) + '...';

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
            if (offer.days_remaining <= 7 && offer.days_remaining > 0) {
                html += '<div style="margin-top:0.75rem;font-size:0.75rem;color:var(--warning);font-weight:600;"><i class="fa-solid fa-clock"></i> ' + offer.days_remaining + ' days left</div>';
            }
            html += '</div>';
            html += '</div>';
        });

        $container.html(html);
    }

    // Copy coupon
    $(document).on('click', '.btn-copy-coupon', function () {
        var code = $(this).data('code');
        if (code && window.copyToClipboard) {
            window.copyToClipboard(code, 'Coupon code "' + code + '" copied!');
        }
    });

    // ============================================================
    // RENDER RELATED PRODUCTS (Swiper or Grid)
    // ============================================================
    function renderRelatedProducts(products) {
        var $container = $('#pd-related-products');
        if (!$container.length) return;

        if (!products || products.length === 0) {
            $container.closest('.pd-related-section').hide();
            return;
        }

        // Use Swiper if container has swiper class
        if ($container.hasClass('swiper')) {
            var slidesHtml = '';
            $.each(products, function (i, p) {
                slidesHtml += '<div class="swiper-slide">';
                slidesHtml += buildRelatedCard(p, i);
                slidesHtml += '</div>';
            });

            $container.find('.swiper-wrapper').html(slidesHtml);

            new Swiper($container[0], {
                slidesPerView: 1,
                spaceBetween: 16,
                speed: 500,
                navigation: {
                    nextEl: $container.find('.swiper-button-next')[0],
                    prevEl: $container.find('.swiper-button-prev')[0]
                },
                breakpoints: {
                    576: { slidesPerView: 1.3 },
                    768: { slidesPerView: 2 },
                    992: { slidesPerView: 3 },
                    1200: { slidesPerView: 4 }
                }
            });
        } else {
            // Grid layout
            var gridHtml = '';
            $.each(products, function (i, p) {
                gridHtml += buildRelatedCard(p, i);
            });
            $container.html(gridHtml);
        }
    }

    function buildRelatedCard(p, index) {
        var delay = Math.min(index * 50, 300);
        var html = '<div class="product-card" data-aos="fade-up" data-aos-delay="' + delay + '">';

        // Image
        html += '<a href="' + p.url + '" class="pc-image">';
        if (p.image) {
            html += '<img src="' + p.image + '" alt="' + escapeHtml(p.name) + '" loading="lazy">';
        } else {
            html += '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--bg-alt);color:var(--muted);"><i class="fa-solid fa-utensils" style="font-size:2rem;"></i></div>';
        }
        if (p.has_discount && p.discount_percent > 0) {
            html += '<span class="pc-discount-badge">-' + p.discount_percent + '%</span>';
        }
        html += '</a>';

        // Body
        html += '<div class="pc-body">';
        html += '<h4 class="pc-name"><a href="' + p.url + '" style="color:var(--text);transition:color 0.3s;">' + escapeHtml(p.name) + '</a></h4>';

        if (p.calories > 0) {
            html += '<div class="pc-meta"><span><i class="fa-solid fa-fire"></i> ' + p.calories + ' cal</span></div>';
        }

        // Price footer
        html += '<div class="pc-footer">';
        html += '<div class="pc-prices">';
        if (p.has_discount && p.formatted_discount) {
            html += '<span class="pc-original-price">' + p.formatted_regular + '</span>';
            html += '<span class="pc-current-price">' + p.formatted_discount + '</span>';
        } else if (p.formatted_regular) {
            html += '<span class="pc-current-price">' + p.formatted_regular + '</span>';
        }
        html += '</div>';
        html += '<a href="' + p.url + '" class="pc-view-btn">View</a>';
        html += '</div>';

        html += '</div>';
        html += '</div>';

        return html;
    }

    // ============================================================
    // SHARE BUTTONS
    // ============================================================
    function initShareButtons(product) {
        var pageUrl = window.location.href;
        var pageTitle = product.name + ' — ' + (productData.brand ? productData.brand.name : 'FoodScope');
        var pageImage = productData.images && productData.images[0] ? productData.images[0].image : '';

        // Copy link button
        $(document).on('click', '.pd-share-copy', function () {
            if (window.copyToClipboard) {
                window.copyToClipboard(pageUrl, 'Product link copied to clipboard!');
            }
        });

        // Facebook
        $(document).on('click', '.pd-share-facebook', function () {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(pageUrl), '_blank', 'width=600,height=400');
        });

        // Twitter / X
        $(document).on('click', '.pd-share-twitter', function () {
            var text = encodeURIComponent('Check out ' + product.name + ' on FoodScope!');
            window.open('https://twitter.com/intent/tweet?text=' + text + '&url=' + encodeURIComponent(pageUrl), '_blank', 'width=600,height=400');
        });

        // WhatsApp
        $(document).on('click', '.pd-share-whatsapp', function () {
            var text = encodeURIComponent(product.name + ' — ' + pageUrl);
            window.open('https://wa.me/?text=' + text, '_blank');
        });

        // LinkedIn
        $(document).on('click', '.pd-share-linkedin', function () {
            window.open('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(pageUrl), '_blank', 'width=600,height=400');
        });

        // Email
        $(document).on('click', '.pd-share-email', function () {
            var subject = encodeURIComponent(pageTitle);
            var body = encodeURIComponent('Check out this product: ' + pageUrl);
            window.location.href = 'mailto:?subject=' + subject + '&body=' + body;
        });
    }

    // ============================================================
    // SKELETON / ERROR / 404 STATES
    // ============================================================
    function showPageSkeleton() {
        var $skeleton = $('#product-page-skeleton');
        if ($skeleton.length) {
            $skeleton.show();
            $('#product-detail-content').hide();
        }
    }

    function hidePageSkeleton() {
        var $skeleton = $('#product-page-skeleton');
        if ($skeleton.length) {
            $skeleton.hide();
            $('#product-detail-content').show();
        }
    }

    function showError(msg) {
        hidePageSkeleton();
        var $content = $('#product-detail-content');
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
        var $content = $('#product-detail-content');
        if ($content.length) {
            $content.html(
                '<div class="error-page">' +
                '<div class="error-code">404</div>' +
                '<h2 class="error-title">Product Not Found</h2>' +
                '<p class="error-desc">The product you\'re looking for doesn\'t exist or has been removed.</p>' +
                '<a href="' + BASE_URL + '/brands" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;">' +
                '<i class="fa-solid fa-arrow-left" style="font-size:0.75rem;"></i> Browse All Brands</a>' +
                '</div>'
            );
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

    function numberFormat(num) {
        if (num === null || num === undefined) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

})();