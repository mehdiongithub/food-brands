<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Get site settings for SEO
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'MenuCrest';

// Page SEO
 $pageTitle = pageTitle('Blog — Food News, Guides & Reviews');
 $pageDescription = 'Read the latest food news, brand guides, menu reviews, and price comparisons from ' . $siteName . '. Stay updated on everything happening in the food world.';
 $canonical = BASE_URL . '/blog';

// Schema.org JSON-LD for blog listing
 $blogSchema = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Blog',
    'name' => 'Blog — ' . $siteName,
    'description' => $pageDescription,
    'url' => $canonical,
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => BASE_URL . '/'
    ]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

 $schemaJson = $blogSchema;

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
                    <li class="breadcrumb-item active">Blog</li>
                </ol>
            </nav>
            <h1>Food News, Guides & Reviews</h1>
            <p>Menu deep-dives, brand stories, price comparisons and everything else happening across the food world.</p>
        </div>
    </div>
</section>

<!-- ============================================================
     BLOG LISTING SECTION
     ============================================================ -->
<section class="section-padding">
    <div class="container">
        <div class="row g-4">

            <!-- ===== FILTER SIDEBAR (Desktop) ===== -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="filter-panel" id="desktop-blog-filter-panel">

                    <!-- Search -->
                    <div class="filter-title">
                        <span><i class="fa-solid fa-magnifying-glass" style="margin-right:0.5rem;color:var(--primary);"></i>Search Articles</span>
                    </div>
                    <form id="blog-search-form" style="margin-bottom:1.25rem;">
                        <div style="position:relative;">
                            <input type="text" id="blog-search-input" class="form-control" placeholder="Search articles..." style="padding-left:2.5rem;font-size:0.88rem;">
                            <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:0.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.85rem;pointer-events:none;"></i>
                        </div>
                    </form>

                    <!-- Categories Filter -->
                    <div class="filter-group">
                        <div class="filter-group-title">Categories</div>
                        <div id="filter-blog-categories">
                            <div style="padding:0.5rem 0;color:var(--muted);font-size:0.82rem;"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div>
                        </div>
                    </div>

                    <!-- Sort -->
                    <div class="filter-group">
                        <div class="filter-group-title">Sort By</div>
                        <select id="blog-sort-select" class="form-select" style="font-size:0.88rem;">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="popular">Most Popular</option>
                            <option value="title_asc">Title A–Z</option>
                            <option value="title_desc">Title Z–A</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <button id="btn-apply-blog-filters" class="filter-apply-btn">
                        <i class="fa-solid fa-check" style="margin-right:0.4rem;"></i> Apply Filters
                    </button>
                    <button id="btn-reset-blog-filters" class="filter-reset-btn">
                        <i class="fa-solid fa-rotate-left" style="margin-right:0.4rem;"></i> Reset All
                    </button>

                </div>
            </div>

            <!-- ===== MAIN CONTENT (Grid + Pagination) ===== -->
            <div class="col-lg-9">

                <!-- Toolbar -->
                <div class="toolbar" id="blog-toolbar" data-aos="fade-up">
                    <div class="toolbar-left">
                        <span class="toolbar-count" id="blog-count">Loading articles...</span>
                    </div>
                    <div class="toolbar-right">
                        <!-- Mobile filter button -->
                        <button class="toolbar-view-btn d-lg-none" id="btn-mobile-blog-filter" title="Filter articles">
                            <i class="fa-solid fa-sliders"></i>
                        </button>
                        <!-- Mobile sort -->
                        <select id="blog-sort-select-mobile" class="toolbar-sort d-lg-none" style="max-width:160px;">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="popular">Most Popular</option>
                            <option value="title_asc">Title A–Z</option>
                            <option value="title_desc">Title Z–A</option>
                        </select>
                    </div>
                </div>

                <!-- Skeleton Loader -->
                <div id="blog-skeleton">
                    <div class="row g-3">
                        <?php for ($i = 0; $i < 9; $i++): ?>
                        <div class="col-6 col-md-4"><div class="skeleton skeleton-card"></div></div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Blog Grid -->
                <div id="blog-grid" class="row g-3" style="display:none;">
                    <!-- Populated by blogs.js via AJAX -->
                </div>

                <!-- Pagination -->
                <div id="blog-pagination" style="display:none;">
                    <!-- Populated by blogs.js via AJAX -->
                </div>

            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     MOBILE FILTER PANEL (Slide-in)
     ============================================================ -->
<div id="mobile-blog-filter-overlay" style="position:fixed;inset:0;z-index:9998;background:rgba(0,0,0,0.5);display:none;"></div>
<div id="mobile-blog-filter-panel" style="position:fixed;top:0;left:-320px;width:300px;height:100vh;z-index:9999;background:var(--surface);box-shadow:var(--shadow-xl);transition:left 0.3s ease;overflow-y:auto;padding:1.5rem;">
    <!-- Panel Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
        <span style="font-family:var(--font-display);font-size:1.15rem;font-weight:700;">Filters</span>
        <button id="btn-close-mobile-blog-filter" style="width:32px;height:32px;border-radius:50%;border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Mobile Search -->
    <div style="margin-bottom:1.25rem;">
        <form id="blog-search-form-mobile">
            <div style="position:relative;">
                <input type="text" class="form-control mobile-blog-search-sync" placeholder="Search articles..." style="padding-left:2.5rem;font-size:0.88rem;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:0.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.85rem;pointer-events:none;"></i>
            </div>
        </form>
    </div>

    <!-- Mobile Categories -->
    <div class="filter-group">
        <div class="filter-group-title">Categories</div>
        <div id="filter-blog-categories-mobile">
            <!-- Cloned from desktop by blogs.js -->
        </div>
    </div>

    <!-- Mobile Sort -->
    <div class="filter-group">
        <div class="filter-group-title">Sort By</div>
        <select id="blog-sort-select-mobile-panel" class="form-select" style="font-size:0.88rem;">
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="popular">Most Popular</option>
            <option value="title_asc">Title A–Z</option>
            <option value="title_desc">Title Z–A</option>
        </select>
    </div>

    <!-- Mobile Action Buttons -->
    <div style="margin-top:1.5rem;display:flex;flex-direction:column;gap:0.5rem;">
        <button id="btn-apply-blog-filters-mobile" class="filter-apply-btn">
            <i class="fa-solid fa-check" style="margin-right:0.4rem;"></i> Apply Filters
        </button>
        <button id="btn-reset-blog-filters-mobile" class="filter-reset-btn">
            <i class="fa-solid fa-rotate-left" style="margin-right:0.4rem;"></i> Reset All
        </button>
    </div>
</div>

<!-- ============================================================
     SEO CONTENT SECTION (Hidden visually, readable by crawlers)
     ============================================================ -->
<section style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;">
    <h2>Latest Food Industry Articles on <?php echo $siteName; ?></h2>
    <p>Our blog covers the latest food news, brand comparisons, menu reviews, price trend analysis and practical guides to help you get the best value from your favorite food brands. Every article is written to help readers make informed choices about where to eat and what to order.</p>
    <p>Browse articles by category or search for a specific topic to find guides on brand promotions, seasonal menu items, nutritional comparisons, and money-saving tips across all the major fast food and restaurant chains we track.</p>
</section>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>
