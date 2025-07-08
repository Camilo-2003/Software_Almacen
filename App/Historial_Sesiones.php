<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

if (!isset($_SESSION["rol"])) {
    header("Location: Login.php");
    exit();
}

if ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador") {
    header("Location: Error.php");
    exit();
}

$where_clauses = [];
$params = [];
$param_types = '';

$fecha_inicio = '';
$fecha_fin = '';

if (isset($_GET['fecha_inicio']) && !empty($_GET['fecha_inicio'])) {
    $fecha_inicio = $_GET['fecha_inicio'];
    $where_clauses[] = "DATE(hs.hora_ingreso) >= ?";
    $params[] = $fecha_inicio;
    $param_types .= 's';
}

if (isset($_GET['fecha_fin']) && !empty($_GET['fecha_fin'])) {
    $fecha_fin = $_GET['fecha_fin'];
    $where_clauses[] = "DATE(hs.hora_ingreso) <= ?";
    $params[] = $fecha_fin;
    $param_types .= 's';
}

$sql = "SELECT
            DATE(hs.hora_ingreso) AS fecha_ingreso_solo_fecha,
            TIME(hs.hora_ingreso) AS hora_ingreso_solo_hora,
            DATE(hs.hora_salida) AS fecha_salida_solo_fecha,
            TIME(hs.hora_salida) AS hora_salida_solo_hora,
            hs.tipo_usuario,
            CASE
                WHEN hs.tipo_usuario = 'almacenista' THEN a.nombres
                WHEN hs.tipo_usuario = 'administrador' THEN ad.nombres
                ELSE 'Usuario Desconocido'
            END AS nombres_usuario,
            CASE
                WHEN hs.tipo_usuario = 'almacenista' THEN a.apellidos
                WHEN hs.tipo_usuario = 'administrador' THEN ad.apellidos
                ELSE 'Apellido Desconocido'
            END AS apellidos_usuario
        FROM historial_sesiones hs
        LEFT JOIN almacenistas a ON hs.id_usuario = a.id_almacenista AND hs.tipo_usuario = 'almacenista'
        LEFT JOIN administradores ad ON hs.id_usuario = ad.id_administrador AND hs.tipo_usuario = 'administrador'";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY fecha_ingreso_solo_fecha DESC, hora_ingreso_solo_hora DESC";

$stmt = $conexion->prepare($sql);

if ($stmt === false) {
    die('Error en la preparación de la consulta: ' . $conexion->error);
}

if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();

$sesiones_por_fecha = [];
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $fecha = $fila['fecha_ingreso_solo_fecha'];
        if (!isset($sesiones_por_fecha[$fecha])) {
            $sesiones_por_fecha[$fecha] = [];
        }
        $sesiones_por_fecha[$fecha][] = $fila;
    }
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Sesiones | SENA</title>
    <link rel="stylesheet" href="Css/Historial_Sesiones.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
</head>
<body>
<header>
    <div class="header-left">
        <a href="Administrador.php" class="volver-button"><i class="fas fa-reply"></i> Regresar</a>
    </div>
    <div class="header-center">
        <img src="Img/logo_sena.png" alt="Logo SENA" class="logo">
        <h1>Historial de Sesiones</h1>
    </div>
    <div class="header-right">
        </div>
</header>
    <div class="container">
                    <div class="actions-container">
            <div class="filter-section">
                <form action="" method="GET" class="date-filter-form">
                    <div class="form-row">
                        <label for="fecha_inicio">Fecha Inicio:</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($fecha_inicio) ?>">
                    </div>
                    <div class="form-row">
                        <label for="fecha_fin">Fecha Fin:</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($fecha_fin) ?>">
                    </div>
                    <div class="form-buttons">
                        <button type="submit" class="filter-button"><i class="fas fa-filter"></i> Filtrar</button>
                        <a href="Historial_Sesiones.php" class="clear-filter-button"><i class="fas fa-redo"></i> Limpiar Filtro</a>
                    </div>
                </form>
            </div>
            <div class="export-buttons-group">
                <a href="Reportes/Exportar_Historial_Sesiones.php" class="export-button" title="EXPORTAR A EXCEL">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </a>
                <a href="Reportes/Exportar_Historial_Sesiones_PDF.php" class="export-button-pdf" title="EXPORTAR A PDF">
                    <i class="fas fa-file-pdf"></i> Exportar a PDF
                </a>
            </div>
        </div>
        <?php if (!empty($sesiones_por_fecha)): ?>
            <?php foreach ($sesiones_por_fecha as $fecha => $sesiones_del_dia): ?>
                <div class="date-group">
                   <summary> <h3>Fecha: <?= htmlspecialchars(date('d/m/Y', strtotime($fecha))) ?></h3> </summary>
                    <p>Total de Sesiones Registradas:<b> <?= count($sesiones_del_dia) ?></b></p>
                    <div class="table-responsive">
                    <table id="prestamosTable">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Tipo Usuario</th>
                                <th>Hora de Ingreso</th>
                                <th>Hora de Salida</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sesiones_del_dia as $fila): ?>
                                <tr>
                                    <td><?= htmlspecialchars($fila['nombres_usuario'] . ' ' . $fila['apellidos_usuario']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($fila['tipo_usuario'])) ?></td>
                                    <td><?= htmlspecialchars($fila['hora_ingreso_solo_hora']) ?></td>
                                    <td><?= htmlspecialchars($fila['hora_salida_solo_hora'] ?? 'Activo') ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($fecha))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-records">No hay registros de sesiones para mostrar.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conexion->close();
?>