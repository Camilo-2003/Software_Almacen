<?php
session_start();
session_unset(); // Limpia todas las variables de sesión
session_destroy(); // Destruye la sesión

// Redirección con una salida limpia
header("Location: ../login.html");
die(); // Asegura que el script se detenga aquí
?>
