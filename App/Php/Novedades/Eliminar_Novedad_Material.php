<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

$id_novedad = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id_novedad) {
    header("Location: ../../Novedades_Materiales.php?mensaje=error_id");
    exit();
}

$conexion->begin_transaction();

try {
    // Eliminar la novedad
    $sql_delete = "DELETE FROM novedades2 WHERE id_novedad2 = ? AND tipo_elemento = 'material'";
    $stmt = $conexion->prepare($sql_delete);

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de eliminación: " . $conexion->error);
    }

    $stmt->bind_param("i", $id_novedad);

    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la eliminación: " . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception("Novedad no encontrada o ya eliminada.");
    }

    $stmt->close();
    $conexion->commit();
    header("Location: ../../Novedades_Materiales.php?mensaje=eliminada");
    exit();

} catch (Exception $e) {
    $conexion->rollback();
    error_log("Error al eliminar novedad de material: " . $e->getMessage());
    header("Location: ../../Novedades_Materiales.php?mensaje=error2"); 
    exit();
} finally {
    if ($conexion && $conexion->ping()) {
        $conexion->close();
    }
}
?>