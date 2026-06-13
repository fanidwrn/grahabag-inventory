<?php
require_once '../includes/db_connect.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Ambil data bahan baku
$materials_res = $conn->query("SELECT material_id, material_name, unit FROM material ORDER BY material_name ASC");
$materials_data = [];
while ($mat = $materials_res->fetch_assoc()) {
    $materials_data[] = $mat;
}

// Ambil data supplier
$suppliers_res = $conn->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name ASC");
$suppliers_data = [];
while ($sup = $suppliers_res->fetch_assoc()) {
    $suppliers_data[] = $sup;
}

// Menangani Filter Pencarian dan Tanggal 
$where_clauses = [];
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($start_date)) {
    $where_clauses[] = "s.date_stock_in >= '" . $conn->real_escape_string($start_date) . "'";
}
if (!empty($end_date)) {
    $where_clauses[] = "s.date_stock_in <= '" . $conn->real_escape_string($end_date) . "'";
}
if (!empty($search_keyword)) {
    $sk = $conn->real_escape_string($search_keyword);
    $where_clauses[] = "(m.material_name LIKE '%$sk%' OR s.material_id LIKE '%$sk%' OR s.description_in LIKE '%$sk%')";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Ambil data Stok Masuk
$query_stock = "SELECT s.*, m.material_name, m.unit, sp.supplier_name 
                FROM stock_in s 
                JOIN material m ON s.material_id = m.material_id 
                LEFT JOIN suppliers sp ON s.supplier_id = sp.supplier_id
                $where_sql 
                ORDER BY s.date_stock_in DESC, s.stock_in_id DESC";
$stock_entries = $conn->query($query_stock);
$total_rows = $stock_entries->num_rows;
?>

<?php if (isset($_SESSION['toast'])): ?>
    <div id="toastNotification" class="toast toast-<?php echo $_SESSION['toast']['type']; ?>">
        <?php echo $_SESSION['toast']['message']; ?>
    </div>
    <?php unset($_SESSION['toast']); ?>
<?php endif; ?>

<div class="stok-masuk-container">
    <div class="page-header-action">
        <div class="header-left">
            <h1 class="page-title-main">Riwayat Stok Masuk</h1>
            <p class="page-subtitle-main">Catatan penerimaan bahan baku dari supplier.</p>
        </div>
        <button class="btn-add-primary btn-open-modal" data-target="addStockModal">＋ Tambah Stok Masuk</button>
    </div>

    <form action="" method="GET" class="filter-stok-wrapper">
        <div class="filter-date-group">
            <div class="date-input-box">
                <label>Dari Tanggal</label>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            <div class="date-input-box">
                <label>Sampai Tanggal</label>
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
        </div>
        
        <div class="search-stock-box">
            <label>Cari Bahan Baku</label>
            <div class="search-input-inner">
                <img src="../assets/icons/search.png" alt="Search" class="table-icon-img">
                <input type="text" name="search" placeholder="Masukkan Nama Bahan..." value="<?php echo htmlspecialchars($search_keyword); ?>">
            </div>
        </div>

        <div class="filter-action-buttons">
            <a href="stok_masuk.php" class="btn-filter-reset">Reset</a>
            <button type="submit" class="btn-filter-apply">Terapkan</button>
        </div>
    </form>

    <div class="content-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>TANGGAL</th>
                    <th>NAMA BAHAN</th>
                    <th>SUPPLIER</th>
                    <th>JUMLAH</th>
                    <th>SATUAN</th>
                    <th>FOTO</th>
                    <th>CATATAN</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_rows > 0): ?>
                    <?php while($row = $stock_entries->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($row['date_stock_in'])); ?></td>
                        <td class="text-material-name"><?php echo htmlspecialchars($row['material_name']); ?></td>
                        <td><?php echo !empty($row['supplier_name']) ? htmlspecialchars($row['supplier_name']) : '-'; ?></td>
                        <td><?php echo number_format($row['total_in']); ?></td>
                        <td><?php echo htmlspecialchars($row['unit']); ?></td>
                        <td>
                            <?php if (!empty($row['photo'])): ?>
                                <a href="../uploads/stock_in/<?php echo htmlspecialchars($row['photo']); ?>" target="_blank" style="color: #3b82f6; text-decoration: underline; font-size: 13px;">Lihat Foto</a>
                            <?php else: ?>
                                <span style="color: #94a3b8; font-size: 13px;">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-description-cell"><?php echo !empty($row['description_in']) ? htmlspecialchars($row['description_in']) : '-'; ?></td>
                        <td style="text-align: center;">
                            <div class="action-buttons-flex">
                                <button class="btn-action-edit btnEditStockTrigger" 
                                        data-id="<?php echo $row['stock_in_id']; ?>"
                                        data-material="<?php echo $row['material_id']; ?>"
                                        data-supplier="<?php echo $row['supplier_id']; ?>"
                                        data-date="<?php echo $row['date_stock_in']; ?>"
                                        data-total="<?php echo $row['total_in']; ?>"
                                        data-desc="<?php echo htmlspecialchars($row['description_in']); ?>">
                                    <img src="../assets/icons/edit.png" alt="Edit" class="table-icon-img">
                                </button>
                                <a href="../api/delete_stock_in.php?id=<?php echo $row['stock_in_id']; ?>" 
                                   class="btn-action-delete btn-delete-confirm" data-confirm-message="Peringatan! Menghapus data ini akan otomatis mengurangi kembali jumlah stok pada master data Bahan Baku. Lanjutkan?">
                                    <img src="../assets/icons/delete.png" alt="Delete" class="table-icon-img">
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-secondary); padding: 40px 0;">Belum ada data stok masuk.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="table-footer-pagination">
            <div class="pagination-info">Menampilkan 1-<?php echo $total_rows; ?> dari <?php echo $total_rows; ?> data</div>
            <div class="pagination-controls">
                <button class="btn-page" disabled>&lt;</button>
                <button class="btn-page page-active">1</button>
                <button class="btn-page">2</button>
                <button class="btn-page">&gt;</button>
            </div>
        </div>
    </div>
