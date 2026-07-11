<?php
// Load config and functions (functions.php also loads database-config.php internally)
require_once __DIR__ . '/../../includes/functions.php';

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Shared "published & live" condition for every public query
// status = 1 (published) AND (no publish date set OR publish date has passed)
const BLOG_LIVE_SQL = " b.status = 1 AND (b.published_at IS NULL OR b.published_at <= NOW()) ";

/**
 * Shape a raw DB row into the public JSON structure used by both
 * the listing cards and the detail page.
 */
function formatBlogRow($r, $excerptLen = 160) {
    $excerpt = $r['excerpt'];
    if (empty($excerpt) && !empty($r['content'])) {
        $excerpt = stripMeta($r['content'], $excerptLen);
    } elseif (!empty($excerpt) && mb_strlen($excerpt) > $excerptLen) {
        $excerpt = mb_substr($excerpt, 0, $excerptLen - 3) . '...';
    }

    return [
        'id'          => (int) $r['id'],
        'title'       => $r['title'],
        'slug'        => $r['slug'],
        'excerpt'     => $excerpt,
        'category'    => $r['category'] ?: 'General',
        'image'       => asset_url($r['image']),
        'views'       => (int) ($r['views'] ?? 0),
        'author_name' => $r['author_name'] ?? null,
        'author_image'=> !empty($r['author_image']) ? asset_url($r['author_image']) : null,
        'published_at'      => $r['published_at'],
        'published_at_human'=> $r['published_at'] ? date('M d, Y', strtotime($r['published_at'])) : date('M d, Y', strtotime($r['created_at'])),
        'read_time'   => estimateReadTime($r['content'] ?? ''),
        'url'         => BASE_URL . '/blog/' . $r['slug']
    ];
}

/**
 * Rough "X min read" estimate based on word count (~200 wpm)
 */
function estimateReadTime($html) {
    $text = trim(strip_tags((string) $html));
    if ($text === '') return 1;
    $words = str_word_count($text);
    $minutes = (int) ceil($words / 200);
    return max(1, $minutes);
}

