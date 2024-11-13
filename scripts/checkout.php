<?php
session_start();
include('../config/db.php');

// Verificar si el carrito está vacío
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "El carrito está vacío.";
    exit();
}

$idusu = $_SESSION['user_id'];
$cantidad_total = 0;

// Obtener la latitud y longitud del formulario
$lat = $_POST['lat'];
$lng = $_POST['lng'];

// Guardar el pedido
$sql_pedido = "INSERT INTO Pedido (idusu, cantprod, latitud, longitud) VALUES ('$idusu', '0', '$lat', '$lng')";
if ($conn->query($sql_pedido) === TRUE) {
    $id_pedido = $conn->insert_id; // Obtener el ID del pedido recién creado
    
    // Guardar los detalles del pedido
    foreach ($_SESSION['cart'] as $producto_id => $producto) {
        $cantidad = $producto['cantidad'];
        $precio = $producto['precio'];
        $sql_detalle = "INSERT INTO DetallePedido (idped, idprod, cantidad, precioUni) VALUES ('$id_pedido', '$producto_id', '$cantidad', '$precio')";
        $conn->query($sql_detalle);
        $cantidad_total += $cantidad;
    }

    // Actualizar la cantidad total de productos en el pedido
    $sql_update_pedido = "UPDATE Pedido SET cantprod = '$cantidad_total' WHERE idped = '$id_pedido'";
    $conn->query($sql_update_pedido);

    // Vaciar el carrito
    unset($_SESSION['cart']);

    // Redirigir al comprador a la página de confirmación
    header("Location: ../pages/confirmacion_pedido.php?idped=$id_pedido"); // Corregido: pasar idped en la URL
    exit();

} else {
    echo "Error al procesar el pedido: " . $conn->error;
}

$conn->close();
?>
