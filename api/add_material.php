<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['material_name']);
    $category_id = intval($_POST['category_id']);
    $stock = intval($_POST['stock']);
    $unit = trim($_POST['unit']);
    $min_stock = intval($_POST['minimum_stock']);
    $price = floatval($_POST['price']);

    $stmt = $conn->prepare("INSERT INTO material (category_id, material_name, stock, unit, minimum_stock, price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssid", $category_id, $name, $stock, $unit, $min_stock, $price);

    if ($stmt->execute()) {
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Bahan baku baru berhasil ditambahkan!'];
    } else {
        $_SESSION['toast'] = ['type' => 'error', 'message' => 'Gagal menambahkan bahan baku.'];
    }
}
header("Location: ../pages/bahan_baku.php");
exit();