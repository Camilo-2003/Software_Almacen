<?php
include("ProhibirAcceso.php");
 include "Conexion.php";
?>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novedades</title>
    <link rel="stylesheet" href="Css/Novedades.css">

    <header>
    <!-- <img src="Img/logo_sena.png" alt="Logo Sena" class="logo"> -->
    <h1>Registrar Novedades</h1>
    </header>

<a href="Historial_Novedades.php" class="historial">Historial de novedades</a>

    <div class="container">
 <form action="Php/Procesar_novedades.php" method="post" onsubmit="return validarFormulario()"> 
     <h2>Registrar Novedad</h2>
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <label>Tipo de novedad</label>
    <select name="tipoNovedad">
        <option>Seleccione</option>
        <option value="devolucion_material">Novedad Material</option>
        <option value="devolucion_equipo">Novedad Equipos</option>
    </select>
    <label>Descripcion</label>
    <input type="text" placeholder="Descripcion" name="descripcion" id="descripcion" autocomplete="off" required>
    <label>Id instructor</label>
    <input type="number" placeholder="Id instructor" name="id_instructor" id="id_instructor" required>
    <label>Instructor</label>
    <input type="text" placeholder="Nombre instructor" name="instructor" id="instructor" required>
    <label>Id almacenista</label>
    <input type="number" placeholder="Id almacenista" name="id_almacenista" id="id_almacenista" required>
    <label>Almacenista</label>
    <input type="text" placeholder="Nombre almacenista" name="almacenista" id="almacenista" required>
    <label>Observaciones adicionales</label>
    <input type="text" placeholder="Observaciones" name="observaciones" id="observaciones" autocomplete="off" required>
    <br>
    <button type="submit" name="btnIngresar" value="Ok">Enviar</button>
    </form>
    </div>
    <br>

<script src="Js/Novedades.js"></script>
  