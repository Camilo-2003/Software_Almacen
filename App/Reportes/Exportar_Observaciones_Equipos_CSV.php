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

$filename = 'Observaciones_Equipos_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF");

$delimiter = ';'; 

fputcsv($output, [
    'Equipo',
    'Estado Equipo',
    'Instructor',
    'Fecha Prestamo',
    'Hora Prestamo',
    'Fecha Devolucion',
    'Hora Devolucion',
    'Responsable',
    'Estado Item Prestamo',
    'Estado Devolucion', 
    'Observaciones'
], $delimiter); 

$sql = "
    SELECT
        pe.id_prestamo_equipo,
        e.marca,
        e.serial,
        e.estado AS estado_equipo,
        i.nombre AS nombre_instructor,
        i.apellido AS apellido_instructor,
        DATE_FORMAT(pe.fecha_prestamo, '%Y-%m-%d') AS fecha_prestamo_formateada,
        DATE_FORMAT(pe.fecha_prestamo, '%H:%i:%s') AS hora_prestamo_formateada,
        DATE_FORMAT(ped.fecha_devolucion_item, '%Y-%m-%d') AS fecha_devolucion_formateada,
        DATE_FORMAT(ped.fecha_devolucion_item, '%H:%i:%s') AS hora_devolucion_formateada,
        ped.estado_item_prestamo,
        pe.rol_responsable,
        CASE 
            WHEN pe.rol_responsable = 'administrador' THEN CONCAT(a.nombres, ' ', a.apellidos)
            WHEN pe.rol_responsable = 'almacenista' THEN CONCAT(al.nombres, ' ', al.apellidos)
            ELSE 'Desconocido'
        END AS nombre_responsable,
        de.estado_devolucion,
        de.observaciones
    FROM prestamo_equipos pe
    JOIN prestamo_equipos_detalle ped ON pe.id_prestamo_equipo = ped.id_prestamo_equipo
    JOIN equipos e ON ped.id_equipo = e.id_equipo
    JOIN instructores i ON pe.id_instructor = i.id_instructor
    LEFT JOIN devolucion_equipos de ON de.id_prestamo_equipo_detalle = ped.id_prestamo_equipo_detalle
    LEFT JOIN administradores a ON pe.id_responsable = a.id_administrador
    LEFT JOIN almacenistas al ON pe.id_responsable = al.id_almacenista
    WHERE de.observaciones IS NOT NULL AND de.observaciones != '' -- Filtra solo las que tienen observaciones
    ORDER BY pe.fecha_prestamo DESC
";

$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $equipo_display = $row['marca'] . ' - ' . $row['serial'];
        $nombre_instructor_completo = $row['nombre_instructor'] . ' ' . $row['apellido_instructor'];
        $nombre_responsable_display = $row['nombre_responsable'] . ' (' . $row['rol_responsable'] . ')';
        
        $fecha_prestamo_csv = $row['fecha_prestamo_formateada'];
        $hora_prestamo_csv = $row['hora_prestamo_formateada'];

        $fecha_devolucion_csv = $row['fecha_devolucion_formateada'];
        $hora_devolucion_csv = $row['hora_devolucion_formateada'];
        if (empty($row['fecha_devolucion_formateada'])) {
            $fecha_devolucion_csv = 'Pendiente'; 
            $hora_devolucion_csv = '';
        }

        $estado_item_display = str_replace('_', ' ', $row['estado_item_prestamo']);
        $estado_devolucion_display = empty($row['estado_devolucion']) ? 'N/A' : $row['estado_devolucion'];
        $observaciones_display = !empty($row['observaciones']) ? $row['observaciones'] : 'Sin observaciones';

        fputcsv($output, [
            $equipo_display,
            $row['estado_equipo'],
            $nombre_instructor_completo,
            $fecha_prestamo_csv,
            $hora_prestamo_csv,
            $fecha_devolucion_csv,
            $hora_devolucion_csv,
            $nombre_responsable_display,
            $estado_item_display,
            $estado_devolucion_display,
            $observaciones_display
        ], $delimiter);
    }
}

fclose($output);
$conexion->close();
exit;
?>
