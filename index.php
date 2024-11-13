<?php
session_start(); // Iniciar sesión para acceder a las variables de sesión

include('config/db.php');

// Obtener el contenido de marketing de la base de datos
$sql_marketing = "SELECT * FROM Marketing ORDER BY idmark DESC LIMIT 3"; // Mostrar los últimos 3 contenidos
$result_marketing = $conn->query($sql_marketing);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi E-Commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/Anawa/assets/css/index_styles.css">
</head>
<body>
    <!-- Barra de navegación -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="#"><img src="/Anawa/assets/images/LogoAnawa2.png" alt="Logo"></a>
                <ul class="nav-links">
                <li><a href="#">Inicio</a></li>
                <li><a href="/Anawa/pages/servicio_cliente.php">Servicio al Cliente</a></li>
                <li><a href="/Anawa/pages/productos.php">Productos</a></li>                
                <li><a href="/Anawa/pages/vender.php">Vender</a></li> 
                <li><a href="/Anawa/pages/trabaja_con_nosotros.php">Trabaja con Nosotros</a></li> 
            </ul>
            </div>
            <div class="user-section">
                <?php if (isset($_SESSION['username'])): ?>
                    <!-- Mostrar mensaje de bienvenida si el usuario ha iniciado sesión -->
                    <div class="welcome-msg">
                        Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?> 
                        <a href="/Anawa/pages/logout.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                <?php else: ?>
                    <!-- Mostrar los botones de login y registro si el usuario no ha iniciado sesión -->
                    <a href="/Anawa/pages/login.php" class="login-btn">Iniciar sesión</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Banner principal con texto centrado -->
    <section class="hero-banner">
        <div class="banner-content">
            <h1>RAÍCES DE BOLIVIA EN CADA CREACIÓN</h1>
            <p>No puedes comprar AMOR, pero puedes comprar algo HECHO A MANO y eso es más o menos lo mismo</p>
            <a href="/Anawa/pages/signup.php" class="cta-btn">Registrate</a>
        </div>
    </section>

    <!-- Sección de Servicios -->
    <section class="services">
        <h2>Nuestros Servicios</h2>
        <div class="services-grid">
            <div class="service-item">
                <img src="service-icon1.png" alt="Diseño Gráfico">
                <h3>Diseño Gráfico y Logotipos</h3>
                <p>Creamos imágenes atractivas y memorables para fortalecer tu marca.</p>
            </div>
            <div class="service-item">
                <img src="service-icon2.png" alt="Desarrollo Web">
                <h3>Desarrollo Web</h3>
                <p>Desarrollamos sitios web efectivos que impulsan el crecimiento de tu negocio.</p>
            </div>
            <div class="service-item">
                <img src="service-icon3.png" alt="SEO">
                <h3>Optimización para Motores de Búsqueda (SEO)</h3>
                <p>Posicionamos tu sitio web para mejorar su visibilidad en los resultados de búsqueda.</p>
            </div>
            <div class="service-item">
                <img src="service-icon4.png" alt="Marketing Online">
                <h3>Marketing Online</h3>
                <p>Te ayudamos a llegar a más clientes a través de estrategias efectivas de marketing digital.</p>
            </div>
        </div>
    </section>

    <!-- Sección de marketing -->
    <section class="marketing">
    <h2>Últimos Contenidos de Marketing</h2>
    <div class="marketing-grid">
        <?php if ($result_marketing->num_rows > 0): ?>
            <?php while ($row = $result_marketing->fetch_assoc()): ?>
                <div class="marketing-item">
                    <?php if (!empty($row['imagen'])): ?>
                        <img src="<?php echo $row['imagen']; ?>" alt="Imagen de Marketing">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($row['tipo']); ?></h3>
                    <p><?php echo htmlspecialchars($row['contenido']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No hay contenido de marketing disponible.</p>
        <?php endif; ?>
    </div>
    </section>

    <!-- Pie de página -->
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h4>Acerca de Nosotros</h4>
                <p>Información sobre la tienda.</p>
            </div>
            <div class="footer-section">
                <h4>Enlaces Rápidos</h4>
                <ul>
                    <li><a href="#">Términos y Condiciones</a></li>
                    <li><a href="#">Política de Privacidad</a></li>
                    <li><a href="#">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Redes Sociales</h4>
                <ul>
                    <li><a href="#">Facebook</a></li>
                    <li><a href="#">Instagram</a></li>
                    <li><a href="#">Twitter</a></li>
                </ul>
            </div>
        </div>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
