<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Dangnhap/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Không tìm thấy người dùng!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ cá nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container bg-white rounded shadow p-4" style="max-width: 600px">
    <h2 class="mb-4 text-center">👤 Hồ sơ người dùng</h2>
    <table class="table table-bordered">
        <tr>
            <th>Họ tên</th>
            <td><?= htmlspecialchars($user['full_name']) ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?= htmlspecialchars($user['email']) ?></td>
        </tr>
        <tr>
            <th>Số điện thoại</th>
            <td><?= htmlspecialchars($user['phone']) ?></td>
        </tr>
        <tr>
            <th>Vai trò</th>
            <td><?= htmlspecialchars($user['role']) ?></td>
        </tr>
        <tr>
            <th>Ngày tạo</th>
            <td><?= $user['created_at'] ?></td>
        </tr>
    </table>

    <div class="text-center mt-3">
        <a href="../index.php" class="btn btn-secondary">← Quay lại trang chính</a>
        <!-- <a href="edit_profile.php" class="btn btn-warning">✏️ Chỉnh sửa</a> --> <!-- Tùy chọn nâng cao -->
    </div>
</div>
</body>
</html>
