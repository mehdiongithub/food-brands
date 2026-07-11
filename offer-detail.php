<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Get offer slug from URL (?slug=summer-sale set by .htaccess)
 $slug = getInput('slug', '');

if (empty($slug)) {
    // No slug provided, redirect to offers listing
    header('Location: ' . BASE_URL . '/offers');
    exit;
}

 $slug = clean($slug);

// Get site settings
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'FoodScope';

// Default SEO (will be overwritten by API data)
 $offerName = ucfirst(str_replace('-', ' ', $slug));
 $pageTitle = pageTitle($offerName . ' — Offer Details & Coupon');
 $pageDescription = 'Get the details on ' . $offerName . ' — discount, coupon code, validity dates and eligible products on ' . $siteName . '.';
 $canonical = BASE_URL . '/offers/' . $slug;
 $ogImage = '';

// Try to get offer + brand meta from database for faster initial render
 $db = getDB();

 $stmt = $db->prepare("
    SELECT o.title, o.description, o.discount_percent, o.image,
           b.name AS brand_name, b.logo AS brand_logo
    FROM offers o
    INNER JOIN brands b ON b.id = o.brand_id
    WHERE o.slug = ? AND o.status = 1 AND b.status = 1
    LIMIT 1
");
 $stmt->execute([$slug]);
 $offerMeta = $stmt->fetch();

if ($offerMeta) {
    $offerName = $offerMeta['title'];
    $brandName = $offerMeta['brand_name'];
    $pageTitle = pageTitle($offerName . ' — ' . $brandName . ' Offer');
    $pageDescription = stripMeta($offerMeta['description'] ?: ($offerMeta['discount_percent'] . '% off at ' . $brandName . ' — view the coupon code, validity dates and eligible products on ' . $siteName));
    $ogImage = $offerMeta['image'] ?: $offerMeta['brand_logo'] ?: '';
}

// Schema.org placeholder (will be updated by JS from API response)
 $schemaJson = '';

// Include header
require_once __DIR__ . '/includes/header.php';

// Inject offer slug for offer-detail.js
?>
<script>window.OFFER_SLUG = '<?php echo $slug; ?>';</script>

<!-- ============================================================
     PAGE-LEVEL SKELETON
     ============================================================ -->
<div id="offer-page-skeleton">
    <section class="page-banner" style="min-height:200px;">
        <div class="container">
            <div style="max-width:600px;">
                <div class="skeleton skeleton-title" style="height:20px;width:45%;margin-bottom:0.75rem;background:rgba(255,255,255,0.1);border-radius:4px;"></div>
                <div class="skeleton skeleton-text" style="height:14px;width:65%;background:rgba(255,255,255,0.08);border-radius:4px;"></div>
            </div>
        </div>
    </section>
    <section class="section-padding">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="skeleton" style="height:320px;border-radius:var(--radius-lg);margin-bottom:1rem;"></div>
                    <div class="skeleton" style="height:90px;border-radius:var(--radius-md);"></div>
                </div>
                <div class="col-lg-7">
                    <div class="skeleton" style="height:28px;width:40%;margin-bottom:1rem;"></div>
                    <div class="skeleton" style="height:16px;width:90%;margin-bottom:0.5rem;"></div>
                    <div class="skeleton" style="height:16px;width:75%;margin-bottom:1.5rem;"></div>
                    <div class="skeleton" style="height:70px;border-radius:var(--radius-md);margin-bottom:1.5rem;"></div>
                    <div class="skeleton" style="height:44px;width:60%;border-radius:var(--radius-full);"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- ============================================================
     MAIN CONTENT (Hidden until JS loads data)
     ============================================================ -->
<div id="offer-detail-content" style="display:none;">

    <!-- ===== OFFER HERO / BANNER ===== -->
    <section class="page-banner" style="padding-bottom:2rem;">
        <div class="container">
            <div class="page-banner-content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/offers">Offers</a></li>
                        <li class="breadcrumb-item active" aria-current="page" id="od-breadcrumb-title">Offer</li>
                    </ol>
                </nav>
                <div id="od-status-badge" style="margin-bottom:0.75rem;"></div>
                <h1 id="od-title" style="color:#fff;"></h1>
                <div id="od-brand-row" style="display:flex;align-items:center;gap:0.6rem;margin-top:0.75rem;">
                    <!-- Populated by offer-detail.js -->
                </div>
            </div>
        </div>
    </section>

    <!-- ===== OFFER IMAGE + INFO ===== -->
    <section class="section-padding" style="padding-top:2rem;padding-bottom:1.5rem;">
        <div class="container">
            <div class="row g-4">
                <!-- Offer Image + Coupon Box -->
                <div class="col-lg-5">
                    <div style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-md);background:var(--bg-alt);" data-aos="fade-up">
                        <img id="od-image" src="" alt="" style="width:100%;height:320px;object-fit:cover;display:block;">
                    </div>

                    <!-- Coupon Box -->
                    <div id="od-coupon-box" style="margin-top:1.25rem;padding:1.25rem;background:var(--surface);border:1px dashed var(--border);border-radius:var(--radius-md);text-align:center;display:none;" data-aos="fade-up" data-aos-delay="50">
                        <!-- Populated by offer-detail.js -->
                    </div>
                </div>

                <!-- Offer Info -->
                <div class="col-lg-7">
                    <div data-aos="fade-up" data-aos-delay="100">
                        <div class="oc-discount" id="od-discount" style="font-size:2.1rem;margin-bottom:0.85rem;"></div>

                        <div id="od-description" class="static-content" style="margin-bottom:1.5rem;color:var(--text-secondary);line-height:1.7;">
                            <!-- Populated by offer-detail.js -->
                        </div>

                        <!-- Validity / Countdown -->
                        <div id="od-validity" style="background:var(--bg-alt);border-radius:var(--radius-md);padding:1rem 1.25rem;margin-bottom:1.5rem;">
                            <!-- Populated by offer-detail.js -->
                        </div>

                        <!-- Brand Card -->
                        <div id="od-brand-card" style="display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;border:1px solid var(--border-light);border-radius:var(--radius-md);margin-bottom:1.5rem;">
                            <!-- Populated by offer-detail.js -->
                        </div>

                        <!-- Share Buttons -->
                        <div class="pd-share" id="od-share-buttons">
                            <span style="font-size:0.82rem;font-weight:600;color:var(--text-secondary);margin-right:0.75rem;">Share:</span>
                            <button class="pd-share-btn pd-share-copy" title="Copy Link"><i class="fa-solid fa-link"></i></button>
                            <button class="pd-share-btn pd-share-whatsapp" title="Share on WhatsApp"><i class="fa-brands fa-whatsapp"></i></button>
                            <button class="pd-share-btn pd-share-facebook" title="Share on Facebook"><i class="fa-brands fa-facebook-f"></i></button>
                            <button class="pd-share-btn pd-share-twitter" title="Share on X"><i class="fa-brands fa-x-twitter"></i></button>
                            <button class="pd-share-btn pd-share-linkedin" title="Share on LinkedIn"><i class="fa-brands fa-linkedin-in"></i></button>
                            <button class="pd-share-btn pd-share-email" title="Share via Email"><i class="fa-solid fa-envelope"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== COUNTRIES AVAILABLE ===== -->
    <section class="section-padding" id="od-countries-section" style="background:var(--bg-alt);padding-top:1.5rem;padding-bottom:1.5rem;">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <div class="section-label">Availability</div>
                <h2 class="section-title">Available In</h2>
            </div>
            <div id="od-countries" style="display:flex;gap:1.5rem;flex-wrap:wrap;justify-content:center;" data-aos="fade-up">
                <!-- Populated by offer-detail.js -->
            </div>
        </div>
    </section>

    <!-- ===== ELIGIBLE PRODUCTS ===== -->
    <section class="section-padding">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <div class="section-label">Eligible Menu</div>
                <h2 class="section-title">Products With This Offer</h2>
                <p class="section-desc">These products from the brand qualify for this discount in your selected country.</p>
            </div>

            <!-- Products Toolbar -->
            <div class="toolbar" id="od-products-toolbar" data-aos="fade-up">
                <div class="toolbar-left">
                    <form id="od-product-search-form" style="position:relative;flex:1;max-width:300px;">
                        <input type="text" id="od-product-search" class="form-control" placeholder="Search eligible products..." style="padding-left:2.5rem;font-size:0.88rem;border-radius:var(--radius-full);">
                        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:0.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.85rem;pointer-events:none;"></i>
                    </form>
                </div>
                <div class="toolbar-right">
                    <span class="toolbar-count" id="od-products-count">Loading products...</span>
                    <select id="od-product-sort" class="toolbar-sort">
                        <option value="newest">Newest First</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="name_asc">Name A–Z</option>
                        <option value="name_desc">Name Z–A</option>
                    </select>
                </div>
            </div>

            <!-- Products Skeleton -->
            <div id="od-products-skeleton">
                <div class="row g-3">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                    <div class="col-6 col-md-4"><div class="skeleton skeleton-card"></div></div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Products Grid -->
            <div id="od-products-grid" class="row g-3" style="display:none;">
                <!-- Populated by offer-detail.js via AJAX -->
            </div>

            <!-- Pagination -->
            <div id="od-products-pagination" style="display:none;">
                <!-- Populated by offer-detail.js via AJAX -->
            </div>
        </div>
    </section>

    <!-- ===== OTHER OFFERS FROM THIS BRAND ===== -->
    <section class="section-padding" id="od-other-offers-section" style="background:var(--bg-alt);">
        <div class="container">
            <div style="max-width:1000px;margin:0 auto;">
                <div class="section-header" data-aos="fade-up">
                    <div class="section-label">More Deals</div>
                    <h2 class="section-title">Other Offers From This Brand</h2>
                </div>
                <div id="od-other-offers" class="row g-3">
                    <!-- Populated by offer-detail.js -->
                </div>
            </div>
        </div>
    </section>

</div>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>