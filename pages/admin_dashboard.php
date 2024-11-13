<?php
session_start();
include('../config/db.php');

// Verificar si es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 1) {
    echo "Acceso denegado.";
    exit;
}

// Obtener todas las solicitudes pendientes con más información del usuario
$sql = "SELECT Solicitudes.*, Usuario.nomusu, Usuario.ci, Usuario.celular, Comunidad.nomcom 
        FROM Solicitudes
        LEFT JOIN Usuario ON Solicitudes.idusu = Usuario.idusu
        LEFT JOIN Comunidad ON Solicitudes.idcom = Comunidad.idcom
        WHERE Solicitudes.estado = 'pendiente'";
$result = $conn->query($sql);
$sql_solicitudes = "
    SELECT Solicitudes.*, Usuario.nomusu, Usuario.ci, Usuario.celular, Pago.idped
    FROM Solicitudes
    LEFT JOIN Usuario ON Solicitudes.idusu = Usuario.idusu
    LEFT JOIN Pago ON Solicitudes.idped = Pago.idped
    WHERE Solicitudes.estado = 'pendiente'";

$result_solicitudes = $conn->query($sql_solicitudes);
?>




<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Anawa/assets/css/admin_dashboard.css">
</head>

<body>
    <div class="toggle-menu" onclick="toggleSidebar()">☰</div>

    <script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('active');
    }
    </script>


    <div class="sidebar">
        <ul>
            <li><a href="#solicitudes" onclick="showSection('solicitudes')">Solicitudes Pendientes</a></li>
            <li><a href="#marketing" onclick="showSection('marketing')">Publicar Marketing</a></li>
            <li><a href="#comunidad" onclick="showSection('comunidad')">Crear Comunidad</a></li>
            <li><a href="#categoria" onclick="showSection('categoria')">Crear Categoría</a></li>
            <li><a href="#usuarios" onclick="showSection('usuarios')">Gestionar Usuarios</a></li>
            <li><a href="#entregas" onclick="showSection('entregas')">Asignar Entregas</a></li>
            <li><a href="#inventario" onclick="showSection('inventario')">Ver Inventario</a></li>
            <li><a href="#stock" onclick="showSection('stock')">Actualizar Stock</a></li>
            <li><a href="#reporte" onclick="showSection('reporte')">Generar Reporte de Inventario</a></li>
            <li><a href="#servicio_cliente" onclick="showSection('servicio_cliente')">Servicio al Cliente</a></li>

        </ul>
    </div>


    <!-- Contenido principal -->
    <div class="content">
        <section id="solicitudes" class="admin-solicitudes">
            <h2>Solicitudes Pendientes</h2>
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Carnet</th>
                            <th>Celular</th>
                            <th>Tipo de Solicitud</th>
                            <th>Comunidad/Turno</th>
                            <th>Fecha de Solicitud</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nomusu']); ?></td>
                                <td><?php echo htmlspecialchars($row['ci']); ?></td>
                                <td><?php echo htmlspecialchars($row['celular']); ?></td>
                                <td><?php echo htmlspecialchars($row['tipo_solicitud']); ?></td>

                                <td>
                                    <?php
                                    if ($row['tipo_solicitud'] == 'artesano' && isset($row['nomcom'])) {
                                        echo htmlspecialchars($row['nomcom']);
                                    } elseif (isset($row['turno'])) {
                                        echo htmlspecialchars($row['turno']);
                                    } else {
                                        echo "No disponible";
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['fecha_solicitud']); ?></td>
                                <td>
                                    <form action="../scripts/process_admin_solicitud.php" method="POST">

                                        <input type="hidden" name="idsolicitud" value="<?php echo $row['idsolicitud']; ?>">
                                        <input type="hidden" name="idusu" value="<?php echo $row['idusu']; ?>">
                                        <input type="hidden" name="tipo_solicitud"
                                            value="<?php echo $row['tipo_solicitud']; ?>">
                                        <input type="hidden" name="idcom" value="<?php echo $row['idcom']; ?>">
                                        <input type="hidden" name="turno" value="<?php echo $row['turno']; ?>">
                                        <input type="hidden" name="idped" value="<?php echo $row['idped']; ?>">

                                        <!-- Botón para Aprobar -->
                                        <button type="submit" name="accion" value="aprobar" class="btn-aprobar">Aprobar</button>

                                        <!-- Botón para Rechazar -->
                                        <button type="submit" name="accion" value="rechazar"
                                            class="btn-rechazar">Rechazar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay solicitudes pendientes.</p>
            <?php endif; ?>
        </section>


        <!-- Otras secciones aquí... -->
        <section id="marketing" class="admin-marketing">
            <h2>Publicar Contenido de Marketing</h2>
            <form action="../scripts/process_marketing.php" method="POST" enctype="multipart/form-data">
                <label for="tipo">Tipo de Contenido:</label>
                <select name="tipo" id="tipo" required>
                    <option value="promoción">Promoción</option>
                    <option value="anuncio">Anuncio</option>
                    <option value="evento">Evento</option>
                </select>

                <label for="contenido">Contenido:</label>
                <textarea name="contenido" id="contenido" rows="5" required></textarea>

                <label for="imagen">Imagen (opcional):</label>
                <input type="file" name="imagen" id="imagen" accept="image/*">

                <button type="submit" class="btn-publicar">Publicar</button>
            </form>
        </section>



        <section id="comunidad" class="admin-comunidad">
            <h2>Crear Nueva Comunidad</h2>
            <form action="../scripts/process_comunidad.php" method="POST">
                <label for="nomcom">Nombre de la Comunidad:</label>
                <input type="text" name="nomcom" id="nomcom" required>

                <label for="departamento">Departamento:</label>
                <input type="text" name="departamento" id="departamento" required>

                <label for="provincia">Provincia:</label>
                <input type="text" name="provincia" id="provincia" required>

                <button type="submit" class="btn-crear">Crear Comunidad</button>
            </form>
        </section>

        <section id="categoria" class="admin-categoria">
            <h2>Crear Nueva Categoría</h2>
            <form action="../scripts/process_categoria.php" method="POST">
                <label for="nomCat">Nombre de la Categoría:</label>
                <input type="text" name="nomCat" id="nomCat" required>

                <button type="submit" class="btn-crear">Crear Categoría</button>
            </form>
        </section>

        <section id="usuarios" class="admin-usuarios">
            <h2>Gestionar Usuarios</h2>
            <?php
            // Consultar todos los usuarios
            $sql_usuarios = "SELECT idusu, nomusu, ci, email, idver FROM Usuario";
            $result_usuarios = $conn->query($sql_usuarios);
            ?>

            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>CI</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_usuarios->num_rows > 0): ?>
                        <?php while ($row = $result_usuarios->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nomusu']); ?></td>
                                <td><?php echo htmlspecialchars($row['ci']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo $row['idver'] == 1 ? 'Administrador' : ($row['idver'] == 2 ? 'Artesano' : ($row['idver'] == 3 ? 'Comprador' : 'Delivery')); ?>
                                </td>
                                <td>
                                    <a href="../scripts/process_eliminar_usuario.php?idusu=<?php echo $row['idusu']; ?>"
                                        onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No hay usuarios registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section id="entregas" class="admin-entregas">
            <h2>Asignar Entregas</h2>
            <?php
            // Consultar los pedidos no asignados
            $sql_pedidos = "SELECT p.idped, u.nomusu 
                FROM Pedido p
                JOIN Usuario u ON p.idusu = u.idusu
                WHERE p.idped NOT IN (SELECT idped FROM Entrega WHERE idped IS NOT NULL)";
            $result_pedidos = $conn->query($sql_pedidos);

            // Consultar los usuarios que son delivery (idver = 4)
            $sql_delivery = "SELECT idusu, nomusu FROM Usuario WHERE idver = 4";
            $result_delivery = $conn->query($sql_delivery);
            ?>

            <form action="../scripts/process_asignar_entrega.php" method="POST">
                <label for="idped">Pedido:</label>
                <select name="idped" id="idped">
                    <?php while ($pedido = $result_pedidos->fetch_assoc()): ?>
                        <option value="<?php echo $pedido['idped']; ?>">Pedido #<?php echo $pedido['idped']; ?> - Usuario:
                            <?php echo $pedido['nomusu']; ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="idusu_delivery">Repartidor (Delivery):</label>
                <select name="idusu_delivery" id="idusu_delivery">
                    <?php while ($delivery = $result_delivery->fetch_assoc()): ?>
                        <option value="<?php echo $delivery['idusu']; ?>"><?php echo $delivery['nomusu']; ?></option>
                    <?php endwhile; ?>
                </select>

                <button type="submit" class="btn-asignar">Asignar Entrega</button>
            </form>
        </section>

        <section id="inventario" class="admin-inventario">
            <h2>Ver Inventario</h2>
            <?php
            // Consultar inventario
            $sql_inventario = "SELECT p.nomprod, p.precio, i.cantprod, i.fechactua 
                       FROM Producto p
                       JOIN Inventario i ON p.idinv = i.idinv";
            $result_inventario = $conn->query($sql_inventario);
            ?>

            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Última Actualización</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_inventario->num_rows > 0): ?>
                        <?php while ($row = $result_inventario->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nomprod']); ?></td>
                                <td><?php echo htmlspecialchars($row['precio']); ?> Bs</td>
                                <td><?php echo htmlspecialchars($row['cantprod']); ?></td>
                                <td><?php echo htmlspecialchars($row['fechactua']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No hay productos en el inventario.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section id="stock" class="admin-actualizar-stock">
            <h2>Actualizar Stock</h2>
            <?php
            // Consultar productos
            $sql_productos = "SELECT p.idprod, p.nomprod, i.cantprod 
                      FROM Producto p
                      JOIN Inventario i ON p.idinv = i.idinv";
            $result_productos = $conn->query($sql_productos);
            ?>

            <form action="../scripts/process_actualizar_stock.php" method="POST">
                <label for="idprod">Producto:</label>
                <select name="idprod" id="idprod">
                    <?php while ($producto = $result_productos->fetch_assoc()): ?>
                        <option value="<?php echo $producto['idprod']; ?>"><?php echo $producto['nomprod']; ?> - Stock
                            actual: <?php echo $producto['cantprod']; ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="nuevo_stock">Nuevo Stock:</label>
                <input type="number" name="nuevo_stock" id="nuevo_stock" required>

                <button type="submit" class="btn-actualizar-stock">Actualizar Stock</button>
            </form>
        </section>

        <section id="reporte" class="admin-reporte-inventario">
            <h2>Generar Reporte de Inventario</h2>
            <form action="../scripts/generar_reporte_inventario.php" method="POST">
                <button type="submit" name="formato" value="csv" class="btn-reporte">Generar Reporte CSV</button>
                <button type="submit" name="formato" value="pdf" class="btn-reporte">Generar Reporte PDF</button>
            </form>
        </section>
        <?php
        // Sección en el panel de administración donde se gestionan las solicitudes de pago

        $sql_solicitudes_pago = "SELECT Pago.*, Usuario.nomusu, Pedido.idped 
                         FROM Pago
                         JOIN Pedido ON Pago.idped = Pedido.idped
                         JOIN Usuario ON Pedido.idusu = Usuario.idusu
                         WHERE Pago.estado_deposito = 'pendiente'";

        $result_solicitudes_pago = $conn->query($sql_solicitudes_pago);
        ?>

        <section id="pagos" class="admin-pagos">
            <h2>Solicitudes de Pagos Pendientes</h2>
            <?php if ($result_solicitudes_pago->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nombre del Usuario</th>
                            <th>ID del Pedido</th>
                            <th>Método de Pago</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_solicitudes_pago->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nomusu']); ?></td>
                                <td><?php echo htmlspecialchars($row['idped']); ?></td>
                                <td><?php echo htmlspecialchars($row['método']); ?></td>
                                <td>
                                    x <form action="../scripts/process_admin_pago.php" method="POST">
                                        <input type="hidden" name="idped" value="<?php echo $row['idpedido']; ?>">
                                        <button type="submit" name="accion" value="aprobar" class="btn-aprobar">Aprobar
                                            Pago</button>
                                        <button type="submit" name="accion" value="rechazar" class="btn-rechazar">Rechazar
                                            Pago</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay pagos pendientes.</p>
            <?php endif; ?>
        </section>

        <!-- Sección de Servicio al Cliente -->
        <section id="servicio_cliente" class="admin-servicio-cliente">
            <?php
            if (isset($_GET['success']) && $_GET['success'] == 'respondido') {
                echo "<p class='success-msg'>Respuesta enviada con éxito.</p>";
            } elseif (isset($_GET['error']) && $_GET['error'] == 'error_db') {
                echo "<p class='error-msg'>Hubo un error al enviar la respuesta. Inténtalo de nuevo.</p>";
            } elseif (isset($_GET['error']) && $_GET['error'] == 'empty_response') {
                echo "<p class='error-msg'>La respuesta no puede estar vacía.</p>";
            }
            ?>
            <h2>Tickets de Servicio al Cliente</h2>

            <?php
            // Obtener los tickets de soporte pendientes
            $sql_tickets = "SELECT TicketSoporte.*, Usuario.nomusu 
                    FROM TicketSoporte 
                    JOIN Usuario ON TicketSoporte.idusu = Usuario.idusu 
                    ORDER BY fecha_envio DESC";
            $result_tickets = $conn->query($sql_tickets);
            ?>

            <?php if ($result_tickets->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Asunto</th>
                            <th>Mensaje</th>
                            <th>Fecha de Envío</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ticket = $result_tickets->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ticket['nomusu']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['asunto']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['mensaje']); ?></td>
                                <td><?php echo $ticket['fecha_envio']; ?></td>
                                <td><?php echo $ticket['estado']; ?></td>
                                <td>
                                    <form action="../scripts/process_responder_ticket.php" method="POST">
                                        <input type="hidden" name="idticket" value="<?php echo $ticket['idticket']; ?>">
                                        <textarea name="respuesta" placeholder="Escribe tu respuesta..." required></textarea>
                                        <button type="submit" class="btn-aprobar">Responder</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No hay tickets pendientes.</p>
            <?php endif; ?>
        </section>


    </div>
    <script>
        // Función para mostrar la sección seleccionada y ocultar las demás
        function showSection(sectionId) {
            // Ocultar todas las secciones
            const sections = document.querySelectorAll('section');
            sections.forEach(section => {
                section.style.display = 'none';
            });

            // Mostrar solo la sección seleccionada
            document.getElementById(sectionId).style.display = 'block';
        }

        // Mostrar la primera sección al cargar la página
        window.onload = function() {
            showSection('solicitudes'); // Muestra la primera sección por defecto
        };
    </script>

</body>

</html>

<?php
$conn->close();
?>