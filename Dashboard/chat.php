<?php
session_start();
require_once '../config/config.php';

$my_id = $_SESSION['user_id'];
//Hà Lê Quốc Việt 2280603661
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

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trò chuyện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .message-box {
            position: relative;
            max-width: 70%;
            word-wrap: break-word;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .bg-primary {
            background-color: #007bff !important;
        }
        .bg-light {
            background-color: #f8f9fa !important;
        }
        .input-group {
            margin-top: 10px;
        }
        .message-container {
            max-height: 400px;
            overflow-y: auto;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        .message-container small {
            font-size: 0.75rem;
            color: #6c757d;
        }
        .message-form {
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h3 class="text-center mb-4">💬 Trò chuyện với <?= htmlspecialchars($receiver['full_name']) ?></h3>

        <div class="card">
            <div class="card-body message-container">
                <?php foreach ($messages as $m): ?>
                    <div class="d-flex <?= $m['sender_id'] == $my_id ? 'justify-content-end' : 'justify-content-start' ?> mb-3">
                        <div class="message-box <?= $m['sender_id'] == $my_id ? 'bg-primary text-white' : 'bg-light' ?>">
                            <strong><?= $m['sender_id'] == $my_id ? 'Bạn' : $receiver['full_name'] ?>:</strong>
                            <p class="mb-0"><?= htmlspecialchars($m['message']) ?></p>
                            <small><?= $m['sent_at'] ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <form method="POST" class="message-form">
            <div class="input-group">
                <textarea name="message" class="form-control" placeholder="Nhập tin nhắn..." required></textarea>
                <button type="submit" class="btn btn-primary">Gửi</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
