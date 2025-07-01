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
$sql = "SELECT id_material, nombre, tipo, stock, estado_material FROM materiales ORDER BY nombre";
$resultado = $conexion->query($sql);

$total = "SELECT COUNT(*) as total FROM materiales";
$resultado2 = $conexion->query($total);
$total = $resultado2 ? $resultado2->fetch_assoc()['total'] : 0;
?>

<h2 class="txt">Total de Materiales Registrados</h2>
<p class="texto">Listado completo de todos los materiales, su tipo y stock actual. <p class="texto1">Total: <b><?php echo $total; ?></b></p></p>

<?php if ($resultado && $resultado->num_rows > 0): ?>
    <div class="table-responsivee">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Stock Actual</th>
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
    <p>No hay materiales registrados en el sistema.</p>
<?php endif; ?>

<?php
$conexion->close();
?>