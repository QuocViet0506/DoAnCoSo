<?php
session_start();
require_once("../config/config.php");

$driver_id = $_POST['driver_id'] ?? null;
$user_id = $_POST['user_id'] ?? null;

if (!$driver_id || !$user_id) {
    die("Thiếu thông tin tài xế hoặc hành khách.");
}

// Kiểm tra trùng lặp
$stmtCheck = $pdo->prepare("SELECT * FROM ride_requests WHERE user_id = ? AND driver_id = ? AND status = 'pending'");
$stmtCheck->execute([$user_id, $driver_id]);
if ($stmtCheck->rowCount() > 0) {
    header("Location: ../hangkhach.php?msg=da_gui_yeu_cau");
    exit();
}

// Gửi yêu cầu mới
$stmt = $pdo->prepare("INSERT INTO ride_requests (user_id, driver_id, status) VALUES (?, ?, 'pending')");
$stmt->execute([$user_id, $driver_id]);

header("Location: ../hangkhach.php?msg=yeu_cau_gui_thanh_cong");
exit();
