<?php
session_start();
include('../config/db.php');

// Verificar si es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 1) {
    echo "Acceso denegado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $metodo = $_POST['metodo'];

    // Insertar el método de pago en la base de datos
    $sql = "INSERT INTO MetodoPago (metodo) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $metodo);

    if ($stmt->execute()) {
        header("Location: ../pages/admin_dashboard.php?status=metodo_creado");
    } else {
        echo "Error al crear el método de pago: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
