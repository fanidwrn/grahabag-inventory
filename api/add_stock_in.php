<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $material_id = intval($_POST['material_id']);
    $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
    $date_stock_in = $_POST['date_stock_in'];
    $total_in = intval($_POST['total_in']);
    $description_in = trim($_POST['description_in']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback default admin

    // Handle Upload Foto
    $photo_filename = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/stock_in/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_ext, $allowed_exts)) {
            $photo_filename = uniqid('in_') . '.' . $file_ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo_filename);
        }
    }

    $conn->begin_transaction();
    try {
        // Memasukkan log ke tabel stock_in
        $stmt = $conn->prepare("INSERT INTO stock_in (material_id, supplier_id, user_id, date_stock_in, total_in, description_in, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisssss", $material_id, $supplier_id, $user_id, $date_stock_in, $total_in, $description_in, $photo_filename);
        $stmt->execute();

        // Menambahkan kuantitas stok di tabel material utama
        $update_stmt = $conn->prepare("UPDATE material SET stock = stock + ? WHERE material_id = ?");
        $update_stmt->bind_param("ii", $total_in, $material_id);
        $update_stmt->execute();

        $conn->commit();
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Sukses menambah mutasi stok masuk!'];
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal memproses data stok masuk.'];
    }
}
header("Location: ../pages/stok_masuk.php");
exit();