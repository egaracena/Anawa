<?php
session_start();
include('../config/db.php');

// Verificar si el usuario tiene sesión activa y si es un artesano
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 2) {
    echo "Acceso denegado.";
    exit;
}

// Obtener el id del producto
$idprod = $_GET['idprod'];

// Consultar los detalles del producto
$sql = "SELECT * FROM Producto WHERE idprod = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idprod);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $producto = $result->fetch_assoc();
} else {
    echo "Producto no encontrado.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
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
                <li><a href="#">Mi Perfil</a></li>
                <li><a href="#">Mis Productos</a></li>
                <li><a href="#">Subir Producto</a></li>
            </ul>
            <div>
                <a href="/Anawa/pages/logout.php" class="logout-btn">Cerrar sesión</a>
            </div>
        </nav>
    </header>

    <section class="dashboard">
        <h2>Editar Producto: <?php echo htmlspecialchars($producto['nomprod']); ?></h2>

        <div class="product-actions">
            <form action="../scripts/process_editar_producto.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="idprod" value="<?php echo $idprod; ?>">

                <label for="nomprod">Nombre del Producto:</label>
                <input type="text" name="nomprod" id="nomprod" value="<?php echo htmlspecialchars($producto['nomprod']); ?>" required>

                <label for="descripcion">Descripción:</label>
                <textarea name="descripcion" id="descripcion" required><?php echo htmlspecialchars($producto['descripción']); ?></textarea>

                <label for="precio">Precio:</label>
                <input type="number" name="precio" id="precio" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>

                <label for="categoria">Categoría:</label>
                <select name="idcat" id="categoria" required>
                    <?php
                    $sql = "SELECT idcat, nomCat FROM Categoria";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($row['idcat'] == $producto['idcat']) ? 'selected' : '';
                            echo "<option value='{$row['idcat']}' $selected>{$row['nomCat']}</option>";
                        }
                    }
                    ?>
                </select>

                <label for="dimensiones">Dimensiones:</label>
                <input type="text" name="dimensiones" id="dimensiones" value="<?php echo htmlspecialchars($producto['dimensiones']); ?>" required>

                <!-- Stock -->
                <?php
                $sql_inv = "SELECT * FROM Inventario WHERE idinv = ?";
                $stmt_inv = $conn->prepare($sql_inv);
                $stmt_inv->bind_param("i", $producto['idinv']);
                $stmt_inv->execute();
                $result_inv = $stmt_inv->get_result();
                $inventario = $result_inv->fetch_assoc();
                ?>

                <label for="stock_inicial">Stock Inicial:</label>
                <input type="number" name="stock_inicial" id="stock_inicial" value="<?php echo htmlspecialchars($inventario['cantprod']); ?>" required>



                <!-- Imágenes del producto -->
                <label for="imagen1">Imagen Principal (Actual: <?php echo basename($producto['imagen1']); ?>):</label>
                <input type="file" name="imagen1" id="imagen1">

                <label for="imagen2">Imagen Secundaria (Actual:
                    <?php echo !empty($row['imagen2']) ? basename($row['imagen2']) : 'No disponible'; ?>):
                </label>
                <input type="file" name="imagen2" id="imagen2">

                <label for="imagen3">Tercera Imagen (Actual:
                    <?php echo !empty($row['imagen3']) ? basename($row['imagen3']) : 'No disponible'; ?>):
                </label>
                <input type="file" name="imagen3" id="imagen3">

                <button type="submit" class="form-btn">Actualizar Producto</button>
            </form>
        </div>
    </section>
</body>

</html>

<?php
$conn->close();
?>