<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Get category slug from URL (?slug=pakistan set by .htaccess)
 $slug = getInput('slug', '');

if (empty($slug)) {
    // No slug provided, redirect to categories listing
    header('Location: ' . BASE_URL . '/categories');
    exit;
}

 $slug = clean($slug);

// Get site settings for SEO
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'FoodScope';

// Get category data from database for fast initial SEO render
 $db = getDB();
 $stmt = $db->prepare("SELECT id, name, slug, image, description FROM categories WHERE slug = ? AND status = 1 LIMIT 1");
 $stmt->execute([$slug]);
 $categoryMeta = $stmt->fetch();

if (!$categoryMeta) {
    // Category not found — show 404
    $pageTitle = pageTitle('Category Not Found', false);
    $pageDescription = 'The category you are looking for does not exist or has been removed.';
    $canonical = BASE_URL . '/categories';
    $schemaJson = '';

    require_once __DIR__ . '/includes/header.php';
    echo '<div class="error-page">';
    echo '<div class="error-code">404</div>';
    echo '<h2 class="error-title">Category Not Found</h2>';
    echo '<p class="error-desc">The category you\'re looking for doesn\'t exist or has been removed.</p>';
    echo '<a href="' . BASE_URL . '/categories" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.5rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;"><i class="fa-solid fa-arrow-left" style="font-size:0.75rem;"></i> Browse Categories</a>';
    echo '</div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

 $categoryName = $categoryMeta['name'];
 $pageTitle = pageTitle($categoryName . ' — Food Menu, Prices & Brands');
 $pageDescription = stripMeta($categoryMeta['description'] ?: ('Browse all products, brands, and prices in the ' . $categoryName . ' category on ' . $siteName . '. Compare prices and find the best deals near you.'));
 $canonical = BASE_URL . '/category/' . $slug;
 $ogImage = $categoryMeta['image'] ?? '';

// Schema.org JSON-LD placeholder (updated by JS from API)
 $schemaJson = '';

// Include header
require_once __DIR__ . '/includes/header.php';

// Inject category slug for categories.js (detail page mode)
?>
<script>window.CATEGORY_SLUG = '<?php echo $slug; ?>';</script>

<!-- ============================================================
     PAGE-LEVEL SKELETON
     ============================================================ -->
<div id="cat-page-skeleton">
    <section class="page-banner" style="min-height:200px;">
        <div class="container">
            <div style="max-width:600px;">
                <div class="skeleton skeleton-title" style="height:20px;width:45%;margin-bottom:0.75rem;background:rgba(255,255,255,0.1);border-radius:4px;"></div>
                <div class="skeleton skeleton-text" style="height:14px;width:55%;background:rgba(255,255,255,0.08);border-radius:4px;"></div>
            </div>
        </div>
    </section>
    <section class="section-padding">
        <div class="container">
            <!-- Stats bar skeleton -->
            <div style="display:flex;justify-content:center;gap:2.5rem;margin-bottom:2rem;flex-wrap:wrap;">
                <div style="text-align:center;">
                    <div class="skeleton" style="height:32px;width:80px;border-radius:var(--radius-sm);margin:0 auto 0.4rem;"></div>
                    <div class="skeleton skeleton-text short" style="margin:0 auto;width:60px;"></div>
                </div>
                <div style="text-align:center;">
                    <div class="skeleton" style="height:32px;width:80px;border-radius:var(--radius-sm);margin:0 auto 0.4rem;"></div>
                    <div class="skeleton skeleton-text short" style="margin:0 auto;width:60px;"></div>
                </div>
                <div style="text-align:center;">
                    <div class="skeleton" style="height:32px;width:80px;border-radius:var(--radius-sm);margin:0 auto 0.4rem;"></div>
                    <div class="skeleton skeleton-text short" style="margin:0 auto;width:60px;"></div>
                </div>
            </div>

            <!-- Brand pills skeleton -->
            <div style="display:flex;gap:0.5rem;justify-content:center;margin-bottom:1.5rem;flex-wrap:wrap;">
                <div class="skeleton" style="height:36px;width:80px;border-radius:var(--radius-full);"></div>
                <div class="skeleton" style="height:36px;width:100px;border-radius:var(--radius-full);"></div>
                <div class="skeleton" style="height:36px;width:90px;border-radius:var(--radius-full);"></div>
                <div class="skeleton" style="height:36px;width:70px;border-radius:var(--radius-full);"></div>
            </div>

            <!-- Products skeleton -->
            <div class="row g-3">
                <?php for ($i = 0; $i < 6; $i++): ?>
                <div class="col-6 col-md-4 col-lg-3"><div class="skeleton skeleton-card"></div></div>
                <?php endfor; ?>
            </div>
        </div>
    </section>
</div>

<!-- ============================================================
     MAIN CONTENT (Hidden until JS loads data)
     ============================================================ -->
<div id="cat-detail-content" style="display:none;">

    <!-- ===== CATEGORY HERO / HEADER ===== -->
    <section class="page-banner" id="category-hero-section" style="padding-bottom:2.5rem;">
        <div class="container">
            <div class="page-banner-content" style="max-width:700px;">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/categories">Categories</a></li>
                        <li class="breadcrumb-item active"><?php echo escapeHtml($categoryName); ?></li>
                    </ol>
                </nav>
                <h1 id="cat-name"><?php echo escapeHtml($categoryName); ?></h1>
                <p id="cat-description"><?php echo $categoryMeta['description'] ? $categoryMeta['description'] : 'Browse all products, brands, and prices in this category.'; ?></p>

                <!-- Category Stats -->
                <div style="display:flex;gap:2.5rem;justify-content:center;margin-top:1.5rem;flex-wrap:wrap;" data-aos="fade-up">
                    <div style="text-align:center;">
                        <div style="font-size:1.6rem;font-weight:900;color:var(--primary);" id="cat-stat-products">0</div>
                        <div style="font-size:0.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em;">Products</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:1.6rem;font-weight:900;color:var(--primary);" id="cat-stat-brands">0</div>
                        <div style="font-size:0.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em;">Brands</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== BRAND PILLS & PRODUCTS SECTION ===== -->
    <section class="section-padding" style="background:var(--bg-alt);padding-top:2rem;padding-bottom:3rem;">
        <div class="container">
            <!-- Brand Filter Pills -->
            <div id="cat-brand-pills" style="display:flex;gap:0.5rem;flex-wrap:wrap;justify-content:center;margin-bottom:1.5rem;" data-aos="fade-up">
                <!-- Populated by categories.js (detail mode) -->
            </div>

            <!-- Products Toolbar -->
            <div class="toolbar" id="cat-products-toolbar" data-aos="fade-up">
                <div class="toolbar-left">
                    <form id="cat-product-search-form" style="position:relative;flex:1;max-width:300px;">
                        <input type="text" id="cat-product-search" class="form-control" placeholder="Search products in this category..." style="padding-left:2.5rem;font-size:0.88rem;border-radius:var(--radius-full);">
                        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:0.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.85rem;pointer-events:none;"></i>
                    </form>
                </div>
                <div class="toolbar-right">
                    <span class="toolbar-count" id="cat-products-count">Loading products...</span>
                    <select id="cat-product-sort" class="toolbar-sort">
                        <option value="newest">Newest First</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="name_asc">Name A–Z</option>
                        <option value="name_desc">Name Z–A</option>
                        <option value="calories_low">Calories: Low to High</option>
                        <option value="calories_high">Calories: High to Low</option>
                    </select>
                </div>
            </div>

            <!-- Products Skeleton -->
            <div id="cat-products-skeleton">
                <div class="row g-3">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                    <div class="col-6 col-md-4 col-lg-3"><div class="skeleton skeleton-card"></div></div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Products Grid -->
            <div id="cat-products-grid" class="row g-3" style="display:none;">
                <!-- Populated by categories.js (detail mode) via AJAX -->
            </div>

            <!-- Pagination -->
            <div id="cat-products-pagination" style="display:none;">
                <!-- Populated by categories.js (detail mode) via AJAX -->
            </div>

            <!-- Empty State -->
            <div id="cat-products-empty" style="display:none;">
                <!-- Populated by categories.js when no products found -->
            </div>

        </div>
    </section>

</div>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>