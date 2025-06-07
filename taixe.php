<?php
session_start();
require_once("config/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: Dangnhap/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name, role FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !in_array($user['role'], ['driver', 'admin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>Không có quyền truy cập</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #f8d7da;
                font-family: 'Segoe UI', sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }
            .alert-box {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 15px rgba(0,0,0,0.2);
                max-width: 500px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="alert-box">
            <h4 class="text-danger">🚫 Bạn không có quyền truy cập trang này.</h4>
            <p>Vui lòng quay lại trang chính hoặc liên hệ quản trị viên nếu bạn nghĩ đây là lỗi.</p>
            <a href="index.php" class="btn btn-primary mt-3">⬅ Quay về Trang chủ</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$full_name = htmlspecialchars($user['full_name']);
$role = $user['role'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>CarpoolNow - Tài xế</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('assets/images/car_thanhphoHCM.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Segoe UI', sans-serif;
            color: #000;
        }

        .main-box {
            background-color: rgba(255, 255, 255, 0.93);
            border-radius: 15px;
            padding: 30px;
            margin: 50px auto;
            max-width: 900px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }

        h2 {
            color: #0d6efd;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .btn-outline-secondary {
            font-weight: 500;
        }

        .navbar {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">🚗 CarpoolNow</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="hangkhach.php">Khách hàng</a></li>
                    <li class="nav-item"><a class="nav-link active" href="#">Tài xế</a></li>
                    <li class="nav-item"><a class="nav-link" href="tintuc.php">Tin tức</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Liên hệ</a></li>
                </ul>
                <span class="navbar-text me-3 text-white">👋 <?= $full_name ?></span>
                <a href="Dangnhap/logout.php" class="btn btn-warning">Đăng xuất</a>
            </div>
        </div>
    </nav>

    <!-- Nội dung -->
    <div class="main-box">
        <h2>Trang dành cho Tài xế / Admin</h2>
        <p>Xin chào <strong><?= $full_name ?></strong>! Đây là trang dành cho tài xế và quản trị viên để xử lý yêu cầu đi xe.</p>

        <?php if (in_array($role, ['driver', 'admin'])): ?>
        <hr>
        <h5>Yêu cầu được duyệt cho bạn:</h5>
        <ul class="list-group">
            <?php
            $stmt = $pdo->prepare("SELECT r.*, u.full_name AS passenger_name
                                   FROM ride_requests r
                                   JOIN users u ON r.passenger_id = u.user_id
                                   WHERE r.driver_id = ? AND r.status = 'approved'
                                   ORDER BY r.request_time DESC");
            $stmt->execute([$user_id]);
            $requests = $stmt->fetchAll();

            if (!$requests) {
                echo "<li class='list-group-item'>Chưa có yêu cầu nào được duyệt.</li>";
            } else {
                foreach ($requests as $req) {
                    echo "<li class='list-group-item'>";
                    echo "<strong>🚘 {$req['passenger_name']}</strong> — ";
                    echo "Đón tại: <em>{$req['pickup_location']}</em> → Đến: <em>{$req['dropoff_location']}</em><br>";
                    echo "<small>🕒 Thời gian: {$req['request_time']}</small><br>";

                    if ($req['driver_action'] === 'pending') {
                        echo "<form method='post' action='actions/handle_driver_action.php' class='mt-2'>"; // Action file xử lý
                        echo "<input type='hidden' name='request_id' value='{$req['request_id']}'>";
                        echo "<button name='action' value='accept' class='btn btn-success btn-sm me-2'>✔ Thực hiện</button>";
                        echo "<button name='action' value='reject' class='btn btn-danger btn-sm'>✖ Không thực hiện</button>";
                        echo "</form>";
                    } else {
                        $status_text = $req['driver_action'] === 'accepted' ? '✅ Đã chọn thực hiện' : '❌ Đã từ chối';
                        echo "<span class='badge bg-" . ($req['driver_action'] === 'accepted' ? 'success' : 'danger') . " mt-2'>$status_text</span>";
                    }

                    echo "</li>";
                }
            }
            ?>
        </ul>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
