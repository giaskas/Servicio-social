<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../HTML/login.html');
    exit;
}
// Contenido restringido
