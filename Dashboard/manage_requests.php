<?php
session_start();
require_once "../config/Database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "driver") {
    header("Location: ../Dangnhap/login.php");
    //Hà Lê Quốc Việt 2280603661
    exit();
}

$driver_id = $_SESSION["user_id"];

// Xử lý duyệt hoặc từ chối yêu cầu
if (isset($_GET['action'], $_GET['request_id'])) {
    $action = $_GET['action'];
    $request_id = $_GET['request_id'];

    // Lấy thông tin yêu cầu
    $stmt = $pdo->prepare("SELECT rr.*, t.available_seats, t.trip_id FROM ride_requests rr
                           JOIN trips t ON rr.trip_id = t.trip_id
                           WHERE rr.request_id = ? AND t.driver_id = ?");
    $stmt->execute([$request_id, $driver_id]);
    $request = $stmt->fetch();

    if ($request) {
        if ($action === 'accept') {
            if ($request['available_seats'] > 0) {
                $pdo->beginTransaction();

                $pdo->prepare("UPDATE ride_requests SET status = 'accepted' WHERE request_id = ?")
                    ->execute([$request_id]);

                $pdo->prepare("UPDATE trips SET available_seats = available_seats - 1 WHERE trip_id = ?")
                    ->execute([$request['trip_id']]);

                $pdo->commit();
                $msg = "✅ Đã chấp nhận yêu cầu.";
            } else {
                $msg = "❌ Không còn chỗ trống.";
            }
        } elseif ($action === 'reject') {
            $pdo->prepare("UPDATE ride_requests SET status = 'rejected' WHERE request_id = ?")
                ->execute([$request_id]);
            $msg = "🛑 Đã từ chối yêu cầu.";
        }
    } else {
        $msg = "Không tìm thấy yêu cầu hoặc không có quyền.";
    }
}

// Lấy các yêu cầu đặt chỗ cho chuyến của tài xế
$stmt = $pdo->prepare("SELECT rr.request_id, rr.status, u.full_name AS passenger_name,
                              lf.name AS from_name, lt.name AS to_name, t.departure_time
                       FROM ride_requests rr
                       JOIN users u ON rr.passenger_id = u.user_id
                       JOIN trips t ON rr.trip_id = t.trip_id
                       JOIN locations lf ON t.from_location_id = lf.location_id
                       JOIN locations lt ON t.to_location_id = lt.location_id
                       WHERE t.driver_id = ?
                       ORDER BY rr.request_time DESC");
$stmt->execute([$driver_id]);
$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Yêu cầu đặt chỗ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container bg-white rounded p-4 shadow">
    <h3>📥 Danh sách yêu cầu đặt chỗ</h3>

    <?php if (isset($msg)): ?>
        <div class="alert alert-info"><?= $msg ?></div>
    <?php endif; ?>

    <?php if (count($requests)): ?>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Hành khách</th>
                    <th>Điểm đi</th>
                    <th>Điểm đến</th>
                    <th>Khởi hành</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['passenger_name']) ?></td>
                        <td><?= $r['from_name'] ?></td>
                        <td><?= $r['to_name'] ?></td>
                        <td><?= $r['departure_time'] ?></td>
                        <td><?= ucfirst($r['status']) ?></td>
                        <td>
                            <?php if ($r['status'] === 'pending'): ?>
                                <a href="?action=accept&request_id=<?= $r['request_id'] ?>" class="btn btn-success btn-sm">✔ Duyệt</a>
                                <a href="?action=reject&request_id=<?= $r['request_id'] ?>" class="btn btn-danger btn-sm">✖ Từ chối</a>
                            <?php else: ?>
                                <span class="text-muted">Đã xử lý</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning mt-4">Hiện không có yêu cầu nào.</div>
    <?php endif; ?>
</div>
</body>
</html>
