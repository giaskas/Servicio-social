<php
    $SERVER = "localhost";
    $USER = "root";
    $PASSWORD = "";
    $BD = "servicio_social";

    $conexion = new mysqli_connect($SERVER, $USER, $PASSWORD, $BD);
    if($conexion->connect_errno){
        die("Conexion fallida: " . $conexion->connect_error);
    } else {
        echo "Conexion exitosa";
    }
?>