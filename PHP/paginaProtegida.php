<?php
session_start();
//verifica si hay sesion abierta
if (!isset($_SESSION['usuario'])) {
    //en caso que no, te manda a login
    header('Location: ../HTML/login.html');
    exit;
}
