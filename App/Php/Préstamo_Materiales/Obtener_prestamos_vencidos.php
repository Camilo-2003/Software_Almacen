<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
header('Content-Type: application/json');

$sql = "SELECT 
            m.nombre as nombre_material, 
            CONCAT(i.nombre, ' ', i.apellido) as nombre_instructor,
            pm.fecha_limite_devolucion
        FROM prestamo_materiales pm
        JOIN materiales m ON pm.id_material = m.id_material
        JOIN instructores i ON pm.id_instructor = i.id_instructor
        WHERE pm.estado = 'pendiente' AND pm.fecha_limite_devolucion IS NOT NULL AND pm.fecha_limite_devolucion < NOW()";

$resultado = $conexion->query($sql);
$vencidos = [];
if ($resultado) {
    while($row = $resultado->fetch_assoc()) {
        $vencidos[] = $row;
    }
}
$conexion->close();

echo json_encode(['success' => true, 'overdue_items' => $vencidos]);
?>