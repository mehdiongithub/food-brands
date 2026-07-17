<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Get site settings for SEO
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'MenuCrest';
 $siteDescription = $settings['meta_description'] ?? 'Compare food menus, prices, and deals from world-famous brands across countries. Discover what\'s available near you.';
 $logo = $settings['logo'] ?? '';

// Page SEO
 $pageTitle = pageTitle('Global Menu & Price Comparison');
 $pageDescription = $siteDescription;
 $canonical = BASE_URL . '/';
 $ogImage = $logo;

// Schema.org JSON-LD for homepage
 $homepageSchema = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => $siteName,
    'url' => BASE_URL . '/',
    'description' => $siteDescription,
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => BASE_URL . '/search?q={search_term_string}',
        'query-input' => 'required name=search_term_string'
    ]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

 $schemaJson = $homepageSchema;

// Include header (outputs <!DOCTYPE html> through <main>)
require_once __DIR__ . '/includes/header.php';
?>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="hero-section">
    <div class="hero-bg"></div>
    <div class="hero-particles"></div>

    <div class="hero-content">
        <div class="hero-badge">
            <i class="fa-solid fa-globe"></i>
            Trusted Food Comparison Platform
        </div>

        <h1 class="hero-title">
            Compare <span class="highlight">Food Prices</span><br>
            Across the Globe
        </h1>

        <p class="hero-subtitle">
            Explore menus, compare prices, and find the best deals from world-famous food brands — all in one place.
        </p>

        <div class="hero-search-box">
            <form class="hero-search-box" action="<?php echo BASE_URL; ?>/search" method="GET">
                <div class="hero-search-row">
                    <input type="text" name="q" class="hero-search-input" placeholder="Search for brands, products, or categories..." autocomplete="off">
                    <button type="submit" class="hero-search-btn-main">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                    </button>
                </div>
            </form>
            <div class="hero-filters">
                <div class="hero-filter-dropdown" id="hero-filter-category" data-all-url="<?php echo BASE_URL; ?>/categories">
                    <button type="button" class="hero-filter-select hero-filter-toggle" aria-haspopup="true" aria-expanded="false">
                        <span class="hfd-label">All Categories</span>
                        <i class="fa-solid fa-chevron-down hfd-caret"></i>
                    </button>
                    <div class="hero-filter-panel" id="hero-filter-category-panel">
                        <a href="<?php echo BASE_URL; ?>/categories" class="hfd-item hfd-item-all">
                            <span class="hfd-item-logo"><i class="fa-solid fa-grip"></i></span>
                            <span class="hfd-item-name">All Categories</span>
                        </a>
                        <div class="hfd-list">
                            <div class="hfd-loading"><i class="fa-solid fa-spinner fa-spin"></i> Loading categories&hellip;</div>
                        </div>
                    </div>
                </div>
                <div class="hero-filter-dropdown" id="hero-filter-brand" data-all-url="<?php echo BASE_URL; ?>/brands">
                    <button type="button" class="hero-filter-select hero-filter-toggle" aria-haspopup="true" aria-expanded="false">
                        <span class="hfd-label">All Brands</span>
                        <i class="fa-solid fa-chevron-down hfd-caret"></i>
                    </button>
                    <div class="hero-filter-panel" id="hero-filter-brand-panel">
                        <a href="<?php echo BASE_URL; ?>/brands" class="hfd-item hfd-item-all">
                            <span class="hfd-item-logo"><i class="fa-solid fa-shop"></i></span>
                            <span class="hfd-item-name">All Brands</span>
                        </a>
                        <div class="hfd-list">
                            <div class="hfd-loading"><i class="fa-solid fa-spinner fa-spin"></i> Loading brands&hellip;</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="hero-popular">
            <span>Popular:</span>
            <span class="hero-popular-tag">Pizza</span>
            <span class="hero-popular-tag">Burger</span>
            <span class="hero-popular-tag">Chicken</span>
            <span class="hero-popular-tag">Coffee</span>
            <span class="hero-popular-tag">Fries</span>
        </div>
    </div>

    <div class="hero-scroll-indicator">
        <span style="font-size:0.75rem;">Scroll to explore</span>
        <i class="fa-solid fa-chevron-down"></i>
    </div>
</section>

<!-- ============================================================
     WHY CHOOSE US SECTION
     ============================================================ -->
