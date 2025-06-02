<?php
include("ProhibirAcceso.php");
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
<title>Inventario de Almacén</title>
<link rel="stylesheet" href="Css/Inventario.css">

<header>
    <!-- <img src="Img/logo_sena.png" alt="Logo Sena" class="logo"> -->
    <h1>📋 Registro de elementos</h1>
    <!-- <div class="container-rgs">
    <a href="<?php echo $pagina_regresar; ?>" class="rgs" title="Haz clic para volver">Regresar</a>
    </div>  -->
</header>
<div>
    <a href="Php/Historial_Inventario.php" class="historial">📋 Historial de inventario</a>
</div>
<div class="container">
    <h2>Nuevo Registro</h2>  
<form action="Php/Agregar_Al_Inventario.php" method="POST" class="lista" id="formularioInventario">
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
            <option value="">Seleccione</option>
            <option value="Consumible">Consumible</option>
            <option value="No Consumible">No Consumible</option>
        </select>
        <label>Cantidad a ingresar:</label>
        <input type="number" name="stock_material" id="stock_material" min="1" value="1" placeholder="Cantidad de materiales">
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
            <option value="">Seleccione</option>
            <option value="disponible">Disponible</option>
            <option value="prestado">Prestado</option>
            <option value="deteriorado">Deteriorado</option>
        </select>
        <label>Cantidad a ingresar:</label>
        <input type="number" name="stock_equipo" id="stock_equipo" min="1" value="1" placeholder="Cantidad de equipos" required>
    </div>
    <button type="submit" class="agg">Agregar</button>
    </form>
</div>  


<script src="Js/Inventario.js"></script>
