<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

$id = intval($_GET['id']);

if ($id == true) {
    $delete = "DELETE FROM novedades WHERE id_novedad = ?";
    // $resultado = $conexion->query($delete);
    $stmt = $conexion->prepare($delete);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script> alert('✅ Novedad eliminada correctamente'); window.location.href='Historial_Novedades.php';</script>";
    } else {
        echo "<script> alert('❌ No se pudo eliminar la novedad'); window.location.href='Historial_Novedades.php';</script>";

    }
} else {
    echo "No se puedo ejecutar la peticion";
}
?>