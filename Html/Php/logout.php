<?php
session_start();
session_unset(); // Limpia todas las variables de sesión
session_destroy(); // Destruye la sesión

// Redirección con una salida limpia a la pagina de login
header("Location: ../login.html?mensaje=cierre"); 
die(); // Asegura que el script se detenga aquí
?>
