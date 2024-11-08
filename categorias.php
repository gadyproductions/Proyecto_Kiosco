<?php
    // Conecta a tu base de datos
    include 'php/Conexion.php';
    // Obtiene la categoría desde el parámetro de la URL
    $categoria = $_GET['categoria'];

    // Obtiene los productos asociados con la categoría proporcionada
    $query = "SELECT * FROM productos WHERE categoria = '$categoria'";
    $result = mysqli_query($db, $query);

    // Muestra los productos
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            echo "<p>" . $row['nombre_producto'] . "</p>";
        }
    } else {
        echo "<p>No se encontraron productos en esta categoría.</p>";
    }
?>