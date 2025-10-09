<?php
include '../PHP/conexion.php';
$nombre = $_POST['usuario'];
$contrasena = $_POST['contrasena'];
$consulta = "SELECT IdUsuario, ContrasenaConHash FROM usuarios WHERE Nombre = ?";
$resultado = $conexion->prepare($consulta);
if ($resultado) {
    $resultado->bind_param("s", $nombre);
    $resultado->execute();
    $resultado = $resultado->get_result();
    if ($resultado->num_rows === 1) {
        $fila = $resultado->fetch_assoc();  
        $hash_almacenado = $fila['ContrasenaConHash'];
        $hash_ingresado = md5($contrasena);

        if ($hash_ingresado === $hash_almacenado) {
            session_start();
            $_SESSION['usuario'] = $nombre;
            session_regenerate_id(true);
            header("Location: ../HTML/paginaPrincipal.html");
            exit(); 
        } else {
           
            $error = urlencode("Contraseña incorrecta");
            header("Location: ../HTML/login.html?error=" . $error);
            exit();
        }
    } else {
        $error = urlencode("El usuario no existe");
        header("Location: ../HTML/login.html?error=" . $error);
        exit();
    }

    $resultado->close();
} else {
  
    $error = urlencode("Error en el sistema, por favor intente más tarde.");
    header("Location: ../HTML/login.html?error=" . $error);
    exit();
}

$conexion->close();
?>