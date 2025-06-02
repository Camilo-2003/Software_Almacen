<?php
session_start(); // Inicia la sesión, Verifica si la sesión está activa
session_unset(); // Limpia todas las variables de sesión
session_destroy(); // Destruye la sesión

// Prevenir caché
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirección con una salida limpia a la pagina de login
header("Location: ../Login.php?mensaje=cierre"); //mensaje de cierre esta en login.js
exit(); // Asegura que el script se detenga aquí
?>
  