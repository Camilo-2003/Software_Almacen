<?php
$conexion = new mysqli("localhost", "root", "1000191455mm.", "almacen");

if ($conexion->connect_errno) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}

// Mostrar historial de materiales
$sqlM = "SELECT * FROM prestamo_materiales ORDER BY id_prestamo_material DESC";
$resultM = $conexion->query($sqlM);

// 👇 Agrega esta verificación 👇
if (!$resultM) {
    die("❌ Error en la consulta de materiales: " . $conexion->error);
}

echo "<h2>Historial de Préstamos de Materiales</h2>";
echo "<table border='1'>";
echo "<tr><th>Tipo</th><th>Material</th><th>Cantidad</th><th>Instructor</th><th>Fecha</th><th>Hora</th><th>Estado</th></tr>";
while ($row = $resultM->fetch_assoc()) {
    echo "<tr>
            <td>Consumible</td>
            <td>{$row['material']}</td>
            <td>{$row['cantidad']}</td>
            <td>{$row['instructor']}</td>
            <td>{$row['fecha']}</td>
            <td>{$row['hora']}</td>
            <td style='color:red;'>{$row['estado']}</td>
          </tr>";
}
echo "</table>";

// Mostrar historial de equipos
$sqlE = "SELECT * FROM prestamo_equipos ORDER BY id_prestamo_equipo DESC";
$resultE = $conexion->query($sqlE);

if (!$resultE) {
    die("❌ Error en la consulta de equipos: " . $conexion->error);
}

echo "<h2>Historial de Préstamos de Equipos</h2>";
echo "<table border='1'>";
echo "<tr><th>Tipo</th><th>Equipo</th><th>Cantidad</th><th>Instructor</th><th>Fecha</th><th>Hora</th><th>Estado</th></tr>";
while ($row = $resultE->fetch_assoc()) {
    echo "<tr>
            <td>No Consumible</td>
            <td>{$row['equipo']}</td>
            <td>{$row['cantidad']}</td>
            <td>{$row['instructor']}</td>
            <td>{$row['fecha']}</td>
            <td>{$row['hora']}</td>
            <td style='color:red;'>{$row['estado']}</td>
          </tr>";
}
echo "</table>";
?>
