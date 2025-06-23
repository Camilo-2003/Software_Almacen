<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$id = intval($_POST['id']);

   if ($id == true) {
    $tipo = $_POST['tipoNovedad'];
    $descripcion = $_POST['descripcion'];
    $id_instructor = $_POST['id_instructor'];
    $instructor = $_POST['instructor'];
    $id_responsable = $_POST['id_responsable'];
    $responsable = $_POST['rol_responsable'];
    $nombre_responsable = $_POST['nombre_responsable'];
    $observaciones = $_POST['observaciones'];

    $sql = "UPDATE novedades SET tipo=?, descripcion=?, id_instructor=?, nombre_instructor=?, id_responsable=?, rol_responsable=?, nombre_responsable=?, observaciones=? WHERE id_novedad=$id";
    $stmt = $conexion->prepare($sql);

    if ($stmt === false) {
        echo "Error al preparar la consulta: " . $conexion->error;
        exit();
    }

    $stmt->bind_param("ssisisss", $tipo, $descripcion, $id_instructor, $instructor, $id_responsable, $responsable, $nombre_responsable, $observaciones);

    if ($stmt->execute()) {

        echo "<script> alert('✅ Novedad actualizada correctamente'); window.location.href='Historial_Novedades.php';</script>";
        exit();
    } else {
        echo "❌ Error al actualizar: " . $stmt->error;
    }


    $stmt->close();
}


$conexion->close();


?>