try {
    $db = getDB();
    $action = getInput('action', 'list'); // list, detail, categories, home-featured

    // ============================================================
    // ACTION: Category list with post counts (for filter sidebar)
    // ============================================================
    if ($action === 'categories') {
        $stmt = $db->query("
            SELECT b.category AS name, COUNT(*) AS post_count
            FROM blogs b
            WHERE " . BLOG_LIVE_SQL . " AND b.category IS NOT NULL AND b.category <> ''
            GROUP BY b.category
            ORDER BY b.category ASC
        ");
        $rows = $stmt->fetchAll();

        $categories = [];
        foreach ($rows as $row) {
            $categories[] = [
                'name'       => $row['name'],
                'slug'       => strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($row['name']))),
                'post_count' => (int) $row['post_count']
            ];
        }

        jsonResponse(['success' => true, 'categories' => $categories]);
    }

    // ============================================================
    // ACTION: Home featured (latest N posts, for homepage widget)
    // ============================================================
    if ($action === 'home-featured') {
        $limit = (int) getInput('limit', 3);
        if ($limit > 12) $limit = 12;
        if ($limit < 1) $limit = 3;

        $stmt = $db->prepare("
            SELECT b.id, b.title, b.slug, b.image, b.excerpt, b.content, b.category, b.views,
                   b.published_at, b.created_at,
                   u.name AS author_name, u.image AS author_image
            FROM blogs b
            LEFT JOIN users u ON u.id = b.author_id
            WHERE " . BLOG_LIVE_SQL . "
            ORDER BY COALESCE(b.published_at, b.created_at) DESC, b.id DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $rows = $stmt->fetchAll();

        $blogs = [];
        foreach ($rows as $r) {
            $blogs[] = formatBlogRow($r, 120);
        }

        jsonResponse(['success' => true, 'blogs' => $blogs, 'total' => count($blogs)]);
    }

    // ============================================================
    // ACTION: Blog detail (single post by slug) + related posts
    // ============================================================
    if ($action === 'detail') {
        $slug = getInput('slug');

        if (empty($slug)) {
            jsonResponse(['success' => false, 'message' => 'Blog slug is required'], 400);
        }

        $stmt = $db->prepare("
            SELECT b.*, u.name AS author_name, u.image AS author_image
            FROM blogs b
            LEFT JOIN users u ON u.id = b.author_id
            WHERE b.slug = ? AND " . BLOG_LIVE_SQL . "
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $blog = $stmt->fetch();

        if (!$blog) {
            jsonResponse(['success' => false, 'message' => 'Blog post not found'], 404);
        }

        $blogId = (int) $blog['id'];

        // Bump the view counter (best-effort, ignore failure)
        try {
            $upd = $db->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?");
            $upd->execute([$blogId]);
            $blog['views'] = (int) $blog['views'] + 1;
        } catch (PDOException $e) {
            // non-fatal
        }

        $formatted = formatBlogRow($blog, 200);
        $formatted['content']           = $blog['content'];
        $formatted['meta_title']        = $blog['meta_title'] ?: $blog['title'];
        $formatted['meta_description']  = $blog['meta_description'] ?: stripMeta($blog['content'] ?? '', 160);

        // Related posts — same category first, then fill with latest others
        $related = [];
        $stmt = $db->prepare("
            SELECT b.id, b.title, b.slug, b.image, b.excerpt, b.content, b.category, b.views,
                   b.published_at, b.created_at,
                   u.name AS author_name, u.image AS author_image
            FROM blogs b
            LEFT JOIN users u ON u.id = b.author_id
            WHERE " . BLOG_LIVE_SQL . " AND b.id != ? AND b.category = ?
            ORDER BY COALESCE(b.published_at, b.created_at) DESC
            LIMIT 3
        ");
        $stmt->execute([$blogId, $blog['category']]);
        foreach ($stmt->fetchAll() as $r) {
            $related[] = formatBlogRow($r, 100);
        }

        if (count($related) < 3) {
            $need = 3 - count($related);
            $excludeIds = array_merge([$blogId], array_map(function ($x) { return $x['id']; }, $related));
            $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));

            $stmt = $db->prepare("
                SELECT b.id, b.title, b.slug, b.image, b.excerpt, b.content, b.category, b.views,
                       b.published_at, b.created_at,
                       u.name AS author_name, u.image AS author_image
                FROM blogs b
                LEFT JOIN users u ON u.id = b.author_id
                WHERE " . BLOG_LIVE_SQL . " AND b.id NOT IN ($placeholders)
                ORDER BY COALESCE(b.published_at, b.created_at) DESC
                LIMIT $need
            ");
            $stmt->execute($excludeIds);
            foreach ($stmt->fetchAll() as $r) {
                $related[] = formatBlogRow($r, 100);
            }
        }

        // Schema.org JSON-LD for the article
        $settings = getSettings();
        $siteName = $settings['site_name'] ?? 'FoodScope';
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'BlogPosting',
            'headline' => $blog['title'],
            'description' => $formatted['meta_description'],
            'image'    => $formatted['image'],
            'datePublished' => $blog['published_at'] ?: $blog['created_at'],
            'dateModified'  => $blog['updated_at'] ?: $blog['created_at'],
            'author' => [
                '@type' => 'Person',
                'name'  => $blog['author_name'] ?: $siteName
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name'  => $siteName
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id'   => BASE_URL . '/blog/' . $blog['slug']
            ]
        ];

        jsonResponse([
            'success'     => true,
            'blog'        => $formatted,
            'related'     => $related,
            'schema_json' => json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        ]);
    }

    // ============================================================
    // ACTION: List (default) — search, category filter, sort, pagination
    // ============================================================
    $page     = max(1, (int) getInput('page', 1));
    $per_page = max(1, min(50, (int) getInput('per_page', 9)));
    $offset   = ($page - 1) * $per_page;

    $search = trim((string) getInput('search', ''));
    $searchSql = '';
    $searchParams = [];
    if ($search !== '') {
        $searchSql = " AND (b.title LIKE ? OR b.excerpt LIKE ? OR b.content LIKE ?) ";
        $searchParams[] = '%' . $search . '%';
        $searchParams[] = '%' . $search . '%';
        $searchParams[] = '%' . $search . '%';
    }

    $category = trim((string) getInput('category', ''));
    $categorySql = '';
    $categoryParams = [];
    if ($category !== '') {
        $categorySql = " AND b.category = ? ";
        $categoryParams[] = $category;
    }

    $sort = getInput('sort', 'newest');
    $sortSql = " ORDER BY COALESCE(b.published_at, b.created_at) DESC, b.id DESC ";
    if ($sort === 'oldest')     $sortSql = " ORDER BY COALESCE(b.published_at, b.created_at) ASC, b.id ASC ";
    if ($sort === 'popular')    $sortSql = " ORDER BY b.views DESC, b.id DESC ";
    if ($sort === 'title_asc')  $sortSql = " ORDER BY b.title ASC ";
    if ($sort === 'title_desc') $sortSql = " ORDER BY b.title DESC ";

    // Count
    $countParams = array_merge($searchParams, $categoryParams);
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM blogs b
        WHERE " . BLOG_LIVE_SQL . "
        $searchSql $categorySql
    ");
    $stmt->execute($countParams);
    $totalBlogs = (int) $stmt->fetchColumn();

    // Fetch
    $fetchParams = array_merge($searchParams, $categoryParams, [$per_page, $offset]);
    $stmt = $db->prepare("
        SELECT b.id, b.title, b.slug, b.image, b.excerpt, b.content, b.category, b.views,
               b.published_at, b.created_at,
               u.name AS author_name, u.image AS author_image
        FROM blogs b
        LEFT JOIN users u ON u.id = b.author_id
        WHERE " . BLOG_LIVE_SQL . "
        $searchSql $categorySql
        $sortSql
        LIMIT ? OFFSET ?
    ");
    $stmt->execute($fetchParams);
    $rows = $stmt->fetchAll();

    $blogs = [];
    foreach ($rows as $r) {
        $blogs[] = formatBlogRow($r);
    }

    // Category filter list (with counts, ignoring the active category itself so counts stay meaningful)
    $stmt = $db->query("
        SELECT b.category AS name, COUNT(*) AS post_count
        FROM blogs b
        WHERE " . BLOG_LIVE_SQL . " AND b.category IS NOT NULL AND b.category <> ''
        GROUP BY b.category
        ORDER BY b.category ASC
    ");
    $catRows = $stmt->fetchAll();
    $filterCategories = [];
    foreach ($catRows as $row) {
        $filterCategories[] = [
            'name'       => $row['name'],
            'post_count' => (int) $row['post_count']
        ];
    }

    jsonResponse([
        'success'    => true,
        'blogs'      => $blogs,
        'pagination' => [
            'current_page'  => $page,
            'per_page'      => $per_page,
            'total_items'   => $totalBlogs,
            'total_pages'   => (int) (ceil($totalBlogs / $per_page) ?: 1),
            'has_next'      => ($page < ceil($totalBlogs / $per_page)) ? true : false,
            'has_prev'      => ($page > 1) ? true : false
        ],
        'filters' => [
            'categories'      => $filterCategories,
            'active_category' => $category ?: null,
            'active_search'   => $search ?: null,
            'active_sort'     => $sort
        ]
    ]);

} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Failed to load blog posts. Please try again.'
    ], 500);
}
