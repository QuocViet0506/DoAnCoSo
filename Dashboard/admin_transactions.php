<?php
session_start();
require_once '../config/config.php';

if ($_SESSION['role'] !== 'admin') {
    //Hà Lê Quốc Việt 2280603661
    exit("🚫 Truy cập bị từ chối.");
}

// Xử lý cập nhật trạng thái giao dịch
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];

    $stmt = $pdo->prepare("UPDATE payments SET status = ? WHERE payment_id = ?");
    if ($stmt->execute([$action, $id])) {
        header("Location: admin_transactions.php");
        exit();
    } else {
        echo "<div class='alert alert-danger'>❌ Lỗi cập nhật giao dịch.</div>";
    }
}

// Lấy danh sách giao dịch
$sql = "
    SELECT p.*, u.full_name 
    FROM payments p 
    JOIN ride_requests r ON p.request_id = r.request_id
    JOIN users u ON r.passenger_id = u.user_id
    ORDER BY p.created_at DESC
";
$payments = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý giao dịch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5 bg-white p-4 rounded shadow">
    <h2 class="mb-4 text-primary">💳 Quản lý giao dịch thanh toán</h2>

    <?php if (empty($payments)): ?>
        <div class="alert alert-info">Không có giao dịch nào.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Người thanh toán</th>
                        <th>Phương thức</th>
                        <th>Số tiền</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($payments as $row): 
                    $status_text = match ($row['status']) {
                        'completed' => '<span class="badge bg-success">✅ Hoàn tất</span>',
                        'refunded'  => '<span class="badge bg-secondary">🔁 Đã hoàn tiền</span>',
                        default     => '<span class="badge bg-warning text-dark">⏳ Chờ xử lý</span>',
                    };

                    $next_action = $row['status'] === 'completed' ? 'refunded' : 'completed';
                    $label = $row['status'] === 'completed' ? 'Hoàn tiền' : 'Xác nhận';
                    $btn_class = $row['status'] === 'completed' ? 'btn-outline-danger' : 'btn-outline-success';
                ?>
                <tr>
                    <td><?= $row['payment_id'] ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= ucfirst($row['payment_method']) ?></td>
                    <td class="text-end text-success fw-semibold"><?= number_format($row['amount'], 0, ',', '.') ?> VNĐ</td>
                    <td><?= $status_text ?></td>
                    <td>
                        <?php if ($row['status'] !== 'refunded'): ?>
                            <a href="?action=<?= $next_action ?>&id=<?= $row['payment_id'] ?>" class="btn btn-sm <?= $btn_class ?>">
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
</div>
</body>
</html>
