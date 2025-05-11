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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f3f5;
        }
        .profile-card {
            max-width: 600px;
            margin: 60px auto;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            padding: 30px;
        }
        .avatar {
            width: 90px;
            height: 90px;
            background-color: #6c63ff;
            color: white;
            font-size: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        .table th {
            width: 35%;
        }
    </style>
</head>
<body>

<div class="profile-card text-center">
    <div class="avatar">
        <i class="bi bi-person-fill"></i>
    </div>
    <h3 class="mb-4">Hồ sơ người dùng</h3>
    <table class="table table-borderless text-start">
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

    <div class="mt-4">
        <a href="../index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại trang chính
        </a>
        <!-- Tùy chọn: mở tính năng chỉnh sửa -->
        <!-- <a href="edit_profile.php" class="btn btn-warning"><i class="bi bi-pencil-square"></i> Chỉnh sửa</a> -->
    </div>
</div>

</body>
</html>
