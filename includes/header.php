<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user info
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email']
        ];
    }
    return null;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Redirect if already logged in
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مساعد الطالب الذكي</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">مساعد الطالب الذكي</a>
            <div class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <span class="welcome-msg">مرحباً، <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="index.php">المهام</a>
                    <a href="add_task.php">إضافة مهمة</a>
                    <a href="logout.php" class="btn-logout">تسجيل الخروج</a>
                <?php else: ?>
                    <a href="login.php">تسجيل الدخول</a>
                    <a href="register.php">حساب جديد</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main class="container">
