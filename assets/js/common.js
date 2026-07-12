/**
 * common.js — Loaded on EVERY page
 * Handles: preloader, theme, country selector, search, mobile menu,
 * header scroll, back-to-top, toast, quick-view, newsletter, AOS init
 */

(function () {
    'use strict';

    // Base URL from PHP (injected via header.php inline script)
    var BASE_URL = window.BASE_URL || '/food-brands';

    // Store settings globally
    window.AppSettings = null;
    window.CurrentCountry = null;
    window.AllCountries = [];

    // ============================================================
    // On DOM Ready
    // ============================================================
    $(document).ready(function () {

        // Inject BASE_URL into window if not already set
        if (!window.BASE_URL) {
            window.BASE_URL = BASE_URL;
        }

        initPreloader();
        initTheme();
        loadSettings();
        initHeaderScroll();
        initMobileMenu();
        initSearchOverlay();
        initBackToTop();
        initQuickView();
        initNewsletter();
        initAOS();
    });

    // ============================================================
    // 1. PRELOADER
    // ============================================================
    function initPreloader() {
        // Hide preloader after page fully loads
        $(window).on('load', function () {
            setTimeout(function () {
                $('#preloader').addClass('hidden');
            }, 1800); // Matches CSS animation duration
        });

        // Fallback: hide after 4 seconds even if load event is slow
        setTimeout(function () {
            $('#preloader').addClass('hidden');
        }, 4000);
    }

    // ============================================================
    // 2. THEME TOGGLE (Dark/Light)
    // ============================================================
    function initTheme() {
        var savedTheme = localStorage.getItem('foodscope-theme') || 'light';
        applyTheme(savedTheme);

        $(document).on('click', '#theme-toggle', function () {
            var current = $('html').attr('data-theme');
            var next = current === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            localStorage.setItem('foodscope-theme', next);
        });
    }

    function applyTheme(theme) {
        $('html').attr('data-theme', theme);
        var $icon = $('#theme-toggle i');
        if (theme === 'dark') {
            $icon.removeClass('fa-moon').addClass('fa-sun');
        } else {
            $icon.removeClass('fa-sun').addClass('fa-moon');
        }
    }

    // ============================================================
    // 3. LOAD SETTINGS
    // ============================================================
    function loadSettings() {
        $.getJSON(BASE_URL + '/api/site/settings.php', function (res) {
            if (!res.success) return;

            window.AppSettings = res.settings;
            window.CurrentCountry = res.current_country;
            window.AllCountries = res.countries;
        }).fail(function () {
            console.warn('Failed to load site settings.');
        });
    }

    // NOTE: The header country selector is intentionally read-only.
    // The visitor's country is auto-detected from their IP address
    // (see config/database-config.php -> resolveVisitorCountryId())
    // and rendered server-side in includes/header.php. There is no
    // dropdown to switch countries and no client-side country-change
    // logic here by design.

    // ============================================================
    // 6. HEADER SCROLL BEHAVIOR
    // ============================================================
    function initHeaderScroll() {
        var $header = $('#main-header');
        var lastScroll = 0;

        $(window).on('scroll', function () {
            var scrollTop = $(this).scrollTop();

            // Add/remove scrolled class for shadow
            if (scrollTop > 20) {
                $header.addClass('scrolled');
            } else {
                $header.removeClass('scrolled');
            }

            lastScroll = scrollTop;
        });

        // Trigger once on load
        $(window).trigger('scroll');
    }

    // ============================================================
    // 7. MOBILE MENU
    // ============================================================
    function initMobileMenu() {
        // Open
        $(document).on('click', '#mobile-menu-btn', function () {
            $('#mobile-nav-overlay').fadeIn(200);
            $('#mobile-nav-panel').css('right', '0');
            $('body').css('overflow', 'hidden');
        });

        // Close via X button
        $(document).on('click', '#mobile-nav-close', function () {
            closeMobileMenu();
        });

        // Close via overlay click
        $(document).on('click', '#mobile-nav-overlay', function () {
            closeMobileMenu();
        });

        // Close on link click (navigate to page)
        $(document).on('click', '#mobile-nav-links a', function () {
            closeMobileMenu();
        });

        function closeMobileMenu() {
            $('#mobile-nav-panel').css('right', '-300px');
            $('#mobile-nav-overlay').fadeOut(200);
            $('body').css('overflow', '');
        }
    }

    // ============================================================
    // 8. SEARCH OVERLAY & LIVE SEARCH
    // ============================================================
    function initSearchOverlay() {
        var $overlay = $('#search-overlay');
        var $input = $('#search-input');
        var $suggestions = $('#search-suggestions');
        var searchTimer = null;

        // Open search
        $(document).on('click', '#header-search-btn', function () {
            $overlay.addClass('active');
            setTimeout(function () {
                $input.focus();
            }, 200);
        });

        // Close search
        $(document).on('click', '#search-close-btn', function () {
            closeSearch();
        });

        // Close on Escape key
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && $overlay.hasClass('active')) {
                closeSearch();
            }
        });

        // Close on clicking outside search container
        $(document).on('click', function (e) {
            if ($overlay.hasClass('active') && !$(e.target).closest('.search-container').length) {
                closeSearch();
            }
        });

        // Live search on typing (debounced)
        $input.on('input', function () {
            var q = $(this).val().trim();

            clearTimeout(searchTimer);

            if (q.length < 2) {
                $suggestions.html('<div class="search-empty">Type at least 2 characters to search...</div>');
                return;
            }

            searchTimer = setTimeout(function () {
                performLiveSearch(q);
            }, 350); // 350ms debounce
        });

        // Submit on Enter — go to full search page
        $input.on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var q = $(this).val().trim();
                if (q.length >= 2) {
                    window.location.href = BASE_URL + '/search?q=' + encodeURIComponent(q);
                }
            }
        });

        function closeSearch() {
            $overlay.removeClass('active');
            $input.val('');
            $suggestions.html('<div class="search-empty">Start typing to search...</div>');
        }
    }

    function performLiveSearch(q) {
        var $suggestions = $('#search-suggestions');

        // Show loading
        $suggestions.html('<div style="padding:1.5rem;text-align:center;color:var(--muted);"><i class="fa-solid fa-spinner fa-spin"></i> Searching...</div>');

        $.getJSON(BASE_URL + '/api/site/search.php', {
            action: 'live',
            q: q
        }, function (res) {
            if (!res.success) {
                $suggestions.html('<div class="search-empty">Search failed. Try again.</div>');
                return;
            }

            var html = '';
            var hasResults = false;

            // Brands section
            if (res.results.brands.length > 0) {
                hasResults = true;
                html += '<div class="search-suggestion-group">';
                html += '  <div class="search-suggestion-group-title"><i class="fa-solid fa-building"></i> Brands</div>';
                $.each(res.results.brands, function (i, item) {
                    html += '<a href="' + item.url + '" class="search-suggestion-item">';
                    html += '  <img src="' + item.logo + '" alt="' + escapeHtml(item.name_raw) + '">';
                    html += '  <div class="info">';
                    html += '    <div class="name">' + item.name + '</div>';
                    html += '    <div class="sub">' + item.sub + '</div>';
                    html += '  </div>';
                    html += '</a>';
                });
                html += '</div>';
            }

            // Products section
            if (res.results.products.length > 0) {
                hasResults = true;
                html += '<div class="search-suggestion-group">';
                html += '  <div class="search-suggestion-group-title"><i class="fa-solid fa-utensils"></i> Products</div>';
                $.each(res.results.products, function (i, item) {
                    html += '<a href="' + item.url + '" class="search-suggestion-item">';
                    html += '  <img src="' + item.image + '" alt="' + escapeHtml(item.name_raw) + '">';
                    html += '  <div class="info">';
                    html += '    <div class="name">' + item.name + '</div>';
                    html += '    <div class="sub">' + item.sub + (item.price ? ' · ' + item.price : '') + '</div>';
                    html += '  </div>';
                    html += '</a>';
                });
                html += '</div>';
            }

            // Categories section
            if (res.results.categories.length > 0) {
                hasResults = true;
                html += '<div class="search-suggestion-group">';
                html += '  <div class="search-suggestion-group-title"><i class="fa-solid fa-layer-group"></i> Categories</div>';
                $.each(res.results.categories, function (i, item) {
                    var imgSrc = item.image || BASE_URL + '/assets/img/placeholder.webp';
                    html += '<a href="' + item.url + '" class="search-suggestion-item">';
                    html += '  <img src="' + imgSrc + '" alt="' + escapeHtml(item.name_raw) + '">';
                    html += '  <div class="info">';
                    html += '    <div class="name">' + item.name + '</div>';
                    html += '    <div class="sub">' + item.sub + '</div>';
                    html += '  </div>';
                    html += '</a>';
                });
                html += '</div>';
            }

            // View all results link
            if (hasResults) {
                html += '<div style="padding:0.75rem;text-align:center;">';
                html += '  <a href="' + BASE_URL + '/search?q=' + encodeURIComponent(q) + '" style="font-size:0.88rem;font-weight:600;color:var(--primary);display:inline-flex;align-items:center;gap:0.4rem;">';
                html += '    View all results for "' + escapeHtml(q) + '" <i class="fa-solid fa-arrow-right" style="font-size:0.75rem;"></i>';
                html += '  </a>';
                html += '</div>';
            } else {
                html = '<div class="search-empty">';
                html += '  <i class="fa-solid fa-search" style="font-size:1.5rem;margin-bottom:0.5rem;display:block;opacity:0.3;"></i>';
                html += '  No results found for "<strong>' + escapeHtml(q) + '</strong>"';
                html += '</div>';
            }

            $suggestions.html(html);
        }).fail(function () {
            $suggestions.html('<div class="search-empty">Network error. Please check your connection.</div>');
        });
    }

    // ============================================================
    // 9. BACK TO TOP
    // ============================================================
    function initBackToTop() {
        var $btn = $('#back-to-top');

        $(window).on('scroll', function () {
            if ($(this).scrollTop() > 400) {
                $btn.addClass('show');
            } else {
                $btn.removeClass('show');
            }
        });

        $(document).on('click', '#back-to-top', function () {
            $('html, body').animate({ scrollTop: 0 }, 600, 'swing');
        });
    }

    // ============================================================
    // 10. TOAST NOTIFICATIONS
    // ============================================================
    window.showToast = function (message, type) {
        type = type || 'success';
        var $container = $('#toast-container');

        var iconMap = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        var colorMap = {
            success: 'var(--success)',
            error: 'var(--danger)',
            warning: 'var(--warning)',
            info: 'var(--primary)'
        };

        var icon = iconMap[type] || iconMap.info;
        var color = colorMap[type] || colorMap.info;

        var $toast = $('<div class="toast-msg">');
        $toast.html('<i class="fa-solid ' + icon + '" style="color:' + color + ';"></i> ' + escapeHtml(message));
        $toast.css('border-left', '4px solid ' + color);

        $container.append($toast);

        // Auto remove after 4 seconds
        setTimeout(function () {
            $toast.css({
                opacity: '0',
                transform: 'translateY(20px)',
                transition: 'all 0.3s ease'
            });
            setTimeout(function () {
                $toast.remove();
            }, 300);
        }, 4000);
    };

    // ============================================================
    // 11. QUICK VIEW MODAL
    // ============================================================
    function initQuickView() {
        // Close modal
        $(document).on('click', '#qv-close-btn', function () {
            closeQuickView();
        });

        // Close on overlay click
        $(document).on('click', '#qv-modal-overlay', function (e) {
            if ($(e.target).is('#qv-modal-overlay')) {
                closeQuickView();
            }
        });

        // Close on Escape
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && $('#qv-modal-overlay').hasClass('active')) {
                closeQuickView();
            }
        });

        function closeQuickView() {
            $('#qv-modal-overlay').removeClass('active');
            $('#qv-modal-body').html('');
            $('body').css('overflow', '');
        }
    }

    // Open Quick View — called from page-specific JS
    window.openQuickView = function (slug) {
        var $overlay = $('#qv-modal-overlay');
        var $body = $('#qv-modal-body');

        // Show loading
        $body.html('<div style="padding:3rem;text-align:center;color:var(--muted);"><i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;"></i><p style="margin-top:1rem;">Loading product details...</p></div>');
        $overlay.addClass('active');
        $('body').css('overflow', 'hidden');

        $.getJSON(BASE_URL + '/api/site/products.php', {
            action: 'quick-view',
            slug: slug
        }, function (res) {
            if (!res.success) {
                $body.html('<div style="padding:3rem;text-align:center;color:var(--danger);"><i class="fa-solid fa-exclamation-circle" style="font-size:2rem;"></i><p style="margin-top:1rem;">' + (res.message || 'Product not found.') + '</p></div>');
                return;
            }

            var p = res.product;
            var html = '';

            // Images section
            html += '<div style="display:grid;grid-template-columns:280px 1fr;gap:1.5rem;" class="qv-grid">';

            // Image gallery
            html += '<div>';
            if (p.images && p.images.length > 0) {
                html += '<div style="border-radius:var(--radius-md);overflow:hidden;margin-bottom:0.5rem;background:var(--bg-alt);">';
                html += '  <img src="' + p.images[0] + '" alt="' + escapeHtml(p.name) + '" style="width:100%;height:250px;object-fit:cover;" class="qv-main-img">';
                html += '</div>';
                if (p.images.length > 1) {
                    html += '<div style="display:flex;gap:0.4rem;flex-wrap:wrap;">';
                    $.each(p.images, function (i, img) {
                        var activeClass = i === 0 ? 'border-color:var(--primary);opacity:1;' : '';
                        html += '<img src="' + img + '" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:var(--radius-sm);border:2px solid transparent;cursor:pointer;opacity:0.6;' + activeClass + '" class="qv-thumb" data-src="' + img + '">';
                    });
                    html += '</div>';
                }
            } else {
                html += '<div style="width:100%;height:250px;background:var(--bg-alt);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;color:var(--muted);"><i class="fa-solid fa-image" style="font-size:2rem;"></i></div>';
            }
            html += '</div>';

            // Info section
            html += '<div>';
            // Brand
            if (p.brand) {
                html += '<a href="' + p.brand.url + '" style="display:inline-flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;color:var(--primary);font-weight:600;font-size:0.88rem;">';
                html += '  <img src="' + p.brand.logo + '" alt="' + escapeHtml(p.brand.name) + '" style="width:22px;height:22px;object-fit:contain;">';
                html += '  ' + escapeHtml(p.brand.name);
                html += '</a>';
            }

            // Name
            html += '<h3 style="font-family:var(--font-display);font-size:1.35rem;font-weight:700;margin-bottom:0.5rem;">' + escapeHtml(p.name) + '</h3>';

            // Category
            if (p.category) {
                html += '<a href="' + p.category.url + '" style="display:inline-block;padding:0.2rem 0.6rem;border-radius:var(--radius-full);background:var(--bg-alt);font-size:0.75rem;color:var(--text-secondary);margin-bottom:0.75rem;">' + escapeHtml(p.category.name) + '</a>';
            }

            // Description
            if (p.short_description) {
                html += '<p style="font-size:0.88rem;color:var(--text-secondary);line-height:1.6;margin-bottom:1rem;">' + p.short_description + '</p>';
            }

            // Price
            html += '<div style="display:flex;align-items:baseline;gap:0.75rem;margin-bottom:1rem;">';
            if (p.formatted_discount) {
                html += '<span style="font-size:1.4rem;font-weight:800;color:var(--primary);">' + p.formatted_discount + '</span>';
                html += '<span style="font-size:0.95rem;color:var(--muted);text-decoration:line-through;">' + p.formatted_regular + '</span>';
                if (p.discount_percent > 0) {
                    html += '<span style="padding:0.15rem 0.5rem;border-radius:var(--radius-full);background:rgba(239,68,68,0.1);color:var(--danger);font-size:0.72rem;font-weight:700;">-' + p.discount_percent + '%</span>';
                }
            } else if (p.formatted_regular) {
                html += '<span style="font-size:1.4rem;font-weight:800;color:var(--primary);">' + p.formatted_regular + '</span>';
            }
            html += '</div>';

            // Calories
            if (p.calories > 0) {
                html += '<div style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.35rem 0.75rem;border-radius:var(--radius-sm);background:var(--bg-alt);font-size:0.8rem;color:var(--text-secondary);margin-bottom:1rem;">';
                html += '  <i class="fa-solid fa-fire" style="color:var(--primary);"></i> ' + p.calories + ' cal';
                html += '</div>';
            }

            // Ingredients
            if (p.ingredients && p.ingredients.length > 0) {
                html += '<div style="margin-bottom:1rem;">';
                html += '  <div style="font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--muted);margin-bottom:0.4rem;">Ingredients</div>';
                html += '  <div style="display:flex;flex-wrap:wrap;gap:0.3rem;">';
                $.each(p.ingredients, function (i, ing) {
                    html += '<span style="padding:0.2rem 0.55rem;border-radius:var(--radius-full);background:var(--bg-alt);border:1px solid var(--border-light);font-size:0.72rem;color:var(--text-secondary);">' + escapeHtml(ing) + '</span>';
                });
                html += '  </div>';
                html += '</div>';
            }

            // View Full button
            html += '<a href="' + p.url + '" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-md);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;transition:all 0.3s;">';
            html += '  View Full Details <i class="fa-solid fa-arrow-right" style="font-size:0.75rem;"></i>';
            html += '</a>';

            html += '</div>'; // End info section
            html += '</div>'; // End grid

            $body.html(html);

            // Bind thumbnail clicks
            $body.find('.qv-thumb').on('click', function () {
                var src = $(this).data('src');
                $body.find('.qv-main-img').attr('src', src);
                $body.find('.qv-thumb').css({ 'border-color': 'transparent', 'opacity': '0.6' });
                $(this).css({ 'border-color': 'var(--primary)', 'opacity': '1' });
            });

        }).fail(function () {
            $body.html('<div style="padding:3rem;text-align:center;color:var(--danger);"><i class="fa-solid fa-wifi" style="font-size:2rem;"></i><p style="margin-top:1rem;">Network error. Please try again.</p></div>');
        });
    };

    // ============================================================
    // 12. NEWSLETTER FORM
    // ============================================================
    function initNewsletter() {
        $(document).on('submit', '#newsletter-form', function (e) {
            e.preventDefault();

            var $form = $(this);
            var $input = $form.find('#newsletter-email');
            var $msg = $('#newsletter-msg');
            var $btn = $form.find('.newsletter-btn');

            var email = $input.val().trim();

            // Validate
            if (!email) {
                $msg.text('Please enter your email address.').css('color', 'var(--danger)').show();
                return;
            }
            if (!isValidEmail(email)) {
                $msg.text('Please enter a valid email address.').css('color', 'var(--danger)').show();
                return;
            }

            // Disable button
            $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Subscribing...');

            // Simulate subscription (since there's no newsletter API endpoint)
            // In production, you'd POST to an API endpoint here
            setTimeout(function () {
                $msg.text('Successfully subscribed! Check your inbox for confirmation.').css('color', 'var(--success)').show();
                $input.val('');
                $btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane" style="margin-right:0.4rem;"></i> Subscribe');
                showToast('Successfully subscribed to newsletter!', 'success');

                // Hide message after 5 seconds
                setTimeout(function () {
                    $msg.fadeOut(300);
                }, 5000);
            }, 1000);
        });
    }

    // ============================================================
    // 13. AOS (Animate On Scroll) INIT
    // ============================================================
    function initAOS() {
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 700,
                easing: 'ease-out-cubic',
                once: true,        // Animate only once
                offset: 80,        // Trigger offset
                delay: 0,
                anchorPlacement: 'top-bottom'
            });
        }
    }

    // Refresh AOS when dynamic content loads (call from page JS)
    window.refreshAOS = function () {
        if (typeof AOS !== 'undefined') {
            setTimeout(function () {
                AOS.refresh();
            }, 100);
        }
    };

    // ============================================================
    // UTILITY FUNCTIONS
    // ============================================================

    // Escape HTML to prevent XSS
    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // Validate email format
    function isValidEmail(email) {
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    // Format number with commas
    window.formatNumber = function (num) {
        if (num === null || num === undefined) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };

    // Build pagination HTML
    window.buildPagination = function (pagination, onClickCallback) {
        if (!pagination || pagination.total_pages <= 1) return '';

        var html = '<div class="pagination-wrap">';
        html += '<button class="page-btn" data-page="' + (pagination.current_page - 1) + '" ' + (pagination.has_prev ? '' : 'disabled') + '><i class="fa-solid fa-chevron-left"></i></button>';

        // Show smart page range
        var pages = getPaginationRange(pagination.current_page, pagination.total_pages, 5);
        $.each(pages, function (i, p) {
            if (p === '...') {
                html += '<span style="padding:0 0.25rem;color:var(--muted);">...</span>';
            } else {
                var activeClass = p === pagination.current_page ? ' active' : '';
                html += '<button class="page-btn' + activeClass + '" data-page="' + p + '">' + p + '</button>';
            }
        });

        html += '<button class="page-btn" data-page="' + (pagination.current_page + 1) + '" ' + (pagination.has_next ? '' : 'disabled') + '><i class="fa-solid fa-chevron-right"></i></button>';
        html += '</div>';

        return html;
    };

    // Calculate which page numbers to show
    function getPaginationRange(current, total, maxVisible) {
        if (total <= maxVisible) {
            var arr = [];
            for (var i = 1; i <= total; i++) arr.push(i);
            return arr;
        }

        var pages = [];
        var half = Math.floor(maxVisible / 2);
        var start = Math.max(2, current - half);
        var end = Math.min(total - 1, current + half);

        // Adjust if near start
        if (current <= half + 1) {
            end = Math.min(total - 1, maxVisible - 1);
        }

        // Adjust if near end
        if (current >= total - half) {
            start = Math.max(2, total - maxVisible + 2);
        }

        pages.push(1);
        if (start > 2) pages.push('...');

        for (var j = start; j <= end; j++) {
            pages.push(j);
        }

        if (end < total - 1) pages.push('...');
        pages.push(total);

        return pages;
    }

    // Bind pagination click events (call after inserting HTML)
    window.bindPagination = function ($container, callback) {
        $container.find('.page-btn').on('click', function () {
            if ($(this).is(':disabled')) return;
            var page = $(this).data('page');
            if (page && callback) callback(page);
        });
    };

    // Create skeleton loading HTML
    window.skeletonCards = function (count, type) {
        count = count || 6;
        type = type || 'product'; // product, brand, category, offer

        var html = '';
        for (var i = 0; i < count; i++) {
            html += '<div class="skeleton skeleton-card"></div>';
        }
        return html;
    };

    window.skeletonText = function (lines) {
        lines = lines || 3;
        var html = '';
        for (var i = 0; i < lines; i++) {
            var cls = i === 0 ? 'skeleton skeleton-title' : (i === lines - 1 ? 'skeleton skeleton-text short' : 'skeleton skeleton-text');
            html += '<div class="' + cls + '"></div>';
        }
        return html;
    };

    // Copy to clipboard
    window.copyToClipboard = function (text, successMsg) {
        successMsg = successMsg || 'Copied to clipboard!';
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                showToast(successMsg, 'success');
            });
        } else {
            // Fallback for older browsers
            var $temp = $('<textarea>');
            $temp.val(text).css('position', 'absolute').css('left', '-9999px').appendTo('body');
            $temp.select();
            document.execCommand('copy');
            $temp.remove();
            showToast(successMsg, 'success');
        }
    };

})();

