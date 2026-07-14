<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/..');
}
require_once BASE_PATH . '/includes/functions.php';

 $settings = getSettings();
 $country = getCurrentCountry();
 $currentFile = basename($_SERVER['PHP_SELF'], '.php');

// Set active nav state based on current file
if ($currentFile === 'brand') {
    $navActive = 'brands';
} elseif ($currentFile === 'product') {
    $navActive = 'brands';
} elseif ($currentFile === 'category') {
    $navActive = 'categories';
} else {
    $navActive = $currentFile;
}

 $siteName = $settings['site_name'] ?? 'FoodScope';
 $logo = asset_url($settings['logo'] ?? '');
 $favicon = asset_url($settings['favicon'] ?? '');
 $pageTitle = $pageTitle ?? $siteName;
 $pageDescription = $pageDescription ?? ($settings['meta_description'] ?? 'Compare food menus, prices, and deals from world-famous brands across countries.');
 $canonical = $canonical ?? BASE_URL . strtok($_SERVER['REQUEST_URI'], '?');
 $ogImage = $ogImage ?? ($settings['logo'] ?? '');
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo clean($pageTitle); ?></title>
<meta name="description" content="<?php echo clean($pageDescription); ?>">
<meta name="keywords" content="food menu, price comparison, food brands, global food prices, <?php echo clean($pageTitle); ?>">
<?php if (isset($metaRobots)): ?>
<meta name="robots" content="<?php echo clean($metaRobots); ?>">
<?php endif; ?>
<meta property="og:title" content="<?php echo clean($pageTitle); ?>">
<meta property="og:description" content="<?php echo clean($pageDescription); ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo clean($canonical); ?>">
<?php if ($ogImage): ?>
<meta property="og:image" content="<?php echo asset_url($ogImage); ?>">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo clean($pageTitle); ?>">
<meta name="twitter:description" content="<?php echo clean($pageDescription); ?>">
<link rel="canonical" href="<?php echo clean($canonical); ?>">
<?php if ($favicon): ?>
<link rel="icon" type="image/webp" href="<?php echo $favicon; ?>">
<?php endif; ?>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=<?php echo @filemtime(BASE_PATH . '/assets/css/style.css') ?: time(); ?>">
<?php if (isset($schemaJson)): ?>
<script type="application/ld+json"><?php echo $schemaJson; ?></script>
<?php endif; ?>
<?php
// Google AdSense — loads the ONE script Google requires per page, and only
// when an admin has actually turned ads on and saved a Publisher ID
// (Admin → Ads). If ads are off, nothing is printed here at all.
$__adSettings = getSettings();
if (!empty($__adSettings['adsense_enabled']) && !empty($__adSettings['adsense_client'])):
    $__adClient = htmlspecialchars($__adSettings['adsense_client'], ENT_QUOTES, 'UTF-8');
?>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?php echo $__adClient; ?>" crossorigin="anonymous"></script>
<?php endif; ?>
</head>
<body>

<!-- Base URL for all JS (common.js, search.js, etc.) — must be set
     BEFORE those scripts load, so it lives here at the very top of body -->
<script>window.BASE_URL = <?php echo json_encode(BASE_URL); ?>;</script>

<!-- Preloader -->
<div id="preloader">
    <?php if ($logo): ?>
        <img src="<?php echo $logo; ?>" alt="<?php echo clean($siteName); ?>" style="height:50px;margin-bottom:2rem;">
    <?php else: ?>
        <div class="preloader-logo"><?php echo str_replace('Scope', '<span>Scope</span>', $siteName); ?></div>
    <?php endif; ?>
    <div class="preloader-bar"><div class="preloader-bar-inner"></div></div>
    <div class="preloader-dots"><span></span><span></span><span></span></div>
</div>



