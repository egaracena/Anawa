<?php
session_start();
include('../config/db.php');

// Verificar si hay una sesión activa y si el usuario es un artesano (idver = 2)
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 2) {
    echo "Acceso denegado.";
    exit;
}

// Obtener información del perfil del artesano
$user_id = $_SESSION['user_id'];
$sql = "SELECT Usuario.nomusu, Usuario.ci, Usuario.celular, Usuario.email, Comunidad.nomcom 
        FROM Usuario 
        JOIN Artesano ON Usuario.idusu = Artesano.idusu
        JOIN Comunidad ON Artesano.idcom = Comunidad.idcom
        WHERE Usuario.idusu = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result_perfil = $stmt->get_result();
$perfil = $result_perfil->fetch_assoc();

// Obtener estado del inventario del artesano
$sql_inventario = "SELECT Producto.nomprod, Inventario.cantprod 
                   FROM Producto 
                   JOIN Inventario ON Producto.idinv = Inventario.idinv 
                   WHERE Producto.idusu = ?";
$stmt_inventario = $conn->prepare($sql_inventario);
$stmt_inventario->bind_param("i", $user_id);
$stmt_inventario->execute();
$result_inventario = $stmt_inventario->get_result();

// Consultas para obtener ventas e ingresos

// Ventas Diarias
$sqlVentasDiarias = "SELECT COUNT(DISTINCT Pedido.idped) AS total_diarias FROM Pedido 
                     JOIN Pago ON Pedido.idped = Pago.idped 
                     JOIN DetallePedido ON Pedido.idped = DetallePedido.idped
                     JOIN Producto ON DetallePedido.idprod = Producto.idprod
                     WHERE Pago.estado_deposito = 'confirmado' 
                     AND Producto.idusu = ? 
                     AND DATE(Pago.fechapag) = CURDATE()";
$stmtVentasDiarias = $conn->prepare($sqlVentasDiarias);
$stmtVentasDiarias->bind_param("i", $user_id);
$stmtVentasDiarias->execute();
$ventasDiarias = $stmtVentasDiarias->get_result()->fetch_assoc()['total_diarias'];

// Ventas Semanales
$sqlVentasSemanales = "SELECT COUNT(DISTINCT Pedido.idped) AS total_semanales FROM Pedido 
                       JOIN Pago ON Pedido.idped = Pago.idped 
                       JOIN DetallePedido ON Pedido.idped = DetallePedido.idped
                       JOIN Producto ON DetallePedido.idprod = Producto.idprod
                       WHERE Pago.estado_deposito = 'confirmado' 
                       AND Producto.idusu = ? 
                       AND YEARWEEK(Pago.fechapag, 1) = YEARWEEK(CURDATE(), 1)";
$stmtVentasSemanales = $conn->prepare($sqlVentasSemanales);
$stmtVentasSemanales->bind_param("i", $user_id);
$stmtVentasSemanales->execute();
$ventasSemanales = $stmtVentasSemanales->get_result()->fetch_assoc()['total_semanales'];

// Ventas Mensuales
$sqlVentasMensuales = "SELECT COUNT(DISTINCT Pedido.idped) AS total_mensuales FROM Pedido 
                       JOIN Pago ON Pedido.idped = Pago.idped 
                       JOIN DetallePedido ON Pedido.idped = DetallePedido.idped
                       JOIN Producto ON DetallePedido.idprod = Producto.idprod
                       WHERE Pago.estado_deposito = 'confirmado' 
                       AND Producto.idusu = ? 
                       AND MONTH(Pago.fechapag) = MONTH(CURDATE()) 
                       AND YEAR(Pago.fechapag) = YEAR(CURDATE())";
$stmtVentasMensuales = $conn->prepare($sqlVentasMensuales);
$stmtVentasMensuales->bind_param("i", $user_id);
$stmtVentasMensuales->execute();
$ventasMensuales = $stmtVentasMensuales->get_result()->fetch_assoc()['total_mensuales'];

// Ingresos Totales
$sqlIngresosTotales = "SELECT SUM(DetallePedido.cantidad * DetallePedido.precioUni) AS ingresos_totales 
                       FROM Pedido 
                       JOIN Pago ON Pedido.idped = Pago.idped 
                       JOIN DetallePedido ON Pedido.idped = DetallePedido.idped 
                       JOIN Producto ON DetallePedido.idprod = Producto.idprod
                       WHERE Pago.estado_deposito = 'confirmado' 
                       AND Producto.idusu = ?";
