<?php
session_start();
include('../config/db.php');

$idprod = $_POST['idprod'];
$cantidad = $_POST['cantidad'];

// Asegurarse de que la cantidad sea un número entero positivo
$cantidad = intval($cantidad);

if ($cantidad <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Cantidad inválida']);
    exit();
}

// Consultar los detalles del producto y la cantidad disponible en inventario
$sql = "SELECT p.idprod, p.nomprod, p.precio, p.imagen1, i.cantprod 
        FROM Producto p 
        JOIN Inventario i ON p.idinv = i.idinv 
        WHERE p.idprod = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $idprod);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    // Si no se encuentra el producto, devolver un error
    echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado']);
    exit();
}

$availableStock = $product['cantprod'];

// Obtener la cantidad existente en el carrito para este producto
$existingQuantityInCart = isset($_SESSION['cart'][$idprod]) ? $_SESSION['cart'][$idprod]['cantidad'] : 0;

// Calcular la cantidad total solicitada
$totalRequestedQuantity = $existingQuantityInCart + $cantidad;

// Validar que la cantidad solicitada no exceda el stock disponible
if ($totalRequestedQuantity > $availableStock) {
    echo json_encode(['status' => 'error', 'message' => 'No hay suficiente stock disponible.']);
    exit();
}

// Si el producto ya está en el carrito, actualizar la cantidad
if (isset($_SESSION['cart'][$idprod])) {
    $_SESSION['cart'][$idprod]['cantidad'] += $cantidad;
} else {
    // Agregar un nuevo producto al carrito
    $_SESSION['cart'][$idprod] = [
        'idprod' => $product['idprod'],
        'nomprod' => $product['nomprod'],
        'precio' => $product['precio'],
        'cantidad' => $cantidad,
        'imagen' => $product['imagen1']
    ];
}

// Calcular el total de productos en el carrito
$cart_count = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['cantidad'];
}

// Devolver la respuesta JSON con el estado y el número de productos en el carrito
echo json_encode(['status' => 'success', 'cart_count' => $cart_count]);
exit();
?>
