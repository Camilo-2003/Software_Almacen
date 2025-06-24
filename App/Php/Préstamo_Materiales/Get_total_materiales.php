<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once "../../ProhibirAcceso.php";
if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    echo "<p style='color: red;'>Acceso denegado. No tiene permisos para ver este contenido.</p>";
    exit();
}
$sql = "SELECT id_material, nombre, tipo, stock FROM materiales ORDER BY nombre";
$resultado = $conexion->query($sql);

$total = "SELECT COUNT(*) as total FROM materiales";
$resultado2 = $conexion->query($total);
$total = $resultado2 ? $resultado2->fetch_assoc()['total'] : 0;
?>

<h2 class="txt">Total de Materiales Registrados</h2>
<p>Listado completo de todos los materiales, su tipo y stock actual. Total: <b><?php echo $total; ?></b></p>

<?php if ($resultado && $resultado->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Stock Actual</th>
            </tr>
        </thead>
        <tbody>
            <?php while($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['id_material']) ?></td>
                    <td><?= htmlspecialchars($fila['nombre']) ?></td>
                    <td><?= htmlspecialchars($fila['tipo']) ?></td>
                    <td><?= htmlspecialchars($fila['stock']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No hay materiales registrados en el sistema.</p>
<?php endif; ?>

<?php
$conexion->close();
?>