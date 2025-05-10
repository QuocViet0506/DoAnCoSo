<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if ($name && $email && $subject && $message) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at)
                                   VALUES (:name, :email, :subject, :message, NOW())");
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ]);
            echo "<script>alert('✅ Gửi thành công!'); window.location.href='../index.php';</script>";
        } catch (PDOException $e) {
            die("❌ Lỗi SQL: " . $e->getMessage());
        }
    } else {
        echo "<script>alert('❌ Vui lòng điền đầy đủ thông tin!'); window.history.back();</script>";
    }
} else {
    echo "Truy cập không hợp lệ.";
}
?>
