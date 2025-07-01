<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}

// Consulta ajustada con devoluciones correctas
$sql = "
    SELECT
        pe.id_prestamo_equipo,
        e.marca,
        e.serial,
        e.estado AS estado_equipo,
        i.nombre AS nombre_instructor,
        i.apellido AS apellido_instructor,
        pe.fecha_prestamo,
        ped.fecha_devolucion_item,
        ped.estado_item_prestamo,
        pe.rol_responsable,
        CASE 
            WHEN pe.rol_responsable = 'administrador' THEN CONCAT(a.nombres, ' ', a.apellidos)
            WHEN pe.rol_responsable = 'almacenista' THEN CONCAT(al.nombres, ' ', al.apellidos)
            ELSE 'Desconocido'
        END AS nombre_responsable,
        de.estado_devolucion,
        de.observaciones
    FROM prestamo_equipos pe
    JOIN prestamo_equipos_detalle ped ON pe.id_prestamo_equipo = ped.id_prestamo_equipo
    JOIN equipos e ON ped.id_equipo = e.id_equipo
    JOIN instructores i ON pe.id_instructor = i.id_instructor
    LEFT JOIN devolucion_equipos de ON de.id_prestamo_equipo_detalle = ped.id_prestamo_equipo_detalle
    LEFT JOIN administradores a ON pe.id_responsable = a.id_administrador
    LEFT JOIN almacenistas al ON pe.id_responsable = al.id_almacenista
    ORDER BY pe.fecha_prestamo DESC
";

$resultado = $conexion->query($sql);

if ($resultado) {
    if ($resultado->num_rows > 0) {
        echo '
        <h2 class="txt">Historial de Observaciones de Equipos
         <a href="Reportes/Exportar_Historial_Prestamos_Equipos_CSV.php" class="export-button-excel" title="EXPORTAR A EXCEL">
        Exportar a Excel <i class="fas fa-file-excel"></i> 
        </a>
        <a href="Reportes/Exportar_Historial_Prestamos_Equipos_PDF.php" class="export-button-pdf" title="PDF">
        Exportar a PDF<i class="fas fa-file-pdf"></i>
        </a>
        </h2>';
        echo '<div class="table-responsivee">';
        echo '<table>';
        echo '<thead>
                <tr>
                    <th>Equipo</th>
                    <th>Estado</th>
                    <th>Instructor</th>
                    <th>Fecha Préstamo</th>
                    <th>Fecha Devolución</th>
                    <th>Responsable</th>
                    <th>Estado Ítem</th>
                    <!--<th>Estado Devolución</th>-->
                    <th>Observaciones</th>
                </tr>
              </thead>';
        echo '<tbody>';
        while ($row = $resultado->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['marca'] . ' - ' . $row['serial']) . '</td>';
            echo '<td>' . htmlspecialchars($row['estado_equipo']) . '</td>';
            echo '<td>' . htmlspecialchars($row['nombre_instructor'] . ' ' . $row['apellido_instructor']) . '</td>';
            echo '<td>' . htmlspecialchars($row['fecha_prestamo']) . '</td>';
            echo '<td>' . (!empty($row['fecha_devolucion_item']) ? htmlspecialchars($row['fecha_devolucion_item']) : 'Pendiente') . '</td>';
            echo '<td>' . htmlspecialchars($row['nombre_responsable'] . ' (' . $row['rol_responsable'] . ')') . '</td>';
            echo '<td>' . htmlspecialchars(str_replace('_', ' ', $row['estado_item_prestamo'])) . '</td>';
           //echo '<td>' . (!empty($row['estado_devolucion']) ? htmlspecialchars($row['estado_devolucion']) : 'Pendiente') . '</td>';
            echo '<td>' . (!empty($row['observaciones']) ? htmlspecialchars($row['observaciones']) : 'N/A') . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    } else {
        echo '<p style="text-align: center; color: var(--neutral-text-medium); padding: 20px;">No hay observaciones registradas en los préstamos de equipos.</p>';
    }
} else {
    echo '<p style="text-align: center; color: #dc3545; padding: 20px;">Error al cargar los datos: ' . htmlspecialchars($conexion->error) . '</p>';
}

$conexion->close();
?>
