<?php
session_start();
include('../config/db.php');

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Verificar que las contraseñas coincidan
    if ($password !== $confirm_password) {
        echo "Las contraseñas no coinciden.";
        exit;
    }

    // Validar la longitud y complejidad de la contraseña
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/', $password)) {
        echo "La contraseña debe cumplir con los requisitos.";
        exit;
    }

    // Verificar el token en la base de datos y que no haya expirado
    $sql = "SELECT * FROM Usuario WHERE token_verificacion = ? AND expiracion_token > NOW() LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // El token es válido y no ha expirado
        $user = $result->fetch_assoc();

        // Hashear la nueva contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Actualizar la contraseña en la base de datos
        $sql_update = "UPDATE Usuario SET contraseña = ?, token_verificacion = NULL, expiracion_token = NULL WHERE idusu = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $hashed_password, $user['idusu']);

        if ($stmt_update->execute()) {
            echo "Tu contraseña ha sido restablecida exitosamente. Ahora puedes <a href='../pages/login.php'>iniciar sesión</a>.";
        } else {
            echo "Error al actualizar la contraseña.";
        }
    } else {
        // El token no es válido o ha expirado
        echo "El enlace de restablecimiento de contraseña no es válido o ha expirado.";
    }

    $stmt->close();
}

$conn->close();
?>
