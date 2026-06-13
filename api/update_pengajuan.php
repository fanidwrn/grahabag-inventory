<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $purchase_id = $_POST['purchase_id'] ?? '';
    $material_id = $_POST['material_id'] ?? '';
    $supplier_id = $_POST['supplier_id'] ?? '';
    $total = $_POST['total'] ?? '';
    $contact_method = $_POST['contact_method'] ?? 'whatsapp';
    $description = $_POST['description'] ?? '';
    
    if (empty($purchase_id) || empty($material_id) || empty($supplier_id) || empty($total) || empty($description)) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Semua kolom harus diisi'];
        header("Location: ../pages/pengajuan.php");
        exit;
    }

    try {
        // Cek status saat ini, hanya bisa edit jika pending
        $stmt_check = $conn->prepare("SELECT status FROM material_purchase WHERE purchase_id = ?");
        $stmt_check->bind_param("i", $purchase_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows === 0) {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Pengajuan tidak ditemukan'];
            header("Location: ../pages/pengajuan.php");
            exit;
        }
        
        $current_status = $result_check->fetch_assoc()['status'];

        if ($current_status !== 'pending') {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Hanya pengajuan dengan status pending yang dapat diedit'];
            header("Location: ../pages/pengajuan.php");
            exit;
        }

        $stmt = $conn->prepare("UPDATE material_purchase SET material_id = ?, supplier_id = ?, total = ?, contact_method = ?, description = ? WHERE purchase_id = ?");
        $stmt->bind_param("iiissi", $material_id, $supplier_id, $total, $contact_method, $description, $purchase_id);
        
        if ($stmt->execute()) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Data pengajuan berhasil diperbarui.'];
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal memperbarui data pengajuan.'];
        }
    } catch (Exception $e) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
    }
} else {
    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Invalid request method'];
}

header("Location: ../pages/pengajuan.php");
exit();
?>
