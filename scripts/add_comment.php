<?php
session_start();
include('../config/db.php');

// Verificar si los datos necesarios están presentes
if (isset($_POST['idprod'], $_POST['comentario'], $_POST['calificación']) && isset($_SESSION['user_id'])) {
    $idprod = $_POST['idprod'];
    $comentario = $_POST['comentario'];
    $calificación = $_POST['calificación'];
    $idusu = $_SESSION['user_id']; // Usar el ID del usuario desde la sesión
    $fechaCali = date('Y-m-d');

    // Consulta SQL para insertar el comentario
    $sql = "INSERT INTO Califica (idprod, idusu, comentario, calificación, fechaCali) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        echo json_encode(["status" => "error", "message" => "Error en la preparación de la consulta: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("iisis", $idprod, $idusu, $comentario, $calificación, $fechaCali);

    if ($stmt->execute()) {
        // Obtener el nombre de usuario para mostrarlo junto al comentario
        $sql_usuario = "SELECT nomusu FROM Usuario WHERE idusu = ?";
        $stmt_usuario = $conn->prepare($sql_usuario);
        $stmt_usuario->bind_param("i", $idusu);
        $stmt_usuario->execute();
        $result_usuario = $stmt_usuario->get_result();
        $usuario = $result_usuario->fetch_assoc();

        echo json_encode([
            "status" => "success",
            "username" => $usuario['nomusu'],
            "comentario" => $comentario,
            "calificación" => $calificación,
            "fechaCali" => $fechaCali
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error en la ejecución de la consulta: " . $stmt->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Datos no válidos o sesión no iniciada."]);
}
?>
