<?php
include("prohibirAcceso.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <title>Inventario de Almacén</title>
    <link rel="stylesheet" href="Css/inventario.css">
</head>
<body> 

    <header>
        <h1>📋 Registro De Elementos</h1>
        <div class="container-rgs">
            <a href="<?php echo $pagina_regresar; ?>" class="rgs" title="Haz clic para volver">Regresar</a>
        </div> 
    </header>

    <div class="container">
        <h2>Nuevo Registro</h2>
         
        <form action="Php/agregar_material.php" method="POST" class="lista" id="formularioInventario">
            <label>Tipo de registro:</label>
            <select name="tipo_registro" id="tipo_registro" onchange="mostrarCampos()" required>
                <option value="">Seleccione</option>
                <option value="Material">Material</option>
                <option value="Equipo">Equipo</option>
            </select>           
            <div id="materialFields" style="display: none;">
                <label>Nombre del material:</label>
                <input type="text" name="nombre_material" id="nombre_material" placeholder="Ej: Hdmi, Marcador">                  
                <label>Tipo de material:</label>
                <select name="tipo_material" id="tipo_material">
                    <option value="Consumible">Consumible</option>
                    <option value="No Consumible">No Consumible</option>
                </select>
                <label>Cantidad a ingresar:</label>
                <input type="number" name="stock_material" id="stock_material" min="1" value="1">
            </div>
            <div id="equipoFields" style="display: none;">
                <label>Marca:</label>
                <select name="marca" id="marca">
                    <option value="">Seleccione</option>
                    <option value="HP">HP</option>
                    <option value="Dell">Dell</option>
                    <option value="Lenovo">Lenovo</option>
                    <option value="Acer">Acer</option>
                    <option value="Asus">Asus</option>
                </select>
                <label>Serial:</label><br>
                <input type="text" name="serial" id="serial" placeholder="Número de serie">
                <label>Estado:</label>
                <select name="estado" id="estado">
                    <option value="disponible">Disponible</option>
                    <option value="prestado">Prestado</option>
                    <option value="deteriorado">Deteriorado</option>
                </select>
            </div>
            <button type="submit" class="agg">Agregar</button>
        </form>
    </div> 

    <p class="description">
        Accede a esta opción para ver el historial más detallado<br>
        del inventario de los materiales y equipos del almacén.<br>
        <span class="ic">⬇️</span>
    </p>
    <div>
        <a href="Php/historial.php"><button class="historial">📋 Historial de Inventario</button></a>
    </div><br>

    <script src="Js/inventario.js"></script>
</body>
</html>