<!-- Header -->
<header id="main-header">
    <div class="header-inner">
        <!-- Logo -->
        <a href="<?php echo BASE_URL; ?>/" class="header-logo">
            <?php if ($logo): ?>
                <img src="<?php echo $logo; ?>" alt="<?php echo clean($siteName); ?>" style="height:36px;">
            <?php else: ?>
                <?php echo str_replace('Scope', '<span>Scope</span>', $siteName); ?>
            <?php endif; ?>
        </a>

        <!-- Main Navigation -->
        <nav class="header-nav" id="main-nav">
            <a href="<?php echo BASE_URL; ?>/" class="<?php echo $navActive === 'index' ? 'active' : ''; ?>">Home</a>
            <a href="<?php echo BASE_URL; ?>/brands" class="<?php echo $navActive === 'brands' ? 'active' : ''; ?>">Brands</a>
            <a href="<?php echo BASE_URL; ?>/categories" class="<?php echo $navActive === 'categories' ? 'active' : ''; ?>">Categories</a>
            <a href="<?php echo BASE_URL; ?>/offers" class="<?php echo $navActive === 'offers' ? 'active' : ''; ?>">Offers</a>
            <a href="<?php echo BASE_URL; ?>/faq" class="<?php echo $navActive === 'faq' ? 'active' : ''; ?>">FAQ</a>
            <a href="<?php echo BASE_URL; ?>/contact" class="<?php echo $navActive === 'contact' ? 'active' : ''; ?>">Contact</a>
            <a href="<?php echo BASE_URL; ?>/blog" class="<?php echo $navActive === 'blog' ? 'active' : ''; ?>">Blog</a>
            <a href="<?php echo BASE_URL; ?>/about" class="<?php echo $navActive === 'about' ? 'active' : ''; ?>">About</a>
        </nav>

        <!-- Header Actions -->
        <div class="header-actions">
            <!-- Country Display (read-only — auto-detected from visitor IP, not switchable) -->
            <div class="country-selector" id="country-selector" style="cursor: default;">
                <img class="flag" src="<?php echo asset_url($country['flag']); ?>" alt="<?php echo clean($country['name']); ?>" style="width:22px;height:16px;object-fit:cover;border-radius:2px;">
                <span class="country-label" style="font-size:0.82rem;"><?php echo clean($country['name']); ?></span>
            </div>

            <!-- Search Button -->
            <button class="header-search-btn" id="header-search-btn" aria-label="Search">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>

            <!-- Theme Toggle -->
            <button class="theme-toggle" id="theme-toggle" aria-label="Toggle theme">
                <i class="fa-solid fa-moon"></i>
            </button>

            <!-- Mobile Menu Button -->
            <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>
</header>

<!-- Search Overlay -->
<div id="search-overlay">
    <div class="search-container">
        <div class="search-input-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="search-input" placeholder="Search brands, products, categories..." autocomplete="off">
            <button class="search-close-btn" id="search-close-btn" aria-label="Close search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="search-suggestions" id="search-suggestions">
            <div class="search-empty">Start typing to search...</div>
        </div>
    </div>
</div>

<script>
// Vanilla-JS fallback for the search modal — guarantees the modal opens
// even if jQuery is slow to load, fails to load, or common.js hasn't
// initialized yet. Safe to run alongside the jQuery-based handlers in
// common.js since it only ever ADDS the "active" class (idempotent).
(function () {
    function bindSearchFallback() {
        var btn = document.getElementById('header-search-btn');
        var overlay = document.getElementById('search-overlay');
        var input = document.getElementById('search-input');
        var closeBtn = document.getElementById('search-close-btn');
        if (!btn || !overlay) return;

        btn.addEventListener('click', function () {
            overlay.classList.add('active');
            setTimeout(function () { if (input) input.focus(); }, 150);
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                overlay.classList.remove('active');
            });
        }

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                overlay.classList.remove('active');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                overlay.classList.remove('active');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindSearchFallback);
    } else {
        bindSearchFallback();
    }
})();
</script>

