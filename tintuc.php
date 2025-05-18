<?php
session_start();
require_once("../config/config.php");

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Dangnhap/login.php");
    exit();
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name, role FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$full_name = htmlspecialchars($user['full_name']);
$role = $user['role'];

// Xử lý đăng tin tức
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_news'])) {
    $title = htmlspecialchars($_POST['title']);
    $content = htmlspecialchars($_POST['content']);
    $posted_at = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("INSERT INTO news (title, content, posted_by, posted_at, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $content, $full_name . ' (' . $role . ')', $posted_at, $user_id]);
    $message = "Tin tức đã được đăng thành công!";
}

// Lấy tất cả tin tức
$news_query = $pdo->prepare("SELECT * FROM news ORDER BY posted_at DESC");
$news_query->execute();
$news_result = $news_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>CarpoolNow - Tin tức</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('../assets/images/hochiminh_night.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin-top: 20px;
        }
        .news-form {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .news-item {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php">CarpoolNow</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="hangkhach.php">Khách hàng</a></li>
                    <li class="nav-item"><a class="nav-link" href="taixe.php">Tài xế</a></li>
                    <li class="nav-item"><a class="nav-link active" href="#">Tin tức</a></li>
                    <li class="nav-item"><a class="nav-link" href="../contact.php">Liên hệ</a></li>
                </ul>
                <span class="navbar-text me-3 text-white">👋 <?= $full_name ?> (<?= $role ?>)</span>
                <a href="../Dangnhap/logout.php" class="btn btn-warning">Đăng xuất</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Form đăng tin tức -->
        <div class="news-form">
            <h3>Đăng tin tức</h3>
            <?php if (isset($message)) echo "<div class='alert alert-success'>$message</div>"; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="title" class="form-label">Tiêu đề</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Nội dung</label>
                    <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                </div>
                <button type="submit" name="submit_news" class="btn btn-primary">Đăng tin</button>
            </form>
        </div>

        <!-- Danh sách tin tức -->
        <h3>Tin tức mới nhất</h3>
        <?php foreach ($news_result as $news): ?>
            <div class="news-item">
                <h4><?= htmlspecialchars($news['title']) ?></h4>
                <p><?= htmlspecialchars($news['content']) ?></p>
                <small>Đăng bởi: <?= htmlspecialchars($news['posted_by']) ?> - <?= $news['posted_at'] ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>