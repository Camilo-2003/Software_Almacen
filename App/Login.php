<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión</title>
  <link rel="stylesheet" href="Css/Login.css">
  <link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
</head>
<body>
  <header>
    <img src="Img/logo_sena.png" alt="Logo Sena" class="logo">
    <h1>Sistema de Almacén</h1>
    <div class="btn">
      <a href="index.html" class="btnn"><i class="fas fa-reply"></i> Regresar</a>
  </div>
  </header>
 
  <div class="container">
    <h3>Iniciar Sesión <img src="Img/programming.gif" alt="Login"></h3> 
    <form id="loginForm" method="POST" action="Php/Loguearse.php" autocomplete="off" onsubmit="return validarFormulario()">
      <label>Ingresar Correo Electrónico:</label>
      <input type="email" id="correo" name="correo" placeholder="Correo Electrónico" required>
      <label>Ingresar Contraseña:</label>
      <input type="password" id="password" name="password" placeholder="Contraseña" required>
      <span id="ver"><i id="icono" class="fas fa-eye"></i></span>
      <button type="submit">Ingresar</button>
  </form>
  <!-- <p>¿Olvidaste tu Contraseña? <a href="Php/Contraseña/Recuperar_Password.php" class="regist">Ingresa aquí</a></p> -->
  <!-- <p>¿No tienes cuenta? <a href="Registrarse.html" class="regist">Regístrate aquí</a></p> -->
  </div>
  <p><a href="olvidaste.html" class="regist">¿Olvidaste tu contraseña?</a></p>
  <script src="Js/Login.js"></script>
 
</body>
</html>
