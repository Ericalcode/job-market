<?php
// Ensure session starts safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Job Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<body>
<nav>
    <h2>CareerConnect</h2>
    <div>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="reports.php">Reports</a>
            <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['role']); ?>)</a>
        <?php else: ?>
            <a href="index.php">Role Selection</a>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </div>
</nav>
<div class="container">
