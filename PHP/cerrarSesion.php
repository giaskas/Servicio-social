<?php
    session_start();
    $_SESSION = [];
    session_destroy();
    setcookie(session_name(), '', time() - 42000);
    header('Location: ../HTML/login.html');
exit;
?>