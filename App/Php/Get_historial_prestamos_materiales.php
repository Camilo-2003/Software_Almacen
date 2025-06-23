<?php
session_start();
// Habilitar la visualización de errores para depuración (descomentar en desarrollo, comentar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

// Redireccionar si el usuario no tiene los roles permitidos
if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    http_response_code(403); // Forbidden
    echo "Acceso denegado.";
    exit();
}

$sql = "
    SELECT
        pm.id_prestamo_material,
        m.nombre AS nombre_material,
        m.tipo AS tipo_material,
        pm.cantidad,
        i.nombre AS nombre_instructor,
        i.apellido AS apellido_instructor,
        pm.fecha_prestamo,
        pm.fecha_devolucion,
        pm.estado,
        pm.id_responsable,
        pm.rol_responsable,
        dm.estado_devolucion, -- <--- RE-INCLUDED THIS LINE
        dm.observaciones AS observaciones_devolucion
    FROM prestamo_materiales pm
    JOIN materiales m ON pm.id_material = m.id_material
    JOIN instructores i ON pm.id_instructor = i.id_instructor
    LEFT JOIN devolucion_materiales dm ON pm.id_prestamo_material = dm.id_prestamo_material
    ORDER BY pm.fecha_prestamo DESC;
";

$resultado = $conexion->query($sql);

if ($resultado) {
    if ($resultado->num_rows > 0) {
        echo '<h2 class="txt">Historial Completo de Préstamos de Materiales</h2>';
        echo '<div class="table-responsive">';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID</th>';
        echo '<th>Material</th>';
        echo '<th>Tipo</th>';
        echo '<th>Cantidad</th>';
        echo '<th>Instructor</th>';
        echo '<th>Fecha Préstamo</th>';
        echo '<th>Fecha Devolución</th>';
        echo '<th>Estado Préstamo</th>';
        echo '<th>Responsable</th>';
        echo '<th>Estado Devolución</th>'; 
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        while ($row = $resultado->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['id_prestamo_material']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nombre_material']) . '</td>';
            echo '<td>' . htmlspecialchars($row['tipo_material']) . '</td>';
            echo '<td>' . htmlspecialchars($row['cantidad']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nombre_instructor'] . ' ' . $row['apellido_instructor']) . '</td>';
            echo '<td>' . htmlspecialchars($row['fecha_prestamo']) . '</td>';
            echo '<td>' . (empty($row['fecha_devolucion']) ? 'N/A' : htmlspecialchars($row['fecha_devolucion'])) . '</td>';
            echo '<td>' . htmlspecialchars($row['estado']) . '</td>';
            echo '<td>' . htmlspecialchars($row['rol_responsable'] . ' (ID: ' . $row['id_responsable'] . ')') . '</td>';
            echo '<td>' . (empty($row['estado_devolucion']) ? 'N/A' : htmlspecialchars($row['estado_devolucion'])) . '</td>'; 
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>'; // End table-responsive
    } else {
        echo '<p style="text-align: center; color: var(--neutral-text-medium); padding: 20px;">No hay historial de préstamos de materiales para mostrar.</p>';
    }
} else {
    echo '<p style="text-align: center; color: #dc3545; padding: 20px;">Error al cargar el historial de préstamos: ' . htmlspecialchars($conexion->error) . '</p>';
}

$conexion->close();
?>