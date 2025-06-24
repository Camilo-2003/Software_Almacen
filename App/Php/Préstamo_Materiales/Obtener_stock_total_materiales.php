<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
header('Content-Type: application/json');

$sql = "SELECT SUM(stock) as total_disponible FROM materiales WHERE tipo = 'no consumible'";

$resultado = $conexion->query($sql);
$total = 0;
if ($resultado) {
    $total = $resultado->fetch_assoc()['total_disponible'] ?? 0;
}
$conexion->close();

echo json_encode(['success' => true, 'total_disponible' => (int)$total]);
?>