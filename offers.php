<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Get site settings for SEO
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'FoodScope';

// Page SEO
 $pageTitle = pageTitle('Active Offers & Discounts');
 $pageDescription = 'Browse current food deals, discount coupons, and special promotions from top brands on ' . $siteName . '. Save money on your favorite food today.';
 $canonical = BASE_URL . '/offers';

// Schema.org JSON-LD for offers listing
 $offersSchema = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'Active Offers & Discounts — ' . $siteName,
    'description' => $pageDescription,
    'url' => $canonical,
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => BASE_URL . '/'
    ]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

 $schemaJson = $offersSchema;

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
                    <li class="breadcrumb-item active">Offers</li>
                </ol>
            </nav>
            <h1>Active Offers & Discounts</h1>
            <p>Find the best deals, discount coupons, and special promotions from top food brands. Save on your favorite meals today.</p>
        </div>
    </div>
</section>

<!-- ============================================================
     OFFERS LISTING SECTION
     ============================================================ -->
<section class="section-padding">
    <div class="container">
        <div class="row g-4">

            <!-- ===== FILTER SIDEBAR (Desktop) ===== -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="filter-panel" id="desktop-offer-filter-panel">

                    <!-- Search -->
                    <div class="filter-title">
                        <span><i class="fa-solid fa-magnifying-glass" style="margin-right:0.5rem;color:var(--primary);"></i>Search Offers</span>
                    </div>
                    <form id="offer-search-form" style="margin-bottom:1.25rem;">
                        <div style="position:relative;">
                            <input type="text" id="offer-search-input" class="form-control" placeholder="Search offers..." style="padding-left:2.5rem;font-size:0.88rem;">
                            <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:0.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.85rem;pointer-events:none;"></i>
                        </div>
                    </form>

                    <!-- Brands Filter -->
                    <div class="filter-group">
                        <div class="filter-group-title">Filter by Brand</div>
                        <div id="filter-brands">
                            <div style="padding:0.5rem 0;color:var(--muted);font-size:0.82rem;"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div>
                        </div>
                    </div>

                    <!-- Sort -->
                    <div class="filter-group">
                        <div class="filter-group-title">Sort By</div>
                        <select id="offer-sort-select" class="form-select" style="font-size:0.88rem;">
                            <option value="discount_high">Highest Discount</option>
                            <option value="discount_low">Lowest Discount</option>
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="ending_soon">Ending Soon</option>
                            <option value="brand_asc">Brand A–Z</option>
                        </select>
                    </div>

                    <!-- Show Expired Toggle -->
                    <div class="filter-group">
                        <div class="filter-group-title">Display Options</div>
                        <label class="filter-option" style="cursor:pointer;padding:0.5rem 0.75rem;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);transition:all 0.3s;margin-bottom:0.5rem;">
                            <input type="checkbox" id="offer-expired-toggle" style="width:16px;height:16px;accent-color:var(--primary);cursor:pointer;flex-shrink:0;">
                            <span>Show expired offers</span>
                        </label>
                        <div style="font-size:0.72rem;color:var(--muted);padding:0 0.75rem 0.5rem;">When enabled, past offers will also appear in the list.</div>
                    </div>

                    <!-- Action Buttons -->
                    <button id="btn-apply-offer-filters" class="filter-apply-btn">
                        <i class="fa-solid fa-check" style="margin-right:0.4rem;"></i> Apply Filters
                    </button>
                    <button id="btn-reset-offer-filters" class="filter-reset-btn">
                        <i class="fa-solid fa-rotate-left" style="margin-right:0.4rem;"></i> Reset All
                    </button>

                </div>
            </div>

            <!-- ===== MAIN CONTENT (Grid + Pagination) ===== -->
            <div class="col-lg-9">

                <!-- Toolbar -->
                <div class="toolbar" id="offers-toolbar" data-aos="fade-up">
                    <div class="toolbar-left">
                        <span class="toolbar-count" id="offers-count">Loading offers...</span>
                    </div>
                    <div class="toolbar-right">
                        <!-- Mobile filter button -->
                        <button class="toolbar-view-btn d-lg-none" id="btn-mobile-offer-filter" title="Filter offers">
                            <i class="fa-solid fa-sliders"></i>
                        </button>
                        <!-- Mobile sort -->
                        <select id="offer-sort-select-mobile" class="toolbar-sort d-lg-none" style="max-width:160px;">
                            <option value="discount_high">Highest Discount</option>
                            <option value="discount_low">Lowest Discount</option>
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="ending_soon">Ending Soon</option>
                            <option value="brand_asc">Brand A–Z</option>
                        </select>
                    </div>
                </div>

                <!-- Skeleton Loader -->
                <div id="offers-skeleton">
                    <div class="row g-3">
                        <?php for ($i = 0; $i < 12; $i++): ?>
                        <div class="col-6 col-md-4 col-lg-4"><div class="skeleton skeleton-card"></div></div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Offers Grid -->
                <div id="offers-grid" class="row g-3" style="display:none;">
                    <!-- Populated by offers.js via AJAX -->
                </div>

                <!-- Pagination -->
                <div id="offers-pagination" style="display:none;">
                    <!-- Populated by offers.js via AJAX -->
                </div>

                <!-- Empty State -->
                <div id="offers-empty" style="display:none;">
                    <!-- Populated by offers.js when no results found -->
                </div>

                <!-- Error State -->
                <div id="offers-error" style="display:none;">
                    <!-- Populated by offers.js on network error -->
                </div>

            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SEO CONTENT SECTION (Hidden visually, readable by crawlers)
     ============================================================ -->
<section style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;">
    <h2>Active Food Offers and Discounts on <?php echo $siteName; ?></h2>
    <p>Find the latest discount coupons and promotional deals from McDonald's, KFC, Burger King, Pizza Hut, and many more top food brands. Our offers page is updated daily with the most current promotions available in your selected country. Each offer includes the discount percentage, coupon code, validity dates, and a direct link to the eligible products.</p>
    <p>Browse offers by brand to find specific deals, or use our sort options to find the highest discounts or deals ending soon. Toggle "Show Expired" to see past promotions for reference. All offers are verified and linked to real products with accurate pricing information.</p>
    <p>Save money on your next meal by checking our offers page before you order. Whether it's a percentage off, a fixed coupon code, or a limited-time bundle deal, we help you get the best value for your money across all major fast food chains and restaurants.</p>
</section>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>