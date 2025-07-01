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

$filename = 'Inventario_Equipos_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF");

$delimiter = ';'; 

$headers = [
    'ID',
    'Marca',
    'Serial',
    'Estado'
];
array_walk($headers, function(&$value) {
    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
});

fputcsv($output, $headers, $delimiter); 

$sql = "SELECT id_equipo, marca, serial, estado FROM equipos ORDER BY marca, serial"; 
$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        fputcsv($output, [
            $row['id_equipo'],
            $row['marca'],
            $row['serial'],
            $row['estado']
        ], $delimiter);
    }
}
fclose($output);
$conexion->close();
exit;
?>
