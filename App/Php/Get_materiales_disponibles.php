<?php
// Php/Get_materiales_disponibles.php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    echo "<p style='color: red;'>Acceso denegado. No tiene permisos para ver este contenido.</p>";
    exit();
}

// Display materials with stock > 0
$sql = "SELECT id_material, nombre, tipo, stock FROM materiales WHERE stock > 0 ORDER BY nombre";
$resultado = $conexion->query($sql);

?>

<h2 class="txt">Materiales Disponibles</h2>
<p>Listado de materiales con existencias en el inventario.</p>

<?php if ($resultado && $resultado->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Stock Disponible</th>
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
    <p>No hay materiales disponibles en este momento.</p>
<?php endif; ?>

<?php
$conexion->close();
?>