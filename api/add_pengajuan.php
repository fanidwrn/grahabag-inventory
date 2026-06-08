<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $material_id = $_POST['material_id'] ?? '';
    $supplier_id = $_POST['supplier_id'] ?? '';
    $total = $_POST['total'] ?? '';
    $contact_method = $_POST['contact_method'] ?? 'whatsapp';
    $description = $_POST['description'] ?? '';
    $user_id = $_SESSION['user_id'];
    $purchase_date = date('Y-m-d');
    
    if (empty($material_id) || empty($supplier_id) || empty($total) || empty($description) || empty($contact_method)) {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Semua kolom harus diisi'];
        header("Location: ../pages/pengajuan.php");
        exit;
    }

    try {
        $role = $_SESSION['role'] ?? 'admin';
        $status = ($role === 'owner') ? 'approved' : 'pending';
        $stmt = $conn->prepare("INSERT INTO material_purchase (material_id, supplier_id, user_id, total, purchase_date, status, contact_method, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiissss", $material_id, $supplier_id, $user_id, $total, $purchase_date, $status, $contact_method, $description);
        
        if ($stmt->execute()) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Pengajuan berhasil ditambahkan'];
        } else {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal menambahkan pengajuan'];
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
