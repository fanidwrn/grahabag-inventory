<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Simpan data pendaftaran hak akses ke session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        $log_stmt = $conn->prepare("INSERT INTO login_logs (user_id) VALUES (?)");
        $log_stmt->bind_param("i", $user['user_id']);
        $log_stmt->execute();

        header("Location: ../pages/dashboard.php");
        exit();
    } else {
        header("Location: ../pages/login.php?error=1");
        exit();
    }
} else {
    header("Location: ../pages/login.php");
    exit();
}
?>