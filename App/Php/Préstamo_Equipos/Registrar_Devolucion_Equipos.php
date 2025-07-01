<?php
header('Content-Type: application/json; charset=utf-8');
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

if (!isset($_SESSION["rol"]) || ($_SESSION["rol"] !== "almacenista" && $_SESSION["rol"] !== "administrador")) {
    header("Location: /Software_Almacen/App/Error.php");
    exit();
}

$response = ['success' => false, 'message' => ''];

try {
    $datos = json_decode(file_get_contents('php://input'), true);
    if (
        !$datos ||
        !isset($datos['id_prestamo_equipo_detalle']) ||
        !isset($datos['id_responsable']) ||
        !isset($datos['estado_devolucion']) ||
        !isset($datos['rol_responsable']) ||
        !isset($datos['responsable'])
    ) {
        throw new Exception('Datos de entrada incompletos.');
    }

    $id_prestamo_detalles = $datos['id_prestamo_equipo_detalle'];
    $id_responsable = $datos['id_responsable'];
    $rol_responsable = $datos['rol_responsable'];
    $responsable = $datos['responsable'];
    $estado_devolucion = $datos['estado_devolucion'];
    $observaciones = isset($datos['observaciones']) && trim($datos['observaciones']) !== '' ? trim($datos['observaciones']) : 'Sin observaciones';

    $estados_validos = ['bueno', 'regular', 'malo', 'deteriorado'];
    if (!in_array($estado_devolucion, $estados_validos)) {
        throw new Exception('Estado de devolución no válido. Debe ser: bueno, regular, malo o deteriorado.');
    }

    $conexion->begin_transaction();
    date_default_timezone_set('America/Bogota'); 
    $fecha_devolucion_item = date("Y-m-d H:i:s");

    //Sentencia para actualizar detalles de préstamo
    $stmt_update_detalle = $conexion->prepare("UPDATE prestamo_equipos_detalle SET estado_item_prestamo = 'devuelto', fecha_devolucion_item = ? WHERE id_prestamo_equipo_detalle = ?");
    if (!$stmt_update_detalle) {
        throw new Exception("Error preparando actualización de detalle: " . $conexion->error);
    }

    foreach ($id_prestamo_detalles as $id_detalle) {
        // Actualizar estado del préstamo
        $stmt_update_detalle->bind_param("si", $fecha_devolucion_item, $id_detalle);
        if (!$stmt_update_detalle->execute()) {
            throw new Exception("Error al actualizar el detalle del préstamo: " . $stmt_update_detalle->error);
        }

        // Obtener el ID del equipo relacionado
        $resultado = $conexion->query("SELECT id_equipo FROM prestamo_equipos_detalle WHERE id_prestamo_equipo_detalle = $id_detalle");
        $id_equipo = $resultado->fetch_assoc()['id_equipo'];

        // Definir el nuevo estado del equipo
        $nuevo_estado_equipo = in_array($estado_devolucion, ['malo', 'deteriorado']) ? $estado_devolucion : 'disponible';

        // Actualizar estado del equipo
        $stmt_update_equipo = $conexion->prepare("UPDATE equipos SET estado = ? WHERE id_equipo = ?");
        if (!$stmt_update_equipo) {
            throw new Exception("Error preparando actualización de equipo: " . $conexion->error);
        }
        $stmt_update_equipo->bind_param("si", $nuevo_estado_equipo, $id_equipo);
        if (!$stmt_update_equipo->execute()) {
            throw new Exception("Error al actualizar estado del equipo: " . $stmt_update_equipo->error);
        }
        $stmt_update_equipo->close();

        // Insertar en devolucion_equipos
        $stmt_insert_devolucion = $conexion->prepare("INSERT INTO devolucion_equipos (id_prestamo_equipo_detalle, id_responsable, rol_responsable, responsable, estado_devolucion, fecha_devolucion, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt_insert_devolucion) {
            throw new Exception("Error preparando inserción de devolución: " . $conexion->error);
        }
        $stmt_insert_devolucion->bind_param("iisssss", $id_detalle, $id_responsable, $rol_responsable, $responsable, $estado_devolucion, $fecha_devolucion_item, $observaciones);
        if (!$stmt_insert_devolucion->execute()) {
            throw new Exception("Error al insertar la devolución: " . $stmt_insert_devolucion->error);
        }
        $stmt_insert_devolucion->close();
    }

    $stmt_update_detalle->close();

    // Verificar si el préstamo ya fue completamente devuelto
    $id_prestamo = $conexion->query("SELECT id_prestamo_equipo FROM prestamo_equipos_detalle WHERE id_prestamo_equipo_detalle = " . intval($id_prestamo_detalles[0]))->fetch_assoc()['id_prestamo_equipo'];
    $result_pendientes = $conexion->query("SELECT COUNT(*) AS pendientes FROM prestamo_equipos_detalle WHERE id_prestamo_equipo = $id_prestamo AND estado_item_prestamo = 'prestado'");
    $row_pendientes = $result_pendientes->fetch_assoc();

    if ($row_pendientes['pendientes'] == 0) {
        $stmt_update_prestamo = $conexion->prepare("UPDATE prestamo_equipos SET estado_general_prestamo = 'completamente_devuelto' WHERE id_prestamo_equipo = ?");
        if (!$stmt_update_prestamo) {
            throw new Exception("Error preparando actualización del estado general: " . $conexion->error);
        }
        $stmt_update_prestamo->bind_param("i", $id_prestamo);
        if (!$stmt_update_prestamo->execute()) {
            throw new Exception("Error al actualizar estado general del préstamo: " . $stmt_update_prestamo->error);
        }
        $stmt_update_prestamo->close();
    }

    // Verificar si el instructor ya no tiene más préstamos activos
    $id_instructor = $conexion->query("SELECT id_instructor FROM prestamo_equipos WHERE id_prestamo_equipo = $id_prestamo")->fetch_assoc()['id_instructor'];
    $result_pendientes_instructor = $conexion->query("
        SELECT COUNT(*) AS pendientes 
        FROM prestamo_equipos_detalle ped 
        JOIN prestamo_equipos pe ON ped.id_prestamo_equipo = pe.id_prestamo_equipo 
        WHERE pe.id_instructor = $id_instructor AND ped.estado_item_prestamo = 'prestado'
    ");
    $row_pendientes_instructor = $result_pendientes_instructor->fetch_assoc();

    if ($row_pendientes_instructor['pendientes'] == 0) {
        $stmt_update_disponibilidad = $conexion->prepare("UPDATE instructores SET disponibilidad_prestamo = 'disponible' WHERE id_instructor = ?");
        if (!$stmt_update_disponibilidad) {
            throw new Exception("Error preparando actualización de disponibilidad: " . $conexion->error);
        }
        $stmt_update_disponibilidad->bind_param("i", $id_instructor);
        if (!$stmt_update_disponibilidad->execute()) {
            throw new Exception("Error al actualizar disponibilidad del instructor: " . $stmt_update_disponibilidad->error);
        }
        $stmt_update_disponibilidad->close();
    }

    $conexion->commit();
    $response['success'] = true;
    $response['message'] = '✅ Devolución registrada exitosamente.';

} catch (Exception $e) {
    $conexion->rollback();
    $response['success'] = false;
    $response['message'] = '❌ ' . $e->getMessage();
} finally {
    echo json_encode($response);
}
?>
