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

    // Obtener los turnos seleccionados
    if (isset($_POST['turno'])) {
        $turnos = $_POST['turno'];
        $turno_seleccionado = implode(', ', $turnos);  // Combinar los turnos seleccionados en una cadena separada por comas

        // Iniciar la transacción
        $conn->begin_transaction();

        try {
            // Insertar el registro en la tabla Delivery
            $sql = "INSERT INTO Delivery (idusu, turno) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $idusu, $turno_seleccionado);

            if ($stmt->execute()) {
                // Actualizar el campo idver en la tabla Usuario a 4 (Delivery)
                $sql_update = "UPDATE Usuario SET idver = 4 WHERE idusu = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("i", $idusu);
                $stmt_update->execute();

                // Si todo está bien, hacemos commit
                $conn->commit();

                echo "Te has registrado exitosamente como delivery.";
                // Redirigir a una página de éxito o dashboard
                header("Location: ../pages/delivery_dashboard.php");
                exit;
            } else {
                throw new Exception("Error al registrar como delivery: " . $conn->error);
            }

            $stmt->close();
            $stmt_update->close();
        } catch (Exception $e) {
            // Si algo falla, deshacemos la transacción
            $conn->rollback();
            echo $e->getMessage();
        }
    } else {
        echo "Por favor selecciona al menos un turno.";
    }
}

$conn->close();
?>
