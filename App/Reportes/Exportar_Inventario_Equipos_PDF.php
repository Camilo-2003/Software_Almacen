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

$sql = "SELECT id_equipo, marca, serial, estado FROM equipos ORDER BY marca, serial"; 
$resultado = $conexion->query($sql);

$equipos_data = [];
if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $equipos_data[] = $row;
    }
}
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de Equipos</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; margin: 20px;}
        h1 { text-align: center; color: #003366; font-size: 18px; margin-top: -25px; }
        .generation-date { text-align: center; font-size: 10px; color: #555; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; vertical-align: top;}
        th { background-color: #003366; color: white; font-weight: bold; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .no-records { text-align: center; color: #666; margin-top: 20px; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: black; }
    </style>
</head>
<body>
    <h1>Reporte de Inventario de Equipos | SENA</h1>
    <p class="generation-date">Fecha de Generación: ' . date('d/m/Y h:i:s A') . '</p>
';

if (!empty($equipos_data)) {
    $html .= '
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Marca</th>
                <th>Serial</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>';
    foreach ($equipos_data as $row) {
        $html .= '
            <tr>
                <td>' . htmlspecialchars($row['id_equipo']) . '</td>
                <td>' . htmlspecialchars($row['marca']) . '</td>
                <td>' . htmlspecialchars($row['serial']) . '</td>
                <td>' . htmlspecialchars($row['estado']) . '</td>
            </tr>';
    }
    $html .= '
        </tbody>
    </table>';
} else {
    $html .= '<p class="no-records">No hay equipos en el inventario para mostrar.</p>';
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

$filename_pdf = 'Inventario_Equipos_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename_pdf, array("Attachment" => true));
$conexion->close();
exit;
?>
