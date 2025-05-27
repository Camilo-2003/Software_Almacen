<?php
require_once __DIR__ . '/conexion.php';

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
function obtenerFechaOrganizada($fechaHora){
    date_default_timezone_set('America/Bogota');

    $dias = [
        "Sunday" => "Domingo",
        "Monday" => "Lunes",
        "Tuesday" => "Martes",
        "Wednesday" => "Miércoles",
        "Thursday" => "Jueves",
        "Friday" => "Viernes",
        "Saturday" => "Sábado"
    ];
    $meses = [
        "01" => "enero", "02" => "febrero", "03" => "marzo", "04" => "abril", 
        "05" => "mayo", "06" => "junio", "07" => "julio", "08" => "agosto",
        "09" => "septiembre", "10" => "octubre", "11" => "noviembre", "12" => "diciembre"  
    ];
    $timestamp = strtotime($fechaHora);
    $diaSemana = $dias[date("l", $timestamp)];
    $dia = date("d", $timestamp);
    $mes = $meses[date("m", $timestamp)];
    $año = date("Y", $timestamp);
    $hora = date("h:i A", $timestamp);

    return "<b class='fecha'>🗓 Fecha:</b><b class='vv'>$diaSemana, $dia de $mes de $año.</b>".'<br>' ."<b class='hora'>🕒 Hora de ingreso:</b> <b class='vvv'> $hora</b>";
}

$hora_ingreso = '';
$correo = $_SESSION["correo"] ?? null;
if ($correo) {
    $sql = "SELECT hora_ingreso FROM administradores WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->bind_result($hora_ingreso);
        $stmt->fetch();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido Administrador</title>
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Css/administrador.css">
</head>
<body>
    <header>
      <h1>¿Qué deseas hacer hoy?</h1>
    </header>
    <br>
    <div class="sidebar">
        <h1 class="panel">Panel de Administrador</h1>
        <img src="Img/logo_sena.png" alt="Logo Sena" class="logo">
        <p class="username">👋 Hola, <?php echo $_SESSION["nombres"] . ' ' . $_SESSION["apellidos"]; ?></p>
        <nav class="nav-menu">
            <a class="aa" href="administrador.php">🏡 Principal</a>
            <a href="#" class="aa" onclick="cargarPagina('préstamos.php')">📤 Registrar Préstamos</a>
            <a href="#" class="aa" onclick="cargarPagina('Inventario.php')">📋 Gestionar Inventario</a>
            <a href="#" class="aa" onclick="cargarPagina('GestionUsuarios.php')">👨‍🏫 Gestión de Instructores</a>
            <a href="#" class="aa" onclick="cargarPagina('Novedades.php')">🛠️ Novedades</a>
            <a href="#" class="aa" onclick="cargarPagina('aceptarUsuarios.php')">✍️ Aceptar Almacenistas Nuevos</a>
            <a href="Php/logout.php" class="logout">🏃 Cerrar Sesión</a>
            <p class="hora"><strong><?php echo obtenerFechaOrganizada($_SESSION["hora_ingreso"] ?? ''); ?></strong></p>
        </nav>
    </div>
    <br>
    <br> 
    <main class="main-content">
        <section>
            <iframe id="contenido" src=""></iframe>
        </section>
    </main>
  <script src="Js/administrador.js"></script>
    
</body>
</html>
