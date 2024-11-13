<?php
session_start(); // Asegurarse de que la sesión esté iniciada
include('../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        echo "Por favor, rellena todos los campos.";
        exit;
    }

    // Buscar el usuario por su correo electrónico
    $sql = "SELECT * FROM Usuario WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verificar si la cuenta está verificada
        if ($user['verificado'] == 0) {
            echo "Tu cuenta no ha sido verificada. Por favor, revisa tu correo para verificar tu cuenta.";
            exit;
        }

        // Verificar la contraseña
        if (password_verify($password, $user['contraseña'])) {
            // Iniciar sesión del usuario
            $_SESSION['user_id'] = $user['idusu']; // Asegúrate de usar la variable correcta para el ID del usuario
            $_SESSION['username'] = $user['nomusu'];
            $_SESSION['idver'] = $user['idver'];  // Guardar el idver en la sesión

            // Verificar el estado de la solicitud
            if ($user['estado_solicitud'] == 'rechazado') {
                // Guardar el mensaje de rechazo en la sesión
                $_SESSION['mensaje_rechazo'] = "Lo sentimos, tu solicitud fue rechazada.";

                // Actualizar el estado_solicitud para que no se muestre el mensaje nuevamente
                $sql_update_estado = "UPDATE Usuario SET estado_solicitud = 'visto' WHERE idusu = ?";
                $stmt_update_estado = $conn->prepare($sql_update_estado);
                $stmt_update_estado->bind_param("i", $user['idusu']);
                $stmt_update_estado->execute();
                $stmt_update_estado->close();

                // Redirigir a la página correspondiente según el tipo de solicitud
                if ($user['idver'] == 3) {
                    header("Location: ../pages/vender.php"); // Redirigir a "Vender" si fue rechazado como Artesano
                } else {
                    header("Location: ../pages/trabaja_con_nosotros.php"); // Redirigir a "Trabaja con Nosotros" si fue rechazado como Delivery
                }
                exit;
            }

            // Redirigir según el rol del usuario (idver)
            switch ($user['idver']) {
                case 1:
                    header("Location: ../pages/admin_dashboard.php"); // Redirigir a la interfaz de Administrador
                    break;
                case 2:
                    header("Location: ../pages/artesano_dashboard.php"); // Redirigir a la interfaz de Artesano
                    break;
                case 3:
                    header("Location: ../index.php"); // Redirigir a la interfaz de Comprador
                    break;
                case 4:
                    header("Location: ../pages/delivery_dashboard.php"); // Redirigir a la interfaz de Delivery
                    break;
                default:
                    echo "Rol de usuario no identificado."; // Mostrar un mensaje de error si el rol no es válido
                    break;
            }
            exit;
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "El usuario no existe.";
    }

    $stmt->close();
}

$conn->close();
?>