<section class="section-padding" id="why-choose-us-section" data-brands="0" data-products="0" data-countries="0">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div class="section-label">Why Choose Us</div>
            <h2 class="section-title">Everything You Need in One Place</h2>
            <p class="section-desc">We make it easy to compare food options and find the best value for your money.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="50">
                <div class="wcu-card">
                    <div class="wcu-icon"><i class="fa-solid fa-building"></i></div>
                    <h3 class="wcu-title">All Top Brands</h3>
                    <p class="wcu-desc">Browse menus from McDonald's, KFC, Burger King, and hundreds more — all in one place.</p>
                    <div style="font-size:2rem;font-weight:900;color:var(--primary);margin-top:0.75rem;" data-counter="brands">0</div>
                    <div style="font-size:0.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em;">Brands Listed</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="wcu-card">
                    <div class="wcu-icon"><i class="fa-solid fa-utensils"></i></div>
                    <h3 class="wcu-title">Full Menu Details</h3>
                    <p class="wcu-desc">See complete product info including calories, nutrition, ingredients, and high-quality images.</p>
                    <div style="font-size:2rem;font-weight:900;color:var(--primary);margin-top:0.75rem;" data-counter="products">0</div>
                    <div style="font-size:0.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em;">Products Available</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="150">
                <div class="wcu-card">
                    <div class="wcu-icon"><i class="fa-solid fa-earth-americas"></i></div>
                    <h3 class="wcu-title">Multi-Country</h3>
                    <p class="wcu-desc">Compare how prices change across different countries and currencies in real-time.</p>
                    <div style="font-size:2rem;font-weight:900;color:var(--primary);margin-top:0.75rem;" data-counter="countries">0</div>
                    <div style="font-size:0.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em;">Countries Covered</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="wcu-card">
                    <div class="wcu-icon"><i class="fa-solid fa-percent"></i></div>
                    <h3 class="wcu-title">Best Deals</h3>
                    <p class="wcu-desc">Find active offers, discount coupons, and special deals updated daily.</p>
                    <div style="font-size:2rem;font-weight:900;color:var(--primary);margin-top:0.75rem;" data-counter="offers">0</div>
                    <div style="font-size:0.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em;">Active Offers</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     FEATURED BRANDS SECTION
     ============================================================ -->
<section class="featured-brand-section section-padding" style="background:var(--bg-alt);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div class="section-label">Featured Brands</div>
            <h2 class="section-title">Explore Popular Brands</h2>
            <p class="section-desc">Discover menus and prices from the world's most loved food brands available in your country.</p>
        </div>

        <div class="swiper" id="home-featured-brands" style="padding-bottom:3rem;">
            <div class="swiper-wrapper">
                <!-- Populated by home.js via AJAX -->
                <div class="swiper-slide"><div class="skeleton skeleton-card"></div></div>
                <div class="swiper-slide"><div class="skeleton skeleton-card"></div></div>
                <div class="swiper-slide"><div class="skeleton skeleton-card"></div></div>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>

        <div style="text-align:center;margin-top:2rem;" data-aos="fade-up">
            <a href="<?php echo BASE_URL; ?>/brands" class="fb-view-all" style="display:inline-flex;align-items:center;gap:0.5rem;">
                View All Brands <i class="fa-solid fa-arrow-right" style="font-size:0.75rem;"></i>
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     FEATURED PRODUCTS SECTION
     ============================================================ -->
<section class="section-padding" id="home-featured-products">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div class="section-label">Featured Products</div>
            <h2 class="section-title">What's on the Menu</h2>
            <p class="section-desc">Browse popular food items with pricing, nutrition info, and more from brands near you.</p>
        </div>

        <!-- Toolbar -->
        <div class="toolbar" id="products-toolbar" data-aos="fade-up">
            <div class="toolbar-left">
                <span class="toolbar-count" id="products-count">Loading products...</span>
            </div>
            <div class="toolbar-right">
                <div class="toolbar-view-btn active" data-view="grid" title="Grid view"><i class="fa-solid fa-grid-2"></i></div>
                <div class="toolbar-view-btn" data-view="list" title="List view"><i class="fa-solid fa-list"></i></div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="row g-3" id="products-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;">
            <!-- Populated by home.js via AJAX -->
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
        </div>
    </div>
</section>

<?php echo renderAdUnit('home_middle'); // prints nothing unless configured & enabled in Admin -> Ads ?>

