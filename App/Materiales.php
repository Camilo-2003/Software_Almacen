<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("ProhibirAcceso.php");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php'; 

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: Error.php");
    exit();
}
$id_responsable_session = 0;
$rol_responsable_session = '';
$nombre_responsable_completo = '';

if (isset($_SESSION['rol'])) {
    $rol_responsable_session = htmlspecialchars($_SESSION['rol']);
    $nombre_responsable_completo = htmlspecialchars($_SESSION["nombres"] . ' ' . $_SESSION["apellidos"]);

    if ($rol_responsable_session === 'almacenista' && isset($_SESSION['id_almacenista'])) {
        $id_responsable_session = intval($_SESSION['id_almacenista']);
    } elseif ($rol_responsable_session === 'administrador' && isset($_SESSION['id_administrador'])) {
        $id_responsable_session = intval($_SESSION['id_administrador']);
    }
}
$sqlMaterialesDisponiblesForm = "SELECT * FROM materiales";
$resultadoMaterialesDropdown = $conexion->query($sqlMaterialesDisponiblesForm);
$materialesData = [];
if ($resultadoMaterialesDropdown) {
    while ($material = $resultadoMaterialesDropdown->fetch_assoc()) {
        $materialesData[] = $material;
    }
}
$sqlInstructores = "SELECT id_instructor, nombre, apellido FROM instructores ORDER BY nombre, apellido";
$resultadoInstructores = $conexion->query($sqlInstructores);
$instructoresData = [];
if ($resultadoInstructores) {
    while ($instructor = $resultadoInstructores->fetch_assoc()) {
        $instructoresData[] = $instructor;
    }
}
ob_start(); 
?>

<?php
$formHtml = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Materiales | SENA</title>
    <script src="Js/jquery-3.7.1.min.js"></script>
    <link href="Css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="Css/Materiales_prestamo.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <div class="regresar">
        <a href="Préstamos.php" class="rgs" title="Haz clic para volver"><i class="fas fa-reply"></i> Regresar</a>
    </div>
    <div class="header-center-content">
        <img src="Img/logo_sena.png" alt="Logo Sena" class="logo">
        <h1>Gestión de Materiales</h1>
    </div>
</header>

<div class="alert-container-main"> 
    <div id="alert-area-vencidos" style="display: none;">
        <h3><i class="fas fa-bell"></i> Alertas de Préstamos Vencidos</h3>
        <div id="contenido-alertas-vencidas"></div>
    </div>
    <div id="alert-area-stock" style="display: none;">
         <h3><i class="fas fa-box-open"></i> Alertas de Inventario</h3>
        <div id="contenido-alerta-stock-bajo"></div>
    </div>
</div>

<div class="container">
    <div class="content-area"> 
        <div class="tabs-container">
            <div class="tab-buttons">
                <button class="tab-button active" data-tab-content="registrar-prestamo">Registrar Préstamo</button>
                <button class="tab-button" data-tab-content="devoluciones-pendientes">Devoluciones Pendientes</button>
                <button class="tab-button" data-tab-content="historial-prestamos">Historial de Préstamos</button>
                <button class="tab-button" data-tab-content="equipos-disponibles">Materiales Disponibles</button>
                <button class="tab-button" data-tab-content="total-equipos">Total Materiales</button>
                <button class="tab-button" data-tab-content="observaciones">Observaciones</button>
            </div>
            <div id="tab-content-area" class="tab-content-area">
                <p>Cargando...</p>
            </div>
        </div>
    </div>
</div>
<div id="devolverMaterialModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Registrar Devolución</h2>
        <form id="formDevolverMaterial" action="Php/Préstamo_Materiales/Registrar_Devolucion_Materiales.php" method="POST">
            <input type="hidden" id="modal_id_prestamo_material" name="id_prestamo_material">
            
            <input type="hidden" name="id_responsable" value="<?= $id_responsable_session ?>">
            <input type="hidden" name="rol_responsable" value="<?= $rol_responsable_session ?>">
            <input type="hidden" name="responsable" value="<?= $nombre_responsable_completo ?>">

            <div class="form-group">
                <label for="modal_estado_devolucion">Estado del Material:</label>
                <select id="modal_estado_devolucion" name="estado_devolucion" required>
                    <option value="">Seleccione el estado</option>
                    <option value="bueno">Bueno</option>
                    <option value="regular">Regular</option>
                    <option value="malo">Malo</option>
                </select>
            </div>       
            <div class="form-group">
                <label for="modal_observaciones">Observaciones (Opcional):</label>
                <textarea id="modal_observaciones" name="observaciones" rows="4" placeholder="Ingresar observación"></textarea>
            </div>          
            <button type="submit"><i class="fa-regular fa-circle-check"></i> Confirmar Devolución</button>
        </form>
    </div>
