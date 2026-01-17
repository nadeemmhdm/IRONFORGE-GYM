<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IronForge Gym</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Outfit:wght@700;800&display=swap"
        rel="stylesheet">

    <?php
    // Simple logic to determine relative path based on whether style.css exists in current directory
    $base_path = file_exists('style.css') ? '' : '../';
    ?>
    <link rel="stylesheet" href="<?php echo $base_path; ?>style.css">
    <style>
        /* Fix path if in subdir */
    </style>
</head>

<body>
    <header>
        <div class="container nav-container">
            <a href="<?php echo $base_path; ?>index.php" class="logo">IRONFORGE GYM</a>
            <nav>
                <ul>
                    <li><a href="<?php echo $base_path; ?>index.php">Home</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <li><a href="<?php echo $base_path; ?>admin/dashboard.php">Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo $base_path; ?>user/dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo $base_path; ?>logout.php" class="btn btn-outline">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $base_path; ?>register.php">Register</a></li>
                        <li><a href="<?php echo $base_path; ?>login.php">Login</a></li>
                        <li><a href="<?php echo $base_path; ?>admin_login.php">Admin</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>