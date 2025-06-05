<?php
include("ProhibirAcceso.php");
?>
    <meta charset="UTF-8">
    <link rel="icon" href="Img/logo_sena.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="Css/G.usuarios.css">
    
<header>
<img src="Img/logo_sena.png" alt="Logo Sena" class="logo">
<h1>📤 Gestion de instructores</h1>
<div class="contain">
    <a href="<?php echo $pagina_regresar; ?>"  class="rgs" title="Haz clic para volver">Regresar</a>
</div>
</header> 
<div>              
<a href="Instructores.php" class="option">📋 Ver Instructores Registrados</a>
</div>
    
  
    <form action="Php/Agregar_Instructor.php" method="POST" onsubmit="return validarFormulario()">
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
        <input type="number" id="telefono" name="telefono" placeholder="Teléfono" required pattern="[0-9]{10}" maxlength="10" title="Debe ser un número de 10 dígitos">
        <br><br>
        <label>Ambiente:</label>
        <input type="text" id="ambiente" name="ambiente" placeholder="Ambiente" required>
        <br><br>
        <button type="submit" class="button1">Agregar Instructor</button>
    </div>
    </form>
    <script src="Js/GestionUsuarios.js"></script>

