<?php
session_start();
require_once '../includes/db_connect.php';

// Cek autentikasi dan otorisasi (Hanya Owner)
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'owner') {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Anda tidak memiliki akses untuk mengubah data supplier.'];
    header("Location: ../pages/supplier.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = intval($_POST['supplier_id']);
    $supplier_name = trim($_POST['supplier_name']);
    $email = trim($_POST['email']);
    $no_telp = trim($_POST['no_telp']);
    $address = trim($_POST['address']);

    try {
        $stmt = $conn->prepare("UPDATE suppliers SET supplier_name = ?, email = ?, no_telp = ?, address = ? WHERE supplier_id = ?");
        $stmt->bind_param("ssssi", $supplier_name, $email, $no_telp, $address, $supplier_id);
        $stmt->execute();
        
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Data supplier berhasil diperbarui.'];
    } catch (Exception $e) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal memperbarui data supplier.'];
    }
}

header("Location: ../pages/supplier.php");
exit();
