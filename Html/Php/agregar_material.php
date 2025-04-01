<?php

require_once __DIR__ . '/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $tipo = $_POST["tipo"];
    $stock = $_POST["stock"];

    $sql = "INSERT INTO materiales (nombre, tipo, stock) VALUES ('$nombre', '$tipo', '$stock')";

    if ($conexion->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Material agregado correctamente"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $conexion->error]);
    }
}

$conexion->close();
?>
