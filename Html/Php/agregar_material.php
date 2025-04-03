<?php
print_r($_POST);
?>

<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/conexion.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_registro = $_POST["tipo_registro"];

    if ($tipo_registro == "Material") {
        $nombre = $_POST["nombre_material"];
        $tipo = $_POST["tipo_material"];
        $stock = $_POST["stock_material"];

        if (empty($nombre) || empty($tipo) || empty($stock)) {
            echo "Todos los campos son obligatorios";
            exit();
        }

        $sql = "INSERT INTO materiales (nombre, tipo, stock) VALUES ('$nombre', '$tipo', '$stock')";
        $conexion->query($sql);

        $sqlHistorial = "INSERT INTO historial (nombre, cantidad, fecha, hora, tipo) 
                         VALUES ('$nombre', '$stock', NOW(), NOW(), 'Entrada')";
        $conexion->query($sqlHistorial);
    } 
    elseif ($tipo_registro == "Equipo") {
        $marca = $_POST["marca"];
        $serial = $_POST["serial"];
        $estado = $_POST["estado"];

        if (empty($marca) || empty($serial) || empty($estado)) {
            echo "Todos los campos son obligatorios";
            exit();
        }

        $sql = "INSERT INTO equipos (marca, serial, estado) VALUES ('$marca', '$serial', '$estado')";
        $conexion->query($sql);

        $sqlHistorial = "INSERT INTO historial (nombre, cantidad, fecha, hora, tipo) 
                         VALUES ('$serial', '1', NOW(), NOW(), 'Entrada')";
        $conexion->query($sqlHistorial);
    }

    header("Location: ../inventario.html");
    exit();
}
?>
