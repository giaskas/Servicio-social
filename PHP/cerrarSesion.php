<?php
    //cierra sesiones para que no puedan entrar a la pagina principal sin iniciar sesion
    session_start();
    $_SESSION = [];
    session_destroy();
    setcookie(session_name(), '', time() - 42000);
    header('Location: ../HTML/login.html');
exit;
?>