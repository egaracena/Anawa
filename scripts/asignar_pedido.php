<?php
session_start();
include '../config/db.php';

// Verificar si el usuario es delivery y está logueado
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 4) {
    echo "Acceso denegado.";
    exit;
}

$idusu = $_SESSION['user_id'];
$idped = $_POST['idped'];

// Calcular la fecha de entrega (3 días después de hoy)
$fecha_entrega = date('Y-m-d', strtotime('+3 days')); // Fecha actual + 7 días

// Asignar el pedido al delivery
$sql = "INSERT INTO Entrega (idped, idusu, estado, fechaent) VALUES ('$idped', '$idusu', 'En camino', '$fecha_entrega')";

if ($conn->query($sql) === TRUE) {
    echo "Pedido asignado correctamente.";
    header("Location: ../pages/delivery_dashboard.php"); // Redirigir al panel de delivery
} else {
    echo "Error al asignar el pedido: " . $conn->error;
}

$conn->close();
?>
