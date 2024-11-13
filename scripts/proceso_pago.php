<?php
session_start();
include('../config/db.php');

// Verificar si se ha recibido la información necesaria
if (!isset($_POST['idped']) || !isset($_POST['metodoPago']) || !isset($_POST['monto'])) {
    echo "Error: No se recibieron todos los datos necesarios.";
    exit();
}

$id_pedido = $_POST['idped'];
$metodo_pago = $_POST['metodoPago'];
$monto = $_POST['monto'];

// Actualizar la tabla `pago` con el estado "pendiente"
$sql = "INSERT INTO pago (fechapag, método, idped, estado_deposito) 
        VALUES (CURDATE(), '$metodo_pago', '$id_pedido', 'pendiente')";

if ($conn->query($sql) === TRUE) {
    echo "Pago registrado. Esperando confirmación del administrador.";
    // Redirigir a la página de confirmación de pedido
    header("Location: ../pages/confirmacion_pedido.php?idped=$id_pedido&success=true");
} else {
    echo "Error al registrar el pago: " . $conn->error;
}

$conn->close();
?>
