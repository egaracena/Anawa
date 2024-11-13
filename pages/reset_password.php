<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">

    <script>
        // Función para mostrar/ocultar contraseña
        function togglePasswordVisibility(inputId, eyeIconId) {
            var input = document.getElementById(inputId);
            var eyeIcon = document.getElementById(eyeIconId);

            if (input.type === "password") {
                input.type = "text";
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                input.type = "password";
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        }
    </script>
</head>
<body>
    <div class="signup-container">
        <h2>Restablecer tu contraseña</h2>
        <form action="../scripts/process_reset_password.php" method="POST">
            <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
            
            <div class="input-icon">
                <label for="password">Nueva Contraseña:</label>
                <i class="icon-normal  fas fa-lock"></i>
                <input type="password" id="password" name="password" required>
                <i class="eye-icon fas fa-eye-slash toggle-password" id="togglePassword" onclick="togglePasswordVisibility('password', 'togglePassword')"></i>
            </div>

            <div class="input-icon">
                <label for="confirm_password">Confirmar Nueva Contraseña:</label>
                <i class="icon-normal  fas fa-lock"></i>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <i class="eye-icon fas fa-eye-slash toggle-password" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')"></i>
            </div>

            <button type="submit">Restablecer Contraseña</button>
        </form>
    </div>
</body>
</html>
