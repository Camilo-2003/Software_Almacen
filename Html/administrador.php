<?php
require_once __DIR__ . '/Conexion.php';

session_start();
if (!isset($_SESSION["rol"])) {
    header("Location: Error.php");
    exit();
}
// Opcional: verifica si tiene permiso según su rol
if ($_SESSION["rol"] !== "administrador") {
    header("Location: Error.php");
    exit();
}
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// session_destroy();
// // header("Location: ../Error.php");

include_once __DIR__ . '/Php/Hora_ingreso.php';
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
          <a href="Php/Logout.php">🏃 Cerrar Sesión</a>
        </div>
      </details>
    </div>
  </nav>

  <p class="panel">Panel de administrador</p>
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
            <a href="Inventario.php" class="card">
                <i class="fas fa-box"></i>
                <div class="titulo">Inventario</div>
                <div class="descripcion">Administra materiales y equipos disponibles.</div>
            </a>
            <a href="Gestion_Usuarios.php" class="card">
                <i class="fas fa-users"></i>
                <div class="titulo">Usuarios</div>
                <div class="descripcion">Registrar y administrar instructores.</div>
            </a>

        <a href="Aceptar_Usuarios.php" class="card">
        <i class="fa-solid fa-user-plus"></i>
        <div class="titulo">Aceptar Usuarios</div>
        <div class="descripcion">Administra solicitudes de ingreso de nuevos usuarios al sistema.</div>
      </a>
    </div>
  </main>
 <br>
       <h2>Bienvenido</h2>

  <script src="Js/Administrador.js"></script>
</body>
</html>
