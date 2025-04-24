<?php
print_r($_POST);
?>

<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/conexion.php';
//verifica que se haya enviando mediante el metodo post
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_registro = $_POST["tipo_registro"];
    
//si se selecciona la opción de "Material" muestra los campos de material
    if ($tipo_registro == "Material") {
        $nombre = $_POST["nombre_material"];
        $tipo = $_POST["tipo_material"];
        $stock = $_POST["stock_material"];

//No se permiten campos nulos
if (empty($nombre) || empty($tipo) || empty($stock)) {
    echo "Todos los campos son obligatorios.";
    exit();
}
//Se inserta el material en la tabla materiales en la bdd
        $sql = "INSERT INTO materiales (nombre, tipo, stock) VALUES ('$nombre', '$tipo', '$stock')";
        $conexion->query($sql);

    } 
    elseif ($tipo_registro == "Equipo") { //si se selecciona la opción de "Equipo" muestra los campos de equipo
        $marca = $_POST["marca"];
        $serial = $_POST["serial"];
        $estado = $_POST["estado"];
//No se permiten campos nulos

        if (empty($marca) || empty($serial) || empty($estado)) {
            echo "Todos los campos son obligatorios";
            exit();
        }
        //Se inserta el equipo en la tabla equipos en la bdd

        $sql = "INSERT INTO equipos (marca, serial, estado) VALUES ('$marca', '$serial', '$estado')";
        $conexion->query($sql);

      
    }
//redireccion después de agregar el material o equipo
    header("Location: ../inventario.html");
    exit();
}
?>
