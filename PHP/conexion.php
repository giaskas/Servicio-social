<?php
    $SERVER = "localhost";
    $USER = "root";
    $PASSWORD = "";
    $BD = "servicio_social";
    $PORT = 4306;

    $conexion = new mysqli($SERVER, $USER, $PASSWORD, $BD, $PORT);
    if($conexion -> connect_errno){
        die("Conexion fallida: " . $conexion->connect_error);
    } else {
        echo "Conexion exitosa";
    }
?>