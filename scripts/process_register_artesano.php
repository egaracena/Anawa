<?php
session_start();
include('../config/db.php');

// Verificar si hay una sesión activa
if (!isset($_SESSION['user_id'])) {
    echo "Debes iniciar sesión para acceder a esta página.";
    exit;
}

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idusu = $_SESSION['user_id'];
    $idcom = $_POST['idcom'];

    // Iniciar la transacción
    $conn->begin_transaction();

    try {
        // Insertar el registro en la tabla Artesano
        $sql = "INSERT INTO Artesano (idusu, idcom) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $idusu, $idcom);

        if ($stmt->execute()) {
            // Actualizar el campo idver en la tabla Usuario a 2 (Artesano)
            $sql_update = "UPDATE Usuario SET idver = 2 WHERE idusu = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $idusu);
            $stmt_update->execute();

            // Si todo está bien, hacemos commit
            $conn->commit();

            echo "Te has registrado exitosamente como artesano.";
            // Redirigir a una página de éxito o dashboard
            header("Location: ../pages/artesano_dashboard.php");
            exit;
        } else {
            throw new Exception("Error al registrar como artesano: " . $conn->error);
        }

        $stmt->close();
        $stmt_update->close();
    } catch (Exception $e) {
        // Si algo falla, deshacemos la transacción
        $conn->rollback();
        echo $e->getMessage();
    }
}

$conn->close();
?>
