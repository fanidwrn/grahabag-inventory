<?php
require_once '../includes/db_connect.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'] ?? 'admin';

// Ambil data supplier
$query_supplier = "SELECT * FROM suppliers ORDER BY supplier_id ASC";
$suppliers = $conn->query($query_supplier);
$total_supplier = $suppliers->num_rows;
?>

<?php if (isset($_SESSION['toast'])): ?>
    <div id="toastNotification" class="toast toast-<?php echo $_SESSION['toast']['type']; ?>">
        <?php echo $_SESSION['toast']['message']; ?>
    </div>
    <?php unset($_SESSION['toast']); ?>
<?php endif; ?>

<div class="bahan-baku-container">
    <div class="page-header-action">
        <div class="header-left">
            <h1 class="page-title-main">Kelola Supplier</h1>
            <p class="page-subtitle-main">Daftar informasi pemasok bahan baku.</p>
        </div>
        <?php if ($role === 'owner'): ?>
        <button class="btn-add-primary btn-open-modal" data-target="addSupplierModal">＋ Tambah Supplier</button>
        <?php endif; ?>
    </div>

    <div class="filter-stok-wrapper">
        <div class="search-stock-box">
            <label>Cari Supplier</label>
            <div class="search-input-inner">
                <img src="../assets/icons/search.png" alt="Search" class="table-icon-img">
                <input type="text" id="tableSearch" placeholder="Masukkan Nama Supplier atau Email...">
            </div>
        </div>
    </div>

    <div class="content-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NAMA SUPPLIER</th>
                    <th>EMAIL</th>
                    <th>NO TELEPON</th>
                    <th>ALAMAT</th>
                    <?php if ($role === 'owner'): ?>
                    <th style="text-align: center;">AKSI</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_supplier > 0): ?>
                    <?php while($row = $suppliers->fetch_assoc()): ?>
                    <tr>
                        <td>SP-0<?php echo $row['supplier_id']; ?></td>
                        <td class="text-material-name"><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['no_telp']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        
                        <?php if ($role === 'owner'): ?>
                        <td style="text-align: center;">
                            <div class="action-buttons-flex">
                                <button class="btn-action-edit btnEditSupplierTrigger" 
                                        data-id="<?php echo $row['supplier_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($row['supplier_name']); ?>"
                                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                        data-phone="<?php echo htmlspecialchars($row['no_telp']); ?>"
                                        data-address="<?php echo htmlspecialchars($row['address']); ?>">
                                    <img src="../assets/icons/edit.png" alt="Edit" class="table-icon-img">
                                </button>
                                <a href="../api/delete_supplier.php?id=<?php echo $row['supplier_id']; ?>" 
                                   class="btn-action-delete btn-delete-confirm" data-confirm-message="Apakah Anda yakin ingin menghapus data supplier ini secara permanen?">
                                    <img src="../assets/icons/delete.png" alt="Delete" class="table-icon-img"> 
                                </a>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo ($role === 'owner') ? '6' : '5'; ?>" style="text-align: center; color: var(--text-secondary);">Belum ada data supplier.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="table-footer-pagination">
            <div class="pagination-info">Menampilkan 1-<?php echo $total_supplier; ?> dari <?php echo $total_supplier; ?> data</div>
            <div class="pagination-controls">
                <button class="btn-page" disabled>&lt;</button>
                <button class="btn-page page-active">1</button>
                <button class="btn-page">&gt;</button>
            </div>
        </div>
    </div>
</div>

<?php if ($role === 'owner'): ?>
<!-- Modal Tambah Supplier -->
<div id="addSupplierModal" class="modal-backdrop">
    <div class="modal-card">
        <div class="modal-header">
            <h2>Tambah Supplier Baru</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <form action="../api/add_supplier.php" method="POST">
            <div class="modal-body">
                <div class="form-group-row">
                    <label>Nama Supplier</label>
                    <input type="text" name="supplier_name" required placeholder="Masukkan Nama Perusahaan / Perorangan">
                </div>
                <div class="form-flex-grid">
                    <div class="form-group-row">
                        <label>Email</label>
                        <input type="email" name="email" required placeholder="Contoh: info@supplier.com">
                    </div>
                    <div class="form-group-row">
                        <label>No. Telepon</label>
                        <input type="text" name="no_telp" required placeholder="Contoh: 08123456789">
                    </div>
                </div>
                <div class="form-group-row">
                    <label>Alamat Lengkap</label>
                    <input type="text" name="address" required placeholder="Masukkan alamat lengkap">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel">Batal</button>
                <button type="submit" class="btn-modal-submit">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Supplier -->
<div id="editSupplierModal" class="modal-backdrop">
    <div class="modal-card">
        <div class="modal-header">
            <h2>Edit Data Supplier</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <form action="../api/update_supplier.php" method="POST">
            <input type="hidden" name="supplier_id" id="edit_supplier_id">
            <div class="modal-body">
                <div class="form-group-row">
                    <label>Nama Supplier</label>
                    <input type="text" name="supplier_name" id="edit_supplier_name" required>
                </div>
                <div class="form-flex-grid">
                    <div class="form-group-row">
                        <label>Email</label>
                        <input type="email" name="email" id="edit_supplier_email" required>
                    </div>
                    <div class="form-group-row">
                        <label>No. Telepon</label>
                        <input type="text" name="no_telp" id="edit_supplier_phone" required>
                    </div>
                </div>
                <div class="form-group-row">
                    <label>Alamat Lengkap</label>
                    <input type="text" name="address" id="edit_supplier_address" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel">Batal</button>
                <button type="submit" class="btn-modal-submit">Perbarui Data</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

</div> </div> <script src="../assets/main.js?v=<?= time(); ?>"></script>
</body>
</html>
