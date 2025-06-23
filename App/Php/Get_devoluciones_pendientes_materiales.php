<?php
session_start();

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
        m.tipo AS tipo_material, -- <--- THIS IS CRUCIAL
        pm.cantidad,
        i.nombre AS nombre_instructor,
        i.apellido AS apellido_instructor,
        pm.fecha_prestamo,
        pm.id_responsable,
        pm.rol_responsable
    FROM prestamo_materiales pm
    JOIN materiales m ON pm.id_material = m.id_material
    JOIN instructores i ON pm.id_instructor = i.id_instructor
    WHERE pm.estado = 'pendiente'
    AND m.tipo = 'no consumible' -- Ensure this still has the space
    ORDER BY pm.fecha_prestamo ASC;
";
$resultado = $conexion->query($sql);

if ($resultado) {
    if ($resultado->num_rows > 0) {
        echo '<h2 class="txt">Devoluciones Pendientes de Materiales</h2>';
        echo '<div class="table-responsive">';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID Préstamo</th>';
        echo '<th>Material</th>';
        echo '<th>Tipo</th>';
        echo '<th>Cantidad</th>';
        echo '<th>Instructor</th>';
        echo '<th>Fecha Préstamo</th>';
        echo '<th>Responsable</th>';
        echo '<th>Acciones</th>';
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
            echo '<td>' . htmlspecialchars($row['rol_responsable']) . ' (ID: ' . htmlspecialchars($row['id_responsable']) . ')</td>';
            echo '<td>';
            echo '<button id="devolver" class="open-devolver-modal" data-id-prestamo="' . htmlspecialchars($row['id_prestamo_material']) . '"><i class="fa-solid fa-rotate"></i> Devolver</button>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>'; // End table-responsive
    } else {
        echo '<p style="text-align: center; color: var(--neutral-text-medium); padding: 20px;">No hay devoluciones de materiales pendientes.</p>';
    }
} else {
    echo '<p style="text-align: center; color: #dc3545; padding: 20px;">Error al cargar las devoluciones pendientes: ' . htmlspecialchars($conexion->error) . '</p>';
}

$conexion->close();
?>