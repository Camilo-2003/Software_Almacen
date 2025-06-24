<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';

$response = ['success' => false, 'message' => ''];

try {
    $datos = json_decode(file_get_contents('php://input'), true);
    if (!$datos || !isset($datos['instructor']) || !isset($datos['equipo_ids']) || !isset($datos['id_responsable'])) {
        throw new Exception('Datos de entrada incompletos.');
    }

    $id_instructor = $datos['instructor'];
    $equipo_ids = $datos['equipo_ids'];
    $id_responsable = $datos['id_responsable'];
    $rol_responsable = $datos['rol_responsable'];
    $responsable = $datos['responsable'];
    date_default_timezone_set('America/Bogota');
    $fecha_prestamo = date("Y-m-d h:i:s A");

    $conexion->begin_transaction();

    $stmt_insert_prestamo = $conexion->prepare("INSERT INTO prestamo_equipos (id_instructor, id_responsable, rol_responsable, responsable, fecha_prestamo, estado_general_prestamo) VALUES (?, ?, ?, ?, ?, 'activo')");
    if (!$stmt_insert_prestamo) {
        throw new Exception("Error al preparar la sentencia de inserción: " . $conexion->error);
    }
    $stmt_insert_prestamo->bind_param("iisss", $id_instructor, $id_responsable, $rol_responsable, $responsable, $fecha_prestamo);
    if (!$stmt_insert_prestamo->execute()) {
        throw new Exception("Error al insertar el préstamo: " . $stmt_insert_prestamo->error);
    }
    $id_prestamo = $conexion->insert_id;
    $stmt_insert_prestamo->close();

    $stmt_insert_detalle = $conexion->prepare("INSERT INTO prestamo_equipos_detalle (id_prestamo_equipo, id_equipo, estado_item_prestamo, fecha_vencimiento_item) VALUES (?, ?, 'prestado', DATE_FORMAT(DATE_ADD(NOW(), INTERVAL 6 HOUR), '%Y-%m-%d %h:%i:%s %p'))");
    $stmt_update_equipo = $conexion->prepare("UPDATE equipos SET estado = 'prestado' WHERE id_equipo = ?");

    if (!$stmt_insert_detalle || !$stmt_update_equipo) {
        throw new Exception("Error al preparar las sentencias de detalle o equipo: " . $conexion->error);
    }

    foreach ($equipo_ids as $id_equipo) {
        $stmt_insert_detalle->bind_param("ii", $id_prestamo, $id_equipo);
        if (!$stmt_insert_detalle->execute()) {
            throw new Exception("Error al insertar detalle del préstamo: " . $stmt_insert_detalle->error);
        }
        $stmt_update_equipo->bind_param("i", $id_equipo);
        if (!$stmt_update_equipo->execute()) {
            throw new Exception("Error al actualizar el estado del equipo: " . $stmt_update_equipo->error);
        }
    }
    $stmt_insert_detalle->close();
    $stmt_update_equipo->close();

    // Actualizar disponibilidad del instructor
    $stmt_update_disponibilidad = $conexion->prepare("UPDATE instructores SET disponibilidad_prestamo = 'no_disponible' WHERE id_instructor = ?");
    if (!$stmt_update_disponibilidad) {
        throw new Exception("Error al preparar la sentencia de actualización de disponibilidad: " . $conexion->error);
    }
    $stmt_update_disponibilidad->bind_param("i", $id_instructor);
    if (!$stmt_update_disponibilidad->execute()) {
        throw new Exception("Error al actualizar la disponibilidad del instructor: " . $conexion->error);
    }
    $stmt_update_disponibilidad->close();

    $conexion->commit();

    $response['success'] = true;
    $response['message'] = 'Préstamo registrado exitosamente.';

} catch (Exception $e) {
    $conexion->rollback();
    $response['message'] = $e->getMessage();
} finally {
    echo json_encode($response);
}
?>