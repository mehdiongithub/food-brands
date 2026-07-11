<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Send a proper 404 HTTP status (must happen before any output)
http_response_code(404);

// Get site settings for SEO
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'FoodScope';

// Page SEO
 $pageTitle = pageTitle('Page Not Found');
 $pageDescription = 'The page you are looking for could not be found on ' . $siteName . '. Browse our brands, categories, and offers instead.';
 $canonical = BASE_URL . strtok($_SERVER['REQUEST_URI'], '?');
 $metaRobots = 'noindex, follow';

// ============================================================
// Helpful suggestions (real data, same pattern as includes/footer.php)
// ============================================================
 $db = getDB();

 $suggestedCategories = $db->query("
    SELECT name, slug, image
    FROM categories
    WHERE status = 1
    ORDER BY sort_order ASC, id ASC
    LIMIT 4
")->fetchAll();

 $suggestedBrands = $db->query("
    SELECT name, slug, logo
    FROM brands
    WHERE status = 1
    ORDER BY id ASC
    LIMIT 6
")->fetchAll();

// Include header
require_once __DIR__ . '/includes/header.php';
?>

<!-- ============================================================
     404 HERO
     ============================================================ -->
<section class="error-page">
    <div class="container">
        <div class="error-code">404</div>
        <h1 class="error-title">This Page Went Off the Menu</h1>
        <p class="error-desc">
            We couldn't find the page you were looking for. It may have been moved, renamed, or maybe it never existed in the first place. Let's get you back on track.
        </p>

        <!-- ===== SEARCH BOX ===== -->
        <form action="<?php echo BASE_URL; ?>/search" method="get" style="position:relative;max-width:480px;margin:0 auto 2rem;">
            <input type="text" name="q" class="form-control" placeholder="Search for brands, products, categories..." autocomplete="off" style="padding:0.9rem 3.25rem 0.9rem 2.75rem;font-size:0.95rem;border-radius:var(--radius-full);border:1px solid var(--border);">
            <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:1.1rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.9rem;pointer-events:none;"></i>
            <button type="submit" aria-label="Search" style="position:absolute;right:0.4rem;top:50%;transform:translateY(-50%);width:36px;height:36px;border-radius:50%;border:none;background:var(--primary);color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-arrow-right" style="font-size:0.85rem;"></i>
            </button>
        </form>

        <!-- ===== ACTION BUTTONS ===== -->
        <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
            <a href="<?php echo BASE_URL; ?>/" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.75rem 1.75rem;border-radius:var(--radius-full);background:var(--primary);color:#fff;font-weight:600;font-size:0.9rem;text-decoration:none;">
                <i class="fa-solid fa-house"></i> Back to Home
            </a>
            <button type="button" onclick="if (document.referrer && document.referrer.indexOf(window.location.host) !== -1) { history.back(); } else { window.location.href='<?php echo BASE_URL; ?>/'; }" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.75rem 1.75rem;border-radius:var(--radius-full);border:1px solid var(--border);background:var(--surface);color:var(--text);font-weight:600;font-size:0.9rem;cursor:pointer;">
                <i class="fa-solid fa-arrow-left"></i> Go Back
            </button>
        </div>
    </div>
</section>

<!-- ============================================================
     QUICK LINKS
     ============================================================ -->
<section class="section-padding" style="padding-top:0;background:var(--bg-alt);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div class="section-label">Where To Next?</div>
            <h2 class="section-title">Popular Destinations</h2>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="50">
                <a href="<?php echo BASE_URL; ?>/brands" style="text-decoration:none;">
                    <div class="wcu-card" style="text-align:center;">
                        <div class="wcu-icon" style="margin:0 auto 0.75rem;"><i class="fa-solid fa-building"></i></div>
                        <h3 class="wcu-title">All Brands</h3>
                        <p class="wcu-desc">Browse every food brand we cover.</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <a href="<?php echo BASE_URL; ?>/categories" style="text-decoration:none;">
                    <div class="wcu-card" style="text-align:center;">
                        <div class="wcu-icon" style="margin:0 auto 0.75rem;"><i class="fa-solid fa-layer-group"></i></div>
                        <h3 class="wcu-title">Categories</h3>
                        <p class="wcu-desc">Find food by type and cuisine.</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="150">
                <a href="<?php echo BASE_URL; ?>/offers" style="text-decoration:none;">
                    <div class="wcu-card" style="text-align:center;">
                        <div class="wcu-icon" style="margin:0 auto 0.75rem;"><i class="fa-solid fa-percent"></i></div>
                        <h3 class="wcu-title">Offers</h3>
                        <p class="wcu-desc">Check today's active deals.</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <a href="<?php echo BASE_URL; ?>/contact" style="text-decoration:none;">
                    <div class="wcu-card" style="text-align:center;">
                        <div class="wcu-icon" style="margin:0 auto 0.75rem;"><i class="fa-solid fa-envelope"></i></div>
                        <h3 class="wcu-title">Contact Us</h3>
                        <p class="wcu-desc">Report a broken link or ask us.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($suggestedCategories)): ?>
<!-- ============================================================
     BROWSE CATEGORIES
     ============================================================ -->
<section class="section-padding">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <div class="section-label">Or Try Browsing</div>
            <h2 class="section-title">Browse by Category</h2>
        </div>

        <div class="row g-4">
            <?php foreach ($suggestedCategories as $i => $cat): ?>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="<?php echo min($i * 60, 240); ?>">
                <a href="<?php echo BASE_URL; ?>/category/<?php echo clean($cat['slug']); ?>" class="category-card">
                    <?php if (!empty($cat['image'])): ?>
                    <img src="<?php echo asset_url($cat['image']); ?>" alt="<?php echo clean($cat['name']); ?>" loading="lazy">
                    <?php else: ?>
                    <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--secondary),#2D1B4E);display:flex;align-items:center;justify-content:center;">
                        <span style="font-family:var(--font-display);font-size:3rem;font-weight:900;color:rgba(255,255,255,0.15);"><?php echo clean(mb_substr($cat['name'], 0, 1)); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="cc-content">
                        <h3 class="cc-name"><?php echo clean($cat['name']); ?></h3>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($suggestedBrands)): ?>
<!-- ============================================================
     POPULAR BRANDS STRIP
     ============================================================ -->
<section class="section-padding" style="padding-top:0;">
    <div class="container">
        <div style="text-align:center;margin-bottom:1.5rem;" data-aos="fade-up">
            <span style="font-size:0.85rem;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Popular Brands</span>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:1rem;justify-content:center;align-items:center;" data-aos="fade-up" data-aos-delay="50">
            <?php foreach ($suggestedBrands as $brand): ?>
            <a href="<?php echo BASE_URL; ?>/brand/<?php echo clean($brand['slug']); ?>" title="<?php echo clean($brand['name']); ?>" style="width:64px;height:64px;border-radius:var(--radius-md);background:var(--surface);border:1px solid var(--border-light);display:flex;align-items:center;justify-content:center;padding:0.6rem;transition:all var(--transition);">
                <?php if (!empty($brand['logo'])): ?>
                <img src="<?php echo asset_url($brand['logo']); ?>" alt="<?php echo clean($brand['name']); ?>" style="max-width:100%;max-height:100%;object-fit:contain;" loading="lazy">
                <?php else: ?>
                <span style="font-family:var(--font-display);font-weight:900;color:var(--primary);font-size:1.1rem;"><?php echo clean(mb_substr($brand['name'], 0, 1)); ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>