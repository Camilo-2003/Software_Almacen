<?php 
$conn = new mysqli("localhost", "root", "", "almacen");

$sql_counts = "SELECT estado, COUNT(*) AS count_by_status FROM equipos GROUP BY estado";
$result_counts = $conn->query($sql_counts);

$equipo_counts = [
    'disponible' => 0,
    'prestado' => 0,
    'deteriorado' => 0,
];

if ($result_counts) {
    while ($row = $result_counts->fetch_assoc()) {
        $estado = $row['estado'];
        $count = (int)$row['count_by_status']; 

        if (array_key_exists($estado, $equipo_counts)) {
            $equipo_counts[$estado] = $count;
        }
    }
} else {
    error_log("Error en la consulta de conteo de equipos por estado: " . $conn->error);
}
$sql_count = "SELECT tipo, COUNT(*) AS count FROM materiales GROUP BY tipo";
$result = $conn->query($sql_count);

$material_counts = [
    'consumible' => 0,
    'no consumible' => 0,
];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tipo = $row['tipo'];
        $count = (string)$row['count']; 

        if (array_key_exists($tipo, $material_counts)) {
            $material_counts[$tipo] = $count;
        }
    }
} else {
    error_log("Error en la consulta de conteo de equipos por estado: " . $conn->error);
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
    <title>Sistema de Inventario - Almacén</title>
    <link rel="stylesheet" href="../../Css/Inventario.css">
    <link rel="stylesheet" href="../../vendor/fontawesome/css/all.min.css">
</head>
<body>
    <header>
    <div class="regresar">
    <a href="<?php echo $pagina_regresar; ?>" class="rgs" title="Haz clic para volver"><i class="fas fa-reply"></i> Regresar</a></div>
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
            <h2>Gestión de Equipos</h2>
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
                <select id="equipoEstado">
                    <option value="disponible">Disponible</option>
                    <option value="prestado">Prestado</option>
                    <option value="deteriorado">Deteriorado</option>
                </select><br>
                <button class="guardar" onclick="saveEquipo()"><i class="fa-solid fa-floppy-disk"></i> Guardar Equipo</button>
                <button class="limpiar" onclick="clearEquipoForm()"><i class="fa-solid fa-broom"></i> Limpiar</button>
                <div style="display: grid; grid-template-columns: repeat(3, auto); gap: 1rem;">
                <p><b><?php echo $equipo_counts['disponible']; ?></b> Equipos Con Estado "Disponible".</p>
                <p><b><?php echo $equipo_counts['prestado']; ?></b> Equipos Con Estado "Prestado".</p>
                <p><b><?php echo $equipo_counts['deteriorado']; ?></b> Equipos Con Estado "Deteriorado".</p>
                </div>
            </div>
            <div class="list-section">
                <h3>Lista de Equipos</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Marca</th>
                            <th>Serial</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="equiposTableBody">
                        </tbody>
                </table>
            </div>
        </div>
        <div id="materiales" class="tab-content">
            <h2>Gestión de Materiales</h2>
            <div class="form-section">
                <h3>Registrar / Modificar Material</h3>
                <input type="hidden" id="materialId">
                <label for="materialNombre">Nombre:</label>
                <input type="text" id="materialNombre" placeholder="Ej: HDMI" required>
                <label for="materialTipo">Tipo:</label>
                <select id="materialTipo">
                    <option value="consumible">Consumible</option>
                    <option value="no consumible">No Consumible</option>
                </select>
                <label for="materialStock">Cantidad:</label>
                <input type="number" id="materialStock" min="0" value="1" required><br>
                <button class="guardar" onclick="saveMaterial()"><i class="fa-solid fa-floppy-disk"></i> Guardar Material</button>
                <button class="limpiar" onclick="clearMaterialForm()"><i class="fa-solid fa-broom"></i> Limpiar</button>
                <div style="display: grid; grid-template-columns: repeat(3, auto); gap: 1rem;">
                <p><b><?php echo $material_counts['consumible']; ?></b> Materiales Como Tipo "Consumible".</p>
                <p><b><?php echo $material_counts['no consumible']; ?></b> Materiales Como Tipo "No Consumible".</p>
                </div>
            </div>
          <div class="list-section">
                <h3>Lista de Materiales</h3>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="materialesTableBody">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="../../Js/Inventario.js"></script>
</body>
</html>