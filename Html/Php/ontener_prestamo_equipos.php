<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/conexion.php';

header('Content-Type: application/json');

// Consulta para obtener préstamos activos
$sql = "SELECT p.id_prestamo_equipo, e.tipo, e.nombre_equipo, e.serial, p.fecha_prestamo, i.nombre AS instructor
        FROM prestamo_equipos p
        JOIN equipos e ON p.id_equipo = e.id_equipo
        JOIN instructores i ON p.id_instructor = i.id_instructor
        WHERE p.estado = 'pendiente'";

$resultado = $conexion->query($sql);

$prestamos = [];

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $prestamos[] = $fila;
    }
}

echo json_encode($prestamos); 

?>
