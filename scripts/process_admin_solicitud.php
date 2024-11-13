<?php
session_start();
include('../config/db.php');

// Verificar si es un administrador
if (!isset($_SESSION['user_id']) || $_SESSION['idver'] != 1) {
    echo "Acceso denegado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idsolicitud = $_POST['idsolicitud'];
    $idusu = $_POST['idusu'];
    $idped = $_POST['idped'] ?? null; // Obtener idped de la solicitud de pago
    $tipo_solicitud = $_POST['tipo_solicitud'];
    $accion = $_POST['accion'];
    $idcom = $_POST['idcom'] ?? null; // Comunidad para artesano (si aplica)
    $turno = $_POST['turno'] ?? null; // Turno para delivery (si aplica)

    // Iniciar la transacción
    $conn->begin_transaction();

    try {
        if ($accion == 'aprobar') {
            // Actualizar el estado de la solicitud en la tabla Solicitudes
            $sql_update_solicitud = "UPDATE Solicitudes SET estado = 'aprobado' WHERE idsolicitud = ?";
            $stmt_solicitud = $conn->prepare($sql_update_solicitud);
            if (!$stmt_solicitud) {
                throw new Exception("Error al preparar la consulta de solicitud: " . $conn->error);
            }
            $stmt_solicitud->bind_param("i", $idsolicitud);
            $stmt_solicitud->execute();

            // Manejar las solicitudes de acuerdo a su tipo
            if ($tipo_solicitud == 'artesano') {
                $idver = 2; // idver para Artesano

                // Actualizar la tabla Usuario y asignar idver
                $sql_update_usuario = "UPDATE Usuario SET idver = ?, estado_solicitud = 'aprobado' WHERE idusu = ?";
                $stmt_usuario = $conn->prepare($sql_update_usuario);
                if (!$stmt_usuario) {
                    throw new Exception("Error al preparar la consulta de actualización de usuario: " . $conn->error);
                }
                $stmt_usuario->bind_param("ii", $idver, $idusu);
                $stmt_usuario->execute();

                // Insertar en la tabla Artesano
                $sql_insert_artesano = "INSERT INTO Artesano (idusu, idcom) VALUES (?, ?)";
                $stmt_artesano = $conn->prepare($sql_insert_artesano);
                if (!$stmt_artesano) {
                    throw new Exception("Error al preparar la consulta de inserción de artesano: " . $conn->error);
                }
                $stmt_artesano->bind_param("ii", $idusu, $idcom);
                $stmt_artesano->execute();
                $stmt_artesano->close();

            } elseif ($tipo_solicitud == 'delivery') {
                $idver = 4; // idver para Delivery

                // Actualizar la tabla Usuario y asignar idver
                $sql_update_usuario = "UPDATE Usuario SET idver = ?, estado_solicitud = 'aprobado' WHERE idusu = ?";
                $stmt_usuario = $conn->prepare($sql_update_usuario);
                if (!$stmt_usuario) {
                    throw new Exception("Error al preparar la consulta de actualización de usuario: " . $conn->error);
                }
                $stmt_usuario->bind_param("ii", $idver, $idusu);
                $stmt_usuario->execute();

                // Insertar en la tabla Delivery
                $sql_insert_delivery = "INSERT INTO Delivery (idusu, turno) VALUES (?, ?)";
                $stmt_delivery = $conn->prepare($sql_insert_delivery);
                if (!$stmt_delivery) {
                    throw new Exception("Error al preparar la consulta de inserción de delivery: " . $conn->error);
                }
                $stmt_delivery->bind_param("is", $idusu, $turno);
                $stmt_delivery->execute();
                $stmt_delivery->close();

            } elseif ($tipo_solicitud == 'pago') {
                if (!$idped) {
                    throw new Exception("No se proporcionó un idped válido para la solicitud de pago.");
                }

                // Procesar solicitud de pago y actualizar el estado en la tabla Pago
                $sql_update_pago = "UPDATE Pago SET estado_deposito = 'confirmado' WHERE idped = ?";
                $stmt_pago = $conn->prepare($sql_update_pago);
                if (!$stmt_pago) {
                    throw new Exception("Error al preparar la consulta de actualización de pago: " . $conn->error);
                }
                $stmt_pago->bind_param("i", $idped);  // Usando $idped para actualizar el pago
                $stmt_pago->execute();

                // Actualizar inventario para cada producto en el pedido
                $sql_detalle = "SELECT idprod, cantidad FROM DetallePedido WHERE idped = ?";
                $stmt_detalle = $conn->prepare($sql_detalle);
                $stmt_detalle->bind_param("i", $idped);
                $stmt_detalle->execute();
                $result_detalle = $stmt_detalle->get_result();

                while ($detalle = $result_detalle->fetch_assoc()) {
                    $idprod = $detalle['idprod'];
                    $cantidad = $detalle['cantidad'];

                    // Actualizar el inventario de cada producto
                    $sql_update_inventario = "UPDATE Inventario SET cantprod = cantprod - ? WHERE idinv = (SELECT idinv FROM Producto WHERE idprod = ?)";
                    $stmt_inventario = $conn->prepare($sql_update_inventario);
                    if (!$stmt_inventario) {
                        throw new Exception("Error al preparar la consulta de actualización de inventario: " . $conn->error);
                    }
                    $stmt_inventario->bind_param("ii", $cantidad, $idprod);
                    $stmt_inventario->execute();
                    $stmt_inventario->close();
                }                
            }

            $conn->commit();
            echo "Solicitud aprobada.";
        } elseif ($accion == 'rechazar') {
            // Rechazar la solicitud
            $sql_update_solicitud = "UPDATE Solicitudes SET estado = 'rechazado' WHERE idsolicitud = ?";
            $stmt_solicitud = $conn->prepare($sql_update_solicitud);
            if (!$stmt_solicitud) {
                throw new Exception("Error al preparar la consulta de rechazo de solicitud: " . $conn->error);
            }
            $stmt_solicitud->bind_param("i", $idsolicitud);
            $stmt_solicitud->execute();

            // Actualizar el estado_solicitud en la tabla Usuario a 'rechazado'
            $sql_update_usuario = "UPDATE Usuario SET estado_solicitud = 'rechazado' WHERE idusu = ?";
            $stmt_usuario = $conn->prepare($sql_update_usuario);
            if (!$stmt_usuario) {
                throw new Exception("Error al preparar la consulta de actualización de usuario: " . $conn->error);
            }
            $stmt_usuario->bind_param("i", $idusu);
            $stmt_usuario->execute();

            $conn->commit();
            echo "Solicitud rechazada.";
        }

        // Redirigir después de la aprobación/rechazo
        header("Location: ../pages/admin_dashboard.php");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    // Verificación de definición antes de cerrar
    if (isset($stmt_solicitud)) {
        $stmt_solicitud->close();
    }

    if (isset($stmt_usuario)) {
        $stmt_usuario->close();
    }
}

$conn->close();
?>
