<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Dangnhap/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'passenger';
$message = '';

// Xử lý xác nhận hoàn thành (tài xế)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_driver') {
    $trip_id = filter_var($_POST['trip_id'], FILTER_VALIDATE_INT);
    if ($trip_id) {
        $update = $pdo->prepare("UPDATE trips SET driver_confirmed = 1, status = 'completed' WHERE trip_id = ? AND driver_id = ?");
        $update->execute([$trip_id, $user_id]);
        $message = "✅ Bạn đã xác nhận hoàn thành chuyến đi.";
    } else {
        $message = "❌ Chuyến đi không hợp lệ.";
    }
}

// Xử lý đánh giá
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rate') {
    $trip_id = filter_var($_POST['trip_id'], FILTER_VALIDATE_INT);
    $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT);
    $comment = trim($_POST['comment']);

    if ($trip_id && $rating && $rating >= 1 && $rating <= 5) {
        $driver_stmt = $pdo->prepare("SELECT driver_id FROM trips WHERE trip_id = ? AND driver_confirmed = 1");
        $driver_stmt->execute([$trip_id]);
        $driver_id = $driver_stmt->fetchColumn();

        if ($driver_id) {
            $insert = $pdo->prepare("
                INSERT INTO reviews (reviewer_id, target_user_id, trip_id, rating, comment)
                VALUES (?, ?, ?, ?, ?)
            ");
            $insert->execute([$user_id, $driver_id, $trip_id, $rating, $comment]);
            $message = "🎉 Cảm ơn bạn đã đánh giá tài xế!";
        } else {
            $message = "❌ Chuyến đi chưa được tài xế xác nhận hoàn tất.";
        }
    } else {
        $message = "❌ Đánh giá không hợp lệ.";
    }
}

// Xử lý thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay') {
    $trip_id = filter_var($_POST['trip_id'], FILTER_VALIDATE_INT);

    if ($trip_id) {
        $trip_stmt = $pdo->prepare("SELECT driver_id, price_confirmed FROM trips WHERE trip_id = ? AND driver_confirmed = 1 AND is_paid = 0");
        $trip_stmt->execute([$trip_id]);
        $trip = $trip_stmt->fetch(PDO::FETCH_ASSOC);

        if ($trip && $trip['price_confirmed']) {
            $driver_id = $trip['driver_id'];
            $amount = $trip['price_confirmed'];

            $insert = $pdo->prepare("INSERT INTO transactions (trip_id, user_id, driver_id, amount, status) VALUES (?, ?, ?, ?, 'completed')");
            $insert->execute([$trip_id, $user_id, $driver_id, $amount]);

            $update = $pdo->prepare("UPDATE trips SET is_paid = 1 WHERE trip_id = ?");
            $update->execute([$trip_id]);

            $message = "💰 Thanh toán thành công cho chuyến đi!";
        } else {
            $message = "❌ Không thể thanh toán. Vui lòng kiểm tra trạng thái chuyến đi.";
        }
    }
}

