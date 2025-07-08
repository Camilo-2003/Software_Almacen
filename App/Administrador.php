<?php
require_once __DIR__ . '/Conexion.php';

session_start();
if (!isset($_SESSION["rol"])) {
    header("Location: Error.php");
    exit();
}
if ($_SESSION["rol"] !== "administrador") {
    header("Location: Error.php");
    exit();
}
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once __DIR__ . '/Php/Hora_ingreso.php';

$sql_equipos = "SELECT estado, COUNT(*) AS count_by_status FROM equipos GROUP BY estado";
$result_equipos = $conexion->query($sql_equipos);
$equipo_counts = ['disponible' => 0, 'prestado' => 0, 'deteriorado' => 0, 'malo' => 0];
if ($result_equipos) {
    while ($row = $result_equipos->fetch_assoc()) {
        if (array_key_exists($row['estado'], $equipo_counts)) {
            $equipo_counts[$row['estado']] = (int)$row['count_by_status'];
        }
    }
}

$sqlTotalEquipos = "SELECT id_equipo, marca, serial, estado FROM equipos ORDER BY marca, serial"; 
$resultadoTotalEquipos = $conexion->query($sqlTotalEquipos);

$totalEquiposCount = 0;

if ($resultadoTotalEquipos) {
    $totalEquiposCount = $resultadoTotalEquipos->num_rows;
}
$sql_mat_tipo = "SELECT tipo, COUNT(*) AS count FROM materiales GROUP BY tipo";
$result_mat_tipo = $conexion->query($sql_mat_tipo);
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
$resultado2 = $conexion->query($total);
$total = $resultado2 ? $resultado2->fetch_assoc()['total'] : 0;

// Contadores de Materiales por Estado
$sql_mat_estado = "SELECT estado_material, COUNT(*) AS count FROM materiales GROUP BY estado_material";
$result_mat_estado = $conexion->query($sql_mat_estado);
$material_estado_counts = ['disponible' => 0, 'en_revision' => 0, 'descartado' => 0];
if ($result_mat_estado) {
    while ($row = $result_mat_estado->fetch_assoc()) {
        if (array_key_exists($row['estado_material'], $material_estado_counts)) {
            $material_estado_counts[$row['estado_material']] = (int)$row['count'];
        }
    }
}
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido Administrador</title>
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Css/Administrador.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
</head>
<body>
<div class="header-container">
  <nav>
<div class="fecha-hora">
  <?php echo obtenerFechaOrganizada($_SESSION["hora_ingreso"] ?? ''); ?>
</div>
    <div class="user-info">
      <i class="fa-solid fa-circle-user" id="user"></i> 
      <span><?php echo $_SESSION["nombres"] . ' ' . $_SESSION["apellidos"]; ?></span>
    </div>
    <div class="header-right">
      <details>
        <summary><i class="fa-solid fa-arrow-right-from-bracket" id="close"></i> </summary>
        <div class="dropdown-content">
          <a href="Php/Logout.php"><i class="fas fa-door-open" id="cerrar"></i> Cerrar Sesión</a>
          <!-- <a href="Cambiar_contraseña.php" class="cambiarcontrasena">🔑 Cambiar Contraseña</a> -->
        </div>
      </details>
    </div>
  </nav>

  <p class="panel">Panel de administrador</p>
</div>
    <main class="main-content">
        <div class="dashboard">
            <a href="Administrador.php" class="card">
                <i class="fa-solid fa-house"></i>
                <div class="titulo">Inicio</div>
                <div class="descripcion">Pagina principal</div>
            </a>
            <a href="Préstamos.php" class="card">
                <i class="fas fa-exchange-alt"></i>
                <div class="titulo">Préstamos</div>
                <div class="descripcion">Registra, consulta o devuelve material o equipo.</div>
            </a>
            <a href="Php/Inventario/Inventario.php" class="card">
                <i class="fas fa-box"></i>
                <div class="titulo">Inventario</div>
                <div class="descripcion">Administra materiales y equipos disponibles.</div>
            </a>
            <a href="Gestion_Instructores.php" class="card3">
                <i class="fas fa-users"></i>
                <div class="titulo">Instructores</div>
                <div class="descripcion">Registrar y administrar instructores.</div>
            </a>
              <a href="Novedades_Equipos.php" class="card3">
              <i class="fas fa-laptop"></i>
                <div class="titulo">Novedades Equipos</div>
                <div class="descripcion">Visualiza y administra tus novedades de equipos.</div>
            </a>
        <a href="Usuarios.php" class="card3">
        <i class="fa-solid fa-user-plus"></i>
        <div class="titulo">Administrar Usuarios</div>
        <div class="descripcion">Registrar y administrar nuevos usuarios para que puedan tener acceso al sistema.</div>
      </a>
        <a href="Historial_Sesiones.php" class="card2">
        <i class="fa-solid fa-hourglass-start"></i>
        <div class="titulo2">Historial Sesiones</div>
        <div class="descripcion2">Ver el historial de acceso al sistema de cada usuario.</div>
      </a>
        <a href="Novedades_Materiales.php" class="card2">
        <i class="fas fa-boxes"></i> 
        <div class="titulo2">Novedades Materiales</div>
        <div class="descripcion2">Visualiza y administra tus novedades de materiales.</div>
      </a>
           <a class="card2">
        <i class="fas fa-laptop"></i>
        <div class="titulo3">Total de Equipos: <b><?= htmlspecialchars($totalEquiposCount)?></b></div>
      </a>
           <a class="card2">
            <i class="fas fa-laptop"></i>
        <div class="titulo3">Equipos Disponibles: <b><?php echo $equipo_counts['disponible']; ?></b></div>
      </a>
           <a class="card2">
        <i class="fas fa-laptop"></i>
        <div class="titulo3">Equipos Préstados: <b><?php echo $equipo_counts['prestado']; ?></b></div>
      </a>
           <a class="card2">
          <i class="fas fa-laptop"></i>
        <div class="titulo3">Equipos Deteriorados: <b><?php echo $equipo_counts['deteriorado']; ?></b></div>
      </a>
           <a class="card2">
        <i class="fas fa-laptop"></i>
        <div class="titulo3">Equipos Malos: <b><?php echo $equipo_counts['malo'];?></b></div>
      </a>
           <a class="card2">
        <i class="fa-solid fa-boxes-packing"></i>
        <div class="titulo3">Total de Materiales: <b><?php echo $total; ?></b></div>
      </a>
        <a class="card2">
        <i class="fa-solid fa-boxes-packing"></i>
        <div class="titulo3">Consumibles: <b><?php echo $material_tipo_counts['consumible'];?></b></div>
      </a>
        <a class="card2">
        <i class="fa-solid fa-boxes-packing"></i>
        <div class="titulo3">No Consumibles: <b><?php echo $material_tipo_counts['no consumible']; ?></b></div>
      </a>
    </div> 
  </main> 
  <script src="Js/Administrador.js"></script>
</body>
</html>
