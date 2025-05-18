<?php
session_start();
require_once '../config/config.php';

// ✅ Chặn truy cập nếu không phải admin
//Hà Lê Quốc Việt 2280603661
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit("⛔ Truy cập bị từ chối.");
}

// ✅ Lấy danh sách liên hệ kèm tên & vai trò người gửi
$stmt = $pdo->query("
    SELECT cm.*, u.full_name, u.role
    FROM contact_messages cm
    JOIN users u ON cm.email = u.email
    ORDER BY cm.created_at DESC
");
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý liên hệ người dùng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">📨 Liên hệ từ người dùng & tài xế</h2>

    <?php if (empty($messages)): ?>
        <div class="alert alert-info">Chưa có liên hệ nào được gửi.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Người gửi</th>
                    <th>Vai trò</th>
                    <th>Email</th>
                    <th>Chủ đề</th>
                    <th>Nội dung</th>
                    <th>Thời gian</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr>
                        <td><?= htmlspecialchars($msg['full_name']) ?></td>
                        <td>
                            <?= $msg['role'] === 'driver' ? '🚗 Tài xế' : ($msg['role'] === 'passenger' ? '👤 Hành khách' : '🔧 Khác') ?>
                        </td>
                        <td><?= htmlspecialchars($msg['email']) ?></td>
                        <td><?= htmlspecialchars($msg['subject']) ?></td>
                        <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
                        <td><?= $msg['created_at'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
