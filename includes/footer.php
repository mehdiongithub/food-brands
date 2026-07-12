<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/..');
}
require_once BASE_PATH . '/includes/functions.php';

 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'FoodScope';
 $logo = asset_url($settings['logo'] ?? '');
 $copyright = $settings['copyright'] ?? '&copy; ' . date('Y') . ' ' . $siteName . '. All rights reserved.';
 $email = $settings['email'] ?? '';
 $phone = $settings['phone'] ?? '';
 $address = $settings['address'] ?? '';
 $facebook = $settings['facebook'] ?? '';
 $instagram = $settings['instagram'] ?? '';
 $twitter = $settings['twitter'] ?? '';
 $youtube = $settings['youtube'] ?? '';
 $linkedin = $settings['linkedin'] ?? '';

// Get some categories for footer links (limit 6)
 $db = getDB();
 $footerCategories = $db->query("
    SELECT name, slug 
    FROM categories 
    WHERE status = 1 
    ORDER BY sort_order ASC, id ASC 
    LIMIT 6
")->fetchAll();

// Get some brands for footer links (limit 6)
 $footerBrands = $db->query("
    SELECT name, slug 
    FROM brands 
    WHERE status = 1 
    ORDER BY id ASC 
    LIMIT 6
")->fetchAll();

// Detect current page for page-specific JS
 $currentFile = basename($_SERVER['PHP_SELF'], '.php');
 $pageJsMap = [
    'index' => 'home.js',
    'brands' => 'brands.js',
    'brand' => 'brand-detail.js',
    'product' => 'product-detail.js',
    'categories' => 'categories.js',
    'category' => 'category-detail.js',
    'offers' => 'offers.js',
    'offer-detail' => 'offer-detail.js',
    'faq' => 'faq.js',
    'contact' => 'contact.js',
    'about' => '',
    'search' => 'search.js',
    'blog' => 'blogs.js',
    'blog-detail' => 'blog-detail.js',
];
 $pageJsFile = $pageJsMap[$currentFile] ?? '';
?>
</main>

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content">
            <h2 class="newsletter-title">Stay Hungry, Stay Updated</h2>
            <p class="newsletter-desc">Get the latest food deals, new brand menus, and price drops delivered straight to your inbox. No spam, just food.</p>
            <form class="newsletter-form" id="newsletter-form">
                <input type="email" class="newsletter-input" id="newsletter-email" placeholder="Enter your email address" required>
                <button type="submit" class="newsletter-btn">
                    <i class="fa-solid fa-paper-plane" style="margin-right:0.4rem;"></i> Subscribe
                </button>
            </form>
            <div id="newsletter-msg" style="margin-top:0.75rem;font-size:0.85rem;color:rgba(255,255,255,0.7);display:none;"></div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer id="main-footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Column 1: About -->
            <div class="footer-col">
                <div class="footer-logo">
                    <?php if ($logo): ?>
                        <img src="<?php echo $logo; ?>" alt="<?php echo clean($siteName); ?>" style="height:32px;margin-bottom:0.5rem;">
                    <?php else: ?>
                        <?php echo str_replace('Scope', '<span>Scope</span>', $siteName); ?>
                    <?php endif; ?>
                </div>
                <p class="footer-about">
                    Your ultimate destination for comparing food menus, prices, and deals from the world's most popular food brands across different countries.
                </p>
                <?php if ($facebook || $instagram || $twitter || $youtube || $linkedin): ?>
                <div class="footer-social">
                    <?php if ($facebook): ?>
                    <a href="<?php echo clean($facebook); ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if ($instagram): ?>
                    <a href="<?php echo clean($instagram); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <?php endif; ?>
                    <?php if ($twitter): ?>
                    <a href="<?php echo clean($twitter); ?>" target="_blank" rel="noopener noreferrer" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                    <?php endif; ?>
                    <?php if ($youtube): ?>
                    <a href="<?php echo clean($youtube); ?>" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    <?php endif; ?>
                    <?php if ($linkedin): ?>
                    <a href="<?php echo clean($linkedin); ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Column 2: Quick Links -->
            <div class="footer-col">
                <h4 class="footer-title">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo BASE_URL; ?>/">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/brands">All Brands</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/categories">Categories</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/offers">Latest Offers</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/faq">FAQ</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/contact">Contact Us</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/about">About Us</a></li>
                </ul>
            </div>

            <!-- Column 3: Categories -->
            <div class="footer-col">
                <h4 class="footer-title">Categories</h4>
                <ul class="footer-links">
                    <?php if (!empty($footerCategories)): ?>
                        <?php foreach ($footerCategories as $cat): ?>
                        <li><a href="<?php echo BASE_URL; ?>/category/<?php echo clean($cat['slug']); ?>"><?php echo clean($cat['name']); ?></a></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>/categories">View All Categories</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Column 4: Top Brands -->
            <div class="footer-col">
                <h4 class="footer-title">Top Brands</h4>
                <ul class="footer-links">
                    <?php if (!empty($footerBrands)): ?>
                        <?php foreach ($footerBrands as $brand): ?>
                        <li><a href="<?php echo BASE_URL; ?>/brand/<?php echo clean($brand['slug']); ?>"><?php echo clean($brand['name']); ?></a></li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>/brands">View All Brands</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Column 5: Contact Info -->
            <div class="footer-col">
                <h4 class="footer-title">Contact Info</h4>
                <ul class="footer-links" style="list-style:none;">
                    <?php if ($address): ?>
                    <li style="display:flex;gap:0.75rem;margin-bottom:0.75rem;">
                        <i class="fa-solid fa-location-dot" style="color:var(--primary);margin-top:0.25rem;flex-shrink:0;"></i>
                        <span><?php echo nl2br(clean($address)); ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if ($email): ?>
                    <li style="display:flex;gap:0.75rem;margin-bottom:0.75rem;">
                        <i class="fa-solid fa-envelope" style="color:var(--primary);margin-top:0.25rem;flex-shrink:0;"></i>
                        <a href="mailto:<?php echo clean($email); ?>"><?php echo clean($email); ?></a>
                    </li>
                    <?php endif; ?>
                    <?php if ($phone): ?>
                    <li style="display:flex;gap:0.75rem;margin-bottom:0.75rem;">
                        <i class="fa-solid fa-phone" style="color:var(--primary);margin-top:0.25rem;flex-shrink:0;"></i>
                        <a href="tel:<?php echo clean($phone); ?>"><?php echo clean($phone); ?></a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div><?php echo $copyright; ?></div>
            <div class="footer-bottom-links">
                <a href="<?php echo BASE_URL; ?>/about">Privacy Policy</a>
                <a href="<?php echo BASE_URL; ?>/about">Terms of Service</a>
                <a href="<?php echo BASE_URL; ?>/sitemap.xml">Sitemap</a>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="back-to-top" aria-label="Back to top">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<!-- Common JS (loaded on every page) -->
<script src="<?php echo BASE_URL; ?>/assets/js/common.js?v=<?php echo @filemtime(BASE_PATH . '/assets/js/common.js') ?: time(); ?>"></script>

<?php if ($pageJsFile): ?>
<!-- Page-specific JS -->
<script src="<?php echo BASE_URL; ?>/assets/js/<?php echo $pageJsFile; ?>?v=<?php echo @filemtime(BASE_PATH . '/assets/js/' . $pageJsFile) ?: time(); ?>"></script>
<?php endif; ?>

<!-- Google Analytics (if set in settings) -->
<?php if (!empty($settings['google_analytics'])): ?>
<?php echo $settings['google_analytics']; ?>
<?php endif; ?>

<!-- Google Tag Manager (if set in settings) -->
<?php if (!empty($settings['google_tag_manager'])): ?>
<?php echo $settings['google_tag_manager']; ?>
<?php endif; ?>

</body>
</html>