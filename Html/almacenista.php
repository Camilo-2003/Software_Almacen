<?php

require_once __DIR__ . '/conexion.php';

session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION["id_almacenista"])) {
    header("Location: ../login.html");
    exit();
}

?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido Almacenista</title>
    <link rel="stylesheet" href="Css/almacenista.css">
</head>
<body>
    <header>
        <img src="Img\logo_sena.png" alt="Logo Sena" class="logo">
        <h1>Opciones Generales</h1>
        <nav>
        <a href="Php/logout.php" class="access-button">🏃  Cerrar Sesión</a>
        </nav> 
    </header>

    <main class="container">
    <h2>Hola, <?php echo $_SESSION["nombres"] . ' ' . $_SESSION["apellidos"]; ?> 👋</h2> <!--linea 53 login.php-->
        <h3>¿Qué deseas hacer hoy?</h3>
        <div class="options">
            <a href="préstamos.html" class="option">📤 Registrar Préstamos</a>
            <a href="Inventario.html" class="option">📋 Gestionar Inventario</a>
            <a href="GestionUsuarios.php" class="option">👨‍🏫 Gestión de Instructores</a>
            <a href="Novedades.html" class="option">🛠️ Novedades</a>
        </div>
    </main>
   
   <script src="Js/almacenista.js"></script>
</body>
</html>
