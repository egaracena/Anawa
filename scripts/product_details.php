<?php
session_start();
print_r($_SESSION); // Esto imprimirá todas las variables de sesión para que puedas verificar si el idusu está presente.

include('../config/db.php');

if (isset($_POST['idprod'])) {
    $idprod = $_POST['idprod'];

    // Consultar detalles del producto incluyendo el artesano y la cantidad disponible
    $sql = "SELECT p.idprod, p.nomprod, p.descripción, p.precio, p.dimensiones, c.nomcat, p.imagen1, p.imagen2, p.imagen3, u.nomusu, i.cantprod 
            FROM Producto p 
            JOIN Categoria c ON p.idcat = c.idcat 
            JOIN Usuario u ON p.idusu = u.idusu 
            JOIN Inventario i ON p.idinv = i.idinv 
            WHERE p.idprod = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idprod);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Mostrar detalles del producto
        echo "<div class='product-detail'>
            <h2>{$row['nomprod']}</h2>
            <p>{$row['descripción']}</p>
            <p>Categoría: {$row['nomcat']}</p>
            <p>Precio: {$row['precio']} Bs</p>
            <p>Dimensiones: {$row['dimensiones']}</p>
            <p>Artesano: {$row['nomusu']}</p>
            <p>Cantidad Disponible: {$row['cantprod']}</p>
            <div class='images'>
                <img src='{$row['imagen1']}' alt='Imagen principal'>
                <img src='{$row['imagen2']}' alt='Imagen secundaria'>
                <img src='{$row['imagen3']}' alt='Imagen terciaria'>
            </div>";

        // Mostrar formulario para agregar comentarios
        echo "<div class='comments-section'>
            <h3>Comentarios</h3>
            <form id='comment-form'>
                <textarea name='comentario' placeholder='Escribe tu comentario aquí...' required></textarea>
                <label for='calificación'>Calificación:</label>
                <select name='calificación' required>
                    <option value='1'>1 estrella</option>
                    <option value='2'>2 estrellas</option>
                    <option value='3'>3 estrellas</option>
                    <option value='4'>4 estrellas</option>
                    <option value='5'>5 estrellas</option>
                </select>
                <input type='hidden' name='idprod' value='{$row['idprod']}'>
                <button type='submit'>Publicar comentario</button>
            </form>";

        // Consultar comentarios existentes
        $commentSql = "SELECT c.comentario, c.calificación, c.fechaCali, u.nomusu FROM Califica c 
                       JOIN Usuario u ON c.idusu = u.idusu 
                       WHERE c.idprod = ? ORDER BY c.fechaCali DESC";
        $stmt = $conn->prepare($commentSql);
        $stmt->bind_param("i", $idprod);
        $stmt->execute();
        $comments = $stmt->get_result();

        if ($comments->num_rows > 0) {
            echo "<ul class='comment-list'>";
            while ($commentRow = $comments->fetch_assoc()) {
                echo "<li>
                    <strong>{$commentRow['nomusu']}</strong> ({$commentRow['fechaCali']}): 
                    <p>Calificación: {$commentRow['calificación']} estrellas</p>
                    <p>{$commentRow['comentario']}</p>
                </li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No hay comentarios aún. Sé el primero en comentar.</p>";
        }
        echo "</div>";
    }
}

?>

<script>
  $('#comment-form').submit(function (e) {
    e.preventDefault(); // Prevenir la recarga de la página

    var formData = $(this).serialize(); // Obtener los datos del formulario

    $.ajax({
        type: 'POST',
        url: '../scripts/add_comment.php', // Archivo PHP que procesará el comentario
        data: formData,
        dataType: 'json', // Esperamos una respuesta en JSON
        success: function (response) {
            if (response.status === 'success') {
                alert("Comentario publicado con éxito!");

                // Añadir el nuevo comentario a la lista de comentarios sin recargar todo
                var nuevoComentario = `
                    <li>
                        <strong>${response.username}</strong> (${response.fechaCali}): 
                        <p>Calificación: ${response.calificación} estrellas</p>
                        <p>${response.comentario}</p>
                    </li>`;

                // Agregar el nuevo comentario al final de la lista de comentarios
                $('.comment-list').append(nuevoComentario);

                // Limpiar el formulario para que el usuario pueda agregar más comentarios si lo desea
                $('#comment-form')[0].reset();
            } else {
                alert("Error: " + response.message); // Mostrar el mensaje de error desde el servidor
            }
        },
        error: function (xhr, status, error) {
            // Mostrar el error del servidor para depuración
            console.error("Error de AJAX:", error); // Mostrar en consola
            console.error("Detalles:", xhr.responseText); // Ver la respuesta completa del servidor
            alert("Error en la solicitud: " + xhr.responseText); // Mostrar el contenido de la respuesta del servidor en el alert
        }
    });
});

</script>