</div>

<div id="addStockModal" class="modal-backdrop">
    <div class="modal-card">
        <div class="modal-header">
            <h2>Tambah Stok Masuk Baru</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <form action="../api/add_stock_in.php" method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-group-row">
                    <label>Pilih Bahan Baku</label>
                    <select name="material_id" required>
                        <option value="">-- Pilih Material --</option>
                        <?php foreach($materials_data as $mat): ?>
                            <option value="<?php echo $mat['material_id']; ?>">
                                <?php echo htmlspecialchars($mat['material_name'] . ' ('.$mat['unit'].')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group-row">
                    <label>Pilih Pemasok (Supplier)</label>
                    <select name="supplier_id">
                        <option value="">-- Pilih Pemasok --</option>
                        <?php foreach($suppliers_data as $sup): ?>
                            <option value="<?php echo $sup['supplier_id']; ?>">
                                <?php echo htmlspecialchars($sup['supplier_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-flex-grid">
                    <div class="form-group-row">
                        <label>Tanggal Penerimaan</label>
                        <input type="date" name="date_stock_in" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group-row">
                        <label>Jumlah Masuk</label>
                        <input type="number" name="total_in" placeholder="0" min="1" required>
                    </div>
                </div>
                <div class="form-flex-grid">
                    <div class="form-group-row">
                        <label>Foto Dokumentasi (Opsional)</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>
                </div>
                <div class="form-group-row">
                    <label>Catatan</label>
                    <input type="text" name="description_in" placeholder="Contoh: Diterima oleh Pak Budi">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel">Batal</button>
                <button type="submit" class="btn-modal-submit">Simpan Stok</button>
            </div>
        </form>
    </div>
</div>

<div id="editStockModal" class="modal-backdrop">
    <div class="modal-card">
        <div class="modal-header">
            <h2>Edit Record Stok Masuk</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <form action="../api/update_stock_in.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="stock_in_id" id="edit_stock_in_id">
            <div class="modal-body">
                <div class="form-group-row">
                    <label>Bahan Baku (Tetap)</label>
                    <select name="material_id" id="edit_stock_material_id" required>
                        <?php foreach($materials_data as $mat): ?>
                            <option value="<?php echo $mat['material_id']; ?>"><?php echo htmlspecialchars($mat['material_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group-row">
                    <label>Pilih Pemasok (Supplier)</label>
                    <select name="supplier_id" id="edit_stock_supplier_id">
                        <option value="">-- Pilih Pemasok --</option>
                        <?php foreach($suppliers_data as $sup): ?>
                            <option value="<?php echo $sup['supplier_id']; ?>"><?php echo htmlspecialchars($sup['supplier_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-flex-grid">
                    <div class="form-group-row">
                        <label>Tanggal Penerimaan</label>
                        <input type="date" name="date_stock_in" id="edit_stock_date" required>
                    </div>
                    <div class="form-group-row">
                        <label>Jumlah Masuk</label>
                        <input type="number" name="total_in" id="edit_stock_total" min="1" required>
                    </div>
                </div>
                <div class="form-flex-grid">
                    <div class="form-group-row">
                        <label>Ganti Foto (Biarkan kosong jika tidak diubah)</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>
                </div>
                <div class="form-group-row">
                    <label>Catatan</label>
                    <input type="text" name="description_in" id="edit_stock_desc">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel">Batal</button>
                <button type="submit" class="btn-modal-submit">Perbarui Record</button>
            </div>
        </form>
    </div>
</div>

</div>
</div>
<script src="../assets/main.js?v=<?= time(); ?>"></script>
</body>
</html>