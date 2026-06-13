<?php
require_once '../includes/db_connect.php';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once '../api/handle_filter_laporan.php';
?>

<div class="dashboard-header">
    <h1 class="page-title">Laporan</h1>
    <p class="page-subtitle-main">Laporan mingguan atau bulanan stok bahan baku.</p>
</div>

<form method="GET" action="laporan.php" class="filter-stok-wrapper">
    <div class="date-input-box">
        <label for="jenis_laporan">Jenis Laporan</label>
        <select name="jenis_laporan" id="jenis_laporan" required style="width: 200px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 11px; outline: none;">
            <option value="Bulanan" <?php echo $jenis_laporan == 'Bulanan' ? 'selected' : ''; ?>>Bulanan</option>
            <option value="Mingguan" <?php echo $jenis_laporan == 'Mingguan' ? 'selected' : ''; ?>>Mingguan</option>
        </select>
    </div>
    
    <div class="filter-date-group" style="flex-grow: 1; display: flex; gap: 20px;">
        <div class="date-input-box" id="box_bulan" style="display: flex;">
            <label for="bulan">Pilih Bulan</label>
            <select name="bulan" id="bulan" style="width: 200px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 11px; outline: none;">
                <option value="">-- Semua Bulan --</option>
                <?php
                $months = ['1'=>'Januari', '2'=>'Februari', '3'=>'Maret', '4'=>'April', '5'=>'Mei', '6'=>'Juni', '7'=>'Juli', '8'=>'Agustus', '9'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
                foreach($months as $num => $name) {
                    $sel = ($val_bulan == $num) ? 'selected' : '';
                    echo "<option value=\"$num\" $sel>$name</option>";
                }
                ?>
            </select>
        </div>
        <div class="date-input-box" id="box_tahun" style="display: flex;">
            <label for="tahun">Pilih Tahun</label>
            <select name="tahun" id="tahun" required style="width: 200px; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 11px; outline: none;">
                <?php
                $current_y = date('Y');
                for($y = $current_y - 2; $y <= $current_y + 1; $y++) {
                    $sel = ($val_tahun == $y) ? 'selected' : '';
                    echo "<option value=\"$y\" $sel>$y</option>";
                }
                ?>
            </select>
        </div>
    </div>

    <div class="filter-action-buttons">
        <button type="submit" name="filter" class="btn-filter-apply">Terapkan</button>
    </div>
</form>

<?php if (count($report_rows) > 0): ?>
<div class="content-table-wrapper">
    <div class="table-header-actions">
        <h3>Laporan <?php echo htmlspecialchars($jenis_laporan); ?> - <?php echo htmlspecialchars($header_title_suffix); ?></h3>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>JENIS LAPORAN</th>
                <th>BULAN</th>
                <th>RENTANG WAKTU</th>
                <th>AKSI</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $has_data = false;
            foreach ($report_rows as $row): 
                if ($row['start'] <= date('Y-m-d')):
                    $has_data = true;
            ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($row['judul']); ?></strong></td>
                <td><?php echo htmlspecialchars($row['bulan_label']); ?></td>
                <td><?php echo $row['rentang']; ?></td>
                <td>
                    <div class="action-buttons-flex">
                        <a href="cetak_laporan.php?jenis=<?php echo urlencode($row['judul']); ?>&start=<?php echo urlencode($row['start']); ?>&end=<?php echo urlencode($row['end']); ?>" target="_blank" class="btn-add-primary" style="text-decoration: none;">Download PDF</a>
                    </div>
                </td>
            </tr>
            <?php 
                endif;
            endforeach; 
            
            if (!$has_data):
            ?>
            <tr>
                <td colspan="4" style="text-align: center; color: #64748b; font-style: italic; padding: 24px;">Belum ada data laporan.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script src="../assets/main.js?v=<?= time(); ?>"></script>
</body>
</html>
