<?php
// Bắt đầu session (nếu chưa có)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cấu hình kết nối database
define("DB_HOST", "localhost");
define("DB_NAME", "carpool_app");
define("DB_USER", "root");
define("DB_PASS", "");

// Tuỳ chọn kết nối PDO
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    // Tạo kết nối PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        $options
    );
} catch (PDOException $e) {
    // Hiển thị lỗi kết nối rõ ràng
    die("<strong>Lỗi kết nối CSDL:</strong> " . $e->getMessage());
}
?>