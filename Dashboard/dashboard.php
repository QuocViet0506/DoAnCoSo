<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
require_once '../config/config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: ../Dangnhap/login.php");
    //Hà Lê Quốc Việt 2280603661
    exit();
}

$name = htmlspecialchars($_SESSION["user_name"]);
$role = $_SESSION["role"];
$message = '';

// Kiểm tra số lượng chuyến đi cần đánh giá (chỉ cho hành khách)
if ($role === "passenger") {
    try {
        $pending_stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM trips t
            JOIN ride_requests r ON t.trip_id = r.trip_id
            WHERE r.passenger_id = ? AND t.status = 'completed' AND t.driver_confirmed = 1
            AND NOT EXISTS (SELECT 1 FROM reviews rv WHERE rv.trip_id = t.trip_id AND rv.reviewer_id = ?)
        ");
        $pending_stmt->execute([$_SESSION["user_id"], $_SESSION["user_id"]]);
        $pending_count = $pending_stmt->fetchColumn();

        if ($pending_count > 0) {
            $message = "<div class='alert alert-warning text-center'>
                Bạn có <strong>$pending_count</strong> chuyến đi cần đánh giá. 
                <a href='rate_driver.php' class='alert-link'>Xem chi tiết</a>
            </div>";
        }
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger text-center'>❌ Lỗi truy vấn: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>CarpoolNow - Bảng điều khiển</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('../assets/images/hochiminh_night.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
        }
        .dashboard-box {
            background-color: rgba(255,255,255,0.95);
            padding: 40px;
            border-radius: 20px;
            max-width: 600px;
            margin: 60px auto;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }
        h2 {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="dashboard-box text-center">
        <h2>🚗 CarpoolNow – Đặt xe đi chung</h2>
        <p class="mt-3">Chào mừng, <span class="text-success fw-bold"><?= $name ?></span>!</p>
        <p>Bạn đang đăng nhập với vai trò: <span class="badge bg-primary text-uppercase"><?= $role ?></span></p>

        <hr>

        <!-- Hiển thị thông báo nếu có -->
        <?php if (!empty($message)) echo $message; ?>

        <?php if ($role === "passenger"): ?>
            <a href="../TimChuyenDi/timchuyendi.php" class="btn btn-success w-100 mb-2">🔍 Tìm chuyến đi</a>
            <a href="../Dashboard/history.php" class="btn btn-outline-primary w-100 mb-2">📜 Lịch sử chuyến</a>
            <a href="../Dashboard/rate_driver.php" class="btn btn-outline-secondary w-100 mb-2">⭐ Đánh giá tài xế</a>
        <?php elseif ($role === "driver"): ?>
            <a href="../TimChuyenDi/taochuyendi.php" class="btn btn-primary w-100 mb-2">🚗 Tạo chuyến đi</a>
            <a href="../Dashboard/manage_requests.php" class="btn btn-outline-dark w-100 mb-2">📩 Yêu cầu đặt chỗ</a>
        <?php elseif ($role === "admin"): ?>
            <a href="../Dashboard/admin_manage_users.php" class="btn btn-warning w-100 mb-2">👤 Quản lý người dùng</a>
            <a href="../Dashboard/admin_transactions.php" class="btn btn-outline-dark w-100 mb-2">💰 Giao dịch</a>
            <a href="../Dashboard/admin_support.php" class="btn btn-outline-secondary w-100 mb-2">🛠 Hỗ trợ</a>
        <?php endif; ?>

        <a href="../Dashboard/profile.php" class="btn btn-outline-dark w-100 mt-3 mb-2">👤 Hồ sơ cá nhân</a>
        <a href="../Dangnhap/logout.php" class="btn btn-danger w-100">Đăng xuất</a>
    </div>
    
</body>
</html>
