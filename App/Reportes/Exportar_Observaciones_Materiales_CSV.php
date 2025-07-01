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
$filename = 'Observaciones_Materiales_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF");

$delimiter = ';'; 

fputcsv($output, [
    'Material',
    'Tipo',
    'Instructor',
    'Fecha Prestamo',
    'Hora Prestamo',
    'Fecha Devolucion',
    'Hora Devolucion',
    'Estado Devolucion',
    'Observaciones Devolucion'
], $delimiter); 

$sql = "
    SELECT
        m.nombre AS nombre_material,
        m.tipo AS tipo_material,
        i.nombre AS nombre_instructor,
        i.apellido AS apellido_instructor,
        pm.fecha_prestamo,
        dm.estado_devolucion,
        dm.fecha_devolucion,
        dm.observaciones AS observaciones_devolucion
    FROM prestamo_materiales pm
    JOIN materiales m ON pm.id_material = m.id_material
    JOIN instructores i ON pm.id_instructor = i.id_instructor
    LEFT JOIN devolucion_materiales dm ON pm.id_prestamo_material = dm.id_prestamo_material
    WHERE dm.observaciones IS NOT NULL AND dm.observaciones != '' -- Filtra solo las que tienen observaciones
    ORDER BY pm.fecha_prestamo DESC;
";

$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $nombre_instructor_completo = $row['nombre_instructor'] . ' ' . $row['apellido_instructor'];
        
        $fecha_prestamo_csv = date('Y-m-d', strtotime($row['fecha_prestamo']));
        $hora_prestamo_csv = date('H:i:s', strtotime($row['fecha_prestamo']));

        $fecha_devolucion_csv = '';
        $hora_devolucion_csv = '';
        if (!empty($row['fecha_devolucion'])) {
            $fecha_devolucion_csv = date('Y-m-d', strtotime($row['fecha_devolucion']));
            $hora_devolucion_csv = date('H:i:s', strtotime($row['fecha_devolucion']));
        } else {
            $fecha_devolucion_csv = 'N/A'; 
            $hora_devolucion_csv = '';
        }

        $estado_devolucion_display = empty($row['estado_devolucion']) ? 'N/A' : $row['estado_devolucion'];
        $observaciones_devolucion_display = empty($row['observaciones_devolucion']) ? 'Sin observaciones.' : $row['observaciones_devolucion'];

        fputcsv($output, [
            $row['nombre_material'],
            $row['tipo_material'],
            $nombre_instructor_completo,
            $fecha_prestamo_csv,
            $hora_prestamo_csv,
            $fecha_devolucion_csv,
            $hora_devolucion_csv,
            $estado_devolucion_display,
            $observaciones_devolucion_display
        ], $delimiter);
    }
}

fclose($output);
$conexion->close();
exit;
?>
