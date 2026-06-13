<?php
require_once '../includes/db_connect.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'] ?? 'admin';

$where_clauses = [];
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$search_keyword = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($start_date)) {
    $where_clauses[] = "mp.purchase_date >= '" . $conn->real_escape_string($start_date) . "'";
}
if (!empty($end_date)) {
    $where_clauses[] = "mp.purchase_date <= '" . $conn->real_escape_string($end_date) . "'";
}
if (!empty($search_keyword)) {
    $sk = $conn->real_escape_string($search_keyword);
    $where_clauses[] = "(m.material_name LIKE '%$sk%' OR s.supplier_name LIKE '%$sk%')";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Ambil data pengajuan
$query_pengajuan = "
    SELECT 
        mp.purchase_id,
        mp.total,
        mp.status,
        mp.contact_method,
        mp.description,
        mp.purchase_date,
        m.material_id,
        m.material_name,
        m.unit,
        s.supplier_id,
        s.supplier_name,
        s.no_telp,
        s.email
    FROM material_purchase mp
    JOIN material m ON mp.material_id = m.material_id
    JOIN suppliers s ON mp.supplier_id = s.supplier_id
    $where_sql
    ORDER BY 
        CASE WHEN mp.status = 'pending' THEN 1 ELSE 2 END,
        mp.purchase_date DESC, 
        mp.purchase_id DESC
";
$pengajuans = $conn->query($query_pengajuan);
$total_pengajuan = $pengajuans->num_rows;

// Ambil data bahan untuk dropdown form
$materials_query = "SELECT material_id, material_name, unit, price FROM material ORDER BY material_name ASC";
$materials = $conn->query($materials_query);
$bahan_options = '';
while($row = $materials->fetch_assoc()) {
    $bahan_options .= "<option value='{$row['material_id']}' data-price='{$row['price']}'>" . htmlspecialchars($row['material_name']) . " (" . htmlspecialchars($row['unit']) . ")</option>";
}

// Ambil data supplier untuk dropdown form
$suppliers_query = "SELECT supplier_id, supplier_name, no_telp FROM suppliers ORDER BY supplier_name ASC";
$suppliers_res = $conn->query($suppliers_query);
$supplier_options = '';
while($row = $suppliers_res->fetch_assoc()) {
    $supplier_options .= "<option value='{$row['supplier_id']}'>" . htmlspecialchars($row['supplier_name']) . " - " . htmlspecialchars($row['no_telp']) . "</option>";
}
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
            <h1 class="page-title-main">Pengajuan Bahan</h1>
            <p class="page-subtitle-main">Kelola permintaan pembelian bahan baku ke supplier.</p>
        </div>
        <?php if ($role === 'admin' || $role === 'owner'): ?>
        <button class="btn-add-primary btn-open-modal" data-target="addPengajuanModal">＋ Buat Pengajuan</button>
        <?php endif; ?>
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
            <label>Cari Pengajuan</label>
            <div class="search-input-inner">
                <img src="../assets/icons/search.png" alt="Search" class="table-icon-img">
                <input type="text" name="search" placeholder="Cari Bahan atau Supplier..." value="<?php echo htmlspecialchars($search_keyword); ?>">
            </div>
        </div>

        <div class="filter-action-buttons">
            <a href="pengajuan.php" class="btn-filter-reset">Reset</a>
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
                    <th>PESAN</th>
                    <th>STATUS</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_pengajuan > 0): ?>
                    <?php while($row = $pengajuans->fetch_assoc()): ?>
                    <tr class="btnViewPengajuanTrigger" style="cursor: pointer;"
                        data-id="<?php echo $row['purchase_id']; ?>"
                        data-material="<?php echo htmlspecialchars($row['material_name']); ?>"
                        data-supplier="<?php echo htmlspecialchars($row['supplier_name']); ?>"
                        data-total="<?php echo $row['total']; ?>"
                        data-method="<?php echo htmlspecialchars($row['contact_method']); ?>"
                        data-desc="<?php echo htmlspecialchars($row['description']); ?>">
                        <td><?php echo date('d/m/Y', strtotime($row['purchase_date'])); ?></td>
                        <td class="text-material-name"><?php echo htmlspecialchars($row['material_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                        <td><?php echo number_format($row['total'], 0, ',', '.') . ' ' . $row['unit']; ?></td>
                        <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($row['description']); ?>">
                            <?php echo htmlspecialchars($row['description']); ?>
                        </td>
                        <td>
                            <?php 
                            if($row['status'] === 'pending') {
                                echo '<span style="background-color: #FEF08A; color: #854D0E; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: 500;">Pending</span>';
                            } elseif($row['status'] === 'approved') {
                                echo '<span style="background-color: #BBF7D0; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: 500;">Approved</span>';
                            } elseif($row['status'] === 'rejected') {
                                echo '<span style="background-color: #FECACA; color: #991B1B; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: 500;">Rejected</span>';
                            } elseif($row['status'] === 'cancelled') {
                                echo '<span style="background-color: #E5E7EB; color: #374151; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: 500;">Cancelled</span>';
                            }
                            ?>
                        </td>
                        <td style="text-align: center;">
                            <div class="action-buttons-flex" style="justify-content: center; gap: 8px;">

                                <?php if ($role === 'admin' && $row['status'] === 'pending'): ?>
                                    <button class="btn-action-edit btnEditPengajuanTrigger"
                                            data-id="<?php echo $row['purchase_id']; ?>"
                                            data-materialid="<?php echo $row['material_id']; ?>"
                                            data-supplierid="<?php echo $row['supplier_id']; ?>"
                                            data-total="<?php echo $row['total']; ?>"
                                            data-method="<?php echo htmlspecialchars($row['contact_method']); ?>"
                                            data-desc="<?php echo htmlspecialchars($row['description']); ?>"
                                            title="Edit">
                                        <img src="../assets/icons/edit.png" alt="Edit" class="table-icon-img">
                                    </button>
                                    <button class="btn-action-delete btnUpdateStatusTrigger"
                                            data-id="<?php echo $row['purchase_id']; ?>"
                                            data-status="cancelled"
                                            data-confirm="Apakah Anda yakin ingin membatalkan pengajuan ini?"
                                            title="Batalkan">
                                        <img src="../assets/icons/delete.png" alt="Cancel" class="table-icon-img">
                                    </button>
                                <?php endif; ?>

                                <?php if ($role === 'owner' && $row['status'] === 'pending'): ?>
                                    <button class="btn-action-edit btnUpdateStatusTrigger"
                                            data-id="<?php echo $row['purchase_id']; ?>"
                                            data-status="approved"
                                            data-method="<?php echo htmlspecialchars($row['contact_method']); ?>"
                                            data-phone="<?php echo htmlspecialchars($row['no_telp']); ?>"
                                            data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                            data-message="<?php echo htmlspecialchars($row['description']); ?>"
                                            data-confirm="Setujui pengajuan ini dan hubungi supplier via <?php echo strtoupper($row['contact_method']); ?>?"
                                            title="Approve">
                                        <img src="../assets/icons/approve.png" alt="Approve" class="table-icon-img">
                                    </button>
                                    <button class="btn-action-delete btnUpdateStatusTrigger"
                                            data-id="<?php echo $row['purchase_id']; ?>"
                                            data-status="rejected"
                                            data-confirm="Tolak pengajuan ini?"
                                            title="Reject">
                                        <img src="../assets/icons/reject.png" alt="Reject" class="table-icon-img">
                                    </button>
                                <?php endif; ?>

                                <?php if (($role === 'admin' || $role === 'owner') && $row['status'] === 'approved'): ?>
                                    <?php if ($row['contact_method'] === 'whatsapp'): ?>
                                        <a href="https://wa.me/<?php 
                                            $phone = preg_replace('/[^0-9]/', '', $row['no_telp']);
                                            if(substr($phone, 0, 1) === '0') $phone = '62' . substr($phone, 1);
                                            echo $phone;
                                        ?>?text=<?php echo urlencode($row['description']); ?>" target="_blank"
                                           style="background: #25D366; color: white; border: none; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 13px;">
                                            Kirim WA
                                        </a>
                                    <?php else: ?>
                                        <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>?subject=Permintaan%20Bahan%20Baku&body=<?php echo urlencode($row['description']); ?>" target="_blank"
                                           style="background: #3B82F6; color: white; border: none; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 13px;">
                                            Kirim Email
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 40px 0;">Belum ada data pengajuan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="table-footer-pagination">
            <div class="pagination-info">Menampilkan 1-<?php echo $total_pengajuan; ?> dari <?php echo $total_pengajuan; ?> data</div>
            <div class="pagination-controls">
                <button class="btn-page" disabled>&lt;</button>
                <button class="btn-page page-active">1</button>
                <button class="btn-page" disabled>&gt;</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Pengajuan -->
<div id="viewPengajuanModal" class="modal-backdrop">
    <div class="modal-card">
        <div class="modal-header">
            <h2>Detail Pengajuan</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group-row">
                <label>Bahan Baku</label>
                <input type="text" id="viewMaterial" readonly style="background: #f3f4f6;">
            </div>
            <div class="form-group-row">
                <label>Supplier</label>
                <input type="text" id="viewSupplier" readonly style="background: #f3f4f6;">
            </div>
            <div class="form-flex-grid">
                <div class="form-group-row">
                    <label>Jumlah</label>
                    <input type="text" id="viewTotal" readonly style="background: #f3f4f6;">
                </div>
                <div class="form-group-row">
                    <label>Metode Kontak</label>
                    <input type="text" id="viewContactMethod" readonly style="background: #f3f4f6; text-transform: uppercase;">
                </div>
            </div>
            <div class="form-group-row">
                <label>Pesan untuk Supplier</label>
                <textarea id="viewDesc" rows="4" readonly style="background: #f3f4f6; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%;"></textarea>
            </div>
        </div>
    </div>
</div>

<?php if ($role === 'admin' || $role === 'owner'): ?>
<!-- Modal Tambah Pengajuan -->
<div id="addPengajuanModal" class="modal-backdrop">
    <div class="modal-card">
        <div class="modal-header">
            <h2>Buat Pengajuan Baru</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <form id="formAddPengajuan" action="../api/add_pengajuan.php" method="POST">
            <div class="modal-body">
                <div class="form-group-row">
                    <label for="addMaterial">Bahan Baku</label>
                    <select id="addMaterial" name="material_id" required>
                        <option value="">Pilih Bahan</option>
                        <?php echo $bahan_options; ?>
                    </select>
                </div>
                <div class="form-group-row">
                    <label for="addSupplier">Pilih Supplier</label>
                    <select id="addSupplier" name="supplier_id" required>
                        <option value="">Pilih Supplier</option>
                        <?php echo $supplier_options; ?>
                    </select>
                </div>
                <div class="form-flex-grid">
                    <div class="form-group-row">
                        <label for="addTotal">Jumlah Pengajuan</label>
                        <input type="number" id="addTotal" name="total" required min="1" placeholder="Misal: 100">
                        <div id="addPriceEstimate" style="font-size: 13px; color: #475569; margin-top: 6px; font-weight: 500;">Perkiraan Harga: Rp 0</div>
                    </div>
                    <div class="form-group-row">
                        <label for="addContactMethod">Metode Kontak</label>
                        <select id="addContactMethod" name="contact_method" required>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="email">Email</option>
                        </select>
                    </div>
                </div>
                <div class="form-group-row">
                    <label for="addDesc">Pesan untuk Supplier</label>
                    <textarea id="addDesc" name="description" rows="4" required placeholder="Tuliskan pesan yang akan dikirim ke supplier..." style="padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; font-family: inherit;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel">Batal</button>
                <button type="submit" class="btn-modal-submit">Ajukan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pengajuan -->
<div id="editPengajuanModal" class="modal-backdrop">
    <div class="modal-card">
        <div class="modal-header">
            <h2>Edit Pengajuan</h2>
            <button type="button" class="close-modal-btn">&times;</button>
        </div>
        <form id="formEditPengajuan" action="../api/update_pengajuan.php" method="POST">
            <input type="hidden" id="editPurchaseId" name="purchase_id">
            <div class="modal-body">
                <div class="form-group-row">
                    <label for="editMaterial">Bahan Baku</label>
                    <select id="editMaterial" name="material_id" required>
                        <?php echo $bahan_options; ?>
                    </select>
                </div>
                <div class="form-group-row">
                    <label for="editSupplier">Supplier</label>
                    <select id="editSupplier" name="supplier_id" required>
                        <?php echo $supplier_options; ?>
                    </select>
                </div>
                <div class="form-flex-grid">
                    <div class="form-group-row">
                        <label for="editTotal">Jumlah Pengajuan</label>
                        <input type="number" id="editTotal" name="total" required min="1">
                        <div id="editPriceEstimate" style="font-size: 13px; color: #475569; margin-top: 6px; font-weight: 500;">Perkiraan Harga: Rp 0</div>
                    </div>
                    <div class="form-group-row">
                        <label for="editContactMethod">Metode Kontak</label>
                        <select id="editContactMethod" name="contact_method" required>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="email">Email</option>
                        </select>
                    </div>
                </div>
                <div class="form-group-row">
                    <label for="editDesc">Pesan untuk Supplier</label>
                    <textarea id="editDesc" name="description" rows="4" required style="padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; font-family: inherit;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel">Batal</button>
                <button type="submit" class="btn-modal-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

</div> 
</div> 
<script src="../assets/main.js?v=<?= time(); ?>"></script>
</body>
</html>
