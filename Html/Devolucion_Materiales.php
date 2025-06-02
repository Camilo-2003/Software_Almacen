<?php
include("prohibirAcceso.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Devolucion Materiales</title>
    <link rel="stylesheet" href="Css/Devolucionmateriales.css">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
</head>
<body>
    <header>
        <img src="Img\logo_sena.png" alt="Logo Sena" class="logo">
        <h1>Devolución Materiales</h1>
        <div class="regresar">
            <a href="préstamos.php" class="rgs" title="Haz clic para volver ">Regresar</a>
        </div>
    </header>
    <p></p> Devolución de Materiales 📦</p>
    <table>
        <thead>
            <tr class="encabezado">
                <th>Tipo</th>
                <th>Material</th>
                <th>Cantidad</th>
                <th>Instructor</th>
                <th>Fecha de Prestamo</th>
                <th>Hora de devolucion</th>
                <th>novedad</th>
                <th>Estado</th>
            </tr>
        </thead>
    </table>
    <script src="Js/Materiales.js"></script>
</body>
</html>