<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php'; 

if ($conexion->connect_error) {
    echo "<p style='text-align: center; color: #dc3545; padding: 20px;'><strong>Error de Conexión a la Base de Datos</strong></p>";
    exit();
}

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

$sqlEquiposDisponiblesForm = "SELECT id_equipo, marca, serial FROM equipos WHERE estado = 'disponible' ORDER BY marca, serial";
$resultadoEquiposDropdown = $conexion->query($sqlEquiposDisponiblesForm);
$equiposData = [];
if ($resultadoEquiposDropdown) { 
    while ($equipo = $resultadoEquiposDropdown->fetch_assoc()) {
        $equiposData[] = $equipo;
    }
    $resultadoEquiposDropdown->free(); 
}

$sqlInstructores = "SELECT id_instructor, nombre, apellido FROM instructores WHERE estado_activo = 'activo' AND disponibilidad_prestamo = 'disponible' ORDER BY nombre, apellido";
$resultadoInstructores = $conexion->query($sqlInstructores);
$instructoresData = [];
if ($resultadoInstructores) {
    while ($instructor = $resultadoInstructores->fetch_assoc()) {
        $instructoresData[] = $instructor;
    }
    $resultadoInstructores->free(); 
}
?> 
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Equipos | SENA</title>
    <link href="Css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="Css/Equipos_prestamo.css">
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
        <h1>Gestión de Equipos</h1>
    </div>
</header>
<div class="container">
    <div id="low-stock-alert-area" class="alerts-container low-stock-alert">
        <h3 class="alerts-title"><i class="fas fa-cubes"></i> Alerta de Stock Bajo</h3>
        <div id="low-stock-alert-content" class="alerts-content">
            <p class="no-alerts-message">Stock de equipos es suficiente.</p>
        </div>
    </div>

    <div class="content-area"> 
        <div class="tabs-container">
            <div class="tab-buttons">
                <button class="tab-button active" data-tab-content="registrar-prestamo">Registrar Préstamo</button>
                <button class="tab-button" data-tab-content="devoluciones-pendientes">Devoluciones Pendientes</button>
                <button class="tab-button" data-tab-content="historial-prestamos">Historial de Préstamos</button>
                <button class="tab-button" data-tab-content="equipos-disponibles">Equipos Disponibles</button>
                <button class="tab-button" data-tab-content="total-equipos">Total Equipos</button>
                <button class="tab-button" data-tab-content="observaciones">Observaciones</button>
            </div>
            <div id="tab-content-area" class="tab-content-area">
                <p>Cargando...</p>
            </div>
        </div> 
    </div>

    <div id="overdue-alerts-area" class="alerts-container">
        <h3 class="alerts-title"><i class="fas fa-exclamation-triangle"></i> Alertas de Préstamos Vencidos</h3>
        <div id="overdue-alerts-content" class="alerts-content">
            <p class="no-alerts-message">No hay alertas de equipos vencidos.</p>
        </div>
    </div>
</div>

<!-- Modal para devolución individual -->
<div id="devolverEquipoModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Registrar Devolución de Equipo</h2>
        <form id="formRegistrarDevolucionIndividual" method="POST">
            <input type="hidden" name="id_prestamo_equipo_detalle" id="modal_id_prestamo_equipo">
            
            <input type="hidden" name="id_responsable" value="<?= htmlspecialchars($id_responsable_session) ?>">
            <input type="hidden" name="rol_responsable" value="<?= htmlspecialchars($rol_responsable_session) ?>">
            <input type="hidden" name="responsable" value="<?= htmlspecialchars($nombre_responsable_completo) ?>">

            <div class="form-group">
                <label for="modal_estado_devolucion">Estado del Equipo:</label>
                <select id="modal_estado_devolucion" name="estado_devolucion" required>
                    <option value="">Seleccione el estado</option>
                    <option value="bueno">Bueno</option>
                    <option value="regular">Regular</option>
                    <option value="malo">Malo</option>
                    <option value="deteriorado">Deteriorado</option>
                </select>
            </div>      
            <div class="form-group">
                <label for="modal_observaciones">Observaciones (Opcional):</label>
                <textarea id="modal_observaciones" name="observaciones" rows="4" placeholder="Ingrese observaciones"></textarea>
            </div>          
            <button type="submit"><i class="fa-regular fa-circle-check"></i> Confirmar Devolución</button>
        </form>
    </div>
