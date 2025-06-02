<?php

require_once __DIR__ . '/Conexion.php';
$sql = "SELECT * FROM materiales";
$result = $conexion->query($sql);

$materiales = array();

while ($row = $result->fetch_assoc()) {
    $materiales[] = $row;
}

echo json_encode($materiales);
?> 
