<?php
/**
 * config/auth.php
 * Authentication helper functions — loaded via bootstrap.php on every page.
 */

/**
 * Returns true if a user is currently logged in.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Returns the logged-in user's role, or null if not logged in.
 */
function currentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Returns the logged-in user's ID, or null if not logged in.
 */
function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Call this at the top of every protected admin page.
 * Redirects to login if not authenticated.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/admin/login.php");
        exit;
    }
}

/**
 * Call this on pages that should only be accessible to specific roles.
 * Usage: requireRole(['admin']);
 */
function requireApiRole($allowedRoles = []) {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Unauthorized. Please log in."]);
        exit;
    }

    if (!in_array(currentUserRole(), $allowedRoles, true)) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "You don't have permission to access this data."]);
        exit;
    }
}
