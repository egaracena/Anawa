<?php
session_start();
include('../config/db.php');

// Verificar si hay una sesión activa y si el usuario es un artesano (idver = 2)
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 2) {
    echo "Acceso denegado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Artesano</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Anawa/assets/css/artesano_dashboard.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="#"><img src="/Anawa/assets/images/LogoAnawa2.png" alt="Logo"></a>
            </div>
            <ul class="nav-links">
                <li><a href="#">Inicio</a></li>
                <li><a href="mi_perfil.php">Mi Perfil</a></li>
                <li><a href="#">Mis Productos</a></li>
                <li><a href="#">Subir Producto</a></li>                
            </ul>
            <div>
                <a href="/Anawa/pages/logout.php" class="logout-btn">Cerrar sesión</a>
            </div>
            
        </nav>
    </header>

    <section class="dashboard">
        <h2>Bienvenido, <?php echo $_SESSION['username']; ?>. Este es tu panel de artesano.</h2>

        <div class="product-actions">
            <!-- Formulario para subir un producto -->
            <h3>Subir Nuevo Producto</h3>
            <form action="../scripts/process_subir_producto.php" method="POST" enctype="multipart/form-data">
                <label for="nomprod">Nombre del Producto:</label>
                <input type="text" name="nomprod" id="nomprod" required>

                <label for="descripcion">Descripción:</label>
                <textarea name="descripcion" id="descripcion" required></textarea>

                <label for="precio">Precio:</label>
                <input type="number" name="precio" id="precio" required>

                <label for="dimensiones">Dimensiones (Ej.: Largo: 30 cm, Ancho: 20 cm):</label>
                <input type="text" name="dimensiones" id="dimensiones" required>

                <label for="categoria">Selecciona la Categoría:</label>
                <select name="idcat" id="categoria" required>
                    <?php
                    $sql = "SELECT idcat, nomCat FROM Categoria";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['idcat']}'>{$row['nomCat']}</option>";
                        }
                    }
                    ?>
                </select>

                <label for="stock_inicial">Stock Inicial:</label>
                <input type="number" name="stock_inicial" id="stock_inicial" required>

                <!-- Imágenes del producto -->
                <label for="imagen1">Imagen Principal:</label>
                <input type="file" name="imagen1" id="imagen1" required>

                <label for="imagen2">Imagen Secundaria:</label>
                <input type="file" name="imagen2" id="imagen2">

                <label for="imagen3">Tercera Imagen:</label>
                <input type="file" name="imagen3" id="imagen3">

                <button type="submit" class="form-btn">Subir Producto</button>
            </form>
        </div>

        <div class="product-list">
            <h3>Mis Productos</h3>
            <!-- Aquí se listarán los productos del artesano -->
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Consultar los productos del artesano
                    $idusu = $_SESSION['user_id'];
                    $sql = "SELECT * FROM Producto WHERE idusu = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $idusu);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['nomprod']}</td>
                                <td>{$row['descripción']}</td>
                                <td>{$row['precio']}</td>
                                <td>
                                    <a href='edit_producto.php?idprod={$row['idprod']}'>Editar</a> |
                                    <a href='delete_producto.php?idprod={$row['idprod']}' onclick=\"return confirm('¿Estás seguro de eliminar este producto?');\">Eliminar</a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No tienes productos registrados.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>
</body>
</html>
