<?php
// Conexión a la base de datos
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';

// Consulta para obtener los equipos disponibles
$sql = "SELECT id_equipo, marca, serial FROM equipos WHERE estado = 'disponible'";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Equipos Disponibles</title>
    <link rel="stylesheet" href="Css/Consultar_equipos.css">
</head>
    <b>
    <header>
        <img src="Img\logo_sena.png" alt="Logo Sena" class="logo">
        <h1>Consultar Equipos</h1>
        <div class="regresar">
            <a href="Equipos.php" class="rgs" title="Haz clic para volver ">Regresar</a>
        </div>
    </header>
<body>
    <h2>Listado de Equipos Disponibles</h2>
    <table>
        <thead>
            <tr>
                <th>ID Equipo</th>
                <th>Marca</th>
                <th>Serial</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($resultado->num_rows > 0): ?>
                <?php while($fila = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['id_equipo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['marca']); ?></td>
                        <td><?php echo htmlspecialchars($fila['serial']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No hay equipos disponibles.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
