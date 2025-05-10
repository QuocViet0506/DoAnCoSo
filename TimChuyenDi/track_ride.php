<?php
require_once '../config/config.php';

$trip_id = $_GET['trip_id'] ?? null;

if (!$trip_id) {
    echo "<p>Không có mã chuyến đi.</p>";
    exit;
}

// Lấy thông tin chuyến đi + tài xế + điểm đón
$sql = "SELECT t.trip_id, 
               u.full_name AS driver_name,
               u.latitude AS driver_lat, u.longitude AS driver_lng,
               l.latitude AS pickup_lat, l.longitude AS pickup_lng, l.name AS pickup_name
        FROM trips t
        JOIN users u ON t.driver_id = u.user_id
        JOIN locations l ON t.from_location_id = l.location_id
        WHERE t.trip_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$trip_id]);
$ride = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ride || !$ride['driver_lat']) {
    echo "<p>Không tìm thấy vị trí tài xế hoặc chuyến đi.</p>";
    exit;
}

echo "<h2>Theo dõi tài xế: {$ride['driver_name']}</h2>";
?>

<!-- Google Maps -->
<div id="map" style="height: 400px;"></div>

<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async defer></script>

<script>
    function initMap() {
        var driverPos = { lat: <?= $ride['driver_lat'] ?>, lng: <?= $ride['driver_lng'] ?> };
        var pickupPos = { lat: <?= $ride['pickup_lat'] ?>, lng: <?= $ride['pickup_lng'] ?> };

        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 14,
            center: driverPos
        });

        new google.maps.Marker({
            position: driverPos,
            map: map,
            title: 'Vị trí tài xế'
        });

        new google.maps.Marker({
            position: pickupPos,
            map: map,
            title: 'Điểm đón: <?= $ride['pickup_name'] ?>'
        });

        var directionsService = new google.maps.DirectionsService();
        var directionsRenderer = new google.maps.DirectionsRenderer();
        directionsRenderer.setMap(map);

        var request = {
            origin: driverPos,
            destination: pickupPos,
            travelMode: 'DRIVING'
        };

        directionsService.route(request, function(result, status) {
            if (status === 'OK') {
                directionsRenderer.setDirections(result);
            }
        });
    }
</script>

<?php
// Gửi thông báo khi tài xế đến gần điểm đón
if (abs($ride['driver_lat'] - $ride['pickup_lat']) < 0.005 && abs($ride['driver_lng'] - $ride['pickup_lng']) < 0.005) {
    echo "<script>alert('🚗 Tài xế sắp đến điểm đón!');</script>";
}
?>
