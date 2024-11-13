<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <!-- Google Fonts y FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <script>
        function validatePassword() {
            var password = document.getElementById("password").value;
            var confirm_password = document.getElementById("confirm_password").value;
            var passwordError = document.getElementById("password-error");
            var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;
            
            if (!regex.test(password)) {
                passwordError.textContent = "La contraseña debe contener al menos 8 caracteres, incluir letras mayúsculas y minúsculas, números y caracteres especiales.";
                return false;
            }

            if (password !== confirm_password) {
                passwordError.textContent = "Las contraseñas no coinciden.";
                return false;
            }

            passwordError.textContent = "";
            return true;
        }

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
        <h2>Crear una cuenta</h2>
        <form action="../scripts/process_signup.php" method="POST" onsubmit="return validatePassword()">
            
            <div class="input-icon">
                <label for="ci">Cédula de Identidad (CI):</label>
                <i class="icon-normal fas fa-id-card"></i>
                <input type="text" id="ci" name="ci" required>
            </div>

            <div class="input-icon">
                <label for="nomusu">Nombre de usuario:</label>
                <i class="icon-normal fas fa-user"></i>
                <input type="text" id="nomusu" name="nomusu" required>
            </div>

            <div class="input-icon">
                <label for="celular">Número de celular:</label>
                <i class="icon-normal fas fa-phone"></i>
                <input type="text" id="celular" name="celular" required>
            </div>

            <div class="input-icon">
                <label for="email">Correo electrónico:</label>
                <i class="icon-normal fas fa-envelope"></i>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="input-icon">
                <label for="password">Contraseña:</label>
                <i class="icon-normal fas fa-lock"></i>
                <input type="password" id="password" name="password" required>
                <i class="eye-icon fas fa-eye-slash toggle-password" id="togglePassword" onclick="togglePasswordVisibility('password', 'togglePassword')"></i>
            </div>

            <div class="input-icon">
                <label for="confirm_password">Confirmar contraseña:</label>
                <i class="icon-normal fas fa-lock"></i>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <i class="eye-icon fas fa-eye-slash toggle-password" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')"></i>
            </div>

            <span id="password-error"></span>

            <!-- Aquí se integra reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="6LcQoTkqAAAAAJwlqGphxe3ZJU-MkhKfXWE9FSBZ"></div>
            
            <button type="submit">Registrarse</button>
        </form>
        <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>.</p>
    </div>
</body>
</html>
