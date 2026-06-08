<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_stock_out = $_POST['date_stock_out'];
    $catatan_umum = trim($_POST['catatan_umum']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

    $combined_description = $catatan_umum;

    $material_ids = $_POST['material_ids'];
    $total_outs = $_POST['total_outs'];

    // Handle Upload Foto
    $photo_filename = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/stock_out/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png'];
        if (in_array($file_ext, $allowed_exts)) {
            $photo_filename = uniqid('out_') . '.' . $file_ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo_filename);
        }
    }

    $conn->begin_transaction();
    try {
        // Looping per baris data material yang dikirim
        for ($i = 0; $i < count($material_ids); $i++) {
            $mat_id = intval($material_ids[$i]);
            $qty_out = intval($total_outs[$i]);

            if ($mat_id <= 0 || $qty_out <= 0) continue;

            // 1. Validasi Ketersediaan Stok Fisik Terlebih Dahulu
            $check_res = $conn->query("SELECT stock, material_name FROM material WHERE material_id = $mat_id")->fetch_assoc();
            if ($check_res['stock'] < $qty_out) {
                throw new Exception("Stok untuk '" . $check_res['material_name'] . "' tidak cukup! Tersisa: " . $check_res['stock']);
            }

            // 2. Insert Log Pengeluaran ke tabel stock_out
            $stmt = $conn->prepare("INSERT INTO stock_out (material_id, user_id, date_stock_out, total_out, description_out, photo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisiss", $mat_id, $user_id, $date_stock_out, $qty_out, $combined_description, $photo_filename);
            $stmt->execute();

            // 3. Kurangi Angka Stok Fisik di Master Tabel Material
            $update_stmt = $conn->prepare("UPDATE material SET stock = stock - ? WHERE material_id = ?");
            $update_stmt->bind_param("ii", $qty_out, $mat_id);
            $update_stmt->execute();
        }

        $conn->commit();
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Sukses mengeluarkan item material pilihan ke divisi produksi!'];
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['toast'] = ['type' => 'error', 'message' => '✕ Gagal: ' . $e->getMessage()];
    }
}
header("Location: ../pages/stok_keluar.php");
exit();