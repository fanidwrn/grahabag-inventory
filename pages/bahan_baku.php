<?php
require_once '../includes/db_connect.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Ambil data kategori
$categories_res = $conn->query("SELECT * FROM category ORDER BY category_name ASC");
$categories_data = [];
while ($cat = $categories_res->fetch_assoc()) {
    $categories_data[] = $cat;
}

// Ambil data bahan baku utama
$query_material = "SELECT m.*, c.category_name FROM material m 
                   JOIN category c ON m.category_id = c.category_id 
                   ORDER BY m.material_id ASC";
$materials = $conn->query($query_material);
$total_bahan = $materials->num_rows;
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
            <h1 class="page-title-main">Kelola Bahan Baku</h1>
            <p class="page-subtitle-main">Daftar inventaris material produksi.</p>
        </div>
        <button class="btn-add-primary btn-open-modal" data-target="addMaterialModal">＋ Tambah Bahan</button>
    </div>

    <div class="filter-stok-wrapper">
        <div class="search-stock-box">
            <label>Cari Bahan Baku</label>
            <div class="search-input-inner">
                <img src="../assets/icons/search.png" alt="Search" class="table-icon-img">
                <input type="text" id="bahanSearch" placeholder="Masukkan Nama Bahan atau Kategori...">
            </div>
        </div>
        <div class="filter-date-group">
            <div class="date-input-box">
                <label style="margin-bottom: 5px;">Kategori</label>
                <select id="bahanCategory" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; outline: none; background: white; font-size: 11px;">
                    <option value="">Semua Kategori</option>
                    <?php foreach($categories_data as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['category_name']); ?>">
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="content-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NAMA BAHAN</th>
                    <th>KATEGORI</th>
                    <th>STOK</th>
                    <th>SATUAN</th>
                    <th>HARGA SATUAN</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_bahan > 0): ?>
                    <?php while($row = $materials->fetch_assoc()): 
                        $stok = $row['stock'];
                        $min_stok = $row['minimum_stock'];
                        
                        if ($stok <= $min_stok) {
                            $badge_class = 'badge-critical'; // Merah
                        } elseif ($stok <= ($min_stok * 1.5)) {
                            $badge_class = 'badge-warning';  // Oren
                        } else {
                            $badge_class = 'badge-safe';     // Hijau
                        }
                    ?>
                    <tr>
                        <td>BB-0<?php echo $row['material_id']; ?></td>
                        <td class="text-material-name"><?php echo htmlspecialchars($row['material_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td>
                            <span class="pill-stok <?php echo $badge_class; ?>">
                                <?php echo number_format($stok); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['unit']); ?></td>
                        <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                        <td style="text-align: center;">
                            <div class="action-buttons-flex">
                                <button class="btn-action-edit btnEditTrigger" 
                                        data-id="<?php echo $row['material_id']; ?>"
                                        data-name="<?php echo htmlspecialchars($row['material_name']); ?>"
                                        data-category="<?php echo $row['category_id']; ?>"
                                        data-stock="<?php echo $row['stock']; ?>"
                                        data-unit="<?php echo htmlspecialchars($row['unit']); ?>"
                                        data-min="<?php echo $row['minimum_stock']; ?>"
                                        data-price="<?php echo $row['price']; ?>">
                                    <img src="../assets/icons/edit.png" alt="Edit" class="table-icon-img">
                                </button>
                                <a href="../api/delete_material.php?id=<?php echo $row['material_id']; ?>" 
                                   class="btn-action-delete btn-delete-confirm" data-confirm-message="Apakah Anda yakin ingin menghapus data bahan baku ini secara permanen?">
                                    <img src="../assets/icons/delete.png" alt="Delete" class="table-icon-img"> 
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-secondary);">Belum ada data bahan baku.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="table-footer-pagination">
            <div class="pagination-info">Menampilkan 1-<?php echo $total_bahan; ?> dari <?php echo $total_bahan; ?> data</div>
            <div class="pagination-controls">
                <button class="btn-page" disabled>&lt;</button>
                <button class="btn-page page-active">1</button>
                <button class="btn-page">2</button>
                <button class="btn-page">3</button>
                <span class="page-dots">...</span>
                <button class="btn-page">&gt;</button>
            </div>
        </div>
    </div>
</div>

<div id="addMaterialModal" class="modal-backdrop">
    <div class="modal-card">
        <div class="modal-header">
            <h2>Tambah Bahan Baku Baru</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <form action="../api/add_material.php" method="POST">
            <div class="modal-body">
                <div class="form-group-row">
                    <label>Nama Bahan Baku</label>
                    <input type="text" name="material_name" placeholder="Contoh: Kain Cordura Hitam" required>
                </div>
                <div class="form-group-row">
                    <label>Kategori</label>
                    <select name="category_id" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach($categories_data as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-flex-grid">
                    <div class="form-group-row">
                        <label>Stok Awal</label>
                        <input type="number" name="stock" value="0" min="0" required>
                    </div>
                    <div class="form-group-row">
                        <label>Satuan</label>
                        <input type="text" name="unit" placeholder="Meter / Roll / Pcs" required>
                    </div>
                </div>
                <div class="form-flex-grid">
                    <div class="form-group-row">
                        <label>Stok Minimum</label>
                        <input type="number" name="minimum_stock" value="10" min="0" required>
                    </div>
                    <div class="form-group-row">
                        <label>Harga Satuan (Rp)</label>
                        <input type="number" name="price" placeholder="0" min="0" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel">Batal</button>
                <button type="submit" class="btn-modal-submit">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<div id="editMaterialModal" class="modal-backdrop">
    <div class="modal-card">
        <div class="modal-header">
            <h2>Edit Bahan Baku</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <form action="../api/update_material.php" method="POST">
            <input type="hidden" name="material_id" id="edit_material_id">
            
            <div class="modal-body">
                <div class="form-group-row">
                    <label>Nama Bahan Baku</label>
                    <input type="text" name="material_name" id="edit_material_name" required>
                </div>
                <div class="form-group-row">
                    <label>Kategori</label>
                    <select name="category_id" id="edit_category_id" required>
                        <?php foreach($categories_data as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-flex-grid">
                    <div class="form-group-row">
                        <label>Stok</label>
                        <input type="number" name="stock" id="edit_stock" min="0" required>
                    </div>
                    <div class="form-group-row">
                        <label>Satuan</label>
                        <input type="text" name="unit" id="edit_unit" required>
                    </div>
                </div>
                <div class="form-flex-grid">
                    <div class="form-group-row">
                        <label>Stok Minimum</label>
                        <input type="number" name="minimum_stock" id="edit_minimum_stock" min="0" required>
                    </div>
                    <div class="form-group-row">
                        <label>Harga Satuan (Rp)</label>
                        <input type="number" name="price" id="edit_price" min="0" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel">Batal</button>
                <button type="submit" class="btn-modal-submit">Perbarui Data</button>
            </div>
        </form>
    </div>
</div>

</div> </div> 
<script src="../assets/main.js?v=<?= time(); ?>"></script>
</body>
</html>