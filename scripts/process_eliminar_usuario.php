<?php
session_start();
include('../config/db.php');

// Verificar si es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 1) {
    echo "Acceso denegado.";
    exit;
}

if (isset($_GET['idusu'])) {
    $idusu = $_GET['idusu'];

    // Eliminar el usuario
    $sql = "DELETE FROM Usuario WHERE idusu = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idusu);

    if ($stmt->execute()) {
        header("Location: ../pages/admin_dashboard.php?status=usuario_eliminado");
    } else {
        echo "Error al eliminar el usuario: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
