<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/..');
}
require_once BASE_PATH . '/config/database-config.php';

/**
 * Escape a string for safe HTML output (used by category.php and any
 * other page that needs to print dynamic text inside HTML).
 */
function escapeHtml($str) {
    if ($str === null || $str === '') return '';
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Build absolute asset URL from relative database path
 */
function asset_url($path) {
    if (empty($path)) return BASE_URL . '/assets/img/placeholder/placeholder.webp';
    if (strpos($path, 'http') === 0) return $path;
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Get site settings (cached in session for performance)
 */
function getSettings() {
    if (isset($_SESSION['site_settings'])) {
        return $_SESSION['site_settings'];
    }
    $db = getDB();
    $stmt = $db->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch() ?: [];
    $_SESSION['site_settings'] = $settings;
    return $settings;
}

/**
 * Get current country info.
 *
 * $_SESSION['country_id'] is already resolved before this ever runs:
 * config/database-config.php auto-detects it from the visitor's IP on
 * their first request of the session (see resolveVisitorCountryId()
 * there), and the header country dropdown updates it on manual switch.
 * This just reads whatever is currently in the session, with a safe
 * fallback if the stored id is somehow no longer a valid active country.
 */
function getCurrentCountry() {
    $db = getDB();
    $id = (int) ($_SESSION['country_id'] ?? 0);

    $stmt = $db->prepare("SELECT * FROM countries WHERE id = ? AND status = 1");
    $stmt->execute([$id]);
    $country = $stmt->fetch();

    if (!$country) {
        // Fallback to first active country
        $stmt = $db->query("SELECT * FROM countries WHERE status = 1 ORDER BY id ASC LIMIT 1");
        $country = $stmt->fetch();
        if ($country) {
            $_SESSION['country_id'] = $country['id'];
        }
    }

    return $country ?: ['id' => 1, 'name' => 'Pakistan', 'code' => 'PK', 'currency' => 'RUPEE', 'currency_symbol' => 'RS', 'flag' => ''];
}

/**
 * Format price with currency symbol
 */
function formatPrice($amount, $country) {
    if ($amount === null || $amount === '') return 'N/A';
    $symbol = $country['currency_symbol'] ?? '';
    return $symbol . number_format((float) $amount, 0);
}

/**
 * Calculate discount percentage
 */
function discountPercent($regular, $discount) {
    if (!$regular || !$discount || $discount >= $regular) return 0;
    return round((($regular - $discount) / $regular) * 100);
}

/**
 * Strip HTML tags for meta descriptions
 */
function stripMeta($html, $maxLen = 160) {
    $text = strip_tags($html);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    if (strlen($text) > $maxLen) {
        $text = substr($text, 0, $maxLen - 3) . '...';
    }
    return $text;
}

/**
 * ============================================================
 * GOOGLE ADSENSE — renderAdUnit()
 * ============================================================
 * Prints one AdSense placement by its `slug` (see ad_units table).
 *
 * IMPORTANT: this returns an EMPTY STRING (prints nothing at all — no
 * div, no placeholder box) whenever:
 *   - Ads are turned off site-wide (settings.adsense_enabled = 0), OR
 *   - No Publisher ID has been saved (settings.adsense_client), OR
 *   - This specific placement doesn't exist / is disabled / has no
 *     Ad Slot ID filled in yet (ad_units.status = 0 or ad_slot empty)
 *
 * That means: until an admin actually configures + enables a slot in
 * Admin → Ads, the template calls below are 100% inert and can never
 * disturb your layout. Once configured, the ad renders inside a
 * `.ad-slot` card that matches the site's existing card styling, and
 * assets/js/common.js will auto-collapse it if Google itself returns
 * no fill for that impression (see initAdSlots() there).
 *
 * Usage in any page: <?php echo renderAdUnit('home_middle'); ?>
 */
function renderAdUnit($slug) {
    static $adUnitCache = [];   // per-request cache, not per-session — always fresh
    static $adSettings  = null;

    if ($adSettings === null) {
        $s = getSettings(); // already session-cached elsewhere in the app
        $adSettings = [
            'enabled' => !empty($s['adsense_enabled']),
            'client'  => trim($s['adsense_client'] ?? ''),
        ];
    }

    if (!$adSettings['enabled'] || $adSettings['client'] === '') {
        return ''; // AdSense off site-wide — render nothing
    }

    if (!array_key_exists($slug, $adUnitCache)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM ad_units WHERE slug = ? AND status = 1 LIMIT 1");
        $stmt->execute([$slug]);
        $adUnitCache[$slug] = $stmt->fetch() ?: null;
    }

    $unit = $adUnitCache[$slug];
    if (!$unit || empty($unit['ad_slot'])) {
        return ''; // this placement isn't configured/enabled — render nothing
    }

    $client     = htmlspecialchars($adSettings['client'], ENT_QUOTES, 'UTF-8');
    $slotId     = htmlspecialchars($unit['ad_slot'], ENT_QUOTES, 'UTF-8');
    $format     = htmlspecialchars($unit['ad_format'] ?: 'auto', ENT_QUOTES, 'UTF-8');
    $fullWidth  = $unit['full_width_responsive'] ? 'true' : 'false';
    $safeSlug   = htmlspecialchars($slug, ENT_QUOTES, 'UTF-8');

    ob_start();
    ?>
    <div class="ad-slot" data-ad-slug="<?php echo $safeSlug; ?>">
        <span class="ad-slot-label">Advertisement</span>
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="<?php echo $client; ?>"
             data-ad-slot="<?php echo $slotId; ?>"
             data-ad-format="<?php echo $format; ?>"
             data-full-width-responsive="<?php echo $fullWidth; ?>"></ins>
        <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Generate page title
 */
function pageTitle($custom = '', $suffix = true) {
    $settings = getSettings();
    $siteName = $settings['site_name'] ?? 'FoodScope';
    if ($custom) {
        return $suffix ? $custom . ' — ' . $siteName : $custom;
    }
    return $siteName;
}