$stmtIngresosTotales = $conn->prepare($sqlIngresosTotales);
$stmtIngresosTotales->bind_param("i", $user_id);
$stmtIngresosTotales->execute();
$ingresosTotales = $stmtIngresosTotales->get_result()->fetch_assoc()['ingresos_totales'] ?? 0.00;

// Productos Más Vendidos
$sqlProductosMasVendidos = "SELECT Producto.nomprod, SUM(DetallePedido.cantidad) AS total_vendidos 
                            FROM DetallePedido 
                            JOIN Producto ON DetallePedido.idprod = Producto.idprod 
                            JOIN Pedido ON DetallePedido.idped = Pedido.idped 
                            JOIN Pago ON Pedido.idped = Pago.idped 
                            WHERE Pago.estado_deposito = 'confirmado' 
                            AND Producto.idusu = ? 
                            GROUP BY Producto.nomprod 
                            ORDER BY total_vendidos DESC 
                            LIMIT 5";
$stmtProductosMasVendidos = $conn->prepare($sqlProductosMasVendidos);
$stmtProductosMasVendidos->bind_param("i", $user_id);
$stmtProductosMasVendidos->execute();
$productosMasVendidos = $stmtProductosMasVendidos->get_result();



// Si ingresosGenerados es null, asignamos un valor predeterminado de 0
$ingresosGenerados = $ingresosGenerados ?? 0;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Artesano</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Anawa/assets/css/mi_perfil.css">
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="#"><img src="/Anawa/assets/images/LogoAnawa2.png" alt="Logo"></a>
            </div>
            <ul class="nav-links">
                <li><a href="artesano_dashboard.php">Inicio</a></li>
                <li><a href="mi_perfil.php">Mi Perfil</a></li>
                <li><a href="#">Mis Productos</a></li>
                <li><a href="#">Subir Producto</a></li>
            </ul>
            <div>
                <a href="/Anawa/pages/logout.php" class="logout-btn">Cerrar sesión</a>
            </div>
        </nav>
    </header>

    <section class="profile-section">
        <h2>Mi Perfil</h2>
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($perfil['nomusu']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($perfil['email']); ?></p>
        <p><strong>Comunidad:</strong> <?php echo htmlspecialchars($perfil['nomcom']); ?></p>
    </section>

    <section class="inventory-section">
        <h2>Estado del Inventario</h2>
        <h3>Productos en stock:</h3>
        <ul>
            <?php
            $result_inventario->data_seek(0); // Reiniciar el cursor de resultados
            while ($producto = $result_inventario->fetch_assoc()) {
                if ($producto['cantprod'] > 0) {
                    echo "<li>{$producto['nomprod']} - Cantidad en stock: {$producto['cantprod']}</li>";
                }
            }
            ?>

            <h3>Productos agotados:</h3>
            <?php
            $result_inventario->data_seek(0);
            while ($producto = $result_inventario->fetch_assoc()) {
                if ($producto['cantprod'] == 0) {
                    echo "<li>{$producto['nomprod']} - AGOTADO</li>";
                }
            }
            ?>

            <h3>Alertas de bajo inventario:</h3>
            <?php
            $result_inventario->data_seek(0);
            while ($producto = $result_inventario->fetch_assoc()) {
                if ($producto['cantprod'] > 0 && $producto['cantprod'] <= 5) {
                    echo "<li>{$producto['nomprod']} - Solo {$producto['cantprod']} unidades en stock</li>";
                }
            }
            ?>
        </ul>
    </section>

    <section class="ventas-ingresos">
        <h3>Resumen de Ventas e Ingresos</h3>

        <div class="ventas">
            <h4>Ventas Totales</h4>
            <p><strong>Diarias:</strong> <?php echo $ventasDiarias; ?></p>
            <p><strong>Semanales:</strong> <?php echo $ventasSemanales; ?></p>
            <p><strong>Mensuales:</strong> <?php echo $ventasMensuales; ?></p>
        </div>

        <div class="ingresos">
            <h4>Ingresos Generados</h4>
            <p><strong>Total:</strong> <?php echo number_format($ingresosTotales, 2); ?> Bs</p>
        </div>

        <div class="productos-mas-vendidos">
            <h4>Productos Más Vendidos</h4>
            <ul>
                <?php while ($producto = $productosMasVendidos->fetch_assoc()): ?>
                    <li><?php echo $producto['nomprod']; ?> - Vendidos: <?php echo $producto['total_vendidos']; ?></li>
                <?php endwhile; ?>
            </ul>
        </div>
    </section>
</body>

</html>
<?php
$conn->close();
?>