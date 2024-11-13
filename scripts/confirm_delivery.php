<?php
session_start();
include '../config/db.php';

// Verificar si el usuario está autenticado y es delivery
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 4) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit();
}

$idped = isset($_POST['idped']) ? intval($_POST['idped']) : null;
$idusu = $_SESSION['user_id'];

if (!$idped) {
    echo json_encode(['success' => false, 'message' => 'ID del pedido no proporcionado.']);
    exit();
}

// Actualizar el estado de la entrega para el delivery
$sql = "UPDATE entrega SET delivery_confirmed = 1 WHERE idped = '$idped' AND idusu = '$idusu'";
if ($conn->query($sql) === TRUE) {
    // Comprobar si tanto el comprador como el delivery han confirmado
    $sql_check = "SELECT delivery_confirmed, buyer_confirmed FROM entrega WHERE idped = '$idped'";
    $result = $conn->query($sql_check);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($row['delivery_confirmed'] && $row['buyer_confirmed']) {
            // Cambiar el estado a 'Finalizado' y actualizar la fecha de entrega
            $sql_update = "UPDATE entrega SET estado = 'Finalizado', fechaent = CURDATE() WHERE idped = '$idped'";
            if ($conn->query($sql_update) === TRUE) {
                echo json_encode(['success' => true, 'message' => 'Entrega confirmada y estado actualizado a Finalizado con fecha de entrega registrada.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado y la fecha de entrega: ' . $conn->error]);
            }
        } else {
            echo json_encode(['success' => true, 'message' => 'Entrega confirmada, pendiente la confirmación del comprador.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al verificar el estado de confirmación: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al confirmar la entrega: ' . $conn->error]);
}

$conn->close();
