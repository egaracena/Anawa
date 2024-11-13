<?php
session_start();

$idprod = $_POST['idprod'];

// Eliminar el producto del carrito
if (isset($_SESSION['cart'][$idprod])) {
    unset($_SESSION['cart'][$idprod]);
}

header("Location: ../pages/cart.php");
?>