</div>

<!-- Modal para devolución múltiple/seleccionada -->
<div id="devolverModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Registrar Devolución de Equipos Seleccionados</h2>
        <form id="formDevolverEquipoMultiple" method="POST">
            <div id="devolver_multiple_ids_container" style="display:none;"></div>

            <input type="hidden" name="id_responsable" value="<?= htmlspecialchars($id_responsable_session) ?>">
            <input type="hidden" name="rol_responsable" value="<?= htmlspecialchars($rol_responsable_session) ?>">
            <input type="hidden" name="responsable" value="<?= htmlspecialchars($nombre_responsable_completo) ?>">

            <label for="modal_estado_devolucion_multiple">Estado de los Equipos al Devolver:</label>
            <select id="modal_estado_devolucion_multiple" name="estado_devolucion" required>
                <option value="">Seleccione</option>
                <option value="bueno">Bueno</option>
                <option value="regular">Regular</option> 
                <option value="malo">Malo</option> 
                <option value="deteriorado">Deteriorado</option> 
            </select>

            <label for="modal_observaciones_multiple">Observaciones para estos equipos (Opcional):</label>
            <textarea id="modal_observaciones_multiple" name="observaciones" rows="4" placeholder="Observaciones generales para los equipos seleccionados..."></textarea>

            <div id="devolver_error_message" style="color: red; margin-top: 10px; display: none;"></div>

            <button type="submit" class="btn-submit">Registrar Devoluciones</button>
        </form>
    </div>
</div>

<!-- Modal para editar PRÉSTAMO COMPLETO -->
<div id="editarPrestamoCompletoModal" class="modal">
    <div class="modal-content large-modal">
        <span class="close-button">&times;</span>
        <h2>Editar Préstamo Completo</h2>
        <form id="formEditarPrestamoCompleto" method="POST">
            <input type="hidden" name="id_prestamo_equipo" id="edit_modal_id_prestamo_equipo_completo">
            
            <div class="loan-summary">
                <p><strong>Instructor:</strong> <span id="edit_modal_instructor_info"></span></p>
                <p><strong>Fecha Préstamo:</strong> <span id="edit_modal_fecha_prestamo_info"></span></p>
                <hr>
            </div>

            <h3>Equipos en este Préstamo:</h3>
            <div id="current_loaned_equipment" class="equipment-list">
                <!-- Se poblará dinámicamente con equipos ya prestados -->
            </div>
            <div id="edit_loan_error_message" style="color: red; margin-top: 10px; display: none;"></div>

            <h3>Añadir Equipos Adicionales:</h3>
            <div class="form-group">
                <label for="add_equipo_id_to_loan">Seleccionar Equipos Disponibles:</label>
                <select id="add_equipo_id_to_loan" class="select2-enhanced" multiple="multiple">
                    <!-- Las opciones se cargarán dinámicamente -->
                </select>
            </div>
            
            <button type="submit"><i class="fa-solid fa-save"></i> Guardar Cambios del Préstamo</button>
        </form>
    </div>
</div>

<script src="Js/jquery-3.7.1.min.js"></script>
<script src="Js/select2.min.js"></script>
<script>
    const nombreResponsableCompletoJs = "<?= htmlspecialchars($nombre_responsable_completo) ?>";
    const rolResponsableSessionJs = "<?= htmlspecialchars($rol_responsable_session) ?>";
    const idResponsableSessionJs = <?= json_encode($id_responsable_session) ?>;
</script>

<script src="Js/Equipos.js"></script>

</body>
</html>
<?php
if (isset($conexion) && $conexion instanceof mysqli) {
    $conexion->close();
}
?>
