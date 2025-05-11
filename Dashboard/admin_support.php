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
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
    }

    .container {
        background: rgba(0, 0, 0, 0.75); /* nền đen mờ */
        border-radius: 12px;
        padding: 30px;
        color: #fff;
        margin: 40px auto;
        width: 90%;
        max-width: 1000px;
        box-shadow: 0 0 25px rgba(0, 0, 0, 0.5);
    }

    h2 {
        color: #ffcc00;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 8px;
        overflow: hidden;
    }

    th, td {
        padding: 12px 16px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #333;
        color: #fff;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    .status-pending {
        color: #ffc107;
        font-weight: bold;
    }

    .status-done {
        color: #28a745;
        font-weight: bold;
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
