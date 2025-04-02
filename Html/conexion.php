<?php

$host = "localhost";
$user = "root";
$password = "";
$db = "almacen";


$conexion = new mysqli($host, $user, $password, $db);

if ($conexion->connect_error) {
    die("❌Conexión fallida: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>
