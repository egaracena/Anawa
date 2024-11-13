<?php
session_start();
include('../config/db.php');

// Verificar si hay una sesión activa
if (!isset($_SESSION['user_id'])) {
    echo "Acceso denegado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idusu = $_SESSION['user_id'];
    $asunto = $_POST['asunto'];
    $mensaje = $_POST['mensaje'];

    // Insertar el nuevo ticket en la base de datos
    $sql_insert_ticket = "INSERT INTO TicketSoporte (idusu, asunto, mensaje) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_insert_ticket);
    $stmt->bind_param("iss", $idusu, $asunto, $mensaje);

    if ($stmt->execute()) {
        header("Location: servicio_cliente.php?success=ticket_creado");
    } else {
        header("Location: crear_ticket.php?error=error_db");
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Ticket de Soporte</title>
    <link rel="stylesheet" href="/Anawa/assets/css/crear_tickets.css">
</head>

<body>
    <section class="form-container">
        <h2>Crear Nuevo Ticket</h2>

        <form action="crear_ticket.php" method="POST">
            <label for="asunto">Asunto:</label>
            <input type="text" name="asunto" id="asunto" required>

            <label for="mensaje">Mensaje:</label>
            <textarea name="mensaje" id="mensaje" rows="5" required></textarea>
            <div class="btn-container">
                <button type="submit" class="btn-enviar">Enviar Ticket</button>
            </div>
        </form>
        <div class="btn-container">
            <a href="servicio_cliente.php" class="btn-volver">Volver a Mis Tickets</a>
        </div>
    </section>
</body>

</html>