<?php
session_start();
include('../config/db.php');

// Verificar si el usuario está conectado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No estás conectado.']);
    exit;
}

$idusu = $_SESSION['user_id'];

// Consultar el estado de la solicitud
$sql = "SELECT estado FROM Solicitudes WHERE idusu = ? AND estado IN ('aprobado', 'rechazado') LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idusu);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $solicitud = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'estado' => $solicitud['estado']]);
} else {
    echo json_encode(['status' => 'pending', 'message' => 'Solicitud pendiente de aprobación.']);
}

$stmt->close();
$conn->close();
?>
