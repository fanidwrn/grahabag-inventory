<?php
session_start();
require_once '../includes/db_connect.php';

// Cek autentikasi dan otorisasi (Hanya Owner)
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'owner') {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Anda tidak memiliki akses untuk menambah supplier.'];
    header("Location: ../pages/supplier.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_name = trim($_POST['supplier_name']);
    $email = trim($_POST['email']);
    $no_telp = trim($_POST['no_telp']);
    $address = trim($_POST['address']);

    try {
        $stmt = $conn->prepare("INSERT INTO suppliers (supplier_name, email, no_telp, address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $supplier_name, $email, $no_telp, $address);
        $stmt->execute();
        
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Berhasil menambahkan data supplier baru!'];
    } catch (Exception $e) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal menambahkan supplier.'];
    }
}

header("Location: ../pages/supplier.php");
exit();
