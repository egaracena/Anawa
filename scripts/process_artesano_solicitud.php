<?php
session_start();
include('../config/db.php');

// Verificar si hay una sesión activa
if (!isset($_SESSION['user_id'])) {
    echo "Debes iniciar sesión para acceder a esta página.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idusu = $_SESSION['user_id'];
    $idcom = $_POST['idcom'];

    // Insertar la solicitud en la tabla Solicitudes
    $sql = "INSERT INTO Solicitudes (idusu, tipo_solicitud, idcom) VALUES (?, 'artesano', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idusu, $idcom);

    if ($stmt->execute()) {
        echo "Solicitud enviada exitosamente.";
        header("Location: ../pages/vender.php?status=pendiente");
        exit;
    } else {
        echo "Error al enviar la solicitud: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
