<?php
require_once '../config/Database.php';

$success = "";
$error = "";
//Hà Lê Quốc Việt 2280603661

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];
    $admin_code = $_POST["admin_code"] ?? "";

    // ✅ Chỉ cho phép role = admin nếu có mã đúng
    if ($role === "admin" && $admin_code !== "ABC123") {
        $role = "passenger"; // fallback nếu sai mã
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $error = "Email đã được đăng ký!";
    } else {
        $insert = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        if ($insert->execute([$name, $email, $phone, $password, $role])) {
            $success = "🎉 Đăng ký thành công! Bạn có thể <a href='login.php'>đăng nhập</a>.";
        } else {
            $error = "Lỗi khi đăng ký.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
<div class="container bg-white p-4 rounded shadow" style="max-width: 500px;">
    <h2 class="mb-4">Tạo tài khoản</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Họ tên</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Số điện thoại</label>
            <input type="text" name="phone" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Bạn là:</label>
            <select name="role" class="form-select" required>
                <option value="passenger">Hành khách</option>
                <option value="driver">Tài xế</option>
                <option value="admin">Quản trị viên</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Mã nội bộ (chỉ dùng nếu đăng ký admin)</label>
            <input type="text" name="admin_code" class="form-control" placeholder="Nhập mã nếu bạn là admin">
        </div>

        <button type="submit" class="btn btn-primary w-100">Đăng ký</button>

        <p class="mt-3 text-center">
            Đã có tài khoản? <a href="login.php">Đăng nhập</a>
        </p>
    </form>
</div>
</body>
</html>
