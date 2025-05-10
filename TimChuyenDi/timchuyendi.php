<?php
session_start();
require_once '../config/Database.php';

// Hàm tính khoảng cách giữa 2 tọa độ GPS
function haversine($lat1, $lon1, $lat2, $lon2) {
    $R = 6371; // km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) ** 2;
    return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

$results = [];
$locations = $pdo->query("SELECT * FROM locations ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_id = $_POST['from_location_id'];
    $to_id = $_POST['to_location_id'];
    $after_time = $_POST['departure_after'];

    // Lấy tọa độ điểm đón hành khách yêu cầu
    $stmt = $pdo->prepare("SELECT latitude, longitude FROM locations WHERE location_id = ?");
    $stmt->execute([$from_id]);
    $fromLoc = $stmt->fetch();

    // Tìm tất cả chuyến hợp lệ về thời gian và còn ghế
    $sql = "SELECT t.*, 
                   u.full_name AS driver_name,
                   lf.name AS from_name, lf.latitude AS flat, lf.longitude AS flng,
                   lt.name AS to_name, lt.location_id AS to_id
            FROM trips t
            JOIN users u ON t.driver_id = u.user_id
            JOIN locations lf ON t.from_location_id = lf.location_id
            JOIN locations lt ON t.to_location_id = lt.location_id
            WHERE t.departure_time >= ?
              AND t.available_seats > 0";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$after_time]);
    $trips = $stmt->fetchAll();

    foreach ($trips as $trip) {
        $distance = haversine($fromLoc['latitude'], $fromLoc['longitude'], $trip['flat'], $trip['flng']);
        if ($distance <= 3 && $trip['to_id'] == $to_id) {
            $results[] = $trip;
        }
    }
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tìm chuyến đi (Ride Pooling)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
<div class="container" style="max-width: 700px;">
    <div class="bg-white p-4 rounded shadow">
        <h3 class="text-center mb-4">Tìm chuyến đi chung</h3>

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
                <label>Thời gian khởi hành (sau)</label>
                <input type="datetime-local" name="departure_after" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Tìm chuyến</button>
        </form>
    </div>

    <?php if (!empty($results)): ?>
        <div class="mt-4 bg-white p-3 rounded shadow">
            <h5>Kết quả tìm kiếm:</h5>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Tài xế</th>
                    <th>Điểm đi</th>
                    <th>Điểm đến</th>
                    <th>Khởi hành</th>
                    <th>Giá chia</th>
                    <th>Hành động</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($results as $trip): ?>
                    <tr>
                        <td><?= htmlspecialchars($trip['driver_name']) ?></td>
                        <td><?= htmlspecialchars($trip['from_name']) ?></td>
                        <td><?= htmlspecialchars($trip['to_name']) ?></td>
                        <td><?= $trip['departure_time'] ?></td>
                        <td>
                            <?= number_format($trip['price'] / max($trip['available_seats'], 1), 0, ',', '.') ?> VND
                        </td>
                        <td>
                            <form method="POST" action="book_trip.php">
                                <input type="hidden" name="trip_id" value="<?= $trip['trip_id'] ?>">
                                <input type="hidden" name="pickup_location_id" value="<?= $from_id ?>">
                                <input type="hidden" name="dropoff_location_id" value="<?= $to_id ?>">
                                <button class="btn btn-sm btn-primary">Đặt đi chung</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="mt-4 alert alert-warning">Không tìm thấy chuyến đi phù hợp.</div>
    <?php endif; ?>
</div>
</body>
</html>
