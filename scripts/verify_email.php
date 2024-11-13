<?php
include('../config/db.php');

// Verificar si el token está presente en la URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Buscar el usuario por el token
    $sql = "SELECT * FROM Usuario WHERE token_verificacion = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Actualizar el estado del usuario a "verificado"
        $sql_update = "UPDATE Usuario SET verificado = 1, token_verificacion = NULL WHERE token_verificacion = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('s', $token);
        
        if ($stmt_update->execute()) {
            echo "Tu correo ha sido verificado. Ahora puedes iniciar sesión.";
        } else {
            echo "Error al verificar el correo. Inténtalo nuevamente.";
        }
    } else {
        echo "Token inválido o ya ha sido usado.";
    }

    $stmt->close();
} else {
    echo "Token no proporcionado.";
}

$conn->close();
?>
