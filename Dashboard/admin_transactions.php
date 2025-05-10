<?php
session_start();
require_once '../config/config.php';

if ($_SESSION['role'] !== 'admin') {
    exit("Truy cập bị từ chối.");
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
        echo "<p>Lỗi cập nhật giao dịch.</p>";
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

<h2>Quản lý giao dịch thanh toán</h2>
<table class="table table-bordered">
    <thead>
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
            $status_text = $row['status'] === 'completed' ? '✅ Hoàn tất' : ($row['status'] === 'refunded' ? '🔁 Đã hoàn tiền' : '⏳ Chờ xử lý');
            $next_action = $row['status'] === 'completed' ? 'refunded' : 'completed';
            $label = $row['status'] === 'completed' ? 'Hoàn tiền' : 'Hoàn tất';
        ?>
        <tr>
            <td><?= $row['payment_id'] ?></td>
            <td><?= htmlspecialchars($row['full_name']) ?></td>
            <td><?= $row['payment_method'] ?></td>
            <td><?= number_format($row['amount'], 0, ',', '.') ?> VNĐ</td>
            <td><?= $status_text ?></td>
            <td>
                <?php if ($row['status'] !== 'refunded'): ?>
                    <a href="?action=<?= $next_action ?>&id=<?= $row['payment_id'] ?>" class="btn btn-sm btn-outline-primary"><?= $label ?></a>
                <?php else: ?>
                    <span class="text-muted">Không khả dụng</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
