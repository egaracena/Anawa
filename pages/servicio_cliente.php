<?php
session_start();
include('../config/db.php');

// Verificar si hay una sesión activa
if (!isset($_SESSION['user_id'])) {
    echo "Acceso denegado.";
    exit;
}

$idusu = $_SESSION['user_id'];

// Consultar los tickets del usuario y sus respuestas
$sql_tickets = "SELECT idticket, asunto, mensaje, estado, respuesta, fecha_envio, fecha_respuesta 
                FROM TicketSoporte 
                WHERE idusu = ? 
                ORDER BY fecha_envio DESC";
$stmt_tickets = $conn->prepare($sql_tickets);
$stmt_tickets->bind_param("i", $idusu);
$stmt_tickets->execute();
$result_tickets = $stmt_tickets->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicio al Cliente</title>
    <link rel="stylesheet" href="/Anawa/assets/css/servicio_cliente.css">
</head>

<body>

    <section class="form-container">
        <h2>Servicio al Cliente</h2>
        <form action="../scripts/process_servicio_cliente.php" method="POST">
            <label for="asunto">Asunto:</label>
            <input type="text" id="asunto" name="asunto" required>

            <label for="mensaje">Mensaje:</label>
            <textarea id="mensaje" name="mensaje" rows="5" required></textarea>

            <button type="submit">Enviar Mensaje</button>
        </form>
        <p>Nos pondremos en contacto contigo lo antes posible.</p>
    </section>

    <!-- Sección para mostrar los tickets y sus respuestas -->
    <section class="tickets-container">
        <h2>Mis Tickets de Soporte</h2>
        <?php if ($result_tickets->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Asunto</th>
                        <th>Mensaje</th>
                        <th>Estado</th>
                        <th>Respuesta</th>
                        <th>Fecha de Envío</th>
                        <th>Fecha de Respuesta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ticket = $result_tickets->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ticket['asunto']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['mensaje']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['estado']); ?></td>
                            <td>
                                <?php
                                if ($ticket['estado'] == 'respondido') {
                                    echo htmlspecialchars($ticket['respuesta']);
                                } else {
                                    echo "Aún sin respuesta";
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['fecha_envio']); ?></td>
                            <td>
                                <?php
                                if ($ticket['fecha_respuesta']) {
                                    echo htmlspecialchars($ticket['fecha_respuesta']);
                                } else {
                                    echo "Pendiente";
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tienes tickets de soporte creados.</p>
        <?php endif; ?>
    </section>

</body>

</html>

<?php
$stmt_tickets->close();
$conn->close();
?>
