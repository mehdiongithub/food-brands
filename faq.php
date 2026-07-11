<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Get site settings for SEO
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'FoodScope';

// Page SEO
 $pageTitle = pageTitle('Frequently Asked Questions');
 $pageDescription = 'Find answers to common questions about ' . $siteName . ' — pricing, brands, countries, offers, and how our platform works.';
 $canonical = BASE_URL . '/faq';

// Note: Schema.org FAQPage JSON-LD is injected dynamically by faq.js
// once the FAQs are fetched (so it accurately reflects live content).
// We still set a lightweight WebPage schema here for the initial page load.
 $faqPageSchema = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'Frequently Asked Questions — ' . $siteName,
    'description' => $pageDescription,
    'url' => $canonical,
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => BASE_URL . '/'
    ]
 ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

 $schemaJson = $faqPageSchema;

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
                    <li class="breadcrumb-item active">FAQ</li>
                </ol>
            </nav>
            <h1>Frequently Asked Questions</h1>
            <p>Everything you need to know about <?php echo clean($siteName); ?> — browse by topic, search, or get in touch if you can't find your answer.</p>
        </div>
    </div>
</section>

<!-- ============================================================
     FAQ SECTION
     ============================================================ -->
<section class="section-padding">
    <div class="container">
        <div style="max-width:800px;margin:0 auto;">

            <div class="section-header" data-aos="fade-up">
                <div class="section-label">Got Questions?</div>
                <h2 class="section-title">How Can We Help You?</h2>
                <p class="section-desc">Search below or browse through all frequently asked questions.</p>
            </div>

            <!-- ===== SEARCH BAR ===== -->
            <form id="faq-search-form" style="position:relative;margin-bottom:1rem;" data-aos="fade-up">
                <input type="text" id="faq-search-input" class="form-control" placeholder="Search questions..." style="padding-left:2.75rem;padding-right:2.75rem;font-size:0.95rem;border-radius:var(--radius-full);">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.9rem;pointer-events:none;"></i>
                <button type="button" id="btn-clear-faq-search" aria-label="Clear search" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);width:28px;height:28px;border-radius:50%;border:none;background:var(--bg-alt);color:var(--muted);cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </form>

            <!-- ===== TOOLBAR: filter count + expand/collapse ===== -->
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.5rem;" data-aos="fade-up">
                <span id="faq-filter-count" style="font-size:0.85rem;color:var(--text-secondary);display:none;"></span>
                <div style="margin-left:auto;display:flex;gap:0.5rem;">
                    <button type="button" id="btn-expand-all" style="padding:0.4rem 1rem;border-radius:var(--radius-full);border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);font-size:0.8rem;font-weight:600;cursor:pointer;">
                        <i class="fa-solid fa-plus" style="font-size:0.7rem;margin-right:0.35rem;"></i>Expand All
                    </button>
                    <button type="button" id="btn-collapse-all" style="display:none;padding:0.4rem 1rem;border-radius:var(--radius-full);border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);font-size:0.8rem;font-weight:600;cursor:pointer;">
                        <i class="fa-solid fa-minus" style="font-size:0.7rem;margin-right:0.35rem;"></i>Collapse All
                    </button>
                </div>
            </div>

            <!-- ===== FAQ LIST (populated by faq.js via AJAX) ===== -->
            <div id="faq-list" data-aos="fade-up">
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text short"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text short"></div>
                <div class="skeleton skeleton-text"></div>
            </div>

            <!-- ===== STILL NEED HELP CTA ===== -->
            <div style="text-align:center;margin-top:3rem;padding:2.5rem 2rem;background:var(--bg-alt);border-radius:var(--radius-lg);border:1px solid var(--border-light);" data-aos="fade-up">
                <i class="fa-solid fa-headset" style="font-size:2rem;color:var(--primary);margin-bottom:1rem;display:block;"></i>
                <h3 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:0.5rem;">Still Have Questions?</h3>
                <p style="color:var(--text-secondary);font-size:0.9rem;margin-bottom:1.5rem;">Can't find the answer you're looking for? Our team is happy to help.</p>
                <a href="<?php echo BASE_URL; ?>/contact" class="btn-primary-custom" style="display:inline-block;text-decoration:none;">
                    <i class="fa-solid fa-envelope" style="margin-right:0.5rem;"></i>Contact Us
                </a>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     SEO CONTENT SECTION (Hidden visually, readable by crawlers)
     ============================================================ -->
<section style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;">
    <h2>Frequently Asked Questions About <?php echo $siteName; ?></h2>
    <p>Find answers to common questions about using <?php echo $siteName; ?>, including how prices are compared across countries, how to find specific brands and products, how offers and discounts work, and how to get in touch with our support team. Our FAQ section is regularly updated to address the most common queries from our users.</p>
</section>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>