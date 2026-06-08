<?php
    require_once '../includes/db_connect.php';
    require_once '../includes/header.php';
    require_once '../includes/sidebar.php';

    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }

    $username = $_SESSION['username'];
    $conn->query("UPDATE users SET last_notif_read = CURRENT_TIMESTAMP WHERE username = '$username'");

    $notifications = [];

    // Bahan Baku Stok Kritis
    $query_kritis = "SELECT material_id, material_name, stock, minimum_stock, unit FROM material WHERE stock <= minimum_stock";
    $res_kritis = $conn->query($query_kritis);
    while ($row = $res_kritis->fetch_assoc()) {
        $notifications[] = [
            'type' => 'kritis',
            'title' => 'Stok Kritis!',
            'message' => "Stok bahan baku <strong>{$row['material_name']}</strong> saat ini {$row['stock']} {$row['unit']} (Minimum: {$row['minimum_stock']}). Segera lakukan pemesanan.",
            'date' => date('Y-m-d H:i:s'),
            'timestamp' => time(),
            'icon' => 'warning'
        ];
    }

    // Pengajuan Terbaru
    $query_pengajuan = "SELECT p.updated_at, p.total, m.material_name, m.unit, p.status 
                        FROM material_purchase p 
                        JOIN material m ON p.material_id = m.material_id 
                        ORDER BY p.updated_at DESC, p.purchase_id DESC LIMIT 10";
    $res_pengajuan = $conn->query($query_pengajuan);
    while ($row = $res_pengajuan->fetch_assoc()) {
        $title = '';
        $message = '';
        $type = '';

        if ($row['status'] == 'pending') {
            $type = 'success';
            $title = 'Pengajuan Dibuat';
            $message = "Pengajuan bahan <strong>{$row['material_name']}</strong> sejumlah <strong>{$row['total']} {$row['unit']}</strong> telah berhasil dibuat dan menunggu persetujuan.";
        } elseif ($row['status'] == 'approved') {
            $type = 'success';
            $title = 'Pengajuan Disetujui';
            $message = "Pengajuan bahan <strong>{$row['material_name']}</strong> sejumlah <strong>{$row['total']} {$row['unit']}</strong> telah disetujui.";
        } elseif ($row['status'] == 'rejected') {
            $type = 'danger';
            $title = 'Pengajuan Ditolak';
            $message = "Pengajuan bahan <strong>{$row['material_name']}</strong> sejumlah <strong>{$row['total']} {$row['unit']}</strong> telah ditolak.";
        } elseif ($row['status'] == 'cancelled') {
            $type = 'warning';
            $title = 'Pengajuan Dibatalkan';
            $message = "Pengajuan bahan <strong>{$row['material_name']}</strong> sejumlah <strong>{$row['total']} {$row['unit']}</strong> telah dibatalkan.";
        }
        
        $notifications[] = [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'date' => $row['updated_at'],
            'timestamp' => strtotime($row['updated_at']),
            'icon' => 'pengajuan'
        ];
    }



    // Sort notifications by timestamp
    usort($notifications, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });

    $grouped_notifications = [];
    foreach ($notifications as $notif) {
        $date_str = date('d F Y', $notif['timestamp']);
        if (!isset($grouped_notifications[$date_str])) {
            $grouped_notifications[$date_str] = [];
        }
        $grouped_notifications[$date_str][] = $notif;
    }
    ?>

    <div class="bahan-baku-container">
        <div class="page-header-action">
            <div class="header-left">
                <h1 class="page-title-main">Notifikasi</h1>
                <p class="page-subtitle-main">Pusat pemberitahuan dan aktivitas terbaru.</p>
            </div>
        </div>

        <div class="notifikasi-list" style="margin-top: 20px;">
            <?php if (count($grouped_notifications) > 0): ?>
                <?php foreach ($grouped_notifications as $date_group => $notifs): ?>
                    <h3 style="font-size: 14px; color: #64748b; margin-bottom: 12px; margin-top: 24px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;"><?php echo $date_group; ?></h3>
                    <?php foreach ($notifs as $notif): 
                        $bg_color = '#ffffff';
                        $border_color = '#e2e8f0';
                        
                        if ($notif['type'] == 'kritis' || $notif['type'] == 'danger') {
                            $bg_color = '#fef2f2';
                            $border_color = '#fca5a5';
                        } elseif ($notif['type'] == 'success') {
                            $bg_color = '#f0fdf4';
                            $border_color = '#86efac';
                        } elseif ($notif['type'] == 'warning') {
                            $bg_color = '#fffbeb';
                            $border_color = '#fde047';
                        }
                    ?>
                    <div style="background-color: <?php echo $bg_color; ?>; border: 1px solid <?php echo $border_color; ?>; padding: 16px; border-radius: 8px; margin-bottom: 12px; display: flex; align-items: flex-start; gap: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <div style="flex-grow: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                <h4 style="margin: 0; font-size: 15px; color: #1e293b;"><?php echo $notif['title']; ?></h4>
                            </div>
                            <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.5;">
                                <?php echo $notif['message']; ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #64748b; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                    Tidak ada notifikasi saat ini.
                </div>
            <?php endif; ?>
        </div>
    </div>

    </div> 
    <script src="../assets/main.js?v=<?= time(); ?>"></script>
</body>
</html>
