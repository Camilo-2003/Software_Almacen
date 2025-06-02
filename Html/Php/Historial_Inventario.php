<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conexion = new mysqli("localhost", "root", "", "almacen");

if ($conexion->connect_errno) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../Img/logo_sena.png" type="image/x-icon">
    <title>Historial de Inventario</title>
    <link rel="stylesheet" href="../Css/HistorialInventario.css">
</head>
<body>
<header>
 <h1>📋 Historial de Inventario</h1>
</header>
 <br>
 <div>
 <a href="../Inventario.php" class="regresar" title="Haz clic para volver ">Regresar</a>
 </div>
 <br>
 <h2>📦 Materiales</h2><br>
 <input type="text" id="busquedaMateriales" placeholder="🔍 Buscar Materiales..." onkeyup="filtrarTabla('busquedaMateriales', 'tablaMateriales')">
 <br><br>
 <div class="container">
 <table id="tablaMateriales">
 <thead>
    <tr>
        <th>Id_Material</th>
        <th>Nombre</th>
        <th>Tipo</th>
        <th>Cantidad</th>
        <th>Acciones</th>
    </tr>
 </thead>
 <tbody>
    <?php
    $sqlM = "SELECT * FROM materiales";
    $resultM = $conexion->query($sqlM);

if ($resultM->num_rows > 0) {
    while ($row = $resultM->fetch_assoc()) {
   echo "<tr>
            <td>{$row['id_material']}</td>
            <td>{$row['nombre']}</td>
            <td>{$row['tipo']}</td>
            <td>{$row['stock']}</td>
            <td>
                <a class='btn-editar' href='Editar_Inventario.php?tipo=material&id={$row['id_material']}'>✏️ Editar</a>
                <a class='btn-eliminar1' href='Eliminar_De_Inventario.php?tipo=material&id={$row['id_material']}' onclick='return confirmarEliminacion()'>🗑️ Eliminar</a>
            </td>
        </tr>";
    }
  } else {
        echo "<tr><td colspan='4'>No hay materiales registrados en este momento.</td></tr>";
    }
    ?>
  </tbody>
 </table>
</div>
<br>
<h2>💻 Equipos</h2><br>
<input type="text" id="busquedaEquipos" placeholder="🔍 Buscar Equipos..." onkeyup="filtrarTabla('busquedaEquipos', 'tablaEquipos')">
<br><br>
<div class="container2">
<table id="tablaEquipos">
<thead>
    <tr>
        <th>Id_Equipo</th>
        <th>Marca</th>
        <th>Serial</th>
        <th>Estado</th>
        <th>Cantidad</td>
        <th>Acciones</th>
    </tr>
</thead>
<tbody>
    <?php
    $sqlE = "SELECT * FROM equipos";
    $resultE = $conexion->query($sqlE);

if ($resultE->num_rows > 0) {
    while ($row = $resultE->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id_equipo']}</td>
                <td>{$row['marca']}</td>
                <td>{$row['serial']}</td>
                <td>{$row['estado']}</td>
                <td>{$row['stock']}</td>
                <td>
                    <a class='btn-editar2' href='Editar_Inventario.php?tipo=equipo&id={$row['id_equipo']}'>✏️ Editar</a>
                    <a class='btn-eliminar2' href='Eliminar_De_Inventario.php?tipo=equipo&id={$row['id_equipo']}' onclick='return confirmarEliminacion()'>🗑️ Eliminar</a>
                </td>
                </tr>";
    }
} else {
    echo "<tr><td colspan='4'>No hay equipos registrados en este momento.</td></tr>";
}
    ?>
</tbody>
</table>
</div>

<br>

<button class="btn-total" onclick="mostrarTotalesEquipos()">📊 Ver total de equipos por marca</button>

<h3 id="tituloTotales" style="display: none;">🔢 Equipos por Marca</h3>

<div class="container2" id="contenedorTabla" style="display: none; margin-top: 10px;">
<table>
<thead>
    <tr>
        <th>Marca</th>
        <th>Total</th>
    </tr>
</thead>
<tbody>
    <?php
    //Esto agrupa los equipos por marca y cuenta cuántos hay de cada una.
    $sqlTotal = "SELECT marca, COUNT(*) AS total FROM equipos GROUP BY marca";
    $resultTotal = $conexion->query($sqlTotal);

    if ($resultTotal->num_rows > 0) {
        while ($row = $resultTotal->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['marca']}</td>
                    <td>{$row['total']}</td>
                    </tr>";
        }
    } else {
        echo "<tr><td colspan='2'>No hay datos disponibles en este momento.</td></tr>";
    }
    ?>
</tbody>
</table>
</div>

<button class="btn-total" onclick="mostrarTotalesMateriales()">📦 Ver total de materiales por tipo</button>

<h3 id="tituloTotalesMateriales" style="display: none;">📋 Total de Materiales por Tipo</h3>

<div class="container2" id="contenedorTablaMateriales" style="display: none; margin-top: 10px;">
<table>
<thead>
    <tr>
        <th>Tipo</th>
        <th>Total</th>
    </tr>
</thead>
<tbody>
    <?php
    //Esto agrupa los materiales por tipo y cuenta cuántos hay de cada uno.
    $sqlMat = "SELECT tipo, COUNT(*) AS total FROM materiales GROUP BY tipo";
    $resultMat = $conexion->query($sqlMat);

    if ($resultMat->num_rows > 0) {
        while ($row = $resultMat->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['tipo']}</td>
                    <td>{$row['total']}</td>
                    </tr>";
        }
    } else {
        echo "<tr><td colspan='2'>No hay materiales registrados.</td></tr>";
    }
    ?>
</tbody>
</table>
    </div>
    <script src="../Js/HistorialInventario.js"></script>
</body>
</html>
<?php
$conexion->close();
?>
