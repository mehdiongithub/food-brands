<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Get product slug from URL (?slug=pizza set by .htaccess)
 $slug = getInput('slug', '');

if (empty($slug)) {
    // No slug provided, redirect to brands listing
    header('Location: ' . BASE_URL . '/brands');
    exit;
}

 $slug = clean($slug);

// Get site settings
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'MenuCrest';

// Default SEO (will be overwritten by API data)
 $productName = ucfirst(str_replace('-', ' ', $slug));
 $pageTitle = pageTitle($productName . ' — Price, Nutrition & Details');
 $pageDescription = 'View ' . $productName . ' price, calories, nutrition facts, ingredients, and compare prices across countries on ' . $siteName . '.';
 $canonical = BASE_URL . '/product/' . $slug;
 $ogImage = '';

// Try to get product + brand meta from database for faster initial render
 $db = getDB();

 $stmt = $db->prepare("
    SELECT p.name, p.meta_title, p.meta_description, p.image,
           b.name AS brand_name, b.logo AS brand_logo
    FROM products p
    INNER JOIN brands b ON b.id = p.brand_id
    WHERE p.slug = ? AND p.status = 1 AND b.status = 1
    LIMIT 1
");
 $stmt->execute([$slug]);
 $productMeta = $stmt->fetch();

if ($productMeta) {
    $productName = $productMeta['name'];
    $brandName = $productMeta['brand_name'];
    $pageTitle = pageTitle($productMeta['meta_title'] ?: ($productName . ' — Price, Nutrition & Details'));
    $pageDescription = stripMeta($productMeta['meta_description'] ?: ($productName . ' by ' . $brandName . ' — view price, calories, nutrition facts, ingredients and compare prices across countries on ' . $siteName));
    $ogImage = $productMeta['image'] ?: $productMeta['brand_logo'] ?: '';
}

// Schema.org placeholder (will be updated by JS from API response)
 $schemaJson = '';

// Include header
require_once __DIR__ . '/includes/header.php';

// Inject product slug for product-detail.js
?>
<script>window.PRODUCT_SLUG = '<?php echo $slug; ?>';</script>

<!-- ============================================================
     PAGE-LEVEL SKELETON
     ============================================================ -->
<div id="product-page-skeleton">
    <section class="page-banner" style="min-height:200px;">
        <div class="container">
            <div style="max-width:600px;">
                <div class="skeleton skeleton-title" style="height:20px;width:50%;margin-bottom:0.75rem;background:rgba(255,255,255,0.1);border-radius:4px;"></div>
                <div class="skeleton skeleton-text" style="height:14px;width:70%;background:rgba(255,255,255,0.08);border-radius:4px;"></div>
            </div>
        </div>
    </section>
    <section class="section-padding">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="skeleton" style="height:420px;border-radius:var(--radius-lg);margin-bottom:1rem;"></div>
                    <div class="skeleton" style="height:72px;border-radius:var(--radius-sm);margin-bottom:1.5rem;"></div>
                    <div class="skeleton" style="height:28px;width:70%;margin-bottom:0.5rem;"></div>
                    <div class="skeleton" style="height:16px;width:50%;margin-bottom:0.5rem;"></div>
                    <div class="skeleton" style="height:16px;width:90%;margin-bottom:0.5rem;"></div>
                    <div class="skeleton" style="height:16px;width:75%;margin-bottom:1.5rem;"></div>
                    <div class="skeleton" style="height:80px;border-radius:var(--radius-md);margin-bottom:1.5rem;"></div>
                    <div class="row g-3">
                        <div class="col-6"><div class="skeleton skeleton-card" style="height:120px;"></div></div>
                        <div class="col-6"><div class="skeleton skeleton-card" style="height:120px;"></div></div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="skeleton" style="height:200px;border-radius:var(--radius-md);margin-bottom:1.5rem;"></div>
                    <div class="skeleton" style="height:250px;border-radius:var(--radius-md);margin-bottom:1.5rem;"></div>
                    <div class="skeleton" style="height:150px;border-radius:var(--radius-md);"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- ============================================================
     MAIN CONTENT (Hidden until JS loads data)
     ============================================================ -->
<div id="product-detail-content" style="display:none;">

    <!-- ===== PRODUCT GALLERY ===== -->
    <section class="section-padding" style="padding-bottom:1rem;">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-7 col-xl-6">
                    <div class="pd-gallery" data-aos="fade-up">
                        <!-- Main Image -->
                        <div class="pd-main-image">
                            <img src="" alt="" loading="eager">
                        </div>
                        <!-- Thumbnails -->
                        <div class="pd-thumbs">
                            <!-- Populated by product-detail.js -->
                        </div>
                    </div>
                </div>

                <!-- ===== PRODUCT INFO ===== -->
                <div class="col-lg-5 col-xl-6">
                    <div class="pd-info" data-aos="fade-up" data-aos-delay="100">
                        <!-- Brand Row -->
                        <div class="pd-brand-row" id="pd-brand-row">
                            <!-- Populated by product-detail.js -->
                        </div>

                        <!-- Product Name -->
                        <h1 class="pd-name" id="pd-name"></h1>

                        <!-- Category Tag -->
                        <span class="pd-category-tag" id="pd-category-tag"></span>

                        <!-- Description -->
                        <div class="pd-desc" id="pd-description" style="margin-top:1rem;">
                            <!-- Populated by product-detail.js -->
                        </div>

                        <!-- Pricing -->
                        <div class="pd-pricing" id="pd-pricing">
                            <!-- Populated by product-detail.js -->
                        </div>

                        <!-- Details Grid -->
                        <div class="pd-details-grid" id="pd-details-grid">
                            <!-- Populated by product-detail.js -->
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== NUTRITION & INGREDIENTS (Two Column) ===== -->
    <section class="section-padding" style="background:var(--bg-alt);padding-top:2rem;padding-bottom:2rem;">
        <div class="container">
            <div class="row g-4">
                <!-- Nutrition Table -->
                <div class="col-lg-6 pd-nutrition-section">
                    <div class="section-header" data-aos="fade-up" style="text-align:left;">
                        <div class="section-label" style="justify-content:flex-start;">Nutrition Facts</div>
                        <h2 class="section-title" style="text-align:left;">Nutritional Information</h2>
                    </div>
                    <div id="pd-nutrition" style="background:var(--surface);border-radius:var(--radius-md);padding:0.5rem;overflow:hidden;border:1px solid var(--border-light);" data-aos="fade-up" data-aos-delay="50">
                        <!-- Populated by product-detail.js -->
                    </div>
                </div>

                <!-- Ingredients -->
                <div class="col-lg-6 pd-ingredients-section">
                    <div class="section-header" data-aos="fade-up" style="text-align:left;">
                        <div class="section-label" style="justify-content:flex-start;">Ingredients</div>
                        <h2 class="section-title" style="text-align:left;">What's Inside</h2>
                    </div>
                    <div id="pd-ingredients" style="background:var(--surface);border-radius:var(--radius-md);padding:1.25rem;border:1px solid var(--border-light);min-height:200px;" data-aos="fade-up" data-aos-delay="100">
                        <!-- Populated by product-detail.js -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <?php echo renderAdUnit('product_detail'); // prints nothing unless configured & enabled in Admin -> Ads ?>
    </div>

    <!-- ===== ACTIVE OFFERS ===== -->
    <section class="pd-offers-section section-padding" style="padding-top:1rem;padding-bottom:2rem;background:var(--bg-alt);">
        <div class="container">
            <div style="max-width:900px;margin:0 auto;">
                <div class="section-header" data-aos="fade-up">
                    <div class="section-label">Deals</div>
                    <h2 class="section-title">Available Offers</h2>
                    <p class="section-desc">Current promotions that apply to this product from its brand.</p>
                </div>

                <div id="pd-offers" class="row g-3">
                    <!-- Populated by product-detail.js -->
                </div>
            </div>
        </div>
    </section>

    <!-- ===== RELATED PRODUCTS ===== -->
    <section class="pd-related-section section-padding" style="padding-top:2rem;">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <div class="section-label">You May Also Like</div>
                <h2 class="section-title">Related Products</h2>
                <p class="section-desc">Explore more items from the same brand or category.</p>
            </div>

            <div class="swiper" id="pd-related-products" style="padding-bottom:2.5rem;">
                <div class="swiper-wrapper">
                    <!-- Populated by product-detail.js -->
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </section>

</div>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>