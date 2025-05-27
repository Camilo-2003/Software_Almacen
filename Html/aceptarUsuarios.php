<?php
include("prohibirAcceso.php");

if ($_SESSION["rol"] !== "administrador") {
    header("Location: Error.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aceptar Usuarios</title>
</head>
<body>
    <h1>Solicitudes De Acceso AL Sistema</h1>
    <div>
<h2>Numero de solicitud</h2>
<br>
<h2>Información del solicitante</h2>
<br>
<h2>Tipo De Usuario</h2>
<br>
<button>✅Aceptar</button>
<button>❌No Aceptar</button>

    </div>
</body>
</html>