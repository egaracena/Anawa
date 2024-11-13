<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olvidé mi Contraseña</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="signup-container">
        <h2>Recuperar Contraseña</h2>
        <form action="../scripts/process_forgot_password.php" method="POST">
            <div class="input-icon">
                <label for="email">Introduce tu correo electrónico:</label>
                <i class="icon-normal fas fa-envelope"></i>
                <input type="email" id="email" name="email" required>
            </div>
            <button type="submit">Enviar correo</button>
        </form>
        <p>¿Recordaste tu contraseña? <a href="login.php">Inicia sesión aquí</a>.</p>
    </div>
</body>
</html>
