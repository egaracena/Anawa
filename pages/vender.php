<?php
session_start();
include('../config/db.php');

// Verificar si hay una sesión activa
if (!isset($_SESSION['user_id'])) {
    echo "Debes iniciar sesión para acceder a esta página.";
    exit;
}

// Mostrar el mensaje de rechazo si existe
if (isset($_SESSION['mensaje_rechazo'])) {
    $mensaje_rechazo = $_SESSION['mensaje_rechazo'];
    unset($_SESSION['mensaje_rechazo']); // Eliminar el mensaje de la sesión después de mostrarlo
} else {
    $mensaje_rechazo = '';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mandar Solicitud como Artesano</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Anawa/assets/css/artesano_styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Incluir jQuery -->
</head>
<body>
    <!-- Formulario para mandar solicitud como Artesano -->
    <section class="form-container">
        <h2>Mandar solicitud como Artesano</h2>
        
        <!-- Mostrar el mensaje de rechazo si existe -->
        <?php if ($mensaje_rechazo != ''): ?>
            <p style="color: red;"><?php echo $mensaje_rechazo; ?></p>
        <?php endif; ?>

        <form action="../scripts/process_artesano_solicitud.php" method="POST">
            <label for="comunidad">Selecciona tu Comunidad:</label>
            <select name="idcom" id="comunidad" required>
                <?php
                $sql = "SELECT idcom, nomcom FROM Comunidad";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['idcom']}'>{$row['nomcom']}</option>";
                    }
                }
                ?>
            </select>
            <button type="submit" class="form-btn">Mandar Solicitud</button>

            <!-- Botón para volver al index -->
            <button type="button" class="form-btn" onclick="window.location.href='../index.php'">Volver</button>
        </form>
        

        <!-- Mensaje de estado de la solicitud -->
        <div id="solicitud-status" style="margin-top: 20px;"></div>

    </section>

    <!-- Script AJAX para verificar el estado de la solicitud -->
    <script>
        $(document).ready(function () {
            // Verificar si la URL contiene el parámetro 'status=pendiente'
            let urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('status') && urlParams.get('status') === 'pendiente') {
                // Si el usuario ha mandado la solicitud, comenzamos la verificación periódica
                setInterval(function () {
                    $.ajax({
                        url: '../scripts/check_solicitud_status.php',
                        method: 'GET',
                        success: function (response) {
                            let data = JSON.parse(response);
                            if (data.status === 'success') {
                                if (data.estado === 'aprobado') {
                                    $('#solicitud-status').html("<p style='color: green;'>Felicidades, fuiste aceptado como artesano.</p>");
                                } else if (data.estado === 'rechazado') {
                                    $('#solicitud-status').html("<p style='color: red;'>Lo sentimos, fuiste rechazado.</p>");
                                }
                            } else if (data.status === 'pending') {
                                $('#solicitud-status').html("<p style='color: orange;'>Tu solicitud está en proceso. Espera la validación del administrador.</p>");
                            }
                        }
                    });
                }, 5000); // Intervalo de 5 segundos para verificar el estado
            }
        });
    </script>
</body>
</html>
