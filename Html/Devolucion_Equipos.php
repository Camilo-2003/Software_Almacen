<?php
// Conexión a la base de datos
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';

// Procesar devolución si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_prestamo_equipo'])) {
    $id_prestamo_equipo = $_POST['id_prestamo_equipo'];
    $fecha_devolucion = date("Y-m-d H:i:s");

    // Verificar si el préstamo existe y está pendiente
    $query = $conexion->prepare("SELECT id_equipo FROM prestamo_equipos WHERE id_prestamo_equipo = ? AND estado = 'pendiente'");
    $query->bind_param("i", $id_prestamo_equipo);
    $query->execute();
    $resultado = $query->get_result();

    if ($resultado->num_rows === 0) {
        echo "<script>alert('❌ Error: No se encontró el préstamo con ID $id_prestamo_equipo o ya ha sido devuelto.');</script>";
    } else {
        $row = $resultado->fetch_assoc();
        $id_equipo = $row['id_equipo'];

        // Registrar la devolución
        $stmt = $conexion->prepare("UPDATE prestamo_equipos SET fecha_devolucion = ?, estado = 'devuelto' WHERE id_prestamo_equipo = ?");
        $stmt->bind_param("si", $fecha_devolucion, $id_prestamo_equipo);

        if ($stmt->execute()) {
            // Actualizar el estado del equipo a 'disponible'
            $update_stmt = $conexion->prepare("UPDATE equipos SET estado = 'disponible' WHERE id_equipo = ?");
            $update_stmt->bind_param("i", $id_equipo);
            $update_stmt->execute();

            echo "<script>alert('✅ Devolución registrada exitosamente.');</script>";
        } else {
            echo "<script>alert('❌ Error al registrar la devolución: " . $stmt->error . "');</script>";
        }
    }
}

// Obtener todos los préstamos
$sql = "SELECT * FROM prestamo_equipos";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Préstamos</title>
   <link rel="stylesheet" href="Css/Devolucionequipos.css">
</head>
<body>
    <header>
        <img src="Img\logo_sena.png" alt="Logo Sena" class="logo">
        <h1>Devolucion de Equipos</h1>
        <div class="regresar">
            <a href="Equipos.php" class="rgs" title="Haz clic para volver ">Regresar</a>
        </div>
    </header>
    <h2>Listado de Préstamos</h2>
    <table>
        <thead>
            <tr>
                <th>ID Préstamo</th>
                <th>Equipo</th>
                <th>Instructor</th>
                <th>Almacenista</th>
                <!-- <th>Marca</th> -->
                <th>Fecha Préstamo</th>
                <th>Fecha Devolución</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado->num_rows > 0): ?>
                <?php while($fila = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['id_prestamo_equipo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['id_equipo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['id_instructor']); ?></td>
                        <td><?php echo htmlspecialchars($fila['id_almacenista']); ?></td>
                        <!-- <td><?php echo htmlspecialchars($fila['marca']); ?></td> -->
                        <td><?php echo htmlspecialchars($fila['fecha_prestamo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['fecha_devolucion'] ?? 'Pendiente'); ?></td>
                        <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                        <td>
                            <?php if ($fila['estado'] === 'pendiente'): ?>
                                <form method="POST" onsubmit="return confirm('¿Confirmar devolución?');">
                                    <input type="hidden" name="id_prestamo_equipo" value="<?php echo htmlspecialchars($fila['id_prestamo_equipo']); ?>">
                                    <button type="submit" class="btn-devolver">Devolver</button>
                                </form>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9">No hay préstamos registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

