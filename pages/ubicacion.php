<?php
session_start();
include('../config/db.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$idusu = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Ubicación de Entrega</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async defer></script>
    <script>
    var map;
    var marker;

    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: -17.824858, lng: 31.053028}, // Coordenadas por defecto
            zoom: 8
        });

        // Añadir marcador cuando se haga clic en el mapa
        map.addListener('click', function(event) {
            placeMarker(event.latLng);
            document.getElementById('lat').value = event.latLng.lat();
            document.getElementById('lng').value = event.latLng.lng();
        });
    }

    function placeMarker(location) {
        if (marker) {
            marker.setPosition(location);
        } else {
            marker = new google.maps.Marker({
                position: location,
                map: map
            });
        }
    }
    </script>
</head>
<body>
    <h2>Seleccionar Ubicación de Entrega</h2>
    <div id="map" style="height: 400px; width: 100%;"></div>

    <form action="../scripts/guardar_ubicacion.php" method="POST">
        <input type="hidden" id="lat" name="lat">
        <input type="hidden" id="lng" name="lng">
        <button type="submit">Guardar Ubicación y Realizar Pedido</button>
    </form>
</body>
</html>
