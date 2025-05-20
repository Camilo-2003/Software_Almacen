<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/conexion.php';
session_start();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = $_POST['tipo'];
    $equipo = $_POST['equipo']; 
    $serial = $_POST['serial'];
    $cantidad = $_POST['cantidad'];
    $instructor = $_POST['instructor'];
    $fecha = date("Y-m-d");
    $hora = date("H:i:s");
    $estado = "Prestado";

    $sql = "INSERT INTO prestamo_equipos (tipo, equipo, serial, cantidad, instructor, fecha, hora, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssissss", $tipo, $equipo, $serial, $cantidad, $instructor, $fecha, $hora, $estado);

    if ($stmt->execute()) {
        echo "Préstamo registrado exitosamente.";
    } else {
        echo "Error al registrar el préstamo: " . $stmt->error;
    }

    $stmt->close();
    $conexion->close();
}
?>