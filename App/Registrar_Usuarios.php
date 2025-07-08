<?php
include("ProhibirAcceso.php");

if ($_SESSION["rol"] !== "administrador") {
    header("Location: Error.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario De Registro | SENA</title>
    <link rel="stylesheet" href="vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="Css/Registrar_Usuarios.css">
    <script src="Js/Mensajes.js" defer></script>
</head>
<body>
    <header>
    <div class="regresar">
      <a href="Usuarios.php" class="rgs"><i class="fas fa-reply"></i> Regresar</a>
  </div>
</header>
  <br>
<div class="contain">
    <form action="Php/Guardar_Registrarse.php" method="POST" onsubmit="return validarFormulario()">
        <h2>Crear Cuenta</h2>
        <label for="rol">Rol del nuevo usuario: </label>
        <select name="rol" required>
            <option value="">Seleccione una opción</option>
            <option value="administrador">Administrador</option>
            <option value="almacenista">Almacenista</option>
        </select>
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

    <script src="Js/Registrar_Usuarios.js"></script>
</body> 
</html>
