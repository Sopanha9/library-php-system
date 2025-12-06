<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = ? LIMIT 1");
    $stmt->execute([$username, $role]);
    $user = $stmt->fetch();

    if ($user) {
        // Check if password is bcrypt hash or plain text
        $password_valid = false;
        
        if (password_verify($password, $user['password_hash'])) {
            // Bcrypt hash verification
            $password_valid = true;
        } elseif ($user['password_hash'] === $password) {
            // Plain text comparison (for backward compatibility)
            $password_valid = true;
            
            // Optional: Update to bcrypt hash for security
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            $update->execute([$new_hash, $user['user_id']]);
        }
        
        if ($password_valid) {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['member_id'] = $user['member_id'] ?? null;

            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($user['role'] == 'librarian') {
                header("Location: ../librarian/dashboard.php");
            } elseif ($user['role'] == 'member') {
                header("Location: ../member/dashboard.php");
            }
            exit();
        }
    }
    
    header("Location: login.php?error=1");
    exit();
}
?>