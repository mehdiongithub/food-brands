/**
 * faq.js — Loaded ONLY on faq.php (FAQ page)
 * Handles: FAQ accordion, URL hash anchors, search/filter,
 * smooth animations, Schema.org JSON-LD injection
 */

(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '/food-brands';

    // DOM references
    var $faqContainer;
    var faqData = null;

    $(document).ready(function () {
        $faqContainer = $('#faq-list');

        if (!$faqContainer.length) return;

        loadFAQs();
        bindEvents();
    });

    // ============================================================
    // LOAD FAQS
    // ============================================================
    function loadFAQs() {
        // Show skeleton
        $faqContainer.html(window.skeletonText ? window.skeletonText(8) : '');

        $.getJSON(BASE_URL + '/api/site/faqs.php', {
            action: 'list'
        }, function (res) {
            if (!res.success) {
                showError('Failed to load FAQs. Please try again.');
                return;
            }

            faqData = res;

            if (res.faqs.length === 0) {
                showEmpty();
                return;
            }

            renderFAQs(res.faqs);

            // Inject Schema.org JSON-LD for SEO
            if (res.schema_json) {
                injectSchema(res.schema_json);
            }

            // Handle URL hash anchor (#faq-5)
            handleHashAnchor();

            window.refreshAOS();

        }).fail(function () {
            showError('Network error. Please check your connection and try again.');
        });
    }

    // ============================================================
    // RENDER FAQS
    // ============================================================
    function renderFAQs(faqs) {
        var html = '';

        $.each(faqs, function (i, faq) {
            html += buildFaqItem(faq, i);
        });

        $faqContainer.html(html);
    }

    function buildFaqItem(faq, index) {
        var delay = Math.min(index * 40, 320);

        var html = '<div class="faq-item" id="' + faq.anchor + '" data-faq-id="' + faq.id + '" data-aos="fade-up" data-aos-delay="' + delay + '">';
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

    // ============================================================
    // HANDLE URL HASH ANCHOR
    // ============================================================
    function handleHashAnchor() {
        var hash = window.location.hash;

        if (!hash || hash.indexOf('#faq-') !== 0) return;

        var faqId = hash.replace('#faq-', '');

        // Small delay to ensure DOM is rendered and AOS is initialized
        setTimeout(function () {
            var $target = $('#faq-' + faqId);

            if ($target.length) {
                // Open this FAQ
                openFaqItem($target);

                // Scroll to it (offset for fixed header)
                var headerHeight = $('#main-header').outerHeight() || 72;
                var targetOffset = $target.offset().top - headerHeight - 20;

                $('html, body').animate({
                    scrollTop: targetOffset
                }, 500, 'swing');
            }
        }, 400);
    }

    // ============================================================
    // OPEN / CLOSE FAQ ITEM
    // ============================================================
    function openFaqItem($item) {
        // Close all others
        $item.siblings('.faq-item').removeClass('active');
        $item.siblings('.faq-item').find('.faq-answer').css('max-height', '0');

        // Open this one
        $item.addClass('active');
        var $answer = $item.find('.faq-answer');
        var contentHeight = $answer.find('.faq-answer-inner').outerHeight();
        $answer.css('max-height', (contentHeight + 20) + 'px');
    }

    function closeFaqItem($item) {
        $item.removeClass('active');
        $item.find('.faq-answer').css('max-height', '0');
    }

    function closeAllFaqs() {
        $('.faq-item').removeClass('active');
        $('.faq-item .faq-answer').css('max-height', '0');
    }

    // ============================================================
    // SEARCH / FILTER FAQS
    // ============================================================
    function filterFAQs(keyword) {
        if (!keyword || keyword.length < 2) {
            // Reset — show all
            if (faqData && faqData.faqs) {
                renderFAQs(faqData.faqs);
                window.refreshAOS();
            }
            updateFilterCount(faqData ? faqData.faqs.length : 0, 0);
            return;
        }

        var lowerKeyword = keyword.toLowerCase();
        var matched = [];
        var unmatched = [];

        if (faqData && faqData.faqs) {
            $.each(faqData.faqs, function (i, faq) {
                var questionMatch = faq.question.toLowerCase().indexOf(lowerKeyword) !== -1;
                var answerMatch = faq.answer_plain ? faq.answer_plain.toLowerCase().indexOf(lowerKeyword) !== -1 : false;

                if (questionMatch || answerMatch) {
                    // Highlight matching text in question
                    var highlightedQuestion = highlightText(faq.question, keyword);
                    matched.push({
                        id: faq.id,
                        anchor: faq.anchor,
                        question: highlightedQuestion,
                        question_raw: faq.question,
                        answer: faq.answer,
                        answer_plain: faq.answer_plain
                    });
                } else {
                    unmatched.push(faq);
                }
            });
        }

        // Render matched FAQs first
        var allFaqs = matched.concat(unmatched);
        renderFAQs(allFaqs);
        window.refreshAOS();

        // Update count
        updateFilterCount(matched.length, faqData ? faqData.faqs.length : 0);

        // Auto-open first matched FAQ
        if (matched.length > 0) {
            var $firstMatch = $('#' + matched[0].anchor);
            if ($firstMatch.length) {
                openFaqItem($firstMatch);
            }
        }
    }

    function updateFilterCount(shown, total) {
        var $countEl = $('#faq-filter-count');
        if (!$countEl.length) return;

        if (shown < total) {
            $countEl.html('Showing <strong>' + shown + '</strong> of <strong>' + total + '</strong> questions');
            $countEl.show();
        } else {
            $countEl.hide();
        }
    }

    function highlightText(text, keyword) {
        if (!keyword || !text) return escapeHtml(text);

        // Escape HTML first to prevent XSS
        var escaped = escapeHtml(text);
        var escapedKeyword = escapeHtml(keyword);

        // Wrap matches in <mark> tags (case-insensitive)
        var regex = new RegExp('(' + escapeRegex(escapedKeyword) + ')', 'gi');
        return escaped.replace(regex, '<mark>$1</mark>');
    }

    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // ============================================================
    // EXPAND / COLLAPSE ALL
    // ============================================================
    function expandAll() {
        $('.faq-item').each(function () {
            var $item = $(this);
            $item.addClass('active');
            var $answer = $item.find('.faq-answer');
            var contentHeight = $answer.find('.faq-answer-inner').outerHeight();
            $answer.css('max-height', (contentHeight + 20) + 'px');
        });

        var $expandBtn = $('#btn-expand-all');
        var $collapseBtn = $('#btn-collapse-all');
        if ($expandBtn.length) $expandBtn.hide();
        if ($collapseBtn.length) $collapseBtn.show();
    }

    function collapseAll() {
        closeAllFaqs();

        var $expandBtn = $('#btn-expand-all');
        var $collapseBtn = $('#btn-collapse-all');
        if ($expandBtn.length) $expandBtn.show();
        if ($collapseBtn.length) $collapseBtn.hide();
    }

    // ============================================================
    // INJECT SCHEMA.ORG JSON-LD
    // ============================================================
    function injectSchema(schemaJson) {
        if (!schemaJson) return;

        // Remove any existing FAQ schema
        $('#faq-schema-script').remove();

        var $script = $('<script>');
        $script.attr('type', 'application/ld+json');
        $script.attr('id', 'faq-schema-script');
        $script.text(schemaJson);
        $('head').append($script);
    }

    // ============================================================
    // BIND ALL EVENTS
    // ============================================================
    function bindEvents() {

        // --- FAQ question click (accordion toggle) ---
        $(document).on('click', '.faq-question', function () {
            var $item = $(this).closest('.faq-item');
            var isOpen = $item.hasClass('active');

            if (isOpen) {
                closeFaqItem($item);
            } else {
                openFaqItem($item);
            }

            // Update expand/collapse button visibility
            var anyOpen = $('.faq-item.active').length > 0;
            var $expandBtn = $('#btn-expand-all');
            var $collapseBtn = $('#btn-collapse-all');

            if ($expandBtn.length && $collapseBtn.length) {
                if (anyOpen) {
                    $expandBtn.hide();
                    $collapseBtn.show();
                } else {
                    $expandBtn.show();
                    $collapseBtn.hide();
                }
            }
        });

        // --- Search input (debounced) ---
        var searchTimer = null;
        $(document).on('input', '#faq-search-input', function () {
            var val = $(this).val().trim();
            clearTimeout(searchTimer);

            searchTimer = setTimeout(function () {
                filterFAQs(val);

                // Update URL hash (remove if empty, keep clean)
                if (val) {
                    window.history.replaceState(null, '', '#search=' + encodeURIComponent(val));
                } else {
                    window.history.replaceState(null, '', window.location.pathname);
                }
            }, 350);
        });

        // --- Search form submit ---
        $(document).on('submit', '#faq-search-form', function (e) {
            e.preventDefault();
            var val = $(this).find('#faq-search-input').val().trim();
            filterFAQs(val);
        });

        // --- Clear search ---
        $(document).on('click', '#btn-clear-faq-search', function () {
            var $input = $('#faq-search-input');
            if ($input.length) $input.val('');
            filterFAQs('');
            window.history.replaceState(null, '', window.location.pathname);
            $input.focus();
        });

        // --- Expand All ---
        $(document).on('click', '#btn-expand-all', function () {
            expandAll();
        });

        // --- Collapse All ---
        $(document).on('click', '#btn-collapse-all', function () {
            collapseAll();
        });

        // --- Hash change (browser back/forward or manual) ---
        $(window).on('hashchange', function () {
            handleHashAnchor();
        });

        // --- Keyboard navigation inside FAQ ---
        $(document).on('keydown', '.faq-question', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });
    }

    // ============================================================
    // ERROR / EMPTY STATES
    // ============================================================
    function showError(msg) {
        $faqContainer.html(
            '<div style="text-align:center;padding:3rem;">' +
            '<i class="fa-solid fa-exclamation-triangle" style="font-size:2.5rem;color:var(--danger);margin-bottom:1rem;display:block;"></i>' +
            '<p style="color:var(--text-secondary);margin-bottom:1rem;">' + escapeHtml(msg) + '</p>' +
            '<button onclick="location.reload()" style="padding:0.5rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;border:none;cursor:pointer;font-family:var(--font-body);font-weight:600;">Try Again</button>' +
            '</div>'
        );
    }

    function showEmpty() {
        $faqContainer.html(
            '<div style="text-align:center;padding:4rem 2rem;">' +
            '<i class="fa-solid fa-circle-question" style="font-size:3rem;color:var(--muted);opacity:0.3;margin-bottom:1.5rem;display:block;"></i>' +
            '<h3 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:0.5rem;">No FAQs Yet</h3>' +
            '<p style="color:var(--text-secondary);max-width:400px;margin:0 auto;font-size:0.9rem;">We haven\'t added any frequently asked questions yet. Please check back later or contact us directly.</p>' +
            '<div style="margin-top:1.5rem;">' +
            '<a href="' + BASE_URL + '/contact" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;">' +
            '<i class="fa-solid fa-envelope" style="font-size:0.8rem;"></i> Contact Us</a>' +
            '</div>' +
            '</div>'
        );
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