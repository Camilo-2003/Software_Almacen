<?php

$host = "localhost";
$user = "root";
$password = "";
$db = "almacen";


// Crear conexión
$conexion = new mysqli($host, $user, $password, $db);

// Verificar conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

// Opcional: establecer charset para evitar problemas con acentos y caracteres especiales
$conexion->set_charset("utf8mb4");

//http://localhost:3000/Html/Css/PHP/conexion.php