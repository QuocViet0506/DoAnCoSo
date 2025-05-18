<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'passenger') {
    //Hà Lê Quốc Việt 2280603661
    header("Location: ../Dangnhap/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

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
    <style>
        body {
            background: #f0f2f5;
        }
        .container {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            margin-top: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
        }
        h2 {
            font-weight: 600;
        }
        table th, table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4 text-primary">📜 Lịch sử chuyến đi đã hoàn thành</h2>

    <?php if (empty($trips)): ?>
        <div class="alert alert-info text-center">
            🛑 Bạn chưa có chuyến đi nào đã hoàn thành.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Tài xế</th>
                        <th>Điểm đi</th>
                        <th>Điểm đến</th>
                        <th>Thời gian</th>
                        <th>Giá vé</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trips as $trip): ?>
                        <tr>
                            <td><?= htmlspecialchars($trip['driver_name']) ?></td>
                            <td><?= htmlspecialchars($trip['from_location']) ?></td>
                            <td><?= htmlspecialchars($trip['to_location']) ?></td>
                            <td><?= date("d/m/Y H:i", strtotime($trip['departure_time'])) ?></td>
                            <td class="text-end text-success fw-bold"><?= number_format($trip['price'], 0, ',', '.') ?> VNĐ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="../index.php" class="btn btn-outline-secondary">← Quay lại trang chính</a>
    </div>
</div>
</body>
</html>
