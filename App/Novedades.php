<?php
// ProhibirAcceso.php, Conexion.php y resto de código de sesión
include("ProhibirAcceso.php");
include "Conexion.php";
session_start();

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

// Generar opciones de instructores
$instructor_options_html = "";
$sql = "SELECT id_instructor, nombre, apellido FROM instructores ORDER BY nombre ASC";
$res = $conexion->query($sql);
if ($res) {
    if ($res->num_rows > 0) {
        while ($f = $res->fetch_assoc()) {
            $instructor_options_html .= "<option value='" . htmlspecialchars($f["id_instructor"]) . "'>"
                . htmlspecialchars($f["nombre"]) . " " . htmlspecialchars($f["apellido"])
                . "</option>";
        }
    } else {
        $instructor_options_html = "<option value=''>No hay instructores disponibles</option>";
    }
    $res->free();
} else {
    $instructor_options_html = "<option value=''>Error: " . htmlspecialchars($conexion->error) . "</option>";
    error_log("Error en consulta instructores: " . $conexion->error);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Novedades</title>
    <link rel="stylesheet" href="Css/Novedades.css">
</head>

<body>
    <header>
        <div class="contain">
            <a href="<?php echo $pagina_regresar; ?>" class="rgs" title="Haz clic para volver">Regresar</a>
        </div>
        <h1>Registrar Novedades</h1>
    </header>

    <a href="Php/Historial_Novedades.php" class="historial">Historial de novedades</a>

    <div class="container">
        <form action="Procesar_novedades.php" method="post" enctype="multipart/form-data" onsubmit="return validarFormulario()">
            <h2>Registrar Novedad</h2>
            <input type="hidden" name="id_responsable" value="<?= $id_responsable ?>">
            <input type="hidden" name="rol_responsable" value="<?= $rol_responsable ?>">

            <label>Tipo de novedad</label>
            <select name="tipoNovedad" required>
                <option value="">Seleccione</option>
                <option value="devolucion_material">Novedad Material</option>
                <option value="devolucion_equipo">Novedad Equipo</option>
            </select>

            <label>Descripción</label>
            <input type="text" name="descripcion" id="descripcion" placeholder="Descripción" required autocomplete="off">

            <label for="instructor">Instructor</label>
            <select name="instructor_id" id="instructor" required>
                <option value="">Selecciona un instructor</option>
                <?= $instructor_options_html; ?>
            </select>

            <label>Observaciones Adicionales</label>
            <input type="text" name="observaciones" id="observaciones" placeholder="Observaciones" required autocomplete="off">

            <label>Adjuntar imagen</label>
            <input type="file" name="imagen" id="imagen" accept="image/*">

            <label>Responsable de registrar novedad</label>
            <input type="text" name="nombre_responsable" value="<?= htmlspecialchars($nombre_responsable) ?>" readonly>

            <br>
            <button type="submit" name="btnIngresar" value="Ok">Enviar</button>
        </form>
    </div>
    <script src="Js/Novedades.js"></script>
</body>

</html>