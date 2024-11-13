<?php
session_start();
include('../config/db.php');

// Verificar si es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 1) {
    echo "Acceso denegado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idticket = $_POST['idticket'];
    $respuesta = $_POST['respuesta'];

    // Verificar que la respuesta no esté vacía
    if (empty($respuesta)) {
        header("Location: ../pages/admin_dashboard.php?section=servicio_cliente&error=empty_response");
        exit;
    }

    // Actualizar el ticket con la respuesta del administrador
    $sql_update_ticket = "UPDATE TicketSoporte 
                          SET respuesta = ?, estado = 'respondido', fecha_respuesta = NOW() 
                          WHERE idticket = ?";
    $stmt = $conn->prepare($sql_update_ticket);
    $stmt->bind_param("si", $respuesta, $idticket);

    if ($stmt->execute()) {
        header("Location: ../pages/admin_dashboard.php?section=servicio_cliente&success=respondido");
    } else {
        header("Location: ../pages/admin_dashboard.php?section=servicio_cliente&error=error_db");
    }

    $stmt->close();
    $conn->close();
}
?>
