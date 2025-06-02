<?php
// Incluir el archivo de conexión a la base de datos
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/conexion.php';

// Consulta para obtener los préstamos
$sql = "SELECT id_prestamo_equipo, id_equipo, id_instructor, id_almacenista, marca, fecha_prestamo, fecha_devolucion, estado FROM prestamo_equipos";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipos</title>
    <link rel="stylesheet" href="Css/Equipos.css">
</head>
<b>
    <header>
        <img src="Img\logo_sena.png" alt="Logo Sena" class="logo">
        <h1>Préstamo De Equipos</h1>
        <div class="regresar">
            <a href="préstamos.php" class="rgs" title="Haz clic para volver ">Regresar</a>
        </div>
    </header>

    <div >
        <a href="HistorialDevEquipos.php" class="hist">Historial De Devolución</a>
        <a href="consultar_equipos.php" class="hist">consultar equipos</a>
    </div>
    
 <form action="Php/registrar_prestamo_equipo.php" name="préstamos de equipo" method="post" onsubmit="return validarFormulario()">
    <div class="container">
        <h2>Registrar Préstamo</h2>
        <br> 
        <label>Equipo</Em></label>
        <input id="tipo" name="tipo" require>

        <label for="">Instructor</label>
        <input type="text" name="instructor" require>

        <label for="">Almacenista</label>
        <input type="text" name="almacenista" require>

        <label for="">Marca</label>
        <input type="text" name="marca" require>
        
        <br><br>
        <button type="submit">Prestar</button>
    </div>
</form>
  <h2>Listado de Préstamos</h2>
    <table>
        <thead>
            <tr>
                <th>ID Préstamo</th>
                <th>Equipo</th>
                <th>Instructor</th>
                <th>Almacenista</th>
                <th>Marca</th>
                <th>Fecha Préstamo</th>
                <th>Fecha Devolución</th>
                <th>Estado</th>
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
                        <td><?php echo htmlspecialchars($fila['marca']); ?></td>
                        <td><?php echo htmlspecialchars($fila['fecha_prestamo']); ?></td>
                        <td><?php echo htmlspecialchars($fila['fecha_devolucion'] ?? 'Pendiente'); ?></td>
                        <td><?php echo htmlspecialchars($fila['estado']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No hay préstamos registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<br>
  <div class="contenedor-boton">
  <a href="DevolucionEquipos.php" class="boton">Devolución de Equipo</a>
</div>

  <script src="Js/Equipos.js"></script>
</body>
</html>


