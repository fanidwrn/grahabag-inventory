<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['material_id']);
    $name = trim($_POST['material_name']);
    $category_id = intval($_POST['category_id']);
    $stock = intval($_POST['stock']);
    $unit = trim($_POST['unit']);
    $min_stock = intval($_POST['minimum_stock']);
    $price = floatval($_POST['price']);

    $stmt = $conn->prepare("UPDATE material SET category_id = ?, material_name = ?, stock = ?, unit = ?, minimum_stock = ?, price = ? WHERE material_id = ?");
    $stmt->bind_param("isssidi", $category_id, $name, $stock, $unit, $min_stock, $price, $id);

    if ($stmt->execute()) {
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Data bahan baku berhasil diperbarui.'];
    } else {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal memperbarui data bahan baku.'];
    }
}
header("Location: ../pages/bahan_baku.php");
exit();