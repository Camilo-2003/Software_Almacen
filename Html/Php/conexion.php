<?php


$host = "localhost";
$user = "root";
$password = "";
$db = "almacen";

$conexion = new mysqli($host, $user, $password, $db);

if ($conexion->connect_errno) {
    die("Conexion Fallida" . $conexion->connect_errno );

} else {
    echo"Conexión Correcta";
}


?>

<!--http://localhost:3000/Html/Css/PHP/conexion.php-->