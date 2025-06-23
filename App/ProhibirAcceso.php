<?php
session_start();

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("location: /Software_Almacen/App/Error.php");
    exit();
}
$pagina_regresar = '';

// Ruta absoluta desde la raíz de mi proyecto 
if ($_SESSION["rol"] === "almacenista") {
    $pagina_regresar = "/Software_Almacen/App/Almacenista.php";
} elseif ($_SESSION["rol"] === "administrador") {
    $pagina_regresar = "/Software_Almacen/App/Administrador.php";
} else {
    $pagina_regresar = "/Software_Almacen/Error.php";
}
?>
