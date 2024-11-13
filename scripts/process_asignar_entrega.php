<?php
session_start();
include('../config/db.php');

// Verificar si es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 1) {
    echo "Acceso denegado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idped = $_POST['idped'];
    $idusu_delivery = $_POST['idusu_delivery'];

    // Asignar el pedido al delivery
    $sql = "INSERT INTO Entrega (idped, idusu, estado, delivery_confirmed, buyer_confirmed) 
            VALUES (?, ?, 'asignado', 0, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idped, $idusu_delivery);

    if ($stmt->execute()) {
        header("Location: ../pages/admin_dashboard.php?status=entrega_asignada");
    } else {
        echo "Error al asignar la entrega: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
