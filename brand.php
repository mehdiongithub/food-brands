<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Get brand slug from URL (?slug=crsa set by .htaccess)
 $slug = getInput('slug', '');

if (empty($slug)) {
    // No slug provided, redirect to brands listing
    header('Location: ' . BASE_URL . '/brands');
    exit;
}

 $slug = clean($slug);

// Get site settings
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'FoodScope';

// Default SEO (will be overwritten by API data)
 $brandName = ucfirst(str_replace('-', ' ', $slug));
 $pageTitle = pageTitle($brandName . ' — Menu, Prices & Products');
 $pageDescription = 'Explore the full menu, prices, and products for ' . $brandName . ' on ' . $siteName . '. Compare prices across countries.';
 $canonical = BASE_URL . '/brand/' . $slug;
 $ogImage = '';

// Try to get brand meta from database for faster initial render
 $db = getDB();
 $stmt = $db->prepare("SELECT name, meta_title, meta_description, logo, cover_image FROM brands WHERE slug = ? AND status = 1 LIMIT 1");
 $stmt->execute([$slug]);
 $brandMeta = $stmt->fetch();

if ($brandMeta) {
    $brandName = $brandMeta['name'];
    $pageTitle = pageTitle($brandMeta['meta_title'] ?: ($brandName . ' — Menu, Prices & Products'));
    $pageDescription = stripMeta($brandMeta['meta_description'] ?: ($brandName . ' menu, prices, and products on ' . $siteName));
    $ogImage = $brandMeta['logo'] ?: $brandMeta['cover_image'] ?: '';
}

// Schema.org placeholder (will be updated by JS from API response)
 $schemaJson = '';

// Include header
require_once __DIR__ . '/includes/header.php';

// Inject brand slug for brand-detail.js
?>
<script>window.BRAND_SLUG = '<?php echo $slug; ?>';</script>

<!-- ============================================================
     PAGE-LEVEL SKELETON
     ============================================================ -->
<div id="brand-page-skeleton">
    <section class="page-banner" style="min-height:200px;">
        <div class="container">
            <div style="max-width:600px;">
                <div class="skeleton skeleton-title" style="height:20px;width:40%;margin-bottom:0.75rem;background:rgba(255,255,255,0.1);border-radius:4px;"></div>
                <div class="skeleton skeleton-text" style="height:14px;width:60%;background:rgba(255,255,255,0.08);border-radius:4px;"></div>
            </div>
        </div>
    </section>
    <section class="section-padding">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="skeleton" style="height:160px;border-radius:var(--radius-lg);margin-bottom:1.5rem;"></div>
                    <div class="skeleton" style="height:24px;width:50%;margin-bottom:1rem;"></div>
                    <div class="skeleton" style="height:16px;width:80%;margin-bottom:0.5rem;"></div>
                    <div class="skeleton" style="height:16px;width:65%;margin-bottom:1.5rem;"></div>
                    <div class="skeleton" style="height:16px;width:40%;margin-bottom:2rem;"></div>
                    <div class="row g-3">
                        <?php for ($i = 0; $i < 6; $i++): ?>
                        <div class="col-6 col-md-4"><div class="skeleton skeleton-card"></div></div>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="skeleton" style="height:300px;border-radius:var(--radius-lg);margin-bottom:1.5rem;"></div>
                    <div class="skeleton" style="height:200px;border-radius:var(--radius-lg);"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- ============================================================
     MAIN CONTENT (Hidden until JS loads data)
     ============================================================ -->
