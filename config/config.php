<?php
// Thông tin kết nối
define("DB_HOST", "localhost");
define("DB_NAME", "carpool_app");
define("DB_USER", "root");
//Hà Lê Quốc Việt 2280603661
define("DB_PASS", "");

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,          // Bật báo lỗi dạng exception
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Mặc định fetch ra mảng liên kết
        PDO::ATTR_EMULATE_PREPARES => false,                   // Tắt emulation để tăng bảo mật
    ]);
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}
?>
