<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}

if (!isset($conexion) || $conexion->connect_error) {
    echo "<p style='text-align: center; color: #dc3545; padding: 20px;'>Error: No se pudo conectar a la base de datos. Por favor, revise su archivo Conexion.php.</p>";
    exit(); 
}

$sqlEquiposDisponibles = "SELECT id_equipo, marca, serial, estado FROM equipos WHERE estado = 'disponible' ORDER BY marca, serial";
$resultadoEquiposDisponibles = $conexion->query($sqlEquiposDisponibles);

$totalEquiposDisponiblesCount = 0;

if ($resultadoEquiposDisponibles) {
    $totalEquiposDisponiblesCount = $resultadoEquiposDisponibles->num_rows;
}
?>
<h2 class='txt'>Equipos Actualmente Disponibles</h2>
<p class="texto">Consulta el listado de equipos que están listos para ser prestados.<p class="texto1"><b>Total de equipos disponibles: <?= htmlspecialchars($totalEquiposDisponiblesCount) ?></b></p></p>

<?php if ($resultadoEquiposDisponibles && $resultadoEquiposDisponibles->num_rows > 0): ?>
    <div class="table-responsivee">
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

<?php
if (isset($conexion) && $conexion instanceof mysqli) {
    $conexion->close();
}
?>
