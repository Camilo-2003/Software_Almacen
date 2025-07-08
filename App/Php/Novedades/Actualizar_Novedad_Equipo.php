<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

// Redireccionar si no se accede por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../Novedades_Materiales.php?mensaje=error3");
    exit();
}

$id_novedad = filter_input(INPUT_POST, 'id_novedad2', FILTER_VALIDATE_INT);
$descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
$fecha_novedad = filter_input(INPUT_POST, 'fecha_novedad', FILTER_SANITIZE_STRING);
$nombre_instructor = filter_input(INPUT_POST, 'nombre_instructor', FILTER_SANITIZE_STRING);
$tipo_novedad = filter_input(INPUT_POST, 'tipo_novedad', FILTER_SANITIZE_STRING);
$ruta_imagen_existente = filter_input(INPUT_POST, 'imagen_existente', FILTER_SANITIZE_STRING); // Add a hidden input for existing image path

// Obtener datos del responsable de registro 
$id_responsable_registro = $_SESSION["id_almacenista"] ?? ($_SESSION["id_administrador"] ?? null);
$rol_responsable_registro = $_SESSION["rol"];
$nombre_responsable_registro = (isset($_SESSION["nombres"]) ? $_SESSION["nombres"] : '') . ' ' . (isset($_SESSION["apellidos"]) ? $_SESSION["apellidos"] : '');

if (!$id_novedad || empty($descripcion) || empty($fecha_novedad) || empty($nombre_instructor) || empty($tipo_novedad)) {
    header("Location: ../../Novedades_Equipos.php?mensaje=error3"); // Campos incompletos
    exit();
}


$imagen = $ruta_imagen_existente; 

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $nombre_archivo = basename($_FILES['imagen']['name']);
    $directorio_destino = $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Uploads/'; 
    $ruta_completa_destino = $directorio_destino . $nombre_archivo;

    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0755, true); 
    }

    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_completa_destino)) {
        $imagen = '/Software_Almacen/App/Uploads/' . $nombre_archivo;
    } else {
        error_log("Error al mover el archivo subido: " . $_FILES['imagen']['error']);
    }
} 

$conexion->begin_transaction();

try {
    // Preparar la consulta SQL para actualizar la novedad
    $sql_update = "UPDATE novedades2 SET 
                    descripcion = ?, 
                    fecha_novedad = ?, 
                    rol_responsable_registro = ?, 
                    nombre_responsable_registro = ?, 
                    nombre_instructor = ?, 
                    tipo_novedad = ?,
                    ruta_imagen = ?
                  WHERE id_novedad2 = ? AND tipo_elemento = 'equipo'";

    $stmt = $conexion->prepare($sql_update);

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de actualización: " . $conexion->error);
    }

    $stmt->bind_param("sssssssi", 
                        $descripcion, 
                        $fecha_novedad, 
                        $rol_responsable_registro, 
                        $nombre_responsable_registro, 
                        $nombre_instructor, 
                        $tipo_novedad, 
                        $imagen,
                        $id_novedad);

    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la actualización: " . $stmt->error);
    }

    $stmt->close();
    $conexion->commit();
    header("Location: ../../Novedades_Equipos.php?mensaje=actualizada");
    exit();

} catch (Exception $e) {
    $conexion->rollback();
    error_log("Error al actualizar novedad de material: " . $e->getMessage());
    header("Location: ../../Novedades_Equipos.php?mensaje=error_actualizacion");
    exit();
} finally {
    if ($conexion && $conexion->ping()) {
        $conexion->close();
    }
}
?>