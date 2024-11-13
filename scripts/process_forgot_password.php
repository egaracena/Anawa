<?php
session_start();
include('../config/db.php');

// Incluir PHPMailer para el envío del correo
require '../libs/PHPMailer/src/PHPMailer.php';
require '../libs/PHPMailer/src/SMTP.php';
require '../libs/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Verificar si el email existe en la base de datos
    $sql = "SELECT * FROM Usuario WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generar un token único para la recuperación de la contraseña
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(50));
        $expira_en = date("Y-m-d H:i:s", strtotime('+1 hour')); // El token expira en 1 hora

        // Guardar el token en la base de datos
        $sql_token = "UPDATE Usuario SET token_verificacion = ?, expiracion_token = ? WHERE email = ?";
        $stmt_token = $conn->prepare($sql_token);
        $stmt_token->bind_param("sss", $token, $expira_en, $email);
        $stmt_token->execute();

        // Enviar el correo con el enlace de recuperación
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'anawa.ecommerce@gmail.com';
            $mail->Password   = 'h b f p f q x k r p y k z q c c';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('anawa.ecommerce@gmail.com', 'Anawa Ecommerce');
            $mail->addAddress($email);

            $mail->addReplyTo('anawa.ecommerce@gmail.com', 'Soporte Anawa');
            $mail->addCustomHeader("Return-Path", "anawa.ecommerce@gmail.com");

            $mail->isHTML(true);
            $mail->Subject = 'Restablecer contrasenia';
            $reset_link = "http://localhost/Anawa/pages/reset_password.php?token=" . $token;
            $mail->Body    = "Hola, para restablecer tu contraseña, haz clic en el siguiente enlace: <a href='" . $reset_link . "'>Restablecer contraseña</a>";

            $mail->send();
            echo 'Se ha enviado un enlace para restablecer tu contraseña. Revisa tu correo.';
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
        }

    } else {
        echo "No se encontró ninguna cuenta con ese correo electrónico.";
    }

    $stmt->close();
}

$conn->close();
?>
