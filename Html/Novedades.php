<?php
include("ProhibirAcceso.php");
include "Conexion.php";

$id_responsable = "";
$nombre_responsable = "";
$rol_responsable = "";

if (isset($_SESSION["rol"])) {
    $rol_responsable = $_SESSION["rol"];
    if ($rol_responsable == "almacenista" && isset($_SESSION["id_almacenista"])) {
        $id_responsable = $_SESSION["id_almacenista"];
    } elseif ($rol_responsable == "administrador" && isset($_SESSION["id_administrador"])) {
        $id_responsable = $_SESSION["id_administrador"];
    }

    $nombre_responsable = $_SESSION["nombres"] . ' ' . $_SESSION["apellidos"];
}
?>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novedades</title>
    <link rel="stylesheet" href="Css/Novedades.css">

    <header>
        <div class="contain">
    <a href="<?php echo $pagina_regresar; ?>"  class="rgs" title="Haz clic para volver">Regresar</a>
</div>
    <!-- <img src="Img/logo_sena.png" alt="Logo Sena" class="logo"> -->
    <h1>Registrar Novedades</h1> 
    </header>

<a href="Historial_Novedades.php" class="historial">Historial de novedades</a>

    <div class="container">
 <form action="Php/Procesar_novedades.php" method="post" onsubmit="return validarFormulario()"> 
     <h2>Registrar Novedad</h2>
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="id_responsable" value="<?= $id_responsable ?>">
    <input type="hidden" name="rol_responsable" value="<?= $rol_responsable ?>">
    <label>Tipo de novedad</label>
    <select name="tipoNovedad">
        <option>Seleccione</option>
        <option value="devolucion_material">Novedad Material</option>
        <option value="devolucion_equipo">Novedad Equipo</option>
    </select>
    <label>Descripcion</label>
    <input type="text" placeholder="Descripcion" name="descripcion" id="descripcion" autocomplete="off" required>
    <label>Id Instructor</label>
    <input type="number" placeholder="Id instructor" name="id_instructor" id="id_instructor" required>
    <label>Instructor</label>
    <input type="text" placeholder="Nombre instructor" name="instructor" id="instructor" required>
    <label>Observaciones Adicionales</label>
    <input type="text" placeholder="Observaciones" name="observaciones" id="observaciones" autocomplete="off" required>
    <label>Responsable De Registrar Novedad</label>
   <input type="text" name="nombre_responsable" value="<?= $nombre_responsable ?>" readonly>

    <br>
    <button type="submit" name="btnIngresar" value="Ok">Enviar</button>
    </form>
    </div>
    <br>

<script src="Js/Novedades.js"></script>
  