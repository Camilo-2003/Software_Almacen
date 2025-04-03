<?php
include '../conexion.php';

$sql = "SELECT nombre, tipo, stock FROM materiales";
$result = $conexion->query($sql);

$materiales = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $materiales[] = $row;
    }
}

echo json_encode($materiales);
?>
