<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}
$sqlTotalEquipos = "SELECT id_equipo, marca, serial, estado FROM equipos ORDER BY marca, serial"; 
$resultadoTotalEquipos = $conexion->query($sqlTotalEquipos);

$totalEquiposCount = 0;

if ($resultadoTotalEquipos) {
    $totalEquiposCount = $resultadoTotalEquipos->num_rows;
}
?>
<h2 class='txt'>Listado Completo de Equipos</h2>
<p class="texto">Aquí puedes ver todos los equipos registrados en el sistema.<p class="texto1"><b>Total de equipos:<?= htmlspecialchars($totalEquiposCount) ?></b></p></p>

<?php if ($resultadoTotalEquipos->num_rows > 0): ?>
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
            <?php while($fila = $resultadoTotalEquipos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['id_equipo']) ?></td>
                    <td><?= htmlspecialchars($fila['marca']) ?></td>
                    <td><?= htmlspecialchars($fila['serial']) ?></td>
                    <td><?= htmlspecialchars($fila['estado']) ?></td> </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>
<?php else: ?>
    <p>No hay equipos registrados en el sistema.</p>
<?php endif; ?>

<?php
$conexion->close();
?>