<?php
session_start();

// Verificar que haya sesión y que el rol sea válido
if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("location: Error.php");
    exit();
}

$pagina_regresar = '';

if ($_SESSION["rol"] === "almacenista") {
    $pagina_regresar = "almacenista.php";
} elseif ($_SESSION["rol"] === "administrador") {
    $pagina_regresar = "administrador.php";
} else {
    $pagina_regresar = "Error.php"; // Opcional por si hay un rol no permitido
}
?>
