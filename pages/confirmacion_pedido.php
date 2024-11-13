<?php
session_start();
include('../config/db.php');

// Función para encriptar datos de tarjeta
function encryptData($data)
{
    $encryption_key = base64_decode('TuClaveDeEncriptacion'); // Cambia esta clave por una segura
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Función para desencriptar datos de tarjeta con verificación
function decryptData($data)
{
    $encryption_key = base64_decode('TuClaveDeEncriptacion'); // Cambia esta clave por una segura
    $data_parts = explode('::', base64_decode($data), 2);
    if (count($data_parts) === 2) {
        list($encrypted_data, $iv) = $data_parts;
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    } else {
        // Retorna un valor predeterminado si el formato no es correcto
        return null;
    }
}

// Verificar si el comprador ha hecho un pedido
if (!isset($_GET['idped'])) {
    echo "Error: No se ha recibido el ID del pedido.";
    exit();
}

$id_pedido = $_GET['idped'];

// Consultar detalles del pedido
$query_pedido = "SELECT * FROM Pedido WHERE idped = '$id_pedido'";
$result_pedido = $conn->query($query_pedido);

if ($result_pedido->num_rows > 0) {
    $pedido = $result_pedido->fetch_assoc();
} else {
    echo "Error: No se encontró el pedido.";
    exit();
}

// Ruta del QR para transferencia bancaria
$qr_bancario = '/Anawa/assets/images/qr_transferencia.png';

// Consultar tarjetas guardadas del usuario
if (isset($_SESSION['user_id'])) {
    $idusu = $_SESSION['user_id'];
    $query_tarjetas = "SELECT * FROM MetodoPago WHERE idusu = '$idusu'";
    $result_tarjetas = $conn->query($query_tarjetas);
}

// Verificar si se ha enviado el formulario de pago
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $metodoPago = $_POST['metodoPago'];

    if (empty($metodoPago)) {
        echo "<p>Error: No se ha seleccionado un método de pago.</p>";
    } else {
        if (!isset($_SESSION['user_id'])) {
            echo "<p>Error: Usuario no autenticado.</p>";
            exit();
        }

        $idusu = $_SESSION['user_id'];

        if ($metodoPago === 'tarjeta') {
            // Encriptar los datos de la tarjeta
            $numeroTarjeta = encryptData($_POST['numeroTarjeta']);
            $expiracionTarjeta = encryptData($_POST['expiracionTarjeta']);
            $cvvTarjeta = encryptData($_POST['cvvTarjeta']);

            // Obtener los últimos 4 dígitos de la tarjeta ingresada
            $ultimoCuatroDigitos = substr($_POST['numeroTarjeta'], -4);
            $expiracion = $_POST['expiracionTarjeta'];

            // Consultar todas las tarjetas del usuario
            $query_tarjetas_existentes = "SELECT * FROM MetodoPago WHERE idusu = '$idusu'";
            $tarjetas_existentes = $conn->query($query_tarjetas_existentes);

            $tarjeta_existe = false;

            if ($tarjetas_existentes) {
                while ($tarjeta = $tarjetas_existentes->fetch_assoc()) {
                    // Desencriptar el número y fecha de expiración de la tarjeta existente
                    $numeroDesencriptado = decryptData($tarjeta['numeroTarjeta']);
                    $expiracionDesencriptada = decryptData($tarjeta['expiracionTarjeta']);

                    // Comparar los últimos 4 dígitos y la fecha de expiración
                    if (substr($numeroDesencriptado, -4) === $ultimoCuatroDigitos && $expiracionDesencriptada === $expiracion) {
                        $tarjeta_existe = true;
                        break;
                    }
                }
            }

            // Insertar la tarjeta solo si no existe
            if (!$tarjeta_existe) {
                $insert_tarjeta = "INSERT INTO MetodoPago (idusu, nombreTarjeta, numeroTarjeta, expiracionTarjeta, cvvTarjeta) VALUES ('$idusu', '{$_POST['nombreTarjeta']}', '$numeroTarjeta', '$expiracionTarjeta', '$cvvTarjeta')";
                $conn->query($insert_tarjeta);
            }

            // Insertar el pago en la tabla de Pago
            $insert_pago = "INSERT INTO Pago (fechapag, método, idped, estado_deposito) VALUES (NOW(), '$metodoPago', '$id_pedido', 'pendiente')";
            if ($conn->query($insert_pago) === TRUE) {
                $insert_solicitud = "INSERT INTO Solicitudes (idusu, tipo_solicitud, estado, idped) VALUES ('$idusu', 'pago', 'pendiente', '$id_pedido')";
                if ($conn->query($insert_solicitud) === TRUE) {
                    echo "<p>Tu solicitud de pago ha sido enviada. Espera la confirmación del administrador.</p>";
                } else {
                    echo "<p>Error al enviar la solicitud: " . $conn->error . "</p>";
                }
            } else {
                echo "<p>Error al procesar el pago: " . $conn->error . "</p>";
            }
        } elseif ($metodoPago === 'transferencia' && isset($_POST['confirmTransferencia'])) {
            $insert_pago = "INSERT INTO Pago (fechapag, método, idped, estado_deposito) VALUES (NOW(), '$metodoPago', '$id_pedido', 'pendiente')";
            if ($conn->query($insert_pago) === TRUE) {
                $insert_solicitud = "INSERT INTO Solicitudes (idusu, tipo_solicitud, estado, idped) VALUES ('$idusu', 'pago', 'pendiente', '$id_pedido')";
                if ($conn->query($insert_solicitud) === TRUE) {
                    echo "<p>Tu solicitud de pago ha sido enviada. Espera la confirmación del administrador.</p>";
                } else {
                    echo "<p>Error al enviar la solicitud: " . $conn->error . "</p>";
                }
            } else {
                echo "<p>Error al procesar el pago: " . $conn->error . "</p>";
            }
        } else {
            echo "<p>Error: Debes confirmar que realizaste la transferencia.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pedido</title>
    <link rel="stylesheet" href="/Anawa/assets/css/confirmacion_styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container">
        <h2>Confirmación de Pedido</h2>
        <p>Tu pedido con ID <b><?php echo $id_pedido; ?></b> ha sido realizado con éxito.</p>

        <form action="" method="POST" id="paymentForm">
            <input type="hidden" name="idped" value="<?php echo $id_pedido; ?>">

            <label for="metodoPago">Método de pago:</label>
            <select name="metodoPago" id="metodoPago" required>
                <option value="">Selecciona...</option>
                <option value="tarjeta">Tarjeta de Crédito</option>
                <option value="transferencia">Transferencia Bancaria</option>
            </select>

            <div id="tarjetaFields" style="display: none;">
                <h4>Detalles de la Tarjeta de Crédito</h4>
                <label for="nombreTarjeta">Nombre en la tarjeta:</label>
                <input type="text" id="nombreTarjeta" name="nombreTarjeta" required>

                <label for="numeroTarjeta">Número de tarjeta:</label>
                <input type="text" id="numeroTarjeta" name="numeroTarjeta" maxlength="16" required>

                <label for="expiracionTarjeta">Fecha de expiración:</label>
                <input type="month" id="expiracionTarjeta" name="expiracionTarjeta" required>

                <label for="cvvTarjeta">CVV:</label>
                <input type="text" id="cvvTarjeta" name="cvvTarjeta" maxlength="3" required>
            </div>

            <div id="transferenciaFields" style="display: none;">
                <h4>Transferencia Bancaria</h4>
                <p>Escanea el siguiente código QR para realizar la transferencia:</p>
                <img src="<?php echo $qr_bancario; ?>" alt="Código QR para transferencia" style="max-width: 200px;">
                <label for="confirmTransferencia">
                    <input type="checkbox" id="confirmTransferencia" name="confirmTransferencia"> Ya he realizado la
                    transferencia
                </label>
            </div>

            <h4>Usar tarjeta guardada</h4>
            <div id="tarjetasGuardadas" style="display: none;">
                <?php while ($tarjeta = $result_tarjetas->fetch_assoc()): ?>
                    <button type="button" class="tarjetaGuardada" data-nombre="<?php echo $tarjeta['nombreTarjeta']; ?>"
                        data-numero="<?php echo decryptData($tarjeta['numeroTarjeta']); ?>"
                        data-expiracion="<?php echo decryptData($tarjeta['expiracionTarjeta']); ?>"
                        data-cvv="<?php echo decryptData($tarjeta['cvvTarjeta']); ?>">
                        <p><strong>Nombre:</strong> <?php echo $tarjeta['nombreTarjeta']; ?></p>
                        <p><strong>Número:</strong> **** **** ****
                            <?php echo substr(decryptData($tarjeta['numeroTarjeta']), -4); ?>
                        </p>
                        <p><strong>Expiración:</strong> <?php echo decryptData($tarjeta['expiracionTarjeta']); ?></p>
                    </button>
                <?php endwhile; ?>
            </div>
            <button type="submit" id="submitPago" disabled>Pagar</button>
    </div>

    </form>

    <script>
        $(document).ready(function () {
            // Mostrar campos según el método de pago seleccionado
            $('#metodoPago').change(function () {
                var metodo = $(this).val();
                if (metodo === 'tarjeta') {
                    $('#tarjetaFields').show();
                    $('#tarjetasGuardadas').show();
                    $('#transferenciaFields').hide();
                    $('#submitPago').prop('disabled', true); // Deshabilitar por defecto hasta que se llenen los campos
                } else if (metodo === 'transferencia') {
                    $('#tarjetaFields').hide();
                    $('#tarjetasGuardadas').hide();
                    $('#transferenciaFields').show();
                    $('#submitPago').prop('disabled', true);
                } else {
                    $('#tarjetaFields').hide();
                    $('#tarjetasGuardadas').hide();
                    $('#transferenciaFields').hide();
                    $('#submitPago').prop('disabled', true);
                }
            });

            // Habilitar el botón de pago cuando el checkbox de transferencia esté marcado
            $('#confirmTransferencia').change(function () {
                $('#submitPago').prop('disabled', !$(this).is(':checked'));
            });

            // Función para verificar que todos los campos de la tarjeta están llenos
            function checkCardFields() {
                var allFieldsFilled = $('#nombreTarjeta').val() && $('#numeroTarjeta').val() && $('#expiracionTarjeta').val() && $('#cvvTarjeta').val();
                $('#submitPago').prop('disabled', !allFieldsFilled);
            }

            // Verificar los campos de la tarjeta en tiempo real
            $('#nombreTarjeta, #numeroTarjeta, #expiracionTarjeta, #cvvTarjeta').on('input', function () {
                checkCardFields();
            });

            // Al hacer clic en una tarjeta guardada, se rellenan los datos y se habilita el botón
            $('.tarjetaGuardada').click(function () {
                $('#nombreTarjeta').val($(this).data('nombre'));
                $('#numeroTarjeta').val($(this).data('numero'));
                $('#expiracionTarjeta').val($(this).data('expiracion'));
                $('#cvvTarjeta').val($(this).data('cvv'));

                // Ocultar campos manuales y tarjetas guardadas tras la selección
                $('#tarjetaFields').hide();
                $('#tarjetasGuardadas').hide();

                // Habilitar el botón "Pagar" y enviar el formulario automáticamente
                $('#submitPago').prop('disabled', false);
                $('#paymentForm').submit();
            });
        });

    </script>

    <a href="productos.php">Volver a productos</a>
    </div>
</body>

</html>