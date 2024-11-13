<?php
session_start();
include('../config/db.php');

$idusu = $_SESSION['user_id'];
$lat = $_POST['lat'];
$lng = $_POST['lng'];

// Guardar el pedido junto con la ubicación del comprador
$sql_pedido = "INSERT INTO Pedido (idusu, cantprod, latitud, longitud) VALUES ('$idusu', '0', '$lat', '$lng')";

if ($conn->query($sql_pedido) === TRUE) {
    echo "Pedido realizado con éxito.";
    // Redirigir al usuario a la página de confirmación o a su listado de pedidos
    header("Location: ../pages/mis_pedidos.php");
} else {
    echo "Error: " . $sql_pedido . "<br>" . $conn->error;
}

$conn->close();
?>
