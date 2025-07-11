<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once "../../ProhibirAcceso.php";

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}
$sql = "SELECT id_material, nombre, tipo, stock, estado_material FROM materiales WHERE stock > 0 ORDER BY nombre";
$resultado = $conexion->query($sql);

$sqlConsumible = "SELECT COUNT(*) as total_consumibles FROM materiales WHERE tipo = 'consumible' AND stock > 0";
$sqlNoConsumible = "SELECT COUNT(*) as total_no_consumibles FROM materiales WHERE tipo = 'no consumible' AND stock > 0";

$resultado2 = $conexion->query($sqlConsumible);
$resultado3 = $conexion->query($sqlNoConsumible);

$materialesConsumibles = $resultado2 ? $resultado2->fetch_assoc()['total_consumibles'] : 0;
$materialesNoConsumibles = $resultado3 ? $resultado3->fetch_assoc()['total_no_consumibles'] : 0;
?>
<h2 class="txt">Materiales Disponibles</h2>
<p class="texto">Listado de materiales con existencias en el inventario.<p class="texto">Consumible: <b><?php echo $materialesConsumibles; ?></b> | No Consumible: <b><?php echo $materialesNoConsumibles; ?></b></p></p>

<?php if ($resultado && $resultado->num_rows > 0): ?>
<div class="table-responsivee">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Stock Disponible</th>
                <th>Estado Material</th>
            </tr>
        </thead>
        <tbody>
            <?php while($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['id_material']) ?></td>
                    <td><?= htmlspecialchars($fila['nombre']) ?></td>
                    <td><?= htmlspecialchars($fila['tipo']) ?></td>
                    <td><?= htmlspecialchars($fila['stock']) ?></td>
                    <td><?= htmlspecialchars($fila['estado_material']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <p>No hay materiales disponibles en este momento.</p>
<?php endif; ?>

<?php
$conexion->close();
?>