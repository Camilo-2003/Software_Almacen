<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once "../../ProhibirAcceso.php";

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    http_response_code(403); 
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
        echo '<h2 class="txt">Observaciones de Devolución</h2>';
        echo '<div class="table-responsivee">';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Material</th>';
        echo '<th>Tipo</th>';
        echo '<th>Instructor</th>';
        echo '<th>Fecha Préstamo</th>';
        echo '<th>Estado Devolución</th>'; 
        echo '<th>Observaciones Devolución</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        while ($row = $resultado->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['nombre_material']) . '</td>';
            echo '<td>' . htmlspecialchars($row['tipo_material']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nombre_instructor'] . ' ' . $row['apellido_instructor']) . '</td>';
            echo '<td>' . htmlspecialchars($row['fecha_prestamo']) . '</td>';
            echo '<td>' . (empty($row['estado_devolucion']) ? 'N/A' : htmlspecialchars($row['estado_devolucion'])) . '</td>'; 
            echo '<td>' . (empty($row['observaciones_devolucion']) ? 'N/A' : htmlspecialchars($row['observaciones_devolucion'])) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>'; 
    } else {
        echo '<p style="text-align: center; color: var(--neutral-text-medium); padding: 20px;">No hay historial de préstamos de materiales para mostrar.</p>';
    }
} else {
    echo '<p style="text-align: center; color: #dc3545; padding: 20px;">Error al cargar el historial de préstamos: ' . htmlspecialchars($conexion->error) . '</p>';
}
$conexion->close();
?>