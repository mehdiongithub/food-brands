<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Get site settings for SEO
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'MenuCrest';

// Page SEO
 $pageTitle = pageTitle('Food Categories — Browse by Type');
 $pageDescription = 'Browse all food categories including Pakistan, USA, and more. Find products by category on ' . $siteName . '. Compare prices and explore menus across different food types.';
 $canonical = BASE_URL . '/categories';

// Schema.org JSON-LD for categories listing
 $categoriesSchema = json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'Food Categories — ' . $siteName,
    'description' => $pageDescription,
    'url' => $canonical,
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => BASE_URL . '/'
    ]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

 $schemaJson = $categoriesSchema;

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
                    <li class="breadcrumb-item active">Categories</li>
                </ol>
            </nav>
            <h1>Food Categories</h1>
            <p>Browse all food categories to find exactly what you're craving. Each category shows available brands, products, and pricing for your country.</p>
        </div>
    </div>
</section>

<!-- ============================================================
     CATEGORIES LISTING SECTION
     ============================================================ -->
<section class="section-padding">
    <div class="container">

        <!-- ===== TOOLBAR ===== -->
        <div class="toolbar" id="categories-toolbar" data-aos="fade-up">
            <div class="toolbar-left">
                <form id="category-search-form" style="position:relative;flex:1;max-width:350px;">
                    <input type="text" id="category-search-input" class="form-control" placeholder="Search categories..." style="padding-left:2.5rem;font-size:0.88rem;border-radius:var(--radius-full);">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:0.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.85rem;pointer-events:none;"></i>
                </form>
            </div>
            <div class="toolbar-right">
                <span class="toolbar-count" id="categories-count">Loading categories...</span>
                <select id="category-sort-select" class="toolbar-sort">
                    <option value="sort_order">Default Order</option>
                    <option value="name_asc">Name A–Z</option>
                    <option value="name_desc">Name Z–A</option>
                    <option value="products_high">Most Products</option>
                    <option value="products_low">Least Products</option>
                    <option value="newest">Newest First</option>
                </select>
            </div>
        </div>

        <!-- ===== SKELETON LOADER ===== -->
        <div id="categories-skeleton">
            <div class="row g-4">
                <?php for ($i = 0; $i < 12; $i++): ?>
                <div class="col-6 col-md-4 col-lg-3"><div class="skeleton skeleton-card"></div></div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- ===== CATEGORIES GRID ===== -->
        <div id="categories-grid" class="row g-4" style="display:none;">
            <!-- Populated by categories.js via AJAX -->
        </div>

        <!-- ===== PAGINATION ===== -->
        <div id="categories-pagination" style="display:none;">
            <!-- Populated by categories.js via AJAX -->
        </div>

        <!-- ===== EMPTY STATE (hidden by default, shown by JS when no results) ===== -->
        <div id="categories-empty" style="display:none;">
            <!-- Populated by categories.js when no results found -->
        </div>

        <!-- ===== ERROR STATE (hidden by default, shown by JS on failure) ===== -->
        <div id="categories-error" style="display:none;">
            <!-- Populated by categories.js on network error -->
        </div>

    </div>
</section>

<!-- ============================================================
     SEO CONTENT SECTION (Hidden visually, readable by crawlers)
     ============================================================ -->
<section style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;">
    <h2>All Food Categories on <?php echo $siteName; ?></h2>
    <p>Explore our complete collection of food categories. From Pakistani cuisine to American fast food, find detailed menus with pricing, nutrition information, and ingredients for every product. Each category page lists all available brands and their products with real-time prices for your selected country. Whether you're looking for burgers, pizza, chicken, desserts, or beverages — browse by category to find exactly what you need.</p>
    <p>Our categories are dynamically updated as new brands and products are added. Switch countries to see how availability and pricing changes across regions. Use the search and sort features to quickly find the food category you're interested in.</p>
</section>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>