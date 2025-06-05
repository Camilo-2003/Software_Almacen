<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';


$sqlEquiposDisponibles = "SELECT id_equipo, marca, serial, estado FROM equipos WHERE estado = 'disponible' ORDER BY marca, serial";
$resultadoEquiposDisponibles = $conexion->query($sqlEquiposDisponibles);

?>
<h3>Equipos Actualmente Disponibles</h3>
<p>Consulta el listado de equipos que están listos para ser prestados.</p>
<?php if ($resultadoEquiposDisponibles->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID Equipo</th>
                <th>Marca</th>
                <th>Serial</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php while($fila = $resultadoEquiposDisponibles->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['id_equipo']) ?></td>
                    <td><?= htmlspecialchars($fila['marca']) ?></td>
                    <td><?= htmlspecialchars($fila['serial']) ?></td>
                    <td><?= htmlspecialchars($fila['estado']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No hay equipos disponibles para préstamo en este momento.</p>
<?php endif; ?>