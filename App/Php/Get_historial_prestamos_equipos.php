<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

$sqlHistorialPrestamos = "SELECT
    pe.id_prestamo_equipo,
    CONCAT(e.marca, ' - ', e.serial) AS nombre_equipo,
    CONCAT(i.nombre, ' ', i.apellido) AS nombre_instructor,
    COALESCE(
        CONCAT(a.nombres, ' ', a.apellidos), 
        CONCAT(adm.nombres, ' ', adm.apellidos) 
    ) AS nombre_responsable, 
    pe.fecha_prestamo,
    pe.fecha_devolucion,
    pe.estado
FROM prestamo_equipos pe
INNER JOIN equipos e ON pe.id_equipo = e.id_equipo
INNER JOIN instructores i ON pe.id_instructor = i.id_instructor
LEFT JOIN almacenistas a ON pe.id_responsable = a.id_almacenista AND pe.rol_responsable = 'almacenista'
LEFT JOIN administradores adm ON pe.id_responsable = adm.id_administrador AND pe.rol_responsable = 'administrador'
ORDER BY pe.fecha_prestamo DESC";

$resultadoHistorialPrestamos = $conexion->query($sqlHistorialPrestamos);

if (!$resultadoHistorialPrestamos) {
    die("Error en la consulta: " . $conexion->error);
}

?>
<h2 class='txt'>Historial Completo de Préstamos</h2>
<p>Revisa todos los préstamos realizados, incluyendo los ya devueltos.</p>
<?php if ($resultadoHistorialPrestamos->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID Préstamo</th>
                <th>Equipo (Marca - Serial)</th>
                <th>Instructor</th>
                <th>Responsable</th> <th>Fecha de Préstamo</th>
                <th>Fecha de Devolución</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php while($fila = $resultadoHistorialPrestamos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['id_prestamo_equipo']) ?></td>
                    <td><?= htmlspecialchars($fila['nombre_equipo']) ?></td>
                    <td><?= htmlspecialchars($fila['nombre_instructor']) ?></td>
                    <td><?= htmlspecialchars($fila['nombre_responsable']) ?></td> <td><?= htmlspecialchars($fila['fecha_prestamo']) ?></td>
                    <td><?= htmlspecialchars($fila['fecha_devolucion'] ?? 'Pendiente') ?></td>
                    <td><?= htmlspecialchars($fila['estado']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No hay préstamos de equipos registrados.</p>
<?php endif; ?>