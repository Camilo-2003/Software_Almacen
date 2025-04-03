<?php
include '../conexion.php';
$sql = "SELECT nombre, tipo, stock FROM materiales ORDER BY nombre ASC";
$result = $conexion->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['nombre']}</td>
                <td>{$row['tipo']}</td>
                <td>{$row['stock']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='3'>No hay materiales registrados</td></tr>";
}

$conexion->close();
?>
