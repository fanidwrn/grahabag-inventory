<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graha Bag Inventory</title>
    <link rel="stylesheet" href="../assets/style.css?v=<?= time(); ?>">
</head>
<body>
    <div class="top-navbar">
        <!-- <div class="nav-placeholder"></div> -->
        <div class="nav-right">
            <img src="../assets/icons/notification.png" alt="Notifications" width="16" style="vertical-align: middle;">
            <!-- <div class="search-box-top">
                <input type="text" placeholder="Cari...">
            </div> -->
            <?php if (isset($_SESSION['username'])): ?>
                <span class="user-profile-badge">
                    <img src="../assets/icons/profile.png" alt="Profile" width="18" style="vertical-align: middle; margin-right: 4px;">
                    <?php echo $_SESSION['full_name']; ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="app-layout">