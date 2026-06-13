<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../pages/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stock_out_id = intval($_POST['stock_out_id']);
    $material_id = intval($_POST['material_id']);
    $date_stock_out = $conn->real_escape_string($_POST['date_stock_out']);
    $new_total_out = intval($_POST['total_out']);
    
    $catatan_umum = $conn->real_escape_string($_POST['catatan_umum']);
    $description_out = $catatan_umum;

    $conn->begin_transaction();

    try {
        // Ambil data lama
        $stmt_old = $conn->prepare("SELECT total_out, photo FROM stock_out WHERE stock_out_id = ? FOR UPDATE");
        $stmt_old->bind_param("i", $stock_out_id);
        $stmt_old->execute();
        $result_old = $stmt_old->get_result();
        $old_data = $result_old->fetch_assoc();
        $old_total_out = $old_data['total_out'];
        $old_photo = $old_data['photo'];

        $difference = $new_total_out - $old_total_out;

        // Handle upload foto baru
        $photo_filename = $old_photo;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/stock_out/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_ext, $allowed_exts)) {
                $photo_filename = uniqid('out_') . '.' . $file_ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo_filename);
                // Hapus foto lama jika ada
                if (!empty($old_photo) && file_exists($upload_dir . $old_photo)) {
                    unlink($upload_dir . $old_photo);
                }
            }
        }

        // Ambil stok material saat ini
        $stmt_mat = $conn->prepare("SELECT stock FROM material WHERE material_id = ? FOR UPDATE");
        $stmt_mat->bind_param("i", $material_id);
        $stmt_mat->execute();
        $result_mat = $stmt_mat->get_result();
        $mat_data = $result_mat->fetch_assoc();
        
        // Cek jika stok tidak mencukupi (stok kurang dari penambahan pengeluaran)
        if ($mat_data['stock'] < $difference) {
            throw new Exception("Stok bahan baku tidak mencukupi untuk update.");
        }

        // Update material stock (stok dikurangi selisih pengeluaran)
        $update_mat = $conn->prepare("UPDATE material SET stock = stock - ? WHERE material_id = ?");
        $update_mat->bind_param("ii", $difference, $material_id);
        $update_mat->execute();

        // Update stock_out record
        $update_stock = $conn->prepare("UPDATE stock_out SET date_stock_out = ?, total_out = ?, description_out = ?, photo = ? WHERE stock_out_id = ?");
        $update_stock->bind_param("sissi", $date_stock_out, $new_total_out, $description_out, $photo_filename, $stock_out_id);
        $update_stock->execute();

        $conn->commit();
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Data stok keluar berhasil diperbarui!'];
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal memperbarui data stok'];
    }

    header("Location: ../pages/stok_keluar.php");
    exit();
}
