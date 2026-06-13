<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | GRAHABAG Inventory</title>
    <link rel="stylesheet" href="../assets/style.css?v=<?= time(); ?>">
</head>
<body class="login-body">
    <div class="login-card">
        <h1 class="login-title">GRAHABAG INVENTORY</h1>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">Nama pengguna atau kata sandi salah!</div>
        <?php endif; ?>

        <form action="../api/login_process.php" method="POST">
            <div class="form-group">
                <label>USERNAME</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <img src="../assets/icons/user.png" alt="Username" width="14">
                    </span>
                    <input type="text" name="username" placeholder="Masukkan nama pengguna" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>PASSWORD</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <img src="../assets/icons/lock.png" alt="Password" width="14">
                    </span>
                    <input type="password" name="password" id="passwordInput" placeholder="Masukkan kata sandi" required>
                    
                    <button type="button" id="togglePassword" class="btn-toggle-password">
                        <img src="../assets/icons/eye-show.png" id="eyeIcon" alt="Show Password" class="form-icon-img">
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn-login">Masuk</button>
        </form>
    </div>

    <script src="../assets/main.js?v=<?= time(); ?>"></script>
</body>
</html>