<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Get site settings for SEO
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'FoodScope';
 $siteEmail = $settings['email'] ?? '';
 $sitePhone = $settings['phone'] ?? '';
 $siteAddress = $settings['address'] ?? '';

// Page SEO
 $pageTitle = pageTitle('Contact Us');
 $pageDescription = 'Get in touch with ' . $siteName . '. Send us your questions, feedback, or suggestions and our team will get back to you shortly.';
 $canonical = BASE_URL . '/contact';

// Schema.org JSON-LD for contact page
 $contactSchemaData = [
    '@context' => 'https://schema.org',
    '@type' => 'ContactPage',
    'name' => 'Contact Us — ' . $siteName,
    'description' => $pageDescription,
    'url' => $canonical,
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => BASE_URL . '/'
    ]
 ];

 $contactPoint = array_filter([
    '@type' => 'ContactPoint',
    'contactType' => 'customer support',
    'email' => $siteEmail ?: null,
    'telephone' => $sitePhone ?: null,
 ]);

if (count($contactPoint) > 1) {
    $contactSchemaData['mainEntity'] = [
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => BASE_URL . '/',
        'contactPoint' => [$contactPoint]
    ];
}

 $contactSchema = json_encode($contactSchemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

 $schemaJson = $contactSchema;

// Include header
require_once __DIR__ . '/includes/header.php';
?>

<!-- ============================================================
     PAGE BANNER
     ============================================================ -->
<section class="page-banner">
    <div class="container">
        <div class="page-banner-content">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/">Home</a></li>
                    <li class="breadcrumb-item active">Contact</li>
                </ol>
            </nav>
            <h1>Contact Us</h1>
            <p>Have a question, feedback, or a brand you'd like to see added? We'd love to hear from you — send us a message and our team will get back to you shortly.</p>
        </div>
    </div>
</section>

<!-- ============================================================
     CONTACT INFO CARDS
     ============================================================ -->
<section class="section-padding" style="padding-bottom:0;">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="50">
                <div class="wcu-card contact-info-card">
                    <div class="wcu-icon"><i class="fa-solid fa-envelope"></i></div>
                    <h3 class="wcu-title">Email Us</h3>
                    <?php if ($siteEmail): ?>
                    <p class="wcu-desc">
                        <a href="mailto:<?php echo clean($siteEmail); ?>" class="contact-copy-info" data-copy="<?php echo clean($siteEmail); ?>" data-label="Email" style="color:var(--text-secondary);text-decoration:none;"><?php echo clean($siteEmail); ?></a>
                    </p>
                    <?php else: ?>
                    <p class="wcu-desc">Drop us a message using the form below.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="wcu-card contact-info-card">
                    <div class="wcu-icon"><i class="fa-solid fa-phone"></i></div>
                    <h3 class="wcu-title">Call Us</h3>
                    <?php if ($sitePhone): ?>
                    <p class="wcu-desc">
                        <a href="tel:<?php echo clean($sitePhone); ?>" class="contact-phone-link" data-phone="<?php echo clean($sitePhone); ?>" style="color:var(--text-secondary);text-decoration:none;"><?php echo clean($sitePhone); ?></a>
                    </p>
                    <?php else: ?>
                    <p class="wcu-desc">Reach out via email or the contact form.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="150">
                <div class="wcu-card contact-info-card">
                    <div class="wcu-icon"><i class="fa-solid fa-location-dot"></i></div>
                    <h3 class="wcu-title">Visit Us</h3>
                    <p class="wcu-desc"><?php echo $siteAddress ? nl2br(clean($siteAddress)) : 'Our support team works remotely to assist customers everywhere.'; ?></p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="wcu-card contact-info-card">
                    <div class="wcu-icon"><i class="fa-solid fa-clock"></i></div>
                    <h3 class="wcu-title">Response Time</h3>
                    <p class="wcu-desc">We typically reply within 24–48 hours on business days.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     CONTACT FORM SECTION
     ============================================================ -->
<section class="section-padding">
    <div class="container">
        <div class="row g-5 align-items-start">

            <!-- ===== FORM COLUMN ===== -->
            <div class="col-lg-7" data-aos="fade-up">
                <div class="section-header" style="text-align:left;margin-bottom:2rem;">
                    <div class="section-label">Get In Touch</div>
                    <h2 class="section-title">Send Us a Message</h2>
                    <p class="section-desc">Fill out the form below and we'll get back to you as soon as possible.</p>
                </div>

                <!-- Form message (success/error banner injected by contact.js) -->
                <div id="contact-form-message" style="display:none;"></div>

                <form id="contact-form" class="contact-form" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact-name" class="form-label">Full Name <span style="color:var(--danger);">*</span></label>
                                <input type="text" class="form-control" id="contact-name" name="name" placeholder="Your name" maxlength="150" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact-email" class="form-label">Email Address <span style="color:var(--danger);">*</span></label>
                                <input type="email" class="form-control" id="contact-email" name="email" placeholder="you@example.com" maxlength="150" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact-phone" class="form-label">Phone Number <span style="color:var(--muted);font-weight:400;">(optional)</span></label>
                                <input type="tel" class="form-control" id="contact-phone" name="phone" placeholder="e.g. 03001234567" maxlength="30">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contact-subject" class="form-label">Subject <span style="color:var(--danger);">*</span></label>
                                <input type="text" class="form-control" id="contact-subject" name="subject" placeholder="What's this about?" maxlength="255" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="contact-message" class="form-label">Message <span style="color:var(--danger);">*</span></label>
                                <textarea class="form-control" id="contact-message" name="message" rows="6" maxlength="5000" placeholder="Write your message here..." required></textarea>
                                <div id="contact-char-counter" style="font-size:0.75rem;color:var(--muted);margin-top:0.35rem;text-align:right;">5000 characters remaining</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn-primary-custom" id="contact-submit-btn" style="width:100%;position:relative;">
                                <span class="btn-text">Send Message</span>
                                <i class="fa-solid fa-spinner fa-spin btn-loader" style="display:none;margin-left:0.5rem;"></i>
                            </button>
                            <div id="contact-message-id" style="display:none;margin-top:0.75rem;font-size:0.82rem;color:var(--text-secondary);"></div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ===== SIDE COLUMN ===== -->
            <div class="col-lg-5" data-aos="fade-up" data-aos-delay="100">
                <div style="background:var(--bg-alt);border:1px solid var(--border-light);border-radius:var(--radius-lg);padding:2rem;">
                    <h3 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:1rem;">Why Contact Us?</h3>
                    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:1rem;">
                        <li style="display:flex;gap:0.75rem;align-items:flex-start;">
                            <i class="fa-solid fa-circle-check" style="color:var(--primary);margin-top:0.25rem;"></i>
                            <span style="color:var(--text-secondary);font-size:0.92rem;">Report incorrect prices or missing menu items</span>
                        </li>
                        <li style="display:flex;gap:0.75rem;align-items:flex-start;">
                            <i class="fa-solid fa-circle-check" style="color:var(--primary);margin-top:0.25rem;"></i>
                            <span style="color:var(--text-secondary);font-size:0.92rem;">Suggest a new brand or country to add</span>
                        </li>
                        <li style="display:flex;gap:0.75rem;align-items:flex-start;">
                            <i class="fa-solid fa-circle-check" style="color:var(--primary);margin-top:0.25rem;"></i>
                            <span style="color:var(--text-secondary);font-size:0.92rem;">Partnership and business inquiries</span>
                        </li>
                        <li style="display:flex;gap:0.75rem;align-items:flex-start;">
                            <i class="fa-solid fa-circle-check" style="color:var(--primary);margin-top:0.25rem;"></i>
                            <span style="color:var(--text-secondary);font-size:0.92rem;">General feedback and support questions</span>
                        </li>
                    </ul>

                    <hr style="border-color:var(--border-light);margin:1.5rem 0;">

                    <h4 style="font-size:1rem;margin-bottom:0.75rem;">Prefer FAQs?</h4>
                    <p style="color:var(--text-secondary);font-size:0.88rem;margin-bottom:1rem;">Check our frequently asked questions — you might find your answer instantly.</p>
                    <a href="<?php echo BASE_URL; ?>/faq" class="btn-primary-custom" style="display:inline-block;text-decoration:none;">Visit FAQ Page</a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     SEO CONTENT SECTION (Hidden visually, readable by crawlers)
     ============================================================ -->
<section style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;">
    <h2>Contact <?php echo $siteName; ?></h2>
    <p>Reach out to the <?php echo $siteName; ?> team for any questions about food menus, pricing, brands, or categories listed on our platform. Whether you've spotted an outdated price, want to suggest a new brand, or simply have feedback, our contact form makes it easy to get in touch. We aim to respond to all inquiries within 24 to 48 hours.</p>
</section>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>