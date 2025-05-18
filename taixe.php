<?php
session_start();
require_once("../config/config.php");
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Dangnhap/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT full_name, role FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$full_name = htmlspecialchars($user['full_name']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>CarpoolNow - Tài xế</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php">CarpoolNow</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="hangkhach.php">Khách hàng</a></li>
                    <li class="nav-item"><a class="nav-link active" href="#">Tài xế</a></li>
                    <li class="nav-item"><a class="nav-link" href="tintuc.php">Tin tức</a></li>
                    <li class="nav-item"><a class="nav-link" href="../contact.php">Liên hệ</a></li>
                </ul>
                <span class="navbar-text me-3 text-white">👋 <?= $full_name ?></span>
                <a href="../Dangnhap/logout.php" class="btn btn-warning">Đăng xuất</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h2>Trang Tài xế</h2>
        <p>Chào mừng <?= $full_name ?>! Đây là trang dành cho tài xế.</p>
        <a href="manage_requests.php" class="btn btn-outline-secondary">Xem yêu cầu</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>