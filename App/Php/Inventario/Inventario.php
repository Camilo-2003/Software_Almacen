<?php 
$conn = new mysqli("localhost", "root", "", "almacen");
// Contadores de Equipos
$sql_equipos = "SELECT estado, COUNT(*) AS count_by_status FROM equipos GROUP BY estado";
$result_equipos = $conn->query($sql_equipos);
$equipo_counts = ['disponible' => 0, 'prestado' => 0, 'deteriorado' => 0, 'malo' => 0];
if ($result_equipos) {
    while ($row = $result_equipos->fetch_assoc()) {
        if (array_key_exists($row['estado'], $equipo_counts)) {
            $equipo_counts[$row['estado']] = (int)$row['count_by_status'];
        }
    }
}
// Contador de total de equipos 
$sqlTotalEquipos = "SELECT id_equipo, marca, serial, estado FROM equipos ORDER BY marca, serial"; 
$resultadoTotalEquipos = $conn->query($sqlTotalEquipos);

$totalEquiposCount = 0;

if ($resultadoTotalEquipos) { 
    $totalEquiposCount = $resultadoTotalEquipos->num_rows;
}
// Contadores de Materiales por Tipo
$sql_mat_tipo = "SELECT tipo, COUNT(*) AS count FROM materiales GROUP BY tipo";
$result_mat_tipo = $conn->query($sql_mat_tipo);
$material_tipo_counts = ['consumible' => 0, 'no consumible' => 0];
if ($result_mat_tipo) {
    while ($row = $result_mat_tipo->fetch_assoc()) {
        if (array_key_exists($row['tipo'], $material_tipo_counts)) {
            $material_tipo_counts[$row['tipo']] = (int)$row['count'];
        }
    }
}
//Total de materiales
$total = "SELECT COUNT(*) as total FROM materiales";
$resultado2 = $conn->query($total);
$total = $resultado2 ? $resultado2->fetch_assoc()['total'] : 0;

