<?php
session_start();
require_once '../config/Database.php';

if (!isset($_SESSION["user_id"])) {
    //Hà Lê Quốc Việt 2280603661
    header("Location: ../Dangnhap/login.php");
    exit();
}

$trip_id = $_GET['trip_id'] ?? null;
if (!$trip_id) {
    echo "❌ Không tìm thấy chuyến đi.";
    exit();
}

// Lấy thông tin chuyến đi
$stmt = $pdo->prepare("
    SELECT t.*, 
           u.full_name AS driver_name, 
           lf.name AS from_location, 
           lt.name AS to_location
    FROM trips t
    JOIN users u ON t.driver_id = u.user_id
    JOIN locations lf ON t.from_location_id = lf.location_id
    JOIN locations lt ON t.to_location_id = lt.location_id
    WHERE t.trip_id = ?
");
$stmt->execute([$trip_id]);
$trip = $stmt->fetch();

if (!$trip) {
    echo "❌ Chuyến đi không tồn tại.";
    exit();
}

$current_user_id = $_SESSION["user_id"];
$role = $_SESSION["role"];
$is_driver = $role === 'driver' && $trip['driver_id'] == $current_user_id;
$is_passenger = $role === 'passenger';

$passengers = [];
$your_request_status = null;

// Nếu là tài xế, lấy danh sách hành khách
if ($is_driver) {
    $stmt = $pdo->prepare("
        SELECT u.full_name, rr.status
        FROM ride_requests rr
        JOIN users u ON rr.passenger_id = u.user_id
        WHERE rr.trip_id = ?
    ");
    $stmt->execute([$trip_id]);
    $passengers = $stmt->fetchAll();
}

// Nếu là hành khách, lấy trạng thái yêu cầu (nếu có)
if ($is_passenger) {
    $stmt = $pdo->prepare("SELECT status FROM ride_requests WHERE trip_id = ? AND passenger_id = ?");
    $stmt->execute([$trip_id, $current_user_id]);
    $your_request_status = $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết chuyến đi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 rounded shadow">
    <h3 class="mb-3">🚌 Chi tiết chuyến đi</h3>
    <p><strong>Tài xế:</strong> <?= htmlspecialchars($trip['driver_name']) ?></p>
    <p><strong>Điểm đi:</strong> <?= $trip['from_location'] ?></p>
    <p><strong>Điểm đến:</strong> <?= $trip['to_location'] ?></p>
    <p><strong>Khởi hành:</strong> <?= date('d/m/Y H:i', strtotime($trip['departure_time'])) ?></p>
    <p><strong>Số chỗ trống:</strong> <?= $trip['available_seats'] ?></p>
    <p><strong>Giá vé:</strong> <?= number_format($trip['price'], 0, ',', '.') ?> VNĐ</p>

    <?php if ($is_driver): ?>
        <hr>
        <h5>📋 Hành khách đã gửi yêu cầu</h5>
        <?php if (count($passengers) > 0): ?>
            <table class="table">
                <thead>
                    <tr><th>Họ tên</th><th>Trạng thái</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($passengers as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['full_name']) ?></td>
                            <td><?= ucfirst($p['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">Chưa có ai gửi yêu cầu.</p>
        <?php endif; ?>
    <?php elseif ($is_passenger): ?>
        <hr>
        <h5>📨 Trạng thái yêu cầu của bạn:</h5>
        <?php if ($your_request_status): ?>
            <p class="alert alert-info">Yêu cầu của bạn hiện đang ở trạng thái: <strong><?= ucfirst($your_request_status) ?></strong></p>
        <?php else: ?>
            <form method="POST" action="../TimChuyenDi/book_trip.php">
                <input type="hidden" name="trip_id" value="<?= $trip['trip_id'] ?>">
                <button class="btn btn-success">📝 Gửi yêu cầu đặt chỗ</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <a href="../index.php" class="btn btn-secondary mt-4">← Quay lại trang chính</a>
</div>
</body>
</html>
