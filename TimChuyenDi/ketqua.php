<?php
require_once '../config/Database.php';

$start = trim($_GET['start_location']);
$end = trim($_GET['end_location']);
$time = $_GET['departure_time'];

$stmt = $pdo->prepare("SELECT t.*, l1.name AS from_name, l2.name AS to_name
                       FROM trips t
                       JOIN locations l1 ON t.from_location_id = l1.location_id
                       JOIN locations l2 ON t.to_location_id = l2.location_id
                       WHERE l1.name LIKE ? AND l2.name LIKE ? AND t.departure_time >= ?
                       ORDER BY t.departure_time ASC");
$stmt->execute(["%$start%", "%$end%", $time]);
$results = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả chuyến đi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5 bg-light">
<div class="container bg-white p-4 rounded shadow">
    <h2 class="mb-4 text-center">Kết quả tìm kiếm</h2>
    <?php if (count($results) > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Điểm đi</th>
                    <th>Điểm đến</th>
                    <th>Khởi hành</th>
                    <th>Giá</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $trip): ?>
                    <tr>
                        <td><?= htmlspecialchars($trip['from_name']) ?></td>
                        <td><?= htmlspecialchars($trip['to_name']) ?></td>
                        <td><?= $trip['departure_time'] ?></td>
                        <td><?= number_format($trip['price']) ?>đ</td>
                        <td><a href="book_trip.php?trip_id=<?= $trip['trip_id'] ?>" class="btn btn-primary btn-sm">Đặt</a></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">Không tìm thấy chuyến đi phù hợp.</div>
    <?php endif ?>
</div>
</body>
</html>
