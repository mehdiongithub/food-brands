<?php

/**
 * Get initials from a full name — first letter of first + last word.
 * "Alex Kumar" -> "AK", "Madonna" -> "M"
 */
if (!function_exists('getInitials')) {
    function getInitials($name) {
        $name = trim($name);
        if ($name === '') return '?';

        $parts = preg_split('/\s+/', $name);
        if (count($parts) === 1) {
            return mb_strtoupper(mb_substr($parts[0], 0, 1));
        }
        $first = mb_substr($parts[0], 0, 1);
        $last  = mb_substr($parts[count($parts) - 1], 0, 1);
        return mb_strtoupper($first . $last);
    }
}

/**
 * Deterministic color based on name, so the same person always
 * gets the same avatar color (not random on every page load).
 */
if (!function_exists('initialsColor')) {
    function initialsColor($name) {
        $colors = [
            '#E85D04', '#059669', '#0891B2', '#7C3AED',
            '#D97706', '#DC2626', '#2563EB', '#DB2777'
        ];
        $hash = 0;
        foreach (str_split($name) as $char) {
            $hash += ord($char);
        }
        return $colors[$hash % count($colors)];
    }
}

/**
 * Maps common country names to their ISO 3166-1 alpha-2 codes.
 * Add more entries here as needed — this covers the most common ones.
 */
if (!function_exists('getCountryCode')) {
    function getCountryCode($name) {
        static $map = [
            'united states' => 'us', 'usa' => 'us', 'america' => 'us',
            'united kingdom' => 'gb', 'uk' => 'gb', 'britain' => 'gb',
            'pakistan' => 'pk',
            'india' => 'in',
            'united arab emirates' => 'ae', 'uae' => 'ae',
            'saudi arabia' => 'sa',
            'germany' => 'de',
            'france' => 'fr',
            'italy' => 'it',
            'spain' => 'es',
            'canada' => 'ca',
            'australia' => 'au',
            'china' => 'cn',
            'japan' => 'jp',
            'south korea' => 'kr', 'korea' => 'kr',
            'brazil' => 'br',
            'mexico' => 'mx',
            'russia' => 'ru',
            'turkey' => 'tr',
            'egypt' => 'eg',
            'bangladesh' => 'bd',
            'indonesia' => 'id',
            'malaysia' => 'my',
            'singapore' => 'sg',
            'thailand' => 'th',
            'philippines' => 'ph',
            'vietnam' => 'vn',
            'south africa' => 'za',
            'nigeria' => 'ng',
            'kenya' => 'ke',
            'netherlands' => 'nl',
            'belgium' => 'be',
            'switzerland' => 'ch',
            'sweden' => 'se',
            'norway' => 'no',
            'denmark' => 'dk',
            'poland' => 'pl',
            'portugal' => 'pt',
            'greece' => 'gr',
            'ireland' => 'ie',
            'new zealand' => 'nz',
            'argentina' => 'ar',
            'chile' => 'cl',
            'colombia' => 'co',
            'qatar' => 'qa',
            'kuwait' => 'kw',
            'oman' => 'om',
            'bahrain' => 'bh',
            'jordan' => 'jo',
            'lebanon' => 'lb',
            'iraq' => 'iq',
            'iran' => 'ir',
            'israel' => 'il',
            'afghanistan' => 'af',
            'sri lanka' => 'lk',
            'nepal' => 'np',
        ];

        $key = strtolower(trim($name));
        return $map[$key] ?? null;
    }
}

/**
 * Returns a flag <img> tag — uses uploaded image if present,
 * otherwise auto-detects using flagcdn.com based on country name.
 */
