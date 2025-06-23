<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/Html/Conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$tipo_registro = isset($_POST["tipo_registro"]) ? $_POST["tipo_registro"] : '';

if ($tipo_registro == "Material") {
    $nombre = isset($_POST["nombre_material"]) ? trim($_POST["nombre_material"]) : '';
    $tipo = isset($_POST["tipo_material"]) ? $_POST["tipo_material"] : '';
    $stock = isset($_POST["stock_material"]) ? (int)$_POST["stock_material"] : 0;

    if (empty($nombre) || empty($tipo) || $stock <= 0) {
        echo "Todos los campos son obligatorios o la cantidad debe ser mayor a 0.";
        exit();
    }

    $sql = "INSERT INTO materiales (nombre, tipo, stock) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssi", $nombre, $tipo, $stock);

    if ($stmt->execute()) {
        echo "Material agregado correctamente.";
    } else {
        echo "Error al agregar el material.";
    }
    $stmt->close();

} elseif ($tipo_registro == "Equipo") {
    $marca = isset($_POST["marca"]) ? trim($_POST["marca"]) : '';
    $serial = isset($_POST["serial"]) ? trim($_POST["serial"]) : '';
    $estado = isset($_POST["estado"]) ? $_POST["estado"] : '';
    $stock = isset($_POST["stock_equipo"]) ? $_POST["stock_equipo"] : '';


    if (empty($marca) || empty($serial) || empty($estado) || empty($stock)) {
        echo "Todos los campos son obligatorios.";
        exit();
    }

    $sql = "INSERT INTO equipos (marca, serial, estado, stock) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssi", $marca, $serial, $estado, $stock);

    if ($stmt->execute()) {
        echo "Equipo agregado correctamente.";
    } else {
        echo "Error al agregar el equipo.";
    }
    $stmt->close();
} else {
    echo "Tipo de registro no válido.";
}
} else {
echo "Método de solicitud no permitido.";
}

$conexion->close();
?>