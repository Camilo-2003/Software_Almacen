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
        pm.id_prestamo_material,
        m.nombre AS nombre_material,
        m.tipo AS tipo_material,
        pm.cantidad,
        i.nombre AS nombre_instructor,
        i.apellido AS apellido_instructor,
        pm.fecha_prestamo, -- Conservamos el DATETIME completo para formatear en PHP
        pm.estado,
        pm.id_responsable,
        pm.rol_responsable,
        pm.responsable,
        dm.estado_devolucion,
        dm.fecha_devolucion, -- Conservamos el DATETIME completo para formatear en PHP
        dm.observaciones AS observaciones_devolucion
    FROM prestamo_materiales pm
    JOIN materiales m ON pm.id_material = m.id_material
    JOIN instructores i ON pm.id_instructor = i.id_instructor
    LEFT JOIN devolucion_materiales dm ON pm.id_prestamo_material = dm.id_prestamo_material
    ORDER BY pm.fecha_prestamo DESC;
";

$resultado = $conexion->query($sql);

$prestamos_data = [];
if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $prestamos_data[] = $row;
    }
}
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Préstamos de Materiales</title>
    <link rel="icon" href="../Img/logo_sena.png" type="image/x-icon">
    <style>
        body { font-family: sans-serif; font-size: 9px; margin: 20px;}
        h1 { text-align: center; color: #003366; font-size: 18px; margin-bottom: 5px; }
        .generation-date { text-align: center; font-size: 10px; color: #555; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background-color: #003366; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .no-records { text-align: center; color: #666; margin-top: 20px; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: black; }
    </style>
</head>
<body>
    <h1>Historial de Préstamos de Materiales | SENA</h1>
    <p class="generation-date">Fecha de Generación: ' . date('d/m/Y h:i:s A') . '</p>
';

if (!empty($prestamos_data)) {
    $html .= '
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Material</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Instructor</th>
                <th>Fecha Préstamo</th>
                <th>Fecha Devolución</th>
                <th>Estado Préstamo</th>
                <th>Responsable</th>
                <th>Estado Devolución</th>
            </tr>
        </thead>
        <tbody>';
    foreach ($prestamos_data as $row) {
        $nombre_instructor_completo = htmlspecialchars($row['nombre_instructor'] . ' ' . $row['apellido_instructor']);
        $responsable_completo = htmlspecialchars($row['rol_responsable'] . ' (ID: ' . $row['id_responsable'] . ') - ' . $row['responsable']);
        
        $fecha_prestamo_formateada = htmlspecialchars(date('d/m/Y h:i:s A', strtotime($row['fecha_prestamo'])));

        $fecha_devolucion_formateada = 'Pendiente';
        if (!empty($row['fecha_devolucion'])) {
            $fecha_devolucion_formateada = htmlspecialchars(date('d/m/Y H:i:s', strtotime($row['fecha_devolucion'])));
        }
        
        $estado_devolucion_display = empty($row['estado_devolucion']) ? 'N/A' : htmlspecialchars($row['estado_devolucion']);

        $html .= '
            <tr>
                <td>' . htmlspecialchars($row['id_prestamo_material']) . '</td>
                <td>' . htmlspecialchars($row['nombre_material']) . '</td>
                <td>' . htmlspecialchars($row['tipo_material']) . '</td>
                <td>' . htmlspecialchars($row['cantidad']) . '</td>
                <td>' . $nombre_instructor_completo . '</td>
                <td>' . $fecha_prestamo_formateada . '</td>
                <td>' . $fecha_devolucion_formateada . '</td>
                <td>' . htmlspecialchars($row['estado']) . '</td>
                <td>' . $responsable_completo . '</td>
                <td>' . $estado_devolucion_display . '</td>
            </tr>';
    }
    $html .= '
        </tbody>
    </table>';
} else {
    $html .= '<p class="no-records">No hay registros de préstamos para mostrar.</p>';
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

$filename_pdf = 'Historial_Prestamos_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename_pdf, array("Attachment" => true));

$conexion->close();
exit;
?>