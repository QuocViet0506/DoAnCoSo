<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

 //Hà Lê Quốc Việt 2280603661
require_once '../config/Database.php';

// ✅ Kiểm tra đăng nhập & quyền tài xế
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "driver") {
    header("Location: ../Dangnhap/login.php");
    exit();
}

$success = $error = "";

// ✅ Lấy danh sách địa điểm
$locations = $pdo->query("SELECT location_id, name FROM locations ORDER BY name")->fetchAll();

// ✅ Xử lý khi gửi form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $driver_id = $_SESSION["user_id"];
    $from_id   = $_POST["from_location_id"];
    $to_id     = $_POST["to_location_id"];
    $departure = $_POST["departure_time"];
    $seats     = $_POST["available_seats"];
    $price     = $_POST["price"];
    $notes     = $_POST["notes"];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO trips (driver_id, from_location_id, to_location_id, departure_time, available_seats, price, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$driver_id, $from_id, $to_id, $departure, $seats, $price, $notes]);
        $success = "✅ Tạo chuyến đi thành công!";
    } catch (PDOException $e) {
        $error = "❌ Lỗi khi tạo chuyến: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>🚗 Tạo chuyến đi mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('../assets/images/car_background.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.96);
            border-radius: 16px;
            padding: 35px;
            max-width: 650px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        h2 {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2 class="mb-4 text-center">🚗 Tạo chuyến đi mới</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Điểm đi</label>
                <select name="from_location_id" class="form-select" required>
                    <option value="">-- Chọn điểm đi --</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= $loc['location_id'] ?>"><?= $loc['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label>Điểm đến</label>
                <select name="to_location_id" class="form-select" required>
                    <option value="">-- Chọn điểm đến --</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= $loc['location_id'] ?>"><?= $loc['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label>Thời gian khởi hành</label>
                <input type="datetime-local" name="departure_time" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Số chỗ trống</label>
                <input type="number" name="available_seats" class="form-control" min="1" required>
            </div>

            <div class="mb-3">
                <label>Giá vé (VND)</label>
                <input type="number" name="price" class="form-control" min="0" required>
            </div>

            <div class="mb-3">
                <label>Ghi chú (nếu có)</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">🚀 Tạo chuyến</button>
            <a href="../Dashboard/dashboard.php" class="btn btn-outline-dark w-100">⬅ Quay về trang chính</a>
        </form>
    </div>
</body>
</html>
