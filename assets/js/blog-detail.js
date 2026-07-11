/**
 * blog-detail.js — Loaded ONLY on blog-detail.php (Blog article page, URL: /blog/{slug})
 * Handles: article hero, content render, author box, share buttons,
 * sidebar (search + categories + related), "more articles" grid
 */

(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '/food-brands';

    // Blog slug from PHP (injected via inline script in blog-detail.php)
    var blogSlug = window.BLOG_SLUG || '';

    var blogData = null; // Store full API response

    $(document).ready(function () {
        if (!blogSlug) {
            showError('Article slug is missing. Please check the URL.');
            return;
        }

        loadBlogDetail();
        loadCategoriesWidget();
        bindEvents();
    });

    // ============================================================
    // LOAD BLOG DETAIL
    // ============================================================
    function loadBlogDetail() {
        showPageSkeleton();

        $.getJSON(BASE_URL + '/api/site/blog.php', { action: 'detail', slug: blogSlug }, function (res) {
            if (!res.success) {
                if (res.message && res.message.indexOf('not found') !== -1) {
                    show404();
                } else {
                    showError(res.message || 'Failed to load this article.');
                }
                return;
            }

            blogData = res;

            hidePageSkeleton();
            renderHero(res.blog);
            renderImage(res.blog);
            renderContent(res.blog);
            renderAuthorBox(res.blog);
            renderRelatedWidget(res.related);
            renderMoreGrid(res.related);
            initShareButtons(res.blog);

            document.title = res.blog.meta_title || res.blog.title;

            // Inject Schema.org JSON-LD returned by the API
            if (res.schema_json) {
                $('head').append('<script type="application/ld+json">' + res.schema_json + '<' + '/script>');
            }

            window.refreshAOS();

        }).fail(function () {
            showError('Network error. Please check your connection and try again.');
        });
    }

    // ============================================================
    // RENDER HERO (breadcrumb, category badge, title, meta row)
    // ============================================================
    function renderHero(blog) {
        var $breadcrumb = $('#bd-breadcrumb-title');
        if ($breadcrumb.length) $breadcrumb.text(blog.title);

        var $title = $('#bd-title');
        if ($title.length) $title.text(blog.title);

        var $badge = $('#bd-category-badge');
        if ($badge.length) {
            $badge.html(
                '<span style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.35rem 0.9rem;border-radius:var(--radius-full);background:rgba(255,255,255,0.15);color:#fff;font-size:0.78rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">' +
                '<i class="fa-solid fa-folder"></i> ' + escapeHtml(blog.category) +
                '</span>'
            );
        }

        var $meta = $('#bd-meta-row');
        if ($meta.length) {
            var html = '';
            html += '<span><i class="fa-regular fa-calendar" style="margin-right:0.4rem;"></i>' + escapeHtml(blog.published_at_human) + '</span>';
            html += '<span><i class="fa-regular fa-clock" style="margin-right:0.4rem;"></i>' + blog.read_time + ' min read</span>';
            html += '<span><i class="fa-regular fa-eye" style="margin-right:0.4rem;"></i>' + formatNumber(blog.views) + ' views</span>';
            if (blog.author_name) {
                html += '<span><i class="fa-regular fa-user" style="margin-right:0.4rem;"></i>' + escapeHtml(blog.author_name) + '</span>';
            }
            $meta.html(html);
        }
    }

    // ============================================================
    // RENDER FEATURED IMAGE
    // ============================================================
    function renderImage(blog) {
        var $img = $('#bd-image');
        if ($img.length) {
            $img.attr('src', blog.image);
            $img.attr('alt', blog.title);
        }
    }

    // ============================================================
    // RENDER ARTICLE CONTENT (trusted HTML from CMS)
    // ============================================================
    function renderContent(blog) {
        var $content = $('#bd-content');
        if ($content.length) {
            $content.html(blog.content || '<p>' + escapeHtml(blog.excerpt || '') + '</p>');
        }
    }

    // ============================================================
    // RENDER AUTHOR BOX
    // ============================================================
    function renderAuthorBox(blog) {
        var $box = $('#bd-author-box');
        if (!$box.length || !blog.author_name) return;

        var avatar = blog.author_image
            ? '<img src="' + blog.author_image + '" alt="' + escapeHtml(blog.author_name) + '" style="width:56px;height:56px;border-radius:50%;object-fit:cover;flex-shrink:0;">'
            : '<div style="width:56px;height:56px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:700;font-size:1.2rem;flex-shrink:0;">' + escapeHtml(blog.author_name.charAt(0).toUpperCase()) + '</div>';

        $box.css('display', 'flex').html(
            avatar +
            '<div>' +
            '<div style="font-size:0.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;font-weight:700;margin-bottom:0.15rem;">Written by</div>' +
            '<div style="font-family:var(--font-display);font-weight:700;font-size:1.05rem;">' + escapeHtml(blog.author_name) + '</div>' +
            '</div>'
        );
    }

    // ============================================================
    // RENDER RELATED ARTICLES (sidebar widget — compact list)
    // ============================================================
    function renderRelatedWidget(related) {
        var $wrap = $('#bd-related-widget');
        if (!$wrap.length) return;

        if (!related || related.length === 0) {
            $wrap.html('<div style="font-size:0.82rem;color:var(--muted);">No related articles yet.</div>');
            return;
        }

        var html = '';
        $.each(related, function (i, post) {
            html += '<a href="' + post.url + '" style="display:flex;gap:0.75rem;margin-bottom:1rem;text-decoration:none;">';
            html += '  <img src="' + post.image + '" alt="' + escapeHtml(post.title) + '" style="width:64px;height:64px;border-radius:var(--radius-sm);object-fit:cover;flex-shrink:0;">';
            html += '  <div>';
            html += '    <div style="font-size:0.85rem;font-weight:600;color:var(--text);line-height:1.35;margin-bottom:0.25rem;">' + escapeHtml(post.title) + '</div>';
            html += '    <div style="font-size:0.72rem;color:var(--muted);">' + escapeHtml(post.published_at_human) + '</div>';
            html += '  </div>';
            html += '</a>';
        });

        $wrap.html(html);
    }

    // ============================================================
    // RENDER "MORE FROM THE BLOG" GRID (full cards)
    // ============================================================
    function renderMoreGrid(related) {
        var $grid = $('#bd-more-grid');
        var $section = $('#bd-more-section');
        if (!$grid.length) return;

        if (!related || related.length === 0) {
            if ($section.length) $section.hide();
            return;
        }

        var html = '';
        $.each(related, function (i, post) {
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
        html += '</div>';
        html += '<h3 class="blog-card-title"><a href="' + post.url + '" style="color:var(--text);transition:color 0.3s;">' + escapeHtml(post.title) + '</a></h3>';
        html += '<p class="blog-card-excerpt">' + escapeHtml(post.excerpt) + '</p>';
        html += '</div>';

        html += '</div>';
        return html;
    }

    // ============================================================
    // LOAD CATEGORIES SIDEBAR WIDGET
    // ============================================================
    function loadCategoriesWidget() {
        $.getJSON(BASE_URL + '/api/site/blog.php', { action: 'categories' }, function (res) {
            var $wrap = $('#bd-categories-widget');
            if (!$wrap.length || !res.success) return;

            if (!res.categories || res.categories.length === 0) {
                $wrap.html('<div style="font-size:0.82rem;color:var(--muted);">No categories yet.</div>');
                return;
            }

            var html = '';
            $.each(res.categories, function (i, cat) {
                html += '<a href="' + BASE_URL + '/blog?category=' + encodeURIComponent(cat.name) + '" class="filter-option" style="text-decoration:none;">';
                html += '  <span>' + escapeHtml(cat.name) + '</span>';
                html += '  <span class="count">' + cat.post_count + '</span>';
                html += '</a>';
            });
            $wrap.html(html);

        }).fail(function () {
            console.warn('Failed to load blog categories widget.');
        });
    }

    // ============================================================
    // SHARE BUTTONS
    // ============================================================
    function initShareButtons(blog) {
        var pageUrl = window.location.href;
        var pageTitle = blog.title;

        $(document).off('click', '.pd-share-copy').on('click', '.pd-share-copy', function () {
            if (window.copyToClipboard) {
                window.copyToClipboard(pageUrl, 'Article link copied!');
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(pageUrl);
            }
        });

        $(document).off('click', '.pd-share-facebook').on('click', '.pd-share-facebook', function () {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(pageUrl), '_blank', 'width=600,height=400');
        });

        $(document).off('click', '.pd-share-twitter').on('click', '.pd-share-twitter', function () {
            var text = encodeURIComponent(pageTitle);
            window.open('https://twitter.com/intent/tweet?text=' + text + '&url=' + encodeURIComponent(pageUrl), '_blank', 'width=600,height=400');
        });

        $(document).off('click', '.pd-share-whatsapp').on('click', '.pd-share-whatsapp', function () {
            var text = encodeURIComponent(pageTitle + ' — ' + pageUrl);
            window.open('https://wa.me/?text=' + text, '_blank');
        });

        $(document).off('click', '.pd-share-linkedin').on('click', '.pd-share-linkedin', function () {
            window.open('https://www.linkedin.com/sharing/share-offsite/?url=' + encodeURIComponent(pageUrl), '_blank', 'width=600,height=400');
        });

        $(document).off('click', '.pd-share-email').on('click', '.pd-share-email', function () {
            var subject = encodeURIComponent(pageTitle);
            var body = encodeURIComponent('Check out this article: ' + pageUrl);
            window.location.href = 'mailto:?subject=' + subject + '&body=' + body;
        });
    }

    // ============================================================
    // SKELETON / ERROR / 404 STATES
    // ============================================================
    function showPageSkeleton() {
        var $pageSkeleton = $('#blog-page-skeleton');
        if ($pageSkeleton.length) {
            $pageSkeleton.show();
            $('#blog-detail-content').hide();
        }
    }

    function hidePageSkeleton() {
        var $pageSkeleton = $('#blog-page-skeleton');
        if ($pageSkeleton.length) {
            $pageSkeleton.hide();
            $('#blog-detail-content').show();
        }
    }

    function showError(msg) {
        hidePageSkeleton();
        var $content = $('#blog-detail-content');
        if ($content.length) {
            $content.html(
                '<div style="text-align:center;padding:4rem 2rem;">' +
                '<i class="fa-solid fa-exclamation-triangle" style="font-size:3rem;color:var(--danger);margin-bottom:1.5rem;display:block;"></i>' +
                '<h2 style="font-family:var(--font-display);font-size:1.5rem;margin-bottom:0.75rem;">Something went wrong</h2>' +
                '<p style="color:var(--text-secondary);max-width:450px;margin:0 auto 1.5rem;">' + escapeHtml(msg) + '</p>' +
                '<a href="' + BASE_URL + '/blog" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;">' +
                '<i class="fa-solid fa-arrow-left" style="font-size:0.75rem;"></i> Back to Blog</a>' +
                '</div>'
            );
            $content.show();
        }
    }

    function show404() {
        hidePageSkeleton();
        var $content = $('#blog-detail-content');
        if ($content.length) {
            $content.html(
                '<div class="error-page">' +
                '<div class="error-code">404</div>' +
                '<h2 class="error-title">Article Not Found</h2>' +
                '<p class="error-desc">The article you\'re looking for doesn\'t exist, is unpublished, or has been removed.</p>' +
                '<a href="' + BASE_URL + '/blog" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;">' +
                '<i class="fa-solid fa-arrow-left" style="font-size:0.75rem;"></i> Browse All Articles</a>' +
                '</div>'
            );
            $content.show();
        }
    }

    // ============================================================
    // BIND EVENTS
    // ============================================================
    function bindEvents() {
        // Sidebar search — redirect to listing page with the search term
        $(document).on('submit', '#bd-search-form', function (e) {
            e.preventDefault();
            var val = $('#bd-search-input').val().trim();
            if (val) {
                window.location.href = BASE_URL + '/blog?search=' + encodeURIComponent(val);
            } else {
                window.location.href = BASE_URL + '/blog';
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

    function formatNumber(num) {
        if (window.formatNumber) return window.formatNumber(num);
        if (num === null || num === undefined) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

})();