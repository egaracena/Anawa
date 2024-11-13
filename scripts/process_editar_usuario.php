<?php
session_start();
include('../config/db.php');

// Verificar si es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 1) {
    echo "Acceso denegado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idusu = $_POST['idusu'];
    $nomusu = $_POST['nomusu'];
    $email = $_POST['email'];
    $idver = $_POST['idver'];

    // Actualizar los datos del usuario
    $sql = "UPDATE Usuario SET nomusu = ?, email = ?, idver = ? WHERE idusu = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $nomusu, $email, $idver, $idusu);

    if ($stmt->execute()) {
        header("Location: ../pages/admin_dashboard.php?status=usuario_editado");
    } else {
        echo "Error al actualizar el usuario: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