</div>

<!-- Modal para devolución múltiple -->
<div id="devolverMaterialModalMultiple" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Registrar Devolución de Materiales Seleccionados</h2>
        <form id="formDevolverMaterialMultiple" method="POST">
            <input type="hidden" id="modal_id_prestamo_material_multiple" name="id_prestamo_material">
            
            <input type="hidden" name="id_responsable" value="<?= $id_responsable_session ?>">
            <input type="hidden" name="rol_responsable" value="<?= $rol_responsable_session ?>">
            <input type="hidden" name="responsable" value="<?= $nombre_responsable_completo ?>">

            <div class="form-group">
                <label for="modal_estado_devolucion_multiple">Estado de los Materiales a Devolver:</label>
                <select id="modal_estado_devolucion_multiple" name="estado_devolucion" required>
                    <option value="">Seleccione el estado</option>
                    <option value="bueno">Bueno</option>
                    <option value="regular">Regular</option> 
                    <option value="malo">Malo</option> 
                </select>
            </div>

            <div class="form-group">
                <label for="modal_observaciones_multiple">Observaciones para estos materiales (Opcional):</label>
                <textarea id="modal_observaciones_multiple" name="observaciones" rows="4" placeholder="Observaciones generales para los materiales seleccionados..."></textarea>
            </div>

            <button type="submit"><i class="fa-regular fa-circle-check"></i> Registrar Devoluciones</button>
        </form>
    </div>
</div>

<!-- Modal para editar PRÉSTAMO COMPLETO -->
<div id="editarPrestamoCompletoModal" class="modal">
    <div class="modal-content large-modal">
        <span class="close-button">&times;</span>
        <h2>Editar Préstamo Completo</h2>
        <form id="formEditarPrestamoCompleto" method="POST">
            <input type="hidden" name="id_prestamo_material" id="edit_modal_id_prestamo_material_completo">
            
            <div class="loan-summary">
                <p><strong>Instructor:</strong> <span id="edit_modal_instructor_info"></span></p>
                <p><strong>Fecha Préstamo:</strong> <span id="edit_modal_fecha_prestamo_info"></span></p>
                <hr>
            </div>

            <h3>Materiales en este Préstamo:</h3>
            <div id="current_loaned_equipment" class="equipment-list">
                </div>

            <h3 style="margin-top: 20px;">Añadir Materiales Adicionales:</h3>
            <div class="form-group">
                <label for="add_material_id_to_loan">Seleccionar Materiales Disponibles:</label>
                <select id="add_material_id_to_loan" class="select2-enhanced" multiple="multiple"></select>
            </div>

            <!--NUEV0 DEL RESTO TODO IGUAL-->
            <div id="items_to_add_quantities" class="cantidad-fields-container" style="display: none;"></div>
            
            <div id="items_to_add_quantities" class="equipment-list" style="margin-top:15px;">
                </div>
            
            <button type="submit" style="margin-top:20px;"><i class="fa-solid fa-save"></i> Guardar Cambios del Préstamo</button>
        </form>
    </div>
</div>

    <input type="hidden" id="id_responsable_session" value="<?= htmlspecialchars($id_responsable_session) ?>">
    <input type="hidden" id="rol_responsable_session" value="<?= htmlspecialchars($rol_responsable_session) ?>">
    <input type="hidden" id="nombre_responsable_completo" value="<?= htmlspecialchars($nombre_responsable_completo) ?>">
    <input type="hidden" id="initial_tab_loaded" value="<?= htmlspecialchars($_GET['tab'] ?? 'historial-prestamos') ?>">

<script src="Js/Materiales.js"></script>
<script src="Js/select2.min.js"></script>
<script>
    var formHtmlContent = '<?php echo addslashes(json_encode($formHtml)); ?>';
    formHtmlContent = JSON.parse(formHtmlContent);
</script>

</body>
</html>