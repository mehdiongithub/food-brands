<?php
require_once __DIR__ . "/../../config/bootstrap.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$remember = isset($_POST['remember']) && $_POST['remember'] === 'true';

$errors = [];

if ($email === '') {
    $errors['email'] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if ($password === '') {
    $errors['password'] = 'Password is required.';
} elseif (strlen($password) < 6) {
    $errors['password'] = 'Password must be at least 6 characters.';
}

if (!empty($errors)) {
    echo json_encode([
        "success" => false,
        "message" => "Please fix the errors below.",
        "errors" => $errors
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name, email, password, role, status, image FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Generic message on purpose — don't reveal whether the email exists or the password was wrong
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password.",
            "errors" => ["password" => "Invalid email or password."]
        ]);
        exit;
    }

    if ((int)$user['status'] !== 1) {
        echo json_encode([
            "success" => false,
            "message" => "Your account is inactive. Please contact the administrator."
        ]);
        exit;
    }

    // --- Only admin/employee roles may access the admin panel ---
    if (!in_array($user['role'], ['admin', 'employee'], true)) {
        echo json_encode([
            "success" => false,
            "message" => "You don't have permission to access the admin panel."
        ]);
        exit;
    }

    // --- Start session safely, then regenerate ID to prevent session fixation ---
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_role']  = $user['role'];
    $_SESSION['user_image'] = $user['image'];

    // --- Remember me (30 days) ---
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);

        $updateStmt = $pdo->prepare("UPDATE users SET remember_token = :token, last_login = NOW() WHERE id = :id");
        $updateStmt->execute([':token' => $hashedToken, ':id' => $user['id']]);

        setcookie(
            'remember_token',
            $user['id'] . ':' . $token,
            [
                'expires'  => time() + (30 * 24 * 60 * 60),
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax'
                // 'secure' => true, // enable this once you're on HTTPS
            ]
        );
    } else {
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $updateStmt->execute([':id' => $user['id']]);
    }

    echo json_encode([
        "success" => true,
        "message" => "Welcome back, " . $user['name'] . "!",
        "redirect" => BASE_URL . "/admin/dashboard.php"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Something went wrong. Please try again."
    ]);
}