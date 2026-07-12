<?php
// Load config and functions
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database-config.php';

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Manual country switching is disabled by design. The visitor's
// country is auto-detected from their IP address on session start
// (see config/database-config.php -> resolveVisitorCountryId()) and
// cannot be changed by the visitor.
jsonResponse([
    'success' => false,
    'message' => 'Manual country switching is disabled. Your country is detected automatically from your IP address.'
], 403);