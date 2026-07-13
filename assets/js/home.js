/**
 * home.js — Loaded ONLY on index.php (Home page)
 * Handles: hero, featured brands, products, categories, offers,
 * testimonials, why choose us, FAQ accordion, hero search
 */

(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '/food-brands';

    $(document).ready(function () {
        initHeroParticles();
        initHeroSearch();
        initHeroPopularTags();
        loadFeaturedBrands();
        loadFeaturedProducts();
        loadCategories();
        loadHomeOffers();
        loadTestimonials();
        loadHomeFAQs();
        initWhyChooseUsCounters();
    });

    // ============================================================
    // 1. HERO PARTICLES
    // ============================================================
    function initHeroParticles() {
        var $container = $('.hero-particles');
        if (!$container.length) return;

        var colors = ['#E85D04', '#FFBA08', '#06D6A0', '#EF4444', '#FFFFFF'];
        var html = '';

        for (var i = 0; i < 20; i++) {
            var size = Math.random() * 8 + 4;
            var left = Math.random() * 100;
            var delay = Math.random() * 15;
            var duration = Math.random() * 15 + 10;
            var color = colors[Math.floor(Math.random() * colors.length)];

            html += '<div class="hero-particle" style="';
            html += 'width:' + size + 'px;';
            html += 'height:' + size + 'px;';
            html += 'left:' + left + '%;';
            html += 'background:' + color + ';';
            html += 'animation-delay:' + delay + 's;';
            html += 'animation-duration:' + duration + 's;';
            html += '"></div>';
        }

        $container.html(html);
    }

    // ============================================================
    // 2. HERO SEARCH
    // ============================================================
    function initHeroSearch() {
        $(document).on('submit', '.hero-search-box', function (e) {
            e.preventDefault();
            var q = $(this).find('.hero-search-input').val().trim();
            if (q.length >= 2) {
                window.location.href = BASE_URL + '/search?q=' + encodeURIComponent(q);
            } else {
                showToast('Please enter at least 2 characters to search.', 'warning');
            }
        });
    }

    // ============================================================
    // 3. HERO POPULAR TAGS (clickable)
    // ============================================================
    function initHeroPopularTags() {
        $(document).on('click', '.hero-popular-tag', function () {
            var text = $(this).text().trim();
            $('.hero-search-input').val(text).trigger('focus');
        });
    }

    // ============================================================
    // 4. FEATURED BRANDS (Swiper Carousel)
    // ============================================================
    function loadFeaturedBrands() {
        var $container = $('#home-featured-brands');
        if (!$container.length) return;

        // Show skeletons
        var skeletonHtml = '';
        for (var i = 0; i < 3; i++) {
            skeletonHtml += '<div class="swiper-slide"><div class="skeleton skeleton-card"></div></div>';
        }
        $container.find('.swiper-wrapper').html(skeletonHtml);

        $.getJSON(BASE_URL + '/api/site/brands.php', {
            action: 'featured',
            limit: 6
        }, function (res) {
            if (!res.success || res.brands.length === 0) {
                $container.html('<div style="text-align:center;padding:2rem;color:var(--muted);">No brands available in your country yet.</div>');
                return;
            }

            var slidesHtml = '';
            $.each(res.brands, function (i, brand) {
                slidesHtml += buildFeaturedBrandSlide(brand);
            });

            $container.find('.swiper-wrapper').html(slidesHtml);
            initFeaturedBrandsSwiper($container);
            window.refreshAOS();

        }).fail(function () {
            $container.find('.swiper-wrapper').html('<div style="text-align:center;padding:2rem;color:var(--danger);">Failed to load brands. <a href="' + BASE_URL + '/brands" style="color:var(--primary);">View all brands</a></div>');
        });
    }

    function buildFeaturedBrandSlide(brand) {
    var html = '<div class="swiper-slide">';
    html += '<div class="featured-brand-card" data-aos="fade-up" data-aos-delay="' + (50) + '">';

    // Cover image (now a link)
    html += '<a href="' + brand.url + '" class="fb-cover">';
    if (brand.cover_image) {
        html += '<img src="' + brand.cover_image + '" alt="' + escapeHtml(brand.name) + '" loading="lazy">';
    } else {
        html += '<div style="width:100%;height:100%;background:linear-gradient(135deg,var(--secondary),#2D1B4E);display:flex;align-items:center;justify-content:center;"><span style="font-family:var(--font-display);font-size:2rem;font-weight:900;color:rgba(255,255,255,0.2);">' + escapeHtml(brand.name.charAt(0)) + '</span></div>';
    }
    html += '</a>';

    // Logo
    html += '<div class="fb-logo">';
    if (brand.logo) {
        html += '<img src="' + brand.logo + '" alt="' + escapeHtml(brand.name) + '" loading="lazy">';
    } else {
        html += '<span style="font-family:var(--font-display);font-size:1.2rem;font-weight:900;color:var(--primary);">' + escapeHtml(brand.name.charAt(0)) + '</span>';
    }
    html += '</div>';

    // Body
    html += '<div class="fb-body">';
    html += '<h3 class="fb-name">' + escapeHtml(brand.name) + '</h3>';
    if (brand.short_description) {
        html += '<p class="fb-desc">' + escapeHtml(brand.short_description) + '</p>';
    }

    // View all link — pushed to the right
    html += '<div style="display:flex;justify-content:flex-end;">';
    html += '<a href="' + brand.url + '" class="fb-view-all">';
    html += 'View Menu <i class="fa-solid fa-arrow-right" style="font-size:0.75rem;"></i>';
    html += '</a>';
    html += '</div>';

    html += '</div>'; // fb-body
    html += '</div>'; // featured-brand-card
    html += '</div>'; // swiper-slide

    return html;
}

    function initFeaturedBrandsSwiper($container) {
        new Swiper($container.find('.fb-cat-swiper')[0], {
            slidesPerView: 'auto',
            spaceBetween: 8,
            freeMode: true
        });

        new Swiper($container[0], {
            slidesPerView: 1,
            spaceBetween: 24,
            loop: false,
            speed: 600,
            navigation: {
                nextEl: $container.find('.swiper-button-next')[0],
                prevEl: $container.find('.swiper-button-prev')[0]
            },
            pagination: {
                el: $container.find('.swiper-pagination')[0],
                clickable: true
            },
            breakpoints: {
                576: { slidesPerView: 1.2 },
                768: { slidesPerView: 2 },
                992: { slidesPerView: 2.5 },
                1200: { slidesPerView: 3 }
            }
        });
    }

    // ============================================================
    // 5. FEATURED PRODUCTS (Grid + View Toggle)
    // ============================================================
    function loadFeaturedProducts() {
        var $section = $('#home-featured-products');
        if (!$section.length) return;

        var $grid = $section.find('#products-grid');
        var $toolbar = $section.find('.toolbar');

        // Show skeletons
        $grid.html(window.skeletonCards(8, 'product'));

        $.getJSON(BASE_URL + '/api/site/products.php', {
            action: 'featured',
            limit: 8
        }, function (res) {
            if (!res.success || res.products.length === 0) {
                $grid.html('<div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--muted);">No products available in your country yet.</div>');
                $toolbar.hide();
                return;
            }

            renderProductCards($grid, res.products, 'grid');
            $toolbar.show();
            initProductViewToggle($grid, res.products);

        }).fail(function () {
            $grid.html('<div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--danger);">Failed to load products.</div>');
        });
    }

    function renderProductCards($grid, products, viewMode) {
        var html = '';

        if (viewMode === 'list') {
            $grid.removeClass('products-grid').addClass('products-list');
        } else {
            $grid.removeClass('products-list').addClass('products-grid');
        }

        $.each(products, function (i, p) {
            html += buildProductCard(p, viewMode, i);
        });

        $grid.html(html);
        window.refreshAOS();
    }

    function buildProductCard(p, viewMode, index) {
        var delay = Math.min(index * 50, 300);
        var html = '<div class="product-card" data-aos="fade-up" data-aos-delay="' + delay + '">';

        // Image
        html += '<div class="pc-image">';
        if (p.image) {
            html += '<img src="' + p.image + '" alt="' + escapeHtml(p.name) + '" loading="lazy">';
        } else {
            html += '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--bg-alt);color:var(--muted);"><i class="fa-solid fa-utensils" style="font-size:2rem;"></i></div>';
        }

        // Discount badge
        if (p.has_discount && p.discount_percent > 0) {
            html += '<span class="pc-discount-badge">-' + p.discount_percent + '%</span>';
        }

        // Action buttons
        html += '<div class="pc-actions">';
        html += '  <button class="pc-action-btn btn-quick-view" data-slug="' + p.slug + '" title="Quick View"><i class="fa-solid fa-eye"></i></button>';
        html += '  <button class="pc-action-btn btn-copy-link" data-url="' + p.url + '" title="Copy Link"><i class="fa-solid fa-link"></i></button>';
        html += '</div>';

        html += '</div>'; // pc-image

        // Body
        html += '<div class="pc-body">';

        // Brand
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
            html += '<div class="pc-category"><a href="' + BASE_URL + '/category/' + p.category_slug + '" style="color:var(--muted);">' + escapeHtml(p.category_name) + '</a></div>';
        }

        // Meta (calories)
        if (p.calories > 0) {
            html += '<div class="pc-meta">';
            html += '  <span><i class="fa-solid fa-fire"></i> ' + p.calories + ' cal</span>';
            html += '</div>';
        }

        // Footer
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
        html += '</div>'; // pc-footer

        html += '</div>'; // pc-body
        html += '</div>'; // product-card

        return html;
    }

    function initProductViewToggle($grid, products) {
        var currentView = 'grid';

        $(document).on('click', '.toolbar-view-btn', function () {
            var view = $(this).data('view');
            if (view === currentView) return;

            currentView = view;
            $('.toolbar-view-btn').removeClass('active');
            $(this).addClass('active');

            renderProductCards($grid, products, view);
        });
    }

    // Quick view & copy link event delegation
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
    // 6. CATEGORIES SECTION
    // ============================================================
    function loadCategories() {
        var $grid = $('#home-categories-grid');
        if (!$grid.length) return;

        // Show skeletons
        $grid.html(window.skeletonCards(6, 'category'));

        $.getJSON(BASE_URL + '/api/site/categories.php', {
            limit: 6
        }, function (res) {
            if (!res.success || res.categories.length === 0) {
                $grid.html('<div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--muted);">No categories available yet.</div>');
                return;
            }

            var html = '';
            $.each(res.categories, function (i, cat) {
                html += buildCategoryCard(cat, i);
            });

            $grid.html(html);
            window.refreshAOS();

        }).fail(function () {
            $grid.html('<div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--danger);">Failed to load categories.</div>');
        });
    }

    function buildCategoryCard(cat, index) {
        var delay = Math.min(index * 60, 300);
        var html = '<a href="' + cat.url + '" class="category-card" data-aos="fade-up" data-aos-delay="' + delay + '">';

        if (cat.image) {
            html += '<img src="' + cat.image + '" alt="' + escapeHtml(cat.name) + '" loading="lazy">';
        } else {
            html += '<div style="width:100%;height:100%;background:linear-gradient(135deg,var(--secondary),#2D1B4E);display:flex;align-items:center;justify-content:center;"><span style="font-family:var(--font-display);font-size:3rem;font-weight:900;color:rgba(255,255,255,0.15);">' + escapeHtml(cat.name.charAt(0)) + '</span></div>';
        }

        html += '<div class="cc-content">';
        html += '<h3 class="cc-name">' + escapeHtml(cat.name) + '</h3>';

        var infoParts = [];
        if (cat.product_count > 0) infoParts.push(cat.product_count + ' Items');
        if (cat.brand_count > 0) infoParts.push(cat.brand_count + ' Brands');
        if (cat.formatted_min_price) infoParts.push('From ' + cat.formatted_min_price);

        html += '<div class="cc-info">' + infoParts.join(' · ') + '</div>';
        html += '</div>';

        html += '</a>';
        return html;
    }

    // ============================================================
    // 7. HOME OFFERS SECTION (Swiper)
    // ============================================================
    function loadHomeOffers() {
        var $container = $('#home-offers-swiper');
        if (!$container.length) return;

        // Show skeletons
        var skeletonHtml = '';
        for (var i = 0; i < 3; i++) {
            skeletonHtml += '<div class="swiper-slide"><div class="skeleton skeleton-card"></div></div>';
        }
        $container.find('.swiper-wrapper').html(skeletonHtml);

        $.getJSON(BASE_URL + '/api/site/offers.php', {
            action: 'home-featured',
            limit: 6
        }, function (res) {
            if (!res.success || res.offers.length === 0) {
                $container.html('<div style="text-align:center;padding:2rem;color:var(--muted);">No active offers in your country right now.</div>');
                return;
            }

            var slidesHtml = '';
            $.each(res.offers, function (i, offer) {
                slidesHtml += buildOfferSlide(offer, i);
            });

            $container.find('.swiper-wrapper').html(slidesHtml);
            initOffersSwiper($container);
            window.refreshAOS();

        }).fail(function () {
            $container.find('.swiper-wrapper').html('<div class="swiper-slide" style="text-align:center;padding:2rem;color:var(--danger);">Failed to load offers.</div>');
        });
    }

    function buildOfferSlide(offer, index) {
        var delay = Math.min(index * 50, 250);
        var html = '<div class="swiper-slide">';
        html += '<div class="offer-card" data-aos="fade-up" data-aos-delay="' + delay + '">';

        // Brand row
        html += '<div class="oc-body">';
        html += '<div class="oc-brand">';
        if (offer.brand && offer.brand.logo) {
            html += '<img src="' + offer.brand.logo + '" alt="' + escapeHtml(offer.brand.name) + '" loading="lazy">';
        }
        html += '<span>' + escapeHtml(offer.brand ? offer.brand.name : '') + '</span>';
        html += '</div>';

        // Title
        html += '<h4 class="oc-title">' + escapeHtml(offer.title) + '</h4>';

        // Description (stripped)
        if (offer.description) {
            var plainDesc = $('<div>').html(offer.description).text();
            if (plainDesc.length > 100) plainDesc = plainDesc.substring(0, 97) + '...';
            html += '<p class="oc-desc">' + escapeHtml(plainDesc) + '</p>';
        }

        // Footer
        html += '<div class="oc-footer">';
        html += '<div class="oc-discount">' + offer.discount_percent + '% OFF</div>';
        if (offer.coupon_code) {
            html += '<span class="oc-code" title="Click to copy" style="cursor:pointer;" class="btn-copy-coupon" data-code="' + escapeHtml(offer.coupon_code) + '">' + escapeHtml(offer.coupon_code) + '</span>';
        }
        html += '</div>';

        // Days remaining
        if (offer.days_remaining <= 7 && offer.days_remaining > 0) {
            html += '<div style="margin-top:0.75rem;font-size:0.75rem;color:var(--warning);font-weight:600;"><i class="fa-solid fa-clock"></i> ' + offer.days_remaining + ' days left</div>';
        }

        html += '</div>'; // oc-body
        html += '</div>'; // offer-card
        html += '</div>'; // swiper-slide

        return html;
    }

    function initOffersSwiper($container) {
        new Swiper($container[0], {
            slidesPerView: 1,
            spaceBetween: 20,
            speed: 600,
            navigation: {
                nextEl: $container.find('.swiper-button-next')[0],
                prevEl: $container.find('.swiper-button-prev')[0]
            },
            pagination: {
                el: $container.find('.swiper-pagination')[0],
                clickable: true
            },
            breakpoints: {
                576: { slidesPerView: 1.2 },
                768: { slidesPerView: 2 },
                992: { slidesPerView: 2.5 },
                1200: { slidesPerView: 3 }
            }
        });
    }

    // Copy coupon code
    $(document).on('click', '.btn-copy-coupon', function () {
        var code = $(this).data('code');
        if (code && window.copyToClipboard) {
            window.copyToClipboard(code, 'Coupon code "' + code + '" copied!');
        }
    });

    // ============================================================
    // 8. TESTIMONIALS (Swiper Carousel)
    // ============================================================
    function loadTestimonials() {
        var $container = $('#home-testimonials-swiper');
        if (!$container.length) return;

        // Show skeletons
        var skeletonHtml = '';
        for (var i = 0; i < 3; i++) {
            skeletonHtml += '<div class="swiper-slide"><div class="skeleton skeleton-card"></div></div>';
        }
        $container.find('.swiper-wrapper').html(skeletonHtml);

        $.getJSON(BASE_URL + '/api/site/testimonials.php', {
            limit: 6,
            sort: 'rating_high'
        }, function (res) {
            if (!res.success || res.testimonials.length === 0) {
                $container.html('<div style="text-align:center;padding:2rem;color:var(--muted);">No testimonials yet.</div>');
                return;
            }

            var slidesHtml = '';
            $.each(res.testimonials, function (i, t) {
                slidesHtml += buildTestimonialSlide(t, i);
            });

            $container.find('.swiper-wrapper').html(slidesHtml);
            initTestimonialsSwiper($container);
            window.refreshAOS();

            // Update average rating display if exists
            if (res.average_rating > 0) {
                var $avg = $('#testimonials-avg-rating');
                if ($avg.length) {
                    $avg.text(res.average_rating);
                }
                var $count = $('#testimonials-total-count');
                if ($count.length) {
                    $count.text(res.total + ' Reviews');
                }
            }

        }).fail(function () {
            $container.find('.swiper-wrapper').html('<div class="swiper-slide" style="text-align:center;padding:2rem;color:var(--danger);">Failed to load testimonials.</div>');
        });
    }

    function buildTestimonialSlide(t, index) {
        var delay = Math.min(index * 50, 250);

        // Build stars HTML
        var starsHtml = '';
        if (t.stars) {
            $.each(t.stars, function (s, star) {
                if (star === 'full') {
                    starsHtml += '<i class="fa-solid fa-star"></i> ';
                } else {
                    starsHtml += '<i class="fa-regular fa-star"></i> ';
                }
            });
        }

        // Plain text review
        var plainReview = t.review_plain || '';
        if (plainReview.length > 150) {
            plainReview = plainReview.substring(0, 147) + '...';
        }

        var html = '<div class="swiper-slide">';
        html += '<div class="testimonial-card" data-aos="fade-up" data-aos-delay="' + delay + '">';
        html += '<div class="tc-stars">' + starsHtml + '</div>';
        html += '<p class="tc-text">"' + escapeHtml(plainReview) + '"</p>';
        html += '<div class="tc-author">';
        if (t.image) {
            html += '<img class="tc-avatar" src="' + t.image + '" alt="' + escapeHtml(t.name) + '" loading="lazy">';
        } else {
            html += '<div class="tc-avatar" style="background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1rem;">' + escapeHtml(t.name.charAt(0)) + '</div>';
        }
        html += '<div>';
        html += '<div class="tc-name">' + escapeHtml(t.name) + '</div>';
        if (t.designation) {
            html += '<div class="tc-role">' + escapeHtml(t.designation) + '</div>';
        }
        html += '</div>';
        html += '</div>'; // tc-author
        html += '</div>'; // testimonial-card
        html += '</div>'; // swiper-slide

        return html;
    }

    function initTestimonialsSwiper($container) {
        new Swiper($container[0], {
            slidesPerView: 1,
            spaceBetween: 20,
            speed: 600,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            },
            navigation: {
                nextEl: $container.find('.swiper-button-next')[0],
                prevEl: $container.find('.swiper-button-prev')[0]
            },
            pagination: {
                el: $container.find('.swiper-pagination')[0],
                clickable: true
            },
            breakpoints: {
                576: { slidesPerView: 1.1 },
                768: { slidesPerView: 2 },
                992: { slidesPerView: 3 }
            }
        });
    }

    // ============================================================
    // 9. HOME FAQ ACCORDION
    // ============================================================
    function loadHomeFAQs() {
        var $container = $('#home-faqs');
        if (!$container.length) return;

        // Show skeletons
        $container.html(window.skeletonText(5));

        $.getJSON(BASE_URL + '/api/site/faqs.php', {
            limit: 5
        }, function (res) {
            if (!res.success || res.faqs.length === 0) {
                $container.html('<div style="text-align:center;padding:2rem;color:var(--muted);">No FAQs available.</div>');
                return;
            }

            var html = '';
            $.each(res.faqs, function (i, faq) {
                html += buildFaqItem(faq, i);
            });

            $container.html(html);
            window.refreshAOS();

        }).fail(function () {
            $container.html('<div style="text-align:center;padding:2rem;color:var(--danger);">Failed to load FAQs.</div>');
        });
    }

    function buildFaqItem(faq, index) {
        var delay = Math.min(index * 40, 200);
        var html = '<div class="faq-item" data-faq-id="' + faq.id + '" data-aos="fade-up" data-aos-delay="' + delay + '">';
        html += '<button class="faq-question">';
        html += '<span>' + escapeHtml(faq.question) + '</span>';
        html += '<i class="fa-solid fa-chevron-down"></i>';
        html += '</button>';
        html += '<div class="faq-answer">';
        html += '<div class="faq-answer-inner">' + faq.answer + '</div>';
        html += '</div>';
        html += '</div>';
        return html;
    }

    // FAQ accordion toggle (works for both home and FAQ page)
    $(document).on('click', '.faq-question', function () {
        var $item = $(this).closest('.faq-item');
        var $answer = $item.find('.faq-answer');
        var isOpen = $item.hasClass('active');

        // Close all siblings
        $item.siblings('.faq-item').removeClass('active');
        $item.siblings('.faq-item').find('.faq-answer').css('max-height', '0');

        // Toggle current
        if (isOpen) {
            $item.removeClass('active');
            $answer.css('max-height', '0');
        } else {
            $item.addClass('active');
            // Calculate actual height for smooth animation
            $answer.css('max-height', $answer.find('.faq-answer-inner').outerHeight() + 20 + 'px');
        }
    });

    // ============================================================
    // 10. WHY CHOOSE US — Animated Counters
    // ============================================================
    function initWhyChooseUsCounters() {
        var $counters = $('#why-choose-us-section');
        if (!$counters.length) return;

        // Use Intersection Observer to trigger counter animation
        var observed = false;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting && !observed) {
                    observed = true;
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        observer.observe($counters[0]);

        function animateCounters() {
            // Use stats from settings if loaded
            var stats = {
                brands: 0,
                products: 0,
                countries: 0
            };

            if (window.AppSettings) {
                // Use stats from the settings API response
            }
            if (window.AppSettings && window.AppSettings.stats) {
                stats.brands = window.AppSettings.stats.total_brands || 0;
                stats.products = window.AppSettings.stats.total_products || 0;
                stats.countries = window.AppSettings.stats.total_countries || 0;
            }

            // Fallback: read from data attributes on the section
            if (stats.brands === 0) {
                stats.brands = parseInt($counters.data('brands')) || 0;
                stats.products = parseInt($counters.data('products')) || 0;
                stats.countries = parseInt($counters.data('countries')) || 0;
            }

            $counters.find('[data-counter="brands"]').each(function () {
                animateValue($(this), 0, stats.brands, 1500);
            });
            $counters.find('[data-counter="products"]').each(function () {
                animateValue($(this), 0, stats.products, 1800);
            });
            $counters.find('[data-counter="countries"]').each(function () {
                animateValue($(this), 0, stats.countries, 1200);
            });
        }
    }

    function animateValue($el, start, end, duration) {
        if (end <= 0) {
            $el.text('0');
            return;
        }

        var range = end - start;
        var startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);

            // Ease out cubic
            var eased = 1 - Math.pow(1 - progress, 3);
            var current = Math.floor(eased * range + start);

            $el.text(window.formatNumber ? window.formatNumber(current) : current);

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                $el.text(window.formatNumber ? window.formatNumber(end) : end);
            }
        }

        requestAnimationFrame(step);
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