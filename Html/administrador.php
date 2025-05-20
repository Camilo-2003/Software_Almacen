<?php
require_once __DIR__ . '/conexion.php';
session_start();
if (!isset($_SESSION["id_almacenista"])) {
    header("Location: ../login.html");
    exit();
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
        <p class="hr">Hora Entrada:</p>
        <p class="hr">Hora Salida</p>
    </header>
    <br>
    <div class="sidebar">
        <h1 class="panel">Panel de Administrador</h1>
        <img src="Img/logo_sena.png" alt="Logo Sena" class="logo">
        <p class="username">👋 Hola, <?php echo $_SESSION["nombres"] . ' ' . $_SESSION["apellidos"]; ?></p>
        <nav class="nav-menu">
            <a class="aa" href="administrador.php">🏡 Principal</a>
            <a href="#" class="aa" onclick="cargarPagina('préstamos.html')">📤 Registrar Préstamos</a>
            <a href="#" class="aa" onclick="cargarPagina('Inventario.html')">📋 Gestionar Inventario</a>
            <a href="#" class="aa" onclick="cargarPagina('GestionUsuarios.php')">👨‍🏫 Gestión de Instructores</a>
            <a href="#" class="aa" onclick="cargarPagina('Novedades.html')">🛠️ Novedades</a>
            <a href="#" class="aa" onclick="cargarPagina('Aceptar.html')">✍️ Aceptar Almacenistas Nuevos</a>
            <a href="Php/logout.php" class="logout">🏃 Cerrar Sesión</a>
        </nav>
    </div>
    <br>
    <br>

    <main class="main-content">
        <section>
            <iframe id="contenido" src=""></iframe>
        </section>
    </main>

    <script>
        function cargarPagina(pagina) {
            document.getElementById("contenido").src = pagina;
        }
    </script>

</body>
</html>