<!-- ============================================================
     CATEGORIES SECTION
     ============================================================ -->
<section class="section-padding" style="background:var(--bg-alt);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div class="section-label">Browse by Category</div>
            <h2 class="section-title">Food Categories</h2>
            <p class="section-desc">Explore different food categories to find exactly what you're craving.</p>
        </div>

        <div class="row g-4" id="home-categories-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem;">
            <!-- Populated by home.js via AJAX -->
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
        </div>

        <div style="text-align:center;margin-top:2.5rem;" data-aos="fade-up">
            <a href="<?php echo BASE_URL; ?>/categories" class="fb-view-all" style="display:inline-flex;align-items:center;gap:0.5rem;">
                View All Categories <i class="fa-solid fa-arrow-right" style="font-size:0.75rem;"></i>
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     OFFERS SECTION
     ============================================================ -->
<section class="section-padding" id="home-offers-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div class="section-label">Hot Deals</div>
            <h2 class="section-title">Active Offers & Discounts</h2>
            <p class="section-desc">Don't miss out on these limited-time deals from top food brands.</p>
        </div>

        <div class="swiper" id="home-offers-swiper" style="padding-bottom:3rem;">
            <div class="swiper-wrapper">
                <!-- Populated by home.js via AJAX -->
                <div class="swiper-slide"><div class="skeleton skeleton-card"></div></div>
                <div class="swiper-slide"><div class="skeleton skeleton-card"></div></div>
                <div class="swiper-slide"><div class="skeleton skeleton-card"></div></div>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>

        <div style="text-align:center;margin-top:2rem;" data-aos="fade-up">
            <a href="<?php echo BASE_URL; ?>/offers" class="fb-view-all" style="display:inline-flex;align-items:center;gap:0.5rem;">
                View All Offers <i class="fa-solid fa-arrow-right" style="font-size:0.75rem;"></i>
            </a>
        </div>
    </div>
</section>

<?php echo renderAdUnit('home_bottom'); // prints nothing unless configured & enabled in Admin -> Ads ?>

<!-- ============================================================
     TESTIMONIALS SECTION
     ============================================================ -->
<section class="section-padding" style="background:var(--bg-alt);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div class="section-label">What People Say</div>
            <h2 class="section-title">Customer Reviews</h2>
            <p class="section-desc">See what food lovers are saying about their experience using our platform.</p>
            <div style="display:flex;align-items:center;justify-content:center;gap:0.5rem;margin-top:0.75rem;">
                <span id="testimonials-avg-rating" style="font-size:1.5rem;font-weight:800;color:var(--primary-light);">0</span>
                <span style="color:var(--primary-light);font-size:0.9rem;"><i class="fa-solid fa-star"></i></span>
                <span id="testimonials-total-count" style="font-size:0.85rem;color:var(--muted);">0 Reviews</span>
            </div>
        </div>

        <div class="swiper" id="home-testimonials-swiper" style="padding-bottom:3rem;">
            <div class="swiper-wrapper">
                <!-- Populated by home.js via AJAX -->
                <div class="swiper-slide"><div class="skeleton skeleton-card"></div></div>
                <div class="swiper-slide"><div class="skeleton skeleton-card"></div></div>
                <div class="swiper-slide"><div class="skeleton skeleton-card"></div></div>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>

<!-- ============================================================
     FAQ SECTION
     ============================================================ -->
<section class="section-padding" id="home-faq-section">
    <div class="container">
        <div style="max-width:800px;margin:0 auto;">
            <div class="section-header" data-aos="fade-up">
                <div class="section-label">Got Questions?</div>
                <h2 class="section-title">Frequently Asked Questions</h2>
                <p class="section-desc">Find quick answers to the most common questions about our platform.</p>
            </div>

            <div id="home-faqs">
                <!-- Populated by home.js via AJAX -->
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text short"></div>
                <div class="skeleton skeleton-text"></div>
                <div class="skeleton skeleton-text short"></div>
            </div>

            <div style="text-align:center;margin-top:2rem;" data-aos="fade-up">
                <a href="<?php echo BASE_URL; ?>/faq" class="fb-view-all" style="display:inline-flex;align-items:center;gap:0.5rem;">
                    View All FAQs <i class="fa-solid fa-arrow-right" style="font-size:0.75rem;"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer (closes <main>, outputs newsletter, footer, scripts)
require_once __DIR__ . '/includes/footer.php';
?>