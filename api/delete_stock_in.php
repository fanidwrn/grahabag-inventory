<?php
session_start();
require_once '../includes/db_connect.php';

if (isset($_GET['id'])) {
    $stock_in_id = intval($_GET['id']);

    $conn->begin_transaction();
    try {
        // Ambil data jumlah masuk lama dan nama file foto
        $old_res = $conn->query("SELECT total_in, material_id, photo FROM stock_in WHERE stock_in_id = $stock_in_id")->fetch_assoc();
        
        if ($old_res) {
            $old_total = $old_res['total_in'];
            $material_id = $old_res['material_id'];
            $photo = $old_res['photo'];

            // Kurangi stok pada data master material
            $conn->query("UPDATE material SET stock = stock - $old_total WHERE material_id = $material_id");

            // Hapus file foto jika ada
            if (!empty($photo)) {
                $file_path = '../uploads/stock_in/' . $photo;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }

            // Hapus baris riwayat stok masuk
            $conn->query("DELETE FROM stock_in WHERE stock_in_id = $stock_in_id");
        }

        $conn->commit();
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Data stok masuk berhasil dibatalkan.'];
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal menghapus data stok masuk.'];
    }
}
header("Location: ../pages/stok_masuk.php");
exit();