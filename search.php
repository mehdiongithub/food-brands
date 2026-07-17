<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Get site settings for SEO
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'MenuCrest';

// Read search query from URL (rendered server-side for SEO + no-JS fallback)
 $searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

// Page SEO
 $pageTitle = $searchQuery
    ? pageTitle('Search Results for "' . $searchQuery . '"')
    : pageTitle('Search');
 $pageDescription = $searchQuery
    ? 'Search results for "' . $searchQuery . '" on ' . $siteName . ' — brands, products, categories, and offers.'
    : 'Search ' . $siteName . ' for food brands, products, categories, and active offers across every country we cover.';
 $canonical = BASE_URL . '/search' . ($searchQuery ? '?q=' . urlencode($searchQuery) : '');

// Search results pages shouldn't be indexed individually (avoid thin/duplicate content)
 $metaRobots = $searchQuery ? 'noindex, follow' : 'index, follow';

// Schema.org JSON-LD for search page
 $searchSchema = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'SearchResultsPage',
    'name' => $searchQuery ? 'Search Results for "' . $searchQuery . '"' : 'Search — ' . $siteName,
    'description' => $pageDescription,
    'url' => $canonical,
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => BASE_URL . '/'
    ]
 ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

 $schemaJson = $searchSchema;

// Include header
require_once __DIR__ . '/includes/header.php';
?>

<!-- ============================================================
     PAGE BANNER (with live search box)
     ============================================================ -->
<section class="page-banner">
    <div class="container">
        <div class="page-banner-content">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/">Home</a></li>
                    <li class="breadcrumb-item active">Search</li>
                </ol>
            </nav>
            <h1>Search</h1>
            <p>Find brands, products, categories, and offers across <?php echo clean($siteName); ?>.</p>

            <form id="page-search-form" style="position:relative;max-width:560px;margin-top:1.5rem;">
                <input type="text" id="page-search-input" class="form-control" value="<?php echo clean($searchQuery); ?>" placeholder="Search for brands, products, categories..." style="padding:0.9rem 3.25rem 0.9rem 2.75rem;font-size:1rem;border-radius:var(--radius-full);border:none;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.95rem;pointer-events:none;"></i>
                <button type="submit" aria-label="Search" style="position:absolute;right:0.4rem;top:50%;transform:translateY(-50%);width:38px;height:38px;border-radius:50%;border:none;background:var(--primary);color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</section>

<!-- ============================================================
     SEARCH RESULTS SECTION
     ============================================================ -->
<section class="section-padding">
    <div class="container">

        <!-- ===== RESULT TABS + SORT TOOLBAR ===== -->
        <div id="search-toolbar-wrap" style="display:none;">
            <div class="toolbar" id="search-toolbar" data-aos="fade-up">
                <div class="toolbar-left" style="flex-wrap:wrap;gap:0.5rem;">
                    <div id="search-tabs" style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                        <button type="button" class="search-tab-btn active" data-type="all">All</button>
                        <button type="button" class="search-tab-btn" data-type="brands">Brands</button>
                        <button type="button" class="search-tab-btn" data-type="products">Products</button>
                        <button type="button" class="search-tab-btn" data-type="categories">Categories</button>
                        <button type="button" class="search-tab-btn" data-type="offers">Offers</button>
                    </div>
                </div>
                <div class="toolbar-right">
                    <span class="toolbar-count" id="search-count">Searching...</span>
                    <select id="search-sort-select" class="toolbar-sort">
                        <option value="relevance">Most Relevant</option>
                        <option value="newest">Newest First</option>
                        <option value="name_asc">Name A–Z</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ===== SKELETON LOADER ===== -->
        <div id="search-skeleton" style="display:none;">
            <div class="row g-4">
                <?php for ($i = 0; $i < 8; $i++): ?>
                <div class="col-6 col-md-4 col-lg-3"><div class="skeleton skeleton-card"></div></div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- ===== RESULTS CONTAINER (populated by search.js via AJAX) ===== -->
        <div id="search-results"></div>

        <!-- ===== PAGINATION (only shown for "products" tab, per API) ===== -->
        <div id="search-pagination" style="display:none;"></div>

        <!-- ===== NO QUERY / EMPTY STATE ===== -->
        <div id="search-empty" style="display:<?php echo $searchQuery ? 'none' : 'block'; ?>;">
            <div style="text-align:center;padding:4rem 2rem;">
                <i class="fa-solid fa-magnifying-glass" style="font-size:3rem;color:var(--muted);opacity:0.3;margin-bottom:1.5rem;display:block;"></i>
                <h3 style="font-family:var(--font-display);font-size:1.3rem;margin-bottom:0.5rem;">Start Searching</h3>
                <p style="color:var(--text-secondary);max-width:420px;margin:0 auto;font-size:0.9rem;">Type a brand, product, or category name above to see results from across <?php echo clean($siteName); ?>.</p>
            </div>
        </div>

        <!-- ===== ERROR STATE ===== -->
        <div id="search-error" style="display:none;"></div>

    </div>
</section>

<!-- ============================================================
     SEO CONTENT SECTION (Hidden visually, readable by crawlers)
     ============================================================ -->
<section style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;">
    <h2>Search <?php echo $siteName; ?></h2>
    <p>Use the search bar to find food brands, menu items, categories, and active offers across every country <?php echo clean($siteName); ?> covers. Results update instantly as you type, and full results pages let you filter by brand, product, category, or offer for a more focused view.</p>
</section>

<style>
.search-tab-btn {
    padding: 0.5rem 1.1rem;
    border-radius: var(--radius-full);
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--text-secondary);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition);
    white-space: nowrap;
}
.search-tab-btn:hover { border-color: var(--primary); color: var(--primary); }
.search-tab-btn.active { background: var(--primary); border-color: var(--primary); color: #fff; }
.search-result-group { margin-bottom: 3rem; }
.search-result-group:last-child { margin-bottom: 0; }
.search-result-group-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
.search-result-group-title { font-family: var(--font-display); font-size: 1.2rem; font-weight: 700; }
mark { background: rgba(232,93,4,0.18); color: inherit; padding: 0 0.1em; border-radius: 2px; }
</style>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>