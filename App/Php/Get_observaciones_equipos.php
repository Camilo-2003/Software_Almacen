<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    http_response_code(403);
    echo "Acceso denegado.";
    exit();
}
$sql = "SELECT
        pe.id_prestamo_equipo,
        e.marca AS marca_equipo,
        e.estado AS estado_equipo,
        i.nombre AS nombre_instructor,
        i.apellido AS apellido_instructor,
        pe.fecha_prestamo,
        pe.fecha_devolucion,
        pe.estado,
        pe.id_responsable,
        pe.rol_responsable,
        de.estado_devolucion, 
        de.observaciones AS observaciones_devolucion
    FROM prestamo_equipos pe
    JOIN equipos e ON pe.id_equipo = e.id_equipo
    JOIN instructores i ON pe.id_instructor = i.id_instructor
    LEFT JOIN devolucion_equipos de ON pe.id_prestamo_equipo = de.id_prestamo_equipo
    ORDER BY pe.fecha_prestamo DESC;
";
$resultado = $conexion->query($sql);

if ($resultado) {
    if ($resultado->num_rows > 0) {
        echo '<h2 class="txt">Observaciones de Equipos</h2>';
        echo '<div class="table-responsivee">';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Equipo</th>';
        echo '<th>Estado</th>';
        echo '<th>Instructor</th>';
        echo '<th>Fecha Préstamo</th>';
        echo '<th>Responsable</th>';
        echo '<th>Fecha Devolución</th>';
        echo '<th>Estado Devolución</th>';
        echo '<th>Observaciones Devolución</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        while ($row = $resultado->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['marca_equipo']) . '</td>';
            echo '<td>' . htmlspecialchars($row['estado_equipo']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nombre_instructor'] . ' ' . $row['apellido_instructor']) . '</td>';
            echo '<td>' . htmlspecialchars($row['fecha_prestamo']) . '</td>';
            echo '<td>' . (empty($row['rol_responsable']) ? 'N/A' : htmlspecialchars($row['rol_responsable'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['fecha_devolucion']) . '</td>'; 
            echo '<td>' . (empty($row['estado_devolucion']) . htmlspecialchars($row['estado_devolucion'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['observaciones_devolucion']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<p style="text-align: center; color: var(--neutral-text-medium); padding: 20px;">No hay historial de observaciones de equipos para mostrar.</p>';
    }
} else {
    echo '<p style="text-align: center; color: #dc3545; padding: 20px;">Error al cargar el historial de observaciones de equipos: ' . htmlspecialchars($conexion->error) . '</p>';
}
$conexion->close();
?>