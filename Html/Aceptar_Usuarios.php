<?php
include("ProhibirAcceso.php");

if ($_SESSION["rol"] !== "administrador") {
    header("Location: Error.php");
    exit();
}
?>

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
</script>

