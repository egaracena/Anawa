<?php
session_start();
include('../config/db.php');

// Verificar si hay una sesión activa
if (!isset($_SESSION['user_id'])) {
    echo "Acceso denegado.";
    exit;
}

// Obtener los datos del formulario
$idusu = $_SESSION['user_id'];
$asunto = $_POST['asunto'];
$mensaje = $_POST['mensaje'];

// Insertar el ticket en la base de datos
$sql = "INSERT INTO TicketSoporte (idusu, asunto, mensaje) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $idusu, $asunto, $mensaje);

if ($stmt->execute()) {
    header("Location: ../pages/servicio_cliente.php?status=success");
} else {
    header("Location: ../pages/servicio_cliente.php?status=error");
}

$stmt->close();
$conn->close();
?>
