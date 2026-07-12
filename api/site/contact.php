<?php
// Load config and functions
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database-config.php';

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed. Use POST.'], 405);
}

try {
    $db = getDB();

    // Get and sanitize inputs
    $name    = getInput('name');
    $email   = getInput('email');
    $phone   = getInput('phone');
    $subject = getInput('subject');
    $message = getInput('message');

    // ============================================================
    // Validation
    // ============================================================
    $errors = [];

    // Name: required, min 2 chars, max 150 chars
    if (empty($name)) {
        $errors[] = 'Name is required.';
    } elseif (strlen(trim($name)) < 2) {
        $errors[] = 'Name must be at least 2 characters.';
    } elseif (strlen(trim($name)) > 150) {
        $errors[] = 'Name must not exceed 150 characters.';
    }

    // Email: required, valid format, max 150 chars
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen(trim($email)) > 150) {
        $errors[] = 'Email must not exceed 150 characters.';
    }

    // Phone: optional, but if provided must be valid format
    if (!empty($phone)) {
        $phoneClean = preg_replace('/[\s\-\+\(\)]/', '', trim($phone));
        if (!preg_match('/^[0-9]{7,20}$/', $phoneClean)) {
            $errors[] = 'Please enter a valid phone number.';
        } elseif (strlen(trim($phone)) > 30) {
            $errors[] = 'Phone number must not exceed 30 characters.';
        }
    }

    // Subject: required, min 3 chars, max 255 chars
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    } elseif (strlen(trim($subject)) < 3) {
        $errors[] = 'Subject must be at least 3 characters.';
    } elseif (strlen(trim($subject)) > 255) {
        $errors[] = 'Subject must not exceed 255 characters.';
    }

    // Message: required, min 10 chars
    if (empty($message)) {
        $errors[] = 'Message is required.';
    } elseif (strlen(trim($message)) < 10) {
        $errors[] = 'Message must be at least 10 characters.';
    }

    // If there are validation errors, return them all
    if (!empty($errors)) {
        jsonResponse([
            'success' => false,
            'message' => 'Please fix the following errors.',
            'errors'  => $errors
        ], 422);
    }

    // ============================================================
    // Spam / Rate Limiting Check
    // ============================================================
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Check if same IP submitted more than 3 messages in last 5 minutes
    $stmt = $db->prepare("
        SELECT COUNT(*) AS total
        FROM contact_messages
        WHERE ip_address = ?
          AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    $stmt->execute([$clientIp]);
    $recentCount = (int) $stmt->fetchColumn();

    if ($recentCount >= 3) {
        jsonResponse([
            'success' => false,
            'message' => 'Too many messages sent recently. Please wait a few minutes before trying again.'
        ], 429);
    }

    // Check if same email submitted more than 5 messages in last 24 hours
    $stmt = $db->prepare("
        SELECT COUNT(*) AS total
        FROM contact_messages
        WHERE email = ?
          AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stmt->execute([trim($email)]);
    $dailyEmailCount = (int) $stmt->fetchColumn();

    if ($dailyEmailCount >= 5) {
        jsonResponse([
            'success' => false,
            'message' => 'You have reached the daily message limit. Please try again tomorrow.'
        ], 429);
    }

    // ============================================================
    // Basic Spam Content Check
    // ============================================================
    $spamPatterns = [
        '/(http|https):\/\/[^\s]+/i',          // URLs in message
        '/<a\s+href/i',                          // HTML links
        '/(viagra|casino|lottery|bitcoin|crypto|forex)/i', // Common spam words
        '/(\.ru|\.cn|\.xyz|\.top)\b/i',         // Suspicious TLD mentions
    ];

    $checkContent = $name . ' ' . $subject . ' ' . $message;
    $spamScore = 0;

    foreach ($spamPatterns as $pattern) {
        if (preg_match($pattern, $checkContent)) {
            $spamScore++;
        }
    }

    // If high spam score, silently accept but mark differently
    // (Don't tell spammers they were detected)
    $isSpam = ($spamScore >= 2);

    // ============================================================
    // Insert into database
    // ============================================================
    $cleanName    = trim($name);
    $cleanEmail   = trim($email);
    $cleanPhone   = !empty($phone) ? trim($phone) : null;
    $cleanSubject = trim($subject);
    $cleanMessage = trim($message);

    $stmt = $db->prepare("
        INSERT INTO contact_messages (name, email, phone, subject, message, status, ip_address, created_at)
        VALUES (?, ?, ?, ?, ?, 'new', ?, NOW())
    ");
    $result = $stmt->execute([
        $cleanName,
        $cleanEmail,
        $cleanPhone,
        $cleanSubject,
        $cleanMessage,
        $clientIp
    ]);

    if (!$result) {
        jsonResponse([
            'success' => false,
            'message' => 'Failed to send your message. Please try again later.'
        ], 500);
    }

    $messageId = (int) $db->lastInsertId();

    // ============================================================
    // Send Email Notification (Optional - configure if needed)
    // ============================================================
    $emailSent = false;
    $emailError = null;

    // Get site settings for email
    $settings = getSettings();
    $adminEmail = $settings['email'] ?? '';
    $siteName = $settings['site_name'] ?? 'FoodScope';

    if (!empty($adminEmail) && !$isSpam) {
        $emailSent = sendContactNotification($adminEmail, $siteName, $cleanName, $cleanEmail, $cleanPhone, $cleanSubject, $cleanMessage, $messageId);
        if (!$emailSent) {
            $emailError = 'Email notification could not be sent, but your message was saved.';
        }
    }

    // ============================================================
    // Success Response
    // ============================================================
    $response = [
        'success'     => true,
        'message'     => 'Thank you for contacting us! We have received your message and will get back to you shortly.',
        'message_id'  => $messageId
    ];

    // Only include email warning if it failed (for debugging)
    if ($emailError) {
        $response['email_note'] = $emailError;
    }

    jsonResponse($response, 201);

} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'A database error occurred. Please try again later.'
    ], 500);
}


/**
 * Send email notification to admin about new contact message
 * Returns true on success, false on failure
 */
function sendContactNotification($adminEmail, $siteName, $name, $email, $phone, $subject, $message, $messageId) {
    try {
        // Build email content
        $emailSubject = "[{$siteName}] New Contact Message: " . $subject;
        
        $emailBody = "You have received a new contact message from {$siteName}.\n\n";
        $emailBody .= "Message ID: #{$messageId}\n";
        $emailBody .= "Date: " . date('Y-m-d H:i:s') . "\n";
        $emailBody .= "----------------------------------------\n\n";
        $emailBody .= "Name: {$name}\n";
        $emailBody .= "Email: {$email}\n";
        if ($phone) {
            $emailBody .= "Phone: {$phone}\n";
        }
        $emailBody .= "Subject: {$subject}\n\n";
        $emailBody .= "Message:\n{$message}\n\n";
        $emailBody .= "----------------------------------------\n";
        $emailBody .= "Login to your admin panel to view and reply to this message.\n";

        // Additional headers
        $headers = "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
        $headers .= "Reply-To: {$email}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $sent = mail($adminEmail, $emailSubject, $emailBody, $headers);

        return (bool) $sent;

    } catch (Exception $e) {
        return false;
    }
}