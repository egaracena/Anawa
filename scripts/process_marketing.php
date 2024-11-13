<?php
session_start();
include('../config/db.php');

// Verificar si es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 1) {
    echo "Acceso denegado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = $_POST['tipo'];
    $contenido = $_POST['contenido'];
    $idusu = $_SESSION['user_id']; // El administrador que publica el contenido
    $imagen_ruta = null;

    // Procesar la subida de imagen (si hay una)
    if (!empty($_FILES['imagen']['name'])) {
        $target_dir = "/Anawa/uploads/marketing_images/";
        $imagen_ruta = $target_dir . basename($_FILES['imagen']['name']);
        move_uploaded_file($_FILES['imagen']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $imagen_ruta);
    }

    // Insertar el contenido en la tabla Marketing (incluyendo la ruta de la imagen si se subió)
    $sql = "INSERT INTO Marketing (tipo, contenido, imagen, idusu) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $tipo, $contenido, $imagen_ruta, $idusu);

    if ($stmt->execute()) {
        header("Location: ../pages/admin_dashboard.php?status=marketing_success");
    } else {
        echo "Error al publicar el contenido: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
