<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Get site settings for SEO
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'MenuCrest';

// Page SEO
 $pageTitle = pageTitle('About Us');
 $pageDescription = 'Learn more about ' . $siteName . ' — our mission, what we do, and why thousands of food lovers trust us to compare menus, prices, and deals across the globe.';
 $canonical = BASE_URL . '/about';

// Schema.org JSON-LD for about page
 $aboutSchema = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'AboutPage',
    'name' => 'About Us — ' . $siteName,
    'description' => $pageDescription,
    'url' => $canonical,
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => BASE_URL . '/'
    ]
 ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

 $schemaJson = $aboutSchema;

// ============================================================
// Live stats (queried directly, same pattern as includes/footer.php)
// ============================================================
 $db = getDB();

 $totalBrands = (int) $db->query("SELECT COUNT(*) FROM brands WHERE status = 1")->fetchColumn();
 $totalProducts = (int) $db->query("SELECT COUNT(*) FROM products WHERE status = 1")->fetchColumn();
 $totalCategories = (int) $db->query("SELECT COUNT(*) FROM categories WHERE status = 1")->fetchColumn();
 $totalCountries = (int) $db->query("SELECT COUNT(*) FROM countries WHERE status = 1")->fetchColumn();

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
                    <li class="breadcrumb-item active">About</li>
                </ol>
            </nav>
            <h1>About <?php echo clean($siteName); ?></h1>
            <p>We help food lovers compare menus, prices, and deals from the world's most popular brands — across every country we cover.</p>
        </div>
    </div>
</section>

<!-- ============================================================
     OUR STORY SECTION
     ============================================================ -->
<section class="section-padding">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6" data-aos="fade-up">
                <div class="section-header" style="text-align:left;margin-bottom:1.5rem;">
                    <div class="section-label">Our Story</div>
                    <h2 class="section-title">Making Food Choices Simple</h2>
                </div>
                <p style="color:var(--text-secondary);line-height:1.8;margin-bottom:1rem;">
                    <?php echo clean($siteName); ?> started with a simple idea: comparing food prices and menus across brands and countries shouldn't be complicated. Whether you're craving a burger in one country or curious how the same brand's menu differs elsewhere, we bring it all together in one place.
                </p>
                <p style="color:var(--text-secondary);line-height:1.8;margin-bottom:0;">
                    Today, we track menus, pricing, nutrition information, and active offers from top food brands so you can make informed decisions before you order — no more guessing, no more surprises at checkout.
                </p>
            </div>
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                <div class="row g-4">
                    <div class="col-6">
                        <div class="wcu-card" style="text-align:center;">
                            <div class="wcu-icon" style="margin:0 auto 0.75rem;"><i class="fa-solid fa-building"></i></div>
                            <div style="font-size:2rem;font-weight:900;color:var(--primary);"><?php echo number_format($totalBrands); ?>+</div>
                            <div style="font-size:0.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;">Brands</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="wcu-card" style="text-align:center;">
                            <div class="wcu-icon" style="margin:0 auto 0.75rem;"><i class="fa-solid fa-utensils"></i></div>
                            <div style="font-size:2rem;font-weight:900;color:var(--primary);"><?php echo number_format($totalProducts); ?>+</div>
                            <div style="font-size:0.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;">Products</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="wcu-card" style="text-align:center;">
                            <div class="wcu-icon" style="margin:0 auto 0.75rem;"><i class="fa-solid fa-layer-group"></i></div>
                            <div style="font-size:2rem;font-weight:900;color:var(--primary);"><?php echo number_format($totalCategories); ?>+</div>
                            <div style="font-size:0.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;">Categories</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="wcu-card" style="text-align:center;">
                            <div class="wcu-icon" style="margin:0 auto 0.75rem;"><i class="fa-solid fa-earth-americas"></i></div>
                            <div style="font-size:2rem;font-weight:900;color:var(--primary);"><?php echo number_format($totalCountries); ?>+</div>
                            <div style="font-size:0.8rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;">Countries</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     MISSION / VALUES SECTION
     ============================================================ -->
<section class="section-padding" style="background:var(--bg-alt);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div class="section-label">What Drives Us</div>
            <h2 class="section-title">Our Mission &amp; Values</h2>
            <p class="section-desc">The principles that guide everything we build.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="50">
                <div class="wcu-card">
                    <div class="wcu-icon"><i class="fa-solid fa-bullseye"></i></div>
                    <h3 class="wcu-title">Accuracy First</h3>
                    <p class="wcu-desc">We work hard to keep prices, menus, and offers accurate and up to date across every country we cover.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="wcu-card">
                    <div class="wcu-icon"><i class="fa-solid fa-scale-balanced"></i></div>
                    <h3 class="wcu-title">Transparency</h3>
                    <p class="wcu-desc">No hidden agendas. We show you the real menu, real pricing, and real nutrition information — nothing sponsored, nothing hidden.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="150">
                <div class="wcu-card">
                    <div class="wcu-icon"><i class="fa-solid fa-users"></i></div>
                    <h3 class="wcu-title">Built for You</h3>
                    <p class="wcu-desc">Every feature we add starts with one question: does this make it easier for our users to find what they're craving?</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="wcu-card">
                    <div class="wcu-icon"><i class="fa-solid fa-arrows-rotate"></i></div>
                    <h3 class="wcu-title">Always Improving</h3>
                    <p class="wcu-desc">We're constantly adding new brands, countries, and features based on feedback from our community.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     CTA SECTION
     ============================================================ -->
<section class="section-padding">
    <div class="container">
        <div style="text-align:center;max-width:700px;margin:0 auto;padding:2.5rem 2rem;background:var(--bg-alt);border-radius:var(--radius-lg);border:1px solid var(--border-light);" data-aos="fade-up">
            <i class="fa-solid fa-comments" style="font-size:2rem;color:var(--primary);margin-bottom:1rem;display:block;"></i>
            <h3 style="font-family:var(--font-display);font-size:1.4rem;margin-bottom:0.5rem;">We'd Love to Hear From You</h3>
            <p style="color:var(--text-secondary);font-size:0.92rem;margin-bottom:1.5rem;">Got feedback, a brand suggestion, or just want to say hi? Reach out — we read every message.</p>
            <a href="<?php echo BASE_URL; ?>/contact" class="btn-primary-custom" style="display:inline-block;text-decoration:none;margin-right:0.75rem;">
                <i class="fa-solid fa-envelope" style="margin-right:0.5rem;"></i>Contact Us
            </a>
            <a href="<?php echo BASE_URL; ?>/faq" style="display:inline-block;padding:0.8rem 2rem;border-radius:var(--radius-md);border:1px solid var(--border);color:var(--text);font-weight:600;font-size:0.95rem;text-decoration:none;">
                Visit FAQ
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     SEO CONTENT SECTION (Hidden visually, readable by crawlers)
     ============================================================ -->
<section style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;">
    <h2>About <?php echo $siteName; ?></h2>
    <p><?php echo clean($siteName); ?> is a food menu and price comparison platform covering <?php echo $totalBrands; ?> brands, <?php echo $totalProducts; ?> products, and <?php echo $totalCountries; ?> countries. We help users compare pricing, nutrition, and offers from top food brands worldwide, making it easier to decide what to order and where to find the best value.</p>
</section>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>