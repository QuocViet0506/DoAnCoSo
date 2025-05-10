<?php
session_start();
require_once '../config/config.php';

$my_id = $_SESSION['user_id'];
$to_id = $_GET['to'] ?? null;

if (!$to_id) {
    die("Người nhận không xác định.");
}

// 👉 Lấy tên người nhận
$stmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt->execute([$to_id]);
$receiver = $stmt->fetch();

if (!$receiver) die("Không tìm thấy người nhận.");

// 👉 Gửi tin nhắn nếu có
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    if ($msg !== '') {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$my_id, $to_id, $msg]);
    }
}

// 👉 Lấy lịch sử trò chuyện
$stmt = $pdo->prepare("
    SELECT * FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
    ORDER BY sent_at
");
$stmt->execute([$my_id, $to_id, $to_id, $my_id]);
$messages = $stmt->fetchAll();
?>

<h3>💬 Trò chuyện với <?= htmlspecialchars($receiver['full_name']) ?></h3>

<div style="max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
    <?php foreach ($messages as $m): ?>
        <div style="text-align: <?= $m['sender_id'] == $my_id ? 'right' : 'left' ?>;">
            <p><strong><?= $m['sender_id'] == $my_id ? 'Bạn' : $receiver['full_name'] ?>:</strong> <?= htmlspecialchars($m['message']) ?></p>
            <small><?= $m['sent_at'] ?></small>
        </div>
    <?php endforeach; ?>
</div>

<form method="POST" class="mt-3">
    <textarea name="message" class="form-control" required></textarea>
    <button type="submit" class="btn btn-primary mt-2">Gửi</button>
</form>

