<?php
    include '../PHP/conexion.php';

    $nombre = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $consulta = "SELECT contrasena FROM usuarios WHERE nombre= '$nombre'";
    $resultado = $conexion->query($consulta);

    if( $resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $contrasena_almacenada = $fila['contrasena'];


        //verificamos la contrasena
        if($contrasena == $contrasena_almacenada) {
            echo 'Inicio de sesion exitoso';
            header("Location: ../HTML/paginaPrincipal.html");
            exit();
        } else {
            echo "<script>
                alert('Contraseña incorrecta');
                window.location.href = '../HTML/login.html';
                </script>";   
        }
    }else{
        echo "<script>
            alert('Usuario incorrecto');
            window.location.href = '../HTML/login.html';
            </script>";   
    }

    $conexion->close();
?>