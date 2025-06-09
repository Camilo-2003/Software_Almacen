<?php
include("ProhibirAcceso.php");

if ($_SESSION["rol"] !== "administrador") {
    header("Location: Error.php");
    exit();
}
?>
<!-- 
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aceptar Usuarios</title>
    <link rel="stylesheet" href="Css/AceptarUsuarios.css">
<div class="container">
    <h2>Solicitudes de acceso al sistema</h2><br>
<form action="" method="post">
<label>Numero de solicitud</label><br>
<p>Solicitud # ...</p>
<label>Información del solicitante</label><br>
<p>Datos ...</p><br>
<button class="acept">✅Aceptar</button>
<button class="delet">❌No Aceptar</button>
</form>

</div>
<script>
//Evita el mensaje de confirmar envio de formulario nuevamente
window.history.replaceState(null, null, window.location.pathname);
</script> -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario De Registro</title>
    <link rel="stylesheet" href="Css/Registrarse.css">
</head>
<body>
    <header>
    <div class="btn">
      <a href="Administrador.php" class="btnn">Regresar</a>
  </div>
  </header>

  <div id="loader">
    <div class="spinner"></div>
    <div class="loader-text">Cargando...</div> 
  </div>
  <br>
<div class="contain">
    <form action="Php/Guardar_Registrarse.php" method="POST" onsubmit="return validarFormulario()">
        <h2>Crear Cuenta</h2>
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]{2,}" title="El nombre debe tener al menos 2 letras" placeholder="Nombres">
        <label for="apellido">Apellido:</label>
        <input type="text" id="apellido" name="apellido" required pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]{2,}" title="El apellido debe tener al menos 2 letras" placeholder="Apellidos">
        <label for="correo">Correo Electrónico:</label>
        <input type="email" id="correo" name="correo" required placeholder="Correo">
        <label for="telefono">Teléfono:</label>
        <input type="number" id="telefono" name="telefono" required pattern="[0-9]{10}" maxlength="10" title="Debe ser un número de 10 dígitos" placeholder="Telefono">
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required placeholder="Contraseña">
        <label for="confirm-password">Confirmar Contraseña:</label>
        <input type="password" id="confirm-password" name="confirm-password" required placeholder="Confirmar Contraseña">
      
        <div class="botones">
            <button type="submit">Guardar</button>
            <button type="button" onclick="cancelarFormulario()">Cancelar</button>
        </div>
    </form>
    </div>

    <script src="Js/Registrarse.js"></script>
</body> 
</html>
