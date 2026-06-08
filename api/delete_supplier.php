<?php
session_start();
require_once '../includes/db_connect.php';

// Cek autentikasi dan otorisasi (Hanya Owner)
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'owner') {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Anda tidak memiliki akses untuk menghapus data supplier.'];
    header("Location: ../pages/supplier.php");
    exit();
}

if (isset($_GET['id'])) {
    $supplier_id = intval($_GET['id']);

    try {
        $stmt = $conn->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
        $stmt->bind_param("i", $supplier_id);
        $stmt->execute();
        
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Data supplier berhasil dihapus!'];
    } catch (Exception $e) {
        // Jika gagal, mungkin karena constraint foreign key
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal menghapus supplier. Supplier ini mungkin sedang digunakan pada data transaksi (stok masuk/keluar).'];
    }
}

header("Location: ../pages/supplier.php");
exit();
