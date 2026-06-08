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
    $new_status = $_POST['status'] ?? '';
    $role = $_SESSION['role'] ?? 'admin';
    
    if (empty($purchase_id) || empty($new_status)) {
        echo json_encode(['status' => 'error', 'message' => 'ID dan status harus diisi']);
        exit;
    }

    $valid_statuses = ['pending', 'approved', 'rejected', 'cancelled'];
    if (!in_array($new_status, $valid_statuses)) {
        echo json_encode(['status' => 'error', 'message' => 'Status tidak valid']);
        exit;
    }

    try {
        // Cek status saat ini
        $stmt_check = $conn->prepare("SELECT status FROM material_purchase WHERE purchase_id = ?");
        $stmt_check->bind_param("i", $purchase_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Pengajuan tidak ditemukan']);
            exit;
        }
        
        $current_status = $result_check->fetch_assoc()['status'];

        if ($current_status !== 'pending') {
            echo json_encode(['status' => 'error', 'message' => 'Hanya pengajuan dengan status pending yang dapat diubah statusnya']);
            exit;
        }

        // Validasi role
        if ($role === 'admin' && $new_status !== 'cancelled') {
            echo json_encode(['status' => 'error', 'message' => 'Admin hanya dapat membatalkan (cancel) pengajuan']);
            exit;
        }

        if ($role === 'owner' && !in_array($new_status, ['approved', 'rejected'])) {
            echo json_encode(['status' => 'error', 'message' => 'Owner hanya dapat menyetujui atau menolak pengajuan']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE material_purchase SET status = ? WHERE purchase_id = ?");
        $stmt->bind_param("si", $new_status, $purchase_id);
        
        if ($stmt->execute()) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Status pengajuan berhasil diubah'];
            echo json_encode(['status' => 'success', 'message' => 'Status pengajuan berhasil diubah']);
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal mengubah status pengajuan'];
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengubah status']);
        }
    } catch (Exception $e) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
