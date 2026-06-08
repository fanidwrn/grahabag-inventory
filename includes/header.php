<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$has_new_notif = false;
if (isset($_SESSION['username'])) {
    require_once __DIR__ . '/db_connect.php';
    
    $username = $_SESSION['username'];
    $res = $conn->query("SELECT last_notif_read FROM users WHERE username = '$username'");
    $user_data = $res ? $res->fetch_assoc() : null;
    $last_notif_read = $user_data['last_notif_read'] ?? '1970-01-01 00:00:00';
    $last_read_date = date('Y-m-d', strtotime($last_notif_read));
    $today = date('Y-m-d');
    
    // Check critical stock
    $res_kritis = $conn->query("SELECT COUNT(*) as count FROM material WHERE stock <= minimum_stock");
    $kritis_count = $res_kritis ? $res_kritis->fetch_assoc()['count'] : 0;
    
    // Check pengajuan
    $res_purchase = $conn->query("SELECT COUNT(*) as count FROM material_purchase WHERE updated_at > '$last_notif_read'");
    $purchase_count = $res_purchase ? $res_purchase->fetch_assoc()['count'] : 0;
    
    if (($kritis_count > 0 && $last_read_date < $today) || $purchase_count > 0) {
        $has_new_notif = true;
    }
}
$notif_icon = $has_new_notif ? 'notification_active.png' : 'notification.png';
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
        <div class="nav-right">
            <a href="notifikasi.php"><img src="../assets/icons/<?php echo $notif_icon; ?>" alt="Notifications" width="16" style="vertical-align: middle;"></a>
            <?php if (isset($_SESSION['username'])): ?>
                <span class="user-profile-badge">
                    <img src="../assets/icons/profile.png" alt="Profile" width="18" style="vertical-align: middle; margin-right: 4px;">
                    <?php echo $_SESSION['full_name']; ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="app-layout">