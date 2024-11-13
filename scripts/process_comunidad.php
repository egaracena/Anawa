<?php
session_start();
include('../config/db.php');

// Verificar si es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 1) {
    echo "Acceso denegado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomcom = $_POST['nomcom'];
    $departamento = $_POST['departamento'];
    $provincia = $_POST['provincia'];

    // Insertar la nueva comunidad en la tabla Comunidad
    $sql = "INSERT INTO Comunidad (nomcom, departamento, provincia) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nomcom, $departamento, $provincia);

    if ($stmt->execute()) {
        header("Location: ../pages/admin_dashboard.php?status=comunidad_success");
    } else {
        echo "Error al crear la comunidad: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
