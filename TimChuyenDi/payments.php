<?php
session_start();
require_once '../config/Database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'passenger') {
     //Hà Lê Quốc Việt 2280603661
    header("Location: ../Dangnhap/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['method'], $_POST['amount'])) {
    $request_id = $_POST['request_id'];
    $method = $_POST['method'];
    $amount = floatval($_POST['amount']);

    $stmt = $pdo->prepare("SELECT * FROM ride_requests WHERE request_id = ? AND passenger_id = ? AND status = 'accepted'");
    $stmt->execute([$request_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        $status = in_array($method, ['momo', 'zalopay', 'vnpay']) ? 'processing' : 'completed';

        $insert = $pdo->prepare("INSERT INTO payments (request_id, payment_method, amount, status) VALUES (?, ?, ?, ?)");
        if ($insert->execute([$request_id, $method, $amount, $status])) {
            $success = ($status === 'completed') ? "Thanh toán thành công!" : "Đang xử lý thanh toán qua $method...";
        } else {
            $error = "Thanh toán thất bại.";
        }
    } else {
        $error = "Không tìm thấy yêu cầu phù hợp.";
    }
}

$stmt = $pdo->prepare("SELECT r.request_id, r.trip_id, l1.name as from_loc, l2.name as to_loc, t.price
                        FROM ride_requests r
                        JOIN trips t ON r.trip_id = t.trip_id
                        JOIN locations l1 ON t.from_location_id = l1.location_id
                        JOIN locations l2 ON t.to_location_id = l2.location_id
                        LEFT JOIN payments p ON r.request_id = p.request_id
                        WHERE r.passenger_id = ? AND r.status = 'accepted' AND p.request_id IS NULL");
$stmt->execute([$user_id]);
$unpaidRequests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh toán</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-5">
<div class="container bg-white p-4 rounded shadow" style="max-width: 600px;">
    <h3 class="mb-4">Thanh toán</h3>

    <?php if ($success): ?><div class="alert alert-success"> <?= $success ?> </div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"> <?= $error ?> </div><?php endif; ?>

    <?php if (count($unpaidRequests) > 0): ?>
        <form method="POST">
            <div class="mb-3">
                <label>Chọn chuyến:</label>
                <select name="request_id" class="form-select" required>
                    <option value="">-- Chọn chuyến --</option>
                    <?php foreach ($unpaidRequests as $r): ?>
                        <option value="<?= $r['request_id'] ?>">
                            [#<?= $r['request_id'] ?>] <?= $r['from_loc'] ?> → <?= $r['to_loc'] ?> - <?= number_format($r['price']) ?> VND
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label>Phương thức thanh toán:</label>
                <select name="method" class="form-select" required>
                    <option value="cash">Tiền mặt</option>
                    <option value="momo">MoMo</option>
                    <option value="zalopay">ZaloPay</option>
                    <option value="vnpay">Thẻ ngân hàng (VNPay)</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Số tiền (VND):</label>
                <input type="number" step="1000" name="amount" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Xác nhận thanh toán</button>
        </form>
    <?php else: ?>
        <div class="alert alert-info">Không có chuyến nào cần thanh toán.</div>
    <?php endif; ?>
</div>
</body>
</html>
        