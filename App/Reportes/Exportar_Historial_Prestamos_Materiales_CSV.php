<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Bogota');
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php'; 

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}

$filename = 'Historial_Prestamos_Materiales_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF");

$delimiter = ';'; 

fputcsv($output, [
    'ID Prestamo',
    'Material',
    'Tipo',
    'Cantidad',
    'Instructor',
    'Fecha Prestamo',   
    'Hora Prestamo',    
    'Fecha Devolucion',  
    'Hora Devolucion',   
    'Estado Prestamo',
    'Responsable',
    'Estado Devolucion'
], $delimiter);

$sql = "
    SELECT
        pm.id_prestamo_material,
        m.nombre AS nombre_material,
        m.tipo AS tipo_material,
        pm.cantidad,
        i.nombre AS nombre_instructor,
        i.apellido AS apellido_instructor,
        DATE_FORMAT(pm.fecha_prestamo, '%Y-%m-%d') AS fecha_prestamo_formateada, -- Solo fecha
        DATE_FORMAT(pm.fecha_prestamo, '%H:%i:%s') AS hora_prestamo_formateada,   -- Solo hora
        pm.estado,
        pm.id_responsable,
        pm.rol_responsable,
        pm.responsable,
        dm.estado_devolucion,
        DATE_FORMAT(dm.fecha_devolucion, '%Y-%m-%d') AS fecha_devolucion_formateada, -- Solo fecha de devolución
        DATE_FORMAT(dm.fecha_devolucion, '%H:%i:%s') AS hora_devolucion_formateada   -- Solo hora de devolución
    FROM prestamo_materiales pm
    JOIN materiales m ON pm.id_material = m.id_material
    JOIN instructores i ON pm.id_instructor = i.id_instructor
    LEFT JOIN devolucion_materiales dm ON pm.id_prestamo_material = dm.id_prestamo_material
    ORDER BY pm.fecha_prestamo DESC;
";

$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $nombre_instructor_completo = $row['nombre_instructor'] . ' ' . $row['apellido_instructor'];
        $responsable_completo = $row['rol_responsable'] . ' (ID: ' . $row['id_responsable'] . ') - ' . $row['responsable'];
        
        $fecha_devolucion_csv = $row['fecha_devolucion_formateada'];
        $hora_devolucion_csv = $row['hora_devolucion_formateada'];
        $estado_devolucion_csv = $row['estado_devolucion'];

        if (empty($row['fecha_devolucion_formateada'])) {
            $fecha_devolucion_csv = 'Pendiente'; 
            $hora_devolucion_csv = '';
        }
        if (empty($row['estado_devolucion'])) {
            $estado_devolucion_csv = 'N/A';
        }

        fputcsv($output, [
            $row['id_prestamo_material'],
            $row['nombre_material'],
            $row['tipo_material'],
            $row['cantidad'],
            $nombre_instructor_completo,
            $row['fecha_prestamo_formateada'], 
            $row['hora_prestamo_formateada'],  
            $fecha_devolucion_csv,             
            $hora_devolucion_csv,             
            $row['estado'],
            $responsable_completo,
            $estado_devolucion_csv
        ], $delimiter);
    }
}
fclose($output);
$conexion->close();
exit;
?>