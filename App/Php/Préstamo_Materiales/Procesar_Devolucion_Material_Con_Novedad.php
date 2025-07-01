<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1); 
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

$response = ['success' => false, 'message' => ''];

$id_responsable_registro = 0;
$rol_responsable_registro = '';
$nombre_responsable_registro = '';
if (isset($_SESSION['rol'])) {
    $rol_responsable_registro = $_SESSION['rol'];
    $nombres = isset($_SESSION["nombres"]) ? trim($_SESSION["nombres"]) : '';
    $apellidos = isset($_SESSION["apellidos"]) ? trim($_SESSION["apellidos"]) : '';
    $nombre_responsable_registro = trim($nombres . ' ' . $apellidos);
    if ($rol_responsable_registro === 'almacenista' && isset($_SESSION['id_almacenista'])) {
        $id_responsable_registro = $_SESSION['id_almacenista'];
    } elseif ($rol_responsable_registro === 'administrador' && isset($_SESSION['id_administrador'])) {
        $id_responsable_registro = $_SESSION['id_administrador'];
    }
}
if ($id_responsable_registro === 0 || empty($nombre_responsable_registro)) {
    $response['message'] = 'Acceso denegado o datos de sesión incompletos.';
    echo json_encode($response);
    exit();
}

if (!$conexion) {
    $response['message'] = 'Error: No se pudo conectar a la base de datos.';
    echo json_encode($response);
    exit();
}

$conexion->begin_transaction();

