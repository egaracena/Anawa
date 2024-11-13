<?php

session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    // Si no está definida la sesión, redirigir al login
    header("Location: ../pages/login.php");
    exit();
}

// Obtener el idusu y otros datos de la sesión
$idusu = $_SESSION['user_id'];
include('../config/db.php');

// Consultar todos los productos
$sql = "SELECT p.idprod, p.nomprod, p.descripción, p.precio, c.nomCat, p.imagen1, i.cantprod 
        FROM Producto p 
        JOIN Categoria c ON p.idcat = c.idcat 
        JOIN Inventario i ON p.idinv = i.idinv";$result = $conn->query($sql);

// Inicializar el carrito si no existe
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Anawa/assets/css/productos_styles.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> <!-- Cargar jQuery -->
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="#"><img src="/Anawa/assets/images/LogoAnawa2.png" alt="Logo"></a>
            </div>
            <ul class="nav-links">
                <li><a href="../index.php">Inicio</a></li>
                <li><a href="#">Productos</a></li>
                <li><a href="cart.php">Carrito (<span
                            id="cart-count"><?php echo count($_SESSION['cart']); ?></span>)</a></li>
                <li><a href="/Anawa/pages/mis_pedidos.php">Pedidos</a></li>
            </ul>
            <div>
                <a href="/Anawa/pages/logout.php" class="logout-btn">Cerrar sesión</a>
            </div>
        </nav>
    </header>

    <section class="products-list">
        <h2>Productos Disponibles</h2>
        <div class="products-container">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='product-item'>
            <img class='product-img' src='{$row['imagen1']}' alt='{$row['nomprod']}'>
            <h3>{$row['nomprod']}</h3>
            <p>{$row['descripción']}</p>
            <p>Categoría: {$row['nomCat']}</p>
            <p>Precio: {$row['precio']} Bs</p>
            <p>Disponible: {$row['cantprod']} unidades</p>";

                    if ($row['cantprod'] > 0) {
                        echo "<form class='add-to-cart-form'>
                <input type='hidden' name='idprod' value='{$row['idprod']}'>
                <label for='cantidad'>Cantidad:</label>
                <input type='number' name='cantidad' min='1' max='{$row['cantprod']}' value='1'>
                <button type='submit' class='add-to-cart-btn'>Añadir al Carrito</button>
            </form>";
                    } else {
                        echo "<p><strong>Producto agotado</strong></p>";
                    }

                    echo "<button class='view-more-btn' data-idprod='{$row['idprod']}'>Ver Más</button>
        </div>";
                }
            } else {
                echo "<p>No hay productos disponibles.</p>";
            }
            ?>

        </div>
    </section>

    <div id="product-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <div class="modal-body">
                <!-- Los detalles del producto se cargarán aquí con AJAX -->
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            // Abrir modal y cargar más detalles del producto
            $('.view-more-btn').click(function () {
                var idprod = $(this).data('idprod');

                $.ajax({
                    type: 'POST',
                    url: '../scripts/product_details.php', // Archivo PHP para obtener detalles del producto
                    data: { idprod: idprod },
                    success: function (response) {
                        $('.modal-body').html(response);
                        $('#product-modal').css('display', 'block');
                    },
                    error: function () {
                        alert("Error al cargar los detalles del producto.");
                    }
                });
            });

            // Cerrar el modal
            $('.close-btn').click(function () {
                $('#product-modal').css('display', 'none');
            });

            // Cerrar el modal cuando se hace clic fuera de él
            $(window).click(function (event) {
                if ($(event.target).is('#product-modal')) {
                    $('#product-modal').css('display', 'none');
                }
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            // Manejar el envío del formulario con Ajax
            $('.add-to-cart-form').submit(function (e) {
                e.preventDefault(); // Prevenir la recarga de la página

                var $form = $(this);
                var cantidad = parseInt($form.find('input[name="cantidad"]').val());
                var maxCantidad = parseInt($form.find('input[name="cantidad"]').attr('max'));

                if (cantidad > maxCantidad) {
                    alert("No puedes añadir más de " + maxCantidad + " unidades.");
                    return false;
                }

                var formData = $form.serialize(); // Obtener los datos del formulario
                $.ajax({
                    type: 'POST',
                    url: '../scripts/add_to_cart.php', // El archivo PHP que procesará la solicitud
                    data: formData,
                    dataType: 'json', // Esperamos una respuesta JSON del servidor
                    success: function (response) {
                        if (response.status === 'success') {
                            alert("Producto añadido al carrito!");
                            // Actualizar el número de productos en el carrito en el header
                            $('#cart-count').text(response.cart_count); // Actualizar el número del carrito
                        } else {
                            alert("Error al añadir el producto al carrito.");
                        }
                    },
                    error: function () {
                        alert("Error al procesar la solicitud.");
                    }
                });
            });
        });
    </script>
</body>

</html>