<?php
require_once '../includes/db_connect.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Mengambil Ringkasan Data Utama dari Database
// Total Jenis Bahan
$total_jenis_res = $conn->query("SELECT COUNT(*) as total FROM material");
$total_jenis = $total_jenis_res->fetch_assoc()['total'];

// Total Keseluruhan Stok
$total_stok_res = $conn->query("SELECT SUM(stock) as total FROM material");
$total_stok = $total_stok_res->fetch_assoc()['total'] ?? 0;

// Bahan Stok Kritis
$stok_kritis_res = $conn->query("SELECT COUNT(*) as total FROM material WHERE stock <= minimum_stock");
$stok_kritis = $stok_kritis_res->fetch_assoc()['total'];

// Ambil list bahan baku dengan nama kategorinya
$query_material = "SELECT m.*, c.category_name FROM material m 
                   JOIN category_id c ON m.category_id = c.category_id";
$materials = $conn->query($query_material);
?>

<div class="dashboard-header">
    <h1 class="page-title">Dashboard Utama</h1>
    <p class="welcome-text">Selamat datang kembali, <?php echo $_SESSION['full_name']; ?></p>
</div>

<div class="summary-cards">
    <div class="card card-blue">
        <h3>TOTAL JENIS BAHAN BAKU</h3>
        <p class="card-value"><?php echo $total_jenis; ?></p>
    </div>
    <div class="card card-dark-blue">
        <h3>TOTAL KESELURUHAN STOK</h3>
        <p class="card-value"><?php echo number_format($total_stok); ?> <span class="card-unit">Unit</span></p>
    </div>
    <div class="card card-red">
        <div class="card-red-header">
            <h3>BAHAN BAKU STOK KRITIS</h3>
            <span class="badge-order">PERLU ORDER</span>
        </div>
        <p class="card-value value-red"><?php echo $stok_kritis; ?></p>
    </div>
</div>

<div class="content-table-wrapper">
    <div class="table-header-actions">
        <h3>Monitor Stok Bahan Baku</h3>
        <div class="search-sub-box">
            <input type="text" id="tableSearch" placeholder="Cari bahan baku...">
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>NAMA BAHAN BAKU</th>
                <th>KATEGORI</th>
                <th>STOK SAAT INI</th>
                <th>SATUAN</th>
                <th>MIN. STOK</th>
                <th>HARGA SATUAN</th>
                <th>STATUS STOK</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $materials->fetch_assoc()): 
                $is_kritis = $row['stock'] <= $row['minimum_stock'];
                $status_class = $is_kritis ? 'status-kritis' : 'status-aman';
                $status_text = $is_kritis ? 'KRITIS' : 'AMAN';
                $stock_class = $is_kritis ? 'text-danger-bold' : 'text-normal-bold';
            ?>
            <tr>
                <td>BB-0<?php echo $row['material_id']; ?></td>
                <td><strong><?php echo $row['material_name']; ?></strong></td>
                <td><?php echo $row['category_name']; ?></td>
                <td class="<?php echo $stock_class; ?>"><?php echo number_format($row['stock']); ?></td>
                <td><?php echo $row['unit']; ?></td>
                <td><?php echo number_format($row['minimum_stock']); ?></td>
                <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>