<?php
session_start();
require_once '../includes/db_connect.php';

if (isset($_GET['id'])) {
    $stock_out_id = intval($_GET['id']);

    $conn->begin_transaction();

    try {
        // Ambil info data lama untuk mengembalikan stok material dan ambil nama foto
        $stmt_info = $conn->prepare("SELECT material_id, total_out, photo FROM stock_out WHERE stock_out_id = ? FOR UPDATE");
        $stmt_info->bind_param("i", $stock_out_id);
        $stmt_info->execute();
        $res = $stmt_info->get_result();
        
        if ($res->num_rows > 0) {
            $data = $res->fetch_assoc();
            $material_id = $data['material_id'];
            $total_out = $data['total_out'];
            $photo = $data['photo'];

            // Kembalikan kuantitas stok di tabel material
            $stmt_update = $conn->prepare("UPDATE material SET stock = stock + ? WHERE material_id = ?");
            $stmt_update->bind_param("ii", $total_out, $material_id);
            $stmt_update->execute();

            // Hapus file foto jika ada
            if (!empty($photo)) {
                $file_path = '../uploads/stock_out/' . $photo;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            // Hapus log dari database
            $conn->query("DELETE FROM stock_out WHERE stock_out_id = $stock_out_id");
        }

        $conn->commit();
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Data stok keluar berhasil dibatalkan.'];
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal menghapus data stok keluar.'];
    }
}
header("Location: ../pages/stok_keluar.php");
exit();