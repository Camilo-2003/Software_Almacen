<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once "../../ProhibirAcceso.php";

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    echo "<p style='color: red;'>Acceso denegado.</p>";
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

$sqlMaterialesDisponiblesForm = "SELECT * FROM materiales WHERE stock > 0 AND estado_material = 'disponible'";
$resultadoMaterialesDropdown = $conexion->query($sqlMaterialesDisponiblesForm);
$materialesData = [];
if ($resultadoMaterialesDropdown) {
    while ($material = $resultadoMaterialesDropdown->fetch_assoc()) {
        $materialesData[] = $material;
    }
}

$sqlInstructores = "SELECT id_instructor, nombre, apellido FROM instructores WHERE disponibilidad_prestamo = 'disponible' AND estado_activo = 'activo' ORDER BY nombre, apellido ASC";
$resultadoInstructores = $conexion->query($sqlInstructores);
$instructoresData = [];
if ($resultadoInstructores) {
    while ($instructor = $resultadoInstructores->fetch_assoc()) {
        $instructoresData[] = $instructor;
    }
}
?>

<form id="formRegistrarPrestamoMaterial" method="post">
    <h2>Registrar Nuevo Préstamo de Material</h2>
    
    <div class="form-group">
        <label for="material_id">Materiales:</label>
        <select id="material_id" name="material_id[]" multiple="multiple" class="select2-enhanced" required>           
             <?php foreach ($materialesData as $material): ?>
                <option 
                    value='<?= htmlspecialchars($material['id_material']) ?>' 
                    data-stock='<?= htmlspecialchars($material['stock']) ?>'
                >
                    <?= htmlspecialchars($material['nombre']) ?> (Tipo: <?= htmlspecialchars($material['tipo']) ?>) 
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div id="cantidad_fields" class="cantidad-fields-container"></div>
    
    <div class="form-group">
        <label for="instructor_id">Instructor:</label>
        <select name="instructor" id="instructor" class="select2-enhanced" required>
            <option value="">Seleccione un instructor</option>
            <?php foreach ($instructoresData as $instructor): ?>
                <option value='<?= htmlspecialchars($instructor['id_instructor']) ?>'>
                    <?= htmlspecialchars($instructor['nombre']) ?> <?= htmlspecialchars($instructor['apellido']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label>Responsable del Préstamo:</label>
        <input type="text" value="<?php echo $nombre_responsable_completo . ' (Rol: ' . $rol_responsable_session . ')'; ?>" readonly>
        <input type="hidden" name="responsable" value="<?= htmlspecialchars($nombre_responsable_completo) ?>">
        <input type="hidden" name="id_responsable" value="<?= $id_responsable_session ?>">
        <input type="hidden" name="rol_responsable" value="<?= $rol_responsable_session ?>">
    </div>
    
    <button type="submit"><i class="fa-solid fa-handshake" id="icon"></i> Prestar Material</button>
</form>
<?php
$conexion->close();
?>