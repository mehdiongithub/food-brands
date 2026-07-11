<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Get site settings for SEO
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'FoodScope';

// Page SEO
 $pageTitle = pageTitle('All Food Brands');
 $pageDescription = 'Browse all food brands with menus, prices, and products available in your country. Find McDonald\'s, KFC, Burger King, and more on ' . $siteName . '.';
 $canonical = BASE_URL . '/brands';

// Schema.org JSON-LD for brand listing
 $brandsSchema = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'All Food Brands — ' . $siteName,
    'description' => $pageDescription,
    'url' => $canonical,
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => BASE_URL . '/'
    ]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

 $schemaJson = $brandsSchema;

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
                    <li class="breadcrumb-item active">Brands</li>
                </ol>
            </nav>
            <h1>All Food Brands</h1>
            <p>Explore menus, prices, and products from the world's most popular food brands available in your country.</p>
        </div>
    </div>
</section>

<!-- ============================================================
     BRANDS LISTING SECTION
     ============================================================ -->
<section class="section-padding">
    <div class="container">

        <!-- Toolbar -->
        <div class="toolbar" id="brands-toolbar" data-aos="fade-up">
            <div class="toolbar-left">
                <button class="filter-drawer-btn" id="btn-mobile-filter" title="Filter brands">
                    <i class="fa-solid fa-sliders"></i> Filters
                </button>
                <span class="toolbar-count" id="brands-count">Loading brands...</span>
            </div>
            <div class="toolbar-right">
                <select id="brand-sort-select" class="toolbar-sort">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name_asc">Name A–Z</option>
                    <option value="name_desc">Name Z–A</option>
                    <option value="products_high">Most Products</option>
                    <option value="products_low">Least Products</option>
                </select>
            </div>
        </div>

        <!-- Skeleton Loader -->
        <div id="brands-skeleton">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-3"><div class="skeleton skeleton-card"></div></div>
                <div class="col-6 col-md-4 col-lg-3"><div class="skeleton skeleton-card"></div></div>
                <div class="col-6 col-md-4 col-lg-3"><div class="skeleton skeleton-card"></div></div>
                <div class="col-6 col-md-4 col-lg-3"><div class="skeleton skeleton-card"></div></div>
                <div class="col-6 col-md-4 col-lg-3"><div class="skeleton skeleton-card"></div></div>
                <div class="col-6 col-md-4 col-lg-3"><div class="skeleton skeleton-card"></div></div>
                <div class="col-6 col-md-4 col-lg-3"><div class="skeleton skeleton-card"></div></div>
                <div class="col-6 col-md-4 col-lg-3"><div class="skeleton skeleton-card"></div></div>
            </div>
        </div>

        <!-- Brands Grid (native CSS grid — see #brands-grid rule in style.css) -->
        <div id="brands-grid" class="row" style="display:none;">
            <!-- Populated by brands.js via AJAX -->
        </div>

        <!-- Pagination -->
        <div id="brands-pagination" style="display:none;">
            <!-- Populated by brands.js via AJAX -->
        </div>

    </div>
</section>

<!-- ============================================================
     MOBILE FILTER PANEL (Slide-in)
     ============================================================ -->
<div id="mobile-filter-overlay" style="position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,0.5);display:none;"></div>
<div id="mobile-filter-panel" style="position:fixed;top:0;left:-320px;width:300px;height:100vh;z-index:9999;background:var(--surface);box-shadow:var(--shadow-xl);transition:left 0.3s ease;overflow-y:auto;padding:1.5rem;">
    <!-- Panel Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
        <span style="font-family:var(--font-display);font-size:1.15rem;font-weight:700;">Filters</span>
        <button id="btn-close-mobile-filter" style="width:32px;height:32px;border-radius:50%;border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Search -->
    <div style="margin-bottom:1.25rem;">
        <form id="brand-search-form">
            <div style="position:relative;">
                <input type="text" id="brand-search-input" class="form-control" placeholder="Search brands..." style="padding-left:2.5rem;font-size:0.88rem;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:0.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.85rem;pointer-events:none;"></i>
            </div>
        </form>
    </div>

    <!-- Categories -->
    <div class="filter-group">
        <div class="filter-group-title">Categories</div>
        <div id="filter-categories">
            <div style="padding:0.5rem 0;color:var(--muted);font-size:0.82rem;"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div style="margin-top:1.5rem;display:flex;flex-direction:column;gap:0.5rem;">
        <button id="btn-apply-filters" class="filter-apply-btn">
            <i class="fa-solid fa-check" style="margin-right:0.4rem;"></i> Apply Filters
        </button>
        <button id="btn-reset-filters" class="filter-reset-btn">
            <i class="fa-solid fa-rotate-left" style="margin-right:0.4rem;"></i> Reset All
        </button>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>