if (!function_exists('getFlagHtml')) {
    function getFlagHtml($name, $uploadedImage = null) {
        if (!empty($uploadedImage)) {
            return '<img src="' . htmlspecialchars(BASE_URL . '/' . $uploadedImage) . '" alt="' . htmlspecialchars($name) . '" style="width:28px;height:20px;object-fit:cover;border-radius:3px;border:1px solid var(--border);">';
        }

        $code = getCountryCode($name);
        if ($code) {
            return '<img src="https://flagcdn.com/w40/' . $code . '.png" alt="' . htmlspecialchars($name) . '" style="width:28px;height:20px;object-fit:cover;border-radius:3px;border:1px solid var(--border);">';
        }

        // Fallback — no match found, show a generic globe icon
        return '<span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:20px;background:var(--bg);border-radius:3px;border:1px solid var(--border);"><i class="fas fa-globe" style="font-size:.65rem;color:var(--muted);"></i></span>';
    }
}

if (!function_exists('getCurrencySymbol')) {
    /**
     * Gets the currency symbol for a 2-letter country code.
     * Optionally formats a numeric amount with the correct symbol placement.
     *
     * @param string $countryCode 2-letter country code (e.g., 'pk', 'us')
     * @param float|int|null $amount Optional number to format with the symbol
     * @return string The symbol or the fully formatted price string
     */
    function getCurrencySymbol($countryCode, $amount = null) {
        static $map = [
            'us' => ['symbol' => '$',   'position' => 'before'], // USD
            'gb' => ['symbol' => '£',   'position' => 'before'], // GBP
            'pk' => ['symbol' => '₨',   'position' => 'before'], // PKR
            'in' => ['symbol' => '₹',   'position' => 'before'], // INR
            'ae' => ['symbol' => 'د.إ', 'position' => 'after'],  // AED
            'sa' => ['symbol' => 'ر.س', 'position' => 'after'],  // SAR
            'de' => ['symbol' => '€',   'position' => 'after'],  // EUR
            'fr' => ['symbol' => '€',   'position' => 'after'],  // EUR
            'it' => ['symbol' => '€',   'position' => 'after'],  // EUR
            'es' => ['symbol' => '€',   'position' => 'after'],  // EUR
            'ca' => ['symbol' => '$',   'position' => 'before'], // CAD
            'au' => ['symbol' => '$',   'position' => 'before'], // AUD
            'cn' => ['symbol' => '¥',   'position' => 'before'], // CNY
            'jp' => ['symbol' => '¥',   'position' => 'before'], // JPY
            'kr' => ['symbol' => '₩',   'position' => 'before'], // KRW
            'br' => ['symbol' => 'R$',  'position' => 'before'], // BRL
            'mx' => ['symbol' => '$',   'position' => 'before'], // MXN
            'ru' => ['symbol' => '₽',   'position' => 'after'],  // RUB
            'tr' => ['symbol' => '₺',   'position' => 'before'], // TRY
            'eg' => ['symbol' => 'E£',  'position' => 'before'], // EGP
            'bd' => ['symbol' => '৳',   'position' => 'before'], // BDT
            'id' => ['symbol' => 'Rp',  'position' => 'before'], // IDR
            'my' => ['symbol' => 'RM',  'position' => 'before'], // MYR
            'sg' => ['symbol' => '$',   'position' => 'before'], // SGD
            'th' => ['symbol' => '฿',   'position' => 'before'], // THB
            'ph' => ['symbol' => '₱',   'position' => 'before'], // PHP
            'vn' => ['symbol' => '₫',   'position' => 'after'],  // VND
            'za' => ['symbol' => 'R',   'position' => 'before'], // ZAR
            'ng' => ['symbol' => '₦',   'position' => 'before'], // NGN
            'ke' => ['symbol' => 'KSh', 'position' => 'before'], // KES
            'nl' => ['symbol' => '€',   'position' => 'after'],  // EUR
            'be' => ['symbol' => '€',   'position' => 'after'],  // EUR
            'ch' => ['symbol' => 'CHF', 'position' => 'before'], // CHF
            'se' => ['symbol' => 'kr',  'position' => 'after'],  // SEK
            'no' => ['symbol' => 'kr',  'position' => 'after'],  // NOK
            'dk' => ['symbol' => 'kr',  'position' => 'after'],  // DKK
            'pl' => ['symbol' => 'zł',  'position' => 'after'],  // PLN
            'pt' => ['symbol' => '€',   'position' => 'after'],  // EUR
            'gr' => ['symbol' => '€',   'position' => 'after'],  // EUR
            'ie' => ['symbol' => '€',   'position' => 'before'], // EUR (Ireland places symbol before)
            'nz' => ['symbol' => '$',   'position' => 'before'], // NZD
            'ar' => ['symbol' => '$',   'position' => 'before'], // ARS
            'cl' => ['symbol' => '$',   'position' => 'before'], // CLP
            'co' => ['symbol' => '$',   'position' => 'before'], // COP
            'qa' => ['symbol' => 'ر.ق', 'position' => 'after'],  // QAR
            'kw' => ['symbol' => 'د.ك', 'position' => 'after'],  // KWD
            'om' => ['symbol' => 'ر.ع.', 'position' => 'after'], // OMR
            'bh' => ['symbol' => 'د.ب', 'position' => 'after'],  // BHD
            'jo' => ['symbol' => 'د.ا', 'position' => 'after'],  // JOD
            'lb' => ['symbol' => 'ل.ل', 'position' => 'after'],  // LBP
            'iq' => ['symbol' => 'ع.د', 'position' => 'after'],  // IQD
            'ir' => ['symbol' => '﷼',   'position' => 'after'],  // IRR
            'il' => ['symbol' => '₪',   'position' => 'before'], // ILS
            'af' => ['symbol' => '؋',   'position' => 'after'],  // AFN
            'lk' => ['symbol' => 'Rs',  'position' => 'before'], // LKR
            'np' => ['symbol' => 'रू',  'position' => 'before'], // NPR
        ];

        $code = strtolower(trim($countryCode));
        $config = $map[$code] ?? ['symbol' => '$', 'position' => 'before']; // Default fallback

        // If no amount is provided, just return the raw symbol (e.g., "$")
        if ($amount === null) {
            return $config['symbol'];
        }

        // Format the number cleanly with commas and 2 decimals
        $formattedNum = number_format($amount, 2);

        // Put the symbol in the correct place based on local rules
        if ($config['position'] === 'before') {
            return $config['symbol'] . $formattedNum;
        } else {
            return $formattedNum . ' ' . $config['symbol'];
        }
    }
}

