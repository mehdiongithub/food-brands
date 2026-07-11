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
    $action = getInput('action', 'list'); // list, search

    // ============================================================
    // ACTION: Search FAQs (for search page integration)
    // ============================================================
    if ($action === 'search') {
        $keyword = getInput('q');

        if (empty($keyword)) {
            jsonResponse(['success' => false, 'message' => 'Search keyword is required'], 400);
        }

        $keyword = '%' . $keyword . '%';

        $stmt = $db->prepare("
            SELECT id, question, answer, sort_order
            FROM faqs
            WHERE status = 1
              AND (question LIKE ? OR answer LIKE ?)
            ORDER BY sort_order ASC, id ASC
            LIMIT 10
        ");
        $stmt->execute([$keyword, $keyword]);
        $rows = $stmt->fetchAll();

        $faqs = [];
        foreach ($rows as $r) {
            // Highlight matching text in question
            $question = $r['question'];
            $cleanKeyword = trim($keyword, '%');
            if ($cleanKeyword) {
                $question = preg_replace(
                    '/(' . preg_quote($cleanKeyword, '/') . ')/i',
                    '<mark>$1</mark>',
                    $question
                );
            }

            $faqs[] = [
                'id'       => (int) $r['id'],
                'question' => $question,
                'answer'   => $r['answer'],
                'sort'     => (int) $r['sort_order'],
                'url'      => BASE_URL . '/faq#faq-' . $r['id']
            ];
        }

        jsonResponse([
            'success' => true,
            'faqs'    => $faqs,
            'total'   => count($faqs),
            'keyword' => clean(trim($keyword, '%'))
        ]);
    }

    // ============================================================
    // ACTION: List all FAQs (default - for faq.php page)
    // ============================================================

    // Optional: limit to a specific count (for home page preview)
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

    // Optional: category or group filter (for future use if you add groups)
    $group = getInput('group');
    $groupSql = '';
    $groupParams = [];

    // Fetch all active FAQs ordered by sort_order
    $params = array_merge($groupParams, $limitParams);
    $stmt = $db->prepare("
        SELECT id, question, answer, sort_order
        FROM faqs
        WHERE status = 1
        $groupSql
        ORDER BY sort_order ASC, id ASC
        $limitSql
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    // Get total count (without limit)
    $totalStmt = $db->query("
        SELECT COUNT(*) AS total
        FROM faqs
        WHERE status = 1
    ");
    $totalFaqs = (int) $totalStmt->fetchColumn();

    $faqs = [];
    foreach ($rows as $r) {
        // Strip HTML tags from answer for a plain text excerpt
        $plainAnswer = strip_tags($r['answer']);
        $plainAnswer = preg_replace('/\s+/', ' ', $plainAnswer);
        $plainAnswer = trim($plainAnswer);

        // Create excerpt (first 120 chars)
        $excerpt = $plainAnswer;
        if (strlen($excerpt) > 120) {
            $excerpt = substr($excerpt, 0, 117) . '...';
        }

        $faqs[] = [
            'id'              => (int) $r['id'],
            'question'        => $r['question'],
            'answer'          => $r['answer'],
            'answer_plain'    => $plainAnswer,
            'answer_excerpt'  => $excerpt,
            'sort_order'      => (int) $r['sort_order'],
            'anchor'          => 'faq-' . $r['id'],
            'url'             => BASE_URL . '/faq#faq-' . $r['id']
        ];
    }

    // Build Schema.org JSON-LD for FAQ page (only when fetching all, not limited)
    $schemaJson = null;
    if ($limit === null) {
        $schemaFaqs = [];
        foreach ($faqs as $faq) {
            $schemaFaqs[] = [
                '@type'   => 'Question',
                'name'    => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $faq['answer_plain']
                ]
            ];
        }

        if (!empty($schemaFaqs)) {
            $schema = [
                '@context'   => 'https://schema.org',
                '@type'      => 'FAQPage',
                'mainEntity' => $schemaFaqs
            ];
            $schemaJson = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
    }

    jsonResponse([
        'success'     => true,
        'faqs'        => $faqs,
        'total'       => $totalFaqs,
        'schema_json' => $schemaJson
    ]);

} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Failed to load FAQs. Please try again.'
    ], 500);
}