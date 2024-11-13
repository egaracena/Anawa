<?php
session_start();
include('../config/db.php');

// Verificar si hay una sesión activa y si el usuario es un artesano (idver = 2)
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 2) {
    echo "Acceso denegado.";
    exit;
}

// Procesar el formulario de subir producto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomprod = $_POST['nomprod'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $dimensiones = $_POST['dimensiones'];
    $idcat = $_POST['idcat'];
    $idusu = $_SESSION['user_id'];
    $stock_inicial = $_POST['stock_inicial'];

    // Definir el directorio donde se guardarán las imágenes
    $target_dir = "/Anawa/uploads/product_images/";
    
    // Obtener las rutas de las imágenes y moverlas al directorio
    $imagen1 = $target_dir . basename($_FILES["imagen1"]["name"]);
    $imagen2 = !empty($_FILES["imagen2"]["tmp_name"]) ? $target_dir . basename($_FILES["imagen2"]["name"]) : null;
    $imagen3 = !empty($_FILES["imagen3"]["tmp_name"]) ? $target_dir . basename($_FILES["imagen3"]["name"]) : null;

    // Mover las imágenes a la carpeta de destino en el servidor
    move_uploaded_file($_FILES["imagen1"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . $imagen1);
    if ($imagen2) {
        move_uploaded_file($_FILES["imagen2"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . $imagen2);
    }
    if ($imagen3) {
        move_uploaded_file($_FILES["imagen3"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . $imagen3);
    }

    // Insertar en la tabla Inventario para obtener el idinv
    $sql_inventario = "INSERT INTO Inventario (cantprod, fechactua) VALUES (?, NOW())";
    $stmt_inventario = $conn->prepare($sql_inventario);
    $stmt_inventario->bind_param("i", $stock_inicial);
    $stmt_inventario->execute();
    $idinv = $stmt_inventario->insert_id;
    $stmt_inventario->close();

    // Insertar el producto en la base de datos junto con las rutas de las imágenes y el idinv
    $sql_producto = "INSERT INTO Producto (nomprod, descripción, precio, dimensiones, idusu, idcat, idinv, imagen1, imagen2, imagen3) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_producto = $conn->prepare($sql_producto);
    $stmt_producto->bind_param("ssdsiissss", $nomprod, $descripcion, $precio, $dimensiones, $idusu, $idcat, $idinv, $imagen1, $imagen2, $imagen3);

    if ($stmt_producto->execute()) {
        header("Location: ../pages/artesano_dashboard.php?status=success");
    } else {
        header("Location: ../pages/artesano_dashboard.php?status=error");
    }

    $stmt_producto->close();
    $conn->close();
}
?>