<div id="brand-detail-content" style="display:none;">

    <!-- ===== BRAND COVER & HEADER ===== -->
    <section class="page-banner" style="padding-bottom:0;min-height:auto;">
        <div style="position:relative;min-height:160px;overflow:hidden;border-radius:0 0 var(--radius-xl) var(--radius-xl);margin:-1px -1rem 0;">
            <img id="brand-cover-img" src="" alt="" style="width:100%;height:160px;object-fit:cover;display:block;">
            <div style="position:absolute;inset:0;background:linear-gradient(transparent 30%,rgba(27,27,47,0.85));"></div>
            <div style="position:absolute;bottom:0;left:0;right:0;padding:2rem;display:flex;align-items:flex-end;gap:1.25rem;z-index:2;">
                <div id="brand-logo-img" style="width:72px;height:72px;border-radius:var(--radius-md);background:var(--surface);padding:8px;box-shadow:var(--shadow-lg);flex-shrink:0;">
                    <img src="" alt="" style="width:100%;height:100%;object-fit:contain;">
                </div>
                <div style="flex:1;padding-bottom:0.5rem;">
                    <h1 id="brand-name" style="color:#fff;font-size:clamp(1.5rem,4vw,2.5rem);margin-bottom:0.25rem;"></h1>
                    <p id="brand-description" style="color:rgba(255,255,255,0.7);font-size:0.9rem;margin:0;max-width:600px;"></p>
                </div>
            </div>
        </div>
    </section>


    <!-- ===== GALLERY SECTION ===== -->
    <section class="brand-gallery-section section-padding" style="padding-top:2rem;padding-bottom:2rem;">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <div class="section-label">Gallery</div>
                <h2 class="section-title">Brand Gallery</h2>
            </div>
            <div id="brand-gallery-grid" class="row g-3">
                <!-- Populated by brand-detail.js: 3 images per row on desktop/tablet, 1 on mobile -->
            </div>
        </div>
    </section>
    
    <!-- ===== PRODUCTS SECTION (with filter sidebar) ===== -->
    <section class="section-padding" style="background:var(--bg-alt);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <div class="section-label">Menu</div>
                <h2 class="section-title">Products & Prices</h2>
            </div>

            <div class="row g-4">

                <!-- ===== FILTER SIDEBAR ===== -->
                <div class="col-lg-3">
                    <div class="filter-panel" id="brand-filter-panel">

                        <!-- Search -->
                        <div class="filter-title">
                            <span><i class="fa-solid fa-magnifying-glass" style="margin-right:0.5rem;color:var(--primary);"></i>Search Menu</span>
                        </div>
                        <form id="brand-product-search-form" style="margin-bottom:1.25rem;">
                            <div style="position:relative;">
                                <input type="text" id="brand-product-search" class="form-control" placeholder="Search products..." style="padding-left:2.5rem;font-size:0.88rem;">
                                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:0.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.85rem;pointer-events:none;"></i>
                            </div>
                        </form>

                        <!-- Categories Filter -->
                        <div class="filter-group">
                            <div class="filter-group-title">Categories</div>
                            <div id="brand-category-pills">
                                <!-- Populated by brand-detail.js -->
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="filter-group">
                            <div class="filter-group-title">Sort By</div>
                            <select id="brand-product-sort" class="form-select" style="font-size:0.88rem;">
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
                </div>

                <!-- ===== PRODUCTS GRID ===== -->
                <div class="col-lg-9">

                    <!-- Products Toolbar -->
                    <div class="toolbar" id="brand-products-toolbar" data-aos="fade-up">
                        <div class="toolbar-left">
                            <span class="toolbar-count" id="brand-products-count">Loading products...</span>
                        </div>
                    </div>

                    <!-- Products Skeleton -->
                    <div id="brand-products-skeleton">
                        <div class="row g-3">
                            <?php for ($i = 0; $i < 6; $i++): ?>
                            <div class="col-6 col-md-4"><div class="skeleton skeleton-card"></div></div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Products Grid (native CSS grid — see #brand-products-grid rule in style.css) -->
                    <div id="brand-products-grid" class="row" style="display:none;">
                        <!-- Populated by brand-detail.js via AJAX -->
                    </div>

                    <!-- Pagination -->
                    <div id="brand-products-pagination" style="display:none;">
                        <!-- Populated by brand-detail.js via AJAX -->
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- ===== OFFERS SECTION ===== -->
    <section class="brand-offers-section section-padding" style="padding-top:2rem;">
        <div class="container">
            <div style="max-width:900px;margin:0 auto;">
                <div class="section-header" data-aos="fade-up">
                    <div class="section-label">Deals</div>
                    <h2 class="section-title">Active Offers</h2>
                    <p class="section-desc">Current promotions and discount codes for this brand.</p>
                </div>

                <div id="brand-offers" class="row g-3">
                    <!-- Populated by brand-detail.js via AJAX -->
                </div>
            </div>
        </div>
    </section>

    <!-- ===== HISTORY SECTION ===== -->
    <section class="brand-history-section section-padding" style="background:var(--bg-alt);">
        <div class="container">
            <div style="max-width:800px;margin:0 auto;">
                <div class="section-header" data-aos="fade-up">
                    <div class="section-label">About</div>
                    <h2 class="section-title">Brand History</h2>
                </div>

                <div id="brand-history" class="static-content" data-aos="fade-up">
                    <!-- Populated by brand-detail.js via AJAX -->
                </div>
            </div>
        </div>
    </section>

</div>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>