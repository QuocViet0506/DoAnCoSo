<?php
session_start();
require_once("../config/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Dangnhap/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$request_id = $_POST['request_id'] ?? 0;

// Kiểm tra quyền
$stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || !in_array($user['role'], ['driver', 'admin'])) {
    die("❌ Bạn không có quyền.");
}

// Xử lý hành động
if (in_array($action, ['accept', 'reject']) && $request_id > 0) {
    $driver_action = ($action === 'accept') ? 'accepted' : 'rejected';
    $stmt = $pdo->prepare("UPDATE ride_requests SET driver_action = ? WHERE request_id = ?");
    $stmt->execute([$driver_action, $request_id]);
}

header("Location: ../taixe.php");
exit();
