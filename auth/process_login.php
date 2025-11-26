<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];  // plain text
    $role     = $_POST['role'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = ? LIMIT 1");
    $stmt->execute([$username, $role]);
    $user = $stmt->fetch();

    // SIMPLE CHECK: password in DB == typed password
    if ($user && $user['password_hash'] === $password) {
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['member_id'] = $user['member_id'];

        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($user['role'] == 'librarian') {
            header("Location: ../librarian/dashboard.php");
        } elseif ($user['role'] == 'member') {
            header("Location: ../member/dashboard.php");
        }
        exit();
    } else {
        header("Location: login.php?error=1");
        exit();
    }
}
?>