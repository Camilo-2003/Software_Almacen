<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Bogota');

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

session_start();

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: ../Error.php");
    exit();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/vendor/autoload.php'; 

use Dompdf\Dompdf;
use Dompdf\Options;

$sql = "SELECT 
            DATE(hs.hora_ingreso) AS fecha_ingreso_solo_fecha,
            TIME(hs.hora_ingreso) AS hora_ingreso_solo_hora,
            DATE(hs.hora_salida) AS fecha_salida_solo_fecha,
            TIME(hs.hora_salida) AS hora_salida_solo_hora,
            hs.tipo_usuario,
            CASE 
                WHEN hs.tipo_usuario = 'almacenista' THEN a.nombres
                WHEN hs.tipo_usuario = 'administrador' THEN ad.nombres
                ELSE 'Usuario Desconocido'
            END AS nombres_usuario,
            CASE 
                WHEN hs.tipo_usuario = 'almacenista' THEN a.apellidos
                WHEN hs.tipo_usuario = 'administrador' THEN ad.apellidos
                ELSE ''
            END AS apellidos_usuario
        FROM historial_sesiones hs
        LEFT JOIN almacenistas a ON hs.id_usuario = a.id_almacenista AND hs.tipo_usuario = 'almacenista'
        LEFT JOIN administradores ad ON hs.id_usuario = ad.id_administrador AND hs.tipo_usuario = 'administrador'
        ORDER BY hs.hora_ingreso DESC";

$resultado = $conexion->query($sql);

$historial_por_fecha = [];
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $fecha_grupo = $fila['fecha_ingreso_solo_fecha']; 
        if (!isset($historial_por_fecha[$fecha_grupo])) {
            $historial_por_fecha[$fecha_grupo] = [];
        }
        $historial_por_fecha[$fecha_grupo][] = $fila;
    }
}
krsort($historial_por_fecha); 

$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Sesiones</title>
    <link rel="icon" href="../Img/logo_sena.png" type="image/x-icon">
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        h1 { text-align: center; color: #003366; font-size: 18px; }
        h3 { color: #1c8b4e; font-size: 14px; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #003366; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .no-records { text-align: center; color: #666; margin-top: 20px; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: black; }
    </style>
</head>
<body>
    <h1>Historial de Sesiones del Sistema Almacen | SENA</h1>
    <p>Fecha de Generación: ' . date('d/m/Y h:i:s A') . '</p>
';

if (!empty($historial_por_fecha)) {
    foreach ($historial_por_fecha as $fecha => $sesiones_del_dia) {
        $html .= '
        <h3>Fecha: ' . htmlspecialchars(date('d/m/Y', strtotime($fecha))) . '</h3>
        <p>Total de Sesiones Registradas: <b>' . count($sesiones_del_dia) . '</b></p>
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Tipo Usuario</th>
                    <th>Hora de Ingreso</th>
                    <th>Hora de Salida</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>';
        foreach ($sesiones_del_dia as $fila) {
            $html .= '
                <tr>
                    <td>' . htmlspecialchars($fila['nombres_usuario'] . ' ' . $fila['apellidos_usuario']) . '</td>
                    <td>' . htmlspecialchars(ucfirst($fila['tipo_usuario'])) . '</td>
                    <td>' . htmlspecialchars($fila['hora_ingreso_solo_hora']) . '</td>
                    <td>' . htmlspecialchars($fila['hora_salida_solo_hora'] ?? 'Activo') . '</td>
                    <td>' . htmlspecialchars(date('d/m/Y', strtotime($fecha))) . '</td>
                </tr>';
        }
        $html .= '
            </tbody>
        </table>';
    }
} else {
    $html .= '<p class="no-records">No hay registros de sesiones para mostrar.</p>';
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

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$filename_pdf = 'Historial_Sesiones_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename_pdf, array("Attachment" => true)); 
$conexion->close();
exit;
?>