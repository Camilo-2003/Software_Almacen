<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión</title>
  <link rel="stylesheet" href="Css/login.css">
</head>
<body>

  <div id="loader">
    <div class="spinner"></div>
    <div class="loader-text">Cargando...</div>
  </div>

  <header>
    <img src="Img/logo_sena.png" alt="Logo Sena" class="logo">
    <h1>📦 Sistema de Almacén</h1>
    <div class="btn">
      <a href="index.html" class="btnn">Regresar</a>
  </div>
  </header>
 
  <div class="container">
    <h3>Iniciar Sesión <img src="Img/programming.gif" alt="Login"></h3> 
    <form id="loginForm" method="POST" action="Php/login2.php" autocomplete="off" onsubmit="return validarFormulario()">
      <label>Ingresar Correo Electrónico:</label>
      <input type="email" id="correo" name="correo" placeholder="Correo Electrónico" required>
      <label>Ingresar Contraseña:</label>
      <input type="password" id="password" name="password" placeholder="Contraseña" required>
      <button type="submit">Ingresar</button>
  </form>
  
  <p>¿No tienes cuenta? <a href="Registrarse.html" class="regist">Regístrate aquí</a></p>
  </div>

  <script src="Js/login.js"></script>
 
</body>
</html>
