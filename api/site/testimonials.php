<?php
// Load config and functions
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database-config.php';


// Set JSON response header
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $db = getDB();
    $action = getInput('action', 'list'); // list, detail, random

    // ============================================================
    // ACTION: Get single testimonial by ID
    // ============================================================
    if ($action === 'detail') {
        $id = (int) getInput('id', 0);

        if ($id <= 0) {
            jsonResponse(['success' => false, 'message' => 'Testimonial ID is required'], 400);
        }

        $stmt = $db->prepare("
            SELECT id, name, designation, image, review, rating, created_at
            FROM testimonials
            WHERE id = ? AND status = 1
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            jsonResponse(['success' => false, 'message' => 'Testimonial not found'], 404);
        }

        // Build star icons array
        $stars = [];
        $rating = (int) $row['rating'];
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) {
                $stars[] = 'full';
            } else {
                $stars[] = 'empty';
            }
        }

        // Plain text review
        $plainReview = strip_tags($row['review']);
        $plainReview = preg_replace('/\s+/', ' ', $plainReview);
        $plainReview = trim($plainReview);

        jsonResponse([
            'success'     => true,
            'testimonial' => [
                'id'          => (int) $row['id'],
                'name'        => $row['name'],
                'designation' => $row['designation'],
                'image'       => asset_url($row['image']),
                'review'      => $row['review'],
                'review_plain'=> $plainReview,
                'rating'      => $rating,
                'stars'       => $stars,
                'created_at'  => $row['created_at']
            ]
        ]);
    }

    // ============================================================
    // ACTION: Get random testimonials (for rotating sections)
    // ============================================================
    if ($action === 'random') {
        $limit = (int) getInput('limit', 3);
        if ($limit > 20) $limit = 20;
        if ($limit < 1) $limit = 3;

        $stmt = $db->query("
            SELECT id, name, designation, image, review, rating, created_at
            FROM testimonials
            WHERE status = 1
            ORDER BY RAND()
            LIMIT $limit
        ");
        $rows = $stmt->fetchAll();

        $testimonials = [];
        foreach ($rows as $r) {
            $rating = (int) $r['rating'];
            $stars = [];
            for ($i = 1; $i <= 5; $i++) {
                $stars[] = $i <= $rating ? 'full' : 'empty';
            }

            $plainReview = strip_tags($r['review']);
            $plainReview = preg_replace('/\s+/', ' ', $plainReview);
            $plainReview = trim($plainReview);

            $testimonials[] = [
                'id'          => (int) $r['id'],
                'name'        => $r['name'],
                'designation' => $r['designation'],
                'image'       => asset_url($r['image']),
                'review'      => $r['review'],
                'review_plain'=> $plainReview,
                'rating'      => $rating,
                'stars'       => $stars,
                'created_at'  => $r['created_at']
            ];
        }

        jsonResponse([
            'success'      => true,
            'testimonials' => $testimonials,
            'total'        => count($testimonials)
        ]);
    }

    // ============================================================
    // ACTION: List all testimonials (default - for home page, about page)
    // ============================================================

    // Optional limit (for home page preview vs full page)
    $limit = getInput('limit');
    $limitSql = '';
    $limitParams = [];

    if ($limit !== null) {
        $limit = (int) $limit;
        if ($limit > 0) {
            $limitSql = " LIMIT ? ";
            $limitParams[] = $limit;
        }
    }

    // Sort order
    $sort = getInput('sort', 'newest');
    $sortSql = " ORDER BY id DESC ";
    if ($sort === 'oldest') $sortSql = " ORDER BY id ASC ";
    if ($sort === 'rating_high') $sortSql = " ORDER BY rating DESC, id DESC ";
    if ($sort === 'rating_low') $sortSql = " ORDER BY rating ASC, id DESC ";
    if ($sort === 'random') $sortSql = " ORDER BY RAND() ";

    // Get total count (without limit)
    $totalStmt = $db->query("SELECT COUNT(*) AS total FROM testimonials WHERE status = 1");
    $totalTestimonials = (int) $totalStmt->fetchColumn();

    // Fetch testimonials
    $params = array_merge($limitParams);
    $stmt = $db->prepare("
        SELECT id, name, designation, image, review, rating, created_at
        FROM testimonials
        WHERE status = 1
        $sortSql
        $limitSql
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $testimonials = [];
    $totalRatingSum = 0;
    $ratingCount = 0;

    foreach ($rows as $r) {
        $rating = (int) $r['rating'];
        if ($rating > 0) {
            $totalRatingSum += $rating;
            $ratingCount++;
        }

        // Build star icons array for easy frontend rendering
        $stars = [];
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) {
                $stars[] = 'full';
            } else {
                $stars[] = 'empty';
            }
        }

        // Plain text review (for meta/SEO use)
        $plainReview = strip_tags($r['review']);
        $plainReview = preg_replace('/\s+/', ' ', $plainReview);
        $plainReview = trim($plainReview);

        // Excerpt (first 100 chars)
        $excerpt = $plainReview;
        if (strlen($excerpt) > 100) {
            $excerpt = substr($excerpt, 0, 97) . '...';
        }

        // Relative time (e.g., "2 months ago")
        $relativeTime = relativeTime($r['created_at']);

        $testimonials[] = [
            'id'            => (int) $r['id'],
            'name'          => $r['name'],
            'designation'   => $r['designation'],
            'image'         => asset_url($r['image']),
            'review'        => $r['review'],
            'review_plain'  => $plainReview,
            'review_excerpt'=> $excerpt,
            'rating'        => $rating,
            'stars'         => $stars,
            'relative_time' => $relativeTime,
            'created_at'    => $r['created_at']
        ];
    }

    // Calculate average rating from ALL testimonials (not just the limited set)
    $avgStmt = $db->query("
        SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_rated
        FROM testimonials
        WHERE status = 1 AND rating > 0
    ");
    $avgData = $avgStmt->fetch();
    $averageRating = $avgData['avg_rating'] !== null ? round((float) $avgData['avg_rating'], 1) : 0;
    $totalRated = (int) $avgData['total_rated'];

    // Build rating distribution (how many 5-star, 4-star, etc.)
    $distStmt = $db->query("
        SELECT rating, COUNT(*) AS count
        FROM testimonials
        WHERE status = 1 AND rating > 0
        GROUP BY rating
        ORDER BY rating DESC
    ");
    $distRows = $distStmt->fetchAll();

    $ratingDistribution = [
        5 => 0,
        4 => 0,
        3 => 0,
        2 => 0,
        1 => 0
    ];
    foreach ($distRows as $dr) {
        $ratingDistribution[(int) $dr['rating']] = (int) $dr['count'];
    }

    jsonResponse([
        'success'            => true,
        'testimonials'       => $testimonials,
        'total'              => $totalTestimonials,
        'average_rating'     => $averageRating,
        'total_rated'        => $totalRated,
        'rating_distribution'=> $ratingDistribution
    ]);

} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Failed to load testimonials. Please try again.'
    ], 500);
}

/**
 * Convert a datetime string to relative time (e.g., "2 months ago")
 */
function relativeTime($datetime) {
    if (empty($datetime)) return '';

    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) {
        return $diff->y . ($diff->y === 1 ? ' year ago' : ' years ago');
    }
    if ($diff->m > 0) {
        return $diff->m . ($diff->m === 1 ? ' month ago' : ' months ago');
    }
    if ($diff->d > 0) {
        return $diff->d . ($diff->d === 1 ? ' day ago' : ' days ago');
    }
    if ($diff->h > 0) {
        return $diff->h . ($diff->h === 1 ? ' hour ago' : ' hours ago');
    }
    if ($diff->i > 0) {
        return $diff->i . ($diff->i === 1 ? ' minute ago' : ' minutes ago');
    }
    return 'Just now';
}