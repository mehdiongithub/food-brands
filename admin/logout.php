<?php
require_once __DIR__ . "/../config/bootstrap.php";

// Clear all session data
$_SESSION = [];

// Destroy the session cookie itself
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Also clear the "remember me" cookie if one was set
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 42000, '/', '', false, true);

    // Optional: also clear the token from the DB so it can't be reused
    list($userId) = explode(':', $_COOKIE['remember_token'] . ':');
    if ($userId && ctype_digit($userId)) {
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = :id");
        $stmt->execute([':id' => (int)$userId]);
    }
}

// Destroy the session itself
session_destroy();

// Redirect to login page
header("Location: " . BASE_URL . "/admin/login.php");
exit;