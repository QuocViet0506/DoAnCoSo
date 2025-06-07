<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("🚫 Truy cập bị từ chối.");
}

// Xử lý cập nhật trạng thái giao dịch
$success_message = '';
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT); // Kiểm tra id là số nguyên

    // Kiểm tra giá trị action hợp lệ
    if (!in_array($action, ['pending', 'completed', 'refunded']) || !$id) {
        $error_message = "❌ Thao tác không hợp lệ.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE transaction_id = ?");
            if ($stmt->execute([$action, $id])) {
                $success_message = "✅ Cập nhật trạng thái giao dịch thành công!";
            } else {
                $error_message = "❌ Lỗi cập nhật giao dịch.";
            }
        } catch (PDOException $e) {
            $error_message = "❌ Lỗi: " . $e->getMessage();
        }
    }
}

// Lấy danh sách giao dịch
try {
    $sql = "
        SELECT t.*, u.full_name AS passenger_name, d.full_name AS driver_name,
               l1.name AS from_name, l2.name AS to_name
        FROM transactions t
        JOIN trips tr ON t.trip_id = tr.trip_id
        JOIN users u ON t.user_id = u.user_id
        JOIN users d ON tr.driver_id = d.user_id
        JOIN locations l1 ON tr.from_location_id = l1.location_id
        JOIN locations l2 ON tr.to_location_id = l2.location_id
        ORDER BY t.created_at DESC
    ";
    $transactions = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "❌ Lỗi truy vấn: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý giao dịch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 1200px; margin-top: 40px; }
        .table th, .table td { vertical-align: middle; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5 bg-white p-4 rounded shadow">
        <h2 class="mb-4 text-primary">💳 Quản lý giao dịch thanh toán</h2>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php elseif (isset($success_message) && $success_message): ?>
            <div class="alert alert-success"><?= $success_message ?></div>
        <?php elseif (empty($transactions)): ?>
            <div class="alert alert-info">Không có giao dịch nào.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Giao dịch</th>
                            <th>Hành khách</th>
                            <th>Tài xế</th>
                            <th>Chuyến đi</th>
                            <th>Số tiền</th>
                            <th>Trạng thái</th>
                            <th>Thời gian</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($transactions as $row): 
                        $status_text = match ($row['status']) {
                            'completed' => '<span class="badge bg-success">✅ Hoàn tất</span>',
                            'refunded'  => '<span class="badge bg-secondary">🔁 Đã hoàn tiền</span>',
                            'pending'   => '<span class="badge bg-warning text-dark">⏳ Chờ xử lý</span>',
                            default     => '<span class="badge bg-secondary">⏳ Không xác định</span>',
                        };

                        $next_action = $row['status'] === 'completed' ? 'refunded' : ($row['status'] === 'pending' ? 'completed' : 'pending');
                        $label = $row['status'] === 'completed' ? 'Hoàn tiền' : ($row['status'] === 'pending' ? 'Xác nhận' : 'Đặt lại');
                        $btn_class = $row['status'] === 'completed' ? 'btn-outline-danger' : 'btn-outline-success';
                    ?>
                    <tr>
                        <td><?= $row['transaction_id'] ?></td>
                        <td><?= htmlspecialchars($row['passenger_name']) ?></td>
                        <td><?= htmlspecialchars($row['driver_name']) ?></td>
                        <td><?= htmlspecialchars($row['from_name']) ?> → <?= htmlspecialchars($row['to_name']) ?></td>
                        <td class="text-end text-success fw-semibold"><?= number_format($row['amount'], 0, ',', '.') ?> VNĐ</td>
                        <td><?= $status_text ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <?php if ($row['status'] !== 'refunded'): ?>
                                <a href="?action=<?= $next_action ?>&id=<?= $row['transaction_id'] ?>" class="btn btn-sm <?= $btn_class ?>">
                                    <?= $label ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Không khả dụng</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <a href="../Dashboard/dashboard.php" class="btn btn-outline-secondary mt-3">⬅ Quay lại</a>
    </div>
</body>
</html>
