<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php'; 

$response = ['success' => false, 'message' => ''];

// --- Validación de Sesión ---
$id_responsable_registro = 0;
$rol_responsable_registro = '';
$nombre_responsable_registro = '';
if (isset($_SESSION['rol'])) {
    $rol_responsable_registro = $_SESSION['rol'];
    $nombre_completo = trim((isset($_SESSION["nombres"]) ? $_SESSION["nombres"] : '') . ' ' . (isset($_SESSION["apellidos"]) ? $_SESSION["apellidos"] : ''));
    $nombre_responsable_registro = $nombre_completo;
    if ($rol_responsable_registro === 'almacenista' && isset($_SESSION['id_almacenista'])) {
        $id_responsable_registro = $_SESSION['id_almacenista'];
    } elseif ($rol_responsable_registro === 'administrador' && isset($_SESSION['id_administrador'])) {
        $id_responsable_registro = $_SESSION['id_administrador'];
    }
}
if ($id_responsable_registro === 0) {
    $response['message'] = 'Acceso denegado. Su sesión ha expirado.';
    echo json_encode($response);
    exit();
}

$conexion->begin_transaction();

try {
    // Los datos ahora vienen de $_POST porque usamos FormData
    if (!isset($_POST['id_prestamo_equipo_detalle'], $_POST['tipo_novedad'], $_POST['descripcion'])) {
        throw new Exception('Datos de entrada incompletos.');
    }

    $id_prestamo_equipo_detalle = intval($_POST['id_prestamo_equipo_detalle']);
    $tipo_novedad = $conexion->real_escape_string($_POST['tipo_novedad']);
    $descripcion = $conexion->real_escape_string($_POST['descripcion']);

    //MANEJO DE LA SUBIDA DE ARCHIVO ---
    $ruta_imagen_db = null;
    if (isset($_FILES['novedad_file']) && $_FILES['novedad_file']['error'] == 0) {
        $archivo = $_FILES['novedad_file'];
        $limite_mb = 5;
        if ($archivo['size'] > $limite_mb * 1024 * 1024) {
            throw new Exception("El archivo es demasiado grande. Límite: {$limite_mb}MB.");
        }

        $tipos_permitidos = ['image/jpeg', 'image/png'];
        if (!in_array($archivo['type'], $tipos_permitidos)) {
            throw new Exception("Tipo de archivo no permitido. Solo se aceptan JPG y PNG.");
        }

        $directorio_subida = $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Uploads/';
        if (!is_dir($directorio_subida)) {
            mkdir($directorio_subida, 0777, true);
        }

        $nombre_archivo_unico = uniqid() . '-' . basename($archivo['name']);
        $ruta_destino = $directorio_subida . $nombre_archivo_unico;

        if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
            // Guardamos la ruta relativa 
            $ruta_imagen_db = '/Software_Almacen/App/Uploads/' . $nombre_archivo_unico;
        } else {
            throw new Exception('Error al mover el archivo subido.');
        }
    }
    // Obtener información adicional (id_prestamo, id_equipo, etc.)
    $stmt_info_prestamo = $conexion->prepare("SELECT ped.id_prestamo_equipo, ped.id_equipo, pe.id_instructor, e.marca AS marca_equipo, CONCAT(i.nombre, ' ', i.apellido) AS nombre_instructor FROM prestamo_equipos_detalle ped JOIN prestamo_equipos pe ON ped.id_prestamo_equipo = pe.id_prestamo_equipo JOIN equipos e ON ped.id_equipo = e.id_equipo JOIN instructores i ON pe.id_instructor = i.id_instructor WHERE ped.id_prestamo_equipo_detalle = ?");
    $stmt_info_prestamo->bind_param("i", $id_prestamo_equipo_detalle);
    $stmt_info_prestamo->execute();
    $info = $stmt_info_prestamo->get_result()->fetch_assoc();
    $stmt_info_prestamo->close();
    if (!$info) throw new Exception("No se encontró el detalle del préstamo.");

    $id_prestamo_equipo = $info['id_prestamo_equipo'];
    $id_equipo = $info['id_equipo'];
    $id_instructor = $info['id_instructor'];

    // --- Insertar la Novedad en la tabla novedades2 (con la nueva columna de imagen) ---
    $stmt_novedad = $conexion->prepare("INSERT INTO novedades2 (id_prestamo_equipo_detalle, id_prestamo_equipo, id_equipo, marca_equipo, id_instructor, nombre_instructor, tipo_novedad, descripcion, ruta_imagen, id_responsable_registro, nombre_responsable_registro, rol_responsable_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_novedad->bind_param("iiisissssiss", $id_prestamo_equipo_detalle, $id_prestamo_equipo, $id_equipo, $info['marca_equipo'], $id_instructor, $info['nombre_instructor'], $tipo_novedad, $descripcion, $ruta_imagen_db, $id_responsable_registro, $nombre_responsable_registro, $rol_responsable_registro);
    $stmt_novedad->execute();
    $stmt_novedad->close();
    
    // --- Procesar la devolución (el resto del código sigue una lógica similar) ---
    $stmt_devolucion = $conexion->prepare("UPDATE prestamo_equipos_detalle SET estado_item_prestamo = 'devuelto', fecha_devolucion_item = NOW() WHERE id_prestamo_equipo_detalle = ? AND estado_item_prestamo = 'prestado'");
    $stmt_devolucion->bind_param("i", $id_prestamo_equipo_detalle);
    $stmt_devolucion->execute();
    if ($stmt_devolucion->affected_rows === 0) throw new Exception("No se pudo devolver el equipo. Ya fue devuelto o el ID es incorrecto.");
    $stmt_devolucion->close();
    
    // Resto de la lógica para devolucion_equipos, actualizar estado del equipo, etc. ...
    $condicion_devolucion = ($tipo_novedad === 'malo') ? 'malo' : 'deteriorado';

    $stmt_devolucion_registro = $conexion->prepare("INSERT INTO devolucion_equipos (id_prestamo_equipo_detalle, id_responsable, rol_responsable, responsable, estado_devolucion, observaciones, fecha_devolucion) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt_devolucion_registro->bind_param("iissss", $id_prestamo_equipo_detalle, $id_responsable_registro, $rol_responsable_registro, $nombre_responsable_registro, $condicion_devolucion, $descripcion);
    $stmt_devolucion_registro->execute();
    $stmt_devolucion_registro->close();

    $stmt_update_equipo = $conexion->prepare("UPDATE equipos SET estado = ? WHERE id_equipo = ?");
    $stmt_update_equipo->bind_param("si", $condicion_devolucion, $id_equipo);
    $stmt_update_equipo->execute();
    $stmt_update_equipo->close();
    
    // Verificación final de préstamos pendientes...
    // (Esta parte de tu código original es correcta y puede permanecer)
    $res_pendientes_prestamo = $conexion->query("SELECT COUNT(*) AS pendientes FROM prestamo_equipos_detalle WHERE id_prestamo_equipo = $id_prestamo_equipo AND estado_item_prestamo = 'prestado'");
    if ($res_pendientes_prestamo->fetch_assoc()['pendientes'] == 0) {
        $conexion->query("UPDATE prestamo_equipos SET estado_general_prestamo = 'completamente_devuelto' WHERE id_prestamo_equipo = $id_prestamo_equipo");
    }

    $res_pendientes_instructor = $conexion->query("SELECT COUNT(*) AS pendientes FROM prestamo_equipos pe JOIN prestamo_equipos_detalle ped ON pe.id_prestamo_equipo = ped.id_prestamo_equipo WHERE pe.id_instructor = $id_instructor AND ped.estado_item_prestamo = 'prestado'");
    if ($res_pendientes_instructor->fetch_assoc()['pendientes'] == 0) {
        $conexion->query("UPDATE instructores SET disponibilidad_prestamo = 'disponible' WHERE id_instructor = $id_instructor");
    }


    $conexion->commit(); 
    $response['success'] = true;
    $response['message'] = '✅ Novedad con imagen y devolución registradas exitosamente.';

} catch (Exception $e) {
    $conexion->rollback(); 
    $response['message'] = '❌ Error: ' . $e->getMessage();
}

echo json_encode($response);
$conexion->close();
?>