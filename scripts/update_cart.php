<?php
session_start();
include('../config/db.php');

$idprod = $_POST['idprod'];
$cantidad = intval($_POST['cantidad']);

// Validar que la cantidad sea un número entero positivo
if ($cantidad <= 0) {
    // Eliminar el producto del carrito
    unset($_SESSION['cart'][$idprod]);
    // Redirigir al carrito
    header('Location: ../pages/cart.php');
    exit();
}

// Obtener la cantidad disponible del producto desde la base de datos
$sql = "SELECT i.cantprod FROM Producto p JOIN Inventario i ON p.idinv = i.idinv WHERE p.idprod = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idprod);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    $availableStock = $row['cantprod'];

    // Verificar si la cantidad solicitada excede el stock disponible
    if ($cantidad > $availableStock) {
        // No permitir la actualización y mostrar un mensaje
        $_SESSION['error_message'] = "No hay suficiente stock disponible para el producto seleccionado.";
    } else {
        // Actualizar la cantidad en el carrito
        $_SESSION['cart'][$idprod]['cantidad'] = $cantidad;
    }
}

$stmt->close();
$conn->close();

// Redirigir al carrito
header('Location: ../pages/cart.php');
exit();
?>
