<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once '../api/get_laporan.php';

$jenis = $_GET['jenis'] ?? 'Bulanan';
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');

$data_laporan = getLaporanData($conn, $start, $end);
$res_in = $data_laporan['in'];
$res_out = $data_laporan['out'];
$res_purchase = $data_laporan['purchase'];
$res_material = $data_laporan['material'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan</title>
    <link rel="stylesheet" href="../assets/style.css?v=<?= time(); ?>">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            var element = document.body;
            <?php 
                $bulan_id = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
                $bulan_nama = $bulan_id[date('m', strtotime($start))];
                $tahun = date('Y', strtotime($start));
                $nama_file = "Laporan_" . htmlspecialchars($jenis) . "_" . $bulan_nama . "_" . $tahun . ".pdf";
            ?>
            var opt = {
                margin:       10,
                filename:     '<?php echo $nama_file; ?>',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
            };
            
            // Sembunyikan elemen yang tidak perlu jika ada, lalu generate
            html2pdf().set(opt).from(element).save().then(function() {
                setTimeout(function() { window.close(); }, 1500); // Tutup tab otomatis setelah selesai
            });
        }
    </script>
</head>
<body class="print-body" onload="downloadPDF()">

    <div class="print-header">
        <h1>Laporan <?php echo htmlspecialchars($jenis); ?> Inventory Bahan Baku GRAHABAG</h1>
        <p>Periode: <?php echo date('d F Y', strtotime($start)) . ' s/d ' . date('d F Y', strtotime($end)); ?></p>
    </div>

    <!-- Tabel Barang Masuk -->
    <h2 class="print-section-title">1. Laporan Barang Masuk</h2>
    <?php if ($res_in->num_rows > 0): ?>
    <table class="print-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="20%">Bahan Baku</th>
                <th width="10%">Jumlah</th>
                <th width="20%">Supplier</th>
                <th width="15%">Pencatat</th>
                <th width="15%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while($row = $res_in->fetch_assoc()): ?>
            <tr>
                <td class="print-text-center"><?php echo $no++; ?></td>
                <td><?php echo date('d-m-Y', strtotime($row['date_stock_in'])); ?></td>
                <td><?php echo htmlspecialchars($row['material_name']); ?></td>
                <td class="print-text-right"><?php echo number_format($row['total_in']) . ' ' . $row['unit']; ?></td>
                <td><?php echo htmlspecialchars($row['supplier_name'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['pendaftar']); ?></td>
                <td><?php echo htmlspecialchars($row['description_in']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Tidak ada data barang masuk pada periode ini.</p>
    <?php endif; ?>

    <!-- Tabel Barang Keluar -->
    <h2 class="print-section-title">2. Laporan Barang Keluar</h2>
    <?php if ($res_out->num_rows > 0): ?>
    <table class="print-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="25%">Bahan Baku</th>
                <th width="15%">Jumlah</th>
                <th width="20%">Pencatat</th>
                <th width="20%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while($row = $res_out->fetch_assoc()): ?>
            <tr>
                <td class="print-text-center"><?php echo $no++; ?></td>
                <td><?php echo date('d-m-Y', strtotime($row['date_stock_out'])); ?></td>
                <td><?php echo htmlspecialchars($row['material_name']); ?></td>
                <td class="print-text-right"><?php echo number_format($row['total_out']) . ' ' . $row['unit']; ?></td>
                <td><?php echo htmlspecialchars($row['pendaftar']); ?></td>
                <td><?php echo htmlspecialchars($row['description_out']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Tidak ada data barang keluar pada periode ini.</p>
    <?php endif; ?>

    <!-- Tabel Pengajuan Supplier -->
    <h2 class="print-section-title">3. Laporan Pengajuan Supplier</h2>
    <?php if ($res_purchase->num_rows > 0): ?>
    <table class="print-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="20%">Bahan Baku</th>
                <th width="10%">Jumlah</th>
                <th width="20%">Supplier</th>
                <th width="15%">Status</th>
                <th width="15%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while($row = $res_purchase->fetch_assoc()): ?>
            <tr>
                <td class="print-text-center"><?php echo $no++; ?></td>
                <td><?php echo date('d-m-Y', strtotime($row['purchase_date'])); ?></td>
                <td><?php echo htmlspecialchars($row['material_name']); ?></td>
                <td class="print-text-right"><?php echo number_format($row['total']) . ' ' . $row['unit']; ?></td>
                <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                <td class="print-text-center">
                    <?php 
                        $status = $row['status'];
                        if($status == 'pending') echo 'Menunggu';
                        elseif($status == 'approved') echo 'Disetujui';
                        elseif($status == 'rejected') echo 'Ditolak';
                        elseif($status == 'cancelled') echo 'Dibatalkan';
                        else echo htmlspecialchars($status);
                    ?>
                </td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Tidak ada data pengajuan ke supplier pada periode ini.</p>
    <?php endif; ?>

    <!-- Tabel Keseluruhan Bahan Baku -->
    <h2 class="print-section-title">4. Informasi Keseluruhan Bahan Baku</h2>
    <?php if ($res_material->num_rows > 0): ?>
    <table class="print-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="30%">Nama Bahan Baku</th>
                <th width="10%">Kategori</th>
                <th width="15%">Stok</th>
                <th width="15%">Batas Minimum</th>
                <th width="25%">Harga / Satuan</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while($row = $res_material->fetch_assoc()): ?>
            <tr>
                <td class="print-text-center"><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($row['material_name']); ?></td>
                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                <td class="print-text-right">
                    <?php 
                        $calc_stock = $row['calculated_stock'];
                        $stock_style = ($calc_stock <= $row['minimum_stock']) ? 'color: red; font-weight: bold;' : '';
                        echo '<span style="' . $stock_style . '">' . number_format($calc_stock) . ' ' . $row['unit'] . '</span>';
                    ?>
                </td>
                <td class="print-text-right"><?php echo number_format($row['minimum_stock']) . ' ' . $row['unit']; ?></td>
                <td class="print-text-right"><?php echo 'Rp ' . number_format($row['price'], 0, ',', '.') . ' / ' . $row['unit']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Belum ada data bahan baku yang tersimpan.</p>
    <?php endif; ?>

</body>
</html>
