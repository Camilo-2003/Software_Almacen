<?php
require_once '/xampp/htdocs/Software_Almacen/Html/conexion.php';

$sql = "SELECT p.id_prestamo, m.nombre AS material, i.nombre AS instructor, p.cantidad, p.fecha_prestamo
        FROM prestamo_materiales p
        JOIN materiales m ON p.id_material = m.id_material
        JOIN instructores i ON p.id_instructor = i.id_instructor
        WHERE p.estado = 'pendiente'";

$result = $conexion->query($sql);

echo "<table border='1'>
        <tr>
            <th>ID</th>
            <th>Material</th>
            <th>Instructor</th>
            <th>Cantidad</th>
            <th>Fecha de Préstamo</th>
            <th>Acción</th>
        </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['id_prestamo']}</td>
            <td>{$row['material']}</td>
            <td>{$row['instructor']}</td>
            <td>{$row['cantidad']}</td>
            <td>{$row['fecha_prestamo']}</td>
            <td><a href='devolver_material.php?id={$row['id_prestamo']}'>Devolver</a></td>
        </tr>";
}

echo "</table>";

$conexion->close();
?>

