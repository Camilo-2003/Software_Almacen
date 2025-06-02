<?php
// Incluir el archivo de conexión a la base de datos
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';

// Consulta para obtener los préstamos
$sql = "SELECT * FROM prestamo_equipos";
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
    <header>
        <img src="Img\logo_sena.png" alt="Logo Sena" class="logo">
        <h1>Préstamo De Equipos</h1>
        <div class="regresar">
            <a href="Préstamos.php" class="rgs" title="Haz clic para volver ">Regresar</a>
        </div>
    </header>

    <div >
        <a href="Historial_Devolucion_Equipos.php" class="hist">Historial De Devolución</a>
        <a href="Consultar_equipos.php" class="consultar">consultar equipos</a>
    </div>
    
 <form action="Php/Registrar_prestamo_equipos.php" name="préstamos de equipo" method="post" onsubmit="return validarFormulario()">
    <div class="container">
        <h2>Registrar Préstamo</h2>
        <br> 
        <label>Id equipo</Em></label>
        <input type="number" id="tipo" name="tipo" placeholder="Ingrese el id del equipo" required>

        <label>Id instructor</label>
        <input type="number" id="instructor" name="instructor" placeholder="Ingrese el id del instructor" required>

        <label>Id almacenista</label>
        <input type="number" id="almacenista" name="almacenista" placeholder="Ingrese el id del almacenista" required>

        <!-- <label>Marca</label>
        <select name="marca" required>
            <option value="">Seleccionar</option>
            <option value="hp">HP</option>
            <option value="lenovo">Lenovo</option>
            <option value="dell">Dell</option>
            <option value="acer">Acer</option>
            <option value="asus">Asus</option>
        </select> -->
    
        <br><br>
        <button type="submit">Prestar</button>
    </div>
</form>
  <h2>Listado de Préstamos</h2>
    <table>
        <thead>
            <tr>
                <th>Id Préstamo</th>
                <th>Equipo</th>
                <th>Instructor</th>
                <th>Almacenista</th>
                <th>Fecha Préstamo</th>
                <th>Fecha Devolución</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
         <?php if ($resultado->num_rows > 0): ?>
    <?php while($fila = $resultado->fetch_object()): ?>
        <tr>
            <td><?= $fila->id_prestamo_equipo ?></td>
            <td><?= $fila->id_equipo ?></td>
            <td><?= $fila->id_instructor ?></td>
            <td><?= $fila->id_almacenista ?></td>
            <td><?= $fila->fecha_prestamo ?></td>
            <td><?= $fila->fecha_devolucion ?? 'Pendiente' ?></td>
            <td><?= $fila->estado ?></td>
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
  <a href="Devolucion_Equipos.php" class="boton">Devolución de Equipo</a>
</div>
  <script src="Js/Equipos.js"></script>
</body>
</html>


