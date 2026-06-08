<?php
require_once '../includes/db_connect.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Ambil master data bahan baku untuk opsi pilihan di dalam modal dinamis
$materials_res = $conn->query("SELECT material_id, material_name, unit, stock FROM material ORDER BY material_name ASC");
$materials_data = [];
while ($mat = $materials_res->fetch_assoc()) {
    $materials_data[] = $mat;
}

// Ambil parameter filter tanggal jika dikirimkan oleh user
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

$where_clauses = [];
if (!empty($start_date)) {
    $where_clauses[] = "s.date_stock_out >= '" . $conn->real_escape_string($start_date) . "'";
}
if (!empty($end_date)) {
    $where_clauses[] = "s.date_stock_out <= '" . $conn->real_escape_string($end_date) . "'";
}
if (!empty($search_keyword)) {
    $sk = $conn->real_escape_string($search_keyword);
    $where_clauses[] = "(m.material_name LIKE '%$sk%' OR s.material_id LIKE '%$sk%' OR s.description_out LIKE '%$sk%')";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Ambil riwayat stok keluar sesuai ERD (Join ke tabel material)
$query_stock = "SELECT s.*, m.material_name, m.unit 
                FROM stock_out s 
                JOIN material m ON s.material_id = m.material_id 
                $where_sql 
                ORDER BY s.date_stock_out DESC, s.stock_out_id DESC";
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
            <h1 class="page-title-main">Riwayat Stok Keluar</h1>
            <p class="page-subtitle-main">Catatan penggunaan bahan baku untuk produksi.</p>
        </div>
        <button class="btn-add-primary btn-open-modal" data-target="addStockOutModal">＋ Tambah Stok Keluar</button>
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
                <input type="text" name="search" placeholder="Masukkan Kode atau Nama Bahan..." value="<?php echo htmlspecialchars($search_keyword); ?>">
            </div>
        </div>

        <div class="filter-action-buttons">
            <a href="stok_keluar.php" class="btn-filter-reset">Reset</a>
            <button type="submit" class="btn-filter-apply">Terapkan</button>
        </div>
    </form>

    <div class="content-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>TANGGAL</th>
                    <th>ID BAHAN</th>
                    <th>NAMA BAHAN</th>
                    <th style="text-align: right;">JUMLAH</th>
                    <th>SATUAN</th>
                    <th>BUKTI</th>
                    <th>CATATAN</th>
                    <th style="text-align: center;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_rows > 0): ?>
                    <?php while($row = $stock_entries->fetch_assoc()): 
                        // Jika format lama, ambil catatannya saja
                        $raw_desc = $row['description_out'];
                        $catatan = $raw_desc;
                        if (strpos($raw_desc, ' | ') !== false) {
                            $parts = explode(' | ', $raw_desc);
                            $catatan = str_replace('Catatan: ', '', $parts[1]);
                        }
                        
                        $dt = strtotime($row['date_stock_out']);
                        $months = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
                        $display_date = date('d', $dt) . ' ' . $months[date('n', $dt)-1] . ' ' . date('Y', $dt);
                    ?>
                    <tr>
                        <td><?php echo $display_date; ?></td>
                        <td class="text-code-accent">BB-0<?php echo $row['material_id']; ?></td>
                        <td class="text-material-name"><?php echo htmlspecialchars($row['material_name']); ?></td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a;"><?php echo number_format($row['total_out']); ?></td>
                        <td><?php echo htmlspecialchars($row['unit']); ?></td>
                        <td>
                            <?php if (!empty($row['photo'])): ?>
                                <a href="../uploads/stock_out/<?php echo htmlspecialchars($row['photo']); ?>" target="_blank" style="color: #3b82f6; text-decoration: underline; font-size: 13px;">Lihat Foto</a>
                            <?php else: ?>
                                <span style="color: #94a3b8; font-size: 13px;">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-description-cell"><?php echo htmlspecialchars($catatan); ?></td>
                        <td style="text-align: center;">
                            <div class="action-buttons-flex">
                                <button class="btn-action-edit btnEditStockOutTrigger" 
                                        data-id="<?php echo $row['stock_out_id']; ?>"
                                        data-material="<?php echo $row['material_id']; ?>"
                                        data-date="<?php echo $row['date_stock_out']; ?>"
                                        data-total="<?php echo $row['total_out']; ?>"
                                        data-catatan="<?php echo htmlspecialchars($catatan); ?>">
                                    <img src="../assets/icons/edit.png" alt="Edit" class="table-icon-img">
                                </button>
                                <a href="../api/delete_stock_out.php?id=<?php echo $row['stock_out_id']; ?>" class="btn-action-delete btn-delete-confirm" data-confirm-message="Menghapus data pengeluaran ini akan otomatis mengembalikan jumlah kuantitas stok ke master data Bahan Baku. Lanjutkan?">
                                    <img src="../assets/icons/delete.png" alt="Delete" class="table-icon-img">
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 40px 0;">Tidak ada catatan pengeluaran bahan baku pada periode ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="table-footer-pagination">
            <div class="pagination-info">Menampilkan 1-<?php echo $total_rows; ?> dari <?php echo $total_rows; ?> data</div>
            <div class="pagination-controls">
                <button class="btn-page" disabled>&lt;</button>
                <button class="btn-page page-active">1</button>
                <button class="btn-page">&gt;</button>
            </div>
        </div>
    </div>
</div>

<div id="addStockOutModal" class="modal-backdrop">
    <div class="modal-card" style="width: 700px; max-width: 90%;">
        <div class="modal-header">
            <h2>Form Pengeluaran Bahan Baku</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <form action="../api/add_stock_out.php" method="POST" enctype="multipart/form-data">
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                
                <div class="form-flex-grid">
                    <div class="form-group-row">
                        <label>Tanggal Keluar</label>
                        <input type="date" name="date_stock_out" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group-row">
                        <label>Foto Dokumentasi (Opsional)</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>
                </div>
                
                <div class="form-group-row">
                    <label>Tujuan / Keterangan Batch</label>
                    <input type="text" name="catatan_umum" placeholder="Contoh: Produksi Kemeja Batch X" required>
                </div>
                
                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h3 style="font-size: 14px; font-weight: 600; color: #1e293b;">Daftar Material Keluar</h3>
                    <button type="button" class="btn-add-row-item" id="btnAddNewRow">+ Tambah Baris Bahan</button>
                </div>

                <div id="dynamicItemContainer">
                    <div class="bulk-item-row" style="display: flex; gap: 15px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 10px; align-items: center;">
                        <div style="flex-grow: 1;">
                            <label class="sub-label" style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Nama Bahan Baku</label>
                            <select name="material_ids[]" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1;">
                                <option value="">-- Pilih Bahan Baku --</option>
                                <?php foreach($materials_data as $mat): ?>
                                    <option value="<?php echo $mat['material_id']; ?>">
                                        <?php echo htmlspecialchars($mat['material_name'] . ' (Stok Tersedia: ' . $mat['stock'] . ' ' . $mat['unit'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="width: 130px;">
                            <label class="sub-label" style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">Jumlah Keluar</label>
                            <input type="number" name="total_outs[]" placeholder="0" min="1" required style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1;">
                        </div>
                        <div style="align-self: center; margin-top: 20px;">
                            <button type="button" class="btn-delete-row-item remove-row-trigger" style="visibility: hidden; width: 28px; height: 28px; font-size: 12px; display: flex; align-items: center; justify-content: center; background: #fee2e2; color: #ef4444; border: none; border-radius: 6px; cursor: pointer;">✕</button>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel">Batal</button>
                <button type="submit" class="btn-modal-submit" style="background-color: #10b981;">Proses Keluar</button>
            </div>
        </form>
    </div>
</div>

<div id="editStockOutModal" class="modal-backdrop">
    <div class="modal-card">
        <div class="modal-header">
            <h2>Edit Record Stok Keluar</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <form action="../api/update_stock_out.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="stock_out_id" id="edit_stock_out_id">
            <div class="modal-body">
                <div class="form-group-row">
                    <label>Bahan Baku (Tetap)</label>
                    <select name="material_id" id="edit_stock_out_material_id" required>
                        <?php foreach($materials_data as $mat): ?>
                            <option value="<?php echo $mat['material_id']; ?>"><?php echo htmlspecialchars($mat['material_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-flex-grid">
                    <div class="form-group-row">
                        <label>Tanggal Keluar</label>
                        <input type="date" name="date_stock_out" id="edit_stock_out_date" required>
                    </div>
                    <div class="form-group-row">
                        <label>Jumlah Keluar</label>
                        <input type="number" name="total_out" id="edit_stock_out_total" min="1" required>
                    </div>
                </div>
                <div class="form-group-row">
                    <label>Ganti Foto (Biarkan kosong jika tidak diubah)</label>
                    <input type="file" name="photo" accept="image/*">
                </div>
                <div class="form-group-row">
                    <label>Tujuan / Keterangan Batch</label>
                    <input type="text" name="catatan_umum" id="edit_stock_out_catatan" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel">Batal</button>
                <button type="submit" class="btn-modal-submit" style="background-color: #10b981;">Perbarui Record</button>
            </div>
        </form>
    </div>
</div>

</div>
</div>
<script>
    const masterMaterials = <?php echo json_encode($materials_data); ?>;
</script>
<script src="../assets/main.js?v=<?= time(); ?>"></script>
</body>
</html>