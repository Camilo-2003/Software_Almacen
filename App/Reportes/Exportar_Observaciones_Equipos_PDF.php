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

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$sql = "
    SELECT
        pe.id_prestamo_equipo,
        e.marca,
        e.serial,
        e.estado AS estado_equipo,
        i.nombre AS nombre_instructor,
        i.apellido AS apellido_instructor,
        pe.fecha_prestamo,
        ped.fecha_devolucion_item,
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

$observaciones_equipos_data = [];
if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $observaciones_equipos_data[] = $row;
    }
}

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Observaciones de Equipos</title>
    <style>
        body { font-family: sans-serif; font-size: 8px; margin: 20px;} /* Fuente más pequeña para más columnas */
        h1 { text-align: center; color: #003366; font-size: 18px; margin-bottom: 5px; }
        .generation-date { text-align: center; font-size: 10px; color: #555; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 4px; text-align: left; vertical-align: top;} /* Padding más pequeño */
        th { background-color: #003366; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .no-records { text-align: center; color: #666; margin-top: 20px; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: black; }
    </style>
</head>
<body>
    <h1>Reporte de Observaciones de Equipos | SENA</h1>
    <p class="generation-date">Fecha de Generación: ' . date('d/m/Y h:i:s A') . '</p>
';

if (!empty($observaciones_equipos_data)) {
    $html .= '
    <table>
        <thead>
            <tr>
                <th>Equipo</th>
                <th>Estado Equipo</th>
                <th>Instructor</th>
                <th>Fecha Préstamo</th>
                <th>Fecha Devolución</th>
                <th>Responsable</th>
                <th>Estado Ítem</th>
                <th>Estado Devolución</th> <!-- Se incluye en PDF para claridad aunque no en la tabla original -->
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>';
    foreach ($observaciones_equipos_data as $row) {
        $equipo_display = htmlspecialchars($row['marca'] . ' - ' . $row['serial']);
        $nombre_instructor_completo = htmlspecialchars($row['nombre_instructor'] . ' ' . $row['apellido_instructor']);
        $nombre_responsable_display = htmlspecialchars($row['nombre_responsable'] . ' (' . $row['rol_responsable'] . ')');

        $fecha_prestamo_formateada = htmlspecialchars(date('d/m/Y H:i:s', strtotime($row['fecha_prestamo'])));
        
        $fecha_devolucion_formateada = 'Pendiente';
        if (!empty($row['fecha_devolucion_item'])) {
            $fecha_devolucion_formateada = htmlspecialchars(date('d/m/Y H:i:s', strtotime($row['fecha_devolucion_item'])));
        }
        
        $estado_item_display = htmlspecialchars(str_replace('_', ' ', $row['estado_item_prestamo']));
        $estado_devolucion_display = empty($row['estado_devolucion']) ? 'N/A' : htmlspecialchars($row['estado_devolucion']);
        $observaciones_display = !empty($row['observaciones']) ? htmlspecialchars($row['observaciones']) : 'Sin observaciones';

        $html .= '
            <tr>
                <td>' . $equipo_display . '</td>
                <td>' . htmlspecialchars($row['estado_equipo']) . '</td>
                <td>' . $nombre_instructor_completo . '</td>
                <td>' . $fecha_prestamo_formateada . '</td>
                <td>' . $fecha_devolucion_formateada . '</td>
                <td>' . $nombre_responsable_display . '</td>
                <td>' . $estado_item_display . '</td>
                <td>' . $estado_devolucion_display . '</td>
                <td>' . $observaciones_display . '</td>
            </tr>';
    }
    $html .= '
        </tbody>
    </table>';
} else {
    $html .= '<p class="no-records">No hay registros de observaciones de equipos para mostrar.</p>';
}

$html .= '
    <div class="footer">
        Reporte generado por el Sistema de Gestión de Almacén del SENA.
    </div>
</body>
</html>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false); 

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); 
$dompdf->render();

$filename_pdf = 'Observaciones_Equipos_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename_pdf, array("Attachment" => true));

$conexion->close();
exit;
?>
