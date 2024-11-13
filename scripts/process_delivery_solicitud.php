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
    if (isset($_POST['turno'])) {
        $turnos = $_POST['turno'];
        $turno_seleccionado = implode(', ', $turnos);

        // Insertar la solicitud en la tabla Solicitudes
        $sql = "INSERT INTO Solicitudes (idusu, tipo_solicitud, turno) VALUES (?, 'delivery', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $idusu, $turno_seleccionado);

        if ($stmt->execute()) {
            echo "Solicitud enviada exitosamente.";
            header("Location: ../pages/trabaja_con_nosotros.php?status=pendiente");
            exit;
        } else {
            echo "Error al enviar la solicitud: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Por favor selecciona al menos un turno.";
    }
}

$conn->close();
?>
