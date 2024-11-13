<?php
session_start();
include('../config/db.php');

// Verificar si es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 1) {
    echo "Acceso denegado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idprod = $_POST['idprod'];
    $nuevo_stock = $_POST['nuevo_stock'];

    // Actualizar el stock del producto
    $sql = "UPDATE Inventario i
            JOIN Producto p ON i.idinv = p.idinv
            SET i.cantprod = ?, i.fechactua = NOW()
            WHERE p.idprod = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $nuevo_stock, $idprod);

    if ($stmt->execute()) {
        header("Location: ../pages/admin_dashboard.php?status=stock_actualizado");
    } else {
        echo "Error al actualizar el stock: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
