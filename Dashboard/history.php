<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'passenger') {
    header("Location: ../Dangnhap/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Truy vấn lấy lịch sử các chuyến đã hoàn thành
$stmt = $pdo->prepare("
    SELECT t.trip_id, t.departure_time, t.price, t.available_seats,
           l1.name AS from_location, l2.name AS to_location,
           u.full_name AS driver_name
    FROM ride_requests r
    JOIN trips t ON r.trip_id = t.trip_id
    JOIN users u ON t.driver_id = u.user_id
    JOIN locations l1 ON t.from_location_id = l1.location_id
    JOIN locations l2 ON t.to_location_id = l2.location_id
    WHERE r.passenger_id = ? AND r.status = 'completed'
    ORDER BY t.departure_time DESC
");
$stmt->execute([$user_id]);
$trips = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử chuyến đi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 rounded shadow">
    <h2 class="mb-4">📜 Lịch sử chuyến đi đã đặt</h2>

    <?php if (empty($trips)): ?>
        <div class="alert alert-info">Bạn chưa có chuyến đi nào đã hoàn thành.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Tài xế</th>
                    <th>Điểm đi</th>
                    <th>Điểm đến</th>
                    <th>Thời gian</th>
                    <th>Giá</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trips as $trip): ?>
                    <tr>
                        <td><?= htmlspecialchars($trip['driver_name']) ?></td>
                        <td><?= htmlspecialchars($trip['from_location']) ?></td>
                        <td><?= htmlspecialchars($trip['to_location']) ?></td>
                        <td><?= date("d/m/Y H:i", strtotime($trip['departure_time'])) ?></td>
                        <td><?= number_format($trip['price'], 0, ',', '.') ?> VNĐ</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
