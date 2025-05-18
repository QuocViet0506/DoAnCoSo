<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    //Hà Lê Quốc Việt 2280603661
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

// Gán ảnh nền dựa vào vai trò
switch ($user['role']) {
    case 'driver':
        $background = "../assets/images/bg_driver.jpg";
        break;
    case 'passenger':
        $background = "../assets/images/bg_passenger.jpg";
        break;
    case 'admin':
    default:
        $background = "../assets/images/bg_admin.jpg";
        break;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ cá nhân</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: url('<?= $background ?>') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .overlay {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }

        .profile-card {
            position: relative;
            z-index: 1;
            max-width: 600px;
            width: 100%;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(6px);
        }

        .avatar {
            width: 90px;
            height: 90px;
            background-color: #0d6efd;
            color: white;
            font-size: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .profile-card h3 {
            color: #0d6efd;
        }

        .table th {
            width: 35%;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>

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
            <a href="../index.php" class="btn btn-outline-light">
                <i class="bi bi-arrow-left"></i> Quay lại trang chính
            </a>
        </div>
    </div>
</body>
</html>
