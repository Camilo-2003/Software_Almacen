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
}$instructor_options_html = "";
// Selecting id_instructor, nombre, and apellido from the database
$sql_instructores = "SELECT id_instructor, nombre, apellido FROM instructores ORDER BY nombre ASC";
$resultado_instructores = $conexion->query($sql_instructores);

if ($resultado_instructores) {
    if ($resultado_instructores->num_rows > 0) {
        while ($fila_instructor = $resultado_instructores->fetch_assoc()) {
            $instructor_options_html .= "<option value='" . htmlspecialchars($fila_instructor["id_instructor"]) . "'>";
            // Displaying both name and surname in the option text
            $instructor_options_html .= htmlspecialchars($fila_instructor["nombre"]) . " " . htmlspecialchars($fila_instructor["apellido"]);
            $instructor_options_html .= "</option>";
        }
    } else {
        $instructor_options_html = "<option value=''>No hay instructores disponibles</option>";
    }
    $resultado_instructores->free(); // Free the result set
} else {
    // Error handling if the query fails
    $instructor_options_html = "<option value=''>Error al cargar instructores: " . $conexion->error . "</option>";
    error_log("Error en la consulta de instructores: " . $conexion->error);
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

<a href="Php/Historial_Novedades.php" class="historial">Historial de novedades</a>

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
 <label for="instructor">Instructor</label>
    <select name="instructor_id" id="instructor" required> <option value="">Selecciona un instructor</option>
        <?= $instructor_options_html; ?>
    </select>
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
