<?php
session_start();
require_once "../config/config.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "passenger") {
    header("Location: ../Dangnhap/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $trip_id = $_POST["trip_id"];
    $passenger_id = $_SESSION["user_id"];
    $pickup_location_id = $_POST["pickup_location_id"];
    $dropoff_location_id = $_POST["dropoff_location_id"];

    // Lấy thông tin chuyến
    $stmt = $pdo->prepare("SELECT available_seats, price FROM trips WHERE trip_id = ?");
    $stmt->execute([$trip_id]);
    $trip = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($trip && $trip["available_seats"] > 0) {
        // Kiểm tra trùng request
        $check = $pdo->prepare("SELECT * FROM ride_requests 
                                WHERE trip_id = ? AND passenger_id = ?");
        $check->execute([$trip_id, $passenger_id]);

        if ($check->rowCount() > 0) {
            echo "<script>alert('Bạn đã gửi yêu cầu cho chuyến này rồi!'); window.history.back();</script>";
            exit();
        }

        // Tính giá chia đều
        $seats_remaining = $trip["available_seats"];
        $shared_price = $trip["price"] / max($seats_remaining, 1);

        // Ghi yêu cầu đã chấp nhận
        $insert = $pdo->prepare("INSERT INTO ride_requests 
            (trip_id, passenger_id, pickup_location_id, dropoff_location_id, status, payment_method) 
            VALUES (?, ?, ?, ?, 'accepted', 'cash')");
        $insert->execute([$trip_id, $passenger_id, $pickup_location_id, $dropoff_location_id]);

        // Giảm ghế còn lại
        $update = $pdo->prepare("UPDATE trips SET available_seats = available_seats - 1 WHERE trip_id = ?");
        $update->execute([$trip_id]);

        // Thông báo
        echo "<script>alert('Đặt chỗ thành công! Giá chia: " . number_format($shared_price, 0, ',', '.') . " VND');
              window.location.href='../Dashboard/dashboard.php';</script>";
    } else {
        echo "<script>alert('Chuyến đi không còn chỗ trống!'); window.history.back();</script>";
    }
}
?>
