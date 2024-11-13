<?php 
session_start();
include('../config/db.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$idusu = $_SESSION['user_id'];

// Consultar los pedidos no asignados a delivery
$query_pedidos_no_asignados = "
    SELECT P.idped, DP.cantidad, P.idusu, P.latitud, P.longitud
    FROM Pedido P
    JOIN DetallePedido DP ON P.idped = DP.idped
    LEFT JOIN Entrega E ON P.idped = E.idped
    WHERE P.idusu = '$idusu' AND E.idped IS NULL";
$pedidos_no_asignados = $conn->query($query_pedidos_no_asignados);

// Consultar los pedidos en progreso
$query_pedidos_progreso = "
    SELECT P.idped, DP.cantidad, P.idusu, E.estado, E.fechaent, U.nomusu as delivery_name, U.celular as delivery_phone, E.delivery_confirmed
    FROM Pedido P
    JOIN DetallePedido DP ON P.idped = DP.idped
    LEFT JOIN Entrega E ON P.idped = E.idped
    LEFT JOIN Usuario U ON E.idusu = U.idusu
    WHERE P.idusu = '$idusu' AND E.estado != 'Finalizado'";
$pedidos_progreso = $conn->query($query_pedidos_progreso);

// Consultar los pedidos finalizados
$query_pedidos_finalizados = "
    SELECT P.idped, DP.cantidad, P.idusu, E.estado, E.fechaent, U.nomusu as delivery_name, U.celular as delivery_phone
    FROM Pedido P
    JOIN DetallePedido DP ON P.idped = DP.idped
    LEFT JOIN Entrega E ON P.idped = E.idped
    LEFT JOIN Usuario U ON E.idusu = U.idusu
    WHERE P.idusu = '$idusu' AND E.estado = 'Finalizado'";
$pedidos_finalizados = $conn->query($query_pedidos_finalizados);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos</title>
    <link rel="stylesheet" href="/Anawa/assets/css/mis_pedidos_styles.css">
</head>
<body>
    <div class="pedidos-container">
        <h1>Mis Pedidos</h1>

        <!-- Pedidos no asignados -->
        <section>
            <h2>Pedidos No Asignados</h2>
            <?php if ($pedidos_no_asignados->num_rows > 0): ?>
                <table class="pedidos-table">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Cantidad</th>
                            <th>Ubicación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($pedido = $pedidos_no_asignados->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $pedido['idped']; ?></td>
                            <td><?php echo $pedido['cantidad']; ?></td>
                            <td>
                                <?php if ($pedido['latitud'] && $pedido['longitud']): ?>
                                    <a href="https://www.google.com/maps?q=<?php echo $pedido['latitud']; ?>,<?php echo $pedido['longitud']; ?>" target="_blank">
                                        Ver Ubicación en Google Maps
                                    </a>
                                <?php else: ?>
                                    <span>No disponible</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No tienes pedidos no asignados.</p>
            <?php endif; ?>
        </section>

        <!-- Pedidos en progreso -->
        <section>
            <h2>Pedidos en Progreso</h2>
            <?php if ($pedidos_progreso->num_rows > 0): ?>
                <table class="pedidos-table">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Cantidad</th>
                            <th>Estado</th>
                            <th>Fecha de Entrega</th>
                            <th>Delivery</th>
                            <th>Teléfono Delivery</th>
                            <th>Contactar</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($pedido = $pedidos_progreso->fetch_assoc()): ?>
                        <tr id="pedido-<?php echo $pedido['idped']; ?>">
                            <td><?php echo $pedido['idped']; ?></td>
                            <td><?php echo $pedido['cantidad']; ?></td>
                            <td class="estado"><?php echo $pedido['estado'] ? $pedido['estado'] : 'En espera'; ?></td>
                            <td><?php echo $pedido['fechaent'] ? $pedido['fechaent'] : 'Pendiente'; ?></td>
                            <td><?php echo $pedido['delivery_name'] ? $pedido['delivery_name'] : 'No asignado'; ?></td>
                            <td><?php echo $pedido['delivery_phone'] ? $pedido['delivery_phone'] : 'N/A'; ?></td>
                            <td>
                                <?php if ($pedido['delivery_phone']): ?>
                                    <a href="https://wa.me/<?php echo $pedido['delivery_phone']; ?>" target="_blank">
                                        <button class="btn-whatsapp">Contactar por WhatsApp</button>
                                    </a>
                                <?php else: ?>
                                    <span>No disponible</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($pedido['estado'] === 'Finalizado'): ?>
                                    <span>Pedido finalizado</span>
                                <?php else: ?>
                                    <button class="btn-confirmar confirmar-recepcion" data-id="<?php echo $pedido['idped']; ?>">Confirmar Recepción</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No tienes pedidos en progreso.</p>
            <?php endif; ?>
        </section>

        <!-- Pedidos finalizados -->
        <section>
            <h2>Pedidos Finalizados</h2>
            <?php if ($pedidos_finalizados->num_rows > 0): ?>
                <table class="pedidos-table">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Cantidad</th>
                            <th>Estado</th>
                            <th>Fecha de Entrega</th>
                            <th>Delivery</th>
                            <th>Teléfono Delivery</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($pedido = $pedidos_finalizados->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $pedido['idped']; ?></td>
                            <td><?php echo $pedido['cantidad']; ?></td>
                            <td><?php echo $pedido['estado']; ?></td>
                            <td><?php echo $pedido['fechaent']; ?></td>
                            <td><?php echo $pedido['delivery_name']; ?></td>
                            <td><?php echo $pedido['delivery_phone']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No tienes pedidos finalizados aún.</p>
            <?php endif; ?>
        </section>
    </div>

    <script>
        document.querySelectorAll('.confirmar-recepcion').forEach(button => {
            button.addEventListener('click', function() {
                const idped = this.getAttribute('data-id');
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '../scripts/confirm_receipt.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        const respuesta = JSON.parse(this.responseText);
                        if (respuesta.success) {
                            document.querySelector(`#pedido-${idped} .estado`).textContent = "Recepción Confirmada";
                        } else {
                            alert("Error al confirmar la recepción: " + respuesta.error);
                        }
                    }
                };
                xhr.send('idped=' + idped);
            });
        });
    </script>
</body>
</html>
