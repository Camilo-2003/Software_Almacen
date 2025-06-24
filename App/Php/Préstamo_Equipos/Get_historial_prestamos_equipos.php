<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    http_response_code(403);
    echo "Acceso denegado.";
    exit();
}

// Consulta del historial con estados actualizados
$sql = "
    SELECT
        pe.id_prestamo_equipo,
        ped.id_prestamo_equipo_detalle,
        CONCAT(e.marca, ' - ', e.serial) AS equipo,
        i.nombre AS nombre_instructor,
        i.apellido AS apellido_instructor,
        pe.fecha_prestamo,
        ped.fecha_vencimiento_item,
        ped.fecha_devolucion_item,
        ped.estado_item_prestamo,
        de.estado_devolucion,
        de.observaciones AS observaciones_devolucion,
        CASE 
            WHEN pe.rol_responsable = 'administrador' THEN CONCAT(a.nombres, ' ', a.apellidos)
            WHEN pe.rol_responsable = 'almacenista' THEN CONCAT(al.nombres, ' ', al.apellidos)
            ELSE 'Desconocido'
        END AS nombre_responsable,
        pe.rol_responsable
    FROM prestamo_equipos pe
    JOIN prestamo_equipos_detalle ped ON pe.id_prestamo_equipo = ped.id_prestamo_equipo
    JOIN equipos e ON ped.id_equipo = e.id_equipo
    JOIN instructores i ON pe.id_instructor = i.id_instructor
    LEFT JOIN devolucion_equipos de ON de.id_prestamo_equipo_detalle = ped.id_prestamo_equipo_detalle
    LEFT JOIN administradores a ON pe.id_responsable = a.id_administrador
    LEFT JOIN almacenistas al ON pe.id_responsable = al.id_almacenista
    ORDER BY pe.fecha_prestamo DESC, ped.id_prestamo_equipo_detalle DESC
";

$resultado = $conexion->query($sql);

if ($resultado && $resultado->num_rows > 0) {
    echo '<h2 class="txt">Historial de Préstamos de Equipos</h2>';
    echo '<div class="table-responsivee"><table>';
    echo '<thead>
        <tr>
            <th>ID Detalle</th>
            <th>Equipo</th>
            <th>Instructor</th>
            <th>Responsable</th>
            <th>Fecha Préstamo</th>
            <th>Vencimiento</th>
            <th>Devolución</th>
            <th>Estado Ítem</th>
            <!--<th>Observaciones</th>-->
        </tr>
    </thead><tbody>';

    while ($row = $resultado->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['id_prestamo_equipo_detalle']) . '</td>';
        echo '<td>' . htmlspecialchars($row['equipo']) . '</td>';
        echo '<td>' . htmlspecialchars($row['nombre_instructor'] . ' ' . $row['apellido_instructor']) . '</td>';
        echo '<td>' . htmlspecialchars($row['nombre_responsable'] . ' (' . $row['rol_responsable'] . ')') . '</td>';
        echo '<td>' . htmlspecialchars($row['fecha_prestamo']) . '</td>';
        echo '<td>' . ($row['fecha_vencimiento_item'] ?: 'N/A') . '</td>';
        echo '<td>' . ($row['fecha_devolucion_item'] ?: 'Pendiente') . '</td>';
        echo '<td>' . htmlspecialchars(str_replace('_', ' ', $row['estado_item_prestamo'])) . '</td>';
        //echo '<td>' . (!empty($row['observaciones_devolucion']) ? htmlspecialchars($row['observaciones_devolucion']) : 'Sin observaciones') . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';
} else {
    echo '<p class="txt">No hay préstamos registrados aún.</p>';
}

$conexion->close();
?>
