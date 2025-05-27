<?php
include("prohibirAcceso.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Préstamo de Materiales</title>
    <link rel="stylesheet" href="Css/Materiales.css">
</head>
<body>
    <header>
        <img src="Img/logo_sena.png" alt="Logo Sena" class="logo">
        <h1>Préstamo de Materiales</h1>
        <div class="regresar">
<<<<<<< HEAD:Html/Materiales.html
            <a href="préstamos.html" class="rgs" title="Haz clic para volver">Regresar</a>
=======
            <a href="préstamos.php" class="rgs" title="Haz clic para volver ">Regresar</a>
>>>>>>> 4ba1d77a333f087b72b8688ebbc6e206f28f4ca4:Html/Materiales.php
        </div>
    </header>

    <div>
<<<<<<< HEAD:Html/Materiales.html
        <a href="HistorialDevMaterial.html" class="hist">Historial de Devoluciones</a>
=======
        <a href="HistorialDevMaterial.php" class="hist">Historial De Devolución</a>
>>>>>>> 4ba1d77a333f087b72b8688ebbc6e206f28f4ca4:Html/Materiales.php
    </div>

    <form action="Php/registrar_prestamo_materiales.php" method="post" onsubmit="return validarFormulario()">
        <div class="container">
            <h2>Registrar Préstamo</h2>

            <label for="Tipo">Tipo de Préstamo:</label>
            <select id="Tipo" name="Tipo" required>
                <option value="">Seleccionar</option>
                <option value="consumible" style="text-transform: none;">consumible</option>
                <option value="no consumible " style="text-transform: none;">no Consumible</option>
            </select>
            <br><br>

            <label for="Material">Material:</label>
            <input id="Material" name="Material" type="text" placeholder="Nombre del material" required>
            <br><br>

            <label for="Cantidad">Cantidad:</label>
            <input type="number" id="Cantidad" name="Cantidad" min="1" required>
            <br><br>

            <label for="Instructor">Instructor:</label>
            <input type="text" id="Instructor" name="Instructor" placeholder="Nombre del Instructor" required>
            <br><br>

            <button type="submit">Prestar</button>
        </div>
    </form>

    <script src="Js/Materiales.js"></script>
</body>
</html>







