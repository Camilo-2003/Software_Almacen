<?php
include("ProhibirAcceso.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
<title>Materiales</title>
<link rel="stylesheet" href="Css/Materiales.css">
</head>
<body>
<header>
    <img src="Img/logo_sena.png" alt="Logo Sena" class="logo">
    <h1>Préstamo de Materiales</h1>
    <div class="regresar">
        <a href="Préstamos.php" class="rgs" title="Haz clic para volver ">Regresar</a>
    </div>
</header>

<div>
    <!-- <a href="Historial_Devolucion_Material.php" class="hist">Historial De Devolución</a> -->
    <a href="Consultar_materiales.php" class="hist">Consultar Materiales Disponibles</a>
</div>

<form action="Php/Registrar_prestamo_materiales.php" method="post" onsubmit="return validarFormulario()">
    <div class="container">
        <h2>Registrar Préstamo</h2>

        <label for="tipo">Tipo de Préstamo:</label>
        <select id="tipo" name="Tipo" required>
            <option value="">Seleccionar</option>
            <option value="consumible">Consumible</option>
            <option value="no_consumible">No Consumible</option>
        </select>
        <br><br>

        <label for="material">Material:</label>
        <input id="material" name="material" type="text" placeholder="Nombre del material" required>
        <br><br>

        <label for="cantidad">Cantidad:</label>
        <input type="number" id="cantidad" name="Cantidad" min="1" required placeholder="Cantidad de materiales">
        <br><br>

        <label for="instructor">Instructor:</label>
        <input type="text" id="instructor" name="Instructor" placeholder="Nombre del Instructor" required>
        <br><br>

        <button type="submit">Prestar</button>
    </div>
</form>

<script src="Js/Materiales.js"></script>
</body>
</html>







