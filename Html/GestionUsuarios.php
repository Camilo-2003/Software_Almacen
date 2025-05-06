<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="Css/G.usuarios.css">
</head>
<nav>              
    <a href="instructores.php" class="option">📋 Ver Instructores Registrados</a>
</nav>
<body>
    <div class="contain">
        <a href="almacenista.php" class="rgs" title="Haz clic para volver">Regresar</a>
    </div>
  
    <form action="Php/AgregarInstructor.php" method="POST" onsubmit="return validarFormulario()">
    <div class="container">
        <h2>Registrar Instructor👇</h2>
        <br>
        <label>Nombres:</label>
        <input type="text" id="nombre" name="nombre" placeholder="Nombre" required>
        <br><br>
        <label>Apellidos:</label>
        <input type="text" id="apellido" name="apellido" placeholder="Apellido" required>
        <br><br>
        <label>Correo:</label>
        <input type="email" id="correo" name="correo" placeholder="Correo Electrónico" required>
        <br><br>
        <label>Teléfono:</label>
        <input type="tel" id="telefono" name="telefono" placeholder="Teléfono" required>
        <br><br>
        <label>Ambiente:</label>
        <input type="text" id="ambiente" name="ambiente" placeholder="Ambiente" required>
        <br><br>
        <button type="submit" class="button1">Agregar Instructor</button>
    </div>
    </form>
    <script src="Js/gestionUsuarios.js"></script>
</body>
</html>
