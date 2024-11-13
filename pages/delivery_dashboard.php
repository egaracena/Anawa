<?php
include '../config/db.php';
session_start();

// Verificar si el usuario está logueado y si es delivery
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 4) {
    echo "Acceso denegado.";
    exit;
}

// Obtener el idusu de la sesión
$idusu = $_SESSION['user_id'];

// Obtener los pedidos disponibles para ser entregados con el pago confirmado
$query_pedidos_disponibles = "
    SELECT P.idped, Pr.nomprod, DP.cantidad, P.idusu as cliente, P.latitud, P.longitud
    FROM Pedido P
    JOIN DetallePedido DP ON P.idped = DP.idped
    JOIN Producto Pr ON DP.idprod = Pr.idprod
    LEFT JOIN Entrega E ON P.idped = E.idped
    JOIN Pago PA ON P.idped = PA.idped
    WHERE (E.idped IS NULL OR E.estado = 'Disponible') AND PA.estado_deposito = 'confirmado'
    ORDER BY P.idped DESC";
$pedidos_disponibles = $conn->query($query_pedidos_disponibles);

// Obtener los pedidos asignados al delivery actual
$query_pedidos_asignados = "
    SELECT E.idped, E.estado, E.fechaent, P.latitud, P.longitud
    FROM Entrega E
    INNER JOIN Pedido P ON E.idped = P.idped
    WHERE E.idusu = '$idusu' AND E.estado != 'Finalizado'
    ORDER BY E.idped DESC";
$pedidos_asignados = $conn->query($query_pedidos_asignados);

// Obtener los pedidos finalizados
$query_pedidos_finalizados = "
    SELECT E.idped, E.estado, E.fechaent, P.latitud, P.longitud
    FROM Entrega E
    INNER JOIN Pedido P ON E.idped = P.idped
    WHERE E.idusu = '$idusu' AND E.estado = 'Finalizado'
    ORDER BY E.idped DESC";
$pedidos_finalizados = $conn->query($query_pedidos_finalizados);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Delivery</title>
    <link rel="stylesheet" href="/Anawa/assets/css/delivery_dashboard_styles.css">
</head>
<body>
    <div class="delivery-container">
        <h1>Bienvenido al Panel de Delivery</h1>

        <!-- Mostrar pedidos donde los compradores solicitan delivery -->
        <section>
            <h2>Pedidos Disponibles</h2>
            <?php if ($pedidos_disponibles->num_rows > 0): ?>
                <table class="delivery-table">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Cliente</th>
                            <th>Ubicación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($pedido = $pedidos_disponibles->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $pedido['idped']; ?></td>
                            <td><?php echo $pedido['nomprod']; ?></td>
                            <td><?php echo $pedido['cantidad']; ?></td>
                            <td><?php echo $pedido['cliente']; ?></td>
                            <td>
                                <?php if ($pedido['latitud'] && $pedido['longitud']): ?>
                                    <a href="https://www.google.com/maps?q=<?php echo $pedido['latitud']; ?>,<?php echo $pedido['longitud']; ?>" target="_blank">
                                        Ver Ubicación en Google Maps
                                    </a>
                                <?php else: ?>
                                    <span>No disponible</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="../scripts/asignar_pedido.php" method="POST">
                                    <input type="hidden" name="idped" value="<?php echo $pedido['idped']; ?>">
                                    <button type="submit" class="btn-aceptar">Aceptar Pedido</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay pedidos disponibles en este momento.</p>
            <?php endif; ?>
        </section>

        <!-- Mostrar pedidos asignados -->
        <section>
            <h2>Mis Pedidos Asignados</h2>
            <?php if ($pedidos_asignados->num_rows > 0): ?>
                <table class="delivery-table">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Estado</th>
                            <th>Fecha de Entrega</th>
                            <th>Ubicación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($pedido = $pedidos_asignados->fetch_assoc()): ?>
                        <tr id="pedido-<?php echo $pedido['idped']; ?>">
                            <td><?php echo $pedido['idped']; ?></td>
                            <td class="estado"><?php echo $pedido['estado']; ?></td>
                            <td><?php echo $pedido['fechaent']; ?></td>
                            <td>
                                <?php if ($pedido['latitud'] && $pedido['longitud']): ?>
                                    <a href="https://www.google.com/maps?q=<?php echo $pedido['latitud']; ?>,<?php echo $pedido['longitud']; ?>" target="_blank">
                                        Ver Ubicación en Google Maps
                                    </a>
                                <?php else: ?>
                                    <span>No disponible</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-aceptar confirmar-entrega" data-id="<?php echo $pedido['idped']; ?>">Confirmar Entrega</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No tienes pedidos asignados en este momento.</p>
            <?php endif; ?>
        </section>

        <!-- Mostrar pedidos finalizados -->
        <section>
            <h2>Pedidos Finalizados</h2>
            <?php if ($pedidos_finalizados->num_rows > 0): ?>
                <table class="delivery-table">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Estado</th>
                            <th>Fecha de Entrega</th>
                            <th>Ubicación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($pedido = $pedidos_finalizados->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $pedido['idped']; ?></td>
                            <td class="estado"><?php echo $pedido['estado']; ?></td>
                            <td><?php echo $pedido['fechaent']; ?></td>
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
                <p>No tienes pedidos finalizados.</p>
            <?php endif; ?>
        </section>
    </div>

    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Manejador de clic para confirmar la entrega
            $('.confirmar-entrega').on('click', function () {
                const idped = $(this).data('id');
                $.ajax({
                    type: "POST",
                    url: "../scripts/confirm_delivery.php",
                    data: { idped: idped },
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            $('#pedido-' + idped + ' .estado').text('Finalizado');
                            alert(response.message);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        alert("Ocurrió un error al confirmar la entrega.");
                    }
                });
            });
        });
    </script>

</body>
</html>