/**
 * URL-safe encryption/decryption for hiding numeric IDs in URLs.
 * Uses AES-256-CBC. Requires ENCRYPTION_KEY defined in config.php.
 */
if (!function_exists('encryptId')) {
    function encryptId($id) {
        $method = 'AES-256-CBC';
        $key = hash('sha256', ENCRYPTION_KEY, true); // 32-byte key
        $ivLength = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $encrypted = openssl_encrypt((string)$id, $method, $key, OPENSSL_RAW_DATA, $iv);

        // Prepend IV to the ciphertext so we can decrypt later, then base64url encode
        $combined = $iv . $encrypted;
        return rtrim(strtr(base64_encode($combined), '+/', '-_'), '=');
    }
}

if (!function_exists('decryptId')) {
    function decryptId($token) {
        if (empty($token)) return false;

        $method = 'AES-256-CBC';
        $key = hash('sha256', ENCRYPTION_KEY, true);

        $combined = base64_decode(strtr($token, '-_', '+/'));
        if ($combined === false) return false;

        $ivLength = openssl_cipher_iv_length($method);
        if (strlen($combined) <= $ivLength) return false;

        $iv = substr($combined, 0, $ivLength);
        $encrypted = substr($combined, $ivLength);

        $decrypted = openssl_decrypt($encrypted, $method, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false || !ctype_digit($decrypted)) {
            return false; // tampered, malformed, or not a valid integer
        }

        return (int)$decrypted;
    }
}