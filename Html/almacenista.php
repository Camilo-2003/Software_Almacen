<?php

require_once __DIR__ . '/Conexion.php';

session_start();
// Verificar si el usuario está autenticado si no lo redirecciona
if (!isset($_SESSION["rol"])) {
    header("Location: Error.php");
    exit();
}
// Opcional: verifica si tiene permiso según su rol
if ($_SESSION["rol"] !== "almacenista" ) {
    header("Location: Error.php");
    exit();
}
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once __DIR__ . '/Php/Hora_ingreso.php';

?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido Almacenista</title>
    <link rel="stylesheet" href="Css/Almacenista.css"> 
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

    <p class="panel">Panel de almacenista</p>

    <main class="main-content">
        <div class="dashboard">
            <a href="Almacenista.php" class="card">
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
            <a href="Novedades.php" class="card">
                <i class="fas fa-chart-line"></i>
                <div class="titulo">Novedades</div>
                <div class="descripcion">Visualiza reportes de daños, pérdidas y otros eventos.</div>
            </a>
        </div>
    </main>
    <br>
    <h2>Bienvenido</h2>
    <script src="Js/Almacenista.js"></script>
    
    </body>
</html>
