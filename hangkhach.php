<?php
// Bắt đầu phiên làm việc và hiển thị lỗi (giúp debug)
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Kết nối CSDL
require_once("config/config.php");

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: Dangnhap/login.php");
    exit();
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name, role FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu không tìm thấy người dùng
if (!$user) {
    die("Người dùng không tồn tại.");
}

// Lưu thông tin tên và vai trò người dùng
$full_name = htmlspecialchars($user['full_name']);
$role = strtolower($user['role']);

// Nếu là tài xế thì không cho vào trang này
if ($role === 'driver') {
    $access_denied = true;
} else {
    $access_denied = false;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>CarpoolNow - Trang Khách hàng</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        /* Cài đặt ảnh nền toàn trang */
        body {
            background: url('assets/images/car_thanhphoHCM.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #212529;
        }

        /* Container nền trong suốt, riêng phần bên trong sẽ đổi theo vai trò */
        .overlay-container {
            background-color: transparent;
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container.default-style {
            background-color: rgba(255, 255, 255, 0.95);
            color: #212529;
        }
        .container.driver-style {
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            text-shadow: 1px 1px 6px rgba(0,0,0,0.6);
        }
        .container {
            border-radius: 12px;
            box-shadow: 0 0 16px rgba(0,0,0,0.2);
            padding: 2rem;
        }

        /* Thanh navbar */
        .navbar-custom {
            background-color: #007bff;
        }
        .navbar-custom .nav-link,
        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link,
        .navbar-custom .navbar-text {
            color: white;
        }
        .navbar-custom .nav-link.active {
            font-weight: 600;
            text-decoration: underline;
        }

        /* Tiêu đề */
        h2 {
            color: #0d6efd;
            margin-bottom: 1.5rem;
        }

        /* Nút bấm */
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #084298;
            border-color: #084298;
        }

        /* Bảng kết quả */
        table {
            box-shadow: 0 0 12px rgba(0, 123, 255, 0.2);
        }
        table th {
            background-color: #e7f1ff;
            font-weight: 600;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">🚗 CarpoolNow</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link active" href="#">Khách hàng</a></li>
                <li class="nav-item"><a class="nav-link" href="taixe.php">Tài xế</a></li>
                <li class="nav-item"><a class="nav-link" href="tintuc.php">Tin tức</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Liên hệ</a></li>
            </ul>
            <span class="navbar-text me-3">👋 <?= $full_name ?></span>
            <a href="Dangnhap/logout.php" class="btn btn-warning">Đăng xuất</a>
        </div>
    </div>
</nav>

<?php $styleClass = $access_denied ? 'driver-style' : 'default-style'; ?>
<div class="overlay-container d-flex align-items-center justify-content-center">
    <div class="container <?= $styleClass ?>">
        <?php if ($access_denied): ?>
            <div class="text-center">
                <h3>🚫 Truy cập bị từ chối</h3>
                <p>Bạn không có quyền truy cập trang này. Vui lòng đăng nhập với tư cách hành khách hoặc quản trị viên.</p>
                <a href="index.php" class="btn btn-primary">Quay về Trang chủ</a>
            </div>
        <?php else: ?>
            <!-- Nội dung cho hành khách hoặc admin -->
            <h2 class="text-center">Trang Khách hàng</h2>
            <p class="lead text-center mb-4">Chào mừng <strong><?= $full_name ?></strong>! Hãy tìm chuyến đi phù hợp với bạn.</p>

            <!-- Form tìm chuyến -->
            <form method="POST" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Điểm đi</label>
                    <input type="text" class="form-control" name="from_location" value="<?= htmlspecialchars($_POST['from_location'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Điểm đến</label>
                    <input type="text" class="form-control" name="to_location" value="<?= htmlspecialchars($_POST['to_location'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ngày đi</label>
                    <input type="date" class="form-control" name="departure_date" value="<?= htmlspecialchars($_POST['departure_date'] ?? '') ?>" required>
                </div>
                <div class="col-12 text-center">
                    <button type="submit" name="tim_chuyen" class="btn btn-primary px-5">Tìm chuyến đi</button>
                </div>
            </form>

            <!-- Kết quả -->
            <?php
            if (isset($_POST['tim_chuyen'])) {
                $from = trim($_POST['from_location'] ?? '');
                $to = trim($_POST['to_location'] ?? '');
                $date = $_POST['departure_date'] ?? '';

                if ($from && $to && $date) {
                    $query = "SELECT t.*, u.full_name AS driver_name
                              FROM trips t
                              JOIN users u ON t.driver_id = u.user_id
                              WHERE t.from_location LIKE ? AND t.to_location LIKE ? AND t.departure_date = ?";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute(["%$from%", "%$to%", $date]);
                    $trips = $stmt->fetchAll();

                    if ($trips) {
                        echo '<h4>Kết quả tìm kiếm:</h4>';
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-bordered text-center">';
                        echo '<thead><tr>
                                <th>Tài xế</th><th>Điểm đi</th><th>Điểm đến</th><th>Ngày đi</th><th>Chỗ trống</th>
                              </tr></thead><tbody>';
                        foreach ($trips as $trip) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($trip['driver_name']) . "</td>
                                    <td>" . htmlspecialchars($trip['from_location']) . "</td>
                                    <td>" . htmlspecialchars($trip['to_location']) . "</td>
                                    <td>" . htmlspecialchars($trip['departure_date']) . "</td>
                                    <td>" . htmlspecialchars($trip['available_seats']) . "</td>
                                  </tr>";
                        }
                        echo '</tbody></table></div>';
                    } else {
                        echo '<p class="text-danger fw-semibold text-center">❌ Không tìm thấy chuyến đi phù hợp.</p>';
                    }
                } else {
                    echo '<p class="text-danger text-center">Vui lòng điền đầy đủ thông tin để tìm chuyến.</p>';
                }
            }
            ?>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
