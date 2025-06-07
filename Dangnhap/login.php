<?php
session_start();
require_once '../config/Database.php';

$error = "";

// Hà Lê Quốc Việt 2280603661
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] == 0) {
                $error = "⚠️ Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.";
            } else {
                $_SESSION['user_id']    = $user['user_id'];
                $_SESSION['full_name']  = $user['full_name'];
                $_SESSION['role']       = $user['role'];
                header('Location: ../Dashboard/dashboard.php');
                exit();
            }
        } else {
            $error = "❌ Sai email hoặc mật khẩu.";
        }
    } catch (PDOException $e) {
        $error = "❌ Lỗi: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
<div class="container bg-white rounded shadow p-4" style="max-width: 400px;">
    <h2 class="mb-4">Đăng nhập</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label>Email:</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Mật khẩu:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100">Đăng nhập</button>
    </form>

    <p class="mt-3 text-center">
        Chưa có tài khoản? <a href="register.php">Đăng ký tại đây</a>
    </p>
</div>
</body>
</html>
