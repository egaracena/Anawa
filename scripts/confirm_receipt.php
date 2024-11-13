<?php
session_start();
include '../config/db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit();
}

$idped = $_POST['idped'];
$idusu = $_SESSION['user_id'];

// Actualizar la entrega para marcarla como confirmada por el comprador
$sql = "UPDATE entrega SET buyer_confirmed = TRUE WHERE idped = '$idped'";

if ($conn->query($sql) === TRUE) {
    // Verificar si tanto el delivery como el comprador han confirmado
    $sql_check = "SELECT delivery_confirmed, buyer_confirmed FROM entrega WHERE idped = '$idped'";
    $result = $conn->query($sql_check);
    $row = $result->fetch_assoc();

    if ($row['delivery_confirmed'] && $row['buyer_confirmed']) {
        // Actualizar el estado a "Finalizado"
        $sql_update = "UPDATE entrega SET estado = 'Finalizado' WHERE idped = '$idped'";
        $conn->query($sql_update);
    }
    
    echo json_encode(['success' => true, 'message' => 'Recepción confirmada']);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al confirmar la recepción']);
}

$conn->close();
?>