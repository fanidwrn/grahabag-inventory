<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
?>
<div class="sidebar">
    <div class="sidebar-brand">
        <h2>GRAHABAG INVENTORY</h2>
        <p>Management System</p>
    </div>
    
    <ul class="sidebar-menu">
        <li class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php">Dashboard</a>
        </li>
        
        <li class="<?php echo $current_page == 'supplier.php' ? 'active' : ''; ?>">
            <a href="supplier.php">Supplier</a>
        </li>
        
        <li class="<?php echo $current_page == 'bahan_baku.php' ? 'active' : ''; ?>">
            <a href="bahan_baku.php">Bahan Baku</a>
        </li>
        <li class="<?php echo $current_page == 'stok_masuk.php' ? 'active' : ''; ?>">
            <a href="stok_masuk.php">Stok Masuk</a>
        </li>
        <li class="<?php echo $current_page == 'stok_keluar.php' ? 'active' : ''; ?>">
            <a href="stok_keluar.php">Stok Keluar</a>
        </li>
        <li class="<?php echo $current_page == 'pengajuan.php' ? 'active' : ''; ?>">
            <a href="pengajuan.php">Pengajuan Bahan</a>
        </li>
        <li class="<?php echo $current_page == 'laporan.php' ? 'active' : ''; ?>">
            <a href="#laporan">Laporan</a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <a href="logout.php" class="footer-link logout-btn">Logout</a>
    </div>
</div>
<div class="main-content">