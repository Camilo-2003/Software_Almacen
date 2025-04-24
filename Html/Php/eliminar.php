<?php
$conexion = new mysqli("localhost", "root", "", "almacen");

if ($conexion->connect_errno) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}

if (isset($_GET['tipo']) && isset($_GET['id'])) {
    $tipo = $_GET['tipo'];
    $id = intval($_GET['id']);

    if ($tipo === 'material') {
        $sql = "DELETE FROM materiales WHERE id_material = $id";
    } elseif ($tipo === 'equipo') {
        $sql = "DELETE FROM equipos WHERE id_equipo = $id";
    } else {
        die("❌ Tipo no válido.");
    }

    if ($conexion->query($sql)) {
        header("Location: ../Php/historial.php");
        exit();
    } else {
        echo "❌ Error al eliminar: " . $conexion->error;
    }
} else {
    echo "❌ Parámetros inválidos.";
}

$conexion->close();
?>