// Lấy danh sách chuyến đi
try {
    if ($role === 'driver') {
        $stmt = $pdo->prepare("
            SELECT t.trip_id, u.full_name AS passenger_name, l1.name AS from_name, l2.name AS to_name, 
                   t.departure_time, t.price, t.driver_confirmed, t.is_paid
            FROM trips t
            LEFT JOIN ride_requests r ON t.trip_id = r.trip_id
            LEFT JOIN users u ON r.passenger_id = u.user_id
            JOIN locations l1 ON t.from_location_id = l1.location_id
            JOIN locations l2 ON t.to_location_id = l2.location_id
            WHERE t.driver_id = ? AND t.status = 'completed'
        ");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $pdo->prepare("
            SELECT t.trip_id, u.full_name AS driver_name, l1.name AS from_name, l2.name AS to_name, 
                   t.departure_time, t.price, t.driver_confirmed, t.is_paid,
                   (SELECT COUNT(*) FROM reviews r WHERE r.trip_id = t.trip_id AND r.reviewer_id = ?) AS has_reviewed
            FROM trips t
            JOIN ride_requests r ON t.trip_id = r.trip_id
            JOIN users u ON t.driver_id = u.user_id
            JOIN locations l1 ON t.from_location_id = l1.location_id
            JOIN locations l2 ON t.to_location_id = l2.location_id
            WHERE r.passenger_id = ? AND t.status = 'completed'
        ");
        $stmt->execute([$user_id, $user_id]);
    }
    $completed_trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Kiểm tra số lượng chuyến đi lấy được
    $message .= "<div class='alert alert-info'>Số chuyến đi lấy được: " . count($completed_trips) . "</div>";
} catch (PDOException $e) {
    $message = "❌ Lỗi truy vấn: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đánh giá & Thanh toán</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', sans-serif;
        }
        .rating-box {
            max-width: 700px;
            margin: 40px auto;
            padding: 24px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        .btn-space {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="rating-box">
            <h3 class="text-center">⭐ Đánh giá & 💰 Thanh toán tài xế</h3>
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?= strpos($message, '❌') === false ? 'success' : 'danger' ?>"><?= $message ?></div>
            <?php endif; ?>

            <?php if (count($completed_trips) === 0): ?>
                <div class="alert alert-info text-center">Bạn chưa có chuyến đi nào đã hoàn thành.</div>
            <?php endif; ?>

            <?php foreach ($completed_trips as $trip): ?>
                <div class="border-bottom mb-4 pb-3">
                    <p><strong>Chuyến đi:</strong> <?= htmlspecialchars($trip['from_name']) ?> → <?= htmlspecialchars($trip['to_name']) ?></p>
                    <p><strong><?= $role === 'driver' ? 'Hành khách' : 'Tài xế' ?>:</strong> <?= htmlspecialchars($role === 'driver' ? ($trip['passenger_name'] ?? 'Chưa có hành khách') : $trip['driver_name']) ?></p>
                    <p><strong>Ngày giờ khởi hành:</strong> <?= htmlspecialchars($trip['departure_time']) ?></p>
                    <p><strong>Giá tiền:</strong> <?= number_format($trip['price'], 0, ',', '.') ?> VNĐ</p>

                    <!-- Debug: Hiển thị trạng thái chuyến đi -->
                    <p><strong>Trạng thái:</strong> driver_confirmed = <?= $trip['driver_confirmed'] ?>, has_reviewed = <?= isset($trip['has_reviewed']) ? $trip['has_reviewed'] : 'Không xác định' ?></p>

                    <!-- Xác nhận hoàn thành (Tài xế) -->
                    <?php if ($role === 'driver' && $trip['driver_confirmed'] == 0): ?>
                        <form method="POST" class="mb-2">
                            <input type="hidden" name="trip_id" value="<?= $trip['trip_id'] ?>">
                            <input type="hidden" name="action" value="confirm_driver">
                            <button type="submit" class="btn btn-info w-100 btn-space">✅ Xác nhận hoàn thành (Tài xế)</button>
                        </form>
                    <?php elseif ($role === 'driver' && $trip['driver_confirmed'] == 1): ?>
                        <p class="text-success">✅ Tài xế đã xác nhận hoàn thành</p>
                    <?php endif; ?>

                    <!-- Đánh giá (Chỉ dành cho hành khách) -->
                    <?php if (isset($trip['has_reviewed']) && $role === 'passenger' && $trip['driver_confirmed'] && $trip['has_reviewed'] == 0): ?>
                        <form method="POST" class="mb-2">
                            <input type="hidden" name="trip_id" value="<?= $trip['trip_id'] ?>">
                            <input type="hidden" name="action" value="rate">
                            <select name="rating" class="form-select mb-2" required>
                                <option value="">Chọn điểm đánh giá</option>
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?= $i ?>"><?= $i ?> ⭐</option>
                                <?php endfor; ?>
                            </select>
                            <textarea name="comment" class="form-control mb-2" placeholder="Nhận xét (tuỳ chọn)" rows="2"></textarea>
                            <button type="submit" class="btn btn-success w-100 btn-space">Gửi đánh giá</button>
                        </form>
                    <?php elseif ($role === 'passenger' && isset($trip['has_reviewed']) && $trip['has_reviewed'] > 0): ?>
                        <p class="text-success">✅ Bạn đã đánh giá chuyến đi này.</p>
                    <?php elseif ($role === 'passenger' && !$trip['driver_confirmed']): ?>
                        <p class="text-warning">⏳ Chờ tài xế xác nhận hoàn thành.</p>
                    <?php else: ?>
                        <p class="text-danger">❌ Lỗi: Không hiển thị form đánh giá (debug).</p>
                    <?php endif; ?>

                    <!-- Thanh toán (Chỉ dành cho hành khách) -->
                    <?php if (isset($trip['has_reviewed']) && $role === 'passenger' && $trip['driver_confirmed'] && $trip['has_reviewed'] > 0 && !$trip['is_paid']): ?>
                        <form method="POST">
                            <input type="hidden" name="trip_id" value="<?= $trip['trip_id'] ?>">
                            <input type="hidden" name="action" value="pay">
                            <button type="submit" class="btn btn-primary w-100">💳 Thanh toán chuyến đi</button>
                        </form>
                    <?php elseif ($role === 'passenger' && $trip['is_paid']): ?>
                        <p class="text-success mt-2">✅ Đã thanh toán</p>
                    <?php elseif ($role === 'passenger' && isset($trip['has_reviewed']) && !$trip['has_reviewed']): ?>
                        <p class="text-warning mt-2">⏳ Vui lòng đánh giá trước khi thanh toán.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <a href="../Dashboard/dashboard.php" class="btn btn-outline-secondary w-100 mt-2">⬅ Quay lại bảng điều khiển</a>
        </div>
    </div>
</body>
</html>
