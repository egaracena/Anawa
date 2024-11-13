<?php
session_start();
include('../config/db.php');

// Verificar si el usuario tiene sesión activa y si es un artesano
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 2) {
    echo "Acceso denegado.";
    exit;
}

// Procesar la edición del producto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idprod = $_POST['idprod'];
    $nomprod = $_POST['nomprod'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $dimensiones = $_POST['dimensiones'];
    $stock_inicial = $_POST['stock_inicial'];

    // Actualizar las imágenes solo si se han subido nuevas
    $target_dir = "/Anawa/uploads/product_images/";
    $imagen1 = !empty($_FILES["imagen1"]["tmp_name"]) ? $target_dir . basename($_FILES["imagen1"]["name"]) : null;
    $imagen2 = !empty($_FILES["imagen2"]["tmp_name"]) ? $target_dir . basename($_FILES["imagen2"]["name"]) : null;
    $imagen3 = !empty($_FILES["imagen3"]["tmp_name"]) ? $target_dir . basename($_FILES["imagen3"]["name"]) : null;

    // Mover las imágenes a la carpeta de destino en el servidor si existen
    if ($imagen1) {
        move_uploaded_file($_FILES["imagen1"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . $imagen1);
    }
    if ($imagen2) {
        move_uploaded_file($_FILES["imagen2"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . $imagen2);
    }
    if ($imagen3) {
        move_uploaded_file($_FILES["imagen3"]["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . $imagen3);
    }

    // Actualizar el stock en la tabla Inventario
    $sql_inventario = "UPDATE Inventario SET cantprod = ? WHERE idinv = (SELECT idinv FROM Producto WHERE idprod = ?)";
    $stmt_inventario = $conn->prepare($sql_inventario);
    $stmt_inventario->bind_param("ii", $stock_inicial, $idprod);
    $stmt_inventario->execute();

    // Actualizar el producto en la tabla Producto
    $sql_producto = "UPDATE Producto SET nomprod = ?, descripción = ?, precio = ?, dimensiones = ?";

    if ($imagen1) {
        $sql_producto .= ", imagen1 = ?";
    }
    if ($imagen2) {
        $sql_producto .= ", imagen2 = ?";
    }
    if ($imagen3) {
        $sql_producto .= ", imagen3 = ?";
    }

    $sql_producto .= " WHERE idprod = ?";

    $stmt_producto = $conn->prepare($sql_producto);

    if ($imagen1 && $imagen2 && $imagen3) {
        $stmt_producto->bind_param("ssdsssss", $nomprod, $descripcion, $precio, $dimensiones, $imagen1, $imagen2, $imagen3, $idprod);
    } elseif ($imagen1 && $imagen2) {
        $stmt_producto->bind_param("ssdsssss", $nomprod, $descripcion, $precio, $dimensiones, $imagen1, $imagen2, $idprod);
    } elseif ($imagen1) {
        $stmt_producto->bind_param("ssdssss", $nomprod, $descripcion, $precio, $dimensiones, $imagen1, $idprod);
    } else {
        $stmt_producto->bind_param("ssdss", $nomprod, $descripcion, $precio, $dimensiones, $idprod);
    }

    if ($stmt_producto->execute()) {
        header("Location: ../pages/artesano_dashboard.php?status=success");
    } else {
        header("Location: ../pages/artesano_dashboard.php?status=error");
    }

    $stmt_producto->close();
    $conn->close();
}
