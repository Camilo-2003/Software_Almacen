<?php
// Php/Get_total_equipos.php
session_start(); // Start the session if it's needed for any logic here (e.g., user permissions)
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';

// It's good practice to ensure only authorized users can access this data
if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    echo "<p style='color: red;'>Acceso denegado. No tiene permisos para ver este contenido.</p>";
    exit();
}

// Query to get all equipment, regardless of their state
$sqlTotalEquipos = "SELECT id_equipo, marca, serial, estado FROM equipos ORDER BY marca, serial";
$resultadoTotalEquipos = $conexion->query($sqlTotalEquipos);

?>

<h3>Listado Completo de Equipos</h3>
<p>Aquí puedes ver todos los equipos registrados en el sistema, con su estado actual.</p>

<?php if ($resultadoTotalEquipos->num_rows > 0): ?>
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
                    <td><?= htmlspecialchars($fila['estado']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No hay equipos registrados en el sistema.</p>
<?php endif; ?>

<?php
// Close the database connection for this script
$conexion->close();
?>