try {
    if (!isset($_POST['id_prestamo_material'], $_POST['tipo_novedad'], $_POST['descripcion'])) {
        throw new Exception('Datos de entrada incompletos.');
    }

    $id_prestamo_material = intval($_POST['id_prestamo_material']);
    $tipo_novedad = $conexion->real_escape_string($_POST['tipo_novedad']);
    $descripcion = $conexion->real_escape_string($_POST['descripcion']);
    
    $stmt_info = $conexion->prepare("
        SELECT pm.id_material, pm.id_instructor, pm.cantidad AS cantidad_prestada_actual, 
               m.stock AS stock_original, m.nombre AS nombre_material, 
               CONCAT(i.nombre, ' ', i.apellido) AS nombre_instructor,
               pm.estado as estado_prestamo_actual
        FROM prestamo_materiales pm 
        JOIN materiales m ON pm.id_material = m.id_material 
        JOIN instructores i ON pm.id_instructor = i.id_instructor 
        WHERE pm.id_prestamo_material = ? FOR UPDATE"); 
    if (!$stmt_info) throw new Exception('Error preparando consulta de información: ' . $conexion->error);
    $stmt_info->bind_param("i", $id_prestamo_material);
    $stmt_info->execute();
    $info = $stmt_info->get_result()->fetch_assoc();
    if (!$info) throw new Exception("No se encontró el préstamo con ID: $id_prestamo_material.");
    $stmt_info->close();

    if ($info['estado_prestamo_actual'] === 'devuelto') {
        throw new Exception('Este préstamo ya ha sido marcado como devuelto.');
    }

    $cantidad_a_devolver = $info['cantidad_prestada_actual']; 
    
    if ($cantidad_a_devolver <= 0) {
        throw new Exception('La cantidad prestada actual es inválida o ya fue devuelta en este préstamo.');
    }

    $ruta_imagen_db = null;
    if (isset($_FILES['novedad_file']) && $_FILES['novedad_file']['error'] == 0) {
        $directorio_subida = $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Uploads/';
        if (!is_dir($directorio_subida) && !mkdir($directorio_subida, 0777, true)) {
            throw new Exception('No se pudo crear el directorio de uploads.');
        }
        $nombre_archivo_unico = uniqid('material-') . '-' . basename($_FILES['novedad_file']['name']);
        $ruta_destino = $directorio_subida . $nombre_archivo_unico;
        if (!move_uploaded_file($_FILES['novedad_file']['tmp_name'], $ruta_destino)) {
            throw new Exception('Error al mover el archivo subido: ' . print_r(error_get_last(), true));
        }
        $ruta_imagen_db = '/Software_Almacen/App/Uploads/' . $nombre_archivo_unico;
    }

    $stmt_novedad = $conexion->prepare("
        INSERT INTO novedades2 (tipo_elemento, id_prestamo_material, id_material, nombre_material, 
                                id_instructor, nombre_instructor, tipo_novedad, descripcion, 
                                ruta_imagen, id_responsable_registro, nombre_responsable_registro, 
                                rol_responsable_registro) 
        VALUES ('material', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt_novedad) throw new Exception('Error preparando consulta de novedad: ' . $conexion->error);
    $stmt_novedad->bind_param("iissssssiss", $id_prestamo_material, $info['id_material'], $info['nombre_material'], 
                              $info['id_instructor'], $info['nombre_instructor'], $tipo_novedad, $descripcion, 
                              $ruta_imagen_db, $id_responsable_registro, $nombre_responsable_registro, $rol_responsable_registro);
    if (!$stmt_novedad->execute()) throw new Exception('Error registrando novedad: ' . $stmt_novedad->error);
    $stmt_novedad->close();

    //Insertar en devolucion_materiales 
    $observaciones = $descripcion;
    $estado_devolucion = ($tipo_novedad === 'regular' || $tipo_novedad === 'malo') ? $tipo_novedad : 'bueno_con_novedad'; 
    
    $stmt_devolucion = $conexion->prepare("
        INSERT INTO devolucion_materiales (id_prestamo_material, fecha_devolucion, observaciones, 
                                          estado_devolucion, id_responsable, rol_responsable, 
                                          responsable, cantidad) 
        VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)");
    if (!$stmt_devolucion) throw new Exception('Error preparando consulta de devolución: ' . $conexion->error);
    $stmt_devolucion->bind_param("ississi", $id_prestamo_material, $observaciones, $estado_devolucion, 
                                 $id_responsable_registro, $rol_responsable_registro, $nombre_responsable_registro, 
                                 $cantidad_a_devolver); 
    if (!$stmt_devolucion->execute()) throw new Exception('Error registrando devolución: ' . $stmt_devolucion->error);
    $stmt_devolucion->close();

    $calculated_new_cantidad_on_loan = $info['cantidad_prestada_actual'] - $cantidad_a_devolver;
    
    $final_cantidad_for_db = ($calculated_new_cantidad_on_loan == 0) ? 1 : $calculated_new_cantidad_on_loan;
    $final_cantidad_for_db = max(1, $final_cantidad_for_db); 

    $new_estado_prestamo = 'devuelto'; 

    $stmt_update_prestamo = $conexion->prepare("
        UPDATE prestamo_materiales 
        SET cantidad = ?, estado = ? 
        WHERE id_prestamo_material = ? AND estado = 'pendiente'"); 
    if (!$stmt_update_prestamo) {
        throw new Exception('Error preparando actualización de préstamo: ' . $conexion->error);
    }
    $stmt_update_prestamo->bind_param("isi", $final_cantidad_for_db, $new_estado_prestamo, $id_prestamo_material);
    
    if (!$stmt_update_prestamo->execute()) {
        throw new Exception('Error actualizando préstamo: ' . $stmt_update_prestamo->error . ' (Rows affected: ' . $stmt_update_prestamo->affected_rows . ')');
    }
    error_log("DEBUG: prestamo_materiales update successful. Affected rows: " . $stmt_update_prestamo->affected_rows);

 $stmt_stock = $conexion->prepare("UPDATE materiales SET stock = stock + ?, estado_material = ? WHERE id_material = ?");
    if (!$stmt_stock) throw new Exception('Error preparando actualización de stock y estado del material: ' . $conexion->error);

    $new_estado_material = 'disponible'; 

    if ($tipo_novedad === 'malo') {
        $new_estado_material = 'en_revision';
    } else {
        $new_estado_material = 'disponible';
    }

    $stmt_stock->bind_param("isi", $cantidad_a_devolver, $new_estado_material, $info['id_material']);
    if (!$stmt_stock->execute()) {
        throw new Exception('Error actualizando stock y estado del material: ' . $stmt_stock->error . ' (Rows affected: ' . $stmt_stock->affected_rows . ')');
    }
    $stmt_stock->close();

    $stmt_check_pending = $conexion->prepare("
        SELECT COUNT(*) as pending_loans 
        FROM prestamo_materiales 
        WHERE id_instructor = ? AND estado = 'pendiente'");
    if (!$stmt_check_pending) throw new Exception('Error preparando consulta de préstamos pendientes: ' . $conexion->error);
    $stmt_check_pending->bind_param("i", $info['id_instructor']);
    $stmt_check_pending->execute();
    $result = $stmt_check_pending->get_result()->fetch_assoc();
    $stmt_check_pending->close();

    if ($result['pending_loans'] == 0) {
        $stmt_update_instructor = $conexion->prepare("
            UPDATE instructores 
            SET disponibilidad_prestamo = 'disponible' 
            WHERE id_instructor = ? AND disponibilidad_prestamo = 'no_disponible'"); 
        if (!$stmt_update_instructor) throw new Exception('Error preparando actualización de instructor a disponible: ' . $conexion->error);
        $stmt_update_instructor->bind_param("i", $info['id_instructor']);
        if (!$stmt_update_instructor->execute()) {
            throw new Exception('Error actualizando estado del instructor: ' . $stmt_update_instructor->error . ' (Rows affected: ' . $stmt_update_instructor->affected_rows . ')');
        }
        $stmt_update_instructor->close();
    } else {
        $stmt_update_instructor = $conexion->prepare("
            UPDATE instructores 
            SET disponibilidad_prestamo = 'no_disponible' 
            WHERE id_instructor = ? AND diponibilidad_prestamo = 'disponible'"); 
        if (!$stmt_update_instructor) throw new Exception('Error preparando actualización de instructor a ocupado: ' . $conexion->error);
        $stmt_update_instructor->bind_param("i", $info['id_instructor']);
        $stmt_update_instructor->execute(); 
        $stmt_update_instructor->close();
    }

    if (!$conexion->commit()) {
        throw new Exception('Error al confirmar la transacción: ' . $conexion->error);
    }
    $response['success'] = true;
    $response['message'] = '✅ Novedad y devolución registradas exitosamente.';

} catch (Exception $e) {
    if ($conexion->in_transaction) { 
        $conexion->rollback();
    }
    $response['message'] = '❌ Error: ' . $e->getMessage();
    error_log('Error en Procesar_Devolucion_Material_Con_Novedad.php: ' . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | Trace: ' . $e->getTraceAsString());
} finally {
    if ($conexion && $conexion->ping()) { 
        $conexion->close();
    }
}
echo json_encode($response);
?>