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

$filename = 'Inventario_Materiales_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF");

$delimiter = ';'; 

$headers = [
    'ID',
    'Nombre',
    'Tipo',
    'Cantidad',
    'Estado'
];
array_walk($headers, function(&$value) {
    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
});

fputcsv($output, $headers, $delimiter); 

$sql = "SELECT id_material, nombre, tipo, stock, estado_material FROM materiales ORDER BY nombre"; 
$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        fputcsv($output, [
            $row['id_material'],
            $row['nombre'],
            $row['tipo'],
            $row['stock'],
            $row['estado_material']
        ], $delimiter);
    }
}

fclose($output);
$conexion->close();
exit;
?>
