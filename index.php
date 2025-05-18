<?php
// 👉 Khởi động session và kết nối CSDL
session_start();
require_once("config/config.php");

// 👉 Kiểm tra đăng nhập, nếu chưa đăng nhập chuyển về trang login
// Hà Lê Quốc Việt 2280603661
if (!isset($_SESSION['user_id'])) {
    header("Location: Dangnhap/login.php");
    exit();
}

// 👉 Lấy thông tin người dùng từ CSDL
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 👉 Thoát nếu không tìm thấy user
if (!$user) {
    echo "Không tìm thấy người dùng!";
    exit();
}

$name = htmlspecialchars($user['full_name']);
$role = $user['role'];

// 👉 Nếu là tài xế và gửi POST hoàn thành chuyến đi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trip_id'])) {
    $trip_id = $_POST['trip_id'];
    $complete = $pdo->prepare("UPDATE trips SET status = 'completed' WHERE trip_id = ? AND driver_id = ?");
    $complete->execute([$trip_id, $user_id]);
    $message = "✅ Đã hoàn thành chuyến đi #$trip_id.";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>CarpoolNow - Trang chính</title>

    <!-- 👉 Giao diện Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* 👉 CSS nền và hộp */
        body {
            background: url('assets/images/hochiminh_night.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }

        .main-box {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.15);
            max-width: 550px;
            width: 100%;
        }

        .navbar-brand {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }

        .navbar-brand::before {
            content: "\1F698"; /* emoji xe hơi */
            margin-right: 8px;
        }

        .btn {
            min-height: 45px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<!-- 👉 Thanh điều hướng -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">CarpoolNow</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <!-- 👉 Các mục trên thanh menu -->
                <li class="nav-item"><a class="nav-link active" href="index.php">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="hangkhach.php">Khách hàng</a></li>
                <li class="nav-item"><a class="nav-link" href="taixe.php">Tài xế</a></li>
                <li class="nav-item"><a class="nav-link" href="tintuc.php">Tin tức</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Liên hệ</a></li>
            </ul>
            <!-- 👉 Thông tin người dùng và nút đăng xuất -->
            <span class="navbar-text me-3 text-white">👋 <?= $name ?> (<?= $role ?>)</span>
            <a href="Dangnhap/logout.php" class="btn btn-warning">Đăng xuất</a>
        </div>
    </div>
</nav>

<!-- 👉 Nội dung chính của trang -->
<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="main-box text-center">

        <!-- 👉 Tiêu đề chào mừng -->
        <h2 class="mb-3">🚗 CarpoolNow – Đặt xe đi chung</h2>
        <h5 class="text-success mb-4">Chào mừng, <strong><?= $name ?></strong>!</h5>
        <p class="mb-3">
            Bạn đang đăng nhập với vai trò:
            <span class="badge bg-primary text-uppercase"><?= $role ?></span>
        </p>

        <!-- 👉 Thông báo nếu có -->
        <?php if (isset($message)) echo '<div class="alert alert-success">' . $message . '</div>'; ?>

        <!-- 👉 Giao diện cho từng vai trò -->
        <?php if ($role === 'passenger'): ?>
            <!-- 👉 Hành khách: Tìm chuyến đi -->
            <a href="TimChuyenDi/timchuyendi.php" class="btn btn-success w-100 mb-2">🔍 Tìm chuyến đi</a>

        <?php elseif ($role === 'driver'): ?>
            <!-- 👉 Tài xế: Tạo chuyến đi và xử lý yêu cầu -->
            <a href="TimChuyenDi/taochuyendi.php" class="btn btn-primary w-100 mb-2">🚗 Tạo chuyến đi</a>
            <a href="Dashboard/manage_requests.php" class="btn btn-outline-secondary w-100 mb-3">📩 Yêu cầu đặt chỗ</a>

            <!-- 👉 Form hoàn thành chuyến đi -->
            <form method="POST">
                <label for="trip_id" class="form-label">Chọn chuyến đi để hoàn thành:</label>
                <select name="trip_id" class="form-select mb-2" required>
                    <option value="">-- Chọn chuyến --</option>
                    <?php
                    $stmt = $pdo->prepare("SELECT trip_id, departure_time FROM trips WHERE driver_id = ? AND status = 'pending'");
                    $stmt->execute([$user_id]);
                    while ($trip = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$trip['trip_id']}'>Chuyến #{$trip['trip_id']} - {$trip['departure_time']}</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-success w-100">✅ Hoàn thành chuyến đi</button>
            </form>

        <?php elseif ($role === 'admin'): ?>
            <!-- 👉 Quản trị viên: Quản lý người dùng -->
            <a href="Dashboard/admin_manage_users.php" class="btn btn-dark w-100 mb-3">👑 Quản lý người dùng</a>
        <?php endif; ?>

        <!-- 👉 Hồ sơ người dùng -->
        <a href="Dashboard/profile.php" class="btn btn-outline-dark w-100 mt-2">👤 Hồ sơ cá nhân</a>
    </div>
</div>

<!-- 👉 Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