<!-- Mobile Nav Overlay -->
<div id="mobile-nav-overlay" style="position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.5);display:none;"></div>
<div id="mobile-nav-panel" style="position:fixed;top:0;right:-300px;width:280px;height:100vh;z-index:1000;background:var(--surface);box-shadow:var(--shadow-xl);transition:right 0.3s ease;overflow-y:auto;padding:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;">
        <span style="font-family:var(--font-display);font-size:1.2rem;font-weight:700;">Menu</span>
        <button id="mobile-nav-close" style="width:32px;height:32px;border-radius:50%;border:1px solid var(--border);background:var(--surface);color:var(--text-secondary);cursor:pointer;display:flex;align-items:center;justify-content:center;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <nav id="mobile-nav-links" style="display:flex;flex-direction:column;gap:0.25rem;">
        <a href="<?php echo BASE_URL; ?>/" class="mobile-nav-link <?php echo $navActive === 'index' ? 'active' : ''; ?>" style="padding:0.75rem 1rem;border-radius:var(--radius-sm);color:var(--text-secondary);font-weight:500;transition:all 0.3s;">Home</a>
        <a href="<?php echo BASE_URL; ?>/brands" class="mobile-nav-link <?php echo $navActive === 'brands' ? 'active' : ''; ?>" style="padding:0.75rem 1rem;border-radius:var(--radius-sm);color:var(--text-secondary);font-weight:500;transition:all 0.3s;">Brands</a>
        <a href="<?php echo BASE_URL; ?>/categories" class="mobile-nav-link <?php echo $navActive === 'categories' ? 'active' : ''; ?>" style="padding:0.75rem 1rem;border-radius:var(--radius-sm);color:var(--text-secondary);font-weight:500;transition:all 0.3s;">Categories</a>
        <a href="<?php echo BASE_URL; ?>/offers" class="mobile-nav-link <?php echo $navActive === 'offers' ? 'active' : ''; ?>" style="padding:0.75rem 1rem;border-radius:var(--radius-sm);color:var(--text-secondary);font-weight:500;transition:all 0.3s;">Offers</a>
        <a href="<?php echo BASE_URL; ?>/about" class="mobile-nav-link <?php echo $navActive === 'faq' ? 'active' : ''; ?>" style="padding:0.75rem 1rem;border-radius:var(--radius-sm);color:var(--text-secondary);font-weight:500;transition:all 0.3s;">FAQ</a>
        <a href="<?php echo BASE_URL; ?>/contact" class="mobile-nav-link <?php echo $navActive === 'contact' ? 'active' : ''; ?>" style="padding:0.75rem 1rem;border-radius:var(--radius-sm);color:var(--text-secondary);font-weight:500;transition:all 0.3s;">Contact</a>
        <a href="<?php echo BASE_URL; ?>/blog" class="mobile-nav-link <?php echo $navActive === 'blog' ? 'active' : ''; ?>" style="padding:0.75rem 1rem;border-radius:var(--radius-sm);color:var(--text-secondary);font-weight:500;transition:all 0.3s;">Blog</a>
        <a href="<?php echo BASE_URL; ?>/about" class="mobile-nav-link <?php echo $navActive === 'about' ? 'active' : ''; ?>" style="padding:0.75rem 1rem;border-radius:var(--radius-sm);color:var(--text-secondary);font-weight:500;transition:all 0.3s;">About</a>
    </nav>
</div>

<!-- Quick View Modal -->
<div class="qv-modal-overlay" id="qv-modal-overlay">
    <div class="qv-modal" style="position:relative;" id="qv-modal-content">
        <button class="qv-close" id="qv-close-btn"><i class="fa-solid fa-xmark"></i></button>
        <div id="qv-modal-body" style="padding:2rem;">
            <!-- Populated by AJAX -->
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toast-container"></div>

<!-- Main Content Area -->
<main id="main-content"></main>