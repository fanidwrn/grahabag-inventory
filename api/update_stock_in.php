<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stock_in_id = intval($_POST['stock_in_id']);
    $material_id = intval($_POST['material_id']);
    $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
    $date_stock_in = $_POST['date_stock_in'];
    $new_total = intval($_POST['total_in']);
    $description_in = trim($_POST['description_in']);

    $conn->begin_transaction();
    try {
        // Mengambil data jumlah masuk lama untuk mencari selisih dan foto lama
        $old_res = $conn->query("SELECT total_in, material_id, photo FROM stock_in WHERE stock_in_id = $stock_in_id")->fetch_assoc();
        $old_total = $old_res['total_in'];
        $old_material = $old_res['material_id'];
        $old_photo = $old_res['photo'];

        // Handle upload foto baru
        $photo_filename = $old_photo;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/stock_in/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_ext, $allowed_exts)) {
                $photo_filename = uniqid('in_') . '.' . $file_ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo_filename);
                // Hapus foto lama jika ada
                if (!empty($old_photo) && file_exists($upload_dir . $old_photo)) {
                    unlink($upload_dir . $old_photo);
                }
            }
        }

        // Update baris riwayat stok masuk
        $stmt = $conn->prepare("UPDATE stock_in SET material_id = ?, supplier_id = ?, date_stock_in = ?, total_in = ?, description_in = ?, photo = ? WHERE stock_in_id = ?");
        $stmt->bind_param("iisissi", $material_id, $supplier_id, $date_stock_in, $new_total, $description_in, $photo_filename, $stock_in_id);
        $stmt->execute();

        // Update stok pada data master material (Mengurangi nilai lama, Menambahkan nilai baru)
        $adjust_old = $conn->query("UPDATE material SET stock = stock - $old_total WHERE material_id = $old_material");
        $adjust_new = $conn->query("UPDATE material SET stock = stock + $new_total WHERE material_id = $material_id");

        $conn->commit();
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Perubahan data mutasi stok berhasil diperbarui!'];
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Terjadi kegagalan rekonsiliasi data stok.'];
    }
}
header("Location: ../pages/stok_masuk.php");
exit();