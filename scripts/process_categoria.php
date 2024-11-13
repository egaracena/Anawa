<?php
session_start();
include('../config/db.php');

// Verificar si es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 1) {
    echo "Acceso denegado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomCat = $_POST['nomCat'];

    // Insertar la nueva categoría en la tabla Categoria
    $sql = "INSERT INTO Categoria (nomCat) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nomCat);

    if ($stmt->execute()) {
        header("Location: ../pages/admin_dashboard.php?status=categoria_success");
    } else {
        echo "Error al crear la categoría: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
