<?php
    include '../PHP/conexion.php';

    $consulta = "SELECT nombre FROM archivos";
    $resultado = $conexion->query($consulta);

    if ($resultado->num_rows > 0) {
        echo "<ul>";    
        while ($fila = $resultado->fetch_assoc()) {
            // usar htmlspecialchars para evitar inyección/XSS al mostrar en HTML
            echo "" . $fila['nombre'] . "<br>";
        }
        echo "</ul>";
       
    } else {
        echo "No se encontraron archivos.";
    }
?>