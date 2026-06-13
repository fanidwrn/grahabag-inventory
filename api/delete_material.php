<?php
session_start();
require_once '../includes/db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM material WHERE material_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Data bahan baku berhasil dihapus.'];
    } else {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal menghapus bahan karena keterikatan relasi data.'];
    }
}
header("Location: ../pages/bahan_baku.php");
exit();