<?php
session_start();
require_once '../config/config.php';

$my_id = $_SESSION['user_id'];
//Hà Lê Quốc Việt 2280603661
$my_role = $_SESSION['role'];

// 👉 Lấy danh sách user ngược vai trò (tài xế → khách hàng và ngược lại)
$target_role = $my_role === 'driver' ? 'passenger' : 'driver';

$stmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE role = ? AND user_id != ?");
$stmt->execute([$target_role, $my_id]);
$users = $stmt->fetchAll();
?>

<h3>Chọn người bạn muốn nhắn tin</h3>
<ul>
    <?php foreach ($users as $u): ?>
        <li><a href="chat.php?to=<?= $u['user_id'] ?>"><?= htmlspecialchars($u['full_name']) ?></a></li>
    <?php endforeach; ?>
</ul>
