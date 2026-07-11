<?php
// Load functions
require_once __DIR__ . '/includes/functions.php';

// Get blog slug from URL (?slug=some-post set by .htaccess: /blog/some-post)
 $slug = getInput('slug', '');

if (empty($slug)) {
    // No slug provided, redirect to blog listing
    header('Location: ' . BASE_URL . '/blog');
    exit;
}

 $slug = clean($slug);

// Get site settings
 $settings = getSettings();
 $siteName = $settings['site_name'] ?? 'FoodScope';

// Default SEO (will be overwritten by API data)
 $postName = ucwords(str_replace('-', ' ', $slug));
 $pageTitle = pageTitle($postName);
 $pageDescription = 'Read ' . $postName . ' on ' . $siteName . '.';
 $canonical = BASE_URL . '/blog/' . $slug;
 $ogImage = '';

// Try to get blog meta from database for faster initial render / accurate SEO tags
 $db = getDB();

 $stmt = $db->prepare("
    SELECT title, excerpt, content, image, meta_title, meta_description, category
    FROM blogs
    WHERE slug = ? AND status = 1 AND (published_at IS NULL OR published_at <= NOW())
    LIMIT 1
");
 $stmt->execute([$slug]);
 $blogMeta = $stmt->fetch();

if ($blogMeta) {
    $postName = $blogMeta['title'];
    $pageTitle = pageTitle($blogMeta['meta_title'] ?: $blogMeta['title']);
    $pageDescription = stripMeta($blogMeta['meta_description'] ?: ($blogMeta['excerpt'] ?: $blogMeta['content']));
    $ogImage = $blogMeta['image'] ?: '';
}

// Schema.org placeholder (will be updated by JS from API response)
 $schemaJson = '';

// Include header
require_once __DIR__ . '/includes/header.php';

// Inject blog slug for blog-detail.js
?>
<script>window.BLOG_SLUG = '<?php echo $slug; ?>';</script>

<!-- ============================================================
     PAGE-LEVEL SKELETON
     ============================================================ -->
<div id="blog-page-skeleton">
    <section class="page-banner" style="min-height:220px;">
        <div class="container">
            <div style="max-width:700px;">
                <div class="skeleton skeleton-title" style="height:16px;width:30%;margin-bottom:1rem;background:rgba(255,255,255,0.1);border-radius:4px;"></div>
                <div class="skeleton skeleton-title" style="height:28px;width:80%;margin-bottom:0.75rem;background:rgba(255,255,255,0.12);border-radius:4px;"></div>
                <div class="skeleton skeleton-text" style="height:14px;width:50%;background:rgba(255,255,255,0.08);border-radius:4px;"></div>
            </div>
        </div>
    </section>
    <section class="section-padding">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="skeleton" style="height:380px;border-radius:var(--radius-lg);margin-bottom:1.5rem;"></div>
                    <div class="skeleton" style="height:16px;width:95%;margin-bottom:0.6rem;"></div>
                    <div class="skeleton" style="height:16px;width:90%;margin-bottom:0.6rem;"></div>
                    <div class="skeleton" style="height:16px;width:85%;margin-bottom:0.6rem;"></div>
                    <div class="skeleton" style="height:16px;width:92%;margin-bottom:0.6rem;"></div>
                </div>
                <div class="col-lg-4">
                    <div class="skeleton" style="height:220px;border-radius:var(--radius-lg);"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- ============================================================
     MAIN CONTENT (Hidden until JS loads data)
     ============================================================ -->
<div id="blog-detail-content" style="display:none;">

    <!-- ===== ARTICLE HERO ===== -->
    <section class="page-banner" style="padding-bottom:2rem;">
        <div class="container">
            <div class="page-banner-content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/blog">Blog</a></li>
                        <li class="breadcrumb-item active" aria-current="page" id="bd-breadcrumb-title">Article</li>
                    </ol>
                </nav>
                <div id="bd-category-badge" style="margin-bottom:0.75rem;"></div>
                <h1 id="bd-title" style="color:#fff;"></h1>
                <div id="bd-meta-row" style="display:flex;flex-wrap:wrap;align-items:center;gap:1.25rem;margin-top:1rem;color:rgba(255,255,255,0.75);font-size:0.85rem;">
                    <!-- Populated by blog-detail.js -->
                </div>
            </div>
        </div>
    </section>

    <!-- ===== ARTICLE BODY + SIDEBAR ===== -->
    <section class="section-padding" style="padding-top:2rem;">
        <div class="container">
            <div class="row g-4">

                <!-- Article Column -->
                <div class="col-lg-8">
                    <!-- Featured Image -->
                    <div style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-md);margin-bottom:1.75rem;" data-aos="fade-up">
                        <img id="bd-image" src="" alt="" style="width:100%;max-height:440px;object-fit:cover;display:block;">
                    </div>

                    <!-- Article Content (rendered HTML from CMS) -->
                    <div id="bd-content" class="static-content" style="max-width:100%;margin:0;color:var(--text-secondary);" data-aos="fade-up" data-aos-delay="50">
                        <!-- Populated by blog-detail.js -->
                    </div>

                    <!-- Share Buttons -->
                    <div class="pd-share" id="bd-share-buttons" style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--border);">
                        <span style="font-size:0.82rem;font-weight:600;color:var(--text-secondary);margin-right:0.75rem;">Share this article:</span>
                        <button class="pd-share-btn pd-share-copy" title="Copy Link"><i class="fa-solid fa-link"></i></button>
                        <button class="pd-share-btn pd-share-whatsapp" title="Share on WhatsApp"><i class="fa-brands fa-whatsapp"></i></button>
                        <button class="pd-share-btn pd-share-facebook" title="Share on Facebook"><i class="fa-brands fa-facebook-f"></i></button>
                        <button class="pd-share-btn pd-share-twitter" title="Share on X"><i class="fa-brands fa-x-twitter"></i></button>
                        <button class="pd-share-btn pd-share-linkedin" title="Share on LinkedIn"><i class="fa-brands fa-linkedin-in"></i></button>
                        <button class="pd-share-btn pd-share-email" title="Share via Email"><i class="fa-solid fa-envelope"></i></button>
                    </div>

                    <!-- Author Box -->
                    <div id="bd-author-box" style="display:none;margin-top:1.75rem;padding:1.25rem;background:var(--bg-alt);border-radius:var(--radius-md);align-items:center;gap:1rem;">
                        <!-- Populated by blog-detail.js -->
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="filter-panel" style="position:sticky;top:90px;">

                        <!-- Search Widget -->
                        <div class="filter-title">
                            <span><i class="fa-solid fa-magnifying-glass" style="margin-right:0.5rem;color:var(--primary);"></i>Search Articles</span>
                        </div>
                        <form id="bd-search-form" style="margin-bottom:1.25rem;">
                            <div style="position:relative;">
                                <input type="text" id="bd-search-input" class="form-control" placeholder="Search articles..." style="padding-left:2.5rem;font-size:0.88rem;">
                                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:0.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.85rem;pointer-events:none;"></i>
                            </div>
                        </form>

                        <!-- Categories Widget -->
                        <div class="filter-group">
                            <div class="filter-group-title">Categories</div>
                            <div id="bd-categories-widget">
                                <div style="padding:0.5rem 0;color:var(--muted);font-size:0.82rem;"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div>
                            </div>
                        </div>

                        <!-- Related Posts Widget -->
                        <div class="filter-group" style="border-bottom:none;">
                            <div class="filter-group-title">Related Articles</div>
                            <div id="bd-related-widget">
                                <!-- Populated by blog-detail.js -->
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== MORE ARTICLES ===== -->
    <section class="section-padding" id="bd-more-section" style="background:var(--bg-alt);">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <div class="section-label">Keep Reading</div>
                <h2 class="section-title">More From The Blog</h2>
            </div>
            <div id="bd-more-grid" class="row g-3">
                <!-- Populated by blog-detail.js -->
            </div>
        </div>
    </section>

</div>

<?php
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>
