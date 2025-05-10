<?php
session_start();
require_once '../config/config.php';

if ($_SESSION["role"] !== "admin") {
    exit("Truy cập bị từ chối.");
}

$tickets = $pdo->query("
    SELECT t.*, u.full_name, u.email 
    FROM support_tickets t 
    JOIN users u ON t.user_id = u.user_id
    ORDER BY t.created_at DESC
")->fetchAll();
?>

<h2>Danh sách yêu cầu hỗ trợ</h2>
<table class="table table-bordered">
    <thead>
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
            <td><?= $t['status'] === 'pending' ? '⏳ Đang chờ' : '✅ Đã xử lý' ?></td>
            <td><?= $t['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
