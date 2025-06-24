<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

session_start();

if (!isset($_SESSION["rol"])) {
    header("Location: Login.php");
    exit();
}
if ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador") {
    header("Location: Error.php");
    exit();
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
                ELSE ''
            END AS apellidos_usuario
        FROM historial_sesiones hs
        LEFT JOIN almacenistas a ON hs.id_usuario = a.id_almacenista AND hs.tipo_usuario = 'almacenista'
        LEFT JOIN administradores ad ON hs.id_usuario = ad.id_administrador AND hs.tipo_usuario = 'administrador'
        ORDER BY hs.hora_ingreso DESC"; //hora de ingreso más reciente primero

$resultado = $conexion->query($sql);

$historial_por_fecha = [];
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $fecha_grupo = $fila['fecha_ingreso_solo_fecha']; 
        if (!isset($historial_por_fecha[$fecha_grupo])) {
            $historial_por_fecha[$fecha_grupo] = [];
        }
        $historial_por_fecha[$fecha_grupo][] = $fila;
    }
}
krsort($historial_por_fecha);

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
    <div class="container">
        <h2>Historial de Sesiones del Sistema</h2>
        <div class="class-btn">
            <a href="Reportes/Exportar_Historial_Sesiones.php" class="export-button">
            <i class="fas fa-file-excel"></i> Exportar a Hojas de Cálculo
            </a>
        </div>
        <?php if (!empty($historial_por_fecha)): ?>
            <?php foreach ($historial_por_fecha as $fecha => $sesiones_del_dia): ?>
                <!-- <details> -->
                <div class="date-group">
                   <summary> <h3>Fecha: <?= htmlspecialchars(date('d/m/Y', strtotime($fecha))) ?></h3> </summary>
                    <p>Total de Sesiones Registradas:<b> <?= count($sesiones_del_dia) ?></b></p>
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
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-records">No hay registros de sesiones para mostrar.</p>
        <?php endif; ?>
        <!-- </details> -->
        <?php 
        $volver_link = '';
        if (isset($_SESSION["rol"])) {
            if ($_SESSION["rol"] === "administrador") {
                $volver_link = "Administrador.php";
            } elseif ($_SESSION["rol"] === "almacenista") {
                $volver_link = "Almacenista.php";
            }
        }
        if (!empty($volver_link)) {
            echo '<a href="' . htmlspecialchars($volver_link) . '" class="volver-button">Volver al Panel Principal</a>';
        } else {
            echo '<a href="Login.php" class="volver-button">Volver al Login</a>';
        }
        ?>
    </div>
</body>
</html>
<?php
$conexion->close();
?>