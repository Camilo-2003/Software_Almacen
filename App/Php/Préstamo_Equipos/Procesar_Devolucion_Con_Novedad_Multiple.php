<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/Conexion.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Software_Almacen/App/ProhibirAcceso.php';

$response = ['success' => false, 'message' => 'Error desconocido.'];

// --- Validación de Sesión ---
$id_responsable_registro = $_SESSION["id_almacenista"] ?? $_SESSION["id_administrador"] ?? 0;
$rol_responsable_registro = $_SESSION["rol"] ?? '';
$nombre_responsable_registro = trim(($_SESSION["nombres"] ?? '') . ' ' . ($_SESSION["apellidos"] ?? ''));

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id_responsable_registro > 0) {
    // --- CORRECCIÓN: Leer el cuerpo de la solicitud JSON ---
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true);

    // Recopilar datos comunes
    $tipo_novedad = filter_var($data['tipo_novedad'] ?? '', FILTER_SANITIZE_STRING);
    $descripcion = filter_var($data['descripcion'] ?? '', FILTER_SANITIZE_STRING);
    $items_a_procesar = $data['items'] ?? [];

    if (empty($items_a_procesar) || empty($tipo_novedad) || empty($descripcion)) {
        $response['message'] = 'Faltan datos. Se requiere tipo de novedad, descripción e ítems.';
        echo json_encode($response);
        exit();
    }

    $conexion->begin_transaction();
    $instructores_afectados = [];

    try {
        foreach ($items_a_procesar as $item) {
            $id_prestamo_detalle = filter_var($item['id_prestamo_equipo_detalle'], FILTER_VALIDATE_INT);
            if (!$id_prestamo_detalle) continue;

            // Obtener información del préstamo del equipo
            $stmt_info = $conexion->prepare(
                "SELECT ped.id_prestamo_equipo, ped.id_equipo, pe.id_instructor,
                        CONCAT(e.marca, ' - ', e.serial) AS equipo_marca_serial,
                        CONCAT(i.nombre, ' ', i.apellido) AS nombre_instructor
                 FROM prestamo_equipos_detalle ped
                 JOIN prestamo_equipos pe ON ped.id_prestamo_equipo = pe.id_prestamo_equipo
                 JOIN equipos e ON ped.id_equipo = e.id_equipo
                 JOIN instructores i ON pe.id_instructor = i.id_instructor
                 WHERE ped.id_prestamo_equipo_detalle = ?"
            );
            if (!$stmt_info) throw new Exception('Error al preparar consulta de info: ' . $conexion->error);
            $stmt_info->bind_param("i", $id_prestamo_detalle);
            $stmt_info->execute();
            $info = $stmt_info->get_result()->fetch_assoc();
            $stmt_info->close();

            if (!$info) continue;

            $instructores_afectados[] = $info['id_instructor'];

            // Insertar la novedad
            $stmt_novedad = $conexion->prepare(
                "INSERT INTO novedades2 (tipo_elemento, nombre_material, descripcion, tipo_novedad, fecha_novedad, rol_responsable_registro, nombre_responsable_registro, nombre_instructor, id_prestamo_asociado) 
                 VALUES ('equipo', ?, ?, ?, NOW(), ?, ?, ?, ?)"
            );
            if (!$stmt_novedad) throw new Exception('Error al preparar inserción de novedad: ' . $conexion->error);
            $stmt_novedad->bind_param("ssssssi", $info['equipo_marca_serial'], $descripcion, $tipo_novedad, $rol_responsable_registro, $nombre_responsable_registro, $info['nombre_instructor'], $info['id_prestamo_equipo']);
            $stmt_novedad->execute();
            $stmt_novedad->close();

            // Actualizar el estado del item a 'devuelto_con_novedad'
            $stmt_update_detalle = $conexion->prepare("UPDATE prestamo_equipos_detalle SET estado_item_prestamo = 'devuelto_con_novedad', fecha_devolucion_item = NOW() WHERE id_prestamo_equipo_detalle = ?");
            if (!$stmt_update_detalle) throw new Exception('Error al preparar actualización de detalle: ' . $conexion->error);
            $stmt_update_detalle->bind_param("i", $id_prestamo_detalle);
            $stmt_update_detalle->execute();
            $stmt_update_detalle->close();

            // Actualizar el estado del equipo a 'en_revision'
            $stmt_update_equipo = $conexion->prepare("UPDATE equipos SET estado = 'en_revision' WHERE id_equipo = ?");
            if (!$stmt_update_equipo) throw new Exception('Error al preparar actualización de equipo: ' . $conexion->error);
            $stmt_update_equipo->bind_param("i", $info['id_equipo']);
            $stmt_update_equipo->execute();
            $stmt_update_equipo->close();
        }

        // Verificar y actualizar estado de los instructores y préstamos generales
        $unique_instructors = array_unique($instructores_afectados);
        foreach ($unique_instructors as $id_instructor) {
            $stmt_check_pending = $conexion->prepare("SELECT COUNT(*) FROM prestamo_equipos_detalle ped JOIN prestamo_equipos pe ON ped.id_prestamo_equipo = pe.id_prestamo_equipo WHERE pe.id_instructor = ? AND ped.estado_item_prestamo = 'prestado'");
            $stmt_check_pending->bind_param("i", $id_instructor);
            $stmt_check_pending->execute();
            $pending_count = $stmt_check_pending->get_result()->fetch_row()[0];
            $stmt_check_pending->close();

            if ($pending_count == 0) {
                $stmt_update_instructor = $conexion->prepare("UPDATE instructores SET disponibilidad_prestamo = 'disponible' WHERE id_instructor = ?");
                $stmt_update_instructor->bind_param("i", $id_instructor);
                $stmt_update_instructor->execute();
                $stmt_update_instructor->close();

                $stmt_update_prestamo_general = $conexion->prepare("UPDATE prestamo_equipos SET estado_general_prestamo = 'devuelto' WHERE id_instructor = ? AND estado_general_prestamo = 'activo'");
                $stmt_update_prestamo_general->bind_param("i", $id_instructor);
                $stmt_update_prestamo_general->execute();
                $stmt_update_prestamo_general->close();
            }
        }

        $conexion->commit();
        $response['success'] = true;
        $response['message'] = '✅ Novedades y devoluciones múltiples registradas exitosamente.';

    } catch (Exception $e) {
        $conexion->rollback();
        $response['message'] = '❌ Error en la transacción: ' . $e->getMessage();
        error_log('Error en Procesar_Devolucion_Equipos_Con_Novedad_Multiple.php: ' . $e->getMessage());
    }
} else {
    $response['message'] = 'Acceso no permitido o sesión no válida.';
}

echo json_encode($response);
?>