/**
 * contact.js — Loaded ONLY on contact.php (Contact page)
 * Handles: form validation, AJAX submission, field-level errors,
 * loading states, success feedback, map fallback
 */

(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '/food-brands';

    // Form state
    var isSubmitting = false;

    $(document).ready(function () {
        initContactForm();
        initInfoCards();
    });

    // ============================================================
    // INIT CONTACT FORM
    // ============================================================
    function initContactForm() {
        var $form = $('#contact-form');
        if (!$form.length) return;

        // Clear individual field errors on input
        $form.find('input, textarea, select').on('input', function () {
            clearFieldError($(this));
        });

        // Clear individual field errors on focus
        $form.find('input, textarea, select').on('focus', function () {
            clearFieldError($(this));
        });

        // Form submit
        $form.on('submit', function (e) {
            e.preventDefault();

            if (isSubmitting) return;

            // Validate all fields
            var errors = validateForm();

            if (errors.length > 0) {
                // Show field-level errors
                $.each(errors, function (i, err) {
                    showFieldError(err.field, err.message);
                });

                // Scroll to first error
                var $firstError = $form.find('.field-error').first();
                if ($firstError.length) {
                    var headerHeight = $('#main-header').outerHeight() || 72;
                    var offset = $firstError.offset().top - headerHeight - 30;
                    $('html, body').animate({ scrollTop: offset }, 400, 'swing');
                    $firstError.closest('.form-group').find('input, textarea, select').first().focus();
                }

                return;
            }

            // All valid — submit
            submitForm();
        });
    }

    // ============================================================
    // VALIDATE FORM
    // ============================================================
    function validateForm() {
        var errors = [];

        // Name
        var name = val('#contact-name');
        if (!name) {
            errors.push({ field: '#contact-name', message: 'Name is required.' });
        } else if (name.length < 2) {
            errors.push({ field: '#contact-name', message: 'Name must be at least 2 characters.' });
        } else if (name.length > 150) {
            errors.push({ field: '#contact-name', message: 'Name must not exceed 150 characters.' });
        }

        // Email
        var email = val('#contact-email');
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email) {
            errors.push({ field: '#contact-email', message: 'Email is required.' });
        } else if (!emailRegex.test(email)) {
            errors.push({ field: '#contact-email', message: 'Please enter a valid email address.' });
        } else if (email.length > 150) {
            errors.push({ field: '#contact-email', message: 'Email must not exceed 150 characters.' });
        }

        // Phone (optional but validated if provided)
        var phone = val('#contact-phone');
        if (phone) {
            var phoneClean = phone.replace(/[\s\-\+\(\)]/g, '');
            if (!/^[0-9]{7,20}$/.test(phoneClean)) {
                errors.push({ field: '#contact-phone', message: 'Please enter a valid phone number (7-20 digits).' });
            }
        }

        // Subject
        var subject = val('#contact-subject');
        if (!subject) {
            errors.push({ field: '#contact-subject', message: 'Subject is required.' });
        } else if (subject.length < 3) {
            errors.push({ field: '#contact-subject', message: 'Subject must be at least 3 characters.' });
        } else if (subject.length > 255) {
            errors.push({ field: '#contact-subject', message: 'Subject must not exceed 255 characters.' });
        }

        // Message
        var message = val('#contact-message');
        if (!message) {
            errors.push({ field: '#contact-message', message: 'Message is required.' });
        } else if (message.length < 10) {
            errors.push({ field: '#contact-message', message: 'Message must be at least 10 characters.' });
        } else if (message.length > 5000) {
            errors.push({ field: '#contact-message', message: 'Message must not exceed 5000 characters.' });
        }

        return errors;
    }

    // ============================================================
    // SUBMIT FORM (AJAX)
    // ============================================================
    function submitForm() {
        isSubmitting = true;

        var $form = $('#contact-form');
        var $btn = $form.find('#contact-submit-btn');
        var $btnText = $btn.find('.btn-text');
        var $btnLoader = $btn.find('.btn-loader');
        var originalText = $btnText.text();

        // Update button state
        $btn.prop('disabled', true).css('opacity', '0.7');
        $btnText.text('Sending...');
        if ($btnLoader.length) {
            $btnLoader.show();
        }

        // Clear previous messages
        hideFormMessage();

        // Collect data
        var formData = {
            name: val('#contact-name'),
            email: val('#contact-email'),
            phone: val('#contact-phone') || '',
            subject: val('#contact-subject'),
            message: val('#contact-message')
        };

        $.ajax({
            url: BASE_URL + '/api/site/contact.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 15000 // 15 second timeout
        })
        .done(function (res) {
            if (res.success) {
                // Show success
                showFormMessage('success', res.message);

                // Reset form
                $form[0].reset();

                // Show message ID if provided
                if (res.message_id) {
                    var $msgId = $('#contact-message-id');
                    if ($msgId.length) {
                        $msgId.html('Your message ID: <strong>#' + res.message_id + '</strong> — save this for reference.').show();
                    }
                }

                // Show toast
                if (window.showToast) {
                    window.showToast('Message sent successfully!', 'success');
                }

                // Scroll to top of form to see success message
                var headerHeight = $('#main-header').outerHeight() || 72;
                var formOffset = $form.offset().top - headerHeight - 30;
                $('html, body').animate({ scrollTop: formOffset }, 400, 'swing');

                // Email note (if any)
                if (res.email_note && window.showToast) {
                    setTimeout(function () {
                        window.showToast(res.email_note, 'info');
                    }, 1500);
                }

            } else {
                // API returned success: false
                if (res.errors && res.errors.length > 0) {
                    // Field-level errors from server
                    $.each(res.errors, function (i, msg) {
                        // Try to match error to a field
                        var fieldMatch = matchErrorToField(msg);
                        if (fieldMatch) {
                            showFieldError(fieldMatch, msg);
                        } else {
                            showFormMessage('error', msg);
                        }
                    });
                } else {
                    showFormMessage('error', res.message || 'Failed to send message. Please try again.');
                }
            }
        })
        .fail(function (xhr) {
            var msg = 'Network error. Please check your connection and try again.';

            if (xhr.status === 422) {
                // Validation errors from server
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.errors && res.errors.length > 0) {
                        $.each(res.errors, function (i, errMsg) {
                            var fieldMatch = matchErrorToField(errMsg);
                            if (fieldMatch) {
                                showFieldError(fieldMatch, errMsg);
                            }
                        });
                        msg = 'Please fix the errors below.';
                    }
                } catch (e) {
                    // JSON parse failed
                }
            } else if (xhr.status === 429) {
                msg = xhr.responseJSON && xhr.responseJSON.message 
                    ? xhr.responseJSON.message 
                    : 'Too many messages sent. Please wait a few minutes before trying again.';
            } else if (xhr.status === 500) {
                msg = 'A server error occurred. Please try again later.';
            } else if (xhr.status === 0) {
                msg = 'Unable to connect to the server. Please check your internet connection.';
            }

            showFormMessage('error', msg);

            if (window.showToast) {
                window.showToast('Failed to send message.', 'error');
            }
        })
        .always(function () {
            // Restore button state
            isSubmitting = false;
            $btn.prop('disabled', false).css('opacity', '1');
            $btnText.text(originalText);
            if ($btnLoader.length) {
                $btnLoader.hide();
            }
        });
    }

    // ============================================================
    // FIELD-LEVEL ERROR DISPLAY
    // ============================================================
    function showFieldError(fieldSelector, message) {
        var $field = $(fieldSelector);
        if (!$field.length) return;

        var $group = $field.closest('.form-group');
        if (!$group.length) $group = $field.parent();

        // Add error class to input
        $field.addClass('is-invalid').removeClass('is-valid');

        // Remove existing error
        $group.find('.field-error').remove();

        // Add error message below input
        var $error = $('<div class="field-error" style="color:var(--danger);font-size:0.78rem;margin-top:0.35rem;display:flex;align-items:center;gap:0.3rem;">');
        $error.html('<i class="fa-solid fa-circle-exclamation" style="font-size:0.7rem;"></i> ' + escapeHtml(message));
        $group.append($error);

        // Add red border
        $field.css('border-color', 'var(--danger)');
    }

    function clearFieldError($field) {
        if (!$field.length) return;

        var $group = $field.closest('.form-group');
        if (!$group.length) $group = $field.parent();

        $field.removeClass('is-invalid');
        $group.find('.field-error').remove();
        $field.css('border-color', '');
    }

    // ============================================================
    // FORM-LEVEL MESSAGE DISPLAY
    // ============================================================
    function showFormMessage(type, message) {
        var $container = $('#contact-form-message');
        if (!$container.length) return;

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

        var bgMap = {
            success: 'rgba(34,197,94,0.08)',
            error: 'rgba(239,68,68,0.08)',
            warning: 'rgba(245,158,11,0.08)',
            info: 'rgba(232,93,4,0.08)'
        };

        var icon = iconMap[type] || iconMap.info;
        var color = colorMap[type] || colorMap.info;
        var bg = bgMap[type] || bgMap.info;

        var html = '<div style="display:flex;align-items:flex-start;gap:0.75rem;padding:1rem 1.25rem;border-radius:var(--radius-md);background:' + bg + ';border:1px solid ' + color + ';margin-bottom:1.5rem;animation:fadeIn 0.3s ease;">';
        html += '<i class="fa-solid ' + icon + '" style="color:' + color + ';font-size:1.1rem;margin-top:0.1rem;flex-shrink:0;"></i>';
        html += '<div>';
        html += '<div style="font-weight:600;font-size:0.9rem;color:' + color + ';margin-bottom:0.2rem;">' + (type === 'success' ? 'Message Sent!' : 'Error') + '</div>';
        html += '<div style="font-size:0.85rem;color:var(--text-secondary);line-height:1.5;">' + escapeHtml(message) + '</div>';
        html += '</div>';
        html += '</div>';

        $container.html(html).show();

        // Auto-hide success message after 10 seconds
        if (type === 'success') {
            setTimeout(function () {
                $container.fadeOut(500, function () {
                    $container.html('').hide();
                });
            }, 10000);
        }
    }

    function hideFormMessage() {
        var $container = $('#contact-form-message');
        if ($container.length) {
            $container.html('').hide();
        }
    }

    // ============================================================
    // MATCH ERROR MESSAGE TO FORM FIELD
    // ============================================================
    function matchErrorToField(errorMessage) {
        var msg = errorMessage.toLowerCase();

        if (msg.indexOf('name') !== -1) return '#contact-name';
        if (msg.indexOf('email') !== -1) return '#contact-email';
        if (msg.indexOf('phone') !== -1) return '#contact-phone';
        if (msg.indexOf('subject') !== -1) return '#contact-subject';
        if (msg.indexOf('message') !== -1) return '#contact-message';

        return null;
    }

    // ============================================================
    // INFO CARDS (Contact info section)
    // ============================================================
    function initInfoCards() {
        // Animate info cards on scroll
        var $cards = $('.contact-info-card');
        if (!$cards.length) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    $(entry.target).addClass('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        $cards.each(function () {
            observer.observe(this);
        });

        // Copy email/phone on click
        $(document).on('click', '.contact-copy-info', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var text = $(this).data('copy');
            var label = $(this).data('label') || 'Copied';

            if (text && window.copyToClipboard) {
                window.copyToClipboard(text, label + ' to clipboard!');
            }
        });

        // Phone link — show toast with copy option
        $(document).on('click', '.contact-phone-link', function (e) {
            // Let the default tel: link work on mobile
            if (/Mobi|Android/i.test(navigator.userAgent)) {
                return; // Let native dialer handle it
            }

            // On desktop, copy the number
            e.preventDefault();
            var phone = $(this).data('phone');
            if (phone && window.copyToClipboard) {
                window.copyToClipboard(phone, 'Phone number copied!');
            }
        });
    }

    // ============================================================
    // CHARACTER COUNTER (for message textarea)
    // ============================================================
    $(document).on('input', '#contact-message', function () {
        var $counter = $('#contact-char-counter');
        if (!$counter.length) return;

        var len = $(this).val().length;
        var max = 5000;
        var remaining = max - len;

        if (remaining < 100) {
            $counter.css('color', 'var(--danger)');
        } else if (remaining < 500) {
            $counter.css('color', 'var(--warning)');
        } else {
            $counter.css('color', 'var(--muted)');
        }

        $counter.text(remaining + ' characters remaining');
    });

    // ============================================================
    // UTILITY
    // ============================================================
    function val(selector) {
        var $el = $(selector);
        return $el.length ? $el.val().trim() : '';
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

})();