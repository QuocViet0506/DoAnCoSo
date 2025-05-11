<?php
session_start();
require_once '../config/config.php';

if ($_SESSION["role"] !== "admin") {
    exit("🚫 Truy cập bị từ chối.");
}

$tickets = $pdo->query("
    SELECT t.*, u.full_name, u.email 
    FROM support_tickets t 
    JOIN users u ON t.user_id = u.user_id
    ORDER BY t.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Yêu cầu hỗ trợ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('../images/hochiminh_night.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }
        .container {
            background-color: rgba(0, 0, 0, 0.75);
            padding: 30px;
            border-radius: 12px;
            margin-top: 40px;
        }
        table td, table th {
            vertical-align: middle !important;
        }
        .badge-status {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container shadow-lg">
        <h2 class="text-warning mb-4">📨 Danh sách yêu cầu hỗ trợ</h2>

        <?php if (empty($tickets)): ?>
            <div class="alert alert-info text-dark bg-light">Không có yêu cầu hỗ trợ nào.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-dark table-hover align-middle">
                    <thead class="table-warning text-dark text-center">
                        <tr>
                            <th>Người gửi</th>
                            <th>Email</th>
                            <th>Chủ đề</th>
                            <th>Nội dung</th>
                            <th>Trạng thái</th>
                            <th>Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['full_name']) ?></td>
                            <td><?= htmlspecialchars($t['email']) ?></td>
                            <td><?= htmlspecialchars($t['subject']) ?></td>
                            <td><?= nl2br(htmlspecialchars($t['message'])) ?></td>
                            <td>
                                <?= $t['status'] === 'pending'
                                    ? '<span class="badge bg-warning text-dark badge-status">⏳ Đang chờ</span>'
                                    : '<span class="badge bg-success badge-status">✅ Đã xử lý</span>'
                                ?>
                            </td>
                            <td><?= date("d/m/Y H:i", strtotime($t['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
