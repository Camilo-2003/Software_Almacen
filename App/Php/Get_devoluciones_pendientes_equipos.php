<?php
// Incluye la conexión a la base de datos
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

// Consulta para obtener préstamos pendientes
$sql = "SELECT 
            pe.id_prestamo_equipo,
            pe.estado,
            e.marca AS equipo_marca,
            e.serial AS equipo_serial,
            CONCAT(i.nombre, ' ', i.apellido) AS instructor_nombre,
            pe.fecha_prestamo
        FROM prestamo_equipos pe
        JOIN equipos e ON pe.id_equipo = e.id_equipo
        JOIN instructores i ON pe.id_instructor = i.id_instructor
        WHERE pe.estado = 'pendiente'
        ORDER BY pe.fecha_prestamo ASC";

$resultado = $conexion->query($sql);

if ($resultado && $resultado->num_rows > 0) {
    echo "<h2 class='txt'>Devoluciones Pendientes</h2>";
    echo "<table>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>ID Préstamo</th>";
    echo "<th>Equipo (Marca - Serial)</th>";
    echo "<th>Instructor</th>";
    echo "<th>Fecha Préstamo</th>";
    echo "<th>Estado</th>";
    echo "<th>Acciones</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($fila['id_prestamo_equipo']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['equipo_marca'] . ' - ' . $fila['equipo_serial']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['instructor_nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['fecha_prestamo']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['estado']) . "</td>";
        echo "<td>";
        // Cambiamos el botón para que sea un JS click que abra un modal
        echo "<button class='btn-devolver-small open-devolver-modal' 
                    data-id-prestamo='" . htmlspecialchars($fila['id_prestamo_equipo']) . "'
                    title='Registrar devolución'><i class='fa-solid fa-rotate'></i> Devolver</button>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
} else {
    echo "<p style='text-align: center; padding: 20px;'>No hay equipos pendientes de devolución.</p>";
}

$conexion->close();
?>