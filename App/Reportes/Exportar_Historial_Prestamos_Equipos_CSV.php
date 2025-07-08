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
$filename = 'Historial_Prestamos_Equipos_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF");

$delimiter = ';'; 

fputcsv($output, [
    'ID Detalle',
    'Equipo',
    'Instructor',
    'Responsable',
    'Fecha Prestamo',
    'Hora Prestamo',
    'Fecha Vencimiento',
    'Hora Vencimiento',
    'Fecha Devolucion',
    'Hora Devolucion',
    'Estado Item Prestamo',
    'Estado Devolucion',
    'Observaciones Devolucion'
], $delimiter); 

$sql = "
    SELECT
        pe.id_prestamo_equipo,
        ped.id_prestamo_equipo_detalle,
        CONCAT(e.marca, ' - ', e.serial) AS equipo,
        i.nombre AS nombre_instructor,
        i.apellido AS apellido_instructor,
        DATE_FORMAT(pe.fecha_prestamo, '%Y-%m-%d') AS fecha_prestamo_formateada,
        DATE_FORMAT(pe.fecha_prestamo, '%H:%i:%s') AS hora_prestamo_formateada,
        DATE_FORMAT(ped.fecha_vencimiento_item, '%Y-%m-%d') AS fecha_vencimiento_formateada,
        DATE_FORMAT(ped.fecha_vencimiento_item, '%H:%i:%s') AS hora_vencimiento_formateada,
        DATE_FORMAT(ped.fecha_devolucion_item, '%Y-%m-%d') AS fecha_devolucion_formateada,
        DATE_FORMAT(ped.fecha_devolucion_item, '%H:%i:%s') AS hora_devolucion_formateada,
        ped.estado_item_prestamo,
        de.estado_devolucion,
        de.observaciones AS observaciones_devolucion,
        CASE 
            WHEN pe.rol_responsable = 'administrador' THEN CONCAT(a.nombres, ' ', a.apellidos)
            WHEN pe.rol_responsable = 'almacenista' THEN CONCAT(al.nombres, ' ', al.apellidos)
            ELSE 'Desconocido'
        END AS nombre_responsable,
        pe.rol_responsable
    FROM prestamo_equipos pe
    JOIN prestamo_equipos_detalle ped ON pe.id_prestamo_equipo = ped.id_prestamo_equipo
    JOIN equipos e ON ped.id_equipo = e.id_equipo
    JOIN instructores i ON pe.id_instructor = i.id_instructor
    LEFT JOIN devolucion_equipos de ON de.id_prestamo_equipo_detalle = ped.id_prestamo_equipo_detalle
    LEFT JOIN administradores a ON pe.id_responsable = a.id_administrador
    LEFT JOIN almacenistas al ON pe.id_responsable = al.id_almacenista
    ORDER BY pe.fecha_prestamo DESC, ped.id_prestamo_equipo_detalle DESC
";

$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $nombre_instructor_completo = $row['nombre_instructor'] . ' ' . $row['apellido_instructor'];
        $nombre_responsable_display = $row['nombre_responsable'] . ' (' . $row['rol_responsable'] . ')';

        $fecha_vencimiento_csv = $row['fecha_vencimiento_formateada'] ?: 'N/A';
        $hora_vencimiento_csv = $row['hora_vencimiento_formateada'] ?: '';

        $fecha_devolucion_csv = $row['fecha_devolucion_formateada'] ?: 'Pendiente';
        $hora_devolucion_csv = $row['hora_devolucion_formateada'] ?: '';

        $estado_devolucion_display = $row['estado_devolucion'] ?: 'N/A';
        $observaciones_devolucion_display = !empty($row['observaciones_devolucion']) ? $row['observaciones_devolucion'] : 'Sin observaciones';

        fputcsv($output, [
            $row['id_prestamo_equipo_detalle'],
            $row['equipo'],
            $nombre_instructor_completo,
            $nombre_responsable_display,
            $row['fecha_prestamo_formateada'],
            $row['hora_prestamo_formateada'],
            $fecha_vencimiento_csv,
            $hora_vencimiento_csv,
            $fecha_devolucion_csv,
            $hora_devolucion_csv,
            str_replace('_', ' ', $row['estado_item_prestamo']),
            $estado_devolucion_display,
            $observaciones_devolucion_display
        ], $delimiter);
    }
}
fclose($output);
$conexion->close();
exit;
?>
