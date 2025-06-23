<?php
include '../Conexion.php';

$sql = "SELECT marca, serial, estado FROM equipos ORDER BY marca ASC";
$result = $conexion->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['marca']}</td>
                <td>{$row['serial']}</td>
                <td>{$row['estado']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='3'>No hay equipos registrados</td></tr>";
}

$conexion->close();
?>
