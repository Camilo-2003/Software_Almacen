<?php

require_once __DIR__ . '/conexion.php';

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
    <P class="rol">Panel de Almacenista</P>

    <main class="container">
    <h2>Bienvenido, <?php echo $_SESSION["nombres"] . ' ' . $_SESSION["apellidos"]; ?> 👋</h2> <!--linea 53 login.php-->
        <h3>¿Qué deseas hacer hoy?</h3>
        <div class="options">
            <a href="préstamos.php" class="option">📤 Registrar Préstamos</a>
            <a href="Inventario.php" class="option">📋 Gestionar Inventario</a>
            <a href="GestionUsuarios.php" class="option">👨‍🏫 Gestión de Instructores</a>
            <a href="Novedades.php" class="option">🛠️ Novedades</a>
        </div>
</main>
    
   <script src="Js/almacenista.js"></script> 
</body>
</html>

<!-- 
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bienvenido Almacenista</title>
  <link rel="stylesheet" href="Css/almacenista.css">
  <style>
    #contenido {
      margin-top: 20px;
      padding: 20px;
      border: 2px solid #ccc;
      background-color: #f5f5f5;
      min-height: 300px;
    }
    .option {
      cursor: pointer;
    }
  </style>
</head>
<body> 
  <header>
    <img src="Img/logo_sena.png" alt="Logo Sena" class="logo">
    <h1>Opciones Generales</h1>
    <nav>
      <a href="Php/logout.php" class="access-button">🏃 Cerrar Sesión</a>
    </nav> 
  </header>

  <p class="rol">Panel de Almacenista</p>

  <main class="container">
    <h2>Bienvenido, <?php echo $_SESSION["nombres"] . ' ' . $_SESSION["apellidos"]; ?> 👋</h2>
    <h3>¿Qué deseas hacer hoy?</h3>

    <div class="options">
      <a onclick="mostrar('préstamos.php')" class="option">📤 Registrar Préstamos</a>
      <a onclick="mostrar('Inventario.php')" class="option">📋 Gestionar Inventario</a>
      <a onclick="mostrar('GestionUsuarios.php')" class="option">👨‍🏫 Gestión de Instructores</a>
      <a onclick="mostrar('Novedades.php')" class="option">🛠️ Novedades</a>
    </div>

    <div id="contenido">
      <p>Selecciona una opción para ver el contenido aquí.</p>
    </div>
  </main>

  <script>
    function mostrar(pagina) {
      fetch(pagina)
        .then(response => {
          if (!response.ok) {
            throw new Error("No se pudo cargar la página: " + response.status);
          }
          return response.text();
        })
        .then(html => {
          document.getElementById("contenido").innerHTML = html;
        })
        .catch(error => {
          document.getElementById("contenido").innerHTML = "<p style='color:red;'>Error: " + error.message + "</p>";
        });
    }
  </script>
</body>
</html> -->