// Contadores de Materiales por Estado
$sql_mat_estado = "SELECT estado_material, COUNT(*) AS count FROM materiales GROUP BY estado_material";
$result_mat_estado = $conn->query($sql_mat_estado);
$material_estado_counts = ['disponible' => 0, 'en_revision' => 0, 'descartado' => 0];
if ($result_mat_estado) {
    while ($row = $result_mat_estado->fetch_assoc()) {
        if (array_key_exists($row['estado_material'], $material_estado_counts)) {
            $material_estado_counts[$row['estado_material']] = (int)$row['count'];
        }
    }
}
$conn->close();
include("../../ProhibirAcceso.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="../../Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario | SENA</title>
    <link rel="stylesheet" href="../../Css/Inventario.css">
    <link rel="stylesheet" href="../../vendor/fontawesome/css/all.min.css">
</head>
<body>
    <header>
        <div class="regresar">
            <a href="<?php echo $pagina_regresar; ?>" class="rgs" title="Haz clic para volver"><i class="fas fa-reply"></i> Regresar</a>
        </div>
        <div class="header-center-content">
            <img src="../../Img/logo_sena.png" alt="Logo Sena" class="logo">
            <h1>Inventario del Almacén</h1>
        </div>
    </header>

    <div class="container">
        <div class="tabs">
            <button class="tab-button active" onclick="openTab(event, 'equipos')">Equipos</button>
            <button class="tab-button" onclick="openTab(event, 'materiales')">Materiales</button>
        </div>
        
        <div id="equipos" class="tab-content active">
            <div class="form-section">
                <h3>Registrar / Modificar Equipo</h3>
                <input type="hidden" id="equipoId">
                <label for="equipoMarca">Marca:</label>
                <select id="equipoMarca" required>
                    <option value="default" disabled selected>Selecciona una marca</option>
                    <option value="HP">HP</option>
                    <option value="Dell">Dell</option>
                    <option value="Lenovo">Lenovo</option>
                    <option value="Asus">Asus</option>
                    <option value="Acer">Acer</option>
                    <option value="Apple">Apple</option>
                </select>
                <label for="equipoSerial">Serial:</label>
                <input type="text" id="equipoSerial" placeholder="Ej: SN12345" required>
                <label for="equipoEstado">Estado:</label>
                <select id="equipoEstado" required>
                    <option value="">Selecciona el estado</option>
                    <option value="disponible">Disponible</option>
                    <option value="prestado">Prestado</option>
                    <option value="deteriorado">Deteriorado</option>
                </select>
                <button class="guardar" onclick="saveEquipo()"><i class="fa-solid fa-floppy-disk"></i> Guardar Equipo</button>
                <button class="limpiar" onclick="clearEquipoForm()"><i class="fas fa-eraser"></i> Limpiar</button>
                <div class="counts-grid"  style="display: grid; grid-template-columns: repeat(5, auto); gap: 50px;">
                    <p><b><?php echo $equipo_counts['disponible']; ?></b> Disponibles</p>
                    <p><b><?php echo $equipo_counts['prestado']; ?></b> Prestados</p>
                    <p><b><?php echo $equipo_counts['deteriorado']; ?></b> Deteriorados</p>
                    <p><b><?php echo $equipo_counts['malo']; ?></b> Malos</p>
                    <p class="total">Total de equipos: <b><?= htmlspecialchars($totalEquiposCount) ?></b></p>
                </div>
            </div>
            <div class="list-section">
                <h3>Lista de Equipos
                        <a href="../../Reportes/Exportar_Inventario_Equipos _CSV.php" class="export-button-excel" title="EXPORTAR A EXCEL">
                        Exportar a Excel <i class="fas fa-file-excel"></i>
                        </a>
                        <a href="../../Reportes/Exportar_Inventario_Equipos_PDF.php" class="export-button-pdf" title="EXPORTAR A PDF">
                        Exportar a PDF <i class="fas fa-file-pdf"></i>
                        </a>
                    </h3>
                    <div class="table-container-scroll">
                <table id="equiposTable">
                    <thead><tr><th>ID</th><th>Marca</th><th>Serial</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody id="equiposTableBody"></tbody>
                </table>
            </div>
        </div>
</div>
        
        <div id="materiales" class="tab-content">
            <div class="form-section">
                <h3>Registrar / Modificar Material</h3>
                <input type="hidden" id="materialId">
                <label for="materialNombre">Nombre:</label>
                <input type="text" id="materialNombre" placeholder="Ej: Cable HDMI" required>
                <label for="materialTipo">Tipo:</label>
                <select id="materialTipo" required>
                    <option value="">Selecciona el tipo</option>
                    <option value="consumible">Consumible</option>
                    <option value="no consumible">No Consumible</option>
                </select>
                <label for="materialStock">Cantidad:</label>
                <input type="number" id="materialStock" min="0" value="1" required>
                <label for="estadoMaterial">Estado General:</label>
                <select id="estadoMaterial" required>
                    <option value="">Selecciona el estado</option>
                    <option value="disponible">Disponible</option>
                    <option value="en_revision">En Revisión</option>
                    <!-- <option value="descartado">Descartado</option> -->
                </select>
                <button class="guardar" onclick="saveMaterial()"><i class="fa-solid fa-floppy-disk"></i> Guardar Material</button>
                <button class="limpiar" onclick="clearMaterialForm()"><i class="fas fa-eraser"></i> Limpiar</button>
                <div class="counts-grid"  style="display: grid; grid-template-columns: repeat(2, auto); gap: 20px;">
                    <p><b><?php echo $material_tipo_counts['consumible']; ?></b> Como Consumibles</p>
                    <p><b><?php echo $material_tipo_counts['no consumible']; ?></b> Como No Consumibles <b>|| </b></p>
                </div>
                 <div class="counts-grid" style="display: grid; grid-template-columns: repeat(5, auto); gap: 45px;">
                    <p> <b><?php echo $material_estado_counts['disponible']; ?></b> Con Estado Disponible</p>
                    <p><b><?php echo $material_estado_counts['en_revision']; ?></b> Con Estado En Revisión</p>
                    <p class="total">Total de materiales: <b><?php echo $total; ?></b></p>
                    <!-- <p><b><?php echo $material_estado_counts['descartado']; ?></b> Con Estado Descartado</p> -->
                </div>
            </div>
            <div class="list-section">
                <h3>Lista de Materiales
                        <a href="../../Reportes/Exportar_Inventario_Materiales_CSV.php" class="export-button-excel" title="EXPORTAR A EXCEL">
                        Exportar a Excel <i class="fas fa-file-excel"></i>
                        </a>
                        <a href="../../Reportes/Exportar_Inventario_Materiales_PDF.php" class="export-button-pdf" title="EXPORTAR A PDF">
                        Exportar a PDF <i class="fas fa-file-pdf"></i> 
                        </a>
                        </h3>
                        <div class="table-container-scroll">
                <table id="materialesTable">
                    <thead><tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Cantidad</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody id="materialesTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="../../Js/Inventario.js"></script>
</body>
</html>