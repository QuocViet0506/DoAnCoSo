<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    //Hà Lê Quốc Việt 2280603661
    header("Location: ../Dangnhap/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Lấy danh sách chuyến đã hoàn thành mà người dùng là hành khách
$stmt = $pdo->prepare("
    SELECT t.trip_id, u.full_name AS driver_name, l1.name AS from_name, l2.name AS to_name
    FROM trips t
    JOIN ride_requests r ON t.trip_id = r.trip_id
    JOIN users u ON t.driver_id = u.user_id
    JOIN locations l1 ON t.from_location_id = l1.location_id
    JOIN locations l2 ON t.to_location_id = l2.location_id
    WHERE r.passenger_id = ? AND r.status = 'completed'
");
$stmt->execute([$user_id]);
$completed_trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý form đánh giá
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trip_id = $_POST['trip_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Lấy tài xế
    $driver_stmt = $pdo->prepare("SELECT driver_id FROM trips WHERE trip_id = ?");
    $driver_stmt->execute([$trip_id]);
    $driver_id = $driver_stmt->fetchColumn();

    $insert = $pdo->prepare("
        INSERT INTO reviews (reviewer_id, target_user_id, trip_id, rating, comment)
        VALUES (?, ?, ?, ?, ?)
    ");
    $insert->execute([$user_id, $driver_id, $trip_id, $rating, $comment]);
    $message = "🎉 Cảm ơn bạn đã đánh giá tài xế!";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đánh giá tài xế</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="bg-white p-4 rounded shadow">
        <h3>📝 Đánh giá tài xế</h3>

        <?php if ($message): ?>
            <div class="alert alert-success mt-3"><?= $message ?></div>
        <?php endif; ?>

        <?php if (count($completed_trips) === 0): ?>
            <div class="alert alert-warning mt-4">Bạn chưa có chuyến đi nào hoàn tất để đánh giá.</div>
        <?php else: ?>
            <form method="POST" class="mt-3">
                <div class="mb-3">
                    <label for="trip_id" class="form-label">Chọn chuyến đi đã hoàn tất:</label>
                    <select name="trip_id" id="trip_id" class="form-select" required>
                        <option value="">-- Chọn --</option>
                        <?php foreach ($completed_trips as $trip): ?>
                            <option value="<?= $trip['trip_id'] ?>">
                                <?= $trip['from_name'] ?> → <?= $trip['to_name'] ?> - Tài xế: <?= $trip['driver_name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="rating">Điểm đánh giá (1-5):</label>
                    <input type="number" name="rating" min="1" max="5" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="comment">Bình luận:</label>
                    <textarea name="comment" rows="4" class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
