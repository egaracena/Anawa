<?php
// Iniciar sesión
session_start();

// Incluir la conexión a la base de datos
include('../config/db.php');

// Incluir PHPMailer
require '../libs/PHPMailer/src/PHPMailer.php';
require '../libs/PHPMailer/src/SMTP.php';
require '../libs/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verificar si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar los datos del formulario
    $ci = $_POST['ci'];
    $nomusu = $_POST['nomusu'];
    $celular = $_POST['celular'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validaciones
    if (empty($ci) || empty($nomusu) || empty($celular) || empty($email) || empty($password) || empty($confirm_password)) {
        echo "Por favor, rellena todos los campos.";
        exit;
    }

    if ($password !== $confirm_password) {
        echo "Las contraseñas no coinciden.";
        exit;
    }
    
    // Validar reCAPTCHA
    $secret_key = '6LcQoTkqAAAAAB94rps__Ynj9kAeAMdMQznqG_JS';
    $recaptcha_response = $_POST['g-recaptcha-response'];

    // Verificar que el usuario haya interactuado con reCAPTCHA
    if (empty($recaptcha_response)) {
        echo "Por favor, confirma que no eres un robot.";
        exit;
    }

    // Validar la respuesta de reCAPTCHA con la API de Google
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_data = array(
        'secret' => $secret_key,
        'response' => $recaptcha_response
    );

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($recaptcha_data),
        ),
    );

    $context  = stream_context_create($options);
    $verify = file_get_contents($recaptcha_url, false, $context);
    $captcha_success = json_decode($verify);

    if (!$captcha_success->success) {
        echo "La validación de reCAPTCHA falló. Intenta nuevamente.";
        exit;
    }

    // Generar un token de verificación
    $token = bin2hex(random_bytes(50));

    // Hashear la contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Asignar idver = 3 para el rol de Comprador
    $idver = 3;

    // Insertar el nuevo usuario en la base de datos con el token de verificación y el idver
    $sql = "INSERT INTO Usuario (ci, nomusu, celular, email, contraseña, token_verificacion, verificado, idver) 
            VALUES (?, ?, ?, ?, ?, ?, 0, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $ci, $nomusu, $celular, $email, $hashed_password, $token, $idver);

    if ($stmt->execute()) {
        // Enviar correo de verificación usando PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'anawa.ecommerce@gmail.com'; // Tu correo de Gmail
            $mail->Password   = 'h b f p f q x k r p y k z q c c'; // Tu contraseña de Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            // Destinatario
            $mail->setFrom('anawa.ecommerce@gmail.com', 'Anawa Ecommerce');
            $mail->addAddress($email);

            $mail->addReplyTo('anawa.ecommerce@gmail.com', 'Soporte Anawa');
            $mail->addCustomHeader("Return-Path", "anawa.ecommerce@gmail.com");

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = 'Verifica tu cuenta';
            $verification_link = "http://localhost/Anawa/scripts/verify_email.php?token=" . $token;
            $mail->Body    = "Dale click en el siguiente enlace para verificar tu cuenta: <a href='" . $verification_link . "'>Verificar cuenta</a>";

            // Enviar correo
            $mail->send();
            echo 'Se ha enviado un correo de verificación. Por favor, revisa tu bandeja de entrada.';
        } catch (Exception $e) {
            echo "Error al enviar el correo de verificación: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error al registrar el usuario: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
