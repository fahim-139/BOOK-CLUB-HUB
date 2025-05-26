<?php
require_once '../db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form inputs
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($token) || empty($password) || empty($confirmPassword)) {
        die('❌ All fields are required.');
    }

    if ($password !== $confirmPassword) {
        die('❌ Passwords do not match.');
    }

    // Optional: enforce stronger password rules (e.g., at least 8 characters)
    if (strlen($password) < 8) {
        die('❌ Password must be at least 8 characters.');
    }

    // Check token in DB
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // Hash new password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update password and clear token
        $update = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $update->execute([$hashedPassword, $user['id']]);

        echo '✅ Password successfully reset. <a href="../html/login.html">Login now</a>';
    } else {
        echo '❌ Invalid or expired token.';
    }
} else {
    echo '❌ Invalid request method.